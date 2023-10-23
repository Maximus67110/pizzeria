<?php

namespace App\Enum;

enum OrderStatus: string
{
    case CREATED = 'created';
    case PENDING = 'pending';
    case TREATED = 'treated';
    case CANCELED = 'canceled';
    case ERROR = 'error';
}