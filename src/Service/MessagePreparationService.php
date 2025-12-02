<?php

namespace App\Service;

use App\Enum\MessageRole;
use App\Service\Product\ProductService;

class MessagePreparationService
{
    public function __construct(
        private readonly FaqService $faqService,
        private readonly HistoryService $historyService,
        private readonly ProductService $productService,
        private readonly SessionService $sessionService,
        private readonly OperatorChatService $chatService
    ) {
    }

    public function prepare(string $userMessage): array
    {
        $session = $this->chatService->getOrCreateClientSession();

        // return [['role' => 'operator', 'text' => '<div class="system-note">✅ Сообщение отправлено оператору.</div>']];
        $userId = $this->sessionService->getUserId();

        // 0. Если сессия с оператором уже активна — все запросы идут оператору
        if ($this->chatService->isOperatorSession($session)) {
            $this->chatService->storeClientMessage($session, $userMessage);

            return [[
                'role' => MessageRole::OPERATOR->value,
                'text' => '<div class="system-note">✅ Сообщение отправлено оператору.</div>',
            ]];
        }

        // 1. Проверка FAQ
        if ($answer = $this->faqService->getPredefinedAnswer($userMessage)) {
            return [[
                'role' => MessageRole::ASSISTANT->value,
                'text' => '<div class="products-card">'.$answer.'</div>',
            ]];
        }

        // 2. Проверка триггерных фраз // Вызов оператора
        if ($this->shouldTransferToOperator($userMessage, $userId)) {
            $this->chatService->storeClientMessage($session, $userMessage);

            return [[
                'role' => MessageRole::OPERATOR->value,
                'text' => '<div class="system-note">📨 Запрос передан оператору — ожидайте ответ.</div>',
            ]];
        }

        // 3. Отображаем новинки
        if ($this->isNewProductQuestion($userMessage)) {
            $products = $this->productService->getNewRandomProducts();
            $answer = $this->productService->generateProductAnswer(
                $userMessage,
                $products,
                'Наши новинки'
            );

            return [[
                'role' => MessageRole::ASSISTANT->value,
                'text' => $answer,
            ]];
        }

        // 4. Берем данные из БД
        if ($products = $this->productService->getProductsByQuery($userMessage)) {
            $answer = $this->productService->generateProductAnswer(
                $userMessage,
                $products,
                'Наши товары'
            );

            return [[
                'role' => MessageRole::ASSISTANT->value,
                'text' => $answer,
            ]];
        }

        // 5. Вызываем оператора если нет подходящих ответов
        $this->chatService->storeClientMessage($session, $userMessage);

        return [[
            'role' => MessageRole::OPERATOR->value,
            'text' => '<div class="system-note">❗ Ответ не найден — запрос передан оператору.</div>',
        ]];
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
