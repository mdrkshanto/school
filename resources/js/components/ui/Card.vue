<script setup lang="ts">
withDefaults(
    defineProps<{
        padded?: boolean;
    }>(),
    { padded: true },
);
</script>

<template>
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div :class="padded ? 'p-5' : ''">
            <slot />
        </div>
    </div>
</template>