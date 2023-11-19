<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\User::factory(10)->create();
        \App\Models\User::factory()->create([
            'name' => 'Rhys Devine-Davies',
            'email' => 'rdd@example.com',
            'password' => bcrypt('password'),
            'code' => Str::random(6)
        ]);
    }
}
