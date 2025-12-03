<?php

namespace App\Enums;

enum OrderItemStatus: string
{
    case PENDING = 'pending'; // ожидает приготовления
    case PREPARING = 'preparing'; // готовится
    case READY = 'ready'; // готов к подаче
    case IN_DELIVERY = 'in_delivery'; // взят официантом для подачи
    case SERVED = 'served'; // подано клиенту
    case CANCELLED = 'cancelled'; // отменен
}
