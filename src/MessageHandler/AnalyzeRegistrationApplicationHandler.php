<?php

namespace App\MessageHandler;

use App\Automod\Automod;
use App\Message\AnalyzeRegistrationApplicationMessage;
use Rikudou\LemmyApi\LemmyApi;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class AnalyzeRegistrationApplicationHandler
{
    public function __construct(
        private LemmyApi $api,
        private Automod $automod,
    ) {
    }

    public function __invoke(AnalyzeRegistrationApplicationMessage $message): void
    {
        $applications = $this->api->admin()->listRegistrationApplications(unreadOnly: true);
        $target = null;
        foreach ($applications as $application) {
            if ($application->registrationApplication->id === $message->id) {
                $target = $application;
                break;
            }
        }
        if ($target === null) {
            return;
        }

        $this->automod->analyze($target);
    }
}
