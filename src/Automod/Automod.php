<?php

namespace App\Automod;

use App\Automod\ModAction\ModAction;
use App\Context\Context;
use App\Dto\Model\LocalUser;
use App\Enum\FurtherAction;
use App\Enum\RunConfiguration;
use Rikudou\LemmyApi\Response\Model\Person;
use Rikudou\LemmyApi\Response\View\CommentReportView;
use Rikudou\LemmyApi\Response\View\CommentView;
use Rikudou\LemmyApi\Response\View\PostReportView;
use Rikudou\LemmyApi\Response\View\PostView;
use Rikudou\LemmyApi\Response\View\RegistrationApplicationView;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

final readonly class Automod
{
    /**
     * @param iterable<ModAction> $actions
     */
    public function __construct(
        #[TaggedIterator('app.mod_action')]
        private iterable $actions,
    ) {
    }

    public function analyze(
        PostView|CommentView|Person|CommentReportView|PostReportView|RegistrationApplicationView|LocalUser $object
    ): void {
        $furtherAction = FurtherAction::CanContinue;
        $context = new Context();

        foreach ($this->actions as $action) {
            if ($furtherAction !== FurtherAction::CanContinue && $action->getRunConfiguration() !== RunConfiguration::Always) {
                continue;
            }
            if (!$action->shouldRun($object)) {
                continue;
            }
            $furtherAction = $action->takeAction($object, $context);
        }
    }
}
