<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule::command('reserved:check-expired')
//     ->dailyAt('00:01'); // production
Schedule::command('reserved:check-expired')
    ->everyMinute();