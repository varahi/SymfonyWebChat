<?php

namespace App\Service;

class TopicService
{
    public function __construct(
        private readonly array $allowedTopics,
        private readonly array $forbiddenWords,
    ) {
        // $allowedTopics будет ['finance', 'tech', 'support']
    }

    public function isForbidden(string $text): bool
    {
        $text = mb_strtolower($text);

        // Если есть явно запрещённые слова — блокируем
        $forbiddenWords = [];
        foreach ($this->$forbiddenWords as $word) {
            if (str_contains($text, $word)) {
                return true;
            }
        }

        // Если ALLOWED_TOPICS содержит '*' — разрешаем всё остальное
        if (in_array('*', $this->allowedTopics)) {
            return false;
        }

        // Стандартная проверка (если нет '*')
        foreach ($this->allowedTopics as $topic) {
            if (str_contains($text, $topic)) {
                return false;
            }
        }

        return true; // Тема не разрешена
    }
}
