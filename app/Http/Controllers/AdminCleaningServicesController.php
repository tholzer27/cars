<?php

namespace App\Http\Controllers;

use App\Models\CleaningExtra;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AdminCleaningServicesController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('admin/CleaningServices', [
            'services' => CleaningExtra::query()
                ->orderByDesc('is_available')
                ->orderBy('name')
                ->get(['id', 'name', 'description', 'price', 'duration_minutes', 'is_available']),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'services' => 'required|array',
            'services.*.id' => 'nullable|integer|exists:cleaning_extras,id',
            'services.*.name' => 'required|string|max:120',
            'services.*.description' => 'nullable|string|max:500',
            'services.*.price' => 'required|numeric|min:0',
            'services.*.duration_minutes' => 'required|integer|min:1|max:1440',
            'services.*.is_available' => 'required|boolean',
            'deleted_service_ids' => 'array',
            'deleted_service_ids.*' => 'integer|exists:cleaning_extras,id',
        ]);

        DB::transaction(function () use ($validated) {
            $deleteIds = collect($validated['deleted_service_ids'] ?? [])->unique()->values();

            if ($deleteIds->isNotEmpty()) {
                $bookedServiceNames = CleaningExtra::query()
                    ->whereIn('id', $deleteIds)
                    ->whereHas('bookings')
                    ->pluck('name');

                if ($bookedServiceNames->isNotEmpty()) {
                    throw ValidationException::withMessages([
                        'services' => 'Folgende Dienstleistungen haben bereits Buchungen und können nicht gelöscht werden: '.$bookedServiceNames->join(', '),
                    ]);
                }

                CleaningExtra::query()
                    ->whereIn('id', $deleteIds)
                    ->delete();
            }

            foreach ($validated['services'] as $service) {
                CleaningExtra::updateOrCreate(
                    ['id' => $service['id'] ?? null],
                    [
                        'name' => $service['name'],
                        'description' => $service['description'] ?? null,
                        'price' => $service['price'],
                        'duration_minutes' => $service['duration_minutes'],
                        'is_available' => $service['is_available'],
                    ]
                );
            }
        });

        return back()->with('success', 'Dienstleistungen wurden gespeichert.');
    }
}
