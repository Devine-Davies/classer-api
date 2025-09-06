<?php

namespace App\Http\Controllers\Web;

use Illuminate\Routing\Controller;
use App\Logging\AppLogger;

class InsidersController extends Controller
{
    public function __construct(protected AppLogger $logger)
    {
        $this->logger = $logger;
        $this->logger->setContext(context: 'InsidersController');
    }

    /**
     * Show the application subscriptions page.
     */
    public function classerShare()
    {
        return view('insiders/classer-share/index');
    }
}
