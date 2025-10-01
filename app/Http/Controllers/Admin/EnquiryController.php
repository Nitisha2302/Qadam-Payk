<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Enquiry;
use App\Models\PrivacyPolicy;

class EnquiryController extends Controller
{
    // Show all enquiries in admin panel
    public function allQueries()
    {
        // Get all enquiries with user info (including phone number)
        $enquiries = Enquiry::with('user') // eager load user
            ->latest()
            ->paginate(5); // paginate 5 per page

        return view('admin.enquiry.allQuery', compact('enquiries'));
    }


    public function deleteQuery(Request $request)
    {
        $request->validate([
            'query_id' => 'required|exists:enquiries,id',
        ]);

        $Query = Enquiry::find($request->query_id);

        if ($Query) {
            $Query->delete();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Query not found']);
    }


    public function editPrivacyPolicy()
    {
        $privacyPolicy = PrivacyPolicy::first(); // Assume only 1 policy exists
        return view('admin.PrivacyPolicy.edit', compact('privacyPolicy'));
    }

    public function updatePrivacyPolicy(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
        ], [
            'content.required' => 'Please enter the privacy policy content.',
        ]);

        // check if record exists
        $policy = PrivacyPolicy::first();

        if ($policy) {
            $policy->update([
                'content' => $request->content,
            ]);
        } else {
            PrivacyPolicy::create([
                'description' => $request->content,
            ]);
        }

        return redirect()->back()->with('success', 'Privacy Policy updated successfully.');
    }

}
