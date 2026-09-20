<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppLayout from '@/components/ui/AppLayout.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import Card from '@/components/ui/Card.vue';
import SelectInput from '@/components/ui/SelectInput.vue';
import type { SelectOption } from '@/components/ui/SelectInput.vue';
import TextInput from '@/components/ui/TextInput.vue';
import TextareaInput from '@/components/ui/TextareaInput.vue';
import SubmitButton from '@/components/ui/SubmitButton.vue';
import EmptyState from '@/components/ui/EmptyState.vue';

defineOptions({ layout: AppLayout });

const props = defineProps<{
    assignments: Array<{ class_id: number; section_id: number | null; class: string; section: string | null; is_class_teacher: boolean }>;
    students: Array<{ id: number; name: string; admission_no: string; current_status: string }>;
    date: string;
    sessionName: string | null;
}>();

const classOptions = computed<SelectOption[]>(() =>
    props.assignments.map((a) => ({
        value: a.class_id,
        label: `${a.class}${a.section ? ' - ' + a.section : ''}`,
    })),
);

const selectedClassId = ref<number | null>(props.assignments[0]?.class_id ?? null);
const selectedDate = ref(props.date);

const records = ref<Record<number, { status: string; remark: string }>>(
    Object.fromEntries(props.students.map((s) => [s.id, { status: s.current_status, remark: '' }])),
);

function recordFor(studentId: number) {
    if (!records.value[studentId]) {
        records.value[studentId] = { status: 'present', remark: '' };
    }

    return records.value[studentId];
}

function loadStudents() {
    if (selectedClassId.value === null) return;
    const assignment = props.assignments.find((a) => a.class_id === selectedClassId.value);
    router.get(
        '/teacher/attendance',
        {
            class_id: selectedClassId.value,
            section_id: assignment?.section_id,
            date: selectedDate.value,
        },
        { preserveState: true, preserveScroll: true },
    );
}

const form = useForm({
    class_id: 0,
    section_id: null as number | null,
    date: '',
    records: [] as Array<{ student_id: number; status: string; remark: string }>,
});

function save() {
    const assignment = props.assignments.find((a) => a.class_id === selectedClassId.value);
    form.class_id = selectedClassId.value!;
    form.section_id = assignment?.section_id ?? null;
    form.date = selectedDate.value;
    form.records = props.students.map((s) => ({
        student_id: s.id,
        status: records.value[s.id]?.status ?? 'present',
        remark: records.value[s.id]?.remark ?? '',
    }));
    form.post('/teacher/attendance/mark', { preserveScroll: true });
}

function setStatus(studentId: number, status: string) {
    if (!records.value[studentId]) {
        records.value[studentId] = { status, remark: '' };
    } else {
        records.value[studentId].status = status;
    }
}

const statusOptions = ['present', 'absent', 'late', 'leave'];
const statusColors: Record<string, string> = {
    present: 'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-300',
    absent: 'bg-red-50 text-red-700 ring-red-200 dark:bg-red-950/50 dark:text-red-300',
    late: 'bg-amber-50 text-amber-700 ring-amber-200 dark:bg-amber-950/50 dark:text-amber-300',
    leave: 'bg-sky-50 text-sky-700 ring-sky-200 dark:bg-sky-950/50 dark:text-sky-300',
};
const activeStatusColors: Record<string, string> = {
    present: 'bg-emerald-600 text-white ring-emerald-600',
    absent: 'bg-red-600 text-white ring-red-600',
    late: 'bg-amber-600 text-white ring-amber-600',
    leave: 'bg-sky-600 text-white ring-sky-600',
};
</script>

<template>
    <div>
        <Head title="Attendance" />
        <PageHeader title="Mark Attendance" :subtitle="sessionName" />

        <Card class="mb-6">
            <div class="flex flex-wrap items-end gap-4">
                <div class="w-full sm:w-64">
                    <label class="mb-1.5 block text-[13px] font-medium text-slate-700 dark:text-slate-300">Class / Section</label>
                    <SelectInput v-model="selectedClassId" :options="classOptions" placeholder="Select class" @change="loadStudents" />
                </div>
                <div class="w-full sm:w-48">
                    <label class="mb-1.5 block text-[13px] font-medium text-slate-700 dark:text-slate-300">Date</label>
                    <TextInput v-model="selectedDate" type="date" @change="loadStudents" />
                </div>
            </div>
        </Card>

        <template v-if="students.length === 0">
            <EmptyState message="Select a class to load students." icon="students" />
        </template>

        <template v-else>
            <div class="mb-4 flex items-center justify-between">
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ students.length }} student{{ students.length !== 1 ? 's' : '' }}</p>
                <SubmitButton :processing="form.processing" @click="save">Save Attendance</SubmitButton>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <Card v-for="student in students" :key="student.id">
                    <div class="mb-3">
                        <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ student.name }}</p>
                        <p class="text-xs text-slate-400">#{{ student.admission_no }}</p>
                    </div>
                    <div class="mb-3 flex flex-wrap gap-1.5">
                        <button
                            v-for="s in statusOptions"
                            :key="s"
                            type="button"
                            class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold capitalize ring-1 ring-inset transition-colors"
                            :class="(records[student.id]?.status ?? 'present') === s ? activeStatusColors[s] : statusColors[s]"
                            @click="setStatus(student.id, s)"
                        >
                            {{ s }}
                        </button>
                    </div>
                    <textarea
                        v-model="recordFor(student.id).remark"
                        rows="1"
                        placeholder="Remark (optional)"
                        class="w-full rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-xs text-slate-700 outline-none transition-colors placeholder:text-slate-400 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                    />
                </Card>
            </div>
        </template>
    </div>
</template>
