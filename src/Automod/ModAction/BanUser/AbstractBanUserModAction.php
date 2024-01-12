<?php

namespace App\Automod\ModAction\BanUser;

use App\Automod\ModAction\AbstractModAction;
use App\Entity\InstanceBanRegex;
use App\Enum\FurtherAction;
use App\Message\RemovePostMessage;
use App\Repository\InstanceBanRegexRepository;
use Rikudou\LemmyApi\Enum\SortType;
use Rikudou\LemmyApi\Response\Model\Person;
use Rikudou\LemmyApi\Response\View\CommentView;
use Rikudou\LemmyApi\Response\View\PostView;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @template TObject of (PostView|CommentView|Person)
 * @extends AbstractModAction<TObject>
 */
abstract readonly class AbstractBanUserModAction extends AbstractModAction
{
    private InstanceBanRegexRepository $banRegexRepository;
    private MessageBusInterface $messageBus;

    public function getDescription(): ?string
    {
        return 'user has been banned';
    }

    public function shouldRun(object $object): bool
    {
        foreach ($this->getTextsToCheck($object) as $text) {
            if ($this->findMatchingRule($text)) {
                return true;
            }
        }

        return false;
    }

    public function takeAction(object $object, array $previousActions = []): FurtherAction
    {
        if ($object->creatorIsAdmin || $object->creator->admin) {
            // do nothing, but the top admin will still be notified
            return FurtherAction::ShouldAbort;
        }
        foreach ($this->getTextsToCheck($object) as $text) {
            if (!$rule = $this->findMatchingRule($text)) {
                continue;
            }

            $removePosts = !$object->creator->local;
            $this->api->admin()->banUser(user: $object->creator, reason: $rule->getReason(), removeData: $removePosts);
            if ($object->creator->local) {
                $this->deletePostsFederated($object->creator);
            }
            break;
        }

        return FurtherAction::ShouldAbort;
    }

    /**
     * @param TObject $object
     * @return array<string>
     */
    abstract protected function getTextsToCheck(object $object): array;

    private function findMatchingRule(?string $content): ?InstanceBanRegex
    {
        if ($content === null) {
            return null;
        }

        $regexes = $this->banRegexRepository->findAll();
        foreach ($regexes as $regexEntity) {
            $regex = str_replace('@', '\\@', $regexEntity->getRegex());
            $regex = "@{$regex}@";
            if (!preg_match($regex, $content)) {
                continue;
            }

            return $regexEntity;
        }

        return null;
    }

    #[Required]
    public function setRegexRepository(InstanceBanRegexRepository $banRegexRepository): void
    {
        $this->banRegexRepository = $banRegexRepository;
    }

    #[Required]
    public function setMessageBus(MessageBusInterface $messageBus): void
    {
        $this->messageBus = $messageBus;
    }

    private function deletePostsFederated(Person $user): void
    {
        $page = 1;
        do {
            $posts = $this->api->user()->getPosts($user, page: $page, sort: SortType::New);
            foreach ($posts as $post) {
                $this->messageBus->dispatch(new RemovePostMessage($post->post->id));
            }
            ++$page;
        } while (count($posts));
    }
}
