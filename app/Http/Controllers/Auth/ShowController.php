<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShowController extends Controller
{
    // Return the authenticated user's data
    public function show_auth()
    {
        return Auth::user();
    }

    // Check if the user is authenticated or not
    public function check_auth()
    {
        if (Auth::check()) {
            return 'authentic user';
        }

        return 'not authentic user';
    }
}
