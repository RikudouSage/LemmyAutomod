<?php

namespace App\Automod\ModAction;

use App\Enum\RunConfiguration;
use Rikudou\LemmyApi\LemmyApi;
use Rikudou\LemmyApi\Response\Model\Person;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @template TObject of object
 * @implements ModAction<TObject>
 */
abstract readonly class AbstractModAction implements ModAction
{
    protected LemmyApi $api;

    public function getRunConfiguration(): RunConfiguration
    {
        return RunConfiguration::WhenNotAborted;
    }

    #[Required]
    public function setApi(LemmyApi $api): void
    {
        $this->api = $api;
    }
}
