<?php

namespace App\Service;

use App\Service\Interface\ChatBotServiceInterface;

class ChatBotService implements ChatBotServiceInterface
{
    public function processMessage(string $message): string
    {
        // Здесь ваша логика работы бота
        return 'Ответ на: '.$message;
    }
}
