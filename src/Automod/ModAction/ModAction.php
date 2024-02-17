<?php

namespace App\Automod\ModAction;

use App\Context\Context;
use App\Dto\Model\LocalUser;
use App\Enum\FurtherAction;
use App\Enum\RunConfiguration;
use Rikudou\LemmyApi\Response\Model\Person;
use Rikudou\LemmyApi\Response\View\CommentReportView;
use Rikudou\LemmyApi\Response\View\CommentView;
use Rikudou\LemmyApi\Response\View\PostReportView;
use Rikudou\LemmyApi\Response\View\PostView;
use Rikudou\LemmyApi\Response\View\PrivateMessageReportView;
use Rikudou\LemmyApi\Response\View\RegistrationApplicationView;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * @template TObject of (PostView|CommentView|Person|CommentReportView|PostReportView|PrivateMessageReportView|RegistrationApplicationView|LocalUser)
 */
#[AutoconfigureTag(name: 'app.mod_action')]
interface ModAction
{
    public function shouldRun(object $object): bool;

    /**
     * @param TObject $object
     */
    public function takeAction(object $object, Context $context = new Context()): FurtherAction;

    public function getRunConfiguration(): RunConfiguration;

}
