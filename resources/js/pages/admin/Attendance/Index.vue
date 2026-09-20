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

type Student = {
    id: number;
    name: string;
    admission_no: string;
    current_status: string;
};

const props = defineProps<{
    students: Student[];
    sessions: Array<{ id: number; name: string }>;
    classes: Array<{ id: number; name: string }>;
    sections: Array<{ id: number; name: string; class_id: number }>;
    filters: { session_id: string | null; class_id: string | null; section_id: string | null; date: string | null };
    attendanceDate: string;
}>();

defineOptions({ layout: AppLayout });

const statusOptions = [
    { value: 'present', label: 'Present' },
    { value: 'absent', label: 'Absent' },
    { value: 'late', label: 'Late' },
    { value: 'leave', label: 'Leave' },
];

const sessionOptions = props.sessions.map((session) => ({ value: session.id, label: session.name }));
const classOptions = props.classes.map((item) => ({ value: item.id, label: item.name }));

const sessionId = ref(props.filters.session_id ?? String(props.sessions[0]?.id ?? ''));
const classId = ref(props.filters.class_id ?? '');
const sectionId = ref(props.filters.section_id ?? '');
const date = ref(props.attendanceDate ?? todayIso());

watch(
    () => props.filters,
    (filters) => {
        sessionId.value = filters.session_id ?? String(props.sessions[0]?.id ?? '');
        classId.value = filters.class_id ?? '';
        sectionId.value = filters.section_id ?? '';
        date.value = filters.date ?? todayIso();
    },
);

const sectionOptions = computed(() =>
    props.sections
        .filter((section) => Number(classId.value as string) === section.class_id)
        .map((section) => ({ value: section.id, label: section.name })),
);

const statuses = reactive<Record<number, string>>({});
const remarks = reactive<Record<number, string>>({});

watch(
    () => props.students,
    (students) => {
        Object.keys(statuses).forEach((key) => delete statuses[Number(key)]);
        Object.keys(remarks).forEach((key) => delete remarks[Number(key)]);
        students.forEach((student) => {
            statuses[student.id] = student.current_status || 'present';
            remarks[student.id] = '';
        });
    },
    { immediate: true },
);

const summary = computed(() => {
    const counts = { present: 0, absent: 0, late: 0, leave: 0 };

    props.students.forEach((student) => {
        const status = statuses[student.id] || 'present';
        if (counts[status as keyof typeof counts] !== undefined) {
            counts[status as keyof typeof counts] += 1;
        }
    });

    return counts;
});

const markedRate = computed(() => {
    if (props.students.length === 0) return 0;
    return Math.round((summary.value.present / props.students.length) * 100);
});

function go(field: string, value: string | number | null | undefined) {
    router.get(window.location.pathname, { ...props.filters, [field]: value || undefined }, { preserveState: true, preserveScroll: true, replace: true });
}

function changeClass() {
    sectionId.value = '';
    router.get(
        window.location.pathname,
        { ...props.filters, class_id: classId.value || undefined, section_id: undefined },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

function statusTone(status: string): 'green' | 'red' | 'amber' | 'slate' {
    const map: Record<string, 'green' | 'red' | 'amber' | 'slate'> = { present: 'green', absent: 'red', late: 'amber', leave: 'slate' };
    return map[status] ?? 'slate';
}

const form = useForm<{
    session_id: string | null;
    class_id: string | null;
    section_id: string | null;
    date: string;
    records: Array<{ student_id: number; status: string; remark: string | null }>;
}>({
    session_id: null,
    class_id: null,
    section_id: null,
    date: todayIso(),
    records: [],
});

function submit() {
    form.clearErrors();
    form.session_id = sessionId.value || null;
    form.class_id = classId.value || null;
    form.section_id = sectionId.value || null;
    form.date = date.value;
    form.records = props.students.map((student) => ({
        student_id: student.id,
        status: statuses[student.id] || 'present',
        remark: remarks[student.id]?.trim() || null,
    }));
    form.post(AttendanceController.mark.url(), { preserveScroll: true });
}
</script>

<template>
    <div>
        <Head title="Student Attendance" />

        <PageHeader title="Student Attendance" subtitle="Mark daily attendance for a class.">
            <template #actions>
                <Link :href="AttendanceController.records.url()" class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-700 ring-1 ring-inset ring-slate-300 transition-colors hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-200 dark:ring-slate-700 dark:hover:bg-slate-700">
                    <Icon name="clock" class="h-4 w-4" />
                    Records
                </Link>
                <Link :href="AttendanceController.teacherIndex.url()" class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-700 ring-1 ring-inset ring-slate-300 transition-colors hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-200 dark:ring-slate-700 dark:hover:bg-slate-700">
                    <Icon name="teachers" class="h-4 w-4" />
                    Teachers
                </Link>
            </template>
        </PageHeader>

        <Card :padded="false" class="mb-4">
            <div class="grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-4">
                <Field label="Session">
                    <SelectInput v-model="sessionId" :options="sessionOptions" @update:model-value="go('session_id', sessionId)" />
                </Field>
                <Field label="Class">
                    <SelectInput v-model="classId" :options="classOptions" :allow-empty="true" placeholder="Select class" @update:model-value="changeClass" />
                </Field>
                <Field label="Section">
                    <SelectInput v-model="sectionId" :options="sectionOptions" :allow-empty="true" placeholder="All sections" @update:model-value="go('section_id', sectionId)" />
                </Field>
                <Field label="Date">
                    <TextInput v-model="date" type="date" @update:model-value="go('date', date)" />
                </Field>
            </div>
        </Card>

        <form @submit.prevent="submit">
            <Card v-if="students.length > 0" class="mb-4">
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
                    <div class="flex flex-col gap-3">
                        <div class="h-2 w-40 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-indigo-600 transition-all" :style="{ width: markedRate + '%' }" />
                        </div>
                        <p class="text-xs text-slate-400">{{ markedRate }}% present on {{ formatDate(date) }}</p>
                    </div>
                    <SubmitButton :processing="form.processing">Save Attendance</SubmitButton>
                </div>
            </Card>

            <div v-if="students.length > 0" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <div v-for="student in students" :key="student.id" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-semibold text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-300">
                            {{ initials(student.name) }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-slate-800 dark:text-slate-200">{{ student.name }}</p>
                            <p class="text-xs text-slate-400">Adm. {{ student.admission_no }}</p>
                        </div>
                        <Badge :label="statuses[student.id] ?? 'present'" :tone="statusTone(statuses[student.id] ?? 'present')" />
                    </div>
                    <div class="mt-3 space-y-2">
                        <SelectInput v-model="statuses[student.id]" :options="statusOptions" />
                        <TextInput v-model="remarks[student.id]" placeholder="Remark (optional)" />
                    </div>
                </div>
            </div>

            <EmptyState v-else :message="!classId ? 'Select a class to load the student list.' : 'No active students found for the selected class.'" icon="students" />
        </form>
    </div>
</template>