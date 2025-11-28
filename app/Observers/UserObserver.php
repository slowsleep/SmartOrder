<?php

namespace App\Observers;

use App\Models\User;
use App\Services\ActionLogService;
use App\Enums\ActionType;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        ActionLogService::log(
            actionType: ActionType::CREATED,
            entity: $user,
            newValues: $user->toArray(),
            description: "User {$user->login} created"
        );
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        $new = $user->getChanges();
        $old = array_intersect_key($user->getOriginal(), $new);

        $keys = array_keys($new);

        ActionLogService::log(
            actionType: ActionType::UPDATED,
            entity: $user,
            oldValues: $old,
            newValues: $new,
            description: "User {$user->login} updated: " . implode(', ', $keys)
        );
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        ActionLogService::log(
            actionType: ActionType::DELETED,
            entity: $user,
            oldValues: $user->toArray(),
            description: "User {$user->login} deleted"
        );
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
