<?php

namespace App\Http\Controllers;

use App\Support\Session\UserSessionStore;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Shared user session context for cross-controller state.
     */
    protected function userSession(Request $request): UserSessionStore
    {
        return UserSessionStore::fromRequest($request);
    }
}
