<?php

namespace App\Automod\ModAction\BanUser;

use App\Automod\ModAction\AbstractModAction;
use App\Context\Context;
use App\Dto\Model\LocalUser;
use App\Entity\BannedEmail;
use App\Enum\FurtherAction;
use App\Message\BanUserMessage;
use App\Repository\BannedEmailRepository;
use Override;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

/**
 * @extends AbstractBanUserModAction<LocalUser>
 */
final readonly class BanUserForEmailAction extends AbstractModAction
{
    public function __construct(
        private BannedEmailRepository $bannedEmailRepository,
        private MessageBusInterface $messageBus,
    ) {
    }

    public function shouldRun(object $object): bool
    {
        if (!$object instanceof LocalUser) {
            return false;
        }

        return $this->findMatchingEmailRegex($object->email) !== null;
    }

    private function findMatchingEmailRegex(string $email): ?BannedEmail
    {
        foreach ($this->bannedEmailRepository->findAll() as $regexEntity) {
            $regex = str_replace('@', '\\@', $regexEntity->getRegex());
            $regex = "@{$regex}@i";
            if (!preg_match($regex, $email)) {
                continue;
            }

            return $regexEntity;
        }

        return null;
    }

    #[Override]
    public function takeAction(object $object, Context $context = new Context()): FurtherAction
    {
        if ($object->admin) {
            return FurtherAction::ShouldAbort;
        }

        $rule = $this->findMatchingEmailRegex($object->email);
        assert($rule !== null);

        $this->messageBus->dispatch(new BanUserMessage(user: $this->api->user()->get($object->personId), reason: $rule->getReason(), removePosts: true, removeComments: true), [
            new DispatchAfterCurrentBusStamp(),
        ]);
        $context->addMessage("banned for their email matching regex `{$rule->getRegex()}`");

        return FurtherAction::ShouldAbort;
    }
}
