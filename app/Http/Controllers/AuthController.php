<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;  // ← Correct
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AuthController extends Controller
{


    // public function register(Request $request)
    // {
    //     // Validate input
    //     $validator = Validator::make($request->all(), [
    //         'phone_number' => 'required|digits:11',
    //         'otp'          => 'required|digits:6',
    //         'device_type'  => 'nullable|string|max:255',
    //         'device_id'    => 'nullable|string|max:255',
    //         'device_token'    => 'nullable|string|max:255',
    //     ], [
    //         'phone_number.required' => 'Phone number is required.',
    //         'phone_number.digits'   => 'Phone number must be 11 digits.',
    //         'otp.required'          => 'OTP is required.',
    //         'otp.digits'            => 'OTP must be 6 digits.',
    //     ]);

    //     // Return first validation error only
    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => $validator->errors()->first(),
    //         ], 201);
    //     }

    //     // Check if user with same phone number and role already exists
    //     $existingUser = User::where('phone_number', $request->phone_number)->first();


    //     if ($existingUser) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => 'This phone number is already registered.',
    //         ], 201);
    //     }

    //     // Create new user
    //     $user = User::create([
    //         'phone_number'    => $request->phone_number,
    //         'otp'             => $request->otp,
    //         'otp_sent_at'     => now(),
    //         'is_phone_verify' => false,
    //         'device_type'     => $request->device_type,
    //         'device_token'    => $request->device_token,
    //         'device_id'       => $request->device_id,
    //     ]);

    //     return response()->json([
    //         'status'  => true,
    //         'message' => 'Registration successful. Please verify OTP to continue.',
    //         'data'    => [
    //             'user_id'        => $user->id,
    //             'phone_number'   => $user->phone_number,
    //             'otp'            => $user->otp, 
    //             'otp_sent_at'    => $user->otp_sent_at,
    //             'is_phone_verify'=> $user->is_phone_verify,
    //             'device_type'    => $user->device_type,
    //             'device_token'   => $user->device_token,
    //             'device_id'      => $user->device_id,
    //         ],
    //     ], 200);
    // }


    public function verifyOtp(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|digits:11',
            'otp'          => 'required|digits:6',
        ], [
            'phone_number.required' => 'Phone number is required.',
            'phone_number.digits'   => 'Phone number must be 11 digits.',
            'otp.required'          => 'OTP is required.',
            'otp.digits'            => 'OTP must be 6 digits.',
        ]);

        // Return first validation error only
        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ], 201);
        }

        // Find user by phone number and role
        $user = User::where('phone_number', $request->phone_number)
                    ->first();

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'User not found.',
            ], 401);
        }

        // Check OTP and expiry (valid for 5 mins)
        $otpValidTime = now()->subMinutes(10);
        if ($user->otp !== $request->otp || $user->otp_sent_at < $otpValidTime) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid or expired OTP.',
            ], 201);
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
            'device_type'  => 'nullable|string|max:255',
            'device_id'    => 'nullable|string|max:255',
            'device_token'    => 'nullable|string|max:255',
        ], [
            'phone_number.required' => 'Phone number is required.',
            'phone_number.digits'   => 'Phone number must be 11 digits.',
            'otp.digits'            => 'OTP must be 6 digits.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ], 201);
        }

        // Generate a secure random 6-digit OTP
        try {
            $otp = random_int(100000, 999999);
        } catch (\Exception $e) {
            // Fallback if random_int fails (very unlikely)
            $otp = mt_rand(100000, 999999);
        }

        // Find or create user
        $user = User::firstOrCreate(
            [
                'phone_number' => $request->phone_number,
            ],
            [
                'is_phone_verify' => false,
            ]
        );

        // Decide message based on new or existing
        $message = $user->wasRecentlyCreated
            ? 'OTP sent successfully. Please verify OTP to complete registration.'
            : 'OTP sent successfully. Please verify OTP to complete login.';

        // Generate or update OTP
        $user->otp         = $otp;
        $user->otp_sent_at = now();
        $user->device_type = $request->device_type;
        $user->device_id   = $request->device_id;
        $user->device_token = $request->device_token;
        $user->save();

        return response()->json([
            'status'  => true,
            'message' => $message,
            'data'    => [
                'user_id'      => $user->id,
                'phone_number' => $user->phone_number,
                'otp'          => $user->otp, // ⚠️ For testing only
                'otp_sent_at'  => $user->otp_sent_at,
                'is_phone_verify'  => $user->is_phone_verify,
            ],
        ], 200);
    }


    // with sms api  

    // public function register(Request $request)
    // {
    //     // Validate input
    //     $validator = Validator::make($request->all(), [
    //         'phone_number' => 'required',
    //         'device_type'  => 'nullable|string|max:255',
    //         'device_id'    => 'nullable|string|max:255',
    //         'device_token' => 'nullable|string|max:255',
    //     ], [
    //         'phone_number.required' => 'Phone number is required.',
    //         // 'phone_number.digits'   => 'Phone number must be 15 digits.',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => $validator->errors()->first(),
    //         ], 201);
    //     }

    //     //  Check if user already exists
    //     $existingUser = User::where('phone_number', $request->phone_number)->first();
    //     if ($existingUser) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => 'This phone number is already registered.',
    //         ], 201);
    //     }

    //     //  Generate OTP
    //     $otp = rand(100000, 999999);

    //     // Prepare message and compute hash
    //     $txnId  = env('SMS_PAYMENT_ID'); // unique transaction ID
    //     $login  = env('SMS_LOGIN');      // login
    //     $sender = env('SMS_SENDER');     // sender
    //     $phone  = $request->phone_number;
    //     $passSaltHash = env('SMS_PASS_SALT_HASH'); // this is new
    //     $message  = "Your OTP code is $otp ";

    //     // Compute str_hash according to OsonSMS API requirement
    //    $strHash = hash('sha256', $txnId . ';' . $login . ';' . $sender . ';' . $phone . ';' . $passSaltHash);

    //     //  Prepare SMS data
    //     $smsData = [
    //         'phone_number' => $phone,
    //         'msg'          => $message,
    //         'login'        => $login,
    //         'str_hash'     => $strHash,
    //         'from'         => $sender,
    //         'txn_id'       => $txnId,
    //         // Optional:
    //         //'channel'       => 'telegram',
    //         //'is_confidential' => true,
    //     ];

    //     //  Log request
    //     Log::info('OsonSMS Request Data: ', $smsData);

    //     //  Send SMS
    //     $smsResponse = Http::get('https://api.osonsms.com/sendsms_v1.php', $smsData);

    //     //  Log response
    //     Log::info('OsonSMS Response: ' . $smsResponse->body());

    //     //  Check if SMS was sent successfully
    //     if (strpos($smsResponse->body(), 'OK') === false) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => 'Failed to send OTP. Please try again.',
    //         ], 500);
    //     }

    //     //  Create user
    //     $user = User::create([
    //         'phone_number'    => $request->phone_number,
    //         'otp'             => $otp,
    //         'otp_sent_at'     => now(),
    //         'is_phone_verify' => false,
    //         'device_type'     => $request->device_type,
    //         'device_token'    => $request->device_token,
    //         'device_id'       => $request->device_id,
    //     ]);

    //     //  Return response
    //     return response()->json([
    //         'status'  => true,
    //         'message' => 'Registration successful. OTP has been sent.',
    //         'data'    => [
    //             'user_id'        => $user->id,
    //             'phone_number'   => $user->phone_number,
    //             'otp_sent_at'    => $user->otp_sent_at,
    //             'is_phone_verify'=> $user->is_phone_verify,
    //             'device_type'    => $user->device_type,
    //             'device_token'   => $user->device_token,
    //             'device_id'      => $user->device_id,
    //         ],
    //     ], 200);
    // }


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


    // public function updateProfile(Request $request)
    // {
    //     $user = Auth::guard('api')->user();

    //     if (!$user) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => 'User not authenticated.',
    //         ], 401);
    //     }

    //     // Validate request
    //         $validator = Validator::make($request->all(), [
    //         'name'          => 'required|string|max:255',
    //        'dob'           => 'nullable|string',
    //         'gender'        => 'nullable|in:male,female,other',
    //         'profile_image'         => 'nullable|file|mimes:jpeg,png,jpg|max:4096', // profile image
    //          'government_id' => 'required|array', // must be an array
    //         'government_id.*' => 'file|mimes:jpeg,png,jpg,pdf|max:4096',
    //     ], [
    //         'name.required'         => 'Name is required.',
    //         'name.string'           => 'Name must be a valid string.',
    //         'name.max'              => 'Name must not exceed 255 characters.',
    //         'dob.date'              => 'Date of birth must be a valid date (YYYY-MM-DD).',
    //         'gender.in'             => 'Gender must be male, female, or other.',
    //         // 'image.required'        => 'Profile image is required.',
    //         'profile_image.file'            => 'Profile image must be a file.',
    //         'profile_image.mimes'           => 'Profile image must be a file of type: jpeg, png, jpg.',
    //         'profile_image.max'             => 'Profile image must not exceed 4MB.',
    //         'government_id.required'=> 'Government ID is required.',
    //         'government_id.array'   => 'Government ID must be an array of files.',
    //         'government_id.file'    => 'Government ID must be a file.',
    //         'government_id.mimes'   => 'Government ID must be a file of type: jpeg, png, jpg, pdf.',
    //         'government_id.max'     => 'Each government ID must not exceed 4MB.',
    //     ]);


    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => $validator->errors()->first(),
    //         ], 201);
    //     }

    //     // ---- Handle Profile Image (keep original name) ----
    //     if ($request->hasFile('profile_image')) {
    //         $file = $request->file('profile_image');
    //         $user->image = $file->getClientOriginalName();
    //         $file->move(public_path('assets/profile_image/'), $user->profile_image);
    //     }

    //     // ---- Handle multiple Government IDs ----
    //     $uploadedFiles = [];
    //     if ($request->hasFile('government_id')) {
    //         foreach ($request->file('government_id') as $file) {
    //             $extension = $file->getClientOriginalExtension();
    //             $shortPhone = substr($user->phone_number, 0, 5);
    //             $certificateName = $user->id . '_' 
    //                             . Str::slug($user->name) . '_' 
    //                             . $shortPhone . '_' 
    //                             . Str::random(5) 
    //                             . '_certificate.' . $extension;

    //             $file->move(public_path('assets/identity/'), $certificateName);
    //             $uploadedFiles[] = $certificateName;
    //         }

    //         $user->government_id = json_encode($uploadedFiles); // store as JSON
    //     }


    //     // ---- Update other fields ----
    //     $user->name         = $request->name;
    //     $user->gender = $request->gender;

    //     // Convert DD-MM-YYYY → YYYY-MM-DD before saving
    //     if ($request->dob) {
    //         try {
    //             $user->dob = Carbon::createFromFormat('d-m-Y', $request->dob)->format('Y-m-d');
    //         } catch (\Exception $e) {
    //             return response()->json([
    //                 'status'  => false,
    //                 'message' => 'Invalid date format. Use DD-MM-YYYY.',
    //             ], 422);
    //         }
    //     }
    //     $user->save();

    //     return response()->json([
    //         'status'  => true,
    //         'message' => 'Profile updated successfully.',
    //         'data'    => $user,
    //     ], 200);
    // }

    // public function login(Request $request)
    // {
    //     // Validate input
    //     $validator = Validator::make($request->all(), [
    //         'phone_number' => 'required|digits:11',
    //         'device_type'  => 'nullable|string|max:255',
    //         'device_id'    => 'nullable|string|max:255',
    //         'device_token' => 'nullable|string|max:255',
    //     ], [
    //         'phone_number.required' => 'Phone number is required.',
    //         'phone_number.digits'   => 'Phone number must be 11 digits.',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => $validator->errors()->first(),
    //         ], 201);
    //     }

    //     // Generate OTP
    //     try {
    //         $otp = random_int(100000, 999999);
    //     } catch (\Exception $e) {
    //         $otp = mt_rand(100000, 999999);
    //     }

    //     // Find or create user
    //     $user = User::firstOrCreate(
    //         ['phone_number' => $request->phone_number],
    //         ['is_phone_verify' => false]
    //     );

    //     // Generate txn_id
    //     $txn_id = 'otp_' . $user->id . '_' . time();

    //     // Get credentials from .env
    //     $login   = env('OSONSMS_LOGIN');
    //     $from    = env('OSONSMS_FROM');
    //     $apiKey  = env('OSONSMS_API_KEY');

    //     // Generate SHA-256 str_hash
    //     $str_hash = hash('sha256', $login . $apiKey . $txn_id);

    //     // SMS message
    //     $msg = "Confirmation Code: {$otp}\nThis is the password to login to the QadamPayk. The code is valid for 5 minutes. Don't tell anyone the code.";

    //     // Send SMS via OsonSMS
    //     $url = "https://api.osonsms.com/sendsms_v1.php?" . http_build_query([
    //         'login'        => $login,
    //         'from'         => $from,
    //         'phone_number' => $request->phone_number,
    //         'msg'          => $msg,
    //         'txn_id'       => $txn_id,
    //         'str_hash'     => $str_hash,
    //     ]);

    //     $ch = curl_init();
    //     curl_setopt($ch, CURLOPT_URL, $url);
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //     $smsResponse = curl_exec($ch);
    //     curl_close($ch);

    //     // Save OTP & device info
    //     $user->otp          = $otp;
    //     $user->otp_sent_at  = now();
    //     $user->device_type  = $request->device_type;
    //     $user->device_id    = $request->device_id;
    //     $user->device_token = $request->device_token;
    //     $user->save();

    //     $message = $user->wasRecentlyCreated
    //         ? 'OTP sent successfully. Please verify OTP to complete registration.'
    //         : 'OTP sent successfully. Please verify OTP to complete login.';

    //     return response()->json([
    //         'status'  => true,
    //         'message' => $message,
    //         'data'    => [
    //             'user_id'      => $user->id,
    //             'phone_number' => $user->phone_number,
    //             'otp'          => $user->otp,        // ⚠️ For testing only
    //             'otp_sent_at'  => $user->otp_sent_at,
    //             'is_phone_verify' => $user->is_phone_verify,
    //         ],
    //         'sms_response' => $smsResponse, // optional debug info
    //     ], 200);
    // }



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
        'name'            => 'required|string|max:255',
        'dob'             => 'nullable|string',
        'gender'          => 'nullable|in:male,female,other',
        'profile_image'   => 'nullable|file|mimes:jpeg,png,jpg|max:4096',
        'government_id'   => 'nullable|array',
        'government_id.*' => 'file|mimes:jpeg,png,jpg,pdf|max:4096',
    ], [
        'name.required'              => 'Name is required.',
        'name.string'                => 'Name must be a valid string.',
        'name.max'                   => 'Name must not exceed 255 characters.',
        'gender.in'                  => 'Gender must be male, female, or other.',
        'profile_image.file'         => 'Profile image must be a file.',
        'profile_image.mimes'        => 'Profile image must be jpeg, png, or jpg.',
        'profile_image.max'          => 'Profile image must not exceed 4MB.',
        'government_id.required'     => 'Government ID is required.',
        'government_id.array'        => 'Government ID must be an array.',
        'government_id.*.file'       => 'Each government ID must be a file.',
        'government_id.*.mimes'      => 'Each government ID must be jpeg, png, jpg, or pdf.',
        'government_id.*.max'        => 'Each government ID must not exceed 4MB.',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status'  => false,
            'message' => $validator->errors()->first(),
        ], status: 201);
    }

    // ---- Handle Profile Image ----
    if ($request->hasFile('profile_image')) {
        $file = $request->file('profile_image');
        $filename = time() . '_' . $file->getClientOriginalName();
        $file->move(public_path('assets/profile_image/'), $filename);
        $user->image = $filename;
    }

    // ---- Handle multiple Government IDs ----
    $uploadedFiles = [];
    if ($request->hasFile('government_id')) {
        foreach ($request->file('government_id') as $file) {
            $extension = $file->getClientOriginalExtension();
            $shortPhone = substr($user->phone_number, 0, 5);
            $certificateName = $user->id . '_' 
                             . Str::slug($user->name) . '_' 
                             . $shortPhone . '_' 
                             . Str::random(5) 
                             . '_certificate.' . $extension;

            $file->move(public_path('assets/identity/'), $certificateName);
            $uploadedFiles[] = $certificateName;
        }
        $user->government_id = json_encode($uploadedFiles);
    }

    // ---- Update other fields ----
    $user->name   = $request->name;
    $user->gender = $request->gender;

    if ($request->dob) {
        try {
            $user->dob = Carbon::createFromFormat('d-m-Y', $request->dob)->format('Y-m-d');
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid date format. Use DD-MM-YYYY.',
            ], status: 422);
        }
    }

    $user->save();

    return response()->json([
        'status'  => true,
        'message' => 'Profile updated successfully.',
        'data'    => $user,
    ], 200);
}





    
    
}
