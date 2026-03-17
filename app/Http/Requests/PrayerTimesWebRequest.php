<?php

namespace App\Http\Requests;

use App\Support\PrayerTimeHelper;
use Illuminate\Foundation\Http\FormRequest;

class PrayerTimesWebRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'island_id' => ['nullable', 'integer', 'min:1'],
            'date'      => ['nullable', 'string'],
        ];
    }

    public function resolvedDate(): \Carbon\Carbon
    {
        $raw = $this->query('date', '');

        if ($raw) {
            $parsed = PrayerTimeHelper::parseDate($raw);
            if ($parsed !== null) {
                return $parsed;
            }
        }

        return now()->startOfDay();
    }

    public function resolvedIslandId(): int
    {
        return (int) $this->query('island_id', 0);
    }
}
