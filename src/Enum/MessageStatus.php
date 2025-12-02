<?php

namespace App\Enum;

enum MessageStatus: string
{
    case CREATED = 'created';
    case PROCESSED = 'processed';
    case COMPLETED = 'completed';
    case REJECTED = 'rejected';
}
