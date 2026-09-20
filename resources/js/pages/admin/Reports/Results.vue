<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/components/ui/AppLayout.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import Card from '@/components/ui/Card.vue';
import Badge from '@/components/ui/Badge.vue';
import Pagination from '@/components/ui/Pagination.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import SelectInput from '@/components/ui/SelectInput.vue';
import DataTable from '@/components/ui/DataTable.vue';
import { initials } from '@/lib/helpers';

defineOptions({ layout: AppLayout });

type Paginator<T> = {
    data: T[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
};

type ResultRow = {
    id: number;
    obtained_marks: string;
    grade: string | null;
    grade_point: string | null;
    is_absent: boolean;
    student: { id: number; name: string; admission_no: string };
    exam: { id: number; name: string };
    subject: { id: number; name: string };
};

const props = defineProps<{
    results: Paginator<ResultRow>;
    exams: Array<{ id: number; name: string }>;
    filters: { exam_id: string | null };
}>();

const examOptions = props.exams.map((exam) => ({ value: exam.id, label: exam.name }));

function go(field: string, value: string | number | null | undefined) {
    router.get(window.location.pathname, { [field]: value || undefined }, { preserveState: true, preserveScroll: true, replace: true });
}

const columns = [
    { key: 'student', label: 'Student', hideOnMobile: true },
    { key: 'exam', label: 'Exam', value: (r: any) => r.exam.name },
    { key: 'subject', label: 'Subject', value: (r: any) => r.subject.name },
    { key: 'obtained_marks', label: 'Marks', align: 'right' as const },
    { key: 'grade', label: 'Grade', align: 'center' as const },
    { key: 'grade_point', label: 'GPA', align: 'center' as const },
];
</script>

<template>
    <div>
        <Head title="Results Report" />
        <PageHeader title="Results Report" subtitle="Exam marks, grades, and grade points per subject." />

        <Card class="mb-4" :padded="false">
            <div class="grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-3">
                <SelectInput
                    :options="examOptions"
                    :allow-empty="true"
                    placeholder="All exams"
                    :model-value="filters.exam_id ?? ''"
                    @update:model-value="go('exam_id', $event ?? '')"
                />
            </div>
        </Card>

        <Card :padded="false">
            <div v-if="results.data.length === 0">
                <EmptyState message="No results found." icon="results" />
            </div>
            <DataTable
                v-else
                :rows="results.data"
                :columns="columns"
                :row-key="(result) => result.id"
                :title-value="(result) => result.student.name"
                :subtitle-value="(result) => result.student.admission_no"
            >
                <template #cell-student="{ row }">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-semibold text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-300">
                            {{ initials(row.student.name) }}
                        </span>
                        <div>
                            <div class="font-medium text-slate-900 dark:text-slate-100">{{ row.student.name }}</div>
                            <div class="text-xs text-slate-400">{{ row.student.admission_no }}</div>
                        </div>
                    </div>
                </template>
                <template #cell-obtained_marks="{ row }">
                    <Badge v-if="row.is_absent" label="Absent" tone="red" />
                    <span v-else class="font-medium text-slate-800 dark:text-slate-200">{{ row.obtained_marks }}</span>
                </template>
                <template #cell-grade="{ row }">
                    <Badge
                        v-if="!row.is_absent && row.grade"
                        :label="row.grade"
                        :tone="row.grade === 'F' ? 'red' : row.grade_point && Number(row.grade_point) >= 3.5 ? 'green' : 'amber'"
                    />
                    <span v-else>—</span>
                </template>
            </DataTable>
            <Pagination :paginator="results" />
        </Card>
    </div>
</template>