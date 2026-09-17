<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('lexlaw:monitor')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/scheduler.log'));