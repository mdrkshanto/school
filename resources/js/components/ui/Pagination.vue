<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';
import Icon from '@/components/ui/Icon.vue';

type Paginator = {
    data: unknown[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
};

const props = defineProps<{
    paginator: Paginator;
}>();

const range = computed(() => {
    const from = props.paginator.total === 0 ? 0 : (props.paginator.current_page - 1) * props.paginator.per_page + 1;
    const to = Math.min(props.paginator.current_page * props.paginator.per_page, props.paginator.total);

    return { from, to };
});

function go(url: string) {
    router.visit(url, { preserveState: true, preserveScroll: true, only: [] });
}
</script>

<template>
    <div v-if="paginator.total > 0" class="flex flex-col items-center justify-between gap-3 px-1 pt-4 sm:flex-row">
        <p class="text-xs text-slate-500 dark:text-slate-400">
            Showing <span class="font-medium text-slate-700 dark:text-slate-200">{{ range.from }}</span> to
            <span class="font-medium text-slate-700 dark:text-slate-200">{{ range.to }}</span> of
            <span class="font-medium text-slate-700 dark:text-slate-200">{{ paginator.total }}</span> results
        </p>

        <div class="flex items-center gap-1">
            <button
                v-for="link in paginator.links"
                :key="link.label + (link.url ?? '')"
                :disabled="!link.url"
                class="flex h-8 min-w-8 items-center justify-center rounded-lg px-2 text-xs font-medium transition-colors disabled:cursor-not-allowed disabled:opacity-40"
                :class="link.active ? 'bg-indigo-600 text-white' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800'"
                v-html="link.label.replace(/&laquo;|&lsaquo;/, '&lsaquo;').replace(/&raquo;|&rsaquo;/, '&rsaquo;')"
                @click="link.url && go(link.url)"
            />
        </div>
    </div>
</template>