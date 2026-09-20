<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        label: string;
        tone?: 'green' | 'red' | 'amber' | 'slate' | 'indigo' | 'sky';
    }>(),
    { tone: 'slate' },
);

const tones: Record<string, string> = {
    green: 'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-300 dark:ring-emerald-800',
    red: 'bg-red-50 text-red-700 ring-red-200 dark:bg-red-950/50 dark:text-red-300 dark:ring-red-800',
    amber: 'bg-amber-50 text-amber-700 ring-amber-200 dark:bg-amber-950/50 dark:text-amber-300 dark:ring-amber-800',
    slate: 'bg-slate-100 text-slate-600 ring-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-700',
    indigo: 'bg-indigo-50 text-indigo-700 ring-indigo-200 dark:bg-indigo-950/50 dark:text-indigo-300 dark:ring-indigo-800',
    sky: 'bg-sky-50 text-sky-700 ring-sky-200 dark:bg-sky-950/50 dark:text-sky-300 dark:ring-sky-800',
};

const toneClass = computed(() => tones[props.tone] ?? tones.slate!);
</script>

<template>
    <span
        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-semibold capitalize ring-1 ring-inset"
        :class="toneClass"
    >
        {{ label }}
    </span>
</template>