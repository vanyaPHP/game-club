<?php

namespace App\Enum;

enum BookingStatusEnum: string
{
    case PENDING = 'ожидается ответ';
    case APPROVED = 'одобрено';
    case DISAPPROVED = 'отказано';
    case CLOSED = 'дата брони вышла';
}
