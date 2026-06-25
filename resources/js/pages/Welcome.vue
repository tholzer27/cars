<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Check, LogOut, Sparkles } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import PublicFooter from '@/components/PublicFooter.vue';
import { booking, login } from '@/routes';

const page = usePage();
const isAuthenticated = computed(() => Boolean(page.props.auth?.user));
const canAccessAuth = computed(() => Boolean(page.props.auth?.canAccessAuth));
const currentPath = computed(() => page.url.split('?')[0]);
const bookingHref = computed(() => booking());
const navigation = [
    { label: 'Startseite', href: '/' },
    { label: 'Buchungsseite', href: '/booking' },
    { label: 'Pakete', href: '/admin/packages' },
    { label: 'Dienstleistungen', href: '/admin/services' },
    { label: 'Arbeitszeiten', href: '/admin/working-hours' },
    { label: 'Profil', href: '/settings/profile' },
];

const isActive = (href: string) => currentPath.value === href;

const packages = [
    {
        name: 'Basis-Paket',
        price: 'CHF 80',
        features: [
            'Aussenreinigung inkl. Aussenscheiben',
            'Aussaugen',
            'Kofferraum aussaugen',
            'Cockpit abwischen',
        ],
    },
    {
        name: 'Comfort',
        price: 'CHF 200',
        features: [
            'Innen und aussen',
            'Hand-Aussenwäsche',
            'Sitzreinigung',
            'Kunststoffpflege',
        ],
    },
    {
        name: 'Individuell',
        price: 'nach Auswahl',
        features: [
            'Einzelleistungen kombinieren',
            'Transparenter Gesamtpreis',
            'Nur buchen, was gebraucht wird',
        ],
    },
];
</script>

<template>
    <Head title="Autoreinigung buchen" />

    <main class="min-h-screen bg-[#111111] text-white">
        <nav class="border-b border-white/10 bg-black/50">
            <div
                class="mx-auto flex max-w-[1480px] flex-col gap-3 px-5 py-4 sm:px-8 lg:flex-row lg:items-center lg:justify-between lg:px-10"
            >
                <Link href="/" class="flex items-center gap-3">
                    <span
                        class="flex h-10 w-10 items-center justify-center border border-[#f4a340] bg-[#111111] text-[#f4a340]"
                    >
                        <AppLogoIcon class="h-8 w-8" />
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
                <div v-else class="flex items-center gap-3">
                    <Link
                        v-if="canAccessAuth"
                        :href="login()"
                        class="text-sm font-semibold text-zinc-300 hover:text-white"
                    >
                        Anmelden
                    </Link>
                    <Link
                        :href="bookingHref"
                        class="border border-[#f4a340] bg-[#f4a340] px-4 py-2 text-sm font-black text-black hover:bg-[#ffb65c]"
                    >
                        Buchen
                    </Link>
                </div>
            </div>
        </nav>

        <section
            class="relative min-h-[560px] overflow-hidden bg-cover bg-center"
            style="background-image: url('/images/car-cleaning-hero.jpg')"
        >
            <div
                class="absolute inset-0 bg-gradient-to-r from-black via-black/75 to-black/15"
            ></div>
            <div
                class="relative mx-auto flex min-h-[560px] max-w-[1480px] items-end px-5 py-12 sm:px-8 lg:px-10"
            >
                <div class="max-w-3xl">
                    <p
                        class="mb-4 inline-flex items-center gap-2 border border-white/25 bg-black/40 px-3 py-1 text-xs font-semibold tracking-[0.24em] text-[#f4a340] uppercase"
                    >
                        <Sparkles class="h-4 w-4" />
                        Fahrzeugpflege nach Maß
                    </p>
                    <h1 class="text-5xl leading-tight font-black sm:text-7xl">
                        Autoreinigung konfigurieren und buchen
                    </h1>
                    <p class="mt-5 max-w-2xl text-lg leading-8 text-zinc-200">
                        Informieren, Paket auswählen, Zusatzoptionen ergänzen
                        oder eine Reinigung komplett individuell
                        zusammenstellen.
                    </p>
                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <Link
                            :href="bookingHref"
                            class="inline-flex h-12 items-center justify-center bg-[#f4a340] px-6 font-black text-black hover:bg-[#ffb65c]"
                        >
                            Jetzt konfigurieren
                        </Link>
                        <a
                            href="#pakete"
                            class="inline-flex h-12 items-center justify-center border border-white/25 px-6 font-bold text-white hover:border-[#f4a340]"
                        >
                            Pakete ansehen
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section
            id="pakete"
            class="mx-auto max-w-[1480px] px-5 py-12 sm:px-8 lg:px-10"
        >
            <div class="mb-6">
                <p
                    class="text-xs font-semibold tracking-[0.22em] text-[#f4a340] uppercase"
                >
                    Ablauf
                </p>
                <h2 class="mt-1 text-3xl font-black">So bucht der Kunde</h2>
            </div>

            <div class="grid gap-4 lg:grid-cols-3">
                <article
                    v-for="pkg in packages"
                    :key="pkg.name"
                    class="border border-white/10 bg-[#181818] p-5"
                >
                    <div class="flex items-start justify-between gap-4">
                        <h3 class="text-2xl font-black">{{ pkg.name }}</h3>
                        <span
                            class="font-black whitespace-nowrap text-[#f4a340]"
                            >{{ pkg.price }}</span
                        >
                    </div>
                    <ul class="mt-5 space-y-2 text-sm text-zinc-200">
                        <li
                            v-for="feature in pkg.features"
                            :key="feature"
                            class="flex gap-2"
                        >
                            <Check
                                class="mt-0.5 h-4 w-4 shrink-0 text-[#f4a340]"
                            />
                            <span>{{ feature }}</span>
                        </li>
                    </ul>
                </article>
            </div>

            <div class="mt-8 border border-[#f4a340]/40 bg-[#17110c] p-5">
                <div
                    class="flex flex-col justify-between gap-4 md:flex-row md:items-center"
                >
                    <div>
                        <h2 class="text-2xl font-black">
                            Bereit für die Buchung?
                        </h2>
                        <p class="mt-1 text-zinc-300">
                            Im Konfigurator können Pakete erweitert oder
                            individuelle Leistungen frei kombiniert werden.
                        </p>
                    </div>
                    <Link
                        :href="bookingHref"
                        class="inline-flex h-12 items-center justify-center bg-[#f4a340] px-6 font-black text-black hover:bg-[#ffb65c]"
                    >
                        Buchen
                    </Link>
                </div>
            </div>
        </section>

        <PublicFooter />
    </main>
</template>
