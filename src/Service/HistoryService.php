<?php

namespace App\Service;

class HistoryService
{
    public function __construct(
        private readonly string $historyConfig,
        private readonly string $historyStorage
    )
    {
    }

    public function updateHistory(string $userId, string $role, string $text): void
    {
        $historyConfig = [
            'max_history' => 5,
        ];

        if (!isset($this->history[$userId])) {
            $this->history[$userId] = [];
        }

        file_put_contents('updateHistory.log', 'Role: '.$role."\n", FILE_APPEND);

        if ('operator' == $role) {
            $this->history[$userId][] = [
                'role' => $role,
                'text' => $text,
                'time' => date('H:i:s'),
            ];

            // Оставляем только последние $maxHistory сообщений для этого пользователя
            if (count($this->history[$userId]) > $historyConfig['max_history']) {
                array_shift($this->history[$userId]);
            }

            $this->persist();
        }
    }

    public function getHistory(string $userId): array
    {
        return $this->history[$userId] ?? [];
    }

    public function clearHistory(string $userId): void
    {
        unset($this->history[$userId]);
        $this->persist();
    }

    private function persist(): void
    {
        file_put_contents($this->historyStorage, json_encode($this->history, JSON_UNESCAPED_UNICODE));
    }

    public function isOperatorSession(string $userId): bool
    {
        $history = $this->getHistory($userId);
        foreach (array_reverse($history) as $item) {
            if ('operator' === $item['role'] && !empty($item['text'])) {
                return true;
            }
            // Можно добавить условие: если бот дал нормальный ответ, операторская сессия закрывается
            if ('assistant' === $item['role']) {
                break;
            }
        }

        return false;
    }
}
