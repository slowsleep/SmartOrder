<?php

namespace App\Observers;

use App\Services\ActionLogService;
use App\Enums\ActionType;
use Illuminate\Database\Eloquent\Model;

abstract class BaseObserver
{
    abstract protected function getEntityName(): string;
    abstract protected function getIdentifier(Model $entity): string;

    /**
     * Handle the "created" event.
     */
    public function created(Model $entity): void
    {
        ActionLogService::log(
            actionType: ActionType::CREATED,
            entity: $entity,
            newValues: $entity->toArray(),
            description: "{$this->getEntityName()} {$this->getIdentifier($entity)} created"
        );
    }

    /**
     * Handle the "updated" event.
     */
    public function updated(Model $entity): void
    {
        $new = $entity->getChanges();
        $old = array_intersect_key($entity->getOriginal(), $new);

        $keys = array_keys($new);

        ActionLogService::log(
            actionType: ActionType::UPDATED,
            entity: $entity,
            oldValues: $old,
            newValues: $new,
            description: "{$this->getEntityName()} {$this->getIdentifier($entity)} updated: " . implode(', ', $keys)
        );
    }

    /**
     * Handle the "deleted" event.
     */
    public function deleted(Model $entity): void
    {
        ActionLogService::log(
            actionType: ActionType::DELETED,
            entity: $entity,
            oldValues: $entity->toArray(),
            description: "{$this->getEntityName()} {$this->getIdentifier($entity)} deleted"
        );
    }

    /**
     * Handle the "restored" event.
     */
    public function restored(Model $entity): void
    {
        //
    }

    /**
     * Handle the "force deleted" event.
     */
    public function forceDeleted(Model $entity): void
    {
        //
    }
}
