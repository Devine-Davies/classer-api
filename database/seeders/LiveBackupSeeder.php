<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\RecorderModel;
use App\Models\PersonalAccessToken;

/**
 * Seeder for live backup data
 * 
 * php artisan db:seed --class=LiveBackupSeeder
 */
class LiveBackupSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // // read json file
        // $json = file_get_contents('database\seeders\livebackup-data\u329348820_classer_api.json');
        $json = file_get_contents('./database/seeders/livebackup-data/21-08-2025_u329348820_classer_api.json');
        $data = json_decode($json, true);
        foreach ($data as $obj) {
            if ($obj['type'] == 'table') {
                if ($obj['name'] == 'users') {
                    $this->seedUsers($obj['data']);
                }

                // if ($obj['name'] == 'recorder') {
                //     $this->recorder($obj['data']);
                // }

                if ($obj['name'] == 'personal_access_tokens') {
                    $this->personalAccessTokens($obj['data']);
                }
            }
        }
    }

    /**
     * Seed the users
     */
    public function seedUsers($users) {
        foreach ($users as $user) {
            // This field is deprecated and should not be used, need to be removed
            unset($user['logged_in_at']);

            // if password is empty, set it to a default value
            if (empty($user['password'])) {
                $user['password'] = Hash::make(Str::random(32)); // hashed value for 'password'
            }

            User::create($user);
        }
    }

    /**
     * Seed the Recorder Model
     */
    public function recorder($data) {
        foreach ($data as $record) {
            RecorderModel::create($record);
        }
    }

    /**
     * Seed the Access Tokens
     */
    public function personalAccessTokens($data) {
        foreach ($data as $token) {
            $token['abilities'] = json_encode(['user']);
            PersonalAccessToken::create($token);
        }
    }
}


