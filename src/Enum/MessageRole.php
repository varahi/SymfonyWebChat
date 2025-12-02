<?php

namespace App\Enum;

enum MessageRole: string
{
    case CLIENT = 'client';
    case OPERATOR = 'operator';
    case ASSISTANT = 'assistant';
    case SYSTEM = 'system';
}
