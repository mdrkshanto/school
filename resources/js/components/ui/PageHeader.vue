<script setup lang="ts">
defineProps<{
    title: string;
    subtitle?: string | null;
}>();
</script>

<template>
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-slate-50">{{ title }}</h1>
            <p v-if="subtitle" class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">{{ subtitle }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <slot name="actions" />
        </div>
    </div>
</template>