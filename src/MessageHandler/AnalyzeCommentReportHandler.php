<?php

namespace App\MessageHandler;

use App\Automod\Automod;
use App\Message\AnalyzeCommentReportMessage;
use Rikudou\LemmyApi\LemmyApi;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class AnalyzeCommentReportHandler
{
    public function __construct(
        private LemmyApi $api,
        private Automod $automod,
    ) {
    }

    public function __invoke(AnalyzeCommentReportMessage $message): void
    {
        $reports = $this->api->moderator()->listCommentReports(unresolvedOnly: true);
        $targetReport = null;
        foreach ($reports as $report) {
            if ($report->comment->id === $message->commentId) {
                $targetReport = $report;
                break;
            }
        }
        if ($targetReport === null) {
            return;
        }

        $this->automod->analyze($targetReport);
    }
}
