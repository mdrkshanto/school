<script setup lang="ts">
defineProps<{
    label: string;
    error?: string;
    required?: boolean;
    hint?: string;
}>();
</script>

<template>
    <label class="block">
        <span class="mb-1.5 block text-[13px] font-medium text-slate-700 dark:text-slate-300">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </span>
        <slot />
        <span v-if="hint" class="mt-1 block text-[11px] text-slate-400">{{ hint }}</span>
        <span v-if="error" class="mt-1 block text-xs font-medium text-red-500">{{ error }}</span>
    </label>
</template>