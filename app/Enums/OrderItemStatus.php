<?php

namespace App\Enums;

enum OrderItemStatus: string
{
    case PENDING = 'pending'; // ожидает приготовления
    case PREPARING = 'preparing'; // готовится
    case READY = 'ready'; // готов к подаче
    case SERVED = 'served'; // подано клиенту
    case CANCELLED = 'cancelled'; // отменен
}
