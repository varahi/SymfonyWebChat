<?php

namespace App\Service;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class OperatorNotificationService
{
    private const CACHE_KEY = 'operator_notifications';

    public function __construct(
        private CacheInterface $cache
    ) {
    }

    public function add(string $text): void
    {
        $this->cache->get(self::CACHE_KEY, function (ItemInterface $item) use ($text) {
            $item->expiresAfter(3600);

            return [[
                'text' => $text,
                'ts' => time(),
            ]];
        });

        // аккуратно дописываем
        $notifications = $this->all();
        $notifications[] = [
            'text' => $text,
            'ts' => time(),
        ];

        $this->cache->delete(self::CACHE_KEY);
        $this->cache->get(self::CACHE_KEY, fn () => $notifications);
    }

    public function removeByTs(int $ts): void
    {
        $notifications = $this->all();
        $notifications = array_filter($notifications, fn ($n) => $n['ts'] !== $ts);
        $this->cache->delete(self::CACHE_KEY);
        $this->cache->get(self::CACHE_KEY, fn () => $notifications);
    }

    public function remove(string $text): void
    {
        $notifications = $this->all();
        $notifications = array_filter($notifications, fn ($n) => $n['text'] !== $text);
        $this->cache->delete(self::CACHE_KEY);
        $this->cache->get(self::CACHE_KEY, fn () => $notifications);
    }

    public function all(): array
    {
        return $this->cache->get(self::CACHE_KEY, fn () => []);
    }

    public function clear(): void
    {
        $this->cache->delete(self::CACHE_KEY);
    }

    public function hasUnread(): bool
    {
        return count($this->all()) > 0;
    }
}
