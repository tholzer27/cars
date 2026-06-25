<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PublicFooter from '@/components/PublicFooter.vue';
</script>

<template>
    <Head title="AGB" />

    <div class="min-h-screen bg-[#111111] text-white">
        <header class="border-b border-white/10 bg-black">
            <div
                class="mx-auto flex min-h-16 max-w-[1480px] items-center justify-between px-5 sm:px-8 lg:px-10"
            >
                <Link href="/" class="flex items-center gap-3">
                    <span
                        class="flex h-10 w-10 items-center justify-center border border-[#f4a340] bg-[#f4a340] font-black text-black"
                    >
                        CS
                    </span>
                    <span class="text-lg font-black tracking-wide">
                        CarSpa
                    </span>
                </Link>
                <Link
                    href="/booking"
                    class="border border-[#f4a340] px-4 py-2 text-sm font-black text-[#f4a340] hover:bg-[#241b12]"
                >
                    Buchen
                </Link>
            </div>
        </header>

        <main class="mx-auto max-w-4xl px-5 py-12 sm:px-8 lg:px-10">
            <p
                class="text-xs font-semibold tracking-[0.22em] text-[#f4a340] uppercase"
            >
                Rechtliches
            </p>
            <h1 class="mt-2 text-4xl font-black">
                Allgemeine Geschäftsbedingungen
            </h1>

            <div class="mt-8 space-y-6 text-zinc-300">
                <section>
                    <h2 class="text-xl font-black text-white">
                        1. Geltungsbereich
                    </h2>
                    <p class="mt-2">
                        Diese AGB gelten für alle Buchungen von
                        Fahrzeugreinigungen über CarSpa. Abweichende
                        Vereinbarungen gelten nur, wenn sie schriftlich
                        bestätigt wurden.
                    </p>
                </section>
                <section>
                    <h2 class="text-xl font-black text-white">
                        2. Buchung und Termin
                    </h2>
                    <p class="mt-2">
                        Termine werden nach Verfügbarkeit bestätigt. Kundinnen
                        und Kunden stellen sicher, dass das Fahrzeug zur
                        vereinbarten Zeit an der angegebenen Abholadresse
                        zugänglich ist.
                    </p>
                </section>
                <section>
                    <h2 class="text-xl font-black text-white">
                        3. Preise und Zahlung
                    </h2>
                    <p class="mt-2">
                        Es gelten die im Buchungsprozess angezeigten Preise.
                        Zusatzleistungen werden separat ausgewiesen.
                    </p>
                </section>
                <section>
                    <h2 class="text-xl font-black text-white">
                        4. Stornierung
                    </h2>
                    <p class="mt-2">
                        Stornierungen oder Änderungen sollten so früh wie
                        möglich mitgeteilt werden. Bereits begonnene Leistungen
                        können verrechnet werden.
                    </p>
                </section>
                <section>
                    <h2 class="text-xl font-black text-white">5. Haftung</h2>
                    <p class="mt-2">
                        CarSpa haftet für Schäden nur bei vorsätzlicher oder
                        grob fahrlässiger Verursachung. Bereits vorhandene
                        Schäden am Fahrzeug sind vor der Reinigung mitzuteilen.
                    </p>
                </section>
            </div>
        </main>

        <PublicFooter />
    </div>
</template>
