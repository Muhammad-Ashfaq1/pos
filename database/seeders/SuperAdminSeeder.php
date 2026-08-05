<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = (string) env('SUPER_ADMIN_EMAIL', 'superadmin@obtainsolutions.com');
        $name = (string) env('SUPER_ADMIN_NAME', 'Super Admin');
        $password = (string) env('SUPER_ADMIN_PASSWORD', 'password');

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
