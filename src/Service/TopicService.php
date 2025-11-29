<?php

namespace App\Service;

class TopicService
{
    private string $allowedTopics;
    private array $forbiddenWords;

    public function __construct(string $configFilePath)
    {
        $config = include $configFilePath;

        $this->allowedTopics = $config['allowed_topics'] ?? '*';
        $forbiddenWordsString = $config['forbidden_words'] ?? 'политика,религия,насилие,18+,наркотики,экстремизм';

        // Преобразуем строку в массив
        $this->forbiddenWords = array_map('trim', explode(',', $forbiddenWordsString));
    }

    public function isForbidden(string $text): bool
    {
        $text = mb_strtolower($text);

        // Если есть явно запрещённые слова — блокируем
        foreach ($this->forbiddenWords as $word) {
            if (str_contains($text, mb_strtolower($word))) {
                return true;
            }
        }

        // Если ALLOWED_TOPICS содержит '*' — разрешаем всё остальное
        if ('*' === $this->allowedTopics) {
            return false;
        }

        // Преобразуем строку разрешённых тем в массив
        $allowedTopics = array_map('trim', explode(',', $this->allowedTopics));

        // Если в массиве есть '*' — разрешаем всё
        if (in_array('*', $allowedTopics)) {
            return false;
        }

        // Стандартная проверка (если нет '*')
        foreach ($allowedTopics as $topic) {
            if (str_contains($text, mb_strtolower($topic))) {
                return false;
            }
        }

        return true; // Тема не разрешена
    }
}
