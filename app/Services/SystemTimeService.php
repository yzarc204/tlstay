<?php

namespace App\Services;

use App\Models\Setting;
use Carbon\Carbon;

class SystemTimeService
{
    const SETTING_KEY_MANUAL_DATE = 'system_manual_date';
    const SETTING_KEY_MANUAL_TIME = 'system_manual_time';
    const SETTING_KEY_MANUAL_ENABLED = 'system_manual_time_enabled';

    /**
     * Get current system time (real or manual)
     *
     * @return Carbon
     */
    public static function now(): Carbon
    {
        if (self::isManualTimeEnabled()) {
            $date = Setting::get(self::SETTING_KEY_MANUAL_DATE);
            $time = Setting::get(self::SETTING_KEY_MANUAL_TIME, '00:00:00');

            if ($date) {
                try {
                    // Ensure time format is HH:MM:SS
                    if (strlen($time) === 5) {
                        $time .= ':00';
                    }
                    $dateTime = Carbon::parse($date . ' ' . $time);
                    return $dateTime;
                } catch (\Exception $e) {
                    \Log::warning('Failed to parse manual time', [
                        'date' => $date,
                        'time' => $time,
                        'error' => $e->getMessage(),
                    ]);
                    // If parsing fails, return real time
                    return Carbon::now();
                }
            }
        }

        return Carbon::now();
    }

    /**
     * Get current system date (real or manual)
     *
     * @return Carbon
     */
    public static function today(): Carbon
    {
        return self::now()->startOfDay();
    }

    /**
     * Check if manual time is enabled
     *
     * @return bool
     */
    public static function isManualTimeEnabled(): bool
    {
        return Setting::get(self::SETTING_KEY_MANUAL_ENABLED, '0') === '1';
    }

    /**
     * Enable manual time
     *
     * @param string $date Date in Y-m-d format
     * @param string $time Time in H:i or H:i:s format
     * @return void
     */
    public static function enableManualTime(string $date, string $time = '00:00:00'): void
    {
        // Ensure time format is HH:MM:SS
        if (strlen($time) === 5) {
            $time .= ':00';
        }

        Setting::set(self::SETTING_KEY_MANUAL_ENABLED, '1');
        Setting::set(self::SETTING_KEY_MANUAL_DATE, $date);
        Setting::set(self::SETTING_KEY_MANUAL_TIME, $time);
    }

    /**
     * Disable manual time (use real time)
     *
     * @return void
     */
    public static function disableManualTime(): void
    {
        Setting::set(self::SETTING_KEY_MANUAL_ENABLED, '0');
    }

    /**
     * Get manual date if enabled
     *
     * @return string|null
     */
    public static function getManualDate(): ?string
    {
        if (self::isManualTimeEnabled()) {
            $date = Setting::get(self::SETTING_KEY_MANUAL_DATE);
            // Return in YYYY-MM-DD format for internal use
            return $date;
        }
        return null;
    }
    
    /**
     * Get manual date in DD/MM/YYYY format for display
     *
     * @return string|null
     */
    public static function getManualDateFormatted(): ?string
    {
        if (self::isManualTimeEnabled()) {
            $date = Setting::get(self::SETTING_KEY_MANUAL_DATE);
            if ($date) {
                try {
                    return \Carbon\Carbon::parse($date)->format('d/m/Y');
                } catch (\Exception $e) {
                    return $date;
                }
            }
        }
        return null;
    }

    /**
     * Get manual time if enabled
     *
     * @return string|null
     */
    public static function getManualTime(): ?string
    {
        if (self::isManualTimeEnabled()) {
            $time = Setting::get(self::SETTING_KEY_MANUAL_TIME, '00:00:00');
            // Ensure time format is HH:MM:SS
            if (strlen($time) === 5) {
                $time .= ':00';
            }
            return $time;
        }
        return null;
    }

    /**
     * Get current system time info
     *
     * @return array
     */
    public static function getTimeInfo(): array
    {
        $isManual = self::isManualTimeEnabled();
        $now = self::now();

        return [
            'is_manual' => $isManual,
            'current_datetime' => $now->format('Y-m-d H:i:s'),
            'current_date' => $now->format('Y-m-d'),
            'current_time' => $now->format('H:i:s'),
            'manual_date' => $isManual ? self::getManualDate() : null, // YYYY-MM-DD for internal use
            'manual_date_formatted' => $isManual ? self::getManualDateFormatted() : null, // DD/MM/YYYY for display
            'manual_time' => $isManual ? self::getManualTime() : null,
            'real_datetime' => Carbon::now()->format('Y-m-d H:i:s'),
        ];
    }
}
