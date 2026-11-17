<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->firstOrNew(['email' => config('demo.admin.email')]);

        $admin->forceFill([
            'name' => config('demo.admin.name'),
            'password' => config('demo.admin.password'),
            'is_admin' => true,
            'email_verified_at' => now(),
        ])->save();
    }
}
