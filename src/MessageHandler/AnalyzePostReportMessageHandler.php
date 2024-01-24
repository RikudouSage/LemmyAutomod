<?php

namespace App\MessageHandler;

use App\Automod\Automod;
use App\Message\AnalyzePostReportMessage;
use Rikudou\LemmyApi\LemmyApi;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class AnalyzePostReportMessageHandler
{
    public function __construct(
        private LemmyApi $api,
        private Automod $automod,
    ) {
    }

    public function __invoke(AnalyzePostReportMessage $message): void
    {
        $reports = $this->api->moderator()->listPostReports(unresolvedOnly: true);
        $targetReport = null;
        foreach ($reports as $report) {
            if ($report->post->id === $message->postId) {
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
