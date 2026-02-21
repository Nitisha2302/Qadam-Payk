<?php

namespace App\Http\Controllers\Courier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CourierVerificationController extends Controller
{
    // ✅ Submit Courier Documents (Only Online Users)
    public function submitDocuments(Request $request)
    {
        $user = Auth::guard('api')->user();

         if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => __('unauthorised'),
            ], 401);
        }

        if ($user->id_verified != 1) {
            return response()->json([
                'status' => false,
                'message' => 'Your ID is not verified. You cannot become courier.'
            ], 403);
        }

        // if ($user->is_online != 1) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'You must be online to submit courier documents.'
        //     ], 403);
        // }

         $validator = Validator::make($request->all(), [
            'passport_images' => 'required|array|min:1',
            'passport_images.*' => 'image|mimes:jpg,jpeg,png|max:2048',

            'license_images' => 'nullable|array',
            'license_images.*' => 'image|mimes:jpg,jpeg,png|max:2048',

            'selfie' => 'required|image|mimes:jpg,jpeg,png|max:2048'
        ], [
            // ✅ Custom Validation Messages
            'passport_images.required' => 'Passport images are required.',
            'passport_images.array' => 'Passport images must be in array format.',
            'passport_images.*.image' => 'Passport file must be an image.',
            'passport_images.*.mimes' => 'Passport image must be jpg, jpeg or png.',
            'passport_images.*.max' => 'Passport image must not exceed 2MB.',

            'license_images.array' => 'License images must be in array format.',
            'license_images.*.image' => 'License file must be an image.',
            'license_images.*.mimes' => 'License image must be jpg, jpeg or png.',
            'license_images.*.max' => 'License image must not exceed 2MB.',

            'selfie.required' => 'Selfie image is required.',
            'selfie.image' => 'Selfie must be an image.',
            'selfie.mimes' => 'Selfie image must be jpg, jpeg or png.',
            'selfie.max' => 'Selfie image must not exceed 2MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 201);
        }

        // ===============================
        // Upload Passport Images
        // ===============================
        $passportFiles = [];

        if ($request->hasFile('passport_images')) {

            foreach ($request->file('passport_images') as $file) {

                $extension = $file->getClientOriginalExtension();
                $shortPhone = substr($user->phone_number ?? '00000', 0, 5);

                $passportName = $user->id . '_'
                    . Str::slug($user->name ?? 'user') . '_'
                    . $shortPhone . '_'
                    . Str::random(5)
                    . '_passport.' . $extension;

                $file->move(public_path('assets/courier/passport/'), $passportName);

                $passportFiles[] = $passportName;
            }
        }

        // ===============================
        // Upload License Images (Optional)
        // ===============================
        $licenseFiles = [];

        if ($request->hasFile('license_images')) {

            foreach ($request->file('license_images') as $file) {

                $extension = $file->getClientOriginalExtension();
                $shortPhone = substr($user->phone_number ?? '00000', 0, 5);

                $licenseName = $user->id . '_'
                    . Str::slug($user->name ?? 'user') . '_'
                    . $shortPhone . '_'
                    . Str::random(5)
                    . '_license.' . $extension;

                $file->move(public_path('assets/courier/license/'), $licenseName);

                $licenseFiles[] = $licenseName;
            }
        }

        // ===============================
        // Upload Selfie
        // ===============================
        $selfieName = null;

        if ($request->hasFile('selfie')) {

            $file = $request->file('selfie');

            $extension = $file->getClientOriginalExtension();
            $shortPhone = substr($user->phone_number ?? '00000', 0, 5);

            $selfieName = $user->id . '_'
                . Str::slug($user->name ?? 'user') . '_'
                . $shortPhone . '_'
                . Str::random(5)
                . '_selfie.' . $extension;

            $file->move(public_path('assets/courier/selfie/'), $selfieName);
        }

        // ===============================
        // Save Data in User Table
        // ===============================
        $user->passport_images = json_encode($passportFiles);
        $user->license_images = json_encode($licenseFiles);
        $user->courier_selfie = $selfieName;

        $user->courier_doc_status = 'pending';
        $user->courier_reject_reason = null;

        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Documents submitted successfully. Waiting for admin approval.',
            'data' => [
                'passport_images' => $passportFiles,
                'license_images' => $licenseFiles,
                'selfie' => $selfieName,
                'courier_doc_status' => $user->courier_doc_status
            ]
        ]);
    }

    // ✅ Courier Status API
    public function status()
    {
        $user = Auth::guard('api')->user();

        return response()->json([
            'status' => true,
            'message' => 'Courier status fetched successfully.',
            'data' => [
                'is_online' => $user->is_online,
                'courier_doc_status' => $user->courier_doc_status,
                'courier_reject_reason' => $user->courier_reject_reason,
                'id_verified' => $user->id_verified,
                'passport_images' => json_decode($user->passport_images, true),
                'license_images' => json_decode($user->license_images, true),
                'courier_selfie' => $user->courier_selfie,
            ]
        ]);
    }
}
