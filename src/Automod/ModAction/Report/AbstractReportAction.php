<?php

namespace App\Automod\ModAction\Report;

use App\Automod\ModAction\AbstractModAction;
use App\Context\Context;
use App\Entity\ReportRegex;
use App\Enum\FurtherAction;
use App\Repository\ReportRegexRepository;
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

            $context->addMessage("content has been reported for matching regex `{$rule->getRegex()}`");

            $this->report($object, $rule->getMessage());
            break;
        }

        return FurtherAction::CanContinue;
    }

    private function findMatchingRule(?string $content): ?ReportRegex
    {
        if ($content === null) {
            return null;
        }

        $regexes = $this->repository->findAll();
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
}
