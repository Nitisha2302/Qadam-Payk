<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PrivacyPolicy;

class PrivacyPolicyController extends Controller
{
    public function show()
    {
        $policy = PrivacyPolicy::first();
        return view('admin.PrivacyPolicy.privacy_policy', compact('policy'));
    }
}
