<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PrayerTimesApiTest extends TestCase
{
    use RefreshDatabase;

    private function seedFull(): void
    {
        DB::table('prayer_categories')->insert(['id' => 1]);
        DB::table('prayer_islands')->insert([
            'id'             => 1,
            'category_id'    => 1,
            'atoll'          => 'ކ',
            'atoll_latin'    => 'Kaafu',
            'name'           => 'މާލެ',
            'name_latin'     => 'Male',
            'offset_minutes' => 0,
            'latitude'       => 4.175,
            'longitude'      => 73.509,
            'is_active'      => true,
        ]);
        // Seed day 1 (Jan 1) and day 366 (leap day)
        DB::table('prayer_times')->insert([
            ['category_id' => 1, 'day_of_year' => 1,   'fajr' => 290, 'sunrise' => 370, 'dhuhr' => 730, 'asr' => 940, 'maghrib' => 1095, 'isha' => 1150],
            ['category_id' => 1, 'day_of_year' => 365, 'fajr' => 290, 'sunrise' => 370, 'dhuhr' => 730, 'asr' => 940, 'maghrib' => 1095, 'isha' => 1150],
            ['category_id' => 1, 'day_of_year' => 366, 'fajr' => 291, 'sunrise' => 371, 'dhuhr' => 731, 'asr' => 941, 'maghrib' => 1096, 'isha' => 1151],
        ]);
    }

    public function test_prayer_times_returns_correct_shape(): void
    {
        $this->seedFull();
        $response = $this->getJson('/api/prayer-times?island_id=1&date=2026-01-01');
        $response->assertOk()->assertJsonStructure([
            'island'  => ['id', 'name', 'atoll', 'offset_minutes'],
            'date',
            'prayers' => ['fajr', 'sunrise', 'dhuhr', 'asr', 'maghrib', 'isha'],
        ]);
    }

    public function test_prayer_times_does_not_leak_prayers_raw(): void
    {
        $this->seedFull();
        $response = $this->getJson('/api/prayer-times?island_id=1&date=2026-01-01');
        $response->assertOk();
        $this->assertArrayNotHasKey('prayers_raw', $response->json());
    }

    public function test_prayer_times_fajr_is_correct(): void
    {
        $this->seedFull();
        $response = $this->getJson('/api/prayer-times?island_id=1&date=2026-01-01');
        $this->assertEquals('04:50', $response->json('prayers.fajr')); // 290 min = 04:50
    }

    public function test_prayer_times_offset_is_applied(): void
    {
        DB::table('prayer_categories')->insert(['id' => 2]);
        DB::table('prayer_islands')->insert([
            'id' => 2, 'category_id' => 2, 'atoll' => 'ށ', 'name' => 'ތެ',
            'offset_minutes' => 5, 'is_active' => true,
        ]);
        DB::table('prayer_times')->insert([
            'category_id' => 2, 'day_of_year' => 1,
            'fajr' => 290, 'sunrise' => 370, 'dhuhr' => 730, 'asr' => 940, 'maghrib' => 1095, 'isha' => 1150,
        ]);
        $response = $this->getJson('/api/prayer-times?island_id=2&date=2026-01-01');
        // 290 + 5 = 295 min = 04:55
        $this->assertEquals('04:55', $response->json('prayers.fajr'));
    }

    public function test_prayer_times_requires_island_id(): void
    {
        $response = $this->getJson('/api/prayer-times?date=2026-01-01');
        $response->assertStatus(422);
    }

    public function test_prayer_times_nonexistent_island_returns_422(): void
    {
        // The `exists:prayer_islands,id` rule fires at the Form Request level => 422
        $response = $this->getJson('/api/prayer-times?island_id=99999&date=2026-01-01');
        $response->assertStatus(422);
        $response->assertJsonStructure(['error']);
    }

    public function test_prayer_times_missing_data_returns_404(): void
    {
        $this->seedFull();
        // Day 2 has no data seeded
        $response = $this->getJson('/api/prayer-times?island_id=1&date=2026-01-02');
        $response->assertStatus(404);
        $response->assertJsonStructure(['error']);
    }

    public function test_prayer_times_invalid_date_falls_back_to_today(): void
    {
        $this->seedFull();
        // Seed today's day_of_year
        $today      = now()->startOfDay();
        $dayOfYear  = (int) $today->dayOfYear;
        DB::table('prayer_times')->insertOrIgnore([
            'category_id' => 1, 'day_of_year' => $dayOfYear,
            'fajr' => 290, 'sunrise' => 370, 'dhuhr' => 730, 'asr' => 940, 'maghrib' => 1095, 'isha' => 1150,
        ]);
        $response = $this->getJson('/api/prayer-times?island_id=1&date=not-a-date');
        $response->assertOk();
        $this->assertEquals($today->toDateString(), $response->json('date'));
    }

    public function test_leap_year_day_366_returns_data(): void
    {
        $this->seedFull();
        // 2028 is a leap year; day 366 = Dec 31
        $response = $this->getJson('/api/prayer-times?island_id=1&date=2028-12-31');
        $response->assertOk();
        $this->assertEquals('04:51', $response->json('prayers.fajr')); // 291 min
    }

    public function test_day_365_non_leap_year_returns_data(): void
    {
        $this->seedFull();
        $response = $this->getJson('/api/prayer-times?island_id=1&date=2026-12-31');
        $response->assertOk();
    }

    public function test_api_error_response_is_json(): void
    {
        // Non-existent island should return JSON, not HTML
        $response = $this->get('/api/prayer-times?island_id=abc&date=2026-01-01', [
            'Accept' => 'application/json',
        ]);
        $response->assertHeader('Content-Type', 'application/json');
    }
}
