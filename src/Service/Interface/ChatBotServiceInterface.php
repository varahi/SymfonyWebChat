<?php

namespace App\Service\Interface;

interface ChatBotServiceInterface
{
    public function processMessage(string $message): string;
}
