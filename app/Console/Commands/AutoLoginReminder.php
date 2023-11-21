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
    protected $signature = 'app:auto-login-reminder {initiator}';

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
            $jobIds[] = $job->id;
            $metadata = json_decode($job->metadata);
            $userIds[] = $metadata->user_id;
        }

        $dormantUsers = User::whereIn('id', $userIds)->get()->filter(function ($user) {
            return $user->last_login_at == null;
        });

        if ($dormantUsers->count() > 0) {
            foreach ($dormantUsers as $user) {
                MailSenderController::SendAutoLoginReminder($user);
            }
        }

        SchedulerJob::whereIn('id', $jobIds)->delete();
        $initiator = $this->argument('initiator');
        $title = str_replace('{initiator}', $initiator, $this->signature);
        print_r($title . " completed at " . date('Y-m-d H:i:s') . "\n");
        return 0;
    }
}
