<script setup lang="ts">
defineProps<{
    routes?: {
        options: unknown;
        submit: unknown;
    };
    label?: string;
    loadingLabel?: string;
    separator?: string;
}>();
</script>

<template>
    <div
        v-if="separator"
        class="relative my-6 text-center text-sm text-muted-foreground"
    >
        {{ separator }}
    </div>
</template>
