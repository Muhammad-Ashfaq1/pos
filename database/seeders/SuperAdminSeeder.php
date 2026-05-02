<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'superadmin@pos.com'],
            [
                'name' => 'Super Admin',
                'password' => 'password',
                'tenant_id' => null,
                'role' => User::SUPER_ADMIN,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $user->assignPrimaryRole(User::SUPER_ADMIN);
    }
}
