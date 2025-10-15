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
            'phone_number' => 'required|digits_between:8,15',
            'otp'          => 'required|digits:6',
            'fcm_token'    => 'nullable|string|max:255',
        ], [
            'phone_number.required' => 'Phone number is required.',
             'phone_number.digits_between' => 'Phone number must be between 8 and 15 digits.',
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
        $user->device_token = $request->fcm_token;
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

    // public function login(Request $request)
    // {
    //     // Validate input
    //     $validator = Validator::make($request->all(), [
    //         'phone_number' => 'required|digits_between:8,15',
    //         'device_type'  => 'nullable|string|max:255',
    //         'device_id'    => 'nullable|string|max:255',
    //         'device_token'    => 'nullable|string|max:255',
    //     ], [
    //         'phone_number.required' => 'Phone number is required.',
    //          'phone_number.digits_between' => 'Phone number must be between 8 and 15 digits.',
    //         'otp.digits'            => 'OTP must be 6 digits.',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => $validator->errors()->first(),
    //         ], 201);
    //     }

    //     // Generate a secure random 6-digit OTP
    //     try {
    //         $otp = random_int(100000, 999999);
    //     } catch (\Exception $e) {
    //         // Fallback if random_int fails (very unlikely)
    //         $otp = mt_rand(100000, 999999);
    //     }

    //     // Find or create user
    //     $user = User::firstOrCreate(
    //         [
    //             'phone_number' => $request->phone_number,
    //         ],
    //         [
    //             'is_phone_verify' => false,
    //         ]
    //     );

    //     // Decide message based on new or existing
    //     $message = $user->wasRecentlyCreated
    //         ? 'OTP sent successfully. Please verify OTP to complete registration.'
    //         : 'OTP sent successfully. Please verify OTP to complete login.';

    //     // Generate or update OTP
    //     $user->otp         = $otp;
    //     $user->otp_sent_at = now();
    //     $user->device_type = $request->device_type;
    //     $user->device_id   = $request->device_id;
    //     $user->device_token = $request->device_token;
    //     $user->save();

    //     return response()->json([
    //         'status'  => true,
    //         'message' => $message,
    //         'data'    => [
    //             'user_id'      => $user->id,
    //             'phone_number' => $user->phone_number,
    //             'otp'          => $user->otp, // ⚠️ For testing only
    //             'otp_sent_at'  => $user->otp_sent_at,
    //             'is_phone_verify'  => $user->is_phone_verify,
    //         ],
    //     ], 200);
    // }


    // with sms api  corererct for live
    
    // public function login(Request $request)
    // {
    //     // Validate input
    //     $validator = Validator::make($request->all(), [
    //         'phone_number' => 'required|digits_between:8,15',
    //         'device_type'  => 'nullable|string|max:255',
    //         'device_id'    => 'nullable|string|max:255',
    //         'fcm_token'    => 'nullable|string|max:255',
    //     ], [
    //         'phone_number.required' => 'Phone number is required.',
    //          'phone_number.digits_between' => 'Phone number must be between 8 and 15 digits.',
    //         'otp.digits'            => 'OTP must be 6 digits.',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => $validator->errors()->first(),
    //         ], 201);
    //     }

    //     // Generate a secure random 6-digit OTP
    //     try {
    //         $otp = random_int(100000, 999999);
    //     } catch (\Exception $e) {
    //         // Fallback if random_int fails (very unlikely)
    //         $otp = mt_rand(100000, 999999);
    //     }

    //     // Find or create user
    //     $user = User::firstOrCreate(
    //         [
    //             'phone_number' => $request->phone_number,
    //         ],
    //         [
    //             'is_phone_verify' => false,
    //         ]
    //     );

    //     // Decide message based on new or existing
    //     $message = $user->wasRecentlyCreated
    //         ? 'OTP sent successfully. Please verify OTP to complete registration.'
    //         : 'OTP sent successfully. Please verify OTP to complete login.';

    //     // Generate or update OTP
    //     $user->otp         = $otp;
    //     $user->otp_sent_at = now();
    //     $user->device_type = $request->device_type;
    //     $user->device_id   = $request->device_id;
    //     $user->device_token = $request->fcm_token;
    //     $user->save();

    //     // ------------------------------
    //     // 🔹 Send SMS via OsonSMS
    //     // ------------------------------
    //     $login   = env('OSONSMS_LOGIN');       // your OsonSMS login
    //     $from    = env('OSONSMS_FROM');        // sender ID
    //     $apiKey  = env('OSONSMS_API_KEY');     // pass_salt_hash
    //     $txnId   = 'otp_' . time();
    //     $phone   = $request->phone_number;

    //     // $msg = "Confirmation Code: {$otp}\nThis is the password to login to the QadamPayk. The code is valid for 5 minutes. Don't tell anyone the code.";
  
    //     // $msg = "Confirmation Code: {$otp}\nThis is the password to login to the QadamPayk. The code is valid for 5 minutes. Don't tell anyone the code.";

    //     $msg = "Рамзӣ тасдиқ: {$otp}\nИн рамзӣ воридшавӣ ба QadamPayk аст. Рамз барои 5 дақиқа эътибор дорад. Рамзро ба касе надиҳед.";

    //     // ✅ Use helper function
    //     $input   = "$txnId;$login;$from;$phone;$apiKey";
    //     $strHash = $this->generateSha256Hex($input);

    //     // Call OsonSMS API
    //     $smsResponse = Http::get('https://api.osonsms.com/sendsms_v1.php', [
    //         'login'        => $login,
    //         'from'         => $from,
    //         'phone_number' => $phone,
    //         'msg'          => $msg,
    //         'txn_id'       => $txnId,
    //         'str_hash'     => $strHash,
    //     ]);

    //     // Log request/response
    //     Log::info('OsonSMS Request: ', [
    //         'phone' => $phone,
    //         'msg'   => $msg,
    //         'txn'   => $txnId,
    //         'hash'  => $strHash
    //     ]);
    //     Log::info('OsonSMS Response: ' . $smsResponse->body());

    //     // If SMS failed
    //     $smsData = $smsResponse->json();
    //     if (!isset($smsData['status']) || strtolower($smsData['status']) !== 'ok') {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => 'Failed to send OTP. Please try again.',
    //             'sms'     => $smsData, // optional for debugging
    //         ], 201);
    //     }


    //     return response()->json([
    //         'status'  => true,
    //         'message' => $message,
    //         'data'    => [
    //             'user_id'      => $user->id,
    //             'phone_number' => $user->phone_number,
    //             'otp'          => $user->otp, // ⚠️ For testing only
    //             'otp_sent_at'  => $user->otp_sent_at,
    //             'is_phone_verify'  => $user->is_phone_verify,
    //         ],
    //     ], 200);
    // }


    // with conditional for test 


  
    public function login(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|digits_between:8,15',
            'device_type'  => 'nullable|string|max:255',
            'device_id'    => 'nullable|string|max:255',
            'fcm_token'    => 'nullable|string|max:255',
        ], [
            'phone_number.required' => 'Phone number is required.',
             'phone_number.digits_between' => 'Phone number must be between 8 and 15 digits.',
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

        // ✅ Check if user is blocked
        if ($user->is_blocked) {
            return response()->json([
                'status'  => false,
                'message' => 'You are blocked by the admin. Please contact the administrator at admin@qadampayk.com.',
            ], 403); // 403 Forbidden
        }

        // ✅ Check if user deleted their account
        if ($user->is_deleted) {
            return response()->json([
                'status'  => false,
                'message' => 'This account has been deleted.',
            ], 403);
        }

        // Decide message based on new or existing
        $message = $user->wasRecentlyCreated
            ? 'OTP sent successfully. Please verify OTP to complete registration.'
            : 'OTP sent successfully. Please verify OTP to complete login.';

        // Generate or update OTP
        $user->otp         = $otp;
        $user->otp_sent_at = now();
        $user->device_type = $request->device_type;
        $user->device_id   = $request->device_id;
        $user->device_token = $request->fcm_token;
        $user->save();

        $phone = $request->phone_number;

        // ------------------------------
        // 🔹 Condition: 9 digits → OsonSMS
        // ------------------------------
        if (strlen($phone) === 9) {
            $login   = 'borafzo';
            $from    = 'BORAFZO';
            $apiKey  = 'c3cdbb3f1171320d49f2bf1da20f53fc';
            $txnId   = 'otp_' . time();
            // $login   = env('OSONSMS_LOGIN');
            // $from    = env('OSONSMS_FROM');
            // $apiKey  = env('OSONSMS_API_KEY');
            // $txnId   = 'otp_' . time();

            $msg = "Рамзӣ тасдиқ: {$otp}\nИн рамзӣ воридшавӣ ба QadamPayk аст. Рамз барои 5 дақиқа эътибор дорад. Рамзро ба касе надиҳед.";

            $input   = "$txnId;$login;$from;$phone;$apiKey";
            $strHash = $this->generateSha256Hex($input);

            $smsResponse = Http::get('https://api.osonsms.com/sendsms_v1.php', [
                'login'        => $login,
                'from'         => $from,
                'phone_number' => $phone,
                'msg'          => $msg,
                'txn_id'       => $txnId,
                'str_hash'     => $strHash,
            ]);

            Log::info('OsonSMS Request: ', ['phone' => $phone, 'msg' => $msg, 'txn' => $txnId, 'hash' => $strHash]);
            Log::info('OsonSMS Response: ' . $smsResponse->body());

            $smsData = $smsResponse->json();
            if (!isset($smsData['status']) || strtolower($smsData['status']) !== 'ok') {
                return response()->json([
                    'status'  => false,
                    'message' => 'Failed to send OTP via OsonSMS.',
                    'sms'     => $smsData,
                ], 201);
            }
        } else {
            // ✅ For other phone lengths, just return OTP without sending SMS
            Log::info("Skipping OsonSMS, using local OTP for phone: $phone");
        }

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



    /**
     * Generate SHA-256 hash in lowercase hex (for OsonSMS str_hash)
     *
     * @param string $input
     * @return string
     */
    private function generateSha256Hex(string $input): string
    {
        $utf8String = mb_convert_encoding($input, 'UTF-8');
        return hash('sha256', $utf8String);
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




    // ✅ Update user language
    public function updateLanguage(Request $request)
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not authenticated',
            ], 401);
        }

       // Custom validation
       $validator = Validator::make($request->all(), [
            'user_lang' => 'required|string',
        ], [
            'user_lang.required' => 'Language field is required.',
            'user_lang.string'   => 'Language must be a valid string.',
        ]);

       if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 201);
        }
        $user->update([
            'user_lang' => $request->user_lang
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Language updated successfully',
            'data' => [
                'user_id' => $user->id,
                'user_lang' => $user->user_lang,
            ]
        ]);
    }


    public function getLanguage()
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'User not authenticated',
            ], 401);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Language fetched successfully',
            'data'    => [
                'user_id'   => $user->id,
                'user_lang' => $user->user_lang,
            ]
        ], 200);
    }


    public function deleteAccount(Request $request)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        // Soft delete the user
        $user->is_deleted = true;

        // Nullify all tokens & device info
        $user->api_token      = null;
        $user->google_token   = null;
        $user->facebook_token = null;
        $user->apple_token    = null;
        $user->device_token   = null;
        $user->device_type    = null;
        $user->device_id      = null;
        $user->is_social      = 0;

        $user->save();

        return response()->json([
            'status'  => true,
            'message' => 'Your account has been deleted successfully.',
        ]);
    }



    
    
}
