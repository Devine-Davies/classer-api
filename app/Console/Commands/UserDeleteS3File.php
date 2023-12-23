<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SchedulerJob;
use App\Http\Controllers\S3Controller;

class UserDeleteS3File extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:user-delete-s3-file {initiator}';

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
        $files = array();
        $jobIds = array();
        $jobs = SchedulerJob::where('command', 'app:delete-s3-file')->get();

        foreach ($jobs as $job) {
            $metadata = json_decode($job->metadata);
            $jobIds[] = $job->id;
            $files[] = $metadata->file;
        }

        if (S3Controller::DeleteFiles($files)) {
            SchedulerJob::whereIn('id', $jobIds)->delete();
        } else {
            $this->error('Error deleting file from S3');
        }
    }
}
