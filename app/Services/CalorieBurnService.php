<?php

namespace App\Services;

use App\Models\HealthKit;
use App\Models\ManuallyActivityHistory;
use Carbon\Carbon;

class CalorieBurnService
{
    /**
     * Calculate and update today's total burned calories for a user.
     */
    public function updateTodayBurn(int $userId): void
    {
        $today = Carbon::today();

        // 1. Manual burn (works already)
        $manualBurn = ManuallyActivityHistory::where('user_id', $userId)
            ->whereDate('activity_date', $today)
            ->sum('calory_burn');
    
        $todayFormatted = $today->format('d-m-Y'); // "02-09-2025"

        $healthKit = HealthKit::where('user_id', $userId)
            ->where('date', $todayFormatted) // match string
            ->first();
        if (!$healthKit) {
            \Log::warning("No HealthKit entry found for", [
                'user' => $userId,
                'date' => $today->toDateString()
            ]);
            return;
        }

        // 3. Total
        $totalBurn = $healthKit->energy_burn + $manualBurn;

        $healthKit->update(['total_burn_today' => $totalBurn]);

        \Log::info("Updated burn", [
            'user' => $userId,
            'health' => $healthKit->energy_burn,
            'manual' => $manualBurn,
            'total' => $totalBurn,
        ]);
    }



}
