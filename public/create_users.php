<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$users = [
    ['name' => 'Owner Account', 'email' => 'owner@example.com', 'role' => 'owner', 'password' => '12345678'],
    ['name' => 'Kasir Utama', 'email' => 'kasir@example.com', 'role' => 'kasir', 'password' => '12345678'],
    ['name' => 'Kitchen Staff', 'email' => 'kitchen@example.com', 'role' => 'kitchen', 'password' => '12345678']
];

foreach ($users as $u) {
    User::updateOrCreate(
        ['email' => $u['email']],
        [
            'name' => $u['name'],
            'role' => $u['role'],
            'password' => Hash::make($u['password']),
            'email_verified_at' => now(),
        ]
    );
}

echo "Users created successfully!\n";
