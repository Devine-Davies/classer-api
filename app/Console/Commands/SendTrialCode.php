<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\MailSenderController;
use App\Models\SchedulerJob;
use App\Models\User;

class SendTrialCode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-trial-code {initiator}';

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
        $jobs = SchedulerJob::where('command', 'app:send-trial-code')->get();

        foreach ($jobs as $job) {
            $jobIds[] = $job->id;
            $metadata = json_decode($job->metadata);
            $userIds[] = $metadata->user_id;
        }

        $users = User::whereIn('id', $userIds)->get();

        if ($users->count() > 0) {
            foreach ($users as $user) {
                print_r("Top Sending trial code to " . $user->email . "\n");
                MailSenderController::sendTrialCode($user);
            }
        }

        SchedulerJob::whereIn('id', $jobIds)->delete();
        // $initiator = $this->argument('initiator');
        // $title = str_replace('{initiator}', $initiator, $this->signature);
        // print_r($title . " completed at " . date('Y-m-d H:i:s') . "\n");
        return 0;
    }
}
