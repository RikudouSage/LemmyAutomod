<?php

namespace App\Automod\ModAction\BanUser;

use App\Automod\ModAction\AbstractModAction;
use Rikudou\LemmyApi\Response\Model\Person;

/**
 * @template TObject of object
 * @extends AbstractModAction<TObject>
 */
abstract readonly class AbstractBanUserModAction extends AbstractModAction
{
    public function getDescription(): ?string
    {
        return 'user has been banned';
    }
}
