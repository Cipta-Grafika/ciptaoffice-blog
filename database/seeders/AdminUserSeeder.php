<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $password = env('CMS_ADMIN_PASSWORD');
        if (blank($password)) {
            return;
        }
        User::query()->updateOrCreate(['email' => env('CMS_ADMIN_EMAIL', 'admin@ciptaoffice.test')], [
            'name' => env('CMS_ADMIN_NAME', 'Administrator CiptaOffice'), 'password' => $password, 'role' => UserRole::Admin, 'is_active' => true, 'email_verified_at' => now(),
        ]);
    }
}
