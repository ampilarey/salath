<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class IslandsApiTest extends TestCase
{
    use RefreshDatabase;

    private function seedIslands(): void
    {
        DB::table('prayer_categories')->insert([['id' => 1], ['id' => 2]]);

        // Each row must have identical keys for SQLite batch insert
        $base = [
            'atoll_latin' => null, 'name_latin' => null,
            'latitude' => null, 'longitude' => null,
            'offset_minutes' => 0,
        ];

        DB::table('prayer_islands')->insert([
            array_merge($base, [
                'id' => 1, 'category_id' => 1, 'atoll' => 'ކ', 'atoll_latin' => 'Kaafu',
                'name' => 'މާލެ', 'name_latin' => 'Male',
                'latitude' => 4.175, 'longitude' => 73.509, 'is_active' => true,
            ]),
            array_merge($base, [
                'id' => 2, 'category_id' => 1, 'atoll' => 'ކ', 'atoll_latin' => 'Kaafu',
                'name' => 'ހުޅުލެ', 'name_latin' => 'Hulhule',
                'latitude' => 4.19, 'longitude' => 73.52, 'is_active' => true,
            ]),
            array_merge($base, [
                'id' => 3, 'category_id' => 2, 'atoll' => 'ހ', 'atoll_latin' => 'Haa Alif',
                'name' => 'ދިއްދޫ', 'name_latin' => 'Dhidhdhoo',
                'is_active' => false,
            ]),
        ]);
    }

    public function test_islands_endpoint_returns_json(): void
    {
        $this->seedIslands();
        $response = $this->getJson('/api/prayer-times/islands');
        $response->assertOk()->assertJsonStructure(['islands', 'grouped']);
    }

    public function test_islands_endpoint_returns_only_active(): void
    {
        $this->seedIslands();
        $response = $this->getJson('/api/prayer-times/islands');
        $data     = $response->json('islands');

        $this->assertCount(2, $data);
        $names = array_column($data, 'name');
        $this->assertContains('މާލެ', $names);
        $this->assertNotContains('ދިއްދޫ', $names);
    }

    public function test_islands_response_shape(): void
    {
        $this->seedIslands();
        $response = $this->getJson('/api/prayer-times/islands');
        $island   = $response->json('islands.0');

        $this->assertArrayHasKey('id', $island);
        $this->assertArrayHasKey('atoll', $island);
        $this->assertArrayHasKey('atoll_latin', $island);
        $this->assertArrayHasKey('name', $island);
        $this->assertArrayHasKey('name_latin', $island);
        $this->assertArrayHasKey('latitude', $island);
        $this->assertArrayHasKey('longitude', $island);
        $this->assertArrayHasKey('offset_minutes', $island);

        // prayers_raw must NOT be exposed
        $this->assertArrayNotHasKey('prayers_raw', $island);
        $this->assertArrayNotHasKey('category_id', $island);
    }

    public function test_islands_grouped_by_atoll(): void
    {
        $this->seedIslands();
        $response = $this->getJson('/api/prayer-times/islands');
        $grouped  = $response->json('grouped');

        $this->assertArrayHasKey('ކ', $grouped);
        $this->assertCount(2, $grouped['ކ']);
    }
}
