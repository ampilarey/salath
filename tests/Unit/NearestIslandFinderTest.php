<?php

namespace Tests\Unit;

use App\Domains\PrayerTimes\Services\NearestIslandFinder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NearestIslandFinderTest extends TestCase
{
    use RefreshDatabase;

    private function seedIslands(): void
    {
        DB::table('prayer_categories')->insert(['id' => 1]);
        DB::table('prayer_islands')->insert([
            [
                'id'             => 1,
                'category_id'    => 1,
                'atoll'          => 'ކ',
                'name'           => 'Male',
                'offset_minutes' => 0,
                'latitude'       => 4.175,
                'longitude'      => 73.509,
                'is_active'      => true,
            ],
            [
                'id'             => 2,
                'category_id'    => 1,
                'atoll'          => 'ށ',
                'name'           => 'Funadhoo',
                'offset_minutes' => 0,
                'latitude'       => 6.15,
                'longitude'      => 73.29,
                'is_active'      => true,
            ],
            [
                'id'             => 3,
                'category_id'    => 1,
                'atoll'          => 'ލ',
                'name'           => 'Fonadhoo',
                'offset_minutes' => 0,
                'latitude'       => 1.84,
                'longitude'      => 73.50,
                'is_active'      => true,
            ],
        ]);
    }

    public function test_finds_nearest_island(): void
    {
        $this->seedIslands();
        $finder = app(NearestIslandFinder::class);

        // Coordinates very close to Male (id=1)
        $island = $finder->find(4.175, 73.509);

        $this->assertNotNull($island);
        $this->assertEquals(1, $island->id);
        $this->assertEquals('Male', $island->name);
    }

    public function test_finds_nearest_to_south(): void
    {
        $this->seedIslands();
        $finder = app(NearestIslandFinder::class);

        // Closest to Fonadhoo (id=3, lat=1.84)
        $island = $finder->find(2.0, 73.5);

        $this->assertNotNull($island);
        $this->assertEquals(3, $island->id);
    }

    public function test_returns_null_with_no_islands(): void
    {
        $finder = app(NearestIslandFinder::class);
        $island = $finder->find(4.175, 73.509);
        $this->assertNull($island);
    }

    public function test_excludes_inactive_islands(): void
    {
        DB::table('prayer_categories')->insert(['id' => 1]);
        DB::table('prayer_islands')->insert([
            [
                'id' => 1, 'category_id' => 1, 'atoll' => 'ކ', 'name' => 'Inactive',
                'offset_minutes' => 0, 'latitude' => 4.175, 'longitude' => 73.509, 'is_active' => false,
            ],
            [
                'id' => 2, 'category_id' => 1, 'atoll' => 'ށ', 'name' => 'Active',
                'offset_minutes' => 0, 'latitude' => 6.15, 'longitude' => 73.29, 'is_active' => true,
            ],
        ]);

        $finder = app(NearestIslandFinder::class);
        // Even though island 1 is geographically closer, it's inactive
        $island = $finder->find(4.175, 73.509);
        $this->assertNotNull($island);
        $this->assertEquals(2, $island->id);
    }

    public function test_result_is_island_data_dto(): void
    {
        $this->seedIslands();
        $island = app(NearestIslandFinder::class)->find(4.175, 73.509);
        $this->assertInstanceOf(\App\Domains\PrayerTimes\DTOs\IslandData::class, $island);
    }
}
