<?php

namespace App\Automod\ModAction\BanUser;

use App\Automod\ModAction\AbstractModAction;
use App\Entity\InstanceBanRegex;
use App\Enum\FurtherAction;
use App\Repository\InstanceBanRegexRepository;
use Rikudou\LemmyApi\Response\Model\Person;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @template TObject of object
 * @extends AbstractModAction<TObject>
 */
abstract readonly class AbstractBanUserModAction extends AbstractModAction
{
    private InstanceBanRegexRepository $banRegexRepository;

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

            $this->api->admin()->banUser(user: $object->creator, reason: $rule->getReason());
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
}
