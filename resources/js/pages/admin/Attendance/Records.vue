<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/components/ui/AppLayout.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import Card from '@/components/ui/Card.vue';
import TextInput from '@/components/ui/TextInput.vue';
import SelectInput from '@/components/ui/SelectInput.vue';
import Badge from '@/components/ui/Badge.vue';
import Pagination from '@/components/ui/Pagination.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import Icon from '@/components/ui/Icon.vue';
import DataTable from '@/components/ui/DataTable.vue';
import { formatDate } from '@/lib/helpers';
import AttendanceController from '@/actions/App/Http/Controllers/Admin/AttendanceController';

type Paginator<T> = {
    data: T[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
};

type AttendanceRecord = {
    id: number;
    student: { id: number; name: string; admission_no: string };
    class: { id: number; name: string };
    session_id: number;
    class_id: number;
    section_id: number | null;
    student_id: number;
    date: string;
    status: string;
    remark: string | null;
};

const props = defineProps<{
    records: Paginator<AttendanceRecord>;
    classes: Array<{ id: number; name: string }>;
    filters: { class_id: string | null; date: string | null; status: string | null };
}>();

defineOptions({ layout: AppLayout });

const statusOptions = [
    { value: 'present', label: 'Present' },
    { value: 'absent', label: 'Absent' },
    { value: 'late', label: 'Late' },
    { value: 'leave', label: 'Leave' },
];

const classOptions = props.classes.map((item) => ({ value: item.id, label: item.name }));

const classId = ref(props.filters.class_id ?? '');
const date = ref(props.filters.date ?? '');
const status = ref(props.filters.status ?? '');

watch(
    () => props.filters,
    (filters) => {
        classId.value = filters.class_id ?? '';
        date.value = filters.date ?? '';
        status.value = filters.status ?? '';
    },
);

function go(field: string, value: string | number | null | undefined) {
    router.get(window.location.pathname, { ...props.filters, [field]: value || undefined }, { preserveState: true, preserveScroll: true, replace: true });
}

function statusTone(status: string): 'green' | 'red' | 'amber' | 'slate' {
    const map: Record<string, 'green' | 'red' | 'amber' | 'slate'> = { present: 'green', absent: 'red', late: 'amber', leave: 'slate' };
    return map[status] ?? 'slate';
}

const columns = [
    { key: 'student', label: 'Student', hideOnMobile: true },
    { key: 'class', label: 'Class', value: (r: any) => r.class?.name ?? null },
    { key: 'date', label: 'Date', value: (r: any) => formatDate(r.date) },
    { key: 'status', label: 'Status' },
    { key: 'remark', label: 'Remark', truncate: true },
];
</script>

<template>
    <div>
        <Head title="Attendance Records" />

        <PageHeader title="Attendance Records" subtitle="Browse saved attendance entries.">
            <template #actions>
                <a :href="AttendanceController.index.url()" class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-700 ring-1 ring-inset ring-slate-300 transition-colors hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-200 dark:ring-slate-700 dark:hover:bg-slate-700">
                    <Icon name="attendance" class="h-4 w-4" />
                    Mark Attendance
                </a>
            </template>
        </PageHeader>

        <Card :padded="false" class="mb-4">
            <div class="grid gap-3 p-4 sm:grid-cols-3">
                <label class="block">
                    <span class="mb-1.5 block text-[13px] font-medium text-slate-700 dark:text-slate-300">Class</span>
                    <SelectInput v-model="classId" :options="classOptions" :allow-empty="true" placeholder="All classes" @update:model-value="go('class_id', classId)" />
                </label>
                <label class="block">
                    <span class="mb-1.5 block text-[13px] font-medium text-slate-700 dark:text-slate-300">Date</span>
                    <TextInput v-model="date" type="date" @update:model-value="go('date', date)" />
                </label>
                <label class="block">
                    <span class="mb-1.5 block text-[13px] font-medium text-slate-700 dark:text-slate-300">Status</span>
                    <SelectInput v-model="status" :options="statusOptions" :allow-empty="true" placeholder="All statuses" @update:model-value="go('status', status)" />
                </label>
            </div>
        </Card>

        <Card :padded="false">
            <div v-if="records.data.length === 0">
                <EmptyState message="No attendance records found." icon="attendance" />
            </div>
            <DataTable
                v-else
                :rows="records.data"
                :columns="columns"
                :row-key="(record) => record.id"
                :title-value="(record) => record.student?.name ?? '—'"
                :subtitle-value="(record) => record.student?.admission_no ? 'Adm. ' + record.student.admission_no : null"
            >
                <template #cell-student="{ row }">
                    <div class="font-medium text-slate-900 dark:text-slate-100">{{ row.student?.name ?? '—' }}</div>
                    <div class="text-xs text-slate-400">Adm. {{ row.student?.admission_no ?? '—' }}</div>
                </template>
                <template #cell-status="{ row }">
                    <Badge :label="row.status" :tone="statusTone(row.status)" />
                </template>
            </DataTable>
            <Pagination :paginator="records" />
        </Card>
    </div>
</template>