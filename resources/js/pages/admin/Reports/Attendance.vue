<script setup lang="ts">
import { computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/components/ui/AppLayout.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import Card from '@/components/ui/Card.vue';
import Badge from '@/components/ui/Badge.vue';
import Icon from '@/components/ui/Icon.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import DataTable from '@/components/ui/DataTable.vue';
import { formatDate } from '@/lib/helpers';

defineOptions({ layout: AppLayout });

type DailyRow = {
    date: string;
    total: number;
    present: number;
    absent: number;
    late: number;
};

const props = defineProps<{
    dailyTotals: DailyRow[];
    month: string;
    summary: { present: number; absent: number };
}>();

const rate = computed(() => {
    const marked = props.summary.present + props.summary.absent;
    return marked > 0 ? Math.round((props.summary.present / marked) * 100) : 0;
});

function changeMonth(event: Event) {
    const value = (event.target as HTMLInputElement).value;
    router.get(window.location.pathname, { month: value || undefined }, { preserveState: true, preserveScroll: true, replace: true });
}

const columns = [
    { key: 'date', label: 'Date', value: (r: any) => formatDate(r.date) },
    { key: 'present', label: 'Present', align: 'center' as const },
    { key: 'late', label: 'Late', align: 'center' as const },
    { key: 'absent', label: 'Absent', align: 'center' as const },
    { key: 'total', label: 'Total', align: 'center' as const },
    { key: 'rate', label: 'Rate', align: 'right' as const },
];
</script>

<template>
    <div>
        <Head title="Attendance Report" />
        <PageHeader title="Attendance Report" subtitle="Daily student attendance summaries.">
            <template #actions>
                <input
                    type="month"
                    class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm outline-none transition-colors focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                    :value="month"
                    @change="changeMonth"
                />
            </template>
        </PageHeader>

        <div class="mb-6 grid gap-4 sm:grid-cols-3">
            <Card>
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400">
                        <Badge label="P" tone="green" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-slate-900 dark:text-slate-50">{{ summary.present }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Present marks</p>
                    </div>
                </div>
            </Card>
            <Card>
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-red-50 text-red-600 dark:bg-red-950/50 dark:text-red-400">
                        <Badge label="A" tone="red" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-slate-900 dark:text-slate-50">{{ summary.absent }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Absent marks</p>
                    </div>
                </div>
            </Card>
            <Card>
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400">
                        <Icon name="attendance" class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-slate-900 dark:text-slate-50">{{ rate }}%</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Attendance rate</p>
                    </div>
                </div>
            </Card>
        </div>

        <Card :padded="false">
            <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">Daily Totals</h3>
            </div>
            <div v-if="dailyTotals.length === 0">
                <EmptyState message="No attendance recorded for this month." icon="attendance" />
            </div>
            <DataTable
                v-else
                :rows="dailyTotals"
                :columns="columns"
                :row-key="(row) => row.date"
            >
                <template #cell-present="{ row }">
                    <span class="text-emerald-600 dark:text-emerald-400">{{ row.present }}</span>
                </template>
                <template #cell-late="{ row }">
                    <span class="text-amber-600 dark:text-amber-400">{{ row.late }}</span>
                </template>
                <template #cell-absent="{ row }">
                    <span class="text-red-600 dark:text-red-400">{{ row.absent }}</span>
                </template>
                <template #cell-rate="{ row }">
                    <span class="font-medium text-slate-800 dark:text-slate-200">{{ row.total > 0 ? Math.round((row.present / row.total) * 100) : 0 }}%</span>
                </template>
            </DataTable>
        </Card>
    </div>
</template>