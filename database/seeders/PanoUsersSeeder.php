<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PanoUsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'admin', 'email' => 'admin@pano.test', 'password' => 'admin123', 'role' => 'admin'],
            ['name' => 'demo', 'email' => 'demo@pano.test', 'password' => 'demo123', 'role' => 'user'],
            ['name' => 'client', 'email' => 'client@pano.test', 'password' => 'client123', 'role' => 'user'],
            ['name' => 'viewer', 'email' => 'viewer@pano.test', 'password' => 'viewer123', 'role' => 'user'],
            ['name' => 'pano', 'email' => 'pano@pano.test', 'password' => 'pano123', 'role' => 'user'],
        ];

        foreach ($users as $u) {
            User::updateOrCreate(
                ['email' => $u['email']],
                ['name' => $u['name'], 'password' => Hash::make($u['password']), 'email_verified_at' => now(), 'role' => $u['role'], 'is_active' => true]
            );
        }
    }
}
