<?php

namespace App\Observers;

use App\Models\Product;

class ProductObserver extends BaseObserver
{
    protected function getEntityName(): string
    {
        return 'Product';
    }

    /**
     * Summary of getIdentifier
     * @param Product $entity
     * @return string
     */
    protected function getIdentifier($entity): string
    {
        return $entity->name;
    }
}
