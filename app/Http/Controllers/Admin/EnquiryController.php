<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Enquiry;
use App\Models\PrivacyPolicy;
use App\Models\TermsCondition;
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

    public function updatePrvacyPolicy(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
           'content' => 'required|string',
        ], [
            'title.required' => 'Please enter the privacy policy title.',
            'content.required' => 'Please enter the privacy policy content.',
        ]);
        // Clean HTML before saving
        $cleanedTitle = $this->cleanHtml($request->title);
        $cleanedContent = $this->cleanHtml($request->content);

        // check if record exists
        $policy = PrivacyPolicy::first();

        $data = [
            'title'   => $cleanedTitle,
            'content' => $cleanedContent,
        ];

        if ($policy) {
           $policy->update($data);
        } else {
            PrivacyPolicy::create($data);
        }

        return redirect()->back()->with('success', 'Privacy Policy updated successfully.');
    }
    


    public function editTermsConditions()
    {
        $privacyPolicy = TermsCondition::first(); // Assume only 1 policy exists
        return view('admin.PrivacyPolicy.editTermCondition', compact('privacyPolicy'));
    }

    // public function updateTermsConditions(Request $request)
    // {
    //     $request->validate([
    //         'title' => 'required|string',
    //        'content' => 'required|string',
    //     ], [
    //         'title.required' => 'Please enter the privacy policy title.',
    //         'content.required' => 'Please enter the privacy policy content.',
    //     ]);

    //     // check if record exists
    //     $policy = TermsCondition::first();

    //     if ($policy) {
    //         $policy->update([
    //             'title' => $request->title,
    //            'content' => $request->content,
    //         ]);
    //     } else {
    //         TermsCondition::create([
    //            'title' => $request->title,
    //           'content' => $request->content, 
    //         ]);
    //     }

    //     return redirect()->back()->with('success', 'Terms and Condition updated successfully.');
    // }

    public function updateTermsConditions(Request $request)
    {
        $request->validate([
            'title'   => 'required|string',
            'content' => 'required|string',
        ], [
            'title.required'   => 'Please enter the title.',
            'content.required' => 'Please enter the content.',
        ]);

        // Clean HTML before saving
        $cleanedTitle = $this->cleanHtml($request->title);
        $cleanedContent = $this->cleanHtml($request->content);

        $policy = TermsCondition::first();

        $data = [
            'title'   => $cleanedTitle,
            'content' => $cleanedContent,
        ];

        if ($policy) {
            $policy->update($data);
        } else {
            TermsCondition::create($data);
        }

        return redirect()->back()->with('success', 'Terms & Conditions updated successfully.');
    }

    /**
     * Clean CKEditor HTML
     */
    private function cleanHtml($html)
    {
        libxml_use_internal_errors(true); // Prevent HTML5 warnings

        $doc = new \DOMDocument();
        $doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));

        // Remove unwanted attributes
        $xpath = new \DOMXPath($doc);
        foreach ($xpath->query('//*[@data-start or @data-end]') as $node) {
            $node->removeAttribute('data-start');
            $node->removeAttribute('data-end');
        }

        // Remove <p> inside <li>
        foreach ($xpath->query('//li/p') as $p) {
            $parent = $p->parentNode;
            while ($p->firstChild) {
                $parent->insertBefore($p->firstChild, $p);
            }
            $parent->removeChild($p);
        }

        // Return inner HTML of body
        $body = $doc->getElementsByTagName('body')->item(0);
        $innerHTML = '';
        foreach ($body->childNodes as $child) {
            $innerHTML .= $doc->saveHTML($child);
        }

        return trim($innerHTML);
    }


}
