<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PublicFooter from '@/components/PublicFooter.vue';
</script>

<template>
    <Head title="Impressum" />

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
                Kontakt
            </p>
            <h1 class="mt-2 text-4xl font-black">Impressum</h1>

            <div class="mt-8 grid gap-6 text-zinc-300 md:grid-cols-2">
                <section class="border border-white/10 bg-[#181818] p-5">
                    <h2 class="text-xl font-black text-white">Anbieter</h2>
                    <p class="mt-3">
                        CarSpa<br />
                        Musterstrasse 1<br />
                        8000 Zürich<br />
                        Schweiz
                    </p>
                </section>
                <section class="border border-white/10 bg-[#181818] p-5">
                    <h2 class="text-xl font-black text-white">Kontakt</h2>
                    <p class="mt-3">
                        Telefon: +41 00 000 00 00<br />
                        E-Mail: info@example.ch
                    </p>
                </section>
                <section
                    class="border border-white/10 bg-[#181818] p-5 md:col-span-2"
                >
                    <h2 class="text-xl font-black text-white">Hinweis</h2>
                    <p class="mt-3">
                        Bitte ersetzen Sie diese Platzhalter durch die korrekten
                        Unternehmensangaben, bevor die Seite öffentlich genutzt
                        wird.
                    </p>
                </section>
            </div>
        </main>

        <PublicFooter />
    </div>
</template>
