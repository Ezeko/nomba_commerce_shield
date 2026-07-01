<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Background job to retry provisioning Nomba Virtual Accounts for stores
Schedule::command('stores:provision-virtual-accounts')
    ->everyFiveMinutes()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/virtual_accounts_provisioning.log'));
