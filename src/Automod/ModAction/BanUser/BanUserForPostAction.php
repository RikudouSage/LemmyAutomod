<?php

namespace App\Automod\ModAction\BanUser;

use App\Entity\InstanceBanRegex;
use App\Enum\FurtherAction;
use App\Repository\InstanceBanRegexRepository;
use Rikudou\LemmyApi\Response\View\PostView;

/**
 * @extends AbstractBanUserModAction<PostView>
 */
final readonly class BanUserForPostAction extends AbstractBanUserModAction
{
    public function shouldRun(object $object): bool
    {
        if (!$object instanceof PostView) {
            return false;
        }

        return parent::shouldRun($object);
    }

    protected function getTextsToCheck(object $object): array
    {
        return [$object->post->name, $object->post->body, $object->post->url, $object->creator->name, $object->creator->displayName];
    }
}
