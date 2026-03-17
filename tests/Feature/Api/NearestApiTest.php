<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NearestApiTest extends TestCase
{
    use RefreshDatabase;

    private function seedIsland(): void
    {
        DB::table('prayer_categories')->insert(['id' => 1]);
        DB::table('prayer_islands')->insert([
            'id'             => 1,
            'category_id'    => 1,
            'atoll'          => 'ކ',
            'name'           => 'މާލެ',
            'offset_minutes' => 0,
            'latitude'       => 4.175,
            'longitude'      => 73.509,
            'is_active'      => true,
        ]);
    }

    public function test_nearest_returns_island_for_valid_coords(): void
    {
        $this->seedIsland();
        $response = $this->getJson('/api/prayer-times/nearest?lat=4.175&lng=73.509');
        $response->assertOk()->assertJsonStructure(['island']);
        $this->assertEquals(1, $response->json('island.id'));
    }

    public function test_nearest_validates_lat(): void
    {
        $response = $this->getJson('/api/prayer-times/nearest?lat=999&lng=73.5');
        $response->assertStatus(422);
        $response->assertJsonStructure(['error']);
    }

    public function test_nearest_validates_lng(): void
    {
        $response = $this->getJson('/api/prayer-times/nearest?lat=4.1&lng=999');
        $response->assertStatus(422);
    }

    public function test_nearest_requires_lat_and_lng(): void
    {
        $response = $this->getJson('/api/prayer-times/nearest');
        $response->assertStatus(422);
        $response->assertJsonStructure(['errors']);
    }

    public function test_nearest_returns_404_with_no_islands(): void
    {
        $response = $this->getJson('/api/prayer-times/nearest?lat=4.175&lng=73.509');
        $response->assertStatus(404);
    }

    public function test_nearest_excludes_inactive_islands(): void
    {
        DB::table('prayer_categories')->insert(['id' => 1]);
        DB::table('prayer_islands')->insert([
            'id'             => 1,
            'category_id'    => 1,
            'atoll'          => 'ކ',
            'name'           => 'ތެ',
            'offset_minutes' => 0,
            'latitude'       => 4.175,
            'longitude'      => 73.509,
            'is_active'      => false, // inactive
        ]);
        $response = $this->getJson('/api/prayer-times/nearest?lat=4.175&lng=73.509');
        $response->assertStatus(404);
    }
}
