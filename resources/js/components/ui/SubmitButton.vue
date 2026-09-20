<script setup lang="ts">
withDefaults(
    defineProps<{
        processing?: boolean;
        variant?: 'primary' | 'secondary' | 'danger';
    }>(),
    { variant: 'primary' },
);

const variants: Record<string, string> = {
    primary: 'bg-indigo-600 text-white hover:bg-indigo-700 focus:ring-indigo-500/40',
    secondary: 'bg-white text-slate-700 ring-1 ring-inset ring-slate-300 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-200 dark:ring-slate-700 dark:hover:bg-slate-700',
    danger: 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500/40',
};
</script>

<template>
    <button
        type="submit"
        :disabled="processing"
        class="inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-offset-1 disabled:cursor-not-allowed disabled:opacity-60"
        :class="variants[variant]"
    >
        <svg v-if="processing" class="h-4 w-4 animate-spin text-current" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z" />
        </svg>
        <slot>{{ processing ? 'Saving...' : 'Save' }}</slot>
    </button>
</template>