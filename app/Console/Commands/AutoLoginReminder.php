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
     * // $initiator = $this->argument('initiator');
     * // $title = str_replace('{initiator}', $initiator, $this->signature);
     * // print_r($title . " completed at " . date('Y-m-d H:i:s') . "\n");
     */
    public function handle()
    {
        $userIds = array();
        $jobIds = array();
        $jobs = SchedulerJob::where('command', 'app:auto-login-reminder')
            ->where('scheduled_for', '<', now()->subDays(3))
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
                $createdDate = $user->created_at;
                $days = $createdDate->diffInDays(now());
                $subject = $days <= 3 ? "Your invitation to join Classer" : "Do you have action camera recordings? Use Classer to relive your moments";
                MailSenderController::SendAutoLoginReminder($subject, array(
                    "title" => "Hey " . $user->name,
                    "name" => $user->name,
                    "email" => $user->email,
                    "code" => $user->code,
                    "content" => "We built Classer for people who enjoys using their action camera and they want to find an easy way to relive their memories. If you are one of them, we would love for you to try Classer!",
                ));
            }
        }

        SchedulerJob::whereIn('id', $jobIds)->delete();
        return 0;
    }
}
