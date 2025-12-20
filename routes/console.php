<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule room status update to run daily at midnight
Schedule::command('rooms:update-status')
    ->daily()
    ->at('00:00')
    ->timezone('Asia/Ho_Chi_Minh')
    ->withoutOverlapping()
    ->runInBackground();
