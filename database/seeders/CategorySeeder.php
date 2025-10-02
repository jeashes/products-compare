<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $names = [
            'smartphones' => 'Smartphones',
            'laptops' => 'Laptops',
            'earbuds' => 'Earbuds',
            'robot-vacuums' => 'Robot Vacuums',
            'security-cameras' => 'Security Cameras',
        ];

        $categories = [];

        foreach ($names as $key => $value) {
            $categories[] = [
                'name' => $value,
                'slug' => $key,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('categories')->insertOrIgnore($categories);
    }
}
