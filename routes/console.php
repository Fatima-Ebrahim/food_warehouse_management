<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

//Artisan::command('inspire', function () {
//    $this->comment(Inspiring::quote());
//})->purpose('Display an inspiring quote');

//for test ->everyMinute();
Schedule::command('stocktake:dispatch-scheduled')->everyMinute();
//    ->daily();
Schedule::command('stock:check-low')->dailyAt('00:00');
