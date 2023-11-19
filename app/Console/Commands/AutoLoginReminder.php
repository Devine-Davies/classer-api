<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Http\Controllers\MailSenderController;
use App\Models\SchedulerJob;

class AutoLoginReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto-login-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userIds = array();
        $jobIds = array();

        $jobs = SchedulerJob::where('command', 'app:auto-login-reminder')
            ->where('scheduled_for', '<', now())
            ->get();

        foreach ($jobs as $job) {
            print_r("----- Processing job " . $job->id . "\n");
            $jobIds[] = $job->id;
            $metadata = json_decode($job->metadata);
            $userIds[] = $metadata->user_id;
        }

        $dormantUsers = User::whereIn('id', $userIds)->get()->filter(function ($user) {
            return $user->last_login_at == null;
        });

        print_r("----- Dormant users: " . $dormantUsers->count() . "\n");

        if ($dormantUsers->count() > 0) {
            foreach ($dormantUsers as $user) {
                MailSenderController::SendAutoLoginReminder($user);
            }
        }

        SchedulerJob::whereIn('id', $jobIds)->delete();
        return 0;
    }
}
