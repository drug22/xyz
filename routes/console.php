<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
//adauga in cron cand e in productie
//* * * * * cd /home/drug/code/hw && php artisan schedule:run >> /dev/null 2>&1
Schedule::command('exchange-rates:update')
    ->dailyAt('00:00')
    ->withoutOverlapping()
    ->runInBackground();
