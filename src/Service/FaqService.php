<?php

namespace App\Service;

use App\Repository\FaqRepository;
use Psr\Log\LoggerInterface;

class FaqService
{
    private array $faqData = [];

    public function __construct(
        private LoggerInterface $faqLogger,
        private FaqRepository $faqRepository
    ) {
        $this->loadData();
    }

    public function getPredefinedAnswer(string $question): ?string
    {
        $question = mb_strtolower(trim($question));
        $found = false;
        $matchedPattern = null;

        foreach ($this->faqData as $faqItem) {
            foreach ($faqItem['patterns'] as $pattern) {

                // Проверка корректности регулярного выражения
                if (!$this->isValidRegex($pattern)) {
                    $this->faqLogger->error('Некорректный regex в БД', [
                        'pattern' => $pattern,
                        'faq_id' => $faqItem['id'] ?? null,
                    ]);
                    continue; // пропускаем ломанный паттерн
                }

                if (preg_match($pattern, $question)) {
                    $this->faqLogger->info('Найдено совпадение', [
                        'question' => $question,
                        'pattern' => $pattern,
                        'answer' => $faqItem['answer'],
                    ]);

                    return $faqItem['answer'];
                }
            }
        }

        if (!$found) {
            $this->faqLogger->debug("Не найдено совпадений для вопроса: {$question}");
        }

        return null;
    }

    private function loadData(): void
    {
        $records = $this->faqRepository->findAll();

        foreach ($records as $item) {
            // $item->getPattern() может быть строкой или JSON
            $patterns = $item->getPattern();

            if (!is_array($patterns)) {
                // если это строка — превращаем в массив из 1 элемента
                $patterns = [$patterns];
            }

            $this->faqData[] = [
                'patterns' => $patterns,
                'answer' => $item->getAnswer(),
            ];
        }
    }

    private function isValidRegex(string $pattern): bool
    {
        set_error_handler(function () { return true; });
        preg_match($pattern, '');
        restore_error_handler();

        return preg_last_error() === PREG_NO_ERROR;
    }
}
