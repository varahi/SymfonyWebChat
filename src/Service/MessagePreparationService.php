<?php

namespace App\Service;

class MessagePreparationService
{
    public function prepare(string $userMessage): array
    {
        return [['role' => 'operator', 'text' => '<div class="system-note">✅ Сообщение отправлено оператору.</div>']];
    }
}
