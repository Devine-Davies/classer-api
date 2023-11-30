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
                MailSenderController::sendTrialCode(array(
                    "title" => "Welcome " . $user->name,
                    "name" => $user->name,
                    "email" => $user->email,
                    "code" => $user->code,
                    "content"=> "We are excited to have you on board and look forward to showcasing the features and benefits of our product. During this trial period, you will have the opportunity to explore the various functionalities and experience firsthand. We welcome any feedback!"
                ));
            }
        }

        SchedulerJob::whereIn('id', $jobIds)->delete();
        return 0;
    }
}
