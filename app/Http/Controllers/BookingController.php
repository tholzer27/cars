<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\CleaningExtra;
use App\Models\CleaningPackage;
use App\Models\WorkingDayException;
use App\Models\WorkingHour;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Booking', $this->catalog());
    }

    public function index()
    {
        return $this->catalog();
    }

    private function catalog(): array
    {
        $packages = CleaningPackage::query()
            ->where('name', '!=', 'Individuell')
            ->where('is_available', true)
            ->with('includedServices:id')
            ->orderBy('price')
            ->get()
            ->map(function (CleaningPackage $package) {
                $package->setAttribute('included_service_ids', $package->includedServices->pluck('id')->all());
                $package->unsetRelation('includedServices');

                return $package;
            });

        $individualPackage = CleaningPackage::where('name', 'Individuell')
            ->where('is_available', true)
            ->first();

        $extras = CleaningExtra::query()
            ->where('is_available', true)
            ->where('price', '>', 0)
            ->with('packages:id,price')
            ->orderBy('name')
            ->get()
            ->map(function (CleaningExtra $extra) {
                $extra->setAttribute('package_prices', $extra->packages->pluck('price')->all());
                $extra->unsetRelation('packages');

                return $extra;
            });

        return [
            'packages' => $packages,
            'individualPackageId' => $individualPackage?->id,
            'individualOptions' => $this->individualOptions(),
            'extras' => $extras,
            'availability' => $this->availabilitySchedule(),
            'bookedSlots' => Booking::query()
                ->whereDate('booking_date', '>=', now()->toDateString())
                ->where('status', '!=', 'cancelled')
                ->orderBy('booking_date')
                ->orderBy('booking_time')
                ->get(['booking_date', 'booking_time', 'duration_minutes'])
                ->map(fn (Booking $booking) => [
                    'booking_date' => $booking->booking_date->format('Y-m-d'),
                    'booking_time' => $this->formatTime($booking->booking_time),
                    'duration_minutes' => $booking->duration_minutes,
                ])
                ->all(),
        ];
    }

    private function individualOptions(): array
    {
        $options = [
            [
                'key' => 'fussmatten',
                'label' => 'Fussmatten',
                'methods' => [
                    ['key' => 'staubsaugen', 'label' => 'Nur staubsaugen', 'price' => 10],
                    ['key' => 'nasswaesche', 'label' => 'Nasswäsche', 'price' => 25],
                    ['key' => 'tiefenreinigung', 'label' => 'Tiefenreinigung', 'price' => 35],
                ],
            ],
            [
                'key' => 'sitze',
                'label' => 'Sitze',
                'methods' => [
                    ['key' => 'absaugen', 'label' => 'Absaugen', 'price' => 25],
                    ['key' => 'textil_nass', 'label' => 'Textil-Nassreinigung', 'price' => 60],
                    ['key' => 'lederpflege', 'label' => 'Lederpflege', 'price' => 50],
                ],
            ],
            [
                'key' => 'scheiben',
                'label' => 'Scheiben',
                'methods' => [
                    ['key' => 'innen', 'label' => 'Innen reinigen', 'price' => 20],
                    ['key' => 'innen_aussen', 'label' => 'Innen und aussen reinigen', 'price' => 35],
                ],
            ],
            [
                'key' => 'cockpit',
                'label' => 'Cockpit und Armaturen',
                'methods' => [
                    ['key' => 'abwischen', 'label' => 'Abwischen', 'price' => 20],
                    ['key' => 'aufbereiten', 'label' => 'Aufbereiten inkl. Lenkrad', 'price' => 45],
                ],
            ],
            [
                'key' => 'aussen',
                'label' => 'Aussenreinigung',
                'methods' => [
                    ['key' => 'grundreinigung', 'label' => 'Grundreinigung', 'price' => 40],
                    ['key' => 'handwaesche', 'label' => 'Hand-Aussenwäsche', 'price' => 70],
                    ['key' => 'insekten', 'label' => 'Insektenentfernung', 'price' => 10],
                ],
            ],
            [
                'key' => 'felgen',
                'label' => 'Felgen',
                'methods' => [
                    ['key' => 'standard', 'label' => 'Standardreinigung', 'price' => 25],
                    ['key' => 'intensiv', 'label' => 'Intensivreinigung', 'price' => 45],
                ],
            ],
            [
                'key' => 'kofferraum',
                'label' => 'Kofferraum',
                'methods' => [
                    ['key' => 'aussaugen', 'label' => 'Aussaugen', 'price' => 10],
                    ['key' => 'nassreinigung', 'label' => 'Nassreinigung', 'price' => 30],
                ],
            ],
        ];

        $serviceNamesByMethod = [
            'fussmatten.staubsaugen' => 'Aussaugen',
            'fussmatten.nasswaesche' => 'Fussmattenaufbereitung',
            'fussmatten.tiefenreinigung' => 'Fussmattenaufbereitung',
            'sitze.absaugen' => 'Aussaugen',
            'sitze.textil_nass' => 'Sitzreinigung',
            'sitze.lederpflege' => 'Versiegelung von Kunst-/Lederteilen',
            'scheiben.innen' => 'Scheiben innen',
            'scheiben.innen_aussen' => 'Grundreinigung aussen inkl. Aussenscheiben',
            'cockpit.abwischen' => 'Cockpit abwischen',
            'cockpit.aufbereiten' => 'Cockpit aufbereiten',
            'aussen.grundreinigung' => 'Grundreinigung aussen inkl. Aussenscheiben',
            'aussen.handwaesche' => 'Hand-Aussenwäsche',
            'aussen.insekten' => 'Insektenentfernung',
            'felgen.standard' => 'Felgen',
            'felgen.intensiv' => 'Felgen',
            'kofferraum.aussaugen' => 'Kofferraum aussaugen',
            'kofferraum.nassreinigung' => 'Kofferraum aussaugen',
        ];
        $availableServices = CleaningExtra::query()
            ->where('is_available', true)
            ->get(['name', 'duration_minutes'])
            ->keyBy('name');

        return collect($options)
            ->map(function (array $option) use ($availableServices, $serviceNamesByMethod) {
                $option['methods'] = collect($option['methods'])
                    ->map(function (array $method) use ($availableServices, $serviceNamesByMethod, $option) {
                        $serviceName = $serviceNamesByMethod["{$option['key']}.{$method['key']}"] ?? null;
                        $service = $serviceName ? $availableServices->get($serviceName) : null;

                        if (! $service) {
                            return null;
                        }

                        $method['duration_minutes'] = $service->duration_minutes;

                        return $method;
                    })
                    ->filter()
                    ->values()
                    ->all();

                return $option;
            })
            ->filter(fn (array $option) => count($option['methods']) > 0)
            ->values()
            ->all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_mode' => 'required|in:package,individual',
            'cleaning_package_id' => 'required|exists:cleaning_packages,id',
            'booking_date' => 'required|date|after_or_equal:today',
            'booking_time' => 'required|date_format:H:i',
            'pickup_street' => 'required|string|max:255',
            'pickup_postal_code' => 'required|string|max:20',
            'pickup_city' => 'required|string|max:120',
            'vehicle_info' => 'nullable|string',
            'notes' => 'nullable|string',
            'extra_ids' => 'nullable|array',
            'extra_ids.*' => 'exists:cleaning_extras,id',
            'custom_configuration' => 'nullable|array',
            'custom_configuration.*.area_key' => 'required_with:custom_configuration|string',
            'custom_configuration.*.method_key' => 'required_with:custom_configuration|string',
        ]);

        $package = CleaningPackage::findOrFail($validated['cleaning_package_id']);
        abort_unless($package->is_available, 422, 'Dieses Paket ist aktuell nicht verfügbar.');
        abort_if(
            $validated['booking_mode'] === 'individual' && $package->name !== 'Individuell',
            422,
            'Individuelle Buchungen müssen mit dem individuellen Paket gespeichert werden.'
        );
        abort_if(
            $validated['booking_mode'] === 'package' && $package->name === 'Individuell',
            422,
            'Bitte wählen Sie ein Reinigungspaket aus.'
        );
        abort_if(
            $validated['booking_mode'] === 'individual' && empty($validated['custom_configuration']),
            422,
            'Bitte wählen Sie mindestens einen Bereich für Ihr individuelles Paket aus.'
        );

        $totalPrice = $package->price;
        $durationMinutes = $package->duration_minutes;
        $customConfiguration = null;

        if ($validated['booking_mode'] === 'individual') {
            $customConfiguration = $this->normalizeCustomConfiguration($validated['custom_configuration'] ?? []);
            $totalPrice = collect($customConfiguration)->sum('price');
            $durationMinutes = collect($customConfiguration)->sum('duration_minutes');
        }

        if ($validated['booking_mode'] === 'package' && isset($validated['extra_ids'])) {
            $includedServiceIds = $package->includedServices()->pluck('cleaning_extras.id')->all();
            abort_if(
                collect($validated['extra_ids'])->intersect($includedServiceIds)->isNotEmpty(),
                422,
                'Mindestens eine ausgewählte Leistung ist bereits im Paket enthalten.'
            );

            $extras = CleaningExtra::whereIn('id', $validated['extra_ids'])
                ->where('is_available', true)
                ->with('packages:id,price')
                ->get();

            abort_if(
                $extras->count() !== count(array_unique($validated['extra_ids'])),
                422,
                'Mindestens ein ausgewähltes Extra ist aktuell nicht verfügbar.'
            );

            abort_if(
                $extras->contains(fn (CleaningExtra $extra) => $extra->packages->isNotEmpty() && $extra->packages->max('price') <= $package->price),
                422,
                'Mindestens eine Leistung ist für dieses Paket nicht als Upgrade verfügbar.'
            );

            $totalPrice += $extras->sum('price');
            $durationMinutes += $extras->sum('duration_minutes');
        }

        abort_unless(
            $this->isBookableSlot($validated['booking_date'], $validated['booking_time'], $durationMinutes),
            422,
            'Dieser Termin ist für die gewählte Reinigungsdauer nicht verfügbar.'
        );

        $pickupAddress = trim($validated['pickup_street'])."\n"
            .trim($validated['pickup_postal_code']).' '.trim($validated['pickup_city']);

        $booking = Booking::create([
            'user_id' => Auth::id(),
            'cleaning_package_id' => $validated['cleaning_package_id'],
            'booking_mode' => $validated['booking_mode'],
            'booking_date' => $validated['booking_date'],
            'booking_time' => $validated['booking_time'],
            'pickup_address' => $pickupAddress,
            'vehicle_info' => $validated['vehicle_info'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'custom_configuration' => $customConfiguration,
            'total_price' => $totalPrice,
            'duration_minutes' => $durationMinutes,
            'status' => 'pending',
        ]);

        if ($validated['booking_mode'] === 'package' && isset($validated['extra_ids'])) {
            $booking->extras()->sync($validated['extra_ids']);
        }

        return response()->json([
            'message' => 'Buchung erfolgreich erstellt',
            'booking' => $booking,
        ], 201);
    }

    private function normalizeCustomConfiguration(array $submittedConfiguration): array
    {
        $options = collect($this->individualOptions())->keyBy('key');

        $normalized = collect($submittedConfiguration)
            ->map(function (array $selection) use ($options) {
                $area = $options->get($selection['area_key'] ?? '');
                abort_unless($area, 422, 'Mindestens ein ausgewählter Bereich ist ungültig.');

                $method = collect($area['methods'])->firstWhere('key', $selection['method_key'] ?? '');
                abort_unless($method, 422, 'Mindestens eine ausgewählte Reinigungsart ist ungültig.');

                return [
                    'area_key' => $area['key'],
                    'area_label' => $area['label'],
                    'method_key' => $method['key'],
                    'method_label' => $method['label'],
                    'price' => $method['price'],
                    'duration_minutes' => $method['duration_minutes'],
                ];
            })
            ->values();

        $nonAussenSelections = $normalized->where('area_key', '!=', 'aussen');
        abort_if(
            $nonAussenSelections->countBy('area_key')->contains(fn (int $count) => $count > 1),
            422,
            'Pro Bereich darf nur eine Reinigungsart ausgewählt werden.'
        );

        $aussenMethodKeys = $normalized->where('area_key', 'aussen')->pluck('method_key');
        abort_if(
            $aussenMethodKeys->duplicates()->isNotEmpty(),
            422,
            'Eine Reinigungsart darf nicht mehrfach ausgewählt werden.'
        );
        abort_if(
            $aussenMethodKeys->contains('grundreinigung') && $aussenMethodKeys->contains('handwaesche'),
            422,
            'Bitte wählen Sie bei der Aussenreinigung entweder Grundreinigung oder Hand-Wäsche.'
        );
        abort_if(
            $aussenMethodKeys->contains('insekten') && ! $aussenMethodKeys->intersect(['grundreinigung', 'handwaesche'])->isNotEmpty(),
            422,
            'Insektenentfernung kann nur zusätzlich zu Grundreinigung oder Hand-Wäsche gebucht werden.'
        );

        return $normalized->all();
    }

    private function availabilitySchedule(): array
    {
        return [
            'weekly_hours' => WorkingHour::query()
                ->orderBy('weekday')
                ->get()
                ->map(fn (WorkingHour $hour) => [
                    'weekday' => $hour->weekday,
                    'is_working' => $hour->is_working,
                    'starts_at' => $this->formatTime($hour->starts_at),
                    'ends_at' => $this->formatTime($hour->ends_at),
                ])
                ->all(),
            'exceptions' => WorkingDayException::query()
                ->orderBy('starts_on')
                ->get()
                ->map(fn (WorkingDayException $exception) => [
                    'starts_on' => ($exception->starts_on ?? $exception->date)->format('Y-m-d'),
                    'ends_on' => ($exception->ends_on ?? $exception->date)->format('Y-m-d'),
                    'is_working' => false,
                    'starts_at' => null,
                    'ends_at' => null,
                    'note' => $exception->note,
                ])
                ->all(),
        ];
    }

    private function isBookableSlot(string $date, string $time, int $durationMinutes): bool
    {
        $exception = WorkingDayException::query()
            ->where(function ($query) use ($date) {
                $query
                    ->where(function ($rangeQuery) use ($date) {
                        $rangeQuery
                            ->whereDate('starts_on', '<=', $date)
                            ->whereDate('ends_on', '>=', $date);
                    })
                    ->orWhereDate('date', $date);
            })
            ->first();
        if ($exception) {
            return false;
        }

        $weekday = Carbon::parse($date)->dayOfWeekIso;
        $workingHour = WorkingHour::where('weekday', $weekday)->first();

        if (! $workingHour) {
            if (! $this->timeIsWithinAvailability($weekday <= 5, '08:00', '18:00', $time, $durationMinutes)) {
                return false;
            }

            return ! $this->hasBookingOverlap($date, $time, $durationMinutes);
        }

        if (! $this->timeIsWithinAvailability($workingHour->is_working, $workingHour->starts_at, $workingHour->ends_at, $time, $durationMinutes)) {
            return false;
        }

        return ! $this->hasBookingOverlap($date, $time, $durationMinutes);
    }

    private function timeIsWithinAvailability(bool $isWorking, ?string $startsAt, ?string $endsAt, string $time, int $durationMinutes): bool
    {
        if (! $isWorking || ! $startsAt || ! $endsAt) {
            return false;
        }

        $startsAt = $this->formatTime($startsAt);
        $endsAt = $this->formatTime($endsAt);

        $selectionStart = Carbon::createFromFormat('H:i', $time);
        $selectionEnd = $selectionStart->copy()->addMinutes($durationMinutes);
        $availableStart = Carbon::createFromFormat('H:i', $startsAt);
        $availableEnd = Carbon::createFromFormat('H:i', $endsAt);

        return $selectionStart->greaterThanOrEqualTo($availableStart)
            && $selectionEnd->lessThanOrEqualTo($availableEnd);
    }

    private function hasBookingOverlap(string $date, string $time, int $durationMinutes): bool
    {
        $selectionStart = Carbon::parse("{$date} {$time}");
        $selectionEnd = $selectionStart->copy()->addMinutes($durationMinutes);

        return Booking::query()
            ->whereDate('booking_date', $date)
            ->where('status', '!=', 'cancelled')
            ->get()
            ->contains(function (Booking $booking) use ($selectionStart, $selectionEnd) {
                $bookingStart = Carbon::parse($booking->booking_date->format('Y-m-d').' '.$booking->booking_time);
                $bookingEnd = $bookingStart->copy()->addMinutes($booking->duration_minutes);

                return $selectionStart->lessThan($bookingEnd) && $selectionEnd->greaterThan($bookingStart);
            });
    }

    private function formatTime(?string $time): ?string
    {
        return $time ? substr($time, 0, 5) : null;
    }

    public function show(Booking $booking)
    {
        $this->authorize('view', $booking);

        return $booking->load('cleaningPackage', 'extras');
    }

    public function userBookings()
    {
        return Auth::user()->bookings()->with('cleaningPackage', 'extras')->get();
    }
}
