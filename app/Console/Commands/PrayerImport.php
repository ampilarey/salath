<?php

namespace App\Console\Commands;

use App\Domains\PrayerTimes\Actions\GetIslandCollection;
use Illuminate\Console\Command;

class PrayerImport extends Command
{
    protected $signature   = 'prayer:import {--path= : Path to salat.db SQLite source file}';
    protected $description = 'Import prayer categories, islands and times from the salat.db SQLite source';

    public function handle(): int
    {
        $path = $this->option('path');

        if ($path) {
            // Override env var for this run
            putenv("PRAYER_TIMES_DB={$path}");
        }

        $this->call('db:seed', ['--class' => 'PrayerTimesSeeder', '--force' => true]);

        // Run the Latin names enrichment after a fresh import
        $this->call('prayer:add-latin-names');

        // Clear island cache so fresh data is picked up immediately
        GetIslandCollection::forgetCache();

        $this->info('Prayer data import complete. Island cache cleared.');

        return self::SUCCESS;
    }
}
