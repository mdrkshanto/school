<script setup lang="ts">
import { reactive, ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/components/ui/AppLayout.vue';
import Card from '@/components/ui/Card.vue';
import SubmitButton from '@/components/ui/SubmitButton.vue';
import Icon from '@/components/ui/Icon.vue';
import ResultController from '@/actions/App/Http/Controllers/Admin/ResultController';

type Schedule = {
    id: number;
    subject_id: number;
    name: string;
    full_marks: number;
    pass_marks: number;
};

type EntryRow = {
    student: { id: number; name: string; admission_no: string };
    subjects: Array<{
        subject_id: number;
        name: string;
        full_marks: number;
        pass_marks: number;
        obtained_marks: number | null;
        is_absent: boolean;
        grade: string | null;
        grade_point: number | null;
    }>;
};

const props = defineProps<{
    exam: { id: number; name: string; session: { id: number; name: string } | null };
    class: { id: number; name: string } | null;
    schedules: Schedule[];
    rows: EntryRow[];
    activeSession: string | null;
}>();

defineOptions({ layout: AppLayout });

const query = new URLSearchParams(window.location.search);
const sectionId = ref(query.get('section_id'));

type Cell = {
    student_id: number;
    subject_id: number;
    obtained_marks: number | null;
    is_absent: boolean;
};

const entries = reactive<Record<string, Cell>>({});
const gradeMap = reactive<Record<string, string | null>>({});
const pointMap = reactive<Record<string, number | null>>({});

props.rows.forEach((row) => {
    row.subjects.forEach((subject) => {
        const key = `${row.student.id}:${subject.subject_id}`;
        entries[key] = {
            student_id: row.student.id,
            subject_id: subject.subject_id,
            obtained_marks: subject.obtained_marks ?? null,
            is_absent: subject.is_absent,
        };
        gradeMap[key] = subject.grade;
        pointMap[key] = subject.grade_point;
    });
});

function entryFor(studentId: number, subjectId: number): Cell {
    const key = `${studentId}:${subjectId}`;
    if (!entries[key]) {
        entries[key] = { student_id: studentId, subject_id: subjectId, obtained_marks: null, is_absent: false };
    }

    return entries[key];
}

function gradeFor(studentId: number, subjectId: number): string | null {
    return gradeMap[`${studentId}:${subjectId}`] ?? null;
}

function pointFor(studentId: number, subjectId: number): number | null {
    return pointMap[`${studentId}:${subjectId}`] ?? null;
}

const form = useForm<{
    exam_id: number | null;
    class_id: number | null;
    section_id: string | null;
    subject_id: number | null;
    rows: Array<{ student_id: number; obtained_marks: number | null; is_absent: boolean }>;
    entries: Array<Cell & { exam_id: number | null; class_id: number | null; section_id: string | null }>;
}>({
    exam_id: null,
    class_id: null,
    section_id: null,
    subject_id: null,
    rows: [],
    entries: [],
});

function submit() {
    form.clearErrors();
    form.exam_id = props.exam.id;
    form.class_id = props.class?.id ?? (Number(query.get('class_id')) || null);
    form.section_id = sectionId.value;
    form.subject_id = props.schedules[0]?.subject_id ?? null;
    form.rows = props.rows.flatMap((row) => {
        const subject = row.subjects[0];
        if (!subject) return [];

        const cell = entryFor(row.student.id, subject.subject_id);

        return [{ student_id: row.student.id, obtained_marks: cell.obtained_marks, is_absent: cell.is_absent }];
    });
    form.entries = Object.values(entries).map((entry) => ({
        ...entry,
        exam_id: form.exam_id,
        class_id: form.class_id,
        section_id: form.section_id,
    }));
    form.post(ResultController.saveEntry.url(), { preserveScroll: true });
}
</script>

<template>
    <div>
        <Head :title="`Score Entry - ${exam.name ?? ''}`" />

        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-bold text-slate-900 dark:text-slate-50">Score Entry</h1>
                <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">
                    {{ exam.name }}
                    <span v-if="exam.session">· {{ exam.session.name }}</span>
                    <span v-if="props.class">· {{ props.class.name }}</span>
                    <span v-if="activeSession">· Session: {{ activeSession }}</span>
                </p>
            </div>
            <div class="flex items-center gap-2">
                <Link :href="ResultController.index.url()" class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-700 ring-1 ring-inset ring-slate-300 transition-colors hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-200 dark:ring-slate-700 dark:hover:bg-slate-700">
                    <Icon name="arrow-left" class="h-4 w-4" />
                    Back
                </Link>
                <SubmitButton :processing="form.processing" @click="submit">Save All</SubmitButton>
            </div>
        </div>

        <Card :padded="false" class="overflow-x-auto">
            <table class="w-full min-w-max border-collapse text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400">
                    <tr>
                        <th class="sticky left-0 z-10 bg-slate-50 px-5 py-3 font-semibold dark:bg-slate-900">Student</th>
                        <th v-for="schedule in schedules" :key="schedule.id" class="px-3 py-3">
                            <div class="font-semibold normal-case">{{ schedule.name }}</div>
                            <div class="mt-0.5 text-[10px] font-normal normal-case text-slate-400">FM {{ schedule.full_marks }} · PM {{ schedule.pass_marks }}</div>
                        </th>
                        <th class="px-5 py-3 text-right font-semibold">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr v-for="row in rows" :key="row.student.id">
                        <td class="sticky left-0 z-10 bg-white px-5 py-3 dark:bg-slate-900">
                            <div class="font-medium text-slate-900 dark:text-slate-100">{{ row.student.name }}</div>
                            <div class="text-xs text-slate-400">Adm. {{ row.student.admission_no }}</div>
                        </td>
                        <td v-for="subject in row.subjects" :key="subject.subject_id" class="px-3 py-3 align-top">
                            <div class="flex flex-col gap-1.5">
                                <input
                                    v-model.number="entryFor(row.student.id, subject.subject_id).obtained_marks"
                                    type="number"
                                    min="0"
                                    :max="subject.full_marks"
                                    :disabled="entryFor(row.student.id, subject.subject_id).is_absent"
                                    class="w-28 rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-sm text-slate-800 shadow-sm outline-none transition-colors placeholder:text-slate-400 disabled:bg-slate-50 disabled:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                                />
                                <label class="flex items-center gap-1.5 text-[11px] font-medium text-slate-500">
                                    <input
                                        v-model="entryFor(row.student.id, subject.subject_id).is_absent"
                                        type="checkbox"
                                        class="h-3.5 w-3.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                    />
                                    Absent
                                </label>
                                <span v-if="gradeFor(row.student.id, subject.subject_id)" class="text-[11px] text-slate-400">
                                    {{ gradeFor(row.student.id, subject.subject_id) }} · {{ pointFor(row.student.id, subject.subject_id) }}
                                </span>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-right align-top">
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                                {{ row.subjects.reduce((sum, subject) => {
                                    const cell = entryFor(row.student.id, subject.subject_id);
                                    return sum + (cell.is_absent ? 0 : Number(cell.obtained_marks ?? 0));
                                }, 0) }}
                            </span>
                            <div class="text-[11px] text-slate-400">/ {{ row.subjects.reduce((sum, subject) => sum + subject.full_marks, 0) }}</div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </Card>
    </div>
</template>