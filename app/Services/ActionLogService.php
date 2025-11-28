<?php

namespace App\Services;

use App\Models\ActionLog;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;
use App\Enums\ActionType;
use Illuminate\Database\Eloquent\Model;

class ActionLogService
{
    public static function log(
        ActionType $actionType,
        Model $entity,
        $oldValues = null,
        $newValues = null,
        $description = null
    ) {
        return ActionLog::create([
            'user_id'    => Auth::id(),
            'action_type' => $actionType->value,
            'entity_type' => get_class($entity),
            'entity_id'   => $entity->id,
            'old_values'  => $oldValues ? json_encode($oldValues) : null,
            'new_values'  => $newValues ? json_encode($newValues) : null,
            'description' => $description,
            'ip_address'  => Request::ip(),
            'user_agent'  => Request::header('User-Agent'),
        ]);
    }
}
