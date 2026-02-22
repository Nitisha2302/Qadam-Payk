<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class CourierDocumentApprovalController extends Controller
{
    // ✅ Show Only Online Users
    public function index(Request $request)
    {
        $query = User::where('is_online', 1);

        // 🔍 Optional Search
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('phone_number', 'like', '%' . $request->search . '%');
            });
        }

        $users = $query->latest()->paginate(10);

        return view('admin.courier.courier-document-list', compact('users'));
    }

    // ✅ Approve
    public function approve($id)
    {
        $user = User::findOrFail($id);
        $user->courier_doc_status = 'approved';
        $user->save();

        return back()->with('success', 'Courier verified successfully.');
    }

    // ✅ Reject
    public function reject($id)
    {
        $user = User::findOrFail($id);
        $user->courier_doc_status = 'rejected';
        $user->save();

        return back()->with('success', 'Courier rejected successfully.');
    }
}
