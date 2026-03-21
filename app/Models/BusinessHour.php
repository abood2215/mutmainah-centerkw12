<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BusinessHour extends Model
{
    protected $fillable = [
        'day_of_week',
        'start_time',
        'end_time',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Check if the current server time falls within business hours for today.
     * day_of_week: 0 = Sunday, 1 = Monday, ..., 6 = Saturday
     */
    public static function isWithinBusinessHours(): bool
    {
        $today = (int) Carbon::now()->format('w'); // 0 (Sun) to 6 (Sat)

        $record = static::where('day_of_week', $today)
            ->where('is_active', true)
            ->first();

        if (!$record) {
            return false;
        }

        $now       = Carbon::now()->format('H:i:s');
        $startTime = $record->start_time;
        $endTime   = $record->end_time;

        return $now >= $startTime && $now <= $endTime;
    }
}
