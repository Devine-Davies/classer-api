<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use App\Http\Controllers\MailSenderController;
use App\Models\SchedulerJob;
use App\Models\User;

class Daily extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'app:daily {initiator}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'This cmd is designed to execute immediate jobs';

    /**
     * Execute the console command.
     */
    public function handle(Schedule $schedule)
    {
        $verifyReminder = 'daily:email-account-verify-reminder';
        $loginReminder = 'daily:email-account-login-reminder';
        $reviewReminder = 'daily:email-review-reminder';

        // where command is either of the two and scheduled_for is today
        $jobs = SchedulerJob::whereIn('command', [
            $verifyReminder,
            $loginReminder,
            $reviewReminder
        ])->whereDate('scheduled_for', now()->toDateString())->get();

        $groups = $jobs->groupBy('command');

        if($groups->get($verifyReminder)) {
            $this->verifyReminder($groups->get($verifyReminder));
        }

        if($groups->get($loginReminder)) {
            $this->loginReminder($groups->get($loginReminder));
        }

        if($groups->get($reviewReminder)) {
            $this->reviewReminder($groups->get($reviewReminder));
        }

        $jobIds = $jobs->pluck('id')->toArray();
        SchedulerJob::whereIn('id', $jobIds)->delete();
    }

    /**
     * Verify account emails
     */
    protected function verifyReminder($jobs)
    {
        $userIds = array();
        foreach ($jobs as $job) {
            $metadata = json_decode($job->metadata);
            $userIds[] = $metadata->user_id;
        }

        $users = User::whereIn('id', $userIds)->get();
        if ($users->count() > 0) {
            foreach ($users as $user) {
                if($user->account_status == 0) {
                    MailSenderController::verifyAccount($user->email, $user);
                }
            }
        }
    }

    /**
     * Account Verified Emails
     */
    protected function loginReminder($jobs)
    {
        $userIds = array();
        foreach ($jobs as $job) {
            $metadata = json_decode($job->metadata);
            $userIds[] = $metadata->user_id;
        }

        $users = User::whereIn('id', $userIds)->get();
        if ($users->count() > 0) {
            foreach ($users as $user) {
                if($user->logged_in_at == null) {
                    MailSenderController::loginReminder($user->email, $user);
                }
            }
        }
    }

    /**
     * Review Reminder Emails
     */
    protected function reviewReminder($jobs)
    {
        $userIds = array();
        foreach ($jobs as $job) {
            $metadata = json_decode($job->metadata);
            $userIds[] = $metadata->user_id;
        }

        $users = User::whereIn('id', $userIds)->get();
        if ($users->count() > 0) {
            foreach ($users as $user) {
                MailSenderController::reviewReminder($user->email, $user);
            }
        }
    }
}
