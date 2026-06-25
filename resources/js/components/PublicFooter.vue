<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <footer class="border-t border-white/10 bg-black text-white">
        <div
            class="mx-auto flex max-w-[1480px] flex-col gap-4 px-5 py-6 text-sm text-zinc-400 sm:px-8 md:flex-row md:items-center md:justify-between lg:px-10"
        >
            <div>
                <p class="font-black tracking-wide text-white">CarSpa</p>
                <p class="mt-1">Autoreinigung mit Abholung nach Termin.</p>
            </div>
            <nav class="flex flex-wrap gap-4 font-semibold">
                <Link href="/agbs" class="hover:text-[#f4a340]">AGB</Link>
                <Link href="/impressum" class="hover:text-[#f4a340]">
                    Impressum
                </Link>
            </nav>
        </div>
    </footer>
</template>
