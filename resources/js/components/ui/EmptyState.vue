<script setup lang="ts">
import Icon from '@/components/ui/Icon.vue';

withDefaults(
    defineProps<{
        message?: string;
        icon?: string;
    }>(),
    { message: 'No records found.', icon: 'circle-alert' },
);
</script>

<template>
    <div class="flex flex-col items-center justify-center px-4 py-14 text-center">
        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500">
            <Icon :name="icon" class="h-6 w-6" />
        </div>
        <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">{{ message }}</p>
        <div class="mt-2">
            <slot />
        </div>
    </div>
</template>