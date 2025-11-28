<?php

namespace App\Enums;

enum ActionType: string
{
    case CREATED = 'created';
    case UPDATED = 'updated';
    case DELETED = 'deleted';
    case RESTORED = 'restored';
    case STATUS_CHANGED = 'status_changed';
}
