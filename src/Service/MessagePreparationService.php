<?php

namespace App\Service;

use App\Service\Product\ProductService;

class MessagePreparationService
{
    public function __construct(
        private readonly FaqService $faqService,
        private readonly HistoryService $historyService,
        private readonly ProductService $productService,
        private readonly SessionService $sessionService
    ) {
    }

    public function prepare(string $userMessage): array
    {
        // return [['role' => 'operator', 'text' => '<div class="system-note">✅ Сообщение отправлено оператору.</div>']];
        $userId = $this->sessionService->getUserId();

        // 0. Если сессия с оператором уже активна — все запросы идут оператору
        //        if ($this->historyService->isOperatorSession($userId)) {
        //            $this->historyService->updateHistory($userId, 'operator', $userMessage);
        //            // ToDo: implement store to database
        //            return [['role' => 'operator', 'text' => '<div class="system-note">✅ Сообщение отправлено оператору.</div>']];
        //        }

        // 1. Проверка FAQ
        if ($answer = $this->faqService->getPredefinedAnswer($userMessage)) {
            return [['role' => 'assistant', 'text' => '<div class="products-card"> '.$answer.'</div>']]; // ← Только готовый ответ
        }

        // 2. Проверка триггерных фраз // Вызов оператора
        //        if ($this->shouldTransferToOperator($userMessage, $userId)) {
        //            $this->historyService->updateHistory($userId, 'operator', $userMessage);
        //            // ToDo: implement store to database
        //            return [['role' => 'operator', 'text' => '<div class="system-note">✅ Запрос передан оператору — вы получите ответ в чате.</div>']];
        //        }

        // 3. Отображаем новинки
        //        if ($this->isNewProductQuestion($userMessage)) {
        //            $products = $this->productService->getNewRandomProducts();
        //            $answer = $this->productService->generateProductAnswer($userMessage, $products, 'Наши новинки');
        //            return [['role' => 'assistant', 'text' => $answer]];
        //        }

        // 4. Берем данные из БД
        //        if ($products = $this->productService->getProductsByQuery($userMessage)) {
        //            $answer = $this->productService->generateProductAnswer($userMessage, $products, 'Наши товары');
        //            return [['role' => 'assistant', 'text' => $answer]];
        //        }

        // 5. Вызываем оператора если нет подходящих ответов
        // $this->historyService->updateHistory($userId, 'operator', $userMessage);
        // ToDo: implement store to database
        return [['role' => 'operator', 'text' => '<div class="system-note">✅ Ответ на вопрос не найден, передаем оператору.</div>']];
    }

    private function shouldTransferToOperator(string $userMessage, string $userId): bool
    {
        $triggerPhrases = [
            'оператор', 'человек', 'менеджер', 'позовите', 'соедините с',
            'не понимаю', 'помогите', 'ваш ответ не помог', 'живой',
        ];

        foreach ($triggerPhrases as $phrase) {
            if (false !== stripos($userMessage, $phrase)) {
                return true;
            }
        }

        // Если бот уже несколько раз не смог помочь
        $history = $this->historyService->getHistory($userId);
        $botResponses = array_filter($history, fn ($item) => 'assistant' === $item['role']);
        $userQuestions = array_filter($history, fn ($item) => 'user' === $item['role']);

        if (count($userQuestions) >= 3 && count($botResponses) >= 2) {
            return true; // Передаем оператору после 3 вопросов
        }

        return false;
    }

    private function isNewProductQuestion(string $question): bool
    {
        $question = mb_strtolower(trim($question));

        $patterns = [
            '/новинк[иау]?/ui',
            '/новые товары/ui',
            '/новый товар/ui',
            '/что новенького/ui',
            '/последние поступления/ui',
            '/недавно поступившие/ui',
            '/свежие товары/ui',
            '/новое в ассортименте/ui',
            '/наши новинки/ui',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $question)) {
                // file_put_contents('new.log', "Pattern matched: " . $pattern . " for question: " . $question . "\n", FILE_APPEND);
                return true;
            }
        }

        return false;
    }
}
