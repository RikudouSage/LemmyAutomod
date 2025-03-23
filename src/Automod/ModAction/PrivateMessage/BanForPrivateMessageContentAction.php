<?php

namespace App\Automod\ModAction\PrivateMessage;

use App\Automod\ModAction\AbstractModAction;
use App\Context\Context;
use App\Dto\Model\PrivateMessage;
use App\Entity\PrivateMessageBanRegex;
use App\Enum\FurtherAction;
use App\Message\BanUserMessage;
use App\Repository\PrivateMessageBanRegexRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @extends AbstractModAction<PrivateMessage>
 */
final readonly class BanForPrivateMessageContentAction extends AbstractModAction
{
    public function __construct(
        private PrivateMessageBanRegexRepository $repository,
        private MessageBusInterface $messageBus,
        private LoggerInterface $logger,
    ) {
    }

    public function shouldRun(object $object): bool
    {
        if (!$object instanceof PrivateMessage) {
            return false;
        }

        $this->logger->debug("Received a private message with id '{$object->id}'");
        if ($this->findMatchingRegexRule($object->content)) {
            $this->logger->debug('The private message matches a rule');
            return true;
        }

        if ($this->findMatchingRegexRule($this->transliterator->transliterate($object->content))) {
            $this->logger->debug('The private message matches a rule (transliterated)');
            return true;
        }

        $this->logger->debug('The private message does not match any rules');
        return false;
    }

    public function takeAction(object $object, Context $context = new Context()): FurtherAction
    {
        $rule = $this->findMatchingRegexRule($object->content)
            ?? $this->findMatchingRegexRule($this->transliterator->transliterate($object->content))
        ;

        $this->logger->debug("The matched rule has id '{$rule->getId()}' and regex '{$rule->getRegex()}'");

        $sender = $this->api->user()->get($object->creatorId);
        $this->messageBus->dispatch(new BanUserMessage(
            user: $sender,
            reason: $rule->getReason(),
            removePosts: $rule->shouldRemoveAll(),
            removeComments: $rule->shouldRemoveAll(),
        ));
        $context->addMessage("banned for matching regex `{$rule->getRegex()}`");

        return FurtherAction::ShouldAbort;
    }

    private function findMatchingRegexRule(string $content): ?PrivateMessageBanRegex
    {
        $regexes = $this->repository->findBy([
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
}
