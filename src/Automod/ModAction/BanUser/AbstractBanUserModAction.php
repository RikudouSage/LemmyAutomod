<?php

namespace App\Automod\ModAction\BanUser;

use App\Automod\ModAction\AbstractModAction;
use App\Context\Context;
use App\Entity\BannedUsername;
use App\Entity\InstanceBanRegex;
use App\Enum\FurtherAction;
use App\Message\BanUserMessage;
use App\Message\RemoveCommentMessage;
use App\Message\RemovePostMessage;
use App\Repository\BannedUsernameRepository;
use App\Repository\InstanceBanRegexRepository;
use App\Service\Transliterator;
use Rikudou\LemmyApi\Enum\SortType;
use Rikudou\LemmyApi\Response\Model\Person;
use Rikudou\LemmyApi\Response\View\CommentView;
use Rikudou\LemmyApi\Response\View\PostView;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @template TObject of (PostView|CommentView|Person)
 * @extends AbstractModAction<TObject>
 */
abstract readonly class AbstractBanUserModAction extends AbstractModAction
{
    private InstanceBanRegexRepository $banRegexRepository;
    private MessageBusInterface $messageBus;
    private BannedUsernameRepository $usernameRepository;

    public function shouldRun(object $object): bool
    {
        $author = $this->getAuthor($object);
        if ($author->banned) {
            return false;
        }
        foreach ($this->getTextsToCheck($object) as $text) {
            if ($text === null) {
                continue;
            }
            if ($this->findMatchingRegexRule($text)) {
                return true;
            }
            $text = $this->transliterator->transliterate($text);
            if ($this->findMatchingRegexRule($text)) {
                return true;
            }
        }

        if ($this->findMatchingUsernameRule($author->name)) {
            return true;
        }

        return false;
    }

    public function takeAction(object $object, Context $context = new Context()): FurtherAction
    {
        $creator = $this->getAuthor($object);

        // do nothing, but the top admin will still be notified
        if (($object instanceof PostView || $object instanceof CommentView) && $object->creatorIsAdmin) {
            return FurtherAction::ShouldAbort;
        }
        if ($creator->admin) {
            return FurtherAction::ShouldAbort;
        }

        foreach ($this->getTextsToCheck($object) as $text) {
            if ($text === null) {
                continue;
            }
            $transliterated = $this->transliterator->transliterate($text);
            $rule = $this->findMatchingRegexRule($text) ?? $this->findMatchingRegexRule($transliterated);
            if (!$rule) {
                continue;
            }

            $context->addMessage("banned for matching regex `{$rule->getRegex()}`");

            if ($object instanceof PostView) {
                $this->messageBus->dispatch(new RemovePostMessage($object->post->id), [
                    new DispatchAfterCurrentBusStamp(),
                ]);
            } elseif ($object instanceof CommentView) {
                $this->messageBus->dispatch(new RemoveCommentMessage($object->comment->id), [
                    new DispatchAfterCurrentBusStamp(),
                ]);
            }
            $this->messageBus->dispatch(new BanUserMessage(user: $creator, reason: $rule->getReason(), removePosts: $rule->shouldRemoveAll(), removeComments: $rule->shouldRemoveAll()), [
                new DispatchAfterCurrentBusStamp(),
            ]);
            break;
        }

        if ($banned = $this->findMatchingUsernameRule($creator->name)) {
            $context->addMessage("banned for username matching regex `{$banned->getUsername()}`");
            $this->messageBus->dispatch(new BanUserMessage(user: $creator, reason: $banned->getReason(), removePosts: $banned->shouldRemoveAll(), removeComments: $banned->shouldRemoveAll()), [
                new DispatchAfterCurrentBusStamp(),
            ]);
        }

        return FurtherAction::ShouldAbort;
    }

    /**
     * @param TObject $object
     * @return array<string>
     */
    abstract protected function getTextsToCheck(object $object): array;

    /**
     * @param TObject $object
     */
    abstract protected function getAuthor(object $object): Person;

    private function findMatchingRegexRule(?string $content): ?InstanceBanRegex
    {
        if ($content === null) {
            return null;
        }

        $regexes = $this->banRegexRepository->findAll();
        foreach ($regexes as $regexEntity) {
            $regex = str_replace('@', '\\@', $regexEntity->getRegex());
            $regex = "@{$regex}@i";
            if (!preg_match($regex, $content)) {
                continue;
            }

            return $regexEntity;
        }

        return null;
    }

    private function findMatchingUsernameRule(?string $content): ?BannedUsername
    {
        if ($content === null) {
            return null;
        }

        $bannedUsernames = $this->usernameRepository->findAll();
        foreach ($bannedUsernames as $bannedUsername) {
            $regex = str_replace('@', '\\@', $bannedUsername->getUsername());
            $regex = "@{$regex}@i";
            if (!preg_match($regex, $content)) {
                continue;
            }

            return $bannedUsername;
        }

        return null;
    }

    #[Required]
    public function setRegexRepository(InstanceBanRegexRepository $banRegexRepository): void
    {
        $this->banRegexRepository = $banRegexRepository;
    }

    #[Required]
    public function setUsernamesRepository(BannedUsernameRepository $bannedUsernameRepository): void
    {
        $this->usernameRepository = $bannedUsernameRepository;
    }

    #[Required]
    public function setMessageBus(MessageBusInterface $messageBus): void
    {
        $this->messageBus = $messageBus;
    }
}
