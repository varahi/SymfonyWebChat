<?php

namespace App\Service;

use Psr\Log\LoggerInterface;

class FaqService
{
    public function __construct(
        private LoggerInterface $faqLogger,
        private string $faq
    ) {
    }

    public function getPredefinedAnswer(string $question): ?string
    {
        $question = mb_strtolower(trim($question));
        $found = false;
        $matchedPattern = null;

//        foreach ($this->faq as $faqItem) {
//            foreach ($faqItem['patterns'] as $pattern) {
//                if (preg_match($pattern, $question)) {
//                    // Логируем найденное совпадение
//                    $matchedPattern = $pattern;
//                    $this->faqLogger->info('Найдено совпадение', [
//                        'question' => $question,
//                        'pattern' => $pattern,
//                        'answer' => $faqItem['answer'],
//                    ]);
//
//                    return $faqItem['answer'];
//                }
//            }
//        }

        if (!$found) {
            $this->faqLogger->debug("Не найдено совпадений для вопроса: {$question}");
        }

        return null;
    }
}
