<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppLayout from '@/components/ui/AppLayout.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import Card from '@/components/ui/Card.vue';
import Pagination from '@/components/ui/Pagination.vue';
import SelectInput from '@/components/ui/SelectInput.vue';
import type { SelectOption } from '@/components/ui/SelectInput.vue';
import Badge from '@/components/ui/Badge.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import { formatDate } from '@/lib/helpers';

defineOptions({ layout: AppLayout });

const props = defineProps<{
    notices: {
        data: Array<{ id: number; title: string; body: string; category: string; published_at: string }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
        total: number;
        per_page: number;
        current_page: number;
        last_page: number;
    };
    categories: string[];
    filters: { category?: string };
}>();

const categoryOptions = computed<SelectOption[]>(() =>
    props.categories.map((c) => ({ value: c, label: c })),
);

const selectedCategory = ref<string | null>(props.filters.category ?? null);

function applyFilter() {
    const params: Record<string, string> = {};
    if (selectedCategory.value) params.category = selectedCategory.value;
    router.get('/teacher/notices', params, { preserveState: true, preserveScroll: true });
}
</script>

<template>
    <div>
        <Head title="Notices" />
        <PageHeader title="Notices">
            <template #actions>
                <SelectInput v-model="selectedCategory" :options="categoryOptions" placeholder="All categories" :allow-empty="true" @change="applyFilter" />
            </template>
        </PageHeader>

        <template v-if="notices.data.length === 0">
            <EmptyState message="No notices found." icon="notices" />
        </template>

        <template v-else>
            <div class="space-y-4">
                <Card v-for="notice in notices.data" :key="notice.id">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">{{ notice.title }}</h3>
                            <p class="mt-1 text-xs text-slate-400">{{ formatDate(notice.published_at) }}</p>
                        </div>
                        <Badge :label="notice.category" tone="slate" />
                    </div>
                    <p class="mt-3 whitespace-pre-line text-sm text-slate-600 dark:text-slate-400">{{ notice.body }}</p>
                </Card>
            </div>
            <Pagination :paginator="notices" />
        </template>
    </div>
</template>
