<?php

namespace App\Http\Controllers;

use App\Models\PrivacyPolicy;
use Illuminate\Http\Request;

class PrivacyPolicyController extends Controller
{
    public function show()
    {
        $policy = PrivacyPolicy::first();
        return view('privacy_policy', compact('policy'));
    }
}
