<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WebRoutesTest extends TestCase
{
    use RefreshDatabase;

    private function seedMinimal(int $islandId = 1, int $categoryId = 1, int $dayOfYear = 1): void
    {
        DB::table('prayer_categories')->insert(['id' => $categoryId]);
        DB::table('prayer_islands')->insert([
            'id'             => $islandId,
            'category_id'    => $categoryId,
            'atoll'          => 'ކ',
            'atoll_latin'    => 'Kaafu',
            'name'           => 'މާލެ',
            'name_latin'     => 'Male',
            'offset_minutes' => 0,
            'latitude'       => 4.175,
            'longitude'      => 73.509,
            'is_active'      => true,
        ]);
        DB::table('prayer_times')->insert([
            'category_id' => $categoryId,
            'day_of_year' => $dayOfYear,
            'fajr'        => 290,  // 04:50
            'sunrise'     => 370,  // 06:10
            'dhuhr'       => 730,  // 12:10
            'asr'         => 940,  // 15:40
            'maghrib'     => 1095, // 18:15
            'isha'        => 1150, // 19:10
        ]);
    }

    public function test_root_redirects_to_prayer_times(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/prayer-times');
    }

    public function test_prayer_times_page_loads(): void
    {
        $this->seedMinimal();
        $response = $this->get('/prayer-times');
        $response->assertOk();
        $response->assertViewIs('prayer-times');
    }

    public function test_prayer_times_page_shows_islands(): void
    {
        $this->seedMinimal();
        $response = $this->get('/prayer-times');
        $response->assertOk();
        $response->assertSee('މާލެ');
    }

    public function test_prayer_times_with_valid_island_and_date(): void
    {
        $this->seedMinimal(dayOfYear: 1);
        $response = $this->get('/prayer-times?island_id=1&date=2026-01-01');
        $response->assertOk();
        $response->assertSee('04:50');
    }

    public function test_invalid_date_falls_back_to_today(): void
    {
        $this->seedMinimal();
        // A garbage date string should not crash; it should fall back to today
        $response = $this->get('/prayer-times?island_id=1&date=not-a-date');
        $response->assertOk();
    }

    public function test_overflowing_date_falls_back_to_today(): void
    {
        $this->seedMinimal();
        // 2026-13-01 is not a real date — must not overflow to 2027-01-01
        $response = $this->get('/prayer-times?island_id=1&date=2026-13-01');
        $response->assertOk();
    }

    public function test_inactive_island_is_not_shown(): void
    {
        DB::table('prayer_categories')->insert(['id' => 1]);
        DB::table('prayer_islands')->insert([
            'id'             => 10,
            'category_id'    => 1,
            'atoll'          => 'ހ',
            'name'           => 'ތިލަދުންމަތި',
            'offset_minutes' => 0,
            'is_active'      => false,
        ]);
        $response = $this->get('/prayer-times');
        $response->assertOk();
        $response->assertDontSee('ތިލަދުންމަތި');
    }

    public function test_no_prayer_data_shows_empty_state(): void
    {
        DB::table('prayer_categories')->insert(['id' => 1]);
        DB::table('prayer_islands')->insert([
            'id' => 1, 'category_id' => 1, 'atoll' => 'ކ', 'name' => 'ތެ',
            'offset_minutes' => 0, 'is_active' => true,
        ]);
        // No prayer_times rows — should show empty state, not crash
        $response = $this->get('/prayer-times?island_id=1&date=2026-01-01');
        $response->assertOk();
    }
}
