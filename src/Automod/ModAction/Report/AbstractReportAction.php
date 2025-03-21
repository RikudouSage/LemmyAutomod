<?php

namespace App\Automod\ModAction\Report;

use App\Automod\ModAction\AbstractModAction;
use App\Context\Context;
use App\Entity\ReportRegex;
use App\Enum\FurtherAction;
use App\Repository\ReportRegexRepository;
use App\Service\InstanceLinkConverter;
use App\Service\Notification\NotificationSender;
use LogicException;
use Rikudou\LemmyApi\Response\View\CommentView;
use Rikudou\LemmyApi\Response\View\PostView;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @template TObject of (PostView|CommentView)
 * @extends AbstractModAction<TObject>
 */
abstract readonly class AbstractReportAction extends AbstractModAction
{
    private ReportRegexRepository $repository;
    private NotificationSender $notificationSender;
    private InstanceLinkConverter $linkConverter;

    /**
     * @param TObject $object
     * @return array<string>
     */
    abstract protected function getTextsToCheck(object $object): array;

    /**
     * @param TObject $object
     */
    abstract protected function report(object $object, string $message): void;

    #[Required]
    public function setRegexRepository(ReportRegexRepository $repository): void
    {
        $this->repository = $repository;
    }

    #[Required]
    public function setNotificationSender(NotificationSender $notificationSender): void
    {
        $this->notificationSender = $notificationSender;
    }

    #[Required]
    public function setLinkConverter(InstanceLinkConverter $linkConverter): void
    {
        $this->linkConverter = $linkConverter;
    }

    public function shouldRun(object $object): bool
    {
        if (!$object instanceof PostView && !$object instanceof CommentView) {
            return false;
        }
        foreach ($this->getTextsToCheck($object) as $text) {
            if ($text === null) {
                continue;
            }
            $text = $this->transliterator->transliterate($text);
            if ($this->findMatchingRule($text)) {
                return true;
            }
        }

        return false;
    }

    public function takeAction(object $object, Context $context = new Context()): FurtherAction
    {
        foreach ($this->getTextsToCheck($object) as $text) {
            if ($text === null) {
                continue;
            }
            $text = $this->transliterator->transliterate($text);
            if (!$rule = $this->findMatchingRule($text)) {
                continue;
            }


            if ($rule->isPrivate()) {
                $this->reportPrivately($object, $rule->getMessage());
            } else {
                $context->addMessage("content has been reported for matching regex `{$rule->getRegex()}`");
                $this->report($object, $rule->getMessage());
            }
            break;
        }

        return FurtherAction::CanContinue;
    }

    private function findMatchingRule(?string $content): ?ReportRegex
    {
        if ($content === null) {
            return null;
        }

        $regexes = $this->repository->findBy(['enabled' => true]);
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

    /**
     * @param TObject $object
     */
    private function reportPrivately(object $object, string $message): void
    {
        if ($object instanceof PostView) {
            $link = $this->linkConverter->convertPostLink($object->post);
        } else if ($object instanceof CommentView) {
            $link = $this->linkConverter->convertCommentLink($object->comment);
        } else {
            throw new LogicException('Uncovered case: ' . $object::class);
        }

        $this->notificationSender->sendNotificationAsync("An automated report for {$link}: {$message}");
    }
}
