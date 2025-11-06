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

    public function showFeedback()
    {
        $policy = PrivacyPolicy::first();
        return view('admin.PrivacyPolicy.feedbackForm', compact('policy'));
    }

    public function showBlockPolicyPage()
    {
        $policy = PrivacyPolicy::first();
        return view('admin.PrivacyPolicy.blockUserPolicyForm', compact('policy'));
    }

    public function blockuserPolicy()
    {
        $policy = PrivacyPolicy::first();
        return view('admin.PrivacyPolicy.blockUserPolicyForm',compact('policy'));
    }

    public function showReportForm()
    {
        $policy = PrivacyPolicy::first();
        return view('admin.PrivacyPolicy.reportForm', compact('policy'));
    }

}
