<?php

namespace App\Enum;

enum ClientSessionStatus: string
{
    case OPENED = 'opened';
    case CLOSED = 'closed';
    case OPERATOR_STARTED = 'operator_started';

    public function label(): string
    {
        return match ($this) {
            self::OPENED => 'status.opened',
            self::OPERATOR_STARTED => 'status.operator_started',
            self::CLOSED => 'status.closed',
        };
    }
}
