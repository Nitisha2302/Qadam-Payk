<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PrivacyPolicyController extends Controller
{
    public function show()
    {
        // Fetch the first (only) privacy policy row
        $policy = PrivacyPolicy::first();
        return view('privacy_policy', compact('policy'));
    }
}
