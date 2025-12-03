<?php

namespace App\Enum;

enum ClientSessionStatus: string
{
    case OPENED = 'opened';
    case CLOSED = 'closed';
}
