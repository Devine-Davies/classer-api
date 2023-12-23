<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\SubscriptionType;
use App\Models\Subscription;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(10)->create();

        foreach ([[
            'limit_short_count' => 50,
            'limit_short_duration' => 30, // 30 seconds
            'limit_short_size' => 10485760 // 10MB
        ], [
            'limit_short_count' => 100,
            'limit_short_duration' => 60, // 1 minute
            'limit_short_size' => 52428800 // 50MB
        ], [
            'limit_short_count' => 200,
            'limit_short_duration' => 30,
            'limit_short_size' => 104857600 // 100MB
        ]] as $type) {
            SubscriptionType::factory()->create($type);
        }

        $subscriptionType = SubscriptionType::all()->random();
        $mainUser = $this->shortUuid();

        User::factory()->create([
            'uid' => $mainUser,
            'name' => 'Rhys Devine-Davies',
            'email' => 'rdd@example.com',
            'password' => bcrypt('password'),
            'code' => Str::random(6)
        ]);

        Subscription::factory()->create([
            'uid' => $mainUser,
            'sub_type' => $subscriptionType->code,
        ]);
    }

    private function shortUuid(): string
    {
        $uuid = Str::uuid();
        $uuid = substr($uuid, 0, strrpos($uuid, '-'));
        return $uuid;
    }
}
