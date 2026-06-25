<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ChevronDown, Plus, Save, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import Button from '@/components/ui/button/Button.vue';
import { Input } from '@/components/ui/input';

interface CleaningService {
    id: number;
    name: string;
    is_available: boolean;
}

interface CleaningPackage {
    id: number | null;
    name: string;
    description: string | null;
    price: number;
    duration_minutes: number;
    is_available: boolean;
    service_ids: number[];
    client_key?: string;
}

const props = defineProps<{
    packages: CleaningPackage[];
    services: CleaningService[];
}>();

const form = useForm({
    packages: props.packages.map((pkg) => ({
        ...pkg,
        description: pkg.description ?? '',
        service_ids: [...pkg.service_ids],
        client_key: `package-${pkg.id}`,
    })),
    deleted_package_ids: [] as number[],
});

const openPackageKeys = ref<string[]>([]);

const packageKey = (pkg: CleaningPackage, index: number) =>
    pkg.client_key ?? `new-${index}`;

const isPackageOpen = (pkg: CleaningPackage, index: number) =>
    openPackageKeys.value.includes(packageKey(pkg, index));

const togglePackage = (pkg: CleaningPackage, index: number) => {
    const key = packageKey(pkg, index);

    openPackageKeys.value = isPackageOpen(pkg, index)
        ? openPackageKeys.value.filter((openKey) => openKey !== key)
        : [...openPackageKeys.value, key];
};

const addPackage = () => {
    const clientKey = `new-${Date.now()}`;

    form.packages.unshift({
        id: null,
        name: '',
        description: '',
        price: 0,
        duration_minutes: 60,
        is_available: false,
        service_ids: [],
        client_key: clientKey,
    });
    openPackageKeys.value = [clientKey, ...openPackageKeys.value];
};

const removePackage = (index: number) => {
    const [pkg] = form.packages.splice(index, 1);

    if (pkg?.id) {
        form.deleted_package_ids.push(pkg.id);
    }

    if (pkg) {
        openPackageKeys.value = openPackageKeys.value.filter(
            (key) => key !== packageKey(pkg, index),
        );
    }
};

const submit = () => {
    form.put('/admin/packages', { preserveScroll: true });
};
</script>

<template>
    <Head title="Pakete" />

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
                    <h1 class="mt-1 text-2xl font-black">Reinigungspakete</h1>
                    <p class="mt-1 max-w-2xl text-sm leading-5 text-zinc-400">
                        Pakete verwalten, enthaltene Dienstleistungen zuweisen
                        und Buchbarkeit steuern.
                    </p>
                </div>
                <div class="flex gap-2">
                    <Button
                        variant="outline"
                        class="h-9 rounded-none border-white/20 bg-transparent px-3 text-sm text-white hover:border-[#f4a340] hover:bg-[#241b12] hover:text-white"
                        @click="addPackage"
                    >
                        <Plus class="h-4 w-4" />
                        Paket hinzufügen
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
                    form.errors.packages ??
                    'Bitte prüfen Sie die Paketangaben. Jedes Paket benötigt einen Namen, Preis und eine gültige Dauer.'
                }}
            </div>

            <section class="border border-white/10 bg-[#181818] p-3">
                <div class="mb-3">
                    <p
                        class="text-xs font-semibold tracking-[0.22em] text-[#f4a340] uppercase"
                    >
                        Katalog
                    </p>
                    <h2 class="mt-1 text-xl font-black">Buchbare Pakete</h2>
                </div>

                <div class="grid gap-2">
                    <div
                        v-for="(pkg, index) in form.packages"
                        :key="packageKey(pkg, index)"
                        class="border border-white/10 bg-[#202020]"
                    >
                        <div
                            class="grid gap-2 p-2.5 lg:grid-cols-[minmax(180px,1fr)_100px_120px_110px_auto_auto] lg:items-end"
                        >
                            <button
                                type="button"
                                class="flex h-9 min-w-0 items-center gap-2 text-left"
                                @click="togglePackage(pkg, index)"
                            >
                                <ChevronDown
                                    class="h-4 w-4 shrink-0 text-[#f4a340] transition-transform"
                                    :class="
                                        isPackageOpen(pkg, index)
                                            ? 'rotate-180'
                                            : ''
                                    "
                                />
                                <span
                                    class="truncate text-sm font-black text-white"
                                >
                                    {{ pkg.name || 'Neues Paket' }}
                                </span>
                                <span
                                    class="shrink-0 text-xs font-semibold text-zinc-500"
                                >
                                    {{ pkg.service_ids.length }} Leistungen
                                </span>
                            </button>
                            <label
                                class="space-y-1 text-xs font-semibold text-zinc-300"
                            >
                                <span>Preis in CHF</span>
                                <Input
                                    v-model="pkg.price"
                                    type="number"
                                    min="0"
                                    step="1"
                                    class="h-9 rounded-none border-white/10 bg-[#181818] text-sm text-white"
                                />
                            </label>
                            <label
                                class="space-y-1 text-xs font-semibold text-zinc-300"
                            >
                                <span>Dauer in Min.</span>
                                <Input
                                    v-model="pkg.duration_minutes"
                                    type="number"
                                    min="1"
                                    step="5"
                                    class="h-9 rounded-none border-white/10 bg-[#181818] text-sm text-white"
                                />
                            </label>
                            <label
                                class="flex h-9 items-center gap-2 text-sm font-bold"
                            >
                                <input
                                    v-model="pkg.is_available"
                                    type="checkbox"
                                    class="h-4 w-4 accent-[#f4a340]"
                                />
                                Buchbar
                            </label>
                            <Button
                                variant="outline"
                                class="h-9 rounded-none border-white/20 bg-transparent px-3 text-sm text-white hover:border-[#f4a340] hover:bg-[#241b12] hover:text-white"
                                @click="togglePackage(pkg, index)"
                            >
                                Details
                            </Button>
                            <Button
                                variant="outline"
                                class="h-9 w-9 rounded-none border-red-500/40 bg-transparent p-0 text-red-200 hover:border-red-400 hover:bg-red-950/40 hover:text-red-100"
                                title="Paket löschen"
                                @click="removePackage(index)"
                            >
                                <Trash2 class="h-4 w-4" />
                            </Button>
                        </div>

                        <div
                            v-if="isPackageOpen(pkg, index)"
                            class="border-t border-white/10 p-2.5 pt-3"
                        >
                            <label
                                class="block max-w-xl space-y-1 text-xs font-semibold text-zinc-300"
                            >
                                <span>Name</span>
                                <Input
                                    v-model="pkg.name"
                                    placeholder="z.B. Premium Plus"
                                    class="h-9 rounded-none border-white/10 bg-[#181818] text-sm text-white"
                                />
                            </label>

                            <div class="mt-3">
                                <p
                                    class="mb-2 text-xs font-semibold text-zinc-300"
                                >
                                    Enthaltene Dienstleistungen
                                </p>
                                <div
                                    class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4"
                                >
                                    <label
                                        v-for="service in services"
                                        :key="service.id"
                                        class="flex min-h-9 items-center gap-2 border border-white/10 bg-[#181818] px-2 py-1.5 text-sm font-semibold text-zinc-200"
                                        :class="
                                            service.is_available
                                                ? ''
                                                : 'text-zinc-500'
                                        "
                                    >
                                        <input
                                            v-model="pkg.service_ids"
                                            type="checkbox"
                                            :value="service.id"
                                            class="h-4 w-4 shrink-0 accent-[#f4a340]"
                                        />
                                        <span>{{ service.name }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>
