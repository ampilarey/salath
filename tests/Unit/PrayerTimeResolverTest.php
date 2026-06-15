<?php

namespace Tests\Unit;

use App\Domains\PrayerTimes\DTOs\IslandData;
use App\Domains\PrayerTimes\Services\PrayerTimeResolver;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PrayerTimeResolverTest extends TestCase
{
    use RefreshDatabase;

    private function makeIsland(int $offsetMinutes = 0): IslandData
    {
        DB::table('prayer_categories')->insertOrIgnore(['id' => 1]);
        DB::table('prayer_islands')->insertOrIgnore([
            'id' => 1, 'category_id' => 1, 'atoll' => 'ކ', 'name' => 'Test',
            'offset_minutes' => $offsetMinutes, 'is_active' => true,
        ]);

        return new IslandData(
            id:            1,
            categoryId:    1,
            atoll:         'ކ',
            atollLatin:    null,
            name:          'Test',
            nameLatin:     null,
            offsetMinutes: $offsetMinutes,
            latitude:      null,
            longitude:     null,
            isActive:      true,
        );
    }

    private function seedDay(int $dayOfYear, array $times = []): void
    {
        DB::table('prayer_times')->insert(array_merge([
            'category_id' => 1,
            'day_of_year' => $dayOfYear,
            'fajr'        => 290,
            'sunrise'     => 370,
            'dhuhr'       => 730,
            'asr'         => 940,
            'maghrib'     => 1095,
            'isha'        => 1150,
        ], $times));
    }

    public function test_resolve_returns_correct_times(): void
    {
        $island   = $this->makeIsland();
        $this->seedDay(1);

        $resolver = app(PrayerTimeResolver::class);
        $result   = $resolver->resolve($island, Carbon::parse('2026-01-01'));

        $this->assertNotNull($result);
        $this->assertEquals('04:50', $result->fajr);
        $this->assertEquals('06:10', $result->sunrise);
        $this->assertEquals('12:10', $result->dhuhr);
        $this->assertEquals('15:40', $result->asr);
        $this->assertEquals('18:15', $result->maghrib);
        $this->assertEquals('19:10', $result->isha);
    }

    public function test_resolve_applies_positive_offset(): void
    {
        $island = $this->makeIsland(offsetMinutes: 10);
        $this->seedDay(1);

        $result = app(PrayerTimeResolver::class)->resolve($island, Carbon::parse('2026-01-01'));

        $this->assertNotNull($result);
        // 290 + 10 = 300 = 05:00
        $this->assertEquals('05:00', $result->fajr);
    }

    public function test_resolve_applies_negative_offset(): void
    {
        $island = $this->makeIsland(offsetMinutes: -30);
        $this->seedDay(1);

        $result = app(PrayerTimeResolver::class)->resolve($island, Carbon::parse('2026-01-01'));

        $this->assertNotNull($result);
        // 290 - 30 = 260 = 04:20
        $this->assertEquals('04:20', $result->fajr);
    }

    public function test_resolve_returns_null_for_missing_day(): void
    {
        $island = $this->makeIsland();
        // No prayer_times seeded

        $result = app(PrayerTimeResolver::class)->resolve($island, Carbon::parse('2026-06-15'));

        $this->assertNull($result);
    }

    public function test_resolve_handles_midnight_rollover(): void
    {
        $island = $this->makeIsland(offsetMinutes: 60);
        $this->seedDay(1, ['isha' => 1400]); // 1400 + 60 = 1460, should wrap to 00:20

        $result = app(PrayerTimeResolver::class)->resolve($island, Carbon::parse('2026-01-01'));

        $this->assertNotNull($result);
        $this->assertEquals('00:20', $result->isha);
    }

    public function test_prayers_only_excludes_sunrise(): void
    {
        $island = $this->makeIsland();
        $this->seedDay(1);

        $result = app(PrayerTimeResolver::class)->resolve($island, Carbon::parse('2026-01-01'));
        $this->assertNotNull($result);

        $prayersOnly = $result->prayersOnly();
        $this->assertArrayNotHasKey('sunrise', $prayersOnly);
        $this->assertArrayHasKey('fajr', $prayersOnly);
        $this->assertArrayHasKey('isha', $prayersOnly);
    }

    private function seedBoundaryDays(): void
    {
        $this->makeIsland();

        foreach ([59, 60, 61] as $day) {
            DB::table('prayer_times')->insert([
                'category_id' => 1,
                'day_of_year' => $day,
                'fajr' => 200 + $day,
                'sunrise' => 260 + $day,
                'dhuhr' => 720,
                'asr' => 900,
                'maghrib' => 1080,
                'isha' => 1140,
            ]);
        }
    }

    public function test_non_leap_mar_1_uses_realigned_day_61_row(): void
    {
        $this->seedBoundaryDays();

        $island = $this->makeIsland();
        $result = app(PrayerTimeResolver::class)->resolve($island, Carbon::parse('2025-03-01'));

        $this->assertNotNull($result);
        $this->assertSame('04:21', $result->fajr);
    }

    public function test_leap_mar_1_uses_day_61_row_without_realignment(): void
    {
        $this->seedBoundaryDays();

        $island = $this->makeIsland();
        $result = app(PrayerTimeResolver::class)->resolve($island, Carbon::parse('2024-03-01'));

        $this->assertNotNull($result);
        $this->assertSame('04:21', $result->fajr);
    }

    public function test_non_leap_feb_28_uses_day_59_row(): void
    {
        $this->seedBoundaryDays();

        $island = $this->makeIsland();
        $result = app(PrayerTimeResolver::class)->resolve($island, Carbon::parse('2025-02-28'));

        $this->assertNotNull($result);
        $this->assertSame('04:19', $result->fajr);
    }

    public function test_leap_feb_29_uses_day_60_row(): void
    {
        $this->seedBoundaryDays();

        $island = $this->makeIsland();
        $result = app(PrayerTimeResolver::class)->resolve($island, Carbon::parse('2024-02-29'));

        $this->assertNotNull($result);
        $this->assertSame('04:20', $result->fajr);
    }
}
