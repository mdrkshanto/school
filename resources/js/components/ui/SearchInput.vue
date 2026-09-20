<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { onUnmounted, ref, watch } from 'vue';
import Icon from '@/components/ui/Icon.vue';

const props = withDefaults(
    defineProps<{
        placeholder?: string;
        param?: string;
        value?: string;
    }>(),
    { placeholder: 'Search...', param: 'search', value: '' },
);

const query = ref(props.value);

watch(
    () => props.value,
    (value) => {
        query.value = value;
    },
);

let timer: number | null = null;

function handleInput() {
    if (timer) window.clearTimeout(timer);
    timer = window.setTimeout(() => {
        router.get(window.location.pathname, { [props.param]: query.value || undefined }, { preserveState: true, preserveScroll: true, replace: true });
    }, 350);
}

function clear() {
    query.value = '';
    router.get(window.location.pathname, { [props.param]: undefined }, { preserveState: true, preserveScroll: true, replace: true });
}

onUnmounted(() => {
    if (timer) window.clearTimeout(timer);
});
</script>

<template>
    <div class="relative w-full sm:w-64">
        <Icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
        <input
            v-model="query"
            type="search"
            :placeholder="placeholder"
            class="w-full rounded-lg border border-slate-300 bg-white py-2 pl-9 pr-8 text-sm text-slate-800 shadow-sm outline-none transition-colors placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
            @input="handleInput"
        />
        <button v-if="query" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600" @click="clear">
            <Icon name="x" class="h-4 w-4" />
        </button>
    </div>
</template>