<?php

namespace App\Service;

use App\Dto\Stats;
use App\Service\Notification\LemmyMessageChannel;
use App\Service\Notification\NotificationChannel;
use Psr\Container\ContainerInterface;
use Rikudou\Iterables\Iterables;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\Messenger\Transport\Receiver\MessageCountAwareInterface;

final readonly class StatsService
{
    /**
     * @param iterable<NotificationChannel> $channels
     * @param array<string> $lemmyUsers
     */
    public function __construct(
        #[Autowire(service: 'messenger.receiver_locator')]
        private ContainerInterface $transportLocator,
        #[TaggedIterator('app.notification.channel')]
        private iterable $channels,
        #[Autowire('%app.lemmy.notify_users%')]
        private array $lemmyUsers,
        #[Autowire('%app.notify.new_users%')]
        public bool $notifyNewUsers,
        #[Autowire('%app.notify.first_post_comment%')]
        public bool $notifyFirstPostComment,
        #[Autowire('%app.notify.reports%')]
        public bool $notifyReports,
        #[Autowire('%app.fediseer.key%')]
        public string $fediseerApiKey,
        #[Autowire('%app.ai_horde.api_key%')]
        public string $aiHordeApiKey,
        #[Autowire('%app.signature.key%')]
        public string $webhookSignatureKey,
        #[Autowire('%app.log_level%')]
        public string $logLevel,
    ) {
    }

    public function getStats(): Stats
    {
        $transport = $this->transportLocator->get('async');
        $messageCount = null;
        if ($transport instanceof MessageCountAwareInterface) {
            $messageCount = $transport->getMessageCount();
        }

        $notificationChannelNames = Iterables::map(
            fn (NotificationChannel $channel) => $channel->getName(),
            Iterables::filter(
                $this->channels,
                fn (NotificationChannel $channel) => $channel->isEnabled(),
            ),
        );

        $lemmyUsers = null;
        if (count($this->lemmyUsers)) {
            $lemmyUsers = $this->lemmyUsers;
        }

        return new Stats(
            messageCount: $messageCount,
            notificationChannels: [...$notificationChannelNames],
            lemmyNotificationUsers: $lemmyUsers,
            notifyNewUsers: $this->notifyNewUsers,
            notifyFirstPostComment: $this->notifyFirstPostComment,
            notifyReports: $this->notifyReports,
            usesFediseer: !!$this->fediseerApiKey,
            aiHordeConfigured: !!$this->aiHordeApiKey,
            signedWebhooks: !!$this->webhookSignatureKey,
            logLevel: $this->logLevel,
        );
    }
}
