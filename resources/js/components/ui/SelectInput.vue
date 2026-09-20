<script setup lang="ts">
import { computed } from 'vue';
import Icon from '@/components/ui/Icon.vue';

export type SelectOption = {
    value: string | number;
    label: string;
};

const model = defineModel<string | number | null | undefined>();

defineProps<{
    options: SelectOption[];
    placeholder?: string;
    allowEmpty?: boolean;
}>();

const wrapperClass = computed(() =>
    [
        'relative w-full',
    ].join(' '),
);
</script>

<template>
    <div :class="wrapperClass">
        <select
            v-model="model"
            class="w-full appearance-none rounded-lg border border-slate-300 bg-white px-3 py-2 pr-9 text-sm text-slate-800 shadow-sm outline-none transition-colors focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
        >
            <option v-if="allowEmpty" value="">{{ placeholder ?? 'Select...' }}</option>
            <option v-for="option in options" :key="String(option.value)" :value="option.value">{{ option.label }}</option>
        </select>
        <Icon name="chevron-down" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
    </div>
</template>