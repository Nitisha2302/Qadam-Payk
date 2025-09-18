<?php

namespace App\Services;

use App\Models\User;
use App\Models\NotificationData;
use Carbon\Carbon;
use Carbon\CarbonTimeZone;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use App\Models\notification_settings;
use Google\Client;
use Illuminate\Support\Facades\Mail;
use App\Mail\MealNotificationMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\NotificatonSettingRequest;
use App\Models\MenuList;


use App\Models\HealthKit;

class NotificationService
{

    //  notification for morning wakeup  about weight 
    
    public function updateWeightReminder()
    {
        $token = env("CRON_MEAL_TOKEN");

        if ($token !== env('CRON_MEAL_TOKEN')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $users = User::with(['notificationSetting', 'latestWeightHistory', 'dailySchedule','details'])->get();

        if ($users->isEmpty()) {
            return response()->json(['message' => 'No users to notify']);
        }

        $tokens = [];
        
        foreach ($users as $user) {
           
            $setting = $user->notificationSetting;

            // Skip if weight reminders are disabled
            if ($setting && $setting->weight_notify == 1) {
                continue;
            }

            // Get user schedule
            $schedule = $user->dailySchedule;
            if (!$schedule) {
                continue;
            }

            $userTimezone = $user->details->timezone ?? 'UTC'; // e.g., Asia/Kolkata
            $nowInUserTZ = Carbon::now(new CarbonTimeZone($userTimezone));
            $currentTime = $nowInUserTZ->format('H:i');
            $currentDay = $nowInUserTZ->format('l'); // Monday, etc.


            $isWeekend = in_array($currentDay, ['Saturday', 'Sunday']);
            $scheduleJson = $isWeekend && $schedule->weekendSchedule_status == 0
                ? $schedule->weekend_schedule
                : $schedule->weekdays_schedule;

            $decoded = json_decode($scheduleJson, true);
          
            if (!isset($decoded['wakeup']['startTime'])) continue;

            // Parse the stored AM/PM time into 24-hour format in user’s timezone
            try {
                $startTime = Carbon::createFromFormat('h:i A', $decoded['wakeup']['startTime'], $userTimezone)->format('H:i');
            } catch (\Exception $e) {
                continue; // skip invalid time format
            }

            // Compare current time in user's time zone with scheduled time
            if ($currentTime !== $startTime) {
                continue;
            }

            // Check if already sent today
            $alreadySent = Notification::where('user_id', $user->id)
                ->where('notification_type', 14)
                ->whereDate('notification_created_at', now()->toDateString())
                ->exists();

            if ($alreadySent) {
                continue;
            }

            // Only include valid FCM tokens
            if (!empty($user->fcm_token) && is_string($user->fcm_token)) {
                $tokens[$user->fcm_token] = [
                    'user_id' => $user->id,
                    'device_type' => strtolower($user->device_type ?? ''),
                    'name' => $user->name,
                ];
            }
        }

        if (empty($tokens)) {
            return response()->json([
                'message' => 'No users due for weight update reminders at this time.'
            ], 201);
        }

        $notification = \App\Models\NotificationData::where('notification_type', '14')->first();

        if (!$notification) {
            return response()->json([
                'status' => false,
                'error' => 'Notification content not found.'
            ], 201);
        }

        return $this->sendDynamicNotification($tokens, $notification);
    }

    // notification for ready for daily plan

    public function readyForTodayPaln()
    {
        $token = env("CRON_MEAL_TOKEN");

        if ($token !== env('CRON_MEAL_TOKEN')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $users = User::with(['notificationSetting', 'latestWeightHistory', 'dailySchedule', 'details'])->get();

        if ($users->isEmpty()) {
            return response()->json(['message' => 'No users to notify']);
        }

        $notification = \App\Models\NotificationData::where('notification_type', '15')->first();

        if (!$notification) {
            return response()->json([
                'status' => false,
                'error' => 'Notification content not found.'
            ], 201);
        }

        $tokens = [];

        foreach ($users as $user) {
            $schedule = $user->dailySchedule;
            $timezone = $user->details->timezone ?? 'UTC';

            if (!$schedule || !$timezone) {
                continue;
            }

            $now = Carbon::now(new \DateTimeZone($timezone));
            $day = $now->format('l');
            $isWeekend = in_array($day, ['Saturday', 'Sunday']);

            $details = $user->details;
            $todayeatenCalories = $details->total_calory ?? 0;
            $todayburnCalories = $user->total_burn_calory ?? 0;

            $scheduleJson = $isWeekend && $schedule->weekendSchedule_status == 0
                ? $schedule->weekend_schedule
                : $schedule->weekdays_schedule;

            $decoded = json_decode($scheduleJson, true);

            if (!isset($decoded['wakeup']['startTime'])) {
                continue;
            }

            try {
                $wakeTime = Carbon::createFromFormat('h:i A', $decoded['wakeup']['startTime'], $timezone);
                $targetTime = $wakeTime->copy()->addMinutes(30)->format('H:i');
                $nowTime = $now->format('H:i');

                if ($nowTime === $targetTime) {
                  
                    // Check if already sent today
                    $alreadySent = Notification::where('user_id', $user->id)
                        ->where('notification_type', 15)
                        ->whereDate('notification_created_at', now()->toDateString())
                        ->exists();

                    if ($alreadySent) {
                        continue;
                    }

                    if (!empty($user->fcm_token)) {
                        $bmi = $details->bmi ?? 0;
                        $targetBMI = $details->target_bmi ?? 0;
                        // $eat = $todayeatenCalories;
                        // $burn = $todayburnCalories;
                        $eat = round((float) $todayeatenCalories);
                       $burn = round((float) $todayburnCalories);


                        $tokens[$user->fcm_token] = [
                            'user_id' => $user->id,
                            'device_type' => strtolower($user->device_type ?? ''),
                            'description_vars' => [
                                'eat' => $eat,
                                'burn' => $burn,
                                'bmi' => $bmi,
                                'targetBMI' => $targetBMI,
                            ],
                            'name' => $user->first_name ?? '',
                        ];
                    }
                } else {
                   
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        if (empty($tokens)) {
            return response()->json([
                'message' => 'No users due for plan reminders at this time.'
            ], 201);
        }

        return $this->sendDynamicNotification($tokens, $notification);
    }

    // notification for motion alert

    // public function sendEnergyBurnReminderAfterLunch()
    // {
    //     $token = env("CRON_MEAL_TOKEN");

    //     if ($token !== env('CRON_MEAL_TOKEN')) {
    //         return response()->json(['error' => 'Unauthorized'], 401);
    //     }

    //     $users = User::with(['notificationSetting', 'dailySchedule', 'details'])->get();

    //     if ($users->isEmpty()) {
    //         return response()->json(['message' => 'No users found.']);
    //     }

    //     $notification = \App\Models\NotificationData::where('notification_type', 16)->first();

    //     if (!$notification) {
    //         return response()->json(['error' => 'Notification content not found.'], 201);
    //     }

    //     $tokens = [];

    //     foreach ($users as $user) {
    //         $timezone = $user->details->timezone ?? 'UTC';
    //         $schedule = $user->dailySchedule;

    //         if (!$schedule) {
    //             continue;
    //         }

    //         try {
    //             $now = Carbon::now(new \DateTimeZone($timezone));
    //             $today = $now->format('Y-m-d');
    //             $todayFormatted = Carbon::now(new \DateTimeZone($timezone))->format('d-m-Y');
    //             $day = $now->format('l'); // 'Monday', 'Tuesday', etc.
    //             $isWeekend = in_array($day, ['Saturday', 'Sunday']);

    //             $scheduleJson = $isWeekend && $schedule->weekendSchedule_status == 0
    //                 ? $schedule->weekend_schedule
    //                 : $schedule->weekdays_schedule;

    //             $decoded = json_decode($scheduleJson, true);

    //             if (!isset($decoded['lunch']['startTime'])) {
    //                 continue;
    //             }

    //             // Parse lunch start time and add 2 hours
    //             $lunchStart = Carbon::createFromFormat('h:i A', $decoded['lunch']['startTime'], $timezone);
    //             $reminderTime = $lunchStart->copy()->addHours(2);

    //             // Check if it's time to send (within 5 min window to allow for minor delays)
    //             if ($now->diffInMinutes($reminderTime) <= 5 && $now->greaterThanOrEqualTo($reminderTime)) {
                    
    //                 $healthData = \App\Models\HealthKit::where('user_id', $user->id)
    //                     ->whereDate('date', $todayFormatted)
    //                     ->first();

    //                 if($healthData->energy_burn != 0 || $healthData->steps != 0){
    //                     continue;
    //                 }
                        
    //                 if ($healthData || $healthData->energy_burn == 0 || $healthData->steps == 0) {
    //                     if (!empty($user->fcm_token)) {
    //                         $tokens[$user->fcm_token] = [
    //                             'user_id' => $user->id,
    //                             'device_type' => strtolower($user->device_type ?? ''),
    //                             'name' => $user->first_name ?? '',
    //                         ];
    //                     }
    //                 }
    //             }
    //         } catch (\Exception $e) {
    //             \Log::error("Reminder error for user ID {$user->id}: " . $e->getMessage());
    //             continue;
    //         }
    //     }

    //     if (empty($tokens)) {
    //         return response()->json(['message' => 'No users to notify right now.'], 201);
    //     }

    //     return $this->sendDynamicNotification($tokens, $notification);
    // }

    public function sendEnergyBurnReminderAfterLunch()
    {
        $token = env("CRON_MEAL_TOKEN");

        if ($token !== env('CRON_MEAL_TOKEN')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $users = User::with(['notificationSetting', 'dailySchedule', 'details'])->get();

        if ($users->isEmpty()) {
            return response()->json(['message' => 'No users found.']);
        }

        $notification = \App\Models\NotificationData::where('notification_type', 16)->first();

        if (!$notification) {
            return response()->json(['error' => 'Notification content not found.'], 201);
        }

        $tokens = [];

        foreach ($users as $user) {
            $timezone = $user->details->timezone ?? 'UTC';
            $schedule = $user->dailySchedule;

            if (!$schedule) {
                continue;
            }

            try {
                $now = Carbon::now(new \DateTimeZone($timezone));
                $todayFormatted = $now->format('d-m-Y');
                $day = $now->format('l'); // 'Monday', 'Tuesday', etc.
                $isWeekend = in_array($day, ['Saturday', 'Sunday']);

                $scheduleJson = $isWeekend && $schedule->weekendSchedule_status == 0
                    ? $schedule->weekend_schedule
                    : $schedule->weekdays_schedule;

                $decoded = json_decode($scheduleJson, true);

                if (!isset($decoded['lunch']['startTime'])) {
                    continue;
                }

                // Parse lunch start time and add 2 hours
                $lunchStart = Carbon::createFromFormat('h:i A', $decoded['lunch']['startTime'], $timezone);
                $reminderTime = $lunchStart->copy()->addHours(2);

                // Check if it's time to send (within 5 min window)
                if ($now->diffInMinutes($reminderTime) <= 5 && $now->greaterThanOrEqualTo($reminderTime)) {
                    $healthData = \App\Models\HealthKit::where('user_id', $user->id)
                        ->where('date', $todayFormatted) // date is stored as VARCHAR
                        ->first();

                    if ($healthData) {
                        $steps = $healthData->steps;
                        $burn = $healthData->energy_burn;

                        // Only send if both are 0 or null
                        if ((is_null($steps) || $steps == 0) && (is_null($burn) || $burn == 0)) {
                           // Check if already sent today
                            $alreadySent = Notification::where('user_id', $user->id)
                                ->where('notification_type', 16)
                                ->whereDate('notification_created_at', now()->toDateString())
                                ->exists();

                            if ($alreadySent) {
                                \Log::info("Reminder already sent to user {$user->id} for {$todayFormatted}");
                                continue;
                            }
                            if (!empty($user->fcm_token)) {
                                $tokens[$user->fcm_token] = [
                                    'user_id' => $user->id,
                                    'device_type' => strtolower($user->device_type ?? ''),
                                    'name' => $user->first_name ?? '',
                                ];
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::error("Reminder error for user ID {$user->id}: " . $e->getMessage());
                continue;
            }
        }

        if (empty($tokens)) {
            return response()->json(['message' => 'No users to notify right now.'], 201);
        }

        return $this->sendDynamicNotification($tokens, $notification);
    }



    // notification for evenig alert

    public function sendEnergyBurnReminderOnEvening()
    {
        $token = env("CRON_MEAL_TOKEN");

        if ($token !== env('CRON_MEAL_TOKEN')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $users = User::with(['notificationSetting', 'dailySchedule', 'details'])->get();

        if ($users->isEmpty()) {
            return response()->json(['message' => 'No users found.']);
        }

        // Notification type 17 for evening energy burn reminder
        $notification = \App\Models\NotificationData::where('notification_type', 17)->first();

        if (!$notification) {
            return response()->json(['error' => 'Notification content not found.'], 201);
        }

        $tokens = [];

        foreach ($users as $user) {
            
            $timezone = $user->details->timezone ?? 'UTC';
            // Get user schedule
            $schedule = $user->dailySchedule;
            if (!$schedule) {
                continue;
            }

            try {
                $now = Carbon::now(new \DateTimeZone($timezone));
                $currentTime = $now->format('H:i');
                $today = Carbon::now(new \DateTimeZone($timezone))->format('d-m-Y');
                $day = $now->format('l'); // 'Monday', 'Tuesday', etc.
                $isWeekend = in_array($day, ['Saturday', 'Sunday']);


                $scheduleJson = $isWeekend && $schedule->weekendSchedule_status == 0
                    ? $schedule->weekend_schedule
                    : $schedule->weekdays_schedule;

                $decoded = json_decode($scheduleJson, true);

                if (!isset($decoded['lunch']['startTime'])) {
                    continue;
                }

                // Parse lunch start time and add 2 hours
                $lunchStart = Carbon::createFromFormat('h:i A', $decoded['lunch']['startTime'], $timezone);
                $reminderTime = $lunchStart->copy()->addHours(4);

                if ($now->diffInMinutes($reminderTime) <= 5 && $now->greaterThanOrEqualTo($reminderTime)) {
                
                    
                    $healthData = \App\Models\HealthKit::where('user_id', $user->id)->where('date', $today)
                        ->first(); 
                  
                    // $burnedToday = $healthData->energy_burn ?? 0;
                    $burnedToday = round((float)($healthData->energy_burn ?? 0));
                    $targetBurn = $user->total_burn_calory ?? 0;
                    preg_match('/([\d.]+)\s*([a-zA-Z]+)/', $targetBurn, $matches);
                    // $targetBurnValue = isset($matches[1]) ? (float) $matches[1] : 0;
                    $targetBurnValue = isset($matches[1]) ? round((float)$matches[1]) : 0;
                
                    $remainingCalories = max(0, $targetBurnValue - $burnedToday); // avoid negative

                    
                    $total_needed_calory = $user->details->total_calory;
                    preg_match('/([\d.]+)\s*([a-zA-Z]+)/', $total_needed_calory, $matches);
                    $total_needed_calory_value = isset($matches[1]) ? (int) round((float) $matches[1]) : 0;

                    $total_eaten_calory = (int) round((float) $user->details->today_eaten_calory);

                    // Ensure no negative calories
                    $remainingEatenCalories = max(0, $total_needed_calory_value - $total_eaten_calory);

                   

                    if ($burnedToday == 0 || $burnedToday < $targetBurnValue || $total_eaten_calory == 0 || $total_eaten_calory < $total_needed_calory_value) {

                        // Check if already sent today
                        $alreadySent = Notification::where('user_id', $user->id)
                            ->where('notification_type', 17)
                            ->whereDate('notification_created_at', now()->toDateString())
                            ->exists();

                        if ($alreadySent) {
                            continue;
                        }
                    
                        if (!empty($user->fcm_token)) {
                            $tokens[$user->fcm_token] = [
                                'user_id' => $user->id,
                                'device_type' => strtolower($user->device_type ?? ''),
                                'title' => $notification->notification_title,
                                'description_vars' => [
                                    'remaining_calories' => $remainingCalories,
                                    'remainingEatenCalories'=> $remainingEatenCalories,
                                ],
                                'name' => $user->first_name ?? '',
                                // 'remaining_calories' => $remainingCalories,
                            ];
                        }
                    } else {
                        continue;
                    }
                }
            } catch (\Exception $e) {
               continue;
            }
        }

        if (empty($tokens)) {
            return response()->json(['message' => 'No users to notify right now.'], 201);
        }

        return $this->sendDynamicNotification($tokens, $notification);
    }


    //  notification for target percenatge

    // public function sendAchieveTargetPercentageNotification(){
    //   $token = env("CRON_MEAL_TOKEN");

    //     if ($token !== env('CRON_MEAL_TOKEN')) {
    //         return response()->json(['error' => 'Unauthorized'], 401);
    //     }

    //     $users = User::with(['notificationSetting', 'dailySchedule', 'details'])->get();

    //     if ($users->isEmpty()) {
    //         return response()->json(['message' => 'No users found.']);
    //     }

    //     // Notification type 17 for evening energy burn reminder
    //     $notification = \App\Models\NotificationData::where('notification_type', 18)->first();

    //     if (!$notification) {
    //         return response()->json(['error' => 'Notification content not found.'], 201);
    //     }

    //     $tokens = [];

    //     foreach ($users as $user) {
    //         $timezone = $user->details->timezone ?? 'UTC';
    //         // Get user schedule
    //         $schedule = $user->dailySchedule;
    //         if (!$schedule) {
    //             continue;
    //         }

    //         try {
    //             $now = Carbon::now(new \DateTimeZone($timezone));
    //             $currentTime = $now->format('H:i');
    //             $today = $now->format('');
    //             $todayFormatted = Carbon::now(new \DateTimeZone($timezone))->format('d-m-Y');
    //             $day = $now->format('l'); // 'Monday', 'Tuesday', etc.
    //             $isWeekend = in_array($day, ['Saturday', 'Sunday']);

    //             $scheduleJson = $isWeekend && $schedule->weekendSchedule_status == 0
    //                 ? $schedule->weekend_schedule
    //                 : $schedule->weekdays_schedule;

    //             $decoded = json_decode($scheduleJson, true);

    //             if (!isset($decoded['dinner']['startTime'])) {
    //                 continue;
    //             }

    //             // Parse lunch start time and add 2 hours
    //             $lunchStart = Carbon::createFromFormat('h:i A', $decoded['dinner']['startTime'], $timezone);
    //             $reminderTime = $lunchStart->copy()->addHours(2);
    //              // Check if it's time to send (within 5 min window to allow for minor delays)
    //             if ($now->diffInMinutes($reminderTime) <= 5 && $now->greaterThanOrEqualTo($reminderTime)) {

    //                 // Check if already sent today
    //                 $alreadySent = Notification::where('user_id', $user->id)
    //                     ->where('notification_type', 18)
    //                     ->where('notification_created_at', now()->toDateString())
    //                     ->exists();

    //                 if ($alreadySent) {
    //                     continue;
    //                 }
                   
    //                 $healthData = \App\Models\HealthKit::where('user_id', $user->id)->where('date', $todayFormatted)
    //                     ->first();
    //                 $total_needed_calory = $user->details->total_calory;
    //                 preg_match('/([\d.]+)\s*([a-zA-Z]+)/', $total_needed_calory, $matches);
    //                 $total_needed_calory_value = isset($matches[1]) ? (float) $matches[1] : 0;
                    
    //                 $total_eaten_calory = $user->details->today_eaten_calory;
    //                 $total_eaten_calory = (float) $user->details->today_eaten_calory;
    //               // Calculate eaten  percentage
    //                 $eatenPercentage = 0;
    //                 if ($total_eaten_calory > 0) {
    //                     $eatenPercentage = (int)round(( $total_eaten_calory / $total_needed_calory_value) * 100, 2); // Rounded to 2 decimal places
    //                 }

                    
               
    //                 $burnedToday = $healthData->energy_burn ?? 0;
    //                 $targetBurn = $user->total_burn_calory ?? 0;
    //                 preg_match('/([\d.]+)\s*([a-zA-Z]+)/', $targetBurn, $matches);
    //                 $targetBurnValue = isset($matches[1]) ? (float) $matches[1] : 0;

    //                 // Calculate percentage
    //                 $burnPercentage = 0;
    //                 if ($targetBurnValue > 0) {
    //                     $burnPercentage = (int)round(($burnedToday / $targetBurnValue) * 100, 2); // Rounded to 2 decimal places
    //                 }

    //                 if ($burnPercentage <= 0 || $burnPercentage >= 100) {
    //                     Log::info("0 and 100 percentage skipped for user: {$user->id}");
    //                     continue;
    //                 }

    //                 if ($eatenPercentage <= 0 || $eatenPercentage >= 100) {
    //                     Log::info("0 and 100 percentage skipped for user: {$user->id}");
    //                     continue;
    //                 }
                   

    //                 $alreadySent = \App\Models\Notification::where('user_id', $user->id)
    //                     ->where('notification_type', 18) // use your correct type
    //                     ->whereDate('notification_created_at', Carbon::now($timezone)->toDateString())
    //                     ->exists();

    //                 if ($alreadySent) {
    //                     Log::info("Duplicate notification skipped for user: {$user->id}");
    //                     continue;
    //                 }
                                

    //                 if ($eatenPercentage > 0 && $burnPercentage > 0) {
    //                     if (!empty($user->fcm_token)) {
    //                         $tokens[$user->fcm_token] = [
    //                             'user_id' => $user->id,
    //                             'device_type' => strtolower($user->device_type ?? ''),
    //                             'title' => $notification->notification_title,
    //                             'description_vars' => [
    //                                 'burnPercentage' => $burnPercentage,
    //                                 'eatenPercentage' =>$eatenPercentage,
    //                             ],
    //                             'name' => $user->first_name ?? '',
    //                             // 'remaining_calories' => $remainingCalories,
    //                         ];
    //                     }
    //                 } else {
    //                     continue;
    //                 }
    //             }
    //         } catch (\Exception $e) {
    //             continue;
    //         }
    //     }

    //     if (empty($tokens)) {
    //         return response()->json(['message' => 'No users to notify right now.'], 201);
    //     }

    //     return $this->sendDynamicNotification($tokens, $notification);
    // }

    // also send with 0 
    
    public function sendAchieveTargetPercentageNotification(){
      $token = env("CRON_MEAL_TOKEN");

        if ($token !== env('CRON_MEAL_TOKEN')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $users = User::with(['notificationSetting', 'dailySchedule', 'details'])->get();

        if ($users->isEmpty()) {
            return response()->json(['message' => 'No users found.']);
        }

        // Notification type 17 for evening energy burn reminder
        $notification = \App\Models\NotificationData::where('notification_type', 18)->first();

        if (!$notification) {
            return response()->json(['error' => 'Notification content not found.'], 201);
        }

        $tokens = [];

        foreach ($users as $user) {
            $timezone = $user->details->timezone ?? 'UTC';
            // Get user schedule
            $schedule = $user->dailySchedule;
            if (!$schedule) {
                continue;
            }

            try {
                $now = Carbon::now(new \DateTimeZone($timezone));
                $currentTime = $now->format('H:i');
                $today = $now->format('');
                $todayFormatted = Carbon::now(new \DateTimeZone($timezone))->format('d-m-Y');
                $day = $now->format('l'); // 'Monday', 'Tuesday', etc.
                $isWeekend = in_array($day, ['Saturday', 'Sunday']);

                $scheduleJson = $isWeekend && $schedule->weekendSchedule_status == 0
                    ? $schedule->weekend_schedule
                    : $schedule->weekdays_schedule;

                $decoded = json_decode($scheduleJson, true);

                if (!isset($decoded['dinner']['startTime'])) {
                    continue;
                }

                // Parse lunch start time and add 2 hours
                $lunchStart = Carbon::createFromFormat('h:i A', $decoded['dinner']['startTime'], $timezone);
                $reminderTime = $lunchStart->copy()->addHours(2);
                 // Check if it's time to send (within 5 min window to allow for minor delays)
                if ($now->diffInMinutes($reminderTime) <= 5 && $now->greaterThanOrEqualTo($reminderTime)) {

                    // Check if already sent today
                    $alreadySent = Notification::where('user_id', $user->id)
                        ->where('notification_type', 18)
                        ->where('notification_created_at', now()->toDateString())
                        ->exists();

                    if ($alreadySent) {
                        continue;
                    }
                   
                    $healthData = \App\Models\HealthKit::where('user_id', $user->id)->where('date', $todayFormatted)
                        ->first();
                    $total_needed_calory = $user->details->total_calory ?? '0';
                    preg_match('/([\d.]+)\s*([a-zA-Z]+)/', $total_needed_calory, $matches);
                    $total_needed_calory_value = isset($matches[1]) ? (float) $matches[1] : 0;

                    $total_eaten_calory = $user->details->today_eaten_calory;
                    $total_eaten_calory = (float) $user->details->today_eaten_calory;
                  // Calculate eaten  percentage
                    $eatenPercentage = 0;
                    if ($total_eaten_calory > 0) {
                        $eatenPercentage = (int)round(( $total_eaten_calory / $total_needed_calory_value) * 100, 2); // Rounded to 2 decimal places
                    }

                    
               
                    $burnedToday = $healthData->energy_burn ?? 0;
                    $targetBurn = $user->total_burn_calory ?? 0;
                    preg_match('/([\d.]+)\s*([a-zA-Z]+)/', $targetBurn, $matches);
                    $targetBurnValue = isset($matches[1]) ? (float) $matches[1] : 0;

                    // Calculate percentage
                    $burnPercentage = 0;
                    if ($targetBurnValue > 0) {
                        $burnPercentage = (int)round(($burnedToday / $targetBurnValue) * 100, 2); // Rounded to 2 decimal places
                    }

                    if ($burnPercentage <= 0 ) {
                        Log::info("0 and 100 percentage skipped for user: {$user->id}");
                        continue;
                    }

                    if ($eatenPercentage <= 0) {
                        Log::info("0 and 100 percentage skipped for user: {$user->id}");
                        continue;
                    }
                   

                    $alreadySent = \App\Models\Notification::where('user_id', $user->id)
                        ->where('notification_type', 18) // use your correct type
                        ->whereDate('notification_created_at', Carbon::now($timezone)->toDateString())
                        ->exists();

                    if ($alreadySent) {
                        Log::info("Duplicate notification skipped for user: {$user->id}");
                        continue;
                    }
                                

                    if ($eatenPercentage > 0 && $burnPercentage > 0) {
                        if (!empty($user->fcm_token)) {
                            $tokens[$user->fcm_token] = [
                                'user_id' => $user->id,
                                'device_type' => strtolower($user->device_type ?? ''),
                                'title' => $notification->notification_title,
                                'description_vars' => [
                                    'burnPercentage' => $burnPercentage,
                                    'eatenPercentage' =>$eatenPercentage,
                                ],
                                'name' => $user->first_name ?? '',
                                // 'remaining_calories' => $remainingCalories,
                            ];
                        }
                    } else {
                        continue;
                    }
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        if (empty($tokens)) {
            return response()->json(['message' => 'No users to notify right now.'], 201);
        }

        return $this->sendDynamicNotification($tokens, $notification);
    }

    // notification for coplete burn target 

    public function sendAchieveTotalTargetNotification(){
       $token = env("CRON_MEAL_TOKEN");
       
        if ($token !== env('CRON_MEAL_TOKEN')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $users = User::with(['notificationSetting', 'dailySchedule', 'details'])->get();

        if ($users->isEmpty()) {
            return response()->json(['message' => 'No users found.']);
        }

        // Notification type 17 for evening energy burn reminder
        $notification = \App\Models\NotificationData::where('notification_type', 21)->first();
       
        if (!$notification) {
            return response()->json(['error' => 'Notification content not found.'], 201);
        }

        $tokens = [];

        foreach ($users as $user) {
            $timezone = $user->details->timezone ?? 'UTC';

            try {
                $now = Carbon::now(new \DateTimeZone($timezone));
                $currentTime = $now->format('H:i');
                $today = Carbon::now(new \DateTimeZone($timezone))->format('d-m-Y');

                
                $healthData = \App\Models\HealthKit::where('user_id', $user->id)->where('date', $today)
                    ->first();
                if (!$healthData) {
                    continue;
                }
                
                $burnedToday = $healthData->energy_burn ?? 0;
                $targetBurn = $user->total_burn_calory ?? 0;
                preg_match('/([\d.]+)\s*([a-zA-Z]+)/', $targetBurn, $matches);
                $targetBurnValue = isset($matches[1]) ? (float) $matches[1] : 0;
                

                if ((int) $burnedToday == (int) $targetBurnValue) {
                    // Check if already sent today
                    $alreadySent = Notification::where('user_id', $user->id)
                        ->where('notification_type', 21)
                        ->whereDate('notification_created_at', now()->toDateString())
                        ->exists();

                    if ($alreadySent) {
                        continue;
                    }
                    if (!empty($user->fcm_token)) {
                        $tokens[$user->fcm_token] = [
                            'user_id' => $user->id,
                            'device_type' => strtolower($user->device_type ?? ''),
                            'title' => $notification->notification_title,
                            'description_vars' => $notification->notification_description,
                            'name' => $user->first_name ?? '',
                            // 'remaining_calories' => $remainingCalories,
                        ];
                    }
                } else {
                    continue;
                }
                
            } catch (\Exception $e) {
               continue;
            }
        }

        if (empty($tokens)) {
            return response()->json(['message' => 'No users to notify right now.'], 201);
        }

        return $this->sendDynamicNotification($tokens, $notification);
    }

    // api for breakfast notification

    // public function sendBreakfastReminder()
    // {
    //     $token = env("CRON_MEAL_TOKEN");

    //     if ($token !== env('CRON_MEAL_TOKEN')) {
    //         return response()->json(['error' => 'Unauthorized'], 401);
    //     }

    //     $users = User::with(['notificationSetting', 'latestWeightHistory', 'dailySchedule','details'])->get();

    //     if ($users->isEmpty()) {
    //         return response()->json(['message' => 'No users to notify']);
    //     }

    //     $tokens = [];
        
    //     foreach ($users as $user) {
        
    //         $setting = $user->notificationSetting;

    //         // Skip if weight reminders are disabled
    //         if ($setting && $setting->weight_notify == 1) {
    //             continue;
    //         }

    //         // Get user schedule
    //         $schedule = $user->dailySchedule;
    //         if (!$schedule) {
    //             continue;
    //         }

    //         $userTimezone = $user->details->timezone ?? 'UTC'; // e.g., Asia/Kolkata
    //         $nowInUserTZ = Carbon::now(new CarbonTimeZone($userTimezone));
    //         $currentTime = $nowInUserTZ->format('H:i');
    //         $currentDay = $nowInUserTZ->format('l'); // Monday, etc.


    //         $isWeekend = in_array($currentDay, ['Saturday', 'Sunday']);
    //         $scheduleJson = $isWeekend && $schedule->weekendSchedule_status == 0
    //             ? $schedule->weekend_schedule
    //             : $schedule->weekdays_schedule;

    //         $decoded = json_decode($scheduleJson, true);
        
    //         if (!isset($decoded['breakfast']['startTime'])) continue;

    //         // Parse the stored AM/PM time into 24-hour format in user’s timezone
    //         try {
    //             $startTime = Carbon::createFromFormat('h:i A', $decoded['breakfast']['startTime'], $userTimezone)->format('H:i');
    //         } catch (\Exception $e) {
 
    //             continue; // skip invalid time format
    //         }

    //         // Compare current time in user's time zone with scheduled time
    //         if ($currentTime !== $startTime) {
               
    //             continue;
    //         }
            
    //         // ✅ Check for duplicate (already sent today)
    //         $alreadySent = \App\Models\Notification::where('user_id', $user->id)
    //             ->where('notification_type', 8)
    //             ->whereDate('notification_created_at', now($userTimezone)->toDateString())
    //             ->exists();

    //         if ($alreadySent) {
    //             continue;
    //         }

    //         // Only include valid FCM tokens
    //         if (!empty($user->fcm_token) && is_string($user->fcm_token)) {
               
    //             $tokens[$user->fcm_token] = [
    //                 'user_id' => $user->id,
    //                 'device_type' => strtolower($user->device_type ?? ''),
    //                 'name' => $user->name,
    //             ];
    //         }
    //     }

    //     if (empty($tokens)) {
    //         return response()->json([
    //             'message' => 'No users due for weight update reminders at this time.'
    //         ], 201);
    //     }

    //     $notification = \App\Models\NotificationData::where('notification_type', '8')->first();

    //     if (!$notification) {
    //         return response()->json([
    //             'status' => false,
    //             'error' => 'Notification content not found.'
    //         ], 201);
    //     }

    //     return $this->sendDynamicNotification($tokens, $notification);
    // }

    // api for lunch notification

    // public function sendLunchReminder()
    // {
    //     $token = env("CRON_MEAL_TOKEN");

    //     if ($token !== env('CRON_MEAL_TOKEN')) {
    //         return response()->json(['error' => 'Unauthorized'], 401);
    //     }

    //     $users = User::with(['notificationSetting', 'latestWeightHistory', 'dailySchedule','details'])->get();

    //     if ($users->isEmpty()) {
    //         return response()->json(['message' => 'No users to notify']);
    //     }

    //     $tokens = [];
        
    //     foreach ($users as $user) {
        
    //         $setting = $user->notificationSetting;

    //         // Skip if weight reminders are disabled
    //         if ($setting && $setting->weight_notify == 1) {
               
    //             continue;
    //         }

    //         // Get user schedule
    //         $schedule = $user->dailySchedule;
    //         if (!$schedule) {
                
    //             continue;
    //         }

    //         $userTimezone = $user->details->timezone ?? 'UTC'; // e.g., Asia/Kolkata
    //         $nowInUserTZ = Carbon::now(new CarbonTimeZone($userTimezone));
    //         $currentTime = $nowInUserTZ->format('H:i');
    //         $currentDay = $nowInUserTZ->format('l'); // Monday, etc.


    //         $isWeekend = in_array($currentDay, ['Saturday', 'Sunday']);
    //         $scheduleJson = $isWeekend && $schedule->weekendSchedule_status == 0
    //             ? $schedule->weekend_schedule
    //             : $schedule->weekdays_schedule;

    //         $decoded = json_decode($scheduleJson, true);
        
    //         if (!isset($decoded['lunch']['startTime'])) continue;

    //         // Parse the stored AM/PM time into 24-hour format in user’s timezone
    //         try {
    //             $startTime = Carbon::createFromFormat('h:i A', $decoded['lunch']['startTime'], $userTimezone)->format('H:i');
    //         } catch (\Exception $e) {
    //             continue; // skip invalid time format
    //         }

    //         // Compare current time in user's time zone with scheduled time
    //         if ($currentTime !== $startTime) {
    //             continue;
    //         }

    //         // ✅ Check for duplicate (already sent today)
    //         $alreadySent = \App\Models\Notification::where('user_id', $user->id)
    //             ->where('notification_type', 10)
    //             ->whereDate('notification_created_at', now($userTimezone)->toDateString())
    //             ->exists();

    //         if ($alreadySent) {
    //             continue;
    //         }

    //         // Only include valid FCM tokens
    //         if (!empty($user->fcm_token) && is_string($user->fcm_token)) {
    //             $tokens[$user->fcm_token] = [
    //                 'user_id' => $user->id,
    //                 'device_type' => strtolower($user->device_type ?? ''),
    //                 'name' => $user->name,
    //             ];
    //         }
    //     }

    //     if (empty($tokens)) {
    //         return response()->json([
    //             'message' => 'No users due for weight update reminders at this time.'
    //         ], 201);
    //     }

    //     $notification = \App\Models\NotificationData::where('notification_type', '10')->first();

    //     if (!$notification) {
    //         return response()->json([
    //             'status' => false,
    //             'error' => 'Notification content not found.'
    //         ], 201);
    //     }

    //     return $this->sendDynamicNotification($tokens, $notification);
    // }

    // api for dinner notification

    // public function sendDinnerReminder()
    // {
    //     $token = env("CRON_MEAL_TOKEN");

    //     if ($token !== env('CRON_MEAL_TOKEN')) {
    //         return response()->json(['error' => 'Unauthorized'], 401);
    //     }

    //     $users = User::with(['notificationSetting', 'latestWeightHistory', 'dailySchedule','details'])->get();

    //     if ($users->isEmpty()) {
    //         return response()->json(['message' => 'No users to notify']);
    //     }

    //     $tokens = [];
        
    //     foreach ($users as $user) {
        
    //         $setting = $user->notificationSetting;

    //         // Skip if weight reminders are disabled
    //         if ($setting && $setting->weight_notify == 1) {
    //             continue;
    //         }

    //         // Get user schedule
    //         $schedule = $user->dailySchedule;
    //         if (!$schedule) {
    //             continue;
    //         }

    //         $userTimezone = $user->details->timezone ?? 'UTC'; // e.g., Asia/Kolkata
    //         $nowInUserTZ = Carbon::now(new CarbonTimeZone($userTimezone));
    //         $currentTime = $nowInUserTZ->format('H:i');
    //         $currentDay = $nowInUserTZ->format('l'); // Monday, etc.


    //         $isWeekend = in_array($currentDay, ['Saturday', 'Sunday']);
    //         $scheduleJson = $isWeekend && $schedule->weekendSchedule_status == 0
    //             ? $schedule->weekend_schedule
    //             : $schedule->weekdays_schedule;

    //         $decoded = json_decode($scheduleJson, true);
        
    //         if (!isset($decoded['dinner']['startTime'])) continue;

    //         // Parse the stored AM/PM time into 24-hour format in user’s timezone
    //         try {
    //             $startTime = Carbon::createFromFormat('h:i A', $decoded['dinner']['startTime'], $userTimezone)->format('H:i');
    //         } catch (\Exception $e) {
    //             continue; // skip invalid time format
    //         }

    //         // Compare current time in user's time zone with scheduled time
    //         if ($currentTime !== $startTime) {
    //             continue;
    //         }

    //         // ✅ Check for duplicate (already sent today)
    //         $alreadySent = \App\Models\Notification::where('user_id', $user->id)
    //             ->where('notification_type', 13)
    //             ->whereDate('notification_created_at', now($userTimezone)->toDateString())
    //             ->exists();

    //         if ($alreadySent) {
    //             continue;
    //         }

    //         // Only include valid FCM tokens
    //         if (!empty($user->fcm_token) && is_string($user->fcm_token)) {
    //             $tokens[$user->fcm_token] = [
    //                 'user_id' => $user->id,
    //                 'device_type' => strtolower($user->device_type ?? ''),
    //                 'name' => $user->name,
    //             ];
    //         }
    //     }

    //     if (empty($tokens)) {
    //         return response()->json([
    //             'message' => 'No users due for weight update reminders at this time.'
    //         ], 201);
    //     }

    //     $notification = \App\Models\NotificationData::where('notification_type', '13')->first();

    //     if (!$notification) {
    //         return response()->json([
    //             'status' => false,
    //             'error' => 'Notification content not found.'
    //         ], 201);
    //     }

    //     return $this->sendDynamicNotification($tokens, $notification);
    // }

    // api for extra burn calory

    // send random  when extra

    // public function extraBurnCaloryNotification(){
    //    $token = env("CRON_MEAL_TOKEN");

    //     if ($token !== env('CRON_MEAL_TOKEN')) {
    //         return response()->json(['error' => 'Unauthorized'], 401);
    //     }

    //     $users = User::with(['notificationSetting', 'dailySchedule', 'details'])->get();

    //     if ($users->isEmpty()) {
    //         return response()->json(['message' => 'No users found.']);
    //     }

    //     // Notification type 17 for evening energy burn reminder
    //     $notification = \App\Models\NotificationData::where('notification_type', 19)->first();

    //     if (!$notification) {
    //         return response()->json(['error' => 'Notification content not found.'], 201);
    //     }

    //     $tokens = [];

    //     foreach ($users as $user) {
    //         $timezone = $user->details->timezone ?? 'UTC';

    //         try {
    //             $now = Carbon::now(new \DateTimeZone($timezone));
    //             $currentTime = $now->format('H:i');
    //             $today = Carbon::now(new \DateTimeZone($timezone))->format('d-m-Y');

                
    //             $healthData = \App\Models\HealthKit::where('user_id', $user->id)->where('date', $today)
    //                 ->first();

    //             if (!$healthData) {
    //                 continue;
    //             }
                
    //             $burnedToday = $healthData->energy_burn ?? 0;
    //             $targetBurn = $user->total_burn_calory ?? 0;
    //             preg_match('/([\d.]+)\s*([a-zA-Z]+)/', $targetBurn, $matches);
    //             $targetBurnValue = isset($matches[1]) ? (float) $matches[1] : 0;
    //             //  Proceed only if extra calories burned
    //             if ($burnedToday <= $targetBurnValue || $targetBurnValue <= 0) {
    //                 continue; // skip this user
    //             }
    //             // $extraCaloriesBurned = $burnedToday - $targetBurnValue;
    //             // $extraCaloriesBurned = round($burnedToday - $targetBurnValue, 2);
    //             $extraCaloriesBurned = round($burnedToday - $targetBurnValue);

    //             if ($burnedToday > $targetBurnValue && $targetBurnValue > 0) {
    //                 // Check if already sent today
    //                 $alreadySent = Notification::where('user_id', $user->id)
    //                     ->where('notification_type', 19)
    //                     ->whereDate('notification_created_at', now()->toDateString())
    //                     ->exists();

    //                 if ($alreadySent) {
    //                     continue;
    //                 }
    //                 if (!empty($user->fcm_token)) {
    //                     $tokens[$user->fcm_token] = [
    //                         'user_id' => $user->id,
    //                         'device_type' => strtolower($user->device_type ?? ''),
    //                         'title' => $notification->notification_title,
    //                         'description_vars' => [
    //                             'extraCaloriesBurned' => $extraCaloriesBurned,
    //                         ],
    //                         'name' => $user->first_name ?? '',
    //                         // 'remaining_calories' => $remainingCalories,
    //                     ];
    //                 }
    //             } else {
    //                 continue;
    //             }
                
    //         } catch (\Exception $e) {
    //            continue;
    //         }
    //     }

    //     if (empty($tokens)) {
    //         return response()->json(['message' => 'No users to notify right now.'], 201);
    //     }

    //     return $this->sendDynamicNotification($tokens, $notification);
    // }

    // send before half hour to sleep time 

    public function extraBurnCaloryNotification(){
       $token = env("CRON_MEAL_TOKEN");

        if ($token !== env('CRON_MEAL_TOKEN')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $users = User::with(['notificationSetting', 'dailySchedule', 'details'])->get();

        if ($users->isEmpty()) {
            return response()->json(['message' => 'No users found.']);
        }

        // Notification type 17 for evening energy burn reminder
        $notification = \App\Models\NotificationData::where('notification_type', 19)->first();

        if (!$notification) {
            return response()->json(['error' => 'Notification content not found.'], 201);
        }

        $tokens = [];

        foreach ($users as $user) {
            $timezone = $user->details->timezone ?? 'UTC';
           // Get user schedule
            $schedule = $user->dailySchedule;
            if (!$schedule) {
                continue;
            }

            try {

                $now = Carbon::now(new \DateTimeZone($timezone));
                $currentTime = $now->format('H:i');
                $today = Carbon::now(new \DateTimeZone($timezone))->format('d-m-Y');
                $day = $now->format('l'); // 'Monday', 'Tuesday', etc.
                $isWeekend = in_array($day, ['Saturday', 'Sunday']);

                $scheduleJson = $isWeekend && $schedule->weekendSchedule_status == 0
                    ? $schedule->weekend_schedule
                    : $schedule->weekdays_schedule;

                $decoded = json_decode($scheduleJson, true);

                if (!isset($decoded['sleep']['startTime'])) {
                    continue;
                }

                // Parse lunch start time and add 2 hours
                $sleepStart = Carbon::createFromFormat('h:i A', $decoded['sleep']['startTime'], $timezone);
                $reminderTime = $sleepStart->copy()->subMinutes(30);

                if ($now->diffInMinutes($reminderTime) <= 5 && $now->greaterThanOrEqualTo($reminderTime)) {
                   
                    $healthData = \App\Models\HealthKit::where('user_id', $user->id)->where('date', $today)
                        ->first();

                    if (!$healthData) {
                        continue;
                    }
                   
                    $burnedToday = $healthData->energy_burn ?? 0;
                    $targetBurn = $user->total_burn_calory ?? 0;
                    preg_match('/([\d.]+)\s*([a-zA-Z]+)/', $targetBurn, $matches);
                    $targetBurnValue = isset($matches[1]) ? (float) $matches[1] : 0;
                    //  Proceed only if extra calories burned
                    if ($burnedToday <= $targetBurnValue || $targetBurnValue <= 0) {
                        continue; // skip this user
                    }
                    // $extraCaloriesBurned = $burnedToday - $targetBurnValue;
                    // $extraCaloriesBurned = round($burnedToday - $targetBurnValue, 2);
                    $extraCaloriesBurned = round($burnedToday - $targetBurnValue);

                    if ($burnedToday > $targetBurnValue && $targetBurnValue > 0) {
                        // Check if already sent today
                        $alreadySent = Notification::where('user_id', $user->id)
                            ->where('notification_type', 19)
                            ->whereDate('notification_created_at', now()->toDateString())
                            ->exists();

                        if ($alreadySent) {
                            continue;
                        }
                        if (!empty($user->fcm_token)) {
                            $tokens[$user->fcm_token] = [
                                'user_id' => $user->id,
                                'device_type' => strtolower($user->device_type ?? ''),
                                'title' => $notification->notification_title,
                                'description_vars' => [
                                    'extraCaloriesBurned' => $extraCaloriesBurned,
                                ],
                                'name' => $user->first_name ?? '',
                                // 'remaining_calories' => $remainingCalories,
                            ];
                        }
                    } else {
                        continue;
                    }
                }
            } catch (\Exception $e) {
               continue;
            }
        }

        if (empty($tokens)) {
            return response()->json(['message' => 'No users to notify right now.'], 201);
        }

        return $this->sendDynamicNotification($tokens, $notification);
    }

    // api for extra eat calory

    public function extraEatenCaloryNotification(){
        $token = env("CRON_MEAL_TOKEN");

        if ($token !== env('CRON_MEAL_TOKEN')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $users = User::with(['notificationSetting', 'dailySchedule', 'details'])->get();

        if ($users->isEmpty()) {
            return response()->json(['message' => 'No users found.']);
        }

        // Notification type 17 for evening energy burn reminder
        $notification = \App\Models\NotificationData::where('notification_type', 20)->first();

        if (!$notification) {
            return response()->json(['error' => 'Notification content not found.'], 201);
        }

        $tokens = [];

        foreach ($users as $user) {
            
            $timezone = $user->details->timezone ?? 'UTC';

            try {
                
                $now = Carbon::now(new \DateTimeZone($timezone));
                $currentTime = $now->format('H:i');
                $today = Carbon::now(new \DateTimeZone($timezone))->format('d-m-Y');
                
                $details=$user->details;

                // Skip if no details or calories not recorded today
                if (
                    empty($details) || empty($details->today_eaten_calory) || $details->today_eaten_calory_date !== now()->format('Y-m-d')
                ) {
                    continue;
                }

                $eatenToday = $details->today_eaten_calory ?? 0;
                $targetEaten = $details->total_calory ?? 0;
                preg_match('/([\d.]+)\s*([a-zA-Z]+)/', $targetEaten, $matches);
                $targetEatenValue = isset($matches[1]) ? (float) $matches[1] : 0;

                //  Proceed only if extra calories burned
                if ($eatenToday <= $targetEatenValue || $targetEatenValue <= 0) {
                    continue; // skip this user
                }
                // $extraCaloriesEaten = $eatenToday - $targetEatenValue;
                // $extraCaloriesEaten = round($eatenToday - $targetEatenValue, 2);

                $extraCaloriesEaten = round($eatenToday - $targetEatenValue);

                
                if ($eatenToday > $targetEatenValue && $targetEatenValue > 0) {
                    // Check if already sent today
                    $alreadySent = Notification::where('user_id', $user->id)
                        ->where('notification_type', 20)
                        ->whereDate('notification_created_at', now()->toDateString())
                        ->exists();

                    if ($alreadySent) {
                        continue;
                    }
                    if (!empty($user->fcm_token)) {
                        $tokens[$user->fcm_token] = [
                            'user_id' => $user->id,
                            'device_type' => strtolower($user->device_type ?? ''),
                            'title' => $notification->notification_title,
                            'description_vars' => [
                                'extraCaloriesEaten' => $extraCaloriesEaten,
                            ],
                            'name' => $user->first_name ?? '',
                            // 'remaining_calories' => $remainingCalories,
                        ];
                    }
                } else {
                    continue;
                }
                
            } catch (\Exception $e) {
                continue;
            }
        }

        if (empty($tokens)) {
            return response()->json(['message' => 'No users to notify right now.'], 201);
        }

        return $this->sendDynamicNotification($tokens, $notification);
    }


    // notification for birrthday

    public function sendBithdayNotification(){
        $token = env("CRON_MEAL_TOKEN");

        if ($token !== env('CRON_MEAL_TOKEN')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Get today's month and day
        $today = Carbon::today();

        $users = User::with(['notificationSetting', 'dailySchedule', 'details'])->whereMonth('dob', $today->month)->whereDay('dob', $today->day)->get();

        if ($users->isEmpty()) {
            return response()->json(['message' => 'No users found.']);
        }

        // Notification type 17 for evening energy burn reminder
        $notification = \App\Models\NotificationData::where('notification_type', 4)->first();

        if (!$notification) {
            return response()->json(['error' => 'Notification content not found.'], 201);
        }

        $tokens = [];

        foreach ($users as $user) {
            
            $timezone = $user->details->timezone ?? 'UTC';

            try {
                
                $now = Carbon::now(new \DateTimeZone($timezone));
                $currentTime = $now->format('H:i');
                $today = $now->format('');
                
                $details=$user->details;
                $name = $user->name ?? 'buddy';

                // Check if current time is exactly 12:00 AM in user's timezone
                if ($currentTime !== '05:00') {
                    continue; // Skip if not 12:00 AM
                }
                // $reminderTime = Carbon::createFromTime(11, 30, 0, $timezone);

                // if ($now->diffInMinutes($reminderTime) > 5 || $now->lessThan($reminderTime)) {
                //     continue; // Skip if not within 5 minutes after 11:00
                // }

                // Check if this notification was already sent today
                // $alreadySent = \DB::table('user_notifications_sent')
                //     ->where('user_id', $user->id)
                //     ->where('notification_id', $notification->id)
                //     ->whereDate('sent_date', $now->toDateString())
                //     ->exists();

                // if ($alreadySent) {
                //     continue; // Skip duplicate
                // }

                    
                if (!empty($user->fcm_token)) {
                    $tokens[$user->fcm_token] = [
                        'user_id' => $user->id,
                        'device_type' => strtolower($user->device_type ?? ''),
                        'description' => $notification->notification_description,
                        'title_vars' => [
                            'name' => $name,
                        ],
                        'name' => $user->name ?? 'buddy',
                    ];
                }
            
            } catch (\Exception $e) {
                continue;
            }
        }

        if (empty($tokens)) {
            return response()->json(['message' => 'No users to notify right now.'], 201);
        }

        return $this->sendDynamicNotification($tokens, $notification);
    }


    // notification for b'day before 7 days

    public function sendBithdayPreNotification(){
        $token = env("CRON_MEAL_TOKEN");

        if ($token !== env('CRON_MEAL_TOKEN')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Get today's month and day
        $targetDate = \Carbon\Carbon::today()->addDays(7);
        $month = $targetDate->format('m');
        $day = $targetDate->format('d');

        // $users = User::with(['notificationSetting', 'dailySchedule', 'details'])
        //     ->whereRaw('MONTH(dob) = ? AND DAY(dob) = ?', [$month, $day])
        //     ->get();

        $users = User::with(['notificationSetting', 'dailySchedule', 'details'])
            ->whereMonth('dob', $targetDate->month)
            ->whereDay('dob', $targetDate->day)
            ->get();

        if ($users->isEmpty()) {
            return response()->json(['message' => 'No users found.']);
        }

        // Notification type 17 for evening energy burn reminder
        $notification = \App\Models\NotificationData::where('notification_type', 7)->first();

        if (!$notification) {
            return response()->json(['error' => 'Notification content not found.'], 201);
        }

        $tokens = [];

        foreach ($users as $user) {
            
            $timezone = $user->details->timezone ?? 'UTC';

            try {
                
                $now = Carbon::now(new \DateTimeZone($timezone));
                $currentTime = $now->format('H:i');
                $today = $now->format('');
                
                $details=$user->details;
                $name = $user->name ?? 'buddy';
                

                // Check if current time is exactly 12:00 AM in user's timezone
                if ($currentTime !== '11:36') {
                    continue; // Skip if not 12:00 AM
                }

                // $reminderTime = Carbon::createFromTime(5, 0, 0, $timezone);

                // if ($now->diffInMinutes($reminderTime) > 5 || $now->lessThan($reminderTime)) {
                //     continue; // Skip if not within 5 minutes after 11:00
                // }

                // // Check if this notification was already sent today
                // $alreadySent = \DB::table('user_notifications_sent')
                //     ->where('user_id', $user->id)
                //     ->where('notification_id', $notification->id)
                //     ->whereDate('sent_date', $now->toDateString())
                //     ->exists();

                // if ($alreadySent) {
                //     continue; // Skip duplicate
                // }

                    
                if (!empty($user->fcm_token)) {
                    $tokens[$user->fcm_token] = [
                        'user_id' => $user->id,
                        'device_type' => strtolower($user->device_type ?? ''),
                         'title' => $notification->notification_title,
                        'description' => $notification->notification_description,
                        'name' => $user->name ?? 'buddy',
                    ];
                }
            
            } catch (\Exception $e) {
               continue; 
            }
        }

        if (empty($tokens)) {
            return response()->json(['message' => 'No users to notify right now.'], 201);
        }

        return $this->sendDynamicNotification($tokens, $notification);
    }


    public function sendDynamicNotification($tokens,$notification)
    {
        \Log::info("🔔 sendDynamicNotification called", [
            'notification_type' => $notification->notification_type,
            'title' => $notification->notification_title,
            'description' => $notification->notification_description,
            'tokens_count' => count($tokens)
        ]);
         // Access token from Service Account (OAuth2)
         $client = new \Google\Client();
         $client->setAuthConfig(storage_path('app/firebase/service-account.json'));
         $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
         $client->fetchAccessTokenWithAssertion();
         $accessToken = $client->getAccessToken()['access_token'];

         // Loop through each token and send individually
         $responses = [];
         foreach ($tokens as $token=> $info) {
            $userId = $info['user_id'];
            $device_type = $info['device_type'];
            \Log::info("📱 Preparing notification", [
                'user_id' => $userId,
                'device_type' => $device_type,
                'fcm_token' => $token
            ]);

            // Set userName based on notification_type condition
            // if ($notification->notification_type == 4) {
            //     // Append userName if notification_type is 4
            //     $userName = $info['name'];
            // } else {
            //     // Use an empty string or other value if not notification_type 4
            //     $userName = '';
            // }
            // // Determine the title based on notification_type
            // if ($notification->notification_type == 4) {
            //     // If the notification_type is 4, append the userName to the title
            //     $title = $notification->notification_title . ' ' . $userName.' 🎂🎉';
            // } else {
            //     // Otherwise, use the original notification title
            //     $title = $notification->notification_title;
            // }

            // if ($notification->notification_type == 6) {
            //     // Append userName if notification_type is 4
            //     $userName = $info['name'];
            // } else {
            //     // Use an empty string or other value if not notification_type 4
            //     $userName = '';
            // }
            // // Determine the title based on notification_type
            // if ($notification->notification_type == 6) {
            //     // If the notification_type is 4, append the userName to the title
            //     $title = $notification->notification_title . ' ' . $userName.' 🎯';
            // } else {
            //     // Otherwise, use the original notification title
            //     $title = $notification->notification_title;
            // }

            // Set userName and emojis based on notification_type
            $userName = '';
            $title = $notification->notification_title;
            $description = $notification->notification_description;

            //  Only replace variables if notification_type is 15
            if ($notification->notification_type == 15 && isset($info['description_vars'])) {
                $vars = $info['description_vars'];
                $description = str_replace(
                    ['{$eat}', '{$burn}', '{$bmi}', '{$targetBMI}'],
                    [
                        $vars['eat'] ?? 0,
                        $vars['burn'] ?? 0,
                    ],
                    $description
                );
            }

            if ($notification->notification_type == 17 && isset($info['description_vars'])) {
                $vars = $info['description_vars'];
                $description = str_replace(
                    ['{$remainingCalories}','{$remainingEatenCalories}'],
                    [
                        $vars['remaining_calories'] ?? 0,
                        $vars['remainingEatenCalories'] ?? 0,
                    ],
                    $description
                );
            }


            if ($notification->notification_type == 18 && isset($info['description_vars'])) {
                $vars = $info['description_vars'];
                $description = str_replace(
                    ['{$burnPercentage}', '{$eatenPercentage}'],
                    [
                        $vars['burnPercentage'] ?? 0,
                        $vars['eatenPercentage'] ?? 0,
                    ],
                    $description
                );
            }

            if ($notification->notification_type == 19 && isset($info['description_vars'])) {
                $vars = $info['description_vars'];
                $description = str_replace(
                    ['{$extraCaloriesBurned}'],
                    [
                        $vars['extraCaloriesBurned'] ?? 0,
                    ],
                    $description
                );
            }

            if ($notification->notification_type == 20 && isset($info['description_vars'])) {
                $vars = $info['description_vars'];
                $description = str_replace(
                    ['{$extraCaloriesEaten}'],
                    [
                        $vars['extraCaloriesEaten'] ?? 0,
                    ],
                    $description
                );
            }

            if ($notification->notification_type == 4 && isset($info['title_vars'])) {
                $vars = $info['title_vars'];
                $title = str_replace(
                    ['{$name}'],
                    [
                        $vars['name'] ?? 0,
                    ],
                    $title
                );
            }

            if (in_array($notification->notification_type, [6])) {
                $userName = $info['name'];
                $emoji = $notification->notification_type == 4 ? ' 🎂🎉' : ' 🎯';
                $title .= ' ' . $userName . $emoji;
            }

            if (in_array($notification->notification_type, [14])) {
                // Add greeting before description
                $userName = $info['name'] ?? "buddy";
                $description = "Good morning, {$userName}. " . $description;
               
            }

            switch ($device_type) {
                case 'ios':
                    $payload = [
                        'message' => [
                            'token' => $token,
                            'notification' => [
                                'title' => (string)$title,
                                'body' =>(string) $description,
                            ],
                            'data' => [
                                'story_id' => (string)$notification->notification_type,
                            ]
                        ]
                    ];
                    break;
        
                case 'android':
                default:
                    $payload = [
                        'message' => [
                            'token' => $token,
                            'data' => [
                                'title' => (string)$title,
                                'body' => (string)$description,
                                'story_id' => (string)$notification->notification_type,
                            ]
                        ]
                    ];
                    break;
            }
            \Log::info("📤 Sending notification to FCM", [
                'user_id' => $userId,
                'payload' => $payload
            ]);
             try {
                 $response = Http::withHeaders([
                     'Authorization' => 'Bearer ' . $accessToken,
                     'Content-Type' => 'application/json',
                 ])->post('https://fcm.googleapis.com/v1/projects/' . env('FIREBASE_PROJECT_ID') . '/messages:send', $payload);
                     // Handle response
                 if ($response->successful()) {
                    \Log::info("✅ Notification sent successfully", [
                        'user_id' => $userId,
                        'fcm_token' => $token,
                        'response' => $response->json()
                    ]);
                    // Save notification data after successful send
                    $notificationData = new Notification();
                    $notificationData->user_id =  $userId;  // Optional: Save user_id if applicable
                    $notificationData->notification_type = $notification->notification_type;
                    $notificationData->title = $title;
                    $notificationData->description = $description;
                    // $notificationData->status = 'sent';  // Optional: track status (sent, failed, etc.)
                    $notificationData->notification_created_at = now();
                    $notificationData->save();

                    // Add successful response to array
                   // Add successful response to array
                   $responses[] = [
                        'token' => $token,
                        'status' => 'success',
                        'device_type' => $device_type
                    ];
                } else {
                    \Log::error("❌ Notification failed", [
                        'user_id' => $userId,
                        'fcm_token' => $token,
                        'response' => $response->json()
                    ]);
                    // $responses[] = ['token' => $token, 'status' => 'failed', 'response' => $response->json()];
                    $responses[] = [
                        'token' => $token,
                        'status' => 'failed',
                        'response' => $response->json(),
                    ];
                }
            } catch (\Exception $e) {
                \Log::error("🔥 Exception while sending notification", [
                    'user_id' => $userId,
                    'fcm_token' => $token,
                    'error' => $e->getMessage()
                ]);

                // $responses[] = ['token' => $token, 'status' => 'error', 'error' => $e->getMessage()];
                $responses[] = [
                    'token' => $token,
                    'status' => 'error',
                    'error' => $e->getMessage(),
                ];
            }
        }

        // Return the responses with a single message object
        return response()->json([
            'status' => 'true',
            'message' => [
                'data' => [
                    'notification_type' => $notification->notification_type,
                    'notification_title' => $title,
                    'notification_description' => $description,
                ]
            ],
        ],200);
    }

    // send notification with ai api  for breakfast 

    public function sendBreakfastReminder()
    {
        $token = env("CRON_MEAL_TOKEN");
        if ($token !== env('CRON_MEAL_TOKEN')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $users = User::with(['notificationSetting', 'latestWeightHistory', 'dailySchedule', 'details'])->get();
        if ($users->isEmpty()) {
            return response()->json(['message' => 'No users to notify']);
        }

        $tokens = [];
        foreach ($users as $user) {
            $setting = $user->notificationSetting;

            // Skip if weight reminders are disabled
            if ($setting && $setting->weight_notify == 1) {
                continue;
            }

            // Get user schedule
            $schedule = $user->dailySchedule;
            if (!$schedule) {
                continue;
            }

            $userTimezone = $user->details->timezone ?? 'UTC'; 
            $nowInUserTZ = Carbon::now(new CarbonTimeZone($userTimezone));
            $currentTime = $nowInUserTZ->format('H:i');
            $currentDay = $nowInUserTZ->format('l');

            $isWeekend = in_array($currentDay, ['Saturday', 'Sunday']);
            $scheduleJson = $isWeekend && $schedule->weekendSchedule_status == 0
                ? $schedule->weekend_schedule
                : $schedule->weekdays_schedule;

            $decoded = json_decode($scheduleJson, true);
            if (!isset($decoded['breakfast']['startTime'])) {
                continue;
            }

            try {
                $startTime = Carbon::createFromFormat('h:i A', $decoded['breakfast']['startTime'], $userTimezone)->format('H:i');
            } catch (\Exception $e) {
                continue;
            }

            if ($currentTime !== $startTime) {
                continue;
            }

            if (!empty($user->fcm_token) && is_string($user->fcm_token)) {
                $tokens[$user->fcm_token] = [
                    'user_id' => $user->id,
                    'device_type' => strtolower($user->device_type ?? ''),
                    'name' => $user->name,
                ];
            }
        }

        if (empty($tokens)) {
            return response()->json([
                'message' => 'No users due for weight update reminders at this time.'
            ], 201);
        }

        $allResponses = [];

        // ✅ Build and send personalized notifications for each user
        foreach ($tokens as $fcmToken => $info) {
            $user = \App\Models\User::find($info['user_id']);
            $userTimezone = $user->details->timezone ?? 'UTC';

            // Check if breakfast already logged today
            $breakfastLog = MenuList::with('dietPlan')
                ->where('user_id', $user->id)
                ->where('meal_type', 'breakfast')
                ->whereDate('added_menu_at', now($userTimezone)->toDateString())
                ->orderBy('menu_id', 'desc')
                ->first();

            if ($breakfastLog && $breakfastLog->eaten_status === 'yes') {
                $dietPlan = $breakfastLog->dietPlan->diet_plan;
                if (is_string($dietPlan)) {
                    $dietPlan = json_decode($dietPlan, true);
                }
                $mealName = $dietPlan['breakfast']['meal'] ?? null;

                $aiContent = $this->generateAiNotificationContent($user, "post-breakfast", $mealName);
                $notificationType = 102;
            } else {
                $aiContent = $this->generateAiNotificationContent($user, "breakfast");
                $notificationType = 101;
            }

            $alreadySent = \App\Models\Notification::where('user_id', $user->id)
                ->where('notification_type', $notificationType)
                ->whereDate('notification_created_at', now($userTimezone)->toDateString())
                ->exists();

            if ($alreadySent) {
                continue;
            }

            $notification = new \stdClass();
            $notification->notification_type = $notificationType;
            $notification->notification_title = $aiContent['title'];
            $notification->notification_description = $aiContent['description'];

            // ✅ Collect each response instead of returning
            $response = $this->sendDynamicNotification(
                [$fcmToken => $tokens[$fcmToken]],
                $notification
            );

            $allResponses[] = [
                'user_id' => $user->id,
                'name' =>  $user->name ?? 'Buddy',
                'status' => $response->getData(), // store response payload
            ];
        }

        return response()->json([
            'message' => 'Notifications sent successfully.',
            'results' => $allResponses,
        ]);
    }


    // protected function generateAiNotificationContent($user, $type, $mealName = null)
    // {
    //     $client = \OpenAI::client(env('OPENAI_API_KEY'));

    //    $context = match ($type) {
    //         'breakfast' => "Encourage {$user->name} to get ready for breakfast. Focus on energy, positivity, and starting the day strong.",
    //         'post-breakfast' => $mealName
    //             ? "Congratulate {$user->name} for completing breakfast after enjoying {$mealName}. Include the meal name in the notification. Focus on motivation, staying energized, and building momentum."
    //             : "Congratulate {$user->name} for completing breakfast. Focus on motivation, staying energized, and building momentum.",
    //         default => "Motivate {$user->name} with a healthy lifestyle message.",
    //     };

    //     $prompt = "Generate a personalized, motivational notification for {$user->name}.
    //     Context: {$context}
    //     Requirements:
    //     - Output only a Title and a Description (with 'Title:' and 'Description:').
    //     - Make sure the title is different each time (avoid repeating).
    //     - Keep it short, uplifting, and engaging.
    //     - give in dutch.
    //     - Adapt tone to morning/evening depending on context.";

    //     $response = $client->chat()->create([
    //         'model' => 'gpt-4o-mini',
    //         'messages' => [
    //             ['role' => 'system', 'content' => 'You are a motivational health coach creating notifications.'],
    //             ['role' => 'user', 'content' => $prompt],
    //         ],
    //         'temperature' => 0.9,
    //     ]);

    //     $content = $response['choices'][0]['message']['content'] ?? '';

    //     preg_match('/Title:\s*(.*?)\n/i', $content, $titleMatch);
    //     preg_match('/Description:\s*(.*)/i', $content, $descMatch);
      
    //     return [
    //         'title' => $titleMatch[1] ?? ucfirst($type) . " Update",
    //         'description' => $descMatch[1] ?? "Hey {$user->name}, keep up the momentum!",
    //     ];
    // }

    public function sendLunchReminder()
    {
        $token = env("CRON_MEAL_TOKEN");
        if ($token !== env('CRON_MEAL_TOKEN')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $users = User::with(['notificationSetting', 'latestWeightHistory', 'dailySchedule','details'])->get();
        if ($users->isEmpty()) {
            return response()->json(['message' => 'No users to notify']);
        }

        $tokens = [];
        foreach ($users as $user) {
            $setting = $user->notificationSetting;

            if ($setting && $setting->weight_notify == 1) {
                continue;
            }

            $schedule = $user->dailySchedule;
            if (!$schedule) continue;

            $userTimezone = $user->details->timezone ?? 'UTC';
            $nowInUserTZ = Carbon::now(new CarbonTimeZone($userTimezone));
            $currentTime = $nowInUserTZ->format('H:i');
            $currentDay = $nowInUserTZ->format('l');

            $isWeekend = in_array($currentDay, ['Saturday', 'Sunday']);
            $scheduleJson = $isWeekend && $schedule->weekendSchedule_status == 0
                ? $schedule->weekend_schedule
                : $schedule->weekdays_schedule;

            $decoded = json_decode($scheduleJson, true);
            if (!isset($decoded['lunch']['startTime'])) continue;

            try {
                $startTime = Carbon::createFromFormat('h:i A', $decoded['lunch']['startTime'], $userTimezone)->format('H:i');
            } catch (\Exception $e) {
                continue;
            }

            if ($currentTime !== $startTime) continue;

            if (!empty($user->fcm_token) && is_string($user->fcm_token)) {
                $tokens[$user->fcm_token] = [
                    'user_id' => $user->id,
                    'device_type' => strtolower($user->device_type ?? ''),
                    'name' => $user->name,
                ];
            }
        }

        if (empty($tokens)) {
            return response()->json([
                'message' => 'No users due for lunch reminders at this time.'
            ], 201);
        }

        $allResponses = [];

        foreach ($tokens as $fcmToken => $info) {
            $user = \App\Models\User::find($info['user_id']);
            $userTimezone = $user->details->timezone ?? 'UTC';

            $lunchLog = MenuList::with('dietPlan')
                ->where('user_id', $user->id)
                ->where('meal_type', 'lunch')
                ->whereDate('added_menu_at', now($userTimezone)->toDateString())
                ->orderBy('menu_id', 'desc')
                ->first();

            if ($lunchLog && $lunchLog->eaten_status === 'yes') {
                $dietPlan = $lunchLog->dietPlan->diet_plan;
                if (is_string($dietPlan)) {
                    $dietPlan = json_decode($dietPlan, true);
                }
                $mealName = $dietPlan['lunch']['meal'] ?? null;
                $aiContent = $this->generateAiNotificationContent($user, "post-lunch", $mealName);
                $notificationType = 103;
            } else {
                $aiContent = $this->generateAiNotificationContent($user, "lunch");
                $notificationType = 104;
            }

            $alreadySent = \App\Models\Notification::where('user_id', $user->id)
                ->where('notification_type', $notificationType)
                ->whereDate('notification_created_at', now($userTimezone)->toDateString())
                ->exists();

            if ($alreadySent) continue;

            $notification = new \stdClass();
            $notification->notification_type = $notificationType;
            $notification->notification_title = $aiContent['title'];
            $notification->notification_description = $aiContent['description'];

            $response = $this->sendDynamicNotification([$fcmToken => $tokens[$fcmToken]], $notification);

            $allResponses[] = [
                'user_id' => $user->id,
                'name' => $user->name ?? 'Buddy',
                'status' => $response->getData(),
            ];
        }

        return response()->json([
            'message' => 'Lunch notifications sent successfully.',
            'results' => $allResponses,
        ]);
    }


    public function sendDinnerReminder()
    {
        $token = env("CRON_MEAL_TOKEN");
        if ($token !== env('CRON_MEAL_TOKEN')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $users = User::with(['notificationSetting', 'latestWeightHistory', 'dailySchedule','details'])->get();
        if ($users->isEmpty()) {
            return response()->json(['message' => 'No users to notify']);
        }

        $tokens = [];
        foreach ($users as $user) {
            $setting = $user->notificationSetting;

            if ($setting && $setting->weight_notify == 1) continue;

            $schedule = $user->dailySchedule;
            if (!$schedule) continue;

            $userTimezone = $user->details->timezone ?? 'UTC';
            $nowInUserTZ = Carbon::now(new CarbonTimeZone($userTimezone));
            $currentTime = $nowInUserTZ->format('H:i');
            $currentDay = $nowInUserTZ->format('l');

            $isWeekend = in_array($currentDay, ['Saturday', 'Sunday']);
            $scheduleJson = $isWeekend && $schedule->weekendSchedule_status == 0
                ? $schedule->weekend_schedule
                : $schedule->weekdays_schedule;

            $decoded = json_decode($scheduleJson, true);
            if (!isset($decoded['dinner']['startTime'])) continue;

            try {
                $startTime = Carbon::createFromFormat('h:i A', $decoded['dinner']['startTime'], $userTimezone)->format('H:i');
            } catch (\Exception $e) {
                continue;
            }

            if ($currentTime !== $startTime) continue;

            if (!empty($user->fcm_token) && is_string($user->fcm_token)) {
                $tokens[$user->fcm_token] = [
                    'user_id' => $user->id,
                    'device_type' => strtolower($user->device_type ?? ''),
                    'name' => $user->name,
                ];
            }
        }

        if (empty($tokens)) {
            return response()->json([
                'message' => 'No users due for dinner reminders at this time.'
            ], 201);
        }

        $allResponses = [];

        foreach ($tokens as $fcmToken => $info) {
            $user = \App\Models\User::find($info['user_id']);
            $userTimezone = $user->details->timezone ?? 'UTC';

            $dinnerLog = MenuList::with('dietPlan')
                ->where('user_id', $user->id)
                ->where('meal_type', 'dinner')
                ->whereDate('added_menu_at', now($userTimezone)->toDateString())
                ->orderBy('menu_id', 'desc')
                ->first();

            if ($dinnerLog && $dinnerLog->eaten_status === 'yes') {
                $dietPlan = $dinnerLog->dietPlan->diet_plan;
                if (is_string($dietPlan)) {
                    $dietPlan = json_decode($dietPlan, true);
                }
                $mealName = $dietPlan['dinner']['meal'] ?? null;
                $aiContent = $this->generateAiNotificationContent($user, "post-dinner", $mealName);
                $notificationType = 105; // Dinner eaten
            } else {
                $aiContent = $this->generateAiNotificationContent($user, "dinner");
                $notificationType = 106; // Dinner not eaten
            }

            $alreadySent = \App\Models\Notification::where('user_id', $user->id)
                ->where('notification_type', $notificationType)
                ->whereDate('notification_created_at', now($userTimezone)->toDateString())
                ->exists();

            if ($alreadySent) continue;

            $notification = new \stdClass();
            $notification->notification_type = $notificationType;
            $notification->notification_title = $aiContent['title'];
            $notification->notification_description = $aiContent['description'];

            $response = $this->sendDynamicNotification([$fcmToken => $tokens[$fcmToken]], $notification);

            $allResponses[] = [
                'user_id' => $user->id,
                'name' => $user->name ?? 'Buddy',
                'status' => $response->getData(),
            ];
        }

        return response()->json([
            'message' => 'Dinner notifications sent successfully.',
            'results' => $allResponses,
        ]);
    }


    protected function generateAiNotificationContent($user, $type, $mealName = null)
    {
        $client = \OpenAI::client(env('OPENAI_API_KEY'));

        $context = match ($type) {
            'breakfast' => "Moedig {$user->name} aan om klaar te zijn voor het ontbijt. Focus op energie, positiviteit en een sterke start van de dag.",
            'post-breakfast' => $mealName
                ? "Feliciateer {$user->name} met het afronden van het ontbijt na het genieten van {$mealName}. Focus op motivatie, energie behouden en momentum opbouwen."
                : "Feliciateer {$user->name} met het afronden van het ontbijt. Focus op motivatie, energie behouden en momentum opbouwen.",
            'lunch' => "Moedig {$user->name} aan om klaar te zijn voor de lunch. Focus op energie en een gezonde middag.",
            'post-lunch' => $mealName
                ? "Feliciateer {$user->name} met het afronden van de lunch na het genieten van {$mealName}. Focus op motivatie en energie behouden."
                : "Feliciateer {$user->name} met het afronden van de lunch. Focus op motivatie en energie behouden.",
            'dinner' => "Moedig {$user->name} aan om klaar te zijn voor het diner. Focus op een gezonde avond en energie behouden.",
            'post-dinner' => $mealName
                ? "Feliciateer {$user->name} met het afronden van het diner na het genieten van {$mealName}. Focus op ontspanning en energie behouden."
                : "Feliciateer {$user->name} met het afronden van het diner. Focus op ontspanning en energie behouden.",
            default => "Motiver {$user->name} met een bericht over een gezonde levensstijl.",
        };

        $prompt = "Genereer een gepersonaliseerde, motiverende notificatie voor {$user->name}.
        Context: {$context}
        Vereisten:
        - Output alleen een Titel en een Beschrijving (met 'Title:' en 'Description:').
        - Zorg dat de titel elke keer anders is.
        - Houd het kort, opbeurend en boeiend.
        - Gebruik de Nederlandse taal.
        - Pas de toon aan afhankelijk van ochtend/avond context.";

        $response = $client->chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => 'Je bent een motiverende gezondheidscoach die notificaties maakt.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.9,
        ]);

        $content = $response['choices'][0]['message']['content'] ?? '';

        preg_match('/Title:\s*(.*?)\n/i', $content, $titleMatch);
        preg_match('/Description:\s*(.*)/i', $content, $descMatch);

        return [
            'title' => $titleMatch[1] ?? ucfirst($type) . " Update",
            'description' => $descMatch[1] ?? "Hey {$user->name}, blijf het momentum vasthouden!",
        ];
    }






    


}
