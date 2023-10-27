<?php

namespace App\Enum;

enum OrderStatus: string
{
    case CREATED = 'created';
    case PENDING = 'pending';
    case PAYED = 'payed';
    case COMPLETED = 'completed';
    case CANCELED = 'canceled';
    case ERROR = 'error';
}