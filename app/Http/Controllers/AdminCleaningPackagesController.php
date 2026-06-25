<?php

namespace App\Http\Controllers;

use App\Models\CleaningExtra;
use App\Models\CleaningPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AdminCleaningPackagesController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('admin/CleaningPackages', [
            'packages' => CleaningPackage::query()
                ->where('name', '!=', 'Individuell')
                ->with('includedServices:id')
                ->orderBy('price')
                ->get(['id', 'name', 'description', 'price', 'duration_minutes', 'is_available'])
                ->map(function (CleaningPackage $package) {
                    $package->setAttribute('service_ids', $package->includedServices->pluck('id')->all());
                    $package->unsetRelation('includedServices');

                    return $package;
                }),
            'services' => CleaningExtra::query()
                ->orderByDesc('is_available')
                ->orderBy('name')
                ->get(['id', 'name', 'is_available']),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'packages' => 'required|array',
            'packages.*.id' => 'nullable|integer|exists:cleaning_packages,id',
            'packages.*.name' => 'required|string|max:120',
            'packages.*.price' => 'required|numeric|min:0',
            'packages.*.duration_minutes' => 'required|integer|min:1|max:1440',
            'packages.*.is_available' => 'required|boolean',
            'packages.*.service_ids' => 'array',
            'packages.*.service_ids.*' => 'integer|exists:cleaning_extras,id',
            'deleted_package_ids' => 'array',
            'deleted_package_ids.*' => 'integer|exists:cleaning_packages,id',
        ]);

        DB::transaction(function () use ($validated) {
            $deleteIds = collect($validated['deleted_package_ids'] ?? [])->unique()->values();

            if ($deleteIds->isNotEmpty()) {
                $bookedPackageNames = CleaningPackage::query()
                    ->whereIn('id', $deleteIds)
                    ->whereHas('bookings')
                    ->pluck('name');

                if ($bookedPackageNames->isNotEmpty()) {
                    throw ValidationException::withMessages([
                        'packages' => 'Folgende Pakete haben bereits Buchungen und können nicht gelöscht werden: '.$bookedPackageNames->join(', '),
                    ]);
                }

                CleaningPackage::query()
                    ->whereIn('id', $deleteIds)
                    ->where('name', '!=', 'Individuell')
                    ->delete();
            }

            foreach ($validated['packages'] as $packageData) {
                $serviceIds = $packageData['service_ids'] ?? [];
                $description = CleaningExtra::query()
                    ->whereIn('id', $serviceIds)
                    ->orderBy('name')
                    ->pluck('name')
                    ->join("\n");

                $package = CleaningPackage::updateOrCreate(
                    ['id' => $packageData['id'] ?? null],
                    [
                        'name' => $packageData['name'],
                        'description' => $description ?: null,
                        'price' => $packageData['price'],
                        'duration_minutes' => $packageData['duration_minutes'],
                        'is_available' => $packageData['is_available'],
                    ],
                );

                $package->includedServices()->sync($serviceIds);
            }
        });

        return back()->with('success', 'Pakete wurden gespeichert.');
    }
}
