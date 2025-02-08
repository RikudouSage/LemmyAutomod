<?php

namespace App\Automod\ModAction\Community;

use App\Automod\ModAction\AbstractModAction;
use App\Context\Context;
use App\Entity\CommunityRemoveRegex;
use App\Enum\FurtherAction;
use App\Helper\TextsHelper;
use App\Message\RemoveCommunityMessage;
use App\Repository\CommunityRemoveRegexRepository;
use Rikudou\LemmyApi\Response\View\CommunityView;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @extends AbstractModAction<CommunityView>
 */
final readonly class RemoveCommunityAction extends AbstractModAction
{
    public function __construct(
        private CommunityRemoveRegexRepository $removeRepository,
        private MessageBusInterface $messageBus,
    ) {
    }

    public function shouldRun(object $object): bool
    {
        if (!$object instanceof CommunityView) {
            return false;
        }

        foreach (TextsHelper::getCommunityTextsToCheck($object) as $text) {
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

        return false;
    }

    public function takeAction(object $object, Context $context = new Context()): FurtherAction
    {
        foreach (TextsHelper::getCommunityTextsToCheck($object) as $text) {
            $rule = $this->findMatchingRegexRule($text)
                ?? $this->findMatchingRegexRule($this->transliterator->transliterate($text))
            ;
            if (!$rule) {
                continue;
            }

            if ($rule instanceof CommunityRemoveRegex) {
                return $this->removeCommunity($object, $rule, $context);
            }
        }

        return FurtherAction::CanContinue;
    }

    private function findMatchingRegexRule(?string $content): ?CommunityRemoveRegex
    {
        if ($content === null) {
            return null;
        }

        $regexes = $this->removeRepository->findBy([
            'enabled' => true,
        ]);
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

    private function removeCommunity(CommunityView $object, CommunityRemoveRegex $rule, Context $context): FurtherAction
    {
        $context->addMessage("removed community for matching regex `{$rule->getRegex()}`");
        $this->messageBus->dispatch(new RemoveCommunityMessage(
            community: $object->community,
            reason: $rule->getReason(),
            banMods: $rule->shouldBanModerators(),
        ));
        return FurtherAction::ShouldAbort;
    }
}
