<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Ubah waktu menjadi 07:39
Schedule::command('wa:send-daily')->dailyAt('22:50');
