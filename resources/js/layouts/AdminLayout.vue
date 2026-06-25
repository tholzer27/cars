<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { LogOut } from 'lucide-vue-next';
import { computed } from 'vue';
import { Toaster } from '@/components/ui/sonner';

const page = usePage();
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
</script>

<template>
    <div class="min-h-screen bg-[#111111] text-white">
        <header
            class="sticky top-0 z-40 border-b border-white/10 bg-black/95 backdrop-blur"
        >
            <div
                class="mx-auto flex min-h-16 max-w-[1480px] flex-col gap-3 px-5 py-3 sm:px-8 lg:flex-row lg:items-center lg:justify-between lg:px-10 lg:py-0"
            >
                <div class="flex items-center justify-between gap-4">
                    <Link href="/" class="flex items-center gap-3">
                        <span
                            class="flex h-10 w-10 items-center justify-center border border-[#f4a340] bg-[#f4a340] font-black text-black"
                        >
                            CS
                        </span>
                        <span class="text-lg font-black tracking-wide"
                            >CarSpa</span
                        >
                    </Link>
                    <span
                        class="border border-white/15 px-2 py-1 text-xs font-bold tracking-[0.18em] text-zinc-400 uppercase lg:hidden"
                    >
                        Admin
                    </span>
                </div>

                <div
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

        <slot />
        <Toaster />
    </div>
</template>
