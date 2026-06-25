<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Plus, Save, Trash2 } from 'lucide-vue-next';
import Button from '@/components/ui/button/Button.vue';
import { DatePicker } from '@/components/ui/date-picker';
import { Input } from '@/components/ui/input';

interface WeeklyHour {
    weekday: number;
    is_working: boolean;
    starts_at: string | null;
    ends_at: string | null;
}

interface DayException {
    starts_on: string;
    ends_on: string;
    note: string | null;
}

const props = defineProps<{
    weeklyHours: WeeklyHour[];
    exceptions: DayException[];
}>();

const weekdayLabels: Record<number, string> = {
    1: 'Montag',
    2: 'Dienstag',
    3: 'Mittwoch',
    4: 'Donnerstag',
    5: 'Freitag',
    6: 'Samstag',
    7: 'Sonntag',
};

const form = useForm({
    weekly_hours: props.weeklyHours.map((day) => ({
        ...day,
        starts_at: day.starts_at ?? '08:00',
        ends_at: day.ends_at ?? '18:00',
    })),
    exceptions: props.exceptions.map((exception) => ({
        ...exception,
        note: exception.note ?? '',
    })),
});

const addException = () => {
    form.exceptions.push({
        starts_on: new Date().toISOString().split('T')[0],
        ends_on: new Date().toISOString().split('T')[0],
        note: '',
    });
};

const removeException = (index: number) => {
    form.exceptions.splice(index, 1);
};

const submit = () => {
    form.transform((data) => ({
        weekly_hours: data.weekly_hours.map((day) => ({
            ...day,
            starts_at: day.is_working ? day.starts_at : null,
            ends_at: day.is_working ? day.ends_at : null,
        })),
        exceptions: data.exceptions
            .filter((exception) => exception.starts_on && exception.ends_on)
            .map((exception) => ({ ...exception })),
    })).put('/admin/working-hours', {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Arbeitszeiten" />

    <div
        class="min-h-screen bg-[#111111] px-5 py-5 text-white sm:px-8 lg:px-10"
    >
        <div class="mx-auto max-w-[1480px]">
            <div
                class="mb-4 flex flex-col justify-between gap-3 md:flex-row md:items-end"
            >
                <div>
                    <p
                        class="text-xs font-semibold tracking-[0.22em] text-[#f4a340] uppercase"
                    >
                        Admin
                    </p>
                    <h1 class="mt-1 text-2xl font-black">Arbeitszeiten</h1>
                    <p class="mt-1 max-w-2xl text-sm leading-5 text-zinc-400">
                        Definieren Sie hier, an welchen Tagen und zu welchen
                        Zeiten Buchungen möglich sind. Sondertage überschreiben
                        die wöchentlichen Zeiten.
                    </p>
                </div>
                <Button
                    class="h-9 rounded-none bg-[#f4a340] px-4 text-sm font-black text-black hover:bg-[#ffb65c]"
                    :disabled="form.processing"
                    @click="submit"
                >
                    <Save class="h-4 w-4" />
                    {{ form.processing ? 'Speichert...' : 'Speichern' }}
                </Button>
            </div>

            <div
                v-if="form.hasErrors"
                class="mb-4 border border-red-500/30 bg-red-950/30 p-3 text-sm text-red-100"
            >
                Bitte prüfen Sie die Arbeitszeiten. Arbeitstage benötigen eine
                gültige Start- und Endzeit.
            </div>

            <div
                class="grid items-start gap-4 xl:grid-cols-[560px_minmax(0,1fr)]"
            >
                <section class="border border-white/10 bg-[#181818] p-3">
                    <div class="mb-3">
                        <p
                            class="text-xs font-semibold tracking-[0.22em] text-[#f4a340] uppercase"
                        >
                            Woche
                        </p>
                        <h2 class="mt-1 text-xl font-black">
                            Regelmäßige Arbeitszeiten
                        </h2>
                    </div>

                    <div class="grid gap-2">
                        <div
                            v-for="day in form.weekly_hours"
                            :key="day.weekday"
                            class="grid gap-2 border border-white/10 bg-[#202020] p-2.5 sm:grid-cols-[180px_150px_150px] sm:items-end"
                        >
                            <label
                                class="flex h-9 items-center gap-2 text-sm font-bold"
                            >
                                <input
                                    v-model="day.is_working"
                                    type="checkbox"
                                    class="h-4 w-4 accent-[#f4a340]"
                                />
                                {{ weekdayLabels[day.weekday] }}
                            </label>
                            <label
                                class="space-y-1 text-xs font-semibold text-zinc-300"
                            >
                                <span>Start</span>
                                <Input
                                    v-model="day.starts_at"
                                    type="time"
                                    :disabled="!day.is_working"
                                    class="h-9 rounded-none border-white/10 bg-[#181818] text-sm text-white disabled:opacity-40"
                                />
                            </label>
                            <label
                                class="space-y-1 text-xs font-semibold text-zinc-300"
                            >
                                <span>Ende</span>
                                <Input
                                    v-model="day.ends_at"
                                    type="time"
                                    :disabled="!day.is_working"
                                    class="h-9 rounded-none border-white/10 bg-[#181818] text-sm text-white disabled:opacity-40"
                                />
                            </label>
                        </div>
                    </div>
                </section>

                <section class="border border-white/10 bg-[#181818] p-3">
                    <div
                        class="mb-3 flex flex-col justify-between gap-2 sm:flex-row sm:items-end"
                    >
                        <div>
                            <p
                                class="text-xs font-semibold tracking-[0.22em] text-[#f4a340] uppercase"
                            >
                                Ausnahmen
                            </p>
                            <h2 class="mt-1 text-xl font-black">Sondertage</h2>
                        </div>
                        <Button
                            variant="outline"
                            class="h-9 rounded-none border-white/20 bg-transparent px-3 text-sm text-white hover:border-[#f4a340] hover:bg-[#241b12] hover:text-white"
                            @click="addException"
                        >
                            <Plus class="h-4 w-4" />
                            Sondertag hinzufügen
                        </Button>
                    </div>

                    <div
                        v-if="!form.exceptions.length"
                        class="border border-dashed border-white/15 p-3 text-sm text-zinc-400"
                    >
                        Noch keine Sondertage erfasst.
                    </div>

                    <div class="grid gap-2">
                        <div
                            v-for="(exception, index) in form.exceptions"
                            :key="`${exception.starts_on}-${index}`"
                            class="grid gap-2 border border-white/10 bg-[#202020] p-2.5 md:grid-cols-[150px_150px_minmax(160px,1fr)_auto] md:items-end"
                        >
                            <label
                                class="space-y-1 text-xs font-semibold text-zinc-300"
                            >
                                <span>Von</span>
                                <DatePicker v-model="exception.starts_on" />
                            </label>
                            <label
                                class="space-y-1 text-xs font-semibold text-zinc-300"
                            >
                                <span>Bis</span>
                                <DatePicker v-model="exception.ends_on" />
                            </label>
                            <label
                                class="space-y-1 text-xs font-semibold text-zinc-300"
                            >
                                <span>Notiz</span>
                                <Input
                                    v-model="exception.note"
                                    placeholder="z.B. Feiertag"
                                    class="h-9 rounded-none border-white/10 bg-[#181818] text-sm text-white"
                                />
                            </label>
                            <Button
                                variant="outline"
                                size="icon"
                                class="h-9 w-9 rounded-none border-red-500/30 bg-red-950/20 text-red-200 hover:bg-red-950/40 hover:text-red-100"
                                @click="removeException(index)"
                            >
                                <Trash2 class="h-4 w-4" />
                            </Button>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</template>
