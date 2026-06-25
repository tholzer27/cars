<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import type { DateValue } from '@internationalized/date';
import {
    DateFormatter,
    getLocalTimeZone,
    today,
} from '@internationalized/date';
import {
    CalendarDays,
    Check,
    Clock,
    LogOut,
    Sparkles,
    X,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import Button from '@/components/ui/button/Button.vue';
import { Calendar } from '@/components/ui/calendar';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Toaster } from '@/components/ui/sonner';
import PublicFooter from '@/components/PublicFooter.vue';
import { cn } from '@/lib/utils';

interface CleaningPackage {
    id: number;
    name: string;
    description: string | null;
    price: number;
    duration_minutes: number;
    is_available: boolean;
    included_service_ids: number[];
}

interface CleaningExtra {
    id: number;
    name: string;
    description: string | null;
    price: number;
    duration_minutes: number;
    is_available: boolean;
    package_prices: number[];
}

interface IndividualMethod {
    key: string;
    label: string;
    price: number;
    duration_minutes: number;
}

interface IndividualOption {
    key: string;
    label: string;
    methods: IndividualMethod[];
}

interface AvailabilityEntry {
    is_working: boolean;
    starts_at: string | null;
    ends_at: string | null;
}

interface WeeklyAvailability extends AvailabilityEntry {
    weekday: number;
}

interface DateAvailability extends AvailabilityEntry {
    starts_on: string;
    ends_on: string;
    note: string | null;
}

interface Availability {
    weekly_hours: WeeklyAvailability[];
    exceptions: DateAvailability[];
}

interface BookedSlot {
    booking_date: string;
    booking_time: string;
    duration_minutes: number;
}

const props = defineProps<{
    packages?: CleaningPackage[];
    individualPackageId?: number | null;
    individualOptions?: IndividualOption[];
    extras?: CleaningExtra[];
    availability?: Availability;
    bookedSlots?: BookedSlot[];
}>();

const page = usePage();
const isAuthenticated = computed(() => Boolean(page.props.auth?.user));
const currentPath = computed(() => page.url.split('?')[0]);
const navigation = [
    { label: 'Startseite', href: '/' },
    { label: 'Buchungsseite', href: '/booking' },
    { label: 'Pakete', href: '/admin/packages' },
    { label: 'Dienstleistungen', href: '/admin/services' },
    { label: 'Arbeitszeiten', href: '/admin/working-hours' },
    { label: 'Profil', href: '/settings/profile' },
];

const isActive = (href: string) => currentPath.value === href;

const packages = ref<CleaningPackage[]>(
    Array.isArray(props.packages) ? props.packages : [],
);
const extras = ref<CleaningExtra[]>(
    Array.isArray(props.extras) ? props.extras : [],
);
const individualOptions = ref<IndividualOption[]>(
    Array.isArray(props.individualOptions) ? props.individualOptions : [],
);
const bookingMode = ref<'package' | 'individual'>('package');
const selectedPackageId = ref<number | null>(
    packages.value.find((pkg) => pkg.is_available)?.id ?? null,
);
const selectedExtras = ref<number[]>([]);
const selectedMethods = ref<Record<string, string[]>>({});
const selectedDate = ref<DateValue>();
const bookingDate = ref('');
const bookingTime = ref('');
const pickupStreet = ref('');
const pickupPostalCode = ref('');
const pickupCity = ref('');
const vehicleInfo = ref('');
const notes = ref('');
const isLoading = ref(false);

const selectedPackage = computed(() =>
    packages.value.find((pkg) => pkg.id === selectedPackageId.value),
);
const bookingPackageId = computed(() =>
    bookingMode.value === 'individual'
        ? (props.individualPackageId ?? null)
        : selectedPackageId.value,
);

const packageFeatures = (pkg: CleaningPackage) =>
    (pkg.description ?? '')
        .split('\n')
        .map((feature) => feature.trim())
        .filter(Boolean);

const addOnExtras = computed(() => {
    const includedServiceIds =
        selectedPackage.value?.included_service_ids ?? [];

    return extras.value.filter(
        (extra) =>
            extra.is_available &&
            !includedServiceIds.includes(extra.id) &&
            (!extra.package_prices.length ||
                extra.package_prices.some(
                    (price) => price > (selectedPackage.value?.price ?? 0),
                )),
    );
});
const selectedExtraItems = computed(() =>
    selectedExtras.value
        .map((extraId) => extras.value.find((extra) => extra.id === extraId))
        .filter((extra): extra is CleaningExtra => Boolean(extra)),
);

const customConfiguration = computed(() =>
    individualOptions.value.flatMap((option) => {
        const methodKeys = selectedMethods.value[option.key] ?? [];

        return methodKeys
            .map((methodKey) => {
                const method = option.methods.find(
                    (item) => item.key === methodKey,
                );

                if (!method) {
                    return null;
                }

                return {
                    area_key: option.key,
                    area_label: option.label,
                    method_key: method.key,
                    method_label: method.label,
                    price: method.price,
                    duration_minutes: method.duration_minutes,
                };
            })
            .filter((item): item is NonNullable<typeof item> => Boolean(item));
    }),
);

const totalPrice = computed(() => {
    if (bookingMode.value === 'individual') {
        return customConfiguration.value.reduce(
            (sum, item) => sum + item.price,
            0,
        );
    }

    return (
        (selectedPackage.value?.price ?? 0) +
        selectedExtraItems.value.reduce((sum, extra) => sum + extra.price, 0)
    );
});

const totalDuration = computed(() => {
    if (bookingMode.value === 'individual') {
        return customConfiguration.value.reduce(
            (sum, item) => sum + item.duration_minutes,
            0,
        );
    }

    return (
        (selectedPackage.value?.duration_minutes ?? 0) +
        selectedExtraItems.value.reduce(
            (sum, extra) => sum + extra.duration_minutes,
            0,
        )
    );
});

const hasInvalidIndividualConfiguration = computed(() => {
    const aussenSelections = selectedMethods.value.aussen ?? [];

    return (
        aussenSelections.includes('insekten') &&
        !aussenSelections.some((key) =>
            ['grundreinigung', 'handwaesche'].includes(key),
        )
    );
});

const defaultDatePlaceholder = today(getLocalTimeZone());
const dateFormatter = new DateFormatter('de-CH', {
    dateStyle: 'long',
});
const formattedBookingDate = computed(() =>
    selectedDate.value
        ? dateFormatter.format(selectedDate.value.toDate(getLocalTimeZone()))
        : 'Datum auswählen',
);
const availability = computed<Availability>(() => ({
    weekly_hours: props.availability?.weekly_hours ?? [],
    exceptions: props.availability?.exceptions ?? [],
}));
const selectedDateAvailability = computed(() =>
    selectedDate.value ? getAvailabilityForDate(selectedDate.value) : null,
);
const timeOptions = computed(() => {
    const dayAvailability = selectedDateAvailability.value;

    if (
        !dayAvailability?.is_working ||
        !dayAvailability.starts_at ||
        !dayAvailability.ends_at
    ) {
        return [];
    }

    return buildTimeOptions(dayAvailability.starts_at, dayAvailability.ends_at);
});

const formatPrice = (price: number) => `CHF ${price.toFixed(0)}`;
const formatDuration = (durationMinutes: number) => {
    const hours = Math.floor(durationMinutes / 60);
    const minutes = durationMinutes % 60;

    if (!hours) {
        return `${minutes} Min.`;
    }

    if (!minutes) {
        return `${hours} Std.`;
    }

    return `${hours} Std. ${minutes} Min.`;
};

const setBookingDate = (value: DateValue | undefined) => {
    selectedDate.value = value;
    bookingDate.value = value?.toString() ?? '';

    if (!timeOptions.value.includes(bookingTime.value)) {
        bookingTime.value = '';
    }
};

const getAvailabilityForDate = (date: DateValue): AvailabilityEntry => {
    const dateKey = date.toString();
    const exception = availability.value.exceptions.find(
        (item) => item.starts_on <= dateKey && item.ends_on >= dateKey,
    );

    if (exception) {
        return exception;
    }

    const jsDate = date.toDate(getLocalTimeZone());
    const weekday = jsDate.getDay() === 0 ? 7 : jsDate.getDay();
    const weekly = availability.value.weekly_hours.find(
        (item) => item.weekday === weekday,
    );

    return (
        weekly ?? {
            is_working: weekday <= 5,
            starts_at: '08:00',
            ends_at: '18:00',
        }
    );
};

const isDateUnavailable = (date: DateValue) => {
    const dayAvailability = getAvailabilityForDate(date);

    return (
        !dayAvailability.is_working ||
        !dayAvailability.starts_at ||
        !dayAvailability.ends_at
    );
};

const buildTimeOptions = (startsAt: string, endsAt: string) => {
    const [startHour, startMinute] = startsAt.split(':').map(Number);
    const [endHour, endMinute] = endsAt.split(':').map(Number);
    const start = startHour * 60 + startMinute;
    const end = endHour * 60 + endMinute;
    const options: string[] = [];

    for (let totalMinutes = start; totalMinutes <= end; totalMinutes += 30) {
        const candidateEnd = totalMinutes + totalDuration.value;
        const overlapsBooking = (props.bookedSlots ?? []).some((slot) => {
            if (slot.booking_date !== bookingDate.value) {
                return false;
            }

            const [slotHour, slotMinute] = slot.booking_time
                .split(':')
                .map(Number);
            const slotStart = slotHour * 60 + slotMinute;
            const slotEnd = slotStart + slot.duration_minutes;

            return totalMinutes < slotEnd && candidateEnd > slotStart;
        });

        if (!totalDuration.value || candidateEnd > end || overlapsBooking) {
            continue;
        }

        const hours = Math.floor(totalMinutes / 60)
            .toString()
            .padStart(2, '0');
        const minutes = (totalMinutes % 60).toString().padStart(2, '0');
        options.push(`${hours}:${minutes}`);
    }

    return options;
};

watch(timeOptions, (options) => {
    if (!options.includes(bookingTime.value)) {
        bookingTime.value = '';
    }
});

const setBookingMode = (mode: 'package' | 'individual') => {
    bookingMode.value = mode;
    selectedExtras.value = [];
    selectedMethods.value = {};

    if (mode === 'package' && !selectedPackageId.value) {
        selectedPackageId.value =
            packages.value.find((pkg) => pkg.is_available)?.id ?? null;
    }
};

const toggleExtra = (extra: CleaningExtra) => {
    if (!extra.is_available) {
        return;
    }

    if (selectedExtras.value.includes(extra.id)) {
        selectedExtras.value = selectedExtras.value.filter(
            (extraId) => extraId !== extra.id,
        );

        return;
    }

    selectedExtras.value = [...selectedExtras.value, extra.id];
};

const selectPackage = (pkg: CleaningPackage) => {
    if (!pkg.is_available) {
        return;
    }

    selectedPackageId.value = pkg.id;
    selectedExtras.value = selectedExtras.value.filter(
        (extraId) => !pkg.included_service_ids.includes(extraId),
    );
};

const toggleMethod = (option: IndividualOption, method: IndividualMethod) => {
    const selectedForArea = selectedMethods.value[option.key] ?? [];

    if (option.key !== 'aussen') {
        if (selectedForArea.includes(method.key)) {
            const next = { ...selectedMethods.value };
            delete next[option.key];
            selectedMethods.value = next;

            return;
        }

        selectedMethods.value = {
            ...selectedMethods.value,
            [option.key]: [method.key],
        };

        return;
    }

    if (method.key === 'insekten') {
        const nextSelection = selectedForArea.includes(method.key)
            ? selectedForArea.filter((key) => key !== method.key)
            : [...selectedForArea, method.key];

        selectedMethods.value = {
            ...selectedMethods.value,
            [option.key]: nextSelection,
        };

        return;
    }

    const addOns = selectedForArea.filter((key) => key === 'insekten');
    const nextSelection = selectedForArea.includes(method.key)
        ? addOns
        : [method.key, ...addOns];

    selectedMethods.value = {
        ...selectedMethods.value,
        [option.key]: nextSelection,
    };
};

const isExtraSelected = (extraId: number) =>
    selectedExtras.value.includes(extraId);
const isMethodSelected = (option: IndividualOption, method: IndividualMethod) =>
    (selectedMethods.value[option.key] ?? []).includes(method.key);

const submitBooking = async () => {
    if (!bookingPackageId.value || !bookingDate.value || !bookingTime.value) {
        toast.error('Bitte Auswahl, Datum und Uhrzeit auswählen.');

        return;
    }

    if (
        !pickupStreet.value.trim() ||
        !pickupPostalCode.value.trim() ||
        !pickupCity.value.trim()
    ) {
        toast.error('Bitte vollständige Abholadresse angeben.');

        return;
    }

    if (
        bookingMode.value === 'individual' &&
        customConfiguration.value.length === 0
    ) {
        toast.error(
            'Bitte mindestens einen Bereich und eine Reinigungsart auswählen.',
        );

        return;
    }

    if (
        bookingMode.value === 'individual' &&
        hasInvalidIndividualConfiguration.value
    ) {
        toast.error(
            'Insektenentfernung kann nur zusätzlich zu Grundreinigung oder Hand-Wäsche gebucht werden.',
        );

        return;
    }

    isLoading.value = true;

    try {
        const response = await fetch('/bookings', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN':
                    document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute('content') || '',
            },
            body: JSON.stringify({
                booking_mode: bookingMode.value,
                cleaning_package_id: bookingPackageId.value,
                booking_date: bookingDate.value,
                booking_time: bookingTime.value,
                pickup_street: pickupStreet.value,
                pickup_postal_code: pickupPostalCode.value,
                pickup_city: pickupCity.value,
                vehicle_info: vehicleInfo.value,
                notes: notes.value,
                extra_ids:
                    bookingMode.value === 'package' ? selectedExtras.value : [],
                custom_configuration:
                    bookingMode.value === 'individual'
                        ? customConfiguration.value
                        : [],
            }),
        });

        if (!response.ok) {
            const error = await response.json();
            toast.error(
                error.message || 'Buchung konnte nicht erstellt werden.',
            );

            return;
        }

        const result = await response.json();
        toast.success(result.message);

        selectedExtras.value = [];
        selectedMethods.value = {};
        selectedDate.value = undefined;
        bookingDate.value = '';
        bookingTime.value = '';
        pickupStreet.value = '';
        pickupPostalCode.value = '';
        pickupCity.value = '';
        vehicleInfo.value = '';
        notes.value = '';
    } catch (error) {
        console.error('Fehler beim Speichern der Buchung:', error);
        toast.error('Buchung konnte nicht gespeichert werden.');
    } finally {
        isLoading.value = false;
    }
};
</script>

<template>
    <Head title="Autoreinigung buchen" />

    <div class="min-h-screen bg-[#111111] text-white">
        <header
            class="sticky top-0 z-30 border-b border-white/10 bg-black/90 backdrop-blur"
        >
            <div
                class="mx-auto flex min-h-16 max-w-[1480px] flex-col gap-3 px-5 py-3 sm:px-8 lg:flex-row lg:items-center lg:justify-between lg:px-10 lg:py-0"
            >
                <Link href="/" class="flex items-center gap-3">
                    <span
                        class="flex h-10 w-10 items-center justify-center border border-[#f4a340] bg-[#f4a340] font-black text-black"
                    >
                        CS
                    </span>
                    <span class="text-lg font-black tracking-wide">CarSpa</span>
                </Link>

                <div
                    v-if="isAuthenticated"
                    class="flex items-center gap-2 overflow-x-auto pb-1 lg:pb-0"
                >
                    <nav class="flex items-center gap-1">
                        <Link
                            v-for="item in navigation"
                            :key="item.href"
                            :href="item.href"
                            class="h-9 border px-3 py-2 text-sm font-bold whitespace-nowrap transition"
                            :class="
                                isActive(item.href)
                                    ? 'border-[#f4a340] bg-[#241b12] text-[#f4a340]'
                                    : 'border-transparent text-zinc-300 hover:border-white/15 hover:text-white'
                            "
                        >
                            {{ item.label }}
                        </Link>
                    </nav>

                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        class="ml-2 inline-flex h-9 w-9 shrink-0 items-center justify-center border border-white/15 text-zinc-300 transition hover:border-[#f4a340] hover:text-[#f4a340]"
                        title="Abmelden"
                    >
                        <LogOut class="h-4 w-4" />
                    </Link>
                </div>
            </div>
        </header>

        <section
            class="relative min-h-[330px] overflow-hidden bg-cover bg-center"
            style="background-image: url('/images/car-cleaning-hero.jpg')"
        >
            <div
                class="absolute inset-0 bg-gradient-to-r from-black via-black/75 to-black/20"
            ></div>
            <div
                class="relative mx-auto flex min-h-[330px] max-w-[1480px] flex-col justify-end px-5 py-10 sm:px-8 lg:px-10"
            >
                <p
                    class="mb-3 inline-flex w-fit items-center gap-2 border border-white/25 bg-black/40 px-3 py-1 text-xs font-semibold tracking-[0.24em] text-[#f4a340] uppercase"
                >
                    <Sparkles class="h-4 w-4" />
                    Konfigurator
                </p>
                <h1
                    class="max-w-3xl text-4xl leading-tight font-black sm:text-6xl"
                >
                    Autoreinigung buchen
                </h1>
                <p
                    class="mt-4 max-w-2xl text-base leading-7 text-zinc-200 sm:text-lg"
                >
                    Wählen Sie ein fertiges Paket oder stellen Sie genau
                    zusammen, was und wie gereinigt werden soll.
                </p>
            </div>
        </section>

        <main
            class="mx-auto grid max-w-[1480px] gap-6 px-5 py-8 sm:px-8 lg:grid-cols-[minmax(0,1fr)_340px] lg:px-10"
        >
            <div class="space-y-6">
                <section
                    class="border border-white/10 bg-[#181818] p-4 shadow-2xl shadow-black/25 sm:p-5"
                >
                    <p
                        class="text-xs font-semibold tracking-[0.22em] text-[#f4a340] uppercase"
                    >
                        01 Auswahl
                    </p>
                    <h2 class="mt-1 text-2xl font-bold">
                        Wie möchten Sie buchen?
                    </h2>

                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                        <button
                            type="button"
                            class="border p-4 text-left transition"
                            :class="
                                bookingMode === 'package'
                                    ? 'border-[#f4a340] bg-[#241b12]'
                                    : 'border-white/10 bg-[#202020] hover:border-[#f4a340]/70'
                            "
                            @click="setBookingMode('package')"
                        >
                            <span class="block text-lg font-black"
                                >Reinigungspaket wählen</span
                            >
                            <span class="mt-1 block text-sm text-zinc-400"
                                >Ein fertiges Paket als Basis auswählen und
                                Extras dazu buchen.</span
                            >
                        </button>
                        <button
                            type="button"
                            class="border p-4 text-left transition"
                            :class="
                                bookingMode === 'individual'
                                    ? 'border-[#f4a340] bg-[#241b12]'
                                    : 'border-white/10 bg-[#202020] hover:border-[#f4a340]/70'
                            "
                            @click="setBookingMode('individual')"
                        >
                            <span class="block text-lg font-black"
                                >Individuell zusammenstellen</span
                            >
                            <span class="mt-1 block text-sm text-zinc-400"
                                >Bereiche wählen und pro Bereich die
                                Reinigungsart festlegen.</span
                            >
                        </button>
                    </div>
                </section>

                <section
                    v-if="bookingMode === 'package'"
                    class="border border-white/10 bg-[#181818] p-4 sm:p-5"
                >
                    <p
                        class="text-xs font-semibold tracking-[0.22em] text-[#f4a340] uppercase"
                    >
                        02 Paket
                    </p>
                    <h2 class="mt-1 text-2xl font-bold">
                        Basis für die Reinigung
                    </h2>

                    <div class="mt-5 grid gap-3 md:grid-cols-2 2xl:grid-cols-4">
                        <button
                            v-for="pkg in packages"
                            :key="pkg.id"
                            type="button"
                            :disabled="!pkg.is_available"
                            class="flex min-h-[390px] min-w-0 flex-col overflow-hidden border p-4 text-left transition"
                            :class="[
                                selectedPackageId === pkg.id
                                    ? 'border-[#f4a340] bg-[#241b12] shadow-lg shadow-[#f4a340]/10'
                                    : 'border-white/10 bg-[#202020] hover:border-[#f4a340]/70',
                                !pkg.is_available &&
                                    'cursor-not-allowed opacity-45',
                            ]"
                            @click="selectPackage(pkg)"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="text-xl font-black">
                                        {{ pkg.name }}
                                    </h3>
                                    <p class="mt-1 text-sm text-zinc-400">
                                        {{ pkg.duration_minutes }} Min.
                                    </p>
                                </div>
                                <span
                                    class="text-lg font-black whitespace-nowrap text-[#f4a340]"
                                    >{{ formatPrice(pkg.price) }}</span
                                >
                            </div>

                            <ul class="mt-5 space-y-2 text-sm text-zinc-200">
                                <li
                                    v-for="feature in packageFeatures(pkg)"
                                    :key="feature"
                                    class="flex gap-2"
                                >
                                    <Check
                                        class="mt-0.5 h-4 w-4 shrink-0 text-[#f4a340]"
                                    />
                                    <span class="min-w-0 break-words">{{
                                        feature
                                    }}</span>
                                </li>
                            </ul>

                            <div class="mt-auto pt-5">
                                <span
                                    class="inline-flex h-9 items-center border px-3 text-sm font-bold"
                                    :class="
                                        selectedPackageId === pkg.id
                                            ? 'border-[#f4a340] bg-[#f4a340] text-black'
                                            : 'border-white/15 text-zinc-300'
                                    "
                                >
                                    {{
                                        selectedPackageId === pkg.id
                                            ? 'Ausgewählt'
                                            : 'Auswählen'
                                    }}
                                </span>
                            </div>
                        </button>
                    </div>
                </section>

                <section
                    v-if="bookingMode === 'package'"
                    class="border border-white/10 bg-[#181818] p-4 sm:p-5"
                >
                    <p
                        class="text-xs font-semibold tracking-[0.22em] text-[#f4a340] uppercase"
                    >
                        03 Zusatzoptionen
                    </p>
                    <h2 class="mt-1 text-2xl font-bold">Optional dazubuchen</h2>

                    <div class="mt-5 grid gap-3 md:grid-cols-2">
                        <button
                            v-for="extra in addOnExtras"
                            :key="extra.id"
                            type="button"
                            :disabled="!extra.is_available"
                            class="flex items-center justify-between gap-4 border p-4 text-left transition"
                            :class="[
                                isExtraSelected(extra.id)
                                    ? 'border-[#f4a340] bg-[#241b12]'
                                    : 'border-white/10 bg-[#202020] hover:border-[#f4a340]/70',
                                !extra.is_available &&
                                    'cursor-not-allowed border-red-500/30 bg-red-950/20 opacity-65 hover:border-red-500/30',
                            ]"
                            @click="toggleExtra(extra)"
                        >
                            <span class="flex items-center gap-3">
                                <span
                                    class="flex h-9 w-9 shrink-0 items-center justify-center border"
                                    :class="
                                        extra.is_available
                                            ? 'border-[#f4a340]/60 text-[#f4a340]'
                                            : 'border-red-400/50 text-red-300'
                                    "
                                >
                                    <Check
                                        v-if="isExtraSelected(extra.id)"
                                        class="h-4 w-4"
                                    />
                                    <X
                                        v-else-if="!extra.is_available"
                                        class="h-4 w-4"
                                    />
                                    <Sparkles v-else class="h-4 w-4" />
                                </span>
                                <span>
                                    <span class="block font-semibold">{{
                                        extra.name
                                    }}</span>
                                    <span class="text-sm text-zinc-400">{{
                                        extra.is_available
                                            ? 'Optional ergänzen'
                                            : 'Aktuell nicht verfügbar'
                                    }}</span>
                                </span>
                            </span>
                            <span
                                class="font-black"
                                :class="
                                    extra.is_available
                                        ? 'text-[#f4a340]'
                                        : 'text-red-300'
                                "
                            >
                                {{
                                    extra.is_available
                                        ? formatPrice(extra.price)
                                        : '-'
                                }}
                            </span>
                        </button>
                    </div>
                    <p
                        v-if="!addOnExtras.length"
                        class="mt-5 border border-dashed border-white/15 p-4 text-sm text-zinc-400"
                    >
                        Für dieses Paket sind aktuell keine weiteren Leistungen
                        verfügbar.
                    </p>
                </section>

                <section
                    v-if="bookingMode === 'individual'"
                    class="border border-white/10 bg-[#181818] p-4 sm:p-5"
                >
                    <p
                        class="text-xs font-semibold tracking-[0.22em] text-[#f4a340] uppercase"
                    >
                        02 Individuell
                    </p>
                    <h2 class="mt-1 text-2xl font-bold">
                        Was soll wie gereinigt werden?
                    </h2>

                    <div class="mt-5 space-y-4">
                        <div
                            v-for="option in individualOptions"
                            :key="option.key"
                            class="border border-white/10 bg-[#202020] p-4"
                        >
                            <div
                                class="mb-3 flex items-center justify-between gap-3"
                            >
                                <h3 class="font-black">{{ option.label }}</h3>
                                <span
                                    v-if="selectedMethods[option.key]?.length"
                                    class="text-sm font-bold text-[#f4a340]"
                                    >Ausgewählt</span
                                >
                            </div>
                            <p
                                v-if="option.key === 'aussen'"
                                class="mb-3 text-sm text-zinc-400"
                            >
                                Grundreinigung oder Hand-Wäsche auswählen,
                                Insektenentfernung kann zusätzlich gebucht
                                werden.
                            </p>
                            <div
                                class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3"
                            >
                                <button
                                    v-for="method in option.methods"
                                    :key="method.key"
                                    type="button"
                                    class="border p-3 text-left transition"
                                    :class="
                                        isMethodSelected(option, method)
                                            ? 'border-[#f4a340] bg-[#241b12]'
                                            : 'border-white/10 bg-[#181818] hover:border-[#f4a340]/70'
                                    "
                                    @click="toggleMethod(option, method)"
                                >
                                    <span class="block font-semibold">{{
                                        method.label
                                    }}</span>
                                    <span
                                        class="mt-1 block text-sm font-black text-[#f4a340]"
                                        >{{ formatPrice(method.price) }} ·
                                        {{
                                            formatDuration(
                                                method.duration_minutes,
                                            )
                                        }}</span
                                    >
                                </button>
                            </div>
                        </div>
                    </div>
                </section>

                <section
                    class="grid gap-4 border border-white/10 bg-[#181818] p-4 sm:p-5 lg:grid-cols-2"
                >
                    <div>
                        <p
                            class="text-xs font-semibold tracking-[0.22em] text-[#f4a340] uppercase"
                        >
                            Termin
                        </p>
                        <h2 class="mt-1 text-2xl font-bold">
                            Datum und Uhrzeit
                        </h2>
                        <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <label
                                class="min-w-0 space-y-2 text-sm font-semibold"
                            >
                                <span class="flex items-center gap-2"
                                    ><CalendarDays
                                        class="h-4 w-4 text-[#f4a340]"
                                    />Datum</span
                                >
                                <Popover v-slot="{ close }">
                                    <PopoverTrigger as-child>
                                        <Button
                                            variant="outline"
                                            :class="
                                                cn(
                                                    'h-12 w-full min-w-0 justify-start rounded-none border-white/10 bg-[#202020] px-3 text-left font-semibold text-white hover:border-[#f4a340] hover:bg-[#202020] hover:text-white',
                                                    !selectedDate &&
                                                        'text-zinc-500 hover:text-zinc-500',
                                                )
                                            "
                                        >
                                            <CalendarDays
                                                class="h-4 w-4 text-[#f4a340]"
                                            />
                                            <span class="truncate">{{
                                                formattedBookingDate
                                            }}</span>
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent
                                        class="w-auto border-white/10 bg-[#181818] p-0"
                                        align="start"
                                    >
                                        <Calendar
                                            :model-value="selectedDate"
                                            :default-placeholder="
                                                defaultDatePlaceholder
                                            "
                                            :min-value="defaultDatePlaceholder"
                                            :is-date-unavailable="
                                                isDateUnavailable
                                            "
                                            layout="month-and-year"
                                            initial-focus
                                            @update:model-value="
                                                (value) => {
                                                    setBookingDate(value);
                                                    if (value) close();
                                                }
                                            "
                                        />
                                    </PopoverContent>
                                </Popover>
                            </label>
                            <label
                                class="min-w-0 space-y-2 text-sm font-semibold"
                            >
                                <span class="flex items-center gap-2"
                                    ><Clock
                                        class="h-4 w-4 text-[#f4a340]"
                                    />Uhrzeit</span
                                >
                                <Select v-model="bookingTime">
                                    <SelectTrigger
                                        :disabled="
                                            !selectedDate || !timeOptions.length
                                        "
                                        class="h-12 w-full min-w-0 rounded-none border-white/10 bg-[#202020] px-3 text-white shadow-none focus:ring-[#f4a340]/30"
                                    >
                                        <Clock class="h-4 w-4 text-[#f4a340]" />
                                        <SelectValue
                                            placeholder="Uhrzeit auswählen"
                                        />
                                    </SelectTrigger>
                                    <SelectContent
                                        class="max-h-72 border-white/10 bg-[#181818] text-white"
                                    >
                                        <SelectItem
                                            v-for="time in timeOptions"
                                            :key="time"
                                            :value="time"
                                            class="focus:bg-[#241b12] focus:text-white"
                                        >
                                            {{ time }} Uhr
                                        </SelectItem>
                                        <div
                                            v-if="!timeOptions.length"
                                            class="px-3 py-2 text-sm text-zinc-500"
                                        >
                                            Keine Zeiten verfügbar
                                        </div>
                                    </SelectContent>
                                </Select>
                            </label>
                        </div>
                        <div class="mt-3 grid gap-3">
                            <label class="space-y-2 text-sm font-semibold">
                                <span>Strasse und Hausnummer</span>
                                <input
                                    v-model="pickupStreet"
                                    required
                                    placeholder="z.B. Bahnhofstrasse 10"
                                    class="h-12 w-full border border-white/10 bg-[#202020] px-3 text-white outline-none placeholder:text-zinc-500 focus:border-[#f4a340]"
                                />
                            </label>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <label class="space-y-2 text-sm font-semibold">
                                    <span>PLZ</span>
                                    <input
                                        v-model="pickupPostalCode"
                                        required
                                        inputmode="numeric"
                                        placeholder="8000"
                                        class="h-12 w-full border border-white/10 bg-[#202020] px-3 text-white outline-none placeholder:text-zinc-500 focus:border-[#f4a340]"
                                    />
                                </label>
                                <label class="space-y-2 text-sm font-semibold">
                                    <span>Ort</span>
                                    <input
                                        v-model="pickupCity"
                                        required
                                        placeholder="Zürich"
                                        class="h-12 w-full border border-white/10 bg-[#202020] px-3 text-white outline-none placeholder:text-zinc-500 focus:border-[#f4a340]"
                                    />
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-3">
                        <label class="space-y-2 text-sm font-semibold">
                            <span>Fahrzeugangaben</span>
                            <textarea
                                v-model="vehicleInfo"
                                rows="3"
                                placeholder="Modell, Farbe, Besonderheiten"
                                class="w-full resize-none border border-white/10 bg-[#202020] px-3 py-3 text-white outline-none placeholder:text-zinc-500 focus:border-[#f4a340]"
                            ></textarea>
                        </label>
                        <label class="space-y-2 text-sm font-semibold">
                            <span>Besondere Wünsche</span>
                            <textarea
                                v-model="notes"
                                rows="3"
                                placeholder="Hinweise zur Reinigung"
                                class="w-full resize-none border border-white/10 bg-[#202020] px-3 py-3 text-white outline-none placeholder:text-zinc-500 focus:border-[#f4a340]"
                            ></textarea>
                        </label>
                    </div>
                </section>
            </div>

            <aside
                class="h-fit border border-[#f4a340]/40 bg-[#17110c] p-5 lg:sticky lg:top-20"
            >
                <p
                    class="text-xs font-semibold tracking-[0.22em] text-[#f4a340] uppercase"
                >
                    Ihre Auswahl
                </p>
                <h2 class="mt-1 text-2xl font-black">Zusammenfassung</h2>

                <div class="mt-5 space-y-3 text-sm">
                    <div
                        class="flex justify-between gap-4 border-b border-white/10 pb-3"
                    >
                        <span>{{
                            bookingMode === 'package'
                                ? (selectedPackage?.name ??
                                  'Kein Paket gewählt')
                                : 'Individuelle Reinigung'
                        }}</span>
                        <span class="font-bold text-[#f4a340]">
                            {{
                                bookingMode === 'package' && selectedPackage
                                    ? formatPrice(selectedPackage.price)
                                    : formatPrice(0)
                            }}
                        </span>
                    </div>
                    <template v-if="bookingMode === 'package'">
                        <div
                            v-for="extra in selectedExtraItems"
                            :key="extra.id"
                            class="flex justify-between gap-4"
                        >
                            <span>{{ extra.name }}</span>
                            <span class="font-bold text-[#f4a340]">{{
                                formatPrice(extra.price)
                            }}</span>
                        </div>
                    </template>
                    <template v-else>
                        <div
                            v-for="item in customConfiguration"
                            :key="`${item.area_key}-${item.method_key}`"
                            class="flex justify-between gap-4"
                        >
                            <span
                                >{{ item.area_label }}:
                                {{ item.method_label }}</span
                            >
                            <span class="font-bold text-[#f4a340]">{{
                                formatPrice(item.price)
                            }}</span>
                        </div>
                    </template>
                    <p
                        v-if="
                            bookingMode === 'package' &&
                            !selectedExtraItems.length
                        "
                        class="text-zinc-400"
                    >
                        Noch keine Zusatzoptionen ausgewählt.
                    </p>
                    <p
                        v-if="
                            bookingMode === 'individual' &&
                            !customConfiguration.length
                        "
                        class="text-zinc-400"
                    >
                        Noch keine Bereiche ausgewählt.
                    </p>
                </div>

                <div class="mt-6 border-t border-white/10 pt-5">
                    <div
                        class="mb-4 flex items-center justify-between gap-4 text-sm"
                    >
                        <span class="text-zinc-300">Zeitaufwand</span>
                        <span class="font-black text-white">{{
                            formatDuration(totalDuration)
                        }}</span>
                    </div>
                    <div class="flex items-end justify-between gap-4">
                        <span class="text-zinc-300">Gesamt</span>
                        <span class="text-4xl font-black text-[#f4a340]">{{
                            formatPrice(totalPrice)
                        }}</span>
                    </div>
                </div>

                <Button
                    class="mt-6 h-12 w-full bg-[#f4a340] font-black text-black hover:bg-[#ffb65c]"
                    :disabled="
                        !bookingPackageId ||
                        !bookingDate ||
                        !bookingTime ||
                        !pickupStreet.trim() ||
                        !pickupPostalCode.trim() ||
                        !pickupCity.trim() ||
                        (bookingMode === 'individual' &&
                            (!customConfiguration.length ||
                                hasInvalidIndividualConfiguration)) ||
                        isLoading
                    "
                    @click="submitBooking"
                >
                    {{
                        isLoading
                            ? 'Wird verarbeitet...'
                            : 'Buchung abschließen'
                    }}
                </Button>
            </aside>
        </main>

        <PublicFooter />
        <Toaster />
    </div>
</template>
