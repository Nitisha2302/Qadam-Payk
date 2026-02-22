<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PrivacyPolicy;
use App\Models\TermsCondition;

class ContentController extends Controller
{
     // Get Privacy Policy
    public function privacyPolicy()
    {
        $policy = PrivacyPolicy::first();

        if (!$policy) {
            return response()->json([
                'status' => false,
                'message' => 'Privacy Policy not found'
            ], 201);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'title' => $policy->title,
                'content' => $policy->content,
            ]
        ],200);
    }

    // Get Terms & Conditions
    public function termsConditions()
    {
        $terms = TermsCondition::first();

        if (!$terms) {
            return response()->json([
                'status' => false,
                'message' => 'Terms & Conditions not found'
            ], 201);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'title' => $terms->title,
                'content' => $terms->content,
            ]
        ],200);
    }

    
}
