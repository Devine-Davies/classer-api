<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\MailSenderController;
use App\Models\SchedulerJob;
use App\Models\User;

class AutoEmail extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'app:auto-email {initiator}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'This cmd is designed to send immediate emails';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $verifyAccountIds = $this->verifyAccount();
        $accountVerifiedIds = $this->accountVerified();
        $passwordResetIds = $this->passwordReset();

        SchedulerJob::whereIn('id', array_merge(
            $verifyAccountIds,
            $accountVerifiedIds,
            $passwordResetIds
        ))->delete();

        return 0;
    }

    /**
     * Verify account emails
     */
    protected function verifyAccount(): array
    {
        $userIds = array();
        $jobIds = array();
        $jobs = SchedulerJob::where('command', 'app:email-verify-account')->get();

        foreach ($jobs as $job) {
            $jobIds[] = $job->id;
            $metadata = json_decode($job->metadata);
            $userIds[] = $metadata->user_id;
        }

        $users = User::whereIn('id', $userIds)->get();

        if ($users->count() > 0) {
            foreach ($users as $user) {
                MailSenderController::verifyAccount($user->email, $user);
            }
        }

        return $jobIds;
    }

    /**
     * Account Verified Emails
     */
    protected function accountVerified(): array
    {
        $userIds = array();
        $jobIds = array();
        $jobs = SchedulerJob::where('command', 'app:email-account-verified')->get();

        foreach ($jobs as $job) {
            $jobIds[] = $job->id;
            $metadata = json_decode($job->metadata);
            $userIds[] = $metadata->user_id;
        }

        $users = User::whereIn('id', $userIds)->get();

        if ($users->count() > 0) {
            foreach ($users as $user) {
                MailSenderController::accountVerified($user->email, $user);
            }
        }

        return $jobIds;
    }

    /**
     * Password Reset Emails
     */
    protected function passwordReset(): array
    {
        $userIds = array();
        $jobIds = array();
        $jobs = SchedulerJob::where('command', 'app:email-password-reset')->get();

        foreach ($jobs as $job) {
            $jobIds[] = $job->id;
            $metadata = json_decode($job->metadata);
            $userIds[] = $metadata->user_id;
        }

        $users = User::whereIn('id', $userIds)->get();

        if ($users->count() > 0) {
            foreach ($users as $user) {
                MailSenderController::accountVerified($user->email, $user);
            }
        }

        return $jobIds;
    }
}
