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

type StudentRow = {
    id: number;
    admission_no: string;
    name: string;
    gender: string | null;
    status: string;
    class: { id: number; name: string } | null;
    section: { id: number; name: string } | null;
    session: { id: number; name: string } | null;
};

const props = defineProps<{
    students: Paginator<StudentRow>;
    classes: Array<{ id: number; name: string }>;
    filters: { class_id: string | null; status: string | null };
}>();

const classOptions = props.classes.map((item) => ({ value: item.id, label: item.name }));

const statusOptions = [
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' },
    { value: 'alumni', label: 'Alumni' },
];

function go(field: string, value: string | number | null | undefined) {
    router.get(window.location.pathname, { [field]: value || undefined }, { preserveState: true, preserveScroll: true, replace: true });
}

const columns = [
    { key: 'student', label: 'Student', hideOnMobile: true },
    { key: 'admission_no', label: 'Admission' },
    { key: 'class', label: 'Class', value: (s: any) => s.class?.name ?? null },
    { key: 'section', label: 'Section', value: (s: any) => s.section?.name ?? null },
    { key: 'session', label: 'Session', value: (s: any) => s.session?.name ?? null },
    { key: 'status', label: 'Status' },
];
</script>

<template>
    <div>
        <Head title="Student Report" />
        <PageHeader title="Student Report" subtitle="Enrollment details across classes and sessions." />

        <Card class="mb-4" :padded="false">
            <div class="grid gap-3 p-4 sm:grid-cols-2">
                <SelectInput
                    :options="classOptions"
                    :allow-empty="true"
                    placeholder="All classes"
                    :model-value="filters.class_id ?? ''"
                    @update:model-value="go('class_id', $event ?? '')"
                />
                <SelectInput
                    :options="statusOptions"
                    :allow-empty="true"
                    placeholder="All statuses"
                    :model-value="filters.status ?? ''"
                    @update:model-value="go('status', $event ?? '')"
                />
            </div>
        </Card>

        <Card :padded="false">
            <div v-if="students.data.length === 0">
                <EmptyState message="No students found." icon="students" />
            </div>
            <DataTable
                v-else
                :rows="students.data"
                :columns="columns"
                :row-key="(student) => student.id"
                :title-value="(student) => student.name"
                :subtitle-value="(student) => [student.class?.name, student.section?.name].filter(Boolean).join(' · ') || null"
            >
                <template #cell-student="{ row }">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-semibold text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-300">
                            {{ initials(row.name) }}
                        </span>
                        <span class="font-medium text-slate-900 dark:text-slate-100">{{ row.name }}</span>
                    </div>
                </template>
                <template #cell-status="{ row }">
                    <Badge :label="row.status" :tone="row.status === 'active' ? 'green' : row.status === 'alumni' ? 'amber' : 'slate'" />
                </template>
            </DataTable>
            <Pagination :paginator="students" />
        </Card>
    </div>
</template>