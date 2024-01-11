<?php

namespace App\Automod\ModAction\BanUser;

use App\Entity\InstanceBanRegex;
use App\Enum\FurtherAction;
use App\Repository\InstanceBanRegexRepository;
use Rikudou\LemmyApi\Response\View\PostView;

/**
 * @extends AbstractBanUserModAction<PostView>
 */
final readonly class BanUserForPostAdminAction extends AbstractBanUserModAction
{
    public function __construct(
        private InstanceBanRegexRepository $banRegexRepository,
    ) {
    }

    public function shouldRun(object $object): bool
    {
        if (!$object instanceof PostView) {
            return false;
        }

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

    /**
     * @return array<string|null>
     */
    private function getTextsToCheck(PostView $post): array
    {
        return [$post->post->name, $post->post->body, $post->post->url, $post->creator->name, $post->creator->displayName];
    }
}
