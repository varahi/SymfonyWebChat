<?php

namespace App\Enum;

enum ClientSessionStatus: string
{
    case OPENED = 'opened';
    case CLOSED = 'closed';
    case OPERATOR_STARTED = 'operator_started';
}
