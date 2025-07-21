<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\MailSenderController;
use App\Models\User;

class SendAdminAnalyticsReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-admin-analytics-report {initiator}';

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
        $newUserOver7Days = User::where('created_at', '>', now()->subDays(7))->get()->count();
        $loggedInUsersOver7Days = User::where('created_at','>', now()->subDays(7))->get()->count();
        $totalUsers = User::all()->count();
        MailSenderController::sendAdminAnalyticsReport([
            'newUserOver7Days' => $newUserOver7Days,
            'loggedInUsersOver7Days' => $loggedInUsersOver7Days,
            'totalUsers' => $totalUsers
        ]);
    }
}
