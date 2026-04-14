<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $teamKey = $columnNames['team_foreign_key'] ?? 'team_id';
        $pivotRole = $columnNames['role_pivot_key'] ?? 'role_id';
        $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';

        if (! Schema::hasColumn($tableNames['roles'], $teamKey)) {
            Schema::table($tableNames['roles'], function (Blueprint $table) use ($teamKey) {
                $table->unsignedBigInteger($teamKey)->nullable()->after('id');
                $table->index($teamKey, 'roles_team_foreign_key_index');
            });

            Schema::table($tableNames['roles'], function (Blueprint $table) use ($teamKey) {
                $table->dropUnique('roles_name_guard_name_unique');
                $table->unique([$teamKey, 'name', 'guard_name']);
            });
        }

        if (! Schema::hasColumn($tableNames['model_has_permissions'], $teamKey)) {
            Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($teamKey, $pivotPermission) {
                $table->unsignedBigInteger($teamKey)->default(0)->after($pivotPermission);
                $table->index($teamKey, 'model_has_permissions_team_foreign_key_index');
                $table->index($pivotPermission, 'model_has_permissions_permission_id_index');
            });
        }

        if ($this->needsTeamPrimary(
            $tableNames['model_has_permissions'],
            [$teamKey, $pivotPermission, $columnNames['model_morph_key'], 'model_type']
        )) {
            $this->backfillTeamColumn($tableNames['model_has_permissions'], $teamKey);

            Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($pivotPermission) {
                $table->dropForeign([$pivotPermission]);
            });

            Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($teamKey, $pivotPermission, $columnNames) {
                $table->dropPrimary('model_has_permissions_permission_model_type_primary');
                $table->primary([$teamKey, $pivotPermission, $columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_permission_model_type_primary');
            });

            Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames, $pivotPermission) {
                $table->foreign($pivotPermission)
                    ->references('id')
                    ->on($tableNames['permissions'])
                    ->cascadeOnDelete();
            });
        }

        if (! Schema::hasColumn($tableNames['model_has_roles'], $teamKey)) {
            Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($teamKey, $pivotRole) {
                $table->unsignedBigInteger($teamKey)->default(0)->after($pivotRole);
                $table->index($teamKey, 'model_has_roles_team_foreign_key_index');
                $table->index($pivotRole, 'model_has_roles_role_id_index');
            });
        }

        if ($this->needsTeamPrimary(
            $tableNames['model_has_roles'],
            [$teamKey, $pivotRole, $columnNames['model_morph_key'], 'model_type']
        )) {
            $this->backfillTeamColumn($tableNames['model_has_roles'], $teamKey);

            Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($pivotRole) {
                $table->dropForeign([$pivotRole]);
            });

            Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($teamKey, $pivotRole, $columnNames) {
                $table->dropPrimary('model_has_roles_role_model_type_primary');
                $table->primary([$teamKey, $pivotRole, $columnNames['model_morph_key'], 'model_type'], 'model_has_roles_role_model_type_primary');
            });

            Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames, $pivotRole) {
                $table->foreign($pivotRole)
                    ->references('id')
                    ->on($tableNames['roles'])
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        //
    }

    private function backfillTeamColumn(string $table, string $teamKey): void
    {
        if (! $this->isMySql()) {
            return;
        }

        DB::statement("
            UPDATE {$table}
            LEFT JOIN users
                ON {$table}.model_type = '".addslashes(User::class)."'
                AND {$table}.model_id = users.id
            SET {$table}.{$teamKey} = COALESCE(users.tenant_id, 0)
        ");
    }

    private function isMySql(): bool
    {
        return in_array(DB::getDriverName(), ['mysql', 'mariadb'], true);
    }

    private function needsTeamPrimary(string $table, array $expectedColumns): bool
    {
        if (! $this->isMySql()) {
            return false;
        }

        $columns = collect(DB::select("SHOW INDEX FROM {$table} WHERE Key_name = 'PRIMARY'"))
            ->sortBy('Seq_in_index')
            ->pluck('Column_name')
            ->values()
            ->all();

        return $columns !== $expectedColumns;
    }
};
