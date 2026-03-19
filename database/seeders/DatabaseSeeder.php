<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
        ]);

        $categories = [
            ['name' => 'Restock Bahan Baku', 'icon' => '📦', 'is_restock' => true],
            ['name' => 'Sewa Tempat', 'icon' => '🏢', 'is_restock' => false],
            ['name' => 'Listrik & Air', 'icon' => '⚡', 'is_restock' => false],
            ['name' => 'Gaji Karyawan', 'icon' => '👥', 'is_restock' => false],
            ['name' => 'Bahan Kemasan', 'icon' => '🛍️', 'is_restock' => false],
            ['name' => 'Transport / Ongkir', 'icon' => '🛵', 'is_restock' => false],
            ['name' => 'Perawatan Alat', 'icon' => '🔧', 'is_restock' => false],
            ['name' => 'Marketing', 'icon' => '📢', 'is_restock' => false],
            ['name' => 'Lain-lain', 'icon' => '📝', 'is_restock' => false],
        ];

        foreach ($categories as $category) {
            \App\Models\ExpenseCategory::firstOrCreate(['name' => $category['name']], $category);
        }
    }
}
