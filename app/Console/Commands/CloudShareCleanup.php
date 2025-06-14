<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\CloudShare;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class CloudShareCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cloud-share-cleanup {initiator}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is designed to delete S3 files that are no longer needed.';

    /**
     * Delete the S3 files associated with CloudShare entities and remove the CloudShare records.
     * This command is intended to be run periodically to clean up unused files.
     *
     */
    public function deleteS3EntityFiles()
    {
        // This method is not used in the current implementation.
        // It can be removed or repurposed if needed.
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $initiator = $this->argument('initiator');
        $this->info("Command initiated by: $initiator");
    
        DB::beginTransaction();
    
        try {
            CloudShare::where('user_id', '1')->chunk(100, function ($shares) {
                foreach ($shares as $share) {
                    try {
                        $entities = collect($share->cloudEntities);
                        $firstKey = $entities->pluck('key')->first();
                        $directory = explode('/', $firstKey)[0];
    
                        if (!Storage::disk('s3')->deleteDirectory($directory)) {
                            $this->error("S3 delete failed for CloudShare ID: {$share->id}");
                            continue;
                        }
    
                        $user = User::where('id', $share->user_id)->first();
                        if (!$user) {
                            $this->warn("User not found for CloudShare ID: {$share->id}");
                            continue;
                        }
    
                        $totalSize = $entities->sum('size');
    
                        // Delete entities and the share
                        $entities->each->delete();
                        $share->delete();
    
                        // Update usage
                        $user->cloudUsage()
                            ->updateOrCreate(['user_id' => $user->id], [])
                            ->decrement('total_usage', $totalSize);
    
                        $this->info("✅ Deleted CloudShare ID: {$share->id}, reclaimed " . round($totalSize / 1024 / 1024, 2) . " MB");
                    } catch (\Throwable $e) {
                        $this->error("❌ Error with CloudShare ID: {$share->id} - {$e->getMessage()}");
                    }
                }
            });
    
            DB::commit();
            $this->info("🎉 CloudShare cleanup completed successfully.");
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error("🛑 Transaction failed: " . $e->getMessage());
        }
    }
}
