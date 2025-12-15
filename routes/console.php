<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();
//create schedule to run every month on the first day at 00:00
Schedule::call(function () {
    Artisan::call('assets:depreciate');
})->monthlyOn(1, '00:00')->name('assets-depreciate')->withoutOverlapping();
