<?php

namespace App\Automod\ModAction;

use App\Enum\RunConfiguration;
use Rikudou\LemmyApi\LemmyApi;
use Rikudou\LemmyApi\Response\Model\Person;
use Rikudou\LemmyApi\Response\View\CommentView;
use Rikudou\LemmyApi\Response\View\PostView;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @template TObject of (PostView|CommentView|Person)
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
