<?php
namespace App\Http\Controllers;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Http\Requests\GoogleLoginRequest;
use App\Http\Requests\FacebookLoginRequest;
use App\Http\Requests\AppleLoginRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\EditProfileRequest;
use App\Http\Requests\GetProfileRequest;
use App\Http\Requests\ResendOtpRequest;
use App\Http\Requests\MeasurementRequest;
use App\Http\Requests\HealthInfoRequest;
use App\Http\Requests\FoodPreferenceRequest;
use App\Http\Requests\HealthDataRequest;
use App\Http\Requests\GoalRequest;
use App\Http\Requests\ActivityLevelRequest;
use App\Http\Requests\RegisterOnboadingRequest;
use App\Http\Requests\TimespanRequest;
use App\Http\Requests\LocationRequest;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserDetail;
use App\Models\UserLang;
use App\Models\HeightKey;
use App\Models\WeightKey;
use App\Models\NotificationSetting;
use App\Models\MeasurementKey;
use App\Models\TimespanKey;
use App\Models\GenderDropdown;
use App\Models\GoalDropdown;
use App\Models\TargetGoalDropdown;
use App\Models\DietDropdown;
use App\Models\WeightHistory;
use App\Models\DailySchedule;
use App\Models\ActivityLevelDropdown;
use App\Models\HealthKit; 
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Mail\OTPVerificationMail;
use App\Mail\ForgotPasswordMail;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;

use App\Models\Subscription;


class AuthController extends Controller
{
    // registration api

    public function register(RegisterRequest $request)
    {
        Log::info('Register API called', ['request' => $request->all()]);

        try {
            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'email_verified' => 0,
                'fcm_token' => $request->fcm_token,
            ]);
            // Save device type and token
            $user->device_type = $request->device_type;
            $user->device_id = $request->device_id;

            // Generate OTP
            $otp = mt_rand(100000, 999999);
            $user->otp = $otp;
            $user->otp_sent_at = now();

            $logoPath = url('/') . "/assets/email-logo/logo_hewie.png";

            Mail::to($user->email)->send(new OTPVerificationMail($user->name ?? '', $otp, $logoPath));
            $user->save();

            //  Check user_langs table for this device info
            $userLang = UserLang::where('device_id', $request->device_id)
                ->where('device_type', $request->device_type)
                ->first();

            if ($userLang) {
                // Save user_lang to user_details table
                UserDetail::updateOrCreate(
                    ['user_id' => $user->id],
                    ['user_lang' => $userLang->user_lang]
                );
            }else {
                // Always create user_details even if user_lang doesn't exist
                UserDetail::updateOrCreate(
                    ['user_id' => $user->id],
                    [] // Default or empty data, can be extended
                );
            }

            NotificationSetting::create([
               'user_id' => $user->id,
            ]);


           $defaultSchedule = [
                "wakeup" => ["title" => "Wake-Up", "startTime" => "07:00 AM", "endTime" => "08:00 AM"],
                "breakfast" => ["title" => "Breakfast", "startTime" => "08:30 AM", "endTime" => "09:00 AM"],
                "lunch" => ["title" => "Lunch", "startTime" => "12:00 PM", "endTime" => "01:00 PM"],
                "dinner" => ["title" => "Dinner", "startTime" => "06:00 PM", "endTime" => "07:00 PM"],
                "exercise" => ["title" => "Exercise", "startTime" => "05:00 PM", "endTime" => "06:00 PM"],
               "sleep" => ["title" => "Sleep", "startTime" => "10:00 PM", "endTime" => "11:00 PM"],
            ];


            DailySchedule::create([
              'user_id' => $user->id,
             'weekdays_schedule' => json_encode($defaultSchedule),
             'weekend_schedule' => json_encode($defaultSchedule),
            ]);



            Log::info('User registered and OTP sent', ['user_id' => $user->id, 'email' => $user->email]);

            return response()->json([
                'status' => true,
                'message' => 'Gebruiker succesvol geregistreerd',
                'data' => [
                    'id' => $user->id,
                    'api_token' => $user->api_token,
                    'name' => $user->name,
                    'nick_name' => $user->nick_name,
                    'phone_number' => $user->phone_number,
                    'email' => $user->email,
                    'dob' => $user->dob,
                    'gender' => $user->gender,
                    'address' => $user->address,
                    'email_verified' => $user->email_verified,
                    'profile_created_status' => $user->profile_created_status,
                    'profile_created_at' => $user->profile_created_at,
                    'height' => $user->height,
                    'weight' => $user->weight,
                    'bmi' => $user->bmi,
                    'waist_circum' => $user->waist_circum,
                    'neck_circum' => $user->neck_circum,
                    'chest' => $user->chest,
                    'hips' => $user->hips,
                    'upper_leg' => $user->upper_leg,
                    'upper_arm' => $user->upper_arm,
                    'goal' => $user->goal,
                    'target_goal_value' => $user->target_goal_value,
                    'timespan' => $user->timespan,
                    'activity_type' => $user->activity_type,
                    'activitylevel' => $user->activitylevel,
                    'food_activity_scale' => $user->food_activity_scale,
                    'food_preferences' => $user->food_preferences,
                    'other_preferences' => $user->other_preferences,
                    'allergies' => $user->allergies,
                    'favorite_food' => $user->favorite_foods,
                    'dislike_foods' => $user->dislike_foods,
                    'family_member_status' => $user->family_member_status,
                    'family_number_count' => $user->family_number_count,
                    'disease' => $user->disease,
                   
                    'is_social' => $user->is_social,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Exception during registration', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Er is een fout opgetreden tijdens de registratie.',
            ], 500);
        }
    }

    //  login api with password and email

    public function login(LoginRequest  $request)
    {
        // Find the user by email
        $user = User::where('email', $request->email)->first();
 
        // If user does not exist, return an error
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Sorry, je bent niet bij ons geregistreerd met dit e-mailadres. Registreer u alstublieft!',
            ], 201);
        }

        // If user does not exist or password is incorrect, return an error
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Ongeldige inloggegevens',
            ], 201);
        }

        // Generate a secure auth token
        $apiToken = bin2hex(random_bytes(30));
        $lastLogin = now()->toDateString();

        // Check for user_langs match based on device info
        $userLang = UserLang::where('device_id', $request->device_id)
            ->where('device_type', $request->device_type)
            ->first();

        if ($userLang) {
            UserDetail::updateOrCreate(
                ['user_id' => $user->id],
                ['user_lang' => $userLang->user_lang]
            );
        }
        

        // Set the auth token in the user model
        // $user->api_token = $apiToken;

        // If the email is not verified, generate and send OTP
        if ($user->email_verified == 0) {
            // Generate a six-digit OTP
            $otp = mt_rand(100000, 999999);

            // Update the OTP and sent time in the user model
            $user->otp = $otp;
            $user->otp_sent_at = now(); // Save the current time
            $logoPath = url('/') . "/assets/email-logo/logo_hewie.png";
            
            // Send the OTP to the user's email
            Mail::to($user->email)->send(new OTPVerificationMail($user->name,$otp,$logoPath));
            $user->device_id = $request->device_id; // Save device ID
            $user->device_type = $request->device_type; // Save device type
            $user->fcm_token = $request->fcm_token;
            // Save the user model with OTP and auth token
            $user->save();

            // Return response requesting OTP verification
            return response()->json([
                 'status' => true,
                'message' => 'OTP verzonden naar e-mail. Verifieer om het inloggen te voltooien.',
                'data' => [
                    'id' => $user->id, 
                    'api_token' => $user->api_token,
                    // 'image'=>$user->image,
                    // 'name' => $user->name, 
                    // 'nick_name' => $user->nick_name, 
                    // 'phone_number' => $user->phone_number, 
                    'email' => $user->email,
                    // 'dob' => $user->dob,
                    // 'gender' => $user->gender,
                    // 'address' => $user->address,
                    'email_verified' => $user->email_verified,
                    'profile_created_status' => $user->profile_created_status,
                    'profile_created_at' => $user->profile_created_at,
                    // 'height' => $user->height,
                    // 'weight' => $user->weight,
                    // 'bmi' => $user->bmi, 
                    // 'waist_circum' => $user->waist_circum,
                    // 'neck_circum' => $user->neck_circum,

                    // 'chest' => $user->chest,
                    // 'hips' => $user->hips,
                    // 'upper_leg' => $user->upper_leg,
                    // 'upper_arm' => $user->upper_arm,
                    // 'goal' => $user->goal,
                    // 'target_goal_value' => $user->target_goal_value,
                    // 'timespan' => $user->timespan,
                    // 'activity_type' => $user->activity_type,
                    // 'activitylevel' => $user->activitylevel,
                    // 'food_activity_scale' => $user->food_activity_scale,
                    // 'food_preferences' => $user->food_preferences,
                    // 'other_preferences' => $user->other_preferences,
                    // 'allergiess' => $user->allergies,
                    // 'favorite_food' => $user->favorite_foods,
                    // 'dislike_foods' => $user->dislike_foods,
                    // 'family_member_status' => $user->family_member_status,
                    // 'family_number_count' => $user->family_number_count,
                    // 'disease' => $user->disease,
                    'is_social' => $user->is_social,
                   
                    'created_at' => $user->created_at, 
                    'updated_at' => $user->updated_at, 
                ],
            ], 200);
        }
        $user->api_token = $apiToken;
        // Save last login date
        $user->last_login_at = $lastLogin;
        $user->device_id = $request->device_id; // Save device ID
        $user->device_type = $request->device_type; // Save device type
        $user->fcm_token = $request->fcm_token;
        // If email is verified, proceed with login without OTP
        $user->save(); // Save auth token for future requests

        // Return response confirming successful login
        return response()->json([
            'status' => true,
            'message' => 'Inloggen gelukt.',
            'data' => [
                'id' => $user->id,
                    'api_token' => $user->api_token,
                    // 'image'=>$user->image,
                    // 'name' => $user->name, 
                    // 'nick_name' => $user->nick_name, 
                    // 'phone_number' => $user->phone_number, 
                    'email' => $user->email,
                    // 'dob' => $user->dob,
                    // 'gender' => $user->gender,
                    // 'address' => $user->address,
                    'email_verified' => $user->email_verified,
                    'profile_created_status' => $user->profile_created_status,
                    'profile_created_at' => $user->profile_created_at,
                    'last_login_at' => $user->last_login_at,
                    // 'height' => $user->height,
                    // 'weight' => $user->weight,
                    // 'waist_circum' => $user->waist_circum,
                    // 'neck_circum' => $user->neck_circum,
                    // 'chest' => $user->chest,
                    // 'hips' => $user->hips,
                    // 'upper_leg' => $user->upper_leg,
                    // 'upper_arm' => $user->upper_arm,
                    // 'goal' => $user->goal,
                    // 'target_goal_value' => $user->target_goal_value,
                    // 'timespan' => $user->timespan,
                    // 'activity_type' => $user->activity_type,
                    // 'activitylevel' => $user->activitylevel,
                    // 'food_activity_scale' => $user->food_activity_scale,
                    // 'food_preferences' => $user->food_preferences,
                    // 'allergiess' => $user->allergies,
                    // 'favorite_food' => $user->favorite_foods,
                    // 'dislike_foods' => $user->dislike_foods,
                    // 'family_member_status' => $user->family_member_status,
                    // 'family_number_count' => $user->family_number_count,
                    // 'other_preferences' => $user->other_preferences,
                    // 'disease' => $user->disease,
                    'is_social' => $user->is_social,
                    
                    'created_at' => $user->created_at, 
                    'updated_at' => $user->updated_at, 
            ],
        ], 200);
    }


    // verify OTP
    public function verifyOtp(VerifyOtpRequest $request)
    {
    
        // Fetch the user based on email
        $user = User::where('email', $request->email)->first();

        // Check if OTP matches
        if ($request->otp != $user->otp) {
            return response()->json([
                'status' => false,
                'message' => 'Ongeldige OTP.',
            ], 201);
        }

        // Check if OTP has expired (5 minutes)
        $otpSentAt = Carbon::parse($user->otp_sent_at); // Convert otp_sent_at to Carbon instance
        if ($otpSentAt->addMinutes(1)->isPast()) {
            return response()->json([
                'status' => false,
                'message' => 'OTP is verlopen. a.u.b. verzoek voor een nieuwe OTP.',
            ], 201);
        }

        // OTP is valid, generate an auth token or session (optional)
        $apiToken = bin2hex(random_bytes(30)); // Generate a new token or use an existing one
        $lastLogin = now()->toDateString();
        $user->api_token = $apiToken;
        $user->email_verified = 1;
        $user->last_login_at = $lastLogin;
        // Clear OTP only after successful verification
        $user->otp = null;
        $user->otp_sent_at = null;

        $user->save();

        // Return successful login response with auth token
        return response()->json([
            'status' => true,
            'message' => 'Inloggen succesvol.',
            'data' => [
                'id' => $user->id, 
                'api_token' => $user->api_token,
                // 'image'=>$user->image,
                'name' => $user->name, 
                'nick_name' => $user->nick_name, 
                'phone_number' => $user->phone_number, 
                'email' => $user->email,
                'dob' => $user->dob,
                'gender' => $user->gender,
                'address' => $user->address,
                'email_verified' => $user->email_verified,
                'profile_created_status' => $user->profile_created_status,
                'profile_created_at' => $user->profile_created_at,
                'height' => $user->height,
                'weight' => $user->weight,
                'bmi' => $user->bmi, 
                'waist_circum' => $user->waist_circum,
                'neck_circum' => $user->neck_circum,

                'chest' => $user->chest,
                'hips' => $user->hips,
                'upper_leg' => $user->upper_leg,
                'upper_arm' => $user->upper_arm,
                'goal' => $user->goal,
                'target_goal_value' => $user->target_goal_value,
                'timespan' => $user->timespan,
                'activity_type' => $user->activity_type,
                'activitylevel' => $user->activitylevel,
                'food_activity_scale' => $user->food_activity_scale,
                'food_preferences' => $user->food_preferences,
                'other_preferences' => $user->other_preferences,
                'allergiess' => $user->allergies,
                'favorite_food' => $user->favorite_foods,
                'dislike_foods' => $user->dislike_foods,
                'family_member_status' => $user->family_member_status,
                'family_number_count' => $user->family_number_count,
                'disease' => $user->disease,
                'is_social' => $user->is_social,
                'created_at' => $user->created_at, 
                'updated_at' => $user->updated_at, 
            ],
        ], 200);
    }

    // resend otp api
    
    public function resendOtp(ResendOtpRequest $request)
    {
        
        // Find the user by email
        $user = User::where('email', $request->email)->first();
    
        // If the user does not exist, return an error
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Gebruiker niet gevonden.',
            ], 404);
        }
    
        // Check if the user has already been sent an OTP
        $otpSentAt = Carbon::parse($user->otp_sent_at);  // Ensure it's a Carbon instance
    
        // If OTP was sent less than 5 minutes ago, prevent resending
        // if ($otpSentAt->diffInMinutes(now()) < 2) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'OTP has already been sent recently. Please try again later.',
        //     ], 400);
        // }
    
        // Generate a new OTP
        $otp = mt_rand(100000, 999999);
    
        // Update the OTP and sent time in the user model
        $user->otp = $otp;
        $user->otp_sent_at = now(); // Save the current time
        $logoPath = url('/') . "/assets/email-logo/logo_hewie.png";
        // Send the OTP to the user's email
        Mail::to($user->email)->send(new OTPVerificationMail($user->name,$otp,$logoPath));
    
        // Save the user model with new OTP and sent time
        $user->save();
    
        // Return response confirming OTP has been resent
        return response()->json([
            'status' => true,
            'message' => 'OTP opnieuw verzonden. Controleer je e-mail.',
        ], 200);
    }

    // forgot password api

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        
        // Find the user by email
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'E-mail niet gevonden.',
            ], 201);
        }

        // Generate a random password
        $randomPassword = bin2hex(random_bytes(4)); // 8-character random password

        // Hash and update the password in the database
        $user->password = Hash::make($randomPassword);
        $user->save();

        // Send the random password via email
        try {
            $logoPath = url('/') . "/assets/email-logo/logo_hewie.png";
            Mail::to($user->email)->send(new ForgotPasswordMail($user->name, $randomPassword,$logoPath));
        
            return response()->json([
                'status' => true,
                'message' => 'Er is een nieuw wachtwoord naar uw e-mailadres verzonden.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Het verzenden van de e-mail is mislukt. Probeer het later nog eens.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    // change password

    public function changePassword(ChangePasswordRequest $request)
    {

        // Get the currently authenticated user using Bearer token and custom guard
        $user = Auth::guard('api')->user(); 
        // If the user is not authenticated, return an error
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => __('auth.change_password.user_not_authenticated'),
            ], 401);
        }
        // Eager load the related details
        $user=$user->load('details');

        // Set the language based on user preference before using translations
        if ($user) {
            App::setLocale($user->details->user_lang ? $user->details->user_lang : config('app.locale')); // e.g., 'en' or 'nl'
        }
        
        // Check if the current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => __('auth.change_password.current_password_incorrect'),
            ], 201);
        }

        // Update the user's password
        $user->password = Hash::make($request->new_password);
        $user->save();

        // Return a success response
        return response()->json([
            'status' => true,
            'message' => __('auth.change_password.password_changed'),
        ], 200);
    }


    //    login via otp api

    public function loginWithOtp(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email', // Ensure email uniqueness
        ]);
        
        // If validation fails, return an error response
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 201);
        }
    
        // Check if the user already exists
        $existingUser = User::where('email', $request->email)->first();
    
        if ($existingUser) {
            return response()->json([
                'status' => false,
                'message' => 'Email already exists. Please log in instead.',
            ], 201); // HTTP 409 Conflict
        }
    
        // Generate a secure auth token
        $apiToken = bin2hex(random_bytes(30)); // Generate a secure auth token
    
        // Create a new user with the generated auth token
        $user = User::create([
            'email' => $request->email,
            'name' => $request->name ?? 'New User',
            // 'api_token' => $apiToken, // Save the generated auth token
            'email_verified' => 0, 
        ]);
    
        // Generate and send OTP if email is not verified
        if ($user->email_verified == 0) {
            // Generate a six-digit OTP
            $otp = mt_rand(100000, 999999);
    
            // Store OTP and the timestamp when it was sent
            $user->otp = $otp;
            $user->otp_sent_at = now(); // Save the current time
    
            // Send the OTP to the user's email
            Mail::to($user->email)->send(new OTPVerificationMail($otp));
    
            // Save the OTP and timestamp to the user model
            $user->save();
    
            // Return response to indicate OTP is sent
            return response()->json([
                'status' => true,
                'message' => 'OTP sent to email. Please verify to complete login.',
                'data' => [
                    'id' => $user->id, 
                    'api_token' => $user->api_token,
                    // 'image'=>$user->image,
                    'name' => $user->name, 
                    'nick_name' => $user->nick_name, 
                    'phone_number' => $user->phone_number, 
                    'email' => $user->email,
                    'dob' => $user->dob,
                    'gender' => $user->gender,
                    'email_verified' => $user->email_verified,
                    'height' => $user->height,
                    'weight' => $user->weight,
                    'bmi' => $user->bmi, 
                    'waist_circum' => $user->waist_circum,
                    'neck_circum' => $user->neck_circum,

                    'chest' => $user->chest,
                    'hips' => $user->hips,
                    'upper_leg' => $user->upper_leg,
                    'upper_arm' => $user->upper_arm,
                    'goal' => $user->goal,
                    'target_goal_value' => $user->target_goal_value,
                    'timespan' => $user->timespan,
                    'activitylevel' => $user->activitylevel,
                    'food_preferences' => $user->food_preferences,
                    'other_preferences' => $user->other_preferences,
                    'allergiess' => $user->allergies,
                    'favorite_food' => $user->favorite_foods,
                    'disease' => $user->disease,
                    'is_social' => $user->is_social,
                    'created_at' => $user->created_at, 
                    'updated_at' => $user->updated_at, 
                ],
            ], 200);
        }
    
        // If email is already verified, directly log the user in
        return response()->json([
            'status' => true,
            'message' => 'Login successful.',
            'data' => [
             'id' => $user->id, // Include the user ID
                // 'image'=>$user->image,
                'name' => $user->name, // Include the name
                'nick_name' => $user->nick_name, 
                'phone_number' => $user->phone_number, 
                'email' => $user->email,
                'dob' => $user->dob,
                'gender' => $user->gender,
                'email_verified' => $user->email_verified,
                'height' => $user->height,
                'weight' => $user->weight,
                'waist_circum' => $user->waist_circum,
                'neck_circum' => $user->neck_circum,
                'disease' => $user->disease,
                'is_social' => $user->is_social,
                'created_at' => $user->created_at, 
                'updated_at' => $user->updated_at, 
            ],
        ], 200);
    }

    
    // login via google

    public function googleLogIn(GoogleLoginRequest $request)
    {
        $Name = $request->name;
        $email = $request->email;
        $googleToken = $request->google_token;
        $deviceToken = $request->device_token;
        $deviceType = $request->device_type;
        $deviceId = $request->device_id;
        $fcmToken = $request->fcm_token;
        $created = now();
    
        // Generate a secure API token
        $apiToken = bin2hex(random_bytes(30));
    
        // Check if user exists by email
        $user = User::where('email', $email)->first();
    
        if ($user) {
            // Update existing user
            $user->name = $Name;
            $user->google_token = $googleToken;
            $user->api_token = $apiToken; // Updated token generation
            $user->device_token = $deviceToken;
            $user->device_type = $deviceType;
            $user->fcm_token = $fcmToken;
            $user->device_id = $deviceId;
            $user->is_social = 1;
            $user->email_verified = 1;
            $user->save();
    
            return response()->json([
                'status' => true,
                'message' => 'Gebruiker is succesvol ingelogd.',
                'data' => [
                    'id' => $user->id, // Include the user ID
                    'api_token' => $user->api_token,
                     'email' => $user->email,
                    'email_verified' => $user->email_verified,
                    'profile_created_status' => $user->profile_created_status,
                    'profile_created_at' => $user->profile_created_at,
                    'is_social' => $user->is_social,
                    'created_at' => $user->created_at, 
                    'updated_at' => $user->updated_at,
                ],
            ], 200);
        }else {
            // Create new user
            $user = User::create([
                'name' => $Name,
                'email' => $email,
                'google_token' => $googleToken,
                'api_token' => $apiToken, // Updated token generation
                'device_token' => $deviceToken,
                'is_social' => 1,
                'email_verified'=> 1,
                'device_type' => $deviceType,
                'device_id' => $deviceId,
                'fcm_token' => $fcmToken,
                'created_at' => $created,
            ]);

            $defaultSchedule = [
                "wakeup" => ["title" => "Wake-Up", "startTime" => "07:00 AM", "endTime" => "08:00 AM"],
                "breakfast" => ["title" => "Breakfast", "startTime" => "08:30 AM", "endTime" => "09:00 AM"],
                "lunch" => ["title" => "Lunch", "startTime" => "12:00 PM", "endTime" => "01:00 PM"],
                "dinner" => ["title" => "Dinner", "startTime" => "06:00 PM", "endTime" => "07:00 PM"],
                "exercise" => ["title" => "Exercise", "startTime" => "05:00 PM", "endTime" => "06:00 PM"],
               "sleep" => ["title" => "Sleep", "startTime" => "10:00 PM", "endTime" => "11:00 PM"],
            ];


            DailySchedule::create([
              'user_id' => $user->id,
             'weekdays_schedule' => json_encode($defaultSchedule),
             'weekend_schedule' => json_encode($defaultSchedule),
            ]);


            NotificationSetting::create([
               'user_id' => $user->id,
            ]);
    
            return response()->json([
                'status' => true,
                'message' => 'Gebruiker succesvol aangemaakt en ingelogd.',
                'data' => [
                    'id' => $user->id, 
                    'api_token' => $user->api_token,
                     'email' => $user->email,
                    'email_verified' => $user->email_verified,
                    'profile_created_status' => $user->profile_created_status,
                    'profile_created_at' => $user->profile_created_at,
                    'is_social' => $user->is_social,
                    'created_at' => $user->created_at, 
                    'updated_at' => $user->updated_at, 
                ],
            ], 200);
        }
    }

    // login via facebook
    
    public function facebookLogIn(FacebookLoginRequest $request)
    {
        $Name = $request->name;
        $email = $request->email;
        $facebookToken = $request->facebook_token;
        $deviceToken = $request->device_token;
        $deviceType = $request->device_type;
        $deviceId = $request->device_id;
        $fcmToken = $request->fcm_token;
        $created = now();
    
        // Generate a secure API token
        $apiToken = bin2hex(random_bytes(30));
    
        // Check if user exists by email
        $user = User::where('email', $email)->first();
    
        if ($user) {
            // Update existing user
            $user->name = $Name;
            $user->facebook_token = $facebookToken;
            $user->api_token = $apiToken; // Updated token generation
            $user->device_token = $deviceToken;
            $user->device_type = $deviceType;
            $user->device_id = $deviceId;
            $user->fcm_token = $fcmToken;
            $user->is_social = 2;
            $user->email_verified = 1;
            $user->save();
    
            return response()->json([
                'status' => true,
                'message' => 'Gebruiker is succesvol ingelogd.',
                'data' => [
                    'id' => $user->id, // Include the user ID
                    'api_token' => $user->api_token,
                     'email' => $user->email,
                    'email_verified' => $user->email_verified,
                    'profile_created_status' => $user->profile_created_status,
                    'profile_created_at' => $user->profile_created_at,
                    'is_social' => $user->is_social,
                    'created_at' => $user->created_at, 
                    'updated_at' => $user->updated_at, 
                ],
            ], 200);
        }else {
            // Create new user
            $user = User::create([
                'name' => $Name,
                'email' => $email,
                'facebook_token' => $facebookToken,
                'api_token' => $apiToken, 
                'device_token' => $deviceToken,
                'is_social' => 2,
                'email_verified'=> 1,
                'device_type' => $deviceType,
                'device_id' => $deviceId,
                'fcm_token' => $fcmToken,
                'created_at' => $created,
            ]);

            $defaultSchedule = [
                "wakeup" => ["title" => "Wake-Up", "startTime" => "07:00 AM", "endTime" => "08:00 AM"],
                "breakfast" => ["title" => "Breakfast", "startTime" => "08:30 AM", "endTime" => "09:00 AM"],
                "lunch" => ["title" => "Lunch", "startTime" => "12:00 PM", "endTime" => "01:00 PM"],
                "dinner" => ["title" => "Dinner", "startTime" => "06:00 PM", "endTime" => "07:00 PM"],
                "exercise" => ["title" => "Exercise", "startTime" => "05:00 PM", "endTime" => "06:00 PM"],
               "sleep" => ["title" => "Sleep", "startTime" => "10:00 PM", "endTime" => "11:00 PM"],
            ];


            DailySchedule::create([
              'user_id' => $user->id,
             'weekdays_schedule' => json_encode($defaultSchedule),
             'weekend_schedule' => json_encode($defaultSchedule),
            ]);

 

             NotificationSetting::create([
               'user_id' => $user->id,
            ]);
        
            return response()->json([
                'status' => true,
                'message' => 'Gebruiker succesvol aangemaakt en ingelogd.',
                'data' => [
                    'id' => $user->id, 
                    'api_token' => $user->api_token,
                    'email' => $user->email,
                    'email_verified' => $user->email_verified,
                    'profile_created_status' => $user->profile_created_status,
                    'profile_created_at' => $user->profile_created_at,
                    'is_social' => $user->is_social,
                    'created_at' => $user->created_at, 
                    'updated_at' => $user->updated_at, 
                ],
            ], 200);
        }
    }

    // login via apple

    public function appleLogIn(AppleLoginRequest $request)
    {
        $Name = $request->name;
        $email = $request->email;
        $appleToken = $request->apple_token;
        $deviceToken = $request->device_token;
        $deviceType = $request->device_type;
        $deviceId = $request->device_id;
        $fcmToken = $request->fcm_token;
        $created = now();
    
        // Generate a secure API token
        $apiToken = bin2hex(random_bytes(30));
    
        // Check if user exists by email
        $user = User::where('email', $email)->first();
    
        if ($user) {
            // Update existing user
            $user->name = $Name;
            $user->apple_token = $appleToken;
            $user->api_token = $apiToken; // Updated token generation
            $user->device_token = $deviceToken;
            $user->device_type = $deviceType;
            $user->device_id = $deviceId;
            $user->fcm_token =  $fcmToken;
            $user->is_social = 3;
            $user->email_verified = 1;
            $user->save();
    
            return response()->json([
                'status' => true,
                'message' => 'Gebruiker is succesvol ingelogd.',
                'data' => [
                    'id' => $user->id, 
                    'api_token' => $user->api_token,
                     'email' => $user->email,
                    'email_verified' => $user->email_verified,
                    'profile_created_status' => $user->profile_created_status,
                    'profile_created_at' => $user->profile_created_at,
                    'is_social' => $user->is_social,
                    'created_at' => $user->created_at, 
                    'updated_at' => $user->updated_at, 
                ],
            ], 200);
        }else {
            // Create new user
            $user = User::create([
                'name' => $Name,
                'email' => $email,
                'apple_token' => $appleToken,
                'api_token' => $apiToken, 
                'device_token' => $deviceToken,
                'is_social' => 3,
                'email_verified'=> 1,
                'device_type' => $deviceType,
                'device_id' => $deviceId,
                'fcm_token' => $fcmToken,
                'created_at' => $created,
            ]);

            $defaultSchedule = [
                "wakeup" => ["title" => "Wake-Up", "startTime" => "07:00 AM", "endTime" => "08:00 AM"],
                "breakfast" => ["title" => "Breakfast", "startTime" => "08:30 AM", "endTime" => "09:00 AM"],
                "lunch" => ["title" => "Lunch", "startTime" => "12:00 PM", "endTime" => "01:00 PM"],
                "dinner" => ["title" => "Dinner", "startTime" => "06:00 PM", "endTime" => "07:00 PM"],
                "exercise" => ["title" => "Exercise", "startTime" => "05:00 PM", "endTime" => "06:00 PM"],
               "sleep" => ["title" => "Sleep", "startTime" => "10:00 PM", "endTime" => "11:00 PM"],
            ];


            DailySchedule::create([
              'user_id' => $user->id,
             'weekdays_schedule' => json_encode($defaultSchedule),
             'weekend_schedule' => json_encode($defaultSchedule),
            ]);

            NotificationSetting::create([
               'user_id' => $user->id,
            ]);
    
            return response()->json([
                'status' => true,
                'message' => 'Gebruiker succesvol aangemaakt en ingelogd.',
                'data' => [
                    'id' => $user->id, 
                    'api_token' => $user->api_token,
                     'email' => $user->email,
                    'email_verified' => $user->email_verified,
                    'profile_created_status' => $user->profile_created_status,
                    'profile_created_at' => $user->profile_created_at,
                    'is_social' => $user->is_social,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ],
            ], 200);
        }
    }

   

    // logout api
    public function logout(Request $request)
    {
        // Get the currently authenticated user via the 'api' guard
        $user = Auth::guard('api')->user();
        // If the user is not authenticated, return an error
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => __('auth.change_password.user_not_authenticated'),
            ], 401);
        }
        // Eager load the related details
        $user=$user->load('details');

        // Set the language based on user preference before using translations
        if ($user) {
            App::setLocale($user->details->user_lang ? $user->details->user_lang : config('app.locale')); // e.g., 'en' or 'nl'
        }
    
        if ($user) {
            // Clear all tokens
            $user->api_token = null;
            $user->google_token = null; // Clear Google token
            $user->facebook_token = null; // Clear Facebook token
            $user->apple_token = null; // Clear Apple token
    
            // Optionally clear device-specific information
            $user->device_token = null;
            $user->device_type = null;
            $user->device_id = null;
            $user->fcm_token = null;
            $user->is_social = 0;
            // Save changes to the database
            $user->save();
    
            return response()->json([
                'status' => true,
                'message' => __('auth.logout.logout_success'),
            ], 200);
        }
    
        return response()->json([
            'status' => false,
            'message' => 'Gebruiker niet geauthenticeerd.',
        ], 401);
    }



    // ******************************************************************************* new onboarding start **********************************************************


    public function RegisterOnboading(RegisterOnboadingRequest $request){

      // Get the currently authenticated user using Bearer token and custom guard
        $user = Auth::guard('api')->user(); 

        // If the user is not authenticated, return an error
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Gebruiker niet geauthenticeerd.',
            ], 401);
        }

        try {
            $dob = $request->dob ? Carbon::createFromFormat('d-m-Y', $request->dob)->format('Y-m-d') : null;

        //     $desiredWeight = (float) $request->desired_weight ?? 0;
        //     $currentWeight = (float) $user->weight;

        //    $targetGoalValue = $currentWeight - $desiredWeight;

        // Extract number and unit from weight (e.g., "10 Kg", "500 gm")
        preg_match('/([\d.]+)\s*([a-zA-Z]+)/', $request->weight, $matches);
        $currentWeightValue = isset($matches[1]) ? (float) $matches[1] : 0;
        $unit = isset($matches[2]) ? $matches[2] : 'kg';

        $desiredWeight = (float) ($request->desired_weight ?? 0);
            
        // Calculate absolute weight difference and append unit
        $weightDiffernceValue = abs($currentWeightValue - $desiredWeight);
        $weightDiffernceValueWithUnit = $weightDiffernceValue . ' ' . $unit;
        // Calculate BMI
        $heightInMeters = $this->convertToNumericHeight($request->height);
        $weightInKg = $this->convertToNumericWeight($request->weight);
        // dd($heightInMeters,$weightInKg );
        if (is_numeric($heightInMeters) && is_numeric($weightInKg)) {
            $bmi = $weightInKg / ($heightInMeters * $heightInMeters);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Invalid height or weight provided.',
            ], 400);
        }

          // Fill user onboarding data
           $user->gender = $request->gender;
           $user->activitylevel = $request->activitylevel ?? [];
           $user->height = $request->height;
           $user->weight = $request->weight;
           $user->dob = $dob;
           $user->goal = $request->goal;
           $user->target_goal_value = $request->target_goal_value;
           $user->timespan = $request->timespan;
           $user->food_preferences = $request->food_preferences;
           $user->bmi = $bmi;
            if (is_null($user->profile_created_at)) {
                $user->profile_created_at = now(); // Set to current timestamp
                $user->profile_created_status = '1'; // Set status to 'first_time'
            }
         
          // Save to user_details table
           UserDetail::updateOrCreate(
              ['user_id' => $user->id],
              [
                'desired_weight' => $request->desired_weight ?? null,
                'weight_difference' => $weightDiffernceValueWithUnit ?? null,
                'blocker' => $request->blocker ?? [],
                'goal_accomplishment' => $request->goal_accomplishment ?? [],
              ]
            );
            // Save weight history
            DB::table('weight_histories')->insert([
                'user_id' => $user->id,
                'current_weight' => $request->weight,
                'weight_updated_at' => now(), // Set the current timestamp
                'created_at' => now(), // Set the current timestamp
                'updated_at' => now(), // Set the current timestamp
            ]);

                        // Determine subscription plan based on goal
            $subscriptionPlanId = 1; // Default plan ID

            if ($request->goal == 'Gewicht winnen') {
                $subscriptionPlanId = 1;
            } elseif ($request->goal == 'Gewicht verliezen') {
                $subscriptionPlanId = 2;
            }
            // Create free trial subscription
            Subscription::create([
                'user_id' => $user->id,
                'subscription_plan_id' =>$subscriptionPlanId, // Free Trial Plan ID
                // 'payment_type' => 'free', // optional if you want to track payment type
                // 'starts_at' => Carbon::now(),
                // 'ends_at' => Carbon::now()->addDays(7),
                // 'status' => 'free',
                // 'is_free' => 1,
            ]);

          $user->save();
           $userDetails = UserDetail::where('user_id', $user->id)->first();
            return response()->json([
                'status' => true,
                'message' => 'Gebruiker succesvol geregistreerd',
                'data' => [
                    'id' => $user->id,
                    'api_token' => $user->api_token,
                    'dob' => $user->dob,
                    'gender' => $user->gender,
                    'height' => $user->height,
                    'weight' => $user->weight,
                    'desired_weight' => $userDetails->desired_weight ?? null,
                    'weight_difference' => $userDetails->weight_difference ?? null,
                    'bmi' => $user->bmi,
                    'goal' => $user->goal,
                    'target_goal_value' => $user->target_goal_value,
                    'timespan' => $user->timespan,
                    'activitylevel' => $user->activitylevel,
                    'food_preferences' => $user->food_preferences,
                    'is_social' => $user->is_social,
                    'blocker' => $userDetails->blocker ?? [],
                    'goal_accomplishment' => $userDetails->goal_accomplishment ?? [],
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ],
            ], 200);
        } catch (\Exception $e) {
          return response()->json([
             'status' => false,
             'message' => 'Fout bij het opslaan van onboardinggegevens.',
             'error' => $e->getMessage(),
            ], 500);
        }

    }



    private function convertToNumericHeight($height)
    {
        // Extract numeric value and unit from height
        preg_match('/([\d.]+)\s*(ft|in|cm|m)/i', $height, $matches);

        if (!$matches) {
            return 0; // Return 0 if no valid match is found
        }

        $value = $matches[1]; // Numeric part
        $unit = strtolower($matches[2]); // Unit (Feet, Inches, etc.)

        // Convert height to meters
        switch ($unit) {
            case 'ft':
                return floatval($value) * 0.3048; // Convert feet to meters
            case 'in':
                return floatval($value) * 0.0254; // Convert inches to meters
            case 'cm':
                return floatval($value) / 100; // Convert centimeters to meters
            case 'm':
                return floatval($value); // Already in meters
            default:
                return 0; // Return 0 if unit is unknown
        }
    }

    private function convertToNumericWeight($weight)
    {
        // Extract numeric value and unit from weight
        preg_match('/([\d.]+)\s*(kg|lbs|Pounds)/i', $weight, $matches);

        if (!$matches) {
            return 0; // Return 0 if no valid match is found
        }

        $value = $matches[1]; // Numeric part
        $unit = strtolower($matches[2]); // Unit (Kg, lbs, etc.)

        // Convert weight to kilograms
        switch ($unit) {
            case 'kg':
                return floatval($value); // Already in kilograms
            case 'lbs':
            case 'Pounds':
                return floatval($value) * 0.453592; // Convert pounds to kilograms
            default:
                return 0; // Return 0 if unit is unknown
        }
    }


    public function storeLocation(LocationRequest $request){
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Gebruiker niet geauthenticeerd.',
            ], 401);
        }

        if ($user) {
            $user->load('details'); // or 'loads.details' if 'loads' is a relationship
        }
        // If user_details record exists
        if ($user->details) {
            $user->details->latitude = $request->latitude;
            $user->details->longitude = $request->longitude;
            $user->details->timezone = $request->timezone;
            $user->details->save();

            return response()->json([
                'status' => true,
                'message' => 'Locatie succesvol opgeslagen.',
            ]);
        }

        // If user_details doesn't exist, optionally create one
        $user->details()->create([
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'timezone' => $request->timezone,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Locatie succesvol aangemaakt.',
        ]);
    }


    public function selectLanguage(Request $request){

      $validationRules = [
            'device_id' => 'required|string',
            'device_type' => 'required|string',
            'user_lang' => 'required|in:en,nl',
        ];

        $validationMessages = [
            'device_id.required' => 'Device ID is required.',
            'device_type.required' => 'Device type is required.',
            'user_lang.required' => 'Language is required.',
            'user_lang.in' => 'Language must be "en" or "nl".',
        ];

        $validator = Validator::make($request->all(), $validationRules, $validationMessages);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        // Create or update based on device_id
        $userLang = UserLang::updateOrCreate(
            ['device_id' => $request->device_id], // condition
            [
                'device_type' => $request->device_type,
                'user_lang'   => $request->user_lang,
            ]
        );

        return response()->json([
            'status' => true,
            'message' => 'Language preference saved successfully.',
            'data' => $userLang,
        ], 201);
    }
    
    





}
