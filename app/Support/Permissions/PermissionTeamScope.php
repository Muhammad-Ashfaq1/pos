<?php

namespace App\Support\Permissions;

class PermissionTeamScope
{
    public static function for(int $teamId, callable $callback): mixed
    {
        setPermissionsTeamId($teamId);

        try {
            return $callback();
        } finally {
            setPermissionsTeamId(null);
        }
    }
}
