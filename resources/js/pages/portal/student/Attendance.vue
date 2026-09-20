<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/components/ui/AppLayout.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import Card from '@/components/ui/Card.vue';
import Badge from '@/components/ui/Badge.vue';
import Icon from '@/components/ui/Icon.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import DataTable from '@/components/ui/DataTable.vue';
import { formatDate } from '@/lib/helpers';

defineOptions({ layout: AppLayout });

const props = defineProps<{
    records: Array<{ id: number; date: string; status: string; remark: string | null }>;
    month: string;
    summary: { total: number; present: number; absent: number; rate: number | null };
}>();

const selectedMonth = ref(props.month);

function changeMonth() {
    router.get('/student/attendance', { month: selectedMonth.value }, { preserveState: true, preserveScroll: true });
}

function statusTone(status: string): 'green' | 'red' | 'amber' | 'sky' {
    if (status === 'present') return 'green';
    if (status === 'absent') return 'red';
    if (status === 'late') return 'amber';
    return 'sky';
}

const columns = [
    { key: 'date', label: 'Date', value: (r: any) => formatDate(r.date) },
    { key: 'status', label: 'Status' },
    { key: 'remark', label: 'Remark', value: (r: any) => r.remark ?? null },
];
</script>

<template>
    <div>
        <Head title="Attendance" />
        <PageHeader title="Attendance">
            <template #actions>
                <input
                    v-model="selectedMonth"
                    type="month"
                    class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                    @change="changeMonth"
                />
            </template>
        </PageHeader>

        <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
            <Card>
                <div class="flex items-center gap-3">
                    <Icon name="attendance" class="h-5 w-5 text-slate-400" />
                    <div>
                        <p class="text-xs text-slate-400">Total Days</p>
                        <p class="text-lg font-bold text-slate-900 dark:text-slate-100">{{ summary.total }}</p>
                    </div>
                </div>
            </Card>
            <Card>
                <div class="flex items-center gap-3">
                    <Icon name="check" class="h-5 w-5 text-emerald-500" />
                    <div>
                        <p class="text-xs text-slate-400">Present</p>
                        <p class="text-lg font-bold text-emerald-600 dark:text-emerald-400">{{ summary.present }}</p>
                    </div>
                </div>
            </Card>
            <Card>
                <div class="flex items-center gap-3">
                    <Icon name="x" class="h-5 w-5 text-red-500" />
                    <div>
                        <p class="text-xs text-slate-400">Absent</p>
                        <p class="text-lg font-bold text-red-600 dark:text-red-400">{{ summary.absent }}</p>
                    </div>
                </div>
            </Card>
            <Card>
                <div class="flex items-center gap-3">
                    <Icon name="attendance" class="h-5 w-5 text-indigo-500" />
                    <div>
                        <p class="text-xs text-slate-400">Rate</p>
                        <p class="text-lg font-bold text-slate-900 dark:text-slate-100">{{ summary.rate !== null ? summary.rate + '%' : 'N/A' }}</p>
                    </div>
                </div>
            </Card>
        </div>

        <template v-if="records.length === 0">
            <EmptyState message="No attendance records for this month." icon="attendance" />
        </template>

        <template v-else>
            <Card :padded="false">
                <DataTable
                    :rows="records"
                    :columns="columns"
                    :row-key="(record) => record.id"
                >
                    <template #cell-status="{ row }">
                        <Badge :label="row.status" :tone="statusTone(row.status)" />
                    </template>
                </DataTable>
            </Card>
        </template>
    </div>
</template>
