<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'praya@smaesh.com'],
            ['name' => 'Praya', 'password' => bcrypt('password'), 'role' => 'owner']
        );

        User::updateOrCreate(
            ['email' => 'raisha@smaesh.com'],
            ['name' => 'Putra', 'password' => bcrypt('password'), 'role' => 'cashier']
        );

        User::updateOrCreate(
            ['email' => 'arivia@smaesh.com'],
            ['name' => 'Wibowo', 'password' => bcrypt('password'), 'role' => 'kitchen']
        );
    }
}
