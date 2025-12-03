<?php

namespace App\Observers;

class OrderItemObserver extends BaseObserver
{
    protected function getEntityName(): string
    {
        return 'OrderItem';
    }

    protected function getIdentifier($entity): string
    {
        return $entity->product->name;
    }
}
