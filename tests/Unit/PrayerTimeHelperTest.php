<?php

namespace Tests\Unit;

use App\Support\PrayerTimeHelper;
use PHPUnit\Framework\TestCase;

class PrayerTimeHelperTest extends TestCase
{
    public function test_minutes_to_time_basic(): void
    {
        $this->assertEquals('04:50', PrayerTimeHelper::minutesToTime(290));
        $this->assertEquals('12:00', PrayerTimeHelper::minutesToTime(720));
        $this->assertEquals('00:00', PrayerTimeHelper::minutesToTime(0));
        $this->assertEquals('23:59', PrayerTimeHelper::minutesToTime(1439));
    }

    public function test_minutes_to_time_midnight_rollover(): void
    {
        // 1440 minutes = exactly midnight = 00:00
        $this->assertEquals('00:00', PrayerTimeHelper::minutesToTime(1440));
        // 1441 = 00:01
        $this->assertEquals('00:01', PrayerTimeHelper::minutesToTime(1441));
    }

    public function test_minutes_to_time_negative_offset(): void
    {
        // -30 minutes means 23:30 (wraps around)
        $this->assertEquals('23:30', PrayerTimeHelper::minutesToTime(-30));
        $this->assertEquals('23:59', PrayerTimeHelper::minutesToTime(-1));
    }

    public function test_minutes_to_time_large_value(): void
    {
        // 3000 minutes = 2880 + 120 = 02:00 (50 hours wraps to 02:00)
        $this->assertEquals('02:00', PrayerTimeHelper::minutesToTime(3000));
    }

    public function test_parse_date_valid(): void
    {
        $date = PrayerTimeHelper::parseDate('2026-03-17');
        $this->assertNotNull($date);
        $this->assertEquals('2026-03-17', $date->format('Y-m-d'));
    }

    public function test_parse_date_invalid_format(): void
    {
        $this->assertNull(PrayerTimeHelper::parseDate('17-03-2026'));
        $this->assertNull(PrayerTimeHelper::parseDate('not-a-date'));
        $this->assertNull(PrayerTimeHelper::parseDate(''));
    }

    public function test_parse_date_overflowing_month(): void
    {
        // Month 13 does not exist — must return null, not overflow
        $this->assertNull(PrayerTimeHelper::parseDate('2026-13-01'));
    }

    public function test_parse_date_overflowing_day(): void
    {
        // February 30 does not exist — must return null, not overflow to March 2
        $this->assertNull(PrayerTimeHelper::parseDate('2026-02-30'));
    }

    public function test_day_of_year_jan_1(): void
    {
        $date = \Carbon\Carbon::parse('2026-01-01');
        $this->assertEquals(1, PrayerTimeHelper::dayOfYear($date));
    }

    public function test_day_of_year_dec_31_non_leap(): void
    {
        $date = \Carbon\Carbon::parse('2026-12-31');
        $this->assertEquals(365, PrayerTimeHelper::dayOfYear($date));
    }

    public function test_day_of_year_dec_31_leap(): void
    {
        $date = \Carbon\Carbon::parse('2028-12-31'); // 2028 is a leap year
        $this->assertEquals(366, PrayerTimeHelper::dayOfYear($date));
    }

    public function test_day_of_year_feb_29_leap(): void
    {
        $date = \Carbon\Carbon::parse('2028-02-29');
        $this->assertEquals(60, PrayerTimeHelper::dayOfYear($date));
    }
}
