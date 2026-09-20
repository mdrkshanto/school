<script setup lang="ts">
const model = defineModel<string | number | null | undefined>();

defineProps<{
    rows?: number;
    error?: boolean;
    placeholder?: string;
}>();
</script>

<template>
    <textarea
        v-model="model"
        :rows="rows ?? 3"
        :placeholder="placeholder"
        class="w-full rounded-lg border bg-white px-3 py-2 text-sm text-slate-800 shadow-sm outline-none transition-colors placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:placeholder:text-slate-500"
        :class="error ? 'border-red-400 focus:border-red-500 focus:ring-red-500/20' : 'border-slate-300 dark:border-slate-700'"
    />
</template>