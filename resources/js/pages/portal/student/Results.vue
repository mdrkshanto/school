<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/components/ui/AppLayout.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import Card from '@/components/ui/Card.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import Icon from '@/components/ui/Icon.vue';
import { formatDate } from '@/lib/helpers';

defineOptions({ layout: AppLayout });

defineProps<{
    exams: Array<{ id: number; name: string; starts_at: string }>;
}>();
</script>

<template>
    <div>
        <Head title="Results" />
        <PageHeader title="Exam Results" />

        <template v-if="exams.length === 0">
            <EmptyState message="No exam results available yet." icon="results" />
        </template>

        <template v-else>
            <div class="space-y-3">
                <Link
                    v-for="exam in exams"
                    :key="exam.id"
                    :href="`/student/results/${exam.id}`"
                    class="block rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition-colors hover:border-indigo-300 hover:bg-indigo-50/50 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-indigo-700"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ exam.name }}</p>
                            <p class="mt-0.5 text-xs text-slate-400">{{ formatDate(exam.starts_at) }}</p>
                        </div>
                        <Icon name="chevron-right" class="h-5 w-5 text-slate-400" />
                    </div>
                </Link>
            </div>
        </template>
    </div>
</template>
