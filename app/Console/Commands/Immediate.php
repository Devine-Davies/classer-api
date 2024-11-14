<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use App\Http\Controllers\MailSenderController;
use App\Models\SchedulerJob;
use App\Models\User;

class Immediate extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'app:immediate {initiator}';

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
        $accountVerify = 'immediate:email-account-verify';
        $accountVerifySuccess = 'immediate:email-account-verify-success';
        $passwordReset = 'immediate:email-password-reset';
        $passwordResetSuccess = 'immediate:email-password-reset-success';

        $jobs = SchedulerJob::whereIn('command', [
            $accountVerify,
            $accountVerifySuccess,
            $passwordReset,
            $passwordResetSuccess
        ])->get();

        $groups = $jobs->groupBy('command');

        if($groups->get($accountVerify)) {
            $this->verifyAccount($groups->get($accountVerify));
        }

        if($groups->get($accountVerifySuccess)) {
            $this->accountVerified($groups->get($accountVerifySuccess));
        }

        if($groups->get($passwordReset)) {
            $this->passwordReset($groups->get($passwordReset));
        }

        if($groups->get($passwordResetSuccess)) {
            $this->passwordResetSuccess($groups->get($passwordResetSuccess));
        }

        $jobIds = $jobs->pluck('id')->toArray();
        SchedulerJob::whereIn('id', $jobIds)->delete();
    }

    /**
     * Verify account emails
     */
    protected function verifyAccount($jobs)
    {
        $userIds = array();
        foreach ($jobs as $job) {
            $metadata = json_decode($job->metadata);
            $userIds[] = $metadata->user_id;
        }

        $users = User::whereIn('id', $userIds)->get();
        if ($users->count() > 0) {
            foreach ($users as $user) {
                try {
                    MailSenderController::verifyAccount($user->email, $user);
                } catch (\Exception $e) {
                    Log::error('Failed to send email to ' . $email . ': ' . $e->getMessage());
                }
            }
        }
    }

    /**
     * Account Verified Emails
     */
    protected function accountVerified($jobs)
    {
        $userIds = array();
        foreach ($jobs as $job) {
            $metadata = json_decode($job->metadata);
            $userIds[] = $metadata->user_id;
        }

        $users = User::whereIn('id', $userIds)->get();
        if ($users->count() > 0) {
            foreach ($users as $user) {
                try {
                    MailSenderController::accountVerified($user->email, $user);
                } catch (\Exception $e) {
                    Log::error('Failed to send email to ' . $email . ': ' . $e->getMessage());
                }
            }
        }
    }

    /**
     * Password Reset Emails
     */
    protected function passwordReset($jobs)
    {
        $userIds = array();
        foreach ($jobs as $job) {
            $jobIds[] = $job->id;
            $metadata = json_decode($job->metadata);
            $userIds[] = $metadata->user_id;
        }

        $users = User::whereIn('id', $userIds)->get();
        if ($users->count() > 0) {
            foreach ($users as $user) {
                try {
                    MailSenderController::passwordReset($user->email, $user);
                } catch (\Exception $e) {
                    Log::error('Failed to send email to ' . $email . ': ' . $e->getMessage());
                }
            }
        }
    }

    /**
     * Password Reset Success Emails
     */
    protected function passwordResetSuccess($jobs)
    {
        $userIds = array();
        foreach ($jobs as $job) {
            $metadata = json_decode($job->metadata);
            $userIds[] = $metadata->user_id;
        }

        $users = User::whereIn('id', $userIds)->get();
        if ($users->count() > 0) {
            foreach ($users as $user) {
                try {
                    MailSenderController::passwordResetSuccess($user->email, $user);
                } catch (\Exception $e) {
                    Log::error('Failed to send email to ' . $email . ': ' . $e->getMessage());
                }
            }
        }
    }
}
