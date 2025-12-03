<?php

namespace App\Observers;

use App\Models\User;

class UserObserver extends BaseObserver
{
    protected function getEntityName(): string
    {
        return 'User';
    }

    /**
     * Summary of getIdentifier
     * @param User $entity
     * @return string
     */
    protected function getIdentifier($entity): string
    {
        return $entity->login;
    }
}
