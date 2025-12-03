<?php

namespace App\Observers;

use App\Models\OrderItem;

class OrderItemObserver extends BaseObserver
{
    protected function getEntityName(): string
    {
        return 'OrderItem';
    }

    /**
     * Summary of getIdentifier
     * @param OrderItem $entity
     * @return string
     */
    protected function getIdentifier($entity): string
    {
        return $entity->product->name;
    }
}
