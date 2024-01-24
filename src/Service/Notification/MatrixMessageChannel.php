<?php

namespace App\Service\Notification;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class MatrixMessageChannel implements NotificationChannel
{
    /**
     * @param array<string> $rooms
     */
    public function __construct(
        #[Autowire('%app.notify.matrix.token%')]
        private string $token,
        #[Autowire('%app.notify.matrix.rooms%')]
        private array $rooms,
        #[Autowire('%app.notify.matrix.instance%')]
        private string $matrixInstance,
        private HttpClientInterface $httpClient,
        private CacheItemPoolInterface $cache,
    ) {
    }

    public function getName(): string
    {
        return 'matrix';
    }

    public function isEnabled(): bool
    {
        return $this->token && $this->rooms;
    }

    public function notify(string $message): void
    {
        foreach ($this->rooms as $room) {
            $roomId = $this->getRoomId($room);
            $instance = $this->getInstance($room);
            $url = "https://{$instance}/_matrix/client/r0/rooms/{$roomId}/send/m.room.message?access_token={$this->token}";
            $this->httpClient->request(Request::METHOD_POST, $url, [
                'json' => [
                    'msgtype' => 'm.text',
                    'body' => $message,
                ],
            ]);
        }
    }

    private function getRoomId(string $room): string
    {
        if (str_starts_with($room, '!')) {
            return $room;
        }
        $cacheItem = $this->cache->getItem("room_id_" . str_replace(':', '_', $room));
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $instance = $this->getInstance($room);
        $sanitizedRoom = str_replace('#', '%23', $room);
        $url = "https://{$instance}/_matrix/client/v3/directory/room/{$sanitizedRoom}";
        $json = json_decode($this->httpClient->request(Request::METHOD_GET, $url)->getContent(), true, flags: JSON_THROW_ON_ERROR);

        $cacheItem->set($json['room_id']);
        $this->cache->save($cacheItem);

        return $cacheItem->get();
    }

    private function getInstance(string $room): string
    {
        if ($this->matrixInstance) {
            return $this->matrixInstance;
        }

        return explode(':', $room)[1];
    }
}
