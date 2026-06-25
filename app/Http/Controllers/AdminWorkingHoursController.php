<?php

namespace App\Http\Controllers;

use App\Models\WorkingDayException;
use App\Models\WorkingHour;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AdminWorkingHoursController extends Controller
{
    public function edit(): Response
    {
        $this->ensureWeeklyHoursExist();

        return Inertia::render('admin/WorkingHours', [
            'weeklyHours' => $this->weeklyHours(),
            'exceptions' => $this->exceptions(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'weekly_hours' => 'required|array|size:7',
            'weekly_hours.*.weekday' => 'required|integer|between:1,7',
            'weekly_hours.*.is_working' => 'required|boolean',
            'weekly_hours.*.starts_at' => 'nullable|date_format:H:i',
            'weekly_hours.*.ends_at' => 'nullable|date_format:H:i',
            'exceptions' => 'nullable|array',
            'exceptions.*.starts_on' => 'required|date',
            'exceptions.*.ends_on' => 'required|date|after_or_equal:exceptions.*.starts_on',
            'exceptions.*.note' => 'nullable|string|max:120',
        ]);

        $this->validateTimeRanges($validated['weekly_hours'], 'weekly_hours');

        foreach ($validated['weekly_hours'] as $day) {
            WorkingHour::updateOrCreate(
                ['weekday' => $day['weekday']],
                [
                    'is_working' => $day['is_working'],
                    'starts_at' => $day['is_working'] ? $day['starts_at'] : null,
                    'ends_at' => $day['is_working'] ? $day['ends_at'] : null,
                ]
            );
        }

        WorkingDayException::query()->delete();
        foreach ($validated['exceptions'] ?? [] as $exception) {
            WorkingDayException::create([
                'date' => $exception['starts_on'],
                'starts_on' => $exception['starts_on'],
                'ends_on' => $exception['ends_on'],
                'is_working' => false,
                'starts_at' => null,
                'ends_at' => null,
                'note' => $exception['note'] ?? null,
            ]);
        }

        return back()->with('success', 'Arbeitszeiten wurden gespeichert.');
    }

    private function weeklyHours(): array
    {
        return WorkingHour::query()
            ->orderBy('weekday')
            ->get()
            ->map(fn (WorkingHour $hour) => [
                'weekday' => $hour->weekday,
                'is_working' => $hour->is_working,
                'starts_at' => $this->formatTime($hour->starts_at),
                'ends_at' => $this->formatTime($hour->ends_at),
            ])
            ->all();
    }

    private function exceptions(): array
    {
        return WorkingDayException::query()
            ->orderBy('starts_on')
            ->get()
            ->map(fn (WorkingDayException $exception) => [
                'starts_on' => ($exception->starts_on ?? $exception->date)->format('Y-m-d'),
                'ends_on' => ($exception->ends_on ?? $exception->date)->format('Y-m-d'),
                'note' => $exception->note,
            ])
            ->all();
    }

    private function ensureWeeklyHoursExist(): void
    {
        foreach (range(1, 7) as $weekday) {
            WorkingHour::firstOrCreate(
                ['weekday' => $weekday],
                [
                    'is_working' => $weekday <= 5,
                    'starts_at' => $weekday <= 5 ? '08:00' : null,
                    'ends_at' => $weekday <= 5 ? '18:00' : null,
                ]
            );
        }
    }

    private function formatTime(?string $time): ?string
    {
        return $time ? substr($time, 0, 5) : null;
    }

    private function validateTimeRanges(array $entries, string $field): void
    {
        foreach ($entries as $index => $entry) {
            if (! $entry['is_working']) {
                continue;
            }

            if (empty($entry['starts_at']) || empty($entry['ends_at'])) {
                throw ValidationException::withMessages([
                    "{$field}.{$index}.starts_at" => 'Für Arbeitstage müssen Start- und Endzeit gesetzt sein.',
                ]);
            }

            if ($entry['ends_at'] <= $entry['starts_at']) {
                throw ValidationException::withMessages([
                    "{$field}.{$index}.ends_at" => 'Die Endzeit muss nach der Startzeit liegen.',
                ]);
            }
        }
    }
}
