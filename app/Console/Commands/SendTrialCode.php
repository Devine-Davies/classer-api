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
    protected $signature = 'app:send-code {initiator}';

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
        print_r(Date('Y-m-d H:i:s'));
        return 0;
    }

    /**
     * Send code
     */
    protected function sendCode(): array
    {
        $userIds = array();
        $jobIds = array();
        $jobs = SchedulerJob::where('command', 'app:send-code')->get();

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

    /**
     * Resend code
     */
    protected function sendReminder(): array
    {
        $userIds = array();
        $jobIds = array();
        $jobs = SchedulerJob::where('command', 'app:resend-code')->get();

        foreach ($jobs as $job) {
            $jobIds[] = $job->id;
            $metadata = json_decode($job->metadata);
            $userIds[] = $metadata->user_id;
        }

        $users = User::whereIn('id', $userIds)->get();

        if ($users->count() > 0) {
            foreach ($users as $user) {
                MailSenderController::resendCode(array(
                    "title" => "Code Reminder",
                    "name" => $user->name,
                    "email" => $user->email,
                    "code" => $user->code,
                    "content"=> "We understand that it's easy to misplace or forget important details. You've requested a reminder for your access code. If you have any questions or need assistance, we are here to help. Feel free to reach out to us at info@classermedia.com."
                ));
            }
        }

        return $jobIds;
    }
}
