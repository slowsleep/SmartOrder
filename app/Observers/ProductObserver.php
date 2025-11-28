<?php

namespace App\Observers;

use App\Models\Product;
use App\Services\ActionLogService;
use App\Enums\ActionType;

class ProductObserver
{
    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        ActionLogService::log(
            actionType: ActionType::CREATED,
            entity: $product,
            newValues: $product->toArray(),
            description: "Product {$product->name} created"
        );
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        $new = $product->getChanges();
        $old = array_intersect_key($product->getOriginal(), $new);

        $keys = array_keys($new);

        ActionLogService::log(
            actionType: ActionType::UPDATED,
            entity: $product,
            oldValues: $old,
            newValues: $new,
            description: "Product {$product->name} updated: " . implode(', ', $keys)
        );
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        ActionLogService::log(
            actionType: ActionType::DELETED,
            entity: $product,
            oldValues: $product->toArray(),
            description: "Product {$product->name} deleted"
        );
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "force deleted" event.
     */
    public function forceDeleted(Product $product): void
    {
        //
    }
}
