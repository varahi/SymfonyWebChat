<?php

namespace App\Service;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class OperatorNotificationService
{
    private const CACHE_KEY = 'operator_notifications';

    public function __construct(private CacheInterface $cache)
    {
    }

    public function add(string $text): void
    {
        $notifications = $this->all();
        $notifications[] = [
            'text' => $text,
            'ts' => time(),
        ];

        // перезаписываем кэш
        $this->cache->delete(self::CACHE_KEY);
        $this->cache->get(self::CACHE_KEY, fn (ItemInterface $item) => $this->store($item, $notifications));
    }

    public function removeByTs(int $ts): void
    {
        $notifications = $this->all();
        $notifications = array_filter($notifications, fn ($n) => $n['ts'] !== $ts);

        // перезаписываем кэш
        $this->cache->delete(self::CACHE_KEY);
        $this->cache->get(self::CACHE_KEY, fn (ItemInterface $item) => $this->store($item, $notifications));
    }

    public function all(): array
    {
        return $this->cache->get(self::CACHE_KEY, fn (ItemInterface $item) => $this->store($item, []));
    }

    public function clear(): void
    {
        $this->cache->delete(self::CACHE_KEY);
    }

    public function hasUnread(): bool
    {
        return count($this->all()) > 0;
    }

    private function store(ItemInterface $item, array $notifications): array
    {
        $item->expiresAfter(3600);

        return array_values($notifications); // пересчет индексов
    }
}
