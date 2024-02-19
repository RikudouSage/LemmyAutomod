<?php

namespace App\Automod\ModAction\BanUser;

use App\Automod\ModAction\AbstractModAction;
use App\Context\Context;
use App\Enum\FurtherAction;
use App\Message\BanUserMessage;
use App\Message\RemovePostMessage;
use App\Repository\BannedImageRepository;
use App\Service\ImageFetcher;
use Override;
use Rikudou\LemmyApi\Response\View\PostView;
use SapientPro\ImageComparator\ImageComparator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Throwable;

/**
 * @extends AbstractModAction<PostView>
 */
final readonly class BanUserForImageAction extends AbstractModAction
{
    public function __construct(
        private BannedImageRepository $imageRepository,
        private ImageFetcher $imageFetcher,
        private ImageComparator $imageComparator,
        private MessageBusInterface $messageBus,
        #[Autowire('%app.image_check.regex%')]
        private string $imageRegex,
    ) {
    }

    #[Override]
    public function shouldRun(object $object): bool
    {
        if (!$object instanceof PostView) {
            return false;
        }
        if ($object->creator->banned) {
            return false;
        }
        if (!$object->post->url) {
            return false;
        }
        $regex = '@' . str_replace('@', '\@', $this->imageRegex) . '@';
        if (!preg_match($regex, $object->post->url)) {
            return false;
        }
        if (!count($this->imageRepository->findAll())) {
            return false;
        }

        return true;
    }

    #[Override]
    public function takeAction(object $object, Context $context = new Context()): FurtherAction
    {
        try {
            $hash = $this->imageFetcher->getImageHash($object->post->url);
        } catch (ExceptionInterface) {
            return FurtherAction::CanContinue;
        } catch (Throwable $e) {
            error_log("Failed getting image hash: {$e->getMessage()}, {$e->getTraceAsString()}");
            return FurtherAction::CanContinue;
        }
        if ($hash === null) {
            return FurtherAction::CanContinue;
        }
        foreach ($this->imageRepository->findAll() as $image) {
            $similarity = $this->imageComparator->compareHashStrings($hash, $image->getImageHash());
            if ($similarity < $image->getSimilarityPercent()) {
                continue;
            }
            $this->messageBus->dispatch(new BanUserMessage(user: $object->creator, reason: $image->getReason() ?? '', removePosts: $image->shouldRemoveAll(), removeComments: $image->shouldRemoveAll()), [
                new DispatchAfterCurrentBusStamp(),
            ]);
            if (!$image->shouldRemoveAll()) {
                $this->messageBus->dispatch(new RemovePostMessage(postId: $object->post->id), [
                    new DispatchAfterCurrentBusStamp(),
                ]);
            }
            $reportMessage = "user has been banned because the image in their post is {$similarity}% similar to banned image with ";
            if ($description = $image->getDescription()) {
                $reportMessage .= "description: {$description}";
            } else {
                $reportMessage .= "id {$image->getId()}";
            }
            $context->addMessage($reportMessage);

            return FurtherAction::ShouldAbort;
        }

        // this shouldn't really happen, but just to be sure
        return FurtherAction::ShouldAbort;
    }
}
