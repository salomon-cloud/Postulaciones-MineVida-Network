<?php

namespace App\Http\Controllers;

use App\Models\Setting;

class LandingController extends Controller
{
    public function __invoke()
    {
        return view('welcome', [
            'applicationsOpen' => Setting::bool('applications_open', true),
        ]);
    }
}
