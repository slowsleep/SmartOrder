<?php

namespace App\Enums;

enum TableStatus: string
{
    case AVAILABLE = 'available'; // доступен
    case RESERVED = 'reserved'; // забронирован
    case UNAVAILABLE = 'unavailable'; // недоступен
}
