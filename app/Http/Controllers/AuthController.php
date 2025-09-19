<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

    public function register(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|digits:11',
            'otp'          => 'required|digits:6',
            'role'         => 'required|string',
            'device_type'  => 'nullable|string|max:255',
            'device_id'    => 'nullable|string|max:255',
            'device_token'    => 'nullable|string|max:255',
        ], [
            'phone_number.required' => 'Phone number is required.',
            'phone_number.digits'   => 'Phone number must be 11 digits.',
            'otp.required'          => 'OTP is required.',
            'otp.digits'            => 'OTP must be 6 digits.',
            'role.required'         => 'Role is required.',
        ]);

        // Return first validation error only
        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        // Check if user with same phone number and role already exists
        $existingUser = User::where('phone_number', $request->phone_number)
                            ->where('role', $request->role)
                            ->first();

        if ($existingUser) {
            return response()->json([
                'status'  => false,
                'message' => 'This phone number is already registered for the given role.',
            ], 422);
        }

        // Create new user
        $user = User::create([
            'phone_number'    => $request->phone_number,
            'otp'             => $request->otp,
            'otp_sent_at'     => now(),
            'role'            => $request->role,
            'is_phone_verify' => false,
            'device_type'     => $request->device_type,
            'device_token'    => $request->device_token,
            'device_id'       => $request->device_id,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Registration successful. Please verify OTP to continue.',
            'data'    => [
                'user_id'        => $user->id,
                'phone_number'   => $user->phone_number,
                'otp'            => $user->otp, 
                'otp_sent_at'    => $user->otp_sent_at,
                'is_phone_verify'=> $user->is_phone_verify,
                'role'           => $user->role,
                'device_type'    => $user->device_type,
                'device_token'   => $user->device_token,
                'device_id'      => $user->device_id,
            ],
        ], 200);
    }


    public function verifyOtp(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|digits:11',
            'otp'          => 'required|digits:6',
            'role'         => 'required|string',
        ], [
            'phone_number.required' => 'Phone number is required.',
            'phone_number.digits'   => 'Phone number must be 11 digits.',
            'otp.required'          => 'OTP is required.',
            'otp.digits'            => 'OTP must be 6 digits.',
            'role.required'         => 'Role is required.',
        ]);

        // Return first validation error only
        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        // Find user by phone number and role
        $user = User::where('phone_number', $request->phone_number)
                    ->where('role', $request->role)
                    ->first();

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'User not found for the given role.',
            ], 404);
        }

        // Check OTP and expiry (valid for 5 mins)
        $otpValidTime = now()->subMinutes(10);
        if ($user->otp !== $request->otp || $user->otp_sent_at < $otpValidTime) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid or expired OTP.',
            ], 422);
        }

        // Mark phone as verified and generate API token
        $user->is_phone_verify = true;
        $user->api_token = Str::random(60);
        $user->otp = null;
        $user->otp_sent_at = null;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'OTP verified successfully. You are now logged in.',
            'data' => [
                'user_id'        => $user->id,
                'phone_number'   => $user->phone_number,
                'role'           => $user->role,
                'is_phone_verify'=> $user->is_phone_verify,
                'device_type'    => $user->device_type,
                'device_token'   => $user->device_token,
                'device_id'      => $user->device_id,
                'api_token'      => $user->api_token,
            ],
        ], 200);
    }

    public function login(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|digits:11',
            'role'         => 'required|string',
            'otp'          => 'required|digits:6',
            'device_type'  => 'nullable|string|max:255',
            'device_id'    => 'nullable|string|max:255',
            'device_token'    => 'nullable|string|max:255',
        ], [
            'phone_number.required' => 'Phone number is required.',
            'phone_number.digits'   => 'Phone number must be 11 digits.',
            'otp.required'          => 'OTP is required.',
            'otp.digits'            => 'OTP must be 6 digits.',
            'role.required'         => 'Role is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        // Find or create user
        $user = User::firstOrCreate(
            [
                'phone_number' => $request->phone_number,
                'role' => $request->role
            ],
            [
                'is_phone_verify' => false,
            ]
        );

        // Generate or update OTP
        $user->otp         = $request->otp ?? rand(100000, 999999);
        $user->otp_sent_at = now();
        $user->device_type = $request->device_type;
        $user->device_id   = $request->device_id;
        $user->device_token = $request->device_token;
        $user->save();

        return response()->json([
            'status'  => true,
            'message' => 'OTP sent successfully. Please verify OTP to complete login..',
            'data'    => [
                'user_id'      => $user->id,
                'phone_number' => $user->phone_number,
                'role'         => $user->role,
                'otp'          => $user->otp, // ⚠️ For testing only
                'otp_sent_at'  => $user->otp_sent_at,
            ],
        ], 200);
    }


    public function logout(Request $request)
    {
        // Get the currently authenticated user via the 'api' guard
        $user = Auth::guard('api')->user();

        // If the user is not authenticated
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not authenticated.',
            ], 401);
        }

        // Clear tokens & device info
        $user->api_token      = null;
        $user->google_token   = null;
        $user->facebook_token = null;
        $user->apple_token    = null;

        $user->device_token   = null;
        $user->device_type    = null;
        $user->device_id      = null;

        $user->is_social      = 0; // reset if needed

        $user->save();

        return response()->json([
            'status'  => true,
            'message' => 'Logout successful.',
        ], 200);
    }

    public function getProfile(Request $request)
    {
        // Get authenticated user
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'User not authenticated.',
            ], 401);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Profile fetched successfully.',
            'data'    => $user, // return all fields from users table
        ], 200);
    }



    public function updateProfile(Request $request)
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'User not authenticated.',
            ], 401);
        }

        // Validate request
            $validator = Validator::make($request->all(), [
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|max:255|unique:users,email,' . $user->id,
            // 'phone_number'  => 'required|digits:11|unique:users,phone_number,' . $user->id,
            'profile_image'         => 'required|file|mimes:jpeg,png,jpg|max:4096', // profile image
            'government_id' => 'required|file|mimes:jpeg,png,jpg,pdf|max:4096', // government ID
        ], [
            'name.required'         => 'Name is required.',
            'name.string'           => 'Name must be a valid string.',
            'name.max'              => 'Name must not exceed 255 characters.',
            'email.required'        => 'Email is required.',
            'email.email'           => 'Email must be a valid email address.',
            'email.max'             => 'Email must not exceed 255 characters.',
            'email.unique'          => 'This email is already taken.',
            // 'phone_number.required' => 'Phone number is required.',
            // 'phone_number.digits'   => 'Phone number must be exactly 11 digits.',
            // 'phone_number.unique'   => 'This phone number is already taken.',
            'image.required'        => 'Profile image is required.',
            'image.file'            => 'Profile image must be a file.',
            'image.mimes'           => 'Profile image must be a file of type: jpeg, png, jpg.',
            'image.max'             => 'Profile image must not exceed 4MB.',
            'government_id.required'=> 'Government ID is required.',
            'government_id.file'    => 'Government ID must be a file.',
            'government_id.mimes'   => 'Government ID must be a file of type: jpeg, png, jpg, pdf.',
            'government_id.max'     => 'Government ID must not exceed 4MB.',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        // ---- Handle Profile Image (keep original name) ----
        if ($request->hasFile('profile_image')) {
            $file = $request->file('profile_image');
            $user->image = $file->getClientOriginalName();
            $file->move(public_path('assets/profile_image/'), $user->profile_image);
        }

        // ---- Handle Government ID ----
        if ($request->hasFile('government_id')) {
            $file = $request->file('government_id');
            $extension = $file->getClientOriginalExtension();

            // Take only the first 5 digits of the phone number
            $shortPhone = substr($user->phone_number, 0, 5);

            // Create a safe filename: {userID}_{slugified_name}_{first5digits}_certificate.extension
            $certificateName = $user->id . '_' 
                            . Str::slug($user->name) . '_' 
                            . $shortPhone 
                            . '_certificate.' . $extension;

            // Move file to identity folder
            $file->move(public_path('assets/identity/'), $certificateName);

            // Save filename in database
            $user->government_id = $certificateName;
        }


        // ---- Update other fields ----
        $user->name         = $request->name;
        $user->email        = $request->email;
        // $user->phone_number = $request->phone_number;
        $user->save();

        return response()->json([
            'status'  => true,
            'message' => 'Profile updated successfully.',
            'data'    => $user,
        ], 200);
    }










   

    
    
}
