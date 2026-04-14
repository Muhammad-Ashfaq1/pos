<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('SUPER_ADMIN_EMAIL', 'admin@oilchangepos.test');
        $password = env('SUPER_ADMIN_PASSWORD', 'password123');
        $name = env('SUPER_ADMIN_NAME', 'Super Admin');

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => $password,
                'tenant_id' => null,
                'role' => User::SUPER_ADMIN,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $user->assignPrimaryRole(User::SUPER_ADMIN);
    }
}
