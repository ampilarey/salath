<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function prayerTimes(Request $request)
    {
        $islands = DB::table('prayer_islands')
            ->where('is_active', true)
            ->orderBy('atoll')
            ->orderBy('name')
            ->get(['id', 'category_id', 'atoll', 'name', 'latitude', 'longitude', 'offset_minutes']);

        $grouped = $islands->groupBy('atoll');

        // Default island (Male' — category 43, island 0 offset)
        $defaultIslandId = (int) $request->query('island_id', 0);
        $defaultDateStr  = $request->query('date', now()->toDateString());

        try {
            $defaultDate = \Carbon\Carbon::createFromFormat('Y-m-d', $defaultDateStr);
        } catch (\Exception) {
            $defaultDate = now();
        }

        if ($defaultIslandId === 0) {
            // Find Male' or first island
            $male = $islands->firstWhere('name', 'މާލެ')
                ?? $islands->firstWhere('name', 'Male')
                ?? $islands->first();
            $defaultIslandId = $male ? (int) $male->id : ($islands->first()?->id ?? 1);
        }

        $island = DB::table('prayer_islands')->find($defaultIslandId);
        $prayers = null;

        if ($island) {
            $dayOfYear = (int) $defaultDate->dayOfYear;
            $row = DB::table('prayer_times')
                ->where('category_id', $island->category_id)
                ->where('day_of_year', $dayOfYear)
                ->first();

            if ($row) {
                $offset = (int) $island->offset_minutes;
                $prayers = [
                    'fajr'    => $this->minutesToTime($row->fajr    + $offset),
                    'sunrise' => $this->minutesToTime($row->sunrise + $offset),
                    'dhuhr'   => $this->minutesToTime($row->dhuhr   + $offset),
                    'asr'     => $this->minutesToTime($row->asr     + $offset),
                    'maghrib' => $this->minutesToTime($row->maghrib + $offset),
                    'isha'    => $this->minutesToTime($row->isha    + $offset),
                ];
            }
        }

        return view('prayer-times', [
            'islands'         => $islands,
            'grouped'         => $grouped,
            'selectedIsland'  => $island,
            'selectedDate'    => $defaultDate,
            'prayers'         => $prayers,
        ]);
    }

    private function minutesToTime(int $minutes): string
    {
        $minutes = $minutes % 1440;
        if ($minutes < 0) $minutes += 1440;
        return sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
    }
}
