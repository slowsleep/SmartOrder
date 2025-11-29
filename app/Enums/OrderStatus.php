<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending'; // ожидает подтверждения
    case CONFIRMED = 'confirmed'; // подтвержден
    case PREPARING = 'preparing'; // готовится (хотя бы один item)
    case READY = 'ready'; // все готово
    case COMPLETED = 'completed'; // завершен (все подано)
    case CANCELLED = 'cancelled'; // отменен
}
