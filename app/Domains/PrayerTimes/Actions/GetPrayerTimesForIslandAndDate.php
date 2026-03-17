<?php

namespace App\Domains\PrayerTimes\Actions;

use App\Domains\PrayerTimes\DTOs\IslandData;
use App\Domains\PrayerTimes\DTOs\PrayerTimesResult;
use App\Domains\PrayerTimes\Services\PrayerTimeResolver;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

final class GetPrayerTimesForIslandAndDate
{
    private const CACHE_TTL = 86400; // 24 hours — prayer data is static per year

    public function __construct(
        private readonly PrayerTimeResolver $resolver,
    ) {}

    public function execute(IslandData $island, Carbon $date): ?PrayerTimesResult
    {
        $key = "prayer_times.{$island->id}.{$date->format('Y-m-d')}";

        return Cache::remember($key, self::CACHE_TTL, fn () =>
            $this->resolver->resolve($island, $date)
        );
    }

    public static function forgetCache(int $islandId, Carbon $date): void
    {
        Cache::forget("prayer_times.{$islandId}.{$date->format('Y-m-d')}");
    }
}
