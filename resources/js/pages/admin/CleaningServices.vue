<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Plus, Save, Trash2 } from 'lucide-vue-next';
import Button from '@/components/ui/button/Button.vue';
import { Input } from '@/components/ui/input';

interface CleaningService {
    id: number | null;
    name: string;
    description: string | null;
    price: number;
    duration_minutes: number;
    is_available: boolean;
}

const props = defineProps<{
    services: CleaningService[];
}>();

const form = useForm({
    services: props.services.map((service) => ({
        ...service,
        description: service.description ?? '',
    })),
    deleted_service_ids: [] as number[],
});

const addService = () => {
    form.services.unshift({
        id: null,
        name: '',
        description: '',
        price: 0,
        duration_minutes: 30,
        is_available: false,
    });
};

const removeService = (index: number) => {
    const [service] = form.services.splice(index, 1);

    if (service?.id) {
        form.deleted_service_ids.push(service.id);
    }
};

const submit = () => {
    form.put('/admin/services', {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Dienstleistungen" />

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
                    <h1 class="mt-1 text-2xl font-black">Dienstleistungen</h1>
                    <p class="mt-1 max-w-2xl text-sm leading-5 text-zinc-400">
                        Freigegebene Leistungen erscheinen als Zusatzoption,
                        sobald sie im gewählten Paket noch nicht enthalten sind.
                        Deaktivierte Leistungen bleiben nur hier sichtbar.
                    </p>
                </div>
                <div class="flex gap-2">
                    <Button
                        variant="outline"
                        class="h-9 rounded-none border-white/20 bg-transparent px-3 text-sm text-white hover:border-[#f4a340] hover:bg-[#241b12] hover:text-white"
                        @click="addService"
                    >
                        <Plus class="h-4 w-4" />
                        Leistung hinzufügen
                    </Button>
                    <Button
                        class="h-9 rounded-none bg-[#f4a340] px-4 text-sm font-black text-black hover:bg-[#ffb65c]"
                        :disabled="form.processing"
                        @click="submit"
                    >
                        <Save class="h-4 w-4" />
                        {{ form.processing ? 'Speichert...' : 'Speichern' }}
                    </Button>
                </div>
            </div>

            <div
                v-if="form.hasErrors"
                class="mb-4 border border-red-500/30 bg-red-950/30 p-3 text-sm text-red-100"
            >
                {{
                    form.errors.services ??
                    'Bitte prüfen Sie die Angaben. Jede Dienstleistung benötigt einen Namen und einen gültigen Preis.'
                }}
            </div>

            <section class="border border-white/10 bg-[#181818] p-3">
                <div class="mb-3">
                    <p
                        class="text-xs font-semibold tracking-[0.22em] text-[#f4a340] uppercase"
                    >
                        Katalog
                    </p>
                    <h2 class="mt-1 text-xl font-black">
                        Zusätzlich buchbare Leistungen
                    </h2>
                </div>

                <div class="grid gap-2 xl:grid-cols-2">
                    <div
                        v-for="(service, index) in form.services"
                        :key="service.id ?? `new-${index}`"
                        class="grid gap-2 border border-white/10 bg-[#202020] p-2.5 lg:grid-cols-[minmax(180px,1fr)_90px_100px_100px_auto] lg:items-end"
                    >
                        <label
                            class="space-y-1 text-xs font-semibold text-zinc-300"
                        >
                            <span>Name</span>
                            <Input
                                v-model="service.name"
                                placeholder="z.B. Fussmattenaufbereitung"
                                class="h-9 rounded-none border-white/10 bg-[#181818] text-sm text-white"
                            />
                        </label>
                        <label
                            class="space-y-1 text-xs font-semibold text-zinc-300"
                        >
                            <span>Dauer in Min.</span>
                            <Input
                                v-model="service.duration_minutes"
                                type="number"
                                min="1"
                                step="5"
                                class="h-9 rounded-none border-white/10 bg-[#181818] text-sm text-white"
                            />
                        </label>
                        <label
                            class="space-y-1 text-xs font-semibold text-zinc-300"
                        >
                            <span>Preis in CHF</span>
                            <Input
                                v-model="service.price"
                                type="number"
                                min="0"
                                step="1"
                                class="h-9 rounded-none border-white/10 bg-[#181818] text-sm text-white"
                            />
                        </label>
                        <label
                            class="flex h-9 items-center gap-2 text-sm font-bold"
                        >
                            <input
                                v-model="service.is_available"
                                type="checkbox"
                                class="h-4 w-4 accent-[#f4a340]"
                            />
                            Buchbar
                        </label>
                        <Button
                            variant="outline"
                            class="h-9 w-9 rounded-none border-red-500/40 bg-transparent p-0 text-red-200 hover:border-red-400 hover:bg-red-950/40 hover:text-red-100"
                            title="Dienstleistung löschen"
                            @click="removeService(index)"
                        >
                            <Trash2 class="h-4 w-4" />
                        </Button>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>
