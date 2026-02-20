<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class CourierDocumentApprovalController extends Controller
{
    // ✅ Pending Courier Document Users
    public function pendingList()
    {
        $users = User::where('courier_doc_status', 'pending')
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Pending courier documents fetched successfully.',
            'data' => $users
        ]);
    }

    // ✅ Approve Courier Document
    public function approve($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found.'
            ]);
        }

        $user->courier_doc_status = 'approved';
        $user->courier_reject_reason = null;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Courier documents approved successfully.'
        ]);
    }

    // ✅ Reject Courier Document
    public function reject(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found.'
            ]);
        }

        $user->courier_doc_status = 'rejected';
        $user->courier_reject_reason = $request->reason ?? "Rejected by admin";
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Courier documents rejected successfully.'
        ]);
    }
}
