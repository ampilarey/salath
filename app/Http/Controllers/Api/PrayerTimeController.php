<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrayerTimeController extends Controller
{
    /**
     * Return all active islands grouped by atoll.
     */
    public function islands(): JsonResponse
    {
        $islands = DB::table('prayer_islands')
            ->where('is_active', true)
            ->orderBy('atoll')
            ->orderBy('name')
            ->get(['id', 'category_id', 'atoll', 'name', 'latitude', 'longitude', 'offset_minutes']);

        $grouped = $islands->groupBy('atoll')->map(fn($g) => $g->values())->toArray();

        return response()->json([
            'islands' => $islands,
            'grouped' => $grouped,
        ]);
    }

    /**
     * Find the nearest island to the given lat/lng.
     * GET /api/prayer-times/nearest?lat=4.17&lng=73.51
     */
    public function nearest(Request $request): JsonResponse
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        $lat = (float) $request->lat;
        $lng = (float) $request->lng;

        $island = DB::table('prayer_islands')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('is_active', true)
            ->selectRaw(
                '*, ( 6371 * acos( cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)) ) ) AS distance',
                [$lat, $lng, $lat]
            )
            ->orderBy('distance')
            ->first();

        if (!$island) {
            return response()->json(['error' => 'No island found'], 404);
        }

        return response()->json(['island' => $island]);
    }

    /**
     * Return prayer times for a given island and date.
     * GET /api/prayer-times?island_id=1&date=2026-03-17
     */
    public function times(Request $request): JsonResponse
    {
        $request->validate([
            'island_id' => 'required|integer|exists:prayer_islands,id',
            'date'      => 'nullable|date_format:Y-m-d',
        ]);

        $island = DB::table('prayer_islands')->find($request->island_id);
        if (!$island) {
            return response()->json(['error' => 'Island not found'], 404);
        }

        $date      = $request->date ? \Carbon\Carbon::createFromFormat('Y-m-d', $request->date) : now();
        $dayOfYear = (int) $date->dayOfYear;

        $row = DB::table('prayer_times')
            ->where('category_id', $island->category_id)
            ->where('day_of_year', $dayOfYear)
            ->first();

        if (!$row) {
            return response()->json(['error' => 'Prayer times not found for this date'], 404);
        }

        $offset = (int) $island->offset_minutes;

        return response()->json([
            'island'     => $island,
            'date'       => $date->toDateString(),
            'day_of_year'=> $dayOfYear,
            'prayers'    => [
                'fajr'    => $this->minutesToTime($row->fajr    + $offset),
                'sunrise' => $this->minutesToTime($row->sunrise + $offset),
                'dhuhr'   => $this->minutesToTime($row->dhuhr   + $offset),
                'asr'     => $this->minutesToTime($row->asr     + $offset),
                'maghrib' => $this->minutesToTime($row->maghrib + $offset),
                'isha'    => $this->minutesToTime($row->isha    + $offset),
            ],
            'prayers_raw' => [
                'fajr'    => $row->fajr    + $offset,
                'sunrise' => $row->sunrise + $offset,
                'dhuhr'   => $row->dhuhr   + $offset,
                'asr'     => $row->asr     + $offset,
                'maghrib' => $row->maghrib + $offset,
                'isha'    => $row->isha    + $offset,
            ],
        ]);
    }

    private function minutesToTime(int $minutes): string
    {
        $minutes = $minutes % 1440;
        if ($minutes < 0) $minutes += 1440;
        $h = intdiv($minutes, 60);
        $m = $minutes % 60;
        return sprintf('%02d:%02d', $h, $m);
    }
}
