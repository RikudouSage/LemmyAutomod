<?php

namespace App\Automod\ModAction;

use App\Enum\RunConfiguration;
use App\Service\Transliterator;
use Rikudou\LemmyApi\LemmyApi;
use Rikudou\LemmyApi\Response\Model\Person;
use Rikudou\LemmyApi\Response\View\CommentReportView;
use Rikudou\LemmyApi\Response\View\CommentView;
use Rikudou\LemmyApi\Response\View\PostReportView;
use Rikudou\LemmyApi\Response\View\PostView;
use Rikudou\LemmyApi\Response\View\PrivateMessageReportView;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @template TObject of (PostView|CommentView|Person|CommentReportView|PostReportView|PrivateMessageReportView)
 * @implements ModAction<TObject>
 */
abstract readonly class AbstractModAction implements ModAction
{
    protected LemmyApi $api;
    protected Transliterator $transliterator;

    public function getRunConfiguration(): RunConfiguration
    {
        return RunConfiguration::WhenNotAborted;
    }

    #[Required]
    public function setApi(LemmyApi $api): void
    {
        $this->api = $api;
    }

    #[Required]
    public function setTransliterator(Transliterator $transliterator): void
    {
        $this->transliterator = $transliterator;
    }
}
