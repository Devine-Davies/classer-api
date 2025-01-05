<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\SchedulerModel;
use App\Models\PersonalAccessToken;

class LiveBackupSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // // read json file
        // $json = file_get_contents('database\seeders\livebackup-data\u329348820_classer_api.json');
        $json = file_get_contents('./database/seeders/livebackup-data/u329348820_classer_api.json');
        $data = json_decode($json, true);

        foreach ($data as $obj) {
            if ($obj['type'] == 'table') {
                if ($obj['name'] == 'users') {
                    $this->seedUsers($obj['data']);
                }

                if ($obj['name'] == 'scheduler_jobs') {
                    $this->seedScheduler($obj['data']);
                }

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
            User::create($user);
        }
    }

    /**
     * Seed the Scheduler
     */
    public function seedScheduler($data) {
        foreach ($data as $job) {
            SchedulerModel::create($job);
        }
    }

    /**
     * Seed the Access Tokens
     */
    public function personalAccessTokens($data) {
        foreach ($data as $token) {
            PersonalAccessToken::create($token);
        }
    }

}


