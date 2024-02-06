<?php

namespace App\Automod\ModAction\BanUser;

use Rikudou\LemmyApi\Response\Model\Person;

/**
 * @extends AbstractBanUserModAction<Person>
 */
final readonly class BanUserForUsernamesAction extends AbstractBanUserModAction
{
    public function shouldRun(object $object): bool
    {
        return $object instanceof Person && parent::shouldRun($object);
    }

    protected function getTextsToCheck(object $object): array
    {
        return [$object->name, $object->displayName, $object->matrixUserId];
    }

    protected function getAuthor(object $object): Person
    {
        return $object;
    }
}
