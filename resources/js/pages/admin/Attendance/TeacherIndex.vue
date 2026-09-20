<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/components/ui/AppLayout.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import Card from '@/components/ui/Card.vue';
import Field from '@/components/ui/Field.vue';
import TextInput from '@/components/ui/TextInput.vue';
import SelectInput from '@/components/ui/SelectInput.vue';
import SubmitButton from '@/components/ui/SubmitButton.vue';
import Badge from '@/components/ui/Badge.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import Icon from '@/components/ui/Icon.vue';
import { formatDate, initials, todayIso } from '@/lib/helpers';
import AttendanceController from '@/actions/App/Http/Controllers/Admin/AttendanceController';

type Teacher = {
    id: number;
    name: string;
    employee_no: string;
    current_status: string;
};

const props = defineProps<{
    teachers: Teacher[];
    attendanceDate: string;
}>();

defineOptions({ layout: AppLayout });

const statusOptions = [
    { value: 'present', label: 'Present' },
    { value: 'absent', label: 'Absent' },
    { value: 'late', label: 'Late' },
    { value: 'leave', label: 'Leave' },
];

const date = ref(props.attendanceDate ?? todayIso());

function go(field: string, value: string | null | undefined) {
    router.get(window.location.pathname, { [field]: value || undefined }, { preserveState: true, preserveScroll: true, replace: true });
}

const statuses = reactive<Record<number, string>>({});

watch(
    () => props.teachers,
    (teachers) => {
        Object.keys(statuses).forEach((key) => delete statuses[Number(key)]);
        teachers.forEach((teacher) => {
            statuses[teacher.id] = teacher.current_status || 'present';
        });
    },
    { immediate: true },
);

const summary = computed(() => {
    const counts = { present: 0, absent: 0, late: 0, leave: 0 };

    props.teachers.forEach((teacher) => {
        const status = statuses[teacher.id] || 'present';
        if (counts[status as keyof typeof counts] !== undefined) {
            counts[status as keyof typeof counts] += 1;
        }
    });

    return counts;
});

function statusTone(status: string): 'green' | 'red' | 'amber' | 'slate' {
    const map: Record<string, 'green' | 'red' | 'amber' | 'slate'> = { present: 'green', absent: 'red', late: 'amber', leave: 'slate' };
    return map[status] ?? 'slate';
}

const form = useForm<{
    date: string;
    records: Array<{ teacher_id: number; status: string; remark: string | null }>;
}>({
    date: todayIso(),
    records: [],
});

function submit() {
    form.clearErrors();
    form.date = date.value;
    form.records = props.teachers.map((teacher) => ({
        teacher_id: teacher.id,
        status: statuses[teacher.id] || 'present',
        remark: null,
    }));
    form.post(AttendanceController.teacherMark.url(), { preserveScroll: true });
}
</script>

<template>
    <div>
        <Head title="Teacher Attendance" />

        <PageHeader title="Teacher Attendance" subtitle="Mark daily attendance for teaching staff.">
            <template #actions>
                <Link :href="AttendanceController.index.url()" class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-700 ring-1 ring-inset ring-slate-300 transition-colors hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-200 dark:ring-slate-700 dark:hover:bg-slate-700">
                    <Icon name="students" class="h-4 w-4" />
                    Students
                </Link>
            </template>
        </PageHeader>

        <Card :padded="false" class="mb-4">
            <div class="grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="sm:col-span-1">
                    <Field label="Date">
                        <TextInput v-model="date" type="date" @update:model-value="go('date', date)" />
                    </Field>
                </div>
            </div>
        </Card>

        <form @submit.prevent="submit">
            <Card v-if="teachers.length > 0" class="mb-4">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex flex-wrap items-center gap-4 text-sm">
                        <div>
                            <span class="text-xs font-medium text-slate-500">Present</span>
                            <p class="text-lg font-bold text-emerald-600">{{ summary.present }}</p>
                        </div>
                        <div>
                            <span class="text-xs font-medium text-slate-500">Absent</span>
                            <p class="text-lg font-bold text-red-600">{{ summary.absent }}</p>
                        </div>
                        <div>
                            <span class="text-xs font-medium text-slate-500">Late</span>
                            <p class="text-lg font-bold text-amber-600">{{ summary.late }}</p>
                        </div>
                        <div>
                            <span class="text-xs font-medium text-slate-500">Leave</span>
                            <p class="text-lg font-bold text-slate-600">{{ summary.leave }}</p>
                        </div>
                    </div>
                    <p class="text-xs text-slate-400">Attendance for {{ formatDate(date) }}</p>
                    <SubmitButton :processing="form.processing">Save Attendance</SubmitButton>
                </div>
            </Card>

            <div v-if="teachers.length > 0" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <div v-for="teacher in teachers" :key="teacher.id" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-semibold text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-300">
                            {{ initials(teacher.name) }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-slate-800 dark:text-slate-200">{{ teacher.name }}</p>
                            <p class="text-xs text-slate-400">Emp. {{ teacher.employee_no }}</p>
                        </div>
                        <Badge :label="statuses[teacher.id] ?? 'present'" :tone="statusTone(statuses[teacher.id] ?? 'present')" />
                    </div>
                    <div class="mt-3">
                        <SelectInput v-model="statuses[teacher.id]" :options="statusOptions" />
                    </div>
                </div>
            </div>

            <EmptyState v-else message="No active teachers found." icon="teachers" />
        </form>
    </div>
</template>