<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('condition-reports:archive-expired', function () {
    $now = now();

    $updated = DB::table('condition_reports')
        ->where('type', 'general')
        ->whereNull('archived_at')
        ->whereNotNull('expires_at')
        ->where('expires_at', '<=', $now)
        ->update([
            'archived_at' => $now,
            'updated_at' => $now,
        ]);

    $this->info("Archived {$updated} expired condition report(s).");
})->purpose('Archive expired condition reports');

Schedule::command('condition-reports:archive-expired')->daily();
