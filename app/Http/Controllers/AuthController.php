<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserLang;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;  // ← Correct
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AuthController extends Controller
{

    public function verifyOtp(Request $request)
    {
         // Find user first
        $user = User::where('phone_number', $request->phone_number)->first();

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.otp_verify.user_not_found'),
            ], 401);
        }

        // Determine language per device
        $userLang = UserLang::where('user_id', $user->id)
            ->where('device_id', $request->device_id)
            ->where('device_type', $request->device_type)
            ->first();

        $lang = $userLang->language ?? 'ru'; // fallback to Russian
        app()->setLocale($lang);
        // Validation
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|digits_between:8,15',
            'otp'          => 'required|digits:6',
            'fcm_token'    => 'nullable|string|max:255',
        ], [
            'phone_number.required' => __('messages.otp_verify.validation.phone_required'),
            'phone_number.digits_between' => __('messages.otp_verify.validation.phone_digits_between'),
            'otp.required' => __('messages.otp_verify.validation.otp_required'),
            'otp.digits' => __('messages.otp_verify.validation.otp_digits'),
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
                'message' =>__('messages.otp_verify.user_not_found'),

            ], 401);
        }

        // Check OTP and expiry (valid for 5 mins)
        $otpValidTime = now()->subMinutes(10);
        if ($user->otp !== $request->otp || $user->otp_sent_at < $otpValidTime) {
            return response()->json([
                'status'  => false,
                'message' =>  __('messages.otp_verify.invalid_or_expired_otp'),
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
            'message' =>__('messages.otp_verify.otp_verified_success'),
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

    
    // final 
  
    // public function login(Request $request)
    // {
    //     // Determine language first (fallback to Russian)
    //     $lang = $request->language ?? 'ru';
    //     app()->setLocale($lang);
    //     // Validate input
    //     $validator = Validator::make($request->all(), [
    //         'phone_number' => 'required|digits_between:8,15',
    //         'device_type'  => 'nullable|string|max:255',
    //         'device_id'    => 'nullable|string|max:255',
    //         'fcm_token'    => 'nullable|string|max:255',
    //     ], [
    //         'phone_number.required' =>__('messages.login.validation.phone_required'),
    //          'phone_number.digits_between' => __('messages.login.validation.phone_digits_between'),
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => $validator->errors()->first(),
    //         ], 201);
    //     }

    //     // Generate a secure random 6-digit OTP
    //     // try {
    //     //     $otp = random_int(100000, 999999);
    //     // } catch (\Exception $e) {
    //     //     // Fallback if random_int fails (very unlikely)
    //     //     $otp = mt_rand(100000, 999999);
    //     // }
    //     $phone = $request->phone_number;

    //     if ($phone === '123456789') {
    //         $otp = 123456; // ✅ Always use fixed OTP for test number
    //     } else {
    //         try {
    //             $otp = random_int(100000, 999999);
    //         } catch (\Exception $e) {
    //             $otp = mt_rand(100000, 999999);
    //         }
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

    //     // ✅ Check if user is blocked
    //     if ($user->is_blocked) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' =>  __('messages.login.blocked_by_admin'),
    //         ], 403); // 403 Forbidden
    //     }

    //     // ✅ Check if user deleted their account
    //     if ($user->is_deleted) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => __('messages.login.account_deleted'),
    //         ], 403);
    //     }

    //     //     // Save language per device
    //     //    $userLang = UserLang::where('user_id', $user->id)->first();

    //     //     if ($userLang) {
    //     //         $userLang->language = $request->language;
    //     //         $userLang->device_id = $request->device_id;
    //     //         $userLang->device_type = $request->device_type;
    //     //         $userLang->save();
    //     //     } else {
    //     //         UserLang::create([
    //     //             'user_id' => $user->id,
    //     //             'device_id' => $request->device_id,
    //     //             'device_type' => $request->device_type,
    //     //             'language' => $request->language,
    //     //         ]);
    //     //     }

    //     // Save language per device
    //     $userLang = UserLang::where('user_id', $user->id)
    //         ->where('device_id', $request->device_id)
    //         ->where('device_type', $request->device_type)
    //         ->first();

    //     if ($userLang) {
    //         $userLang->language = $request->language;
    //         $userLang->save();
    //     } else {
    //         UserLang::create([
    //             'user_id'     => $user->id,
    //             'device_id'   => $request->device_id,
    //             'device_type' => $request->device_type,
    //             'language'    => $request->language,
    //         ]);
    //     }

    //     // ✅ Update User table with latest device info
    //     $user->update([
    //         'otp'          => $otp,
    //         'otp_sent_at'  => now(),
    //         'device_type'  => $request->device_type,
    //         'device_id'    => $request->device_id,
    //         'device_token' => $request->fcm_token,
    //     ]);


    //     // Decide message based on new or existing
    //      $message = $user->wasRecentlyCreated
    //     ? __('messages.login.otp_sent_register')
    //     : __('messages.login.otp_sent_login');

    //     // Generate or update OTP
    //     $user->otp         = $otp;
    //     $user->otp_sent_at = now();
    //     $user->device_type = $request->device_type;
    //     $user->device_id   = $request->device_id;
    //     $user->device_token = $request->fcm_token;
    //     $user->save();

    //     $phone = $request->phone_number;

    //     // ------------------------------
    //     // 🔹 Condition: 9 digits → OsonSMS
    //     // ------------------------------
    //     if (strlen($phone) === 9) {
    //         $login   = 'borafzo';
    //         $from    = 'BORAFZO';
    //         $apiKey  = 'c3cdbb3f1171320d49f2bf1da20f53fc';
    //         $txnId   = 'otp_' . time();
    //         // $login   = env('OSONSMS_LOGIN');
    //         // $from    = env('OSONSMS_FROM');
    //         // $apiKey  = env('OSONSMS_API_KEY');
    //         // $txnId   = 'otp_' . time();

    //          $otpMsg = [
    //             'en' => "OTP verification: {$otp}\nThis is your QadamPayk login code. Valid for 5 minutes. Do not share it.",
    //             'ru' => "Подтверждение OTP: {$otp}\nЭто ваш код для входа в QadamPayk. Действителен 5 минут. Не сообщайте его.",
    //             'tj' => "Рамзӣ тасдиқ: {$otp}\nИн рамзӣ воридшавӣ ба QadamPayk аст. Рамз барои 5 дақиқа эътибор дорад. Рамзро ба касе надиҳед."
    //         ];

    //         $msg = $otpMsg[$lang] ?? $otpMsg['ru'];

    //         $input   = "$txnId;$login;$from;$phone;$apiKey";
    //         $strHash = $this->generateSha256Hex($input);

    //         $smsResponse = Http::get('https://api.osonsms.com/sendsms_v1.php', [
    //             'login'        => $login,
    //             'from'         => $from,
    //             'phone_number' => $phone,
    //             'msg'          => $msg,
    //             'txn_id'       => $txnId,
    //             'str_hash'     => $strHash,
    //         ]);

    //         Log::info('OsonSMS Request: ', ['phone' => $phone, 'msg' => $msg, 'txn' => $txnId, 'hash' => $strHash]);
    //         Log::info('OsonSMS Response: ' . $smsResponse->body());

    //         $smsData = $smsResponse->json();
    //         if (!isset($smsData['status']) || strtolower($smsData['status']) !== 'ok') {
    //             return response()->json([
    //                 'status'  => false,
    //                 'message' =>  __('messages.login.invalid_or_expired_otp'),
    //                 'sms'     => $smsData,
    //             ], 201);
    //         }
    //     } else {
    //         // ✅ For other phone lengths, just return OTP without sending SMS
    //         Log::info("Skipping OsonSMS, using local OTP for phone: $phone");
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


    // new otp format 

    public function login(Request $request)
    {
        // Determine language first (fallback to Russian)
        $lang = $request->language ?? 'ru';
        app()->setLocale($lang);
        // Validate input
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|digits_between:8,15',
            'device_type'  => 'nullable|string|max:255',
            'device_id'    => 'nullable|string|max:255',
            'fcm_token'    => 'nullable|string|max:255',
        ], [
            'phone_number.required' =>__('messages.login.validation.phone_required'),
             'phone_number.digits_between' => __('messages.login.validation.phone_digits_between'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ], 201);
        }

        // Generate a secure random 6-digit OTP
        // try {
        //     $otp = random_int(100000, 999999);
        // } catch (\Exception $e) {
        //     // Fallback if random_int fails (very unlikely)
        //     $otp = mt_rand(100000, 999999);
        // }
        $phone = $request->phone_number;

        if ($phone === '123456789') {
            $otp = 123456; // ✅ Always use fixed OTP for test number
        } else {
            try {
                $otp = random_int(100000, 999999);
            } catch (\Exception $e) {
                $otp = mt_rand(100000, 999999);
            }
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
                'message' =>  __('messages.login.blocked_by_admin'),
            ], 403); // 403 Forbidden
        }

        // ✅ Check if user deleted their account
        if ($user->is_deleted) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.login.account_deleted'),
            ], 403);
        }

        //     // Save language per device
        //    $userLang = UserLang::where('user_id', $user->id)->first();

        //     if ($userLang) {
        //         $userLang->language = $request->language;
        //         $userLang->device_id = $request->device_id;
        //         $userLang->device_type = $request->device_type;
        //         $userLang->save();
        //     } else {
        //         UserLang::create([
        //             'user_id' => $user->id,
        //             'device_id' => $request->device_id,
        //             'device_type' => $request->device_type,
        //             'language' => $request->language,
        //         ]);
        //     }

        // Save language per device
        $userLang = UserLang::where('user_id', $user->id)
            ->where('device_id', $request->device_id)
            ->where('device_type', $request->device_type)
            ->first();

        if ($userLang) {
            $userLang->language = $request->language;
            $userLang->save();
        } else {
            UserLang::create([
                'user_id'     => $user->id,
                'device_id'   => $request->device_id,
                'device_type' => $request->device_type,
                'language'    => $request->language,
            ]);
        }

        // ✅ Update User table with latest device info
        $user->update([
            'otp'          => $otp,
            'otp_sent_at'  => now(),
            'device_type'  => $request->device_type,
            'device_id'    => $request->device_id,
            'device_token' => $request->fcm_token,
        ]);


        // Decide message based on new or existing
         $message = $user->wasRecentlyCreated
        ? __('messages.login.otp_sent_register')
        : __('messages.login.otp_sent_login');

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

            $otpMsg = [
                'en' => "Your verification code: {$otp}\nPlease do not share it with anyone.",
                'ru' => "Ваш код подтверждения: {$otp}\nПожалуйста, никому его не сообщайте.",
                'tj' => "Рамзи тасдиқи шумо: {$otp}\nЛутфан онро ба касе надиҳед.",
            ];

            //  $otpMsg = [
            //     'en' => "OTP verification: {$otp}\nThis is your QadamPayk login code. Valid for 5 minutes. Do not share it.",
            //     'ru' => "Подтверждение OTP: {$otp}\nЭто ваш код для входа в QadamPayk. Действителен 5 минут. Не сообщайте его.",
            //     'tj' => "Рамзӣ тасдиқ: {$otp}\nИн рамзӣ воридшавӣ ба QadamPayk аст. Рамз барои 5 дақиқа эътибор дорад. Рамзро ба касе надиҳед."
            // ];

            $msg = $otpMsg[$lang] ?? $otpMsg['ru'];

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
                    'message' =>  __('messages.login.invalid_or_expired_otp'),
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
                'message' => __('messages.logout.user_not_authenticated'),
            ], 401);
        }

            // 🔹 Detect user's preferred language from UserLang table
            $userLang = UserLang::where('user_id', $user->id)
                ->where('device_id', $user->device_id)
                ->where('device_type', $user->device_type)
                ->first();

            $lang = $userLang->language ?? 'ru'; // fallback to Russian
            app()->setLocale($lang);

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
            'message' =>  __('messages.logout.logout_success'),
        ], 200);
    }

    public function getProfile(Request $request)
    {
        // Get authenticated user
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.getProfile.user_not_authenticated'),
            ], 401);
        }

        // 🔹 Detect user's preferred language from UserLang table
        $userLang = UserLang::where('user_id', $user->id)
            ->where('device_id', $user->device_id)
            ->where('device_type', $user->device_type)
            ->first();

        $lang = $userLang->language ?? 'ru'; // fallback to Russian
        app()->setLocale($lang);

        return response()->json([
            'status'  => true,
            'message' =>  __('messages.getProfile.success'),
            'data'    => $user, // return all fields from users table
        ], 200);
    }


   

    public function updateProfile(Request $request)
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.profile.user_not_authenticated'),
            ], 401);
        }

        // 🔹 Detect user's preferred language from UserLang table
        $userLang = UserLang::where('user_id', $user->id)
            ->where('device_id', $user->device_id)
            ->where('device_type', $user->device_type)
            ->first();

        $lang = $userLang->language ?? 'ru'; // fallback to Russian
        app()->setLocale($lang);

        // Validate request
        $validator = Validator::make($request->all(), [
            'name'            => 'required|string|max:255',
            'dob'             => 'nullable|string',
            'gender'          => 'nullable|in:male,female,other',
            'profile_image'   => 'nullable|file|mimes:jpeg,png,jpg|max:4096',
            'government_id'   => 'nullable|array',
            'government_id.*' => 'file|mimes:jpeg,png,jpg,pdf|max:4096',
         ], [
            'name.required'              => __('messages.profile.validation.name_required'),
            'name.string'                => __('messages.profile.validation.name_string'),
            'name.max'                   => __('messages.profile.validation.name_max'),
            'gender.in'                  => __('messages.profile.validation.gender_in'),
            'profile_image.file'         => __('messages.profile.validation.profile_file'),
            'profile_image.mimes'        => __('messages.profile.validation.profile_mimes'),
            'profile_image.max'          => __('messages.profile.validation.profile_max'),
            'government_id.array'        => __('messages.profile.validation.govid_array'),
            'government_id.*.file'       => __('messages.profile.validation.govid_file'),
            'government_id.*.mimes'      => __('messages.profile.validation.govid_mimes'),
            'government_id.*.max'        => __('messages.profile.validation.govid_max'),
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
                     'message' => __('messages.profile.validation.invalid_dob_format'),
                ], status: 422);
            }
        }

        $user->save();

        return response()->json([
            'status'  => true,
             'message' => __('messages.profile.updated_successfully'),
            'data'    => $user,
        ], 200);
    }


    public function updateLanguage(Request $request)
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'User not authenticated.',
            ], 401);
        }

        // Validate language
        $validator = Validator::make($request->all(), [
            'language'    => 'required|in:en,ru,tj',
            'device_id'   => 'required|string',
            'device_type' => 'required|string',
        ], [
            'language.required'    => __('messages.language.validation.required'),
            'language.in'          => __('messages.language.validation.in'),
            'device_id.required'   => 'Device ID is required.',
            'device_type.required' => 'Device type is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 201);
        }

        // Save language per device
        $userLang = UserLang::where('user_id', $user->id)
            ->where('device_id', $request->device_id)
            ->where('device_type', $request->device_type)
            ->first();

        if ($userLang) {
            $userLang->language = $request->language;
            $userLang->save();
        } else {
            UserLang::create([
                'user_id'     => $user->id,
                'device_id'   => $request->device_id,
                'device_type' => $request->device_type,
                'language'    => $request->language,
            ]);
        }

          // 🔹 Also update in users table for easy lookup during logout or other APIs
        $user->device_id   = $request->device_id;
        $user->device_type = $request->device_type;
        $user->save();

        // Set application locale
        app()->setLocale($request->language);

        return response()->json([
            'status' => true,
            'message' => __('messages.language.updated'),
            'data' => ['language' => $request->language],
        ], 200);
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
                'message' => __('messages.deleteAccount.user_not_authenticated'),
            ], 401);
        }

        // 🔹 Detect user's preferred language from UserLang table
        $userLang = UserLang::where('user_id', $user->id)
            ->where('device_id', $user->device_id)
            ->where('device_type', $user->device_type)
            ->first();

        $lang = $userLang->language ?? 'ru'; // fallback to Russian
        app()->setLocale($lang);

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
            'message' => __('messages.deleteAccount.success'),
        ]);
    }



    
    
}
