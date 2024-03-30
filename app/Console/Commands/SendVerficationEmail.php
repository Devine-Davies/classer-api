<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\MailSenderController;
use App\Models\SchedulerJob;
use App\Models\User;

class SendVerficationEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-verfication-email {initiator}';

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
        $sendIds = $this->sendCode();
        $resendIds = $this->sendReminder();
        SchedulerJob::whereIn('id', array_merge($sendIds, $resendIds))->delete();
        return 0;
    }

    /**
     * Send code
     */
    protected function sendCode(): array
    {
        $userIds = array();
        $jobIds = array();
        $jobs = SchedulerJob::where('command', 'app:send-verfication-email')->get();

        foreach ($jobs as $job) {
            $jobIds[] = $job->id;
            $metadata = json_decode($job->metadata);
            $userIds[] = $metadata->user_id;
        }

        $users = User::whereIn('id', $userIds)->get();

        if ($users->count() > 0) {
            foreach ($users as $user) {
                MailSenderController::sendCode(array(
                    "title" => "Welcome " . $user->name,
                    "name" => $user->name,
                    "email" => $user->email,
                    "code" => $user->code,
                    "content"=> "We are excited to have you on board and look forward to showcasing the features and benefits of our product. We welcome any feedback!"
                ));
            }
        }

        return $jobIds;
    }
}
