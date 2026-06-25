<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 64 64"
        :class="className"
        v-bind="$attrs"
    >
        <path d="M17 36.5h30l-3.8-10.2A5 5 0 0 0 38.5 23h-13a5 5 0 0 0-4.7 3.3L17 36.5Z" fill="currentColor" />
        <path d="M21 36.5h22.2l-2.6-7a3 3 0 0 0-2.8-2H26.4a3 3 0 0 0-2.8 2L21 36.5Z" fill="#111111" />
        <path d="M13 38h38a2 2 0 0 1 2 2v5H11v-5a2 2 0 0 1 2-2Z" fill="currentColor" />
        <circle cx="22" cy="45" r="4" fill="#111111" />
        <circle cx="42" cy="45" r="4" fill="#111111" />
        <path d="M47.5 13l1.6 4.4 4.4 1.6-4.4 1.6-1.6 4.4-1.6-4.4-4.4-1.6 4.4-1.6L47.5 13Z" fill="white" />
        <path d="M12 17.5h11" stroke="currentColor" stroke-width="4" stroke-linecap="round" />
        <path d="M12 25.5h7" stroke="currentColor" stroke-width="4" stroke-linecap="round" />
    </svg>
</template>
