<script setup lang="ts">
import { Link, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppLayout from '@/components/ui/AppLayout.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import Card from '@/components/ui/Card.vue';
import SelectInput from '@/components/ui/SelectInput.vue';
import type { SelectOption } from '@/components/ui/SelectInput.vue';
import TextInput from '@/components/ui/TextInput.vue';
import SubmitButton from '@/components/ui/SubmitButton.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import Icon from '@/components/ui/Icon.vue';

defineOptions({ layout: AppLayout });

const props = defineProps<{
    assignments: Array<{ class_id: number; section_id: number; class: string; section: string | null }>;
    exams: Array<{ id: number; name: string }>;
    rows: Array<{ student: { id: number; name: string; admission_no: string }; subjects: Array<{ subject_id: number; name: string; full_marks: number; pass_marks: number; obtained_marks: number | null; is_absent: boolean; grade: string | null; grade_point: string | null }> }>;
    filters: { class_id?: string | number; section_id?: string | number; exam_id?: string | number };
    sessionName: string | null;
}>();

const classOptions = computed<SelectOption[]>(() =>
    props.assignments.map((a) => ({ value: a.class_id, label: `${a.class}${a.section ? ' - ' + a.section : ''}` })),
);

const examOptions = computed<SelectOption[]>(() =>
    props.exams.map((e) => ({ value: e.id, label: e.name })),
);

const selectedClassId = ref<string | number | null>(props.filters.class_id ?? null);
const selectedExamId = ref<string | number | null>(props.filters.exam_id ?? null);

function applyFilters() {
    const params: Record<string, string | number> = {};
    if (selectedClassId.value) params.class_id = selectedClassId.value;
    const assignment = props.assignments.find((a) => a.class_id == selectedClassId.value);
    if (assignment) params.section_id = assignment.section_id;
    if (selectedExamId.value) params.exam_id = selectedExamId.value;
    router.get('/teacher/results', params, { preserveState: true, preserveScroll: true });
}

const entries = ref<Record<string, { obtained_marks: string; is_absent: boolean }>>({});

function cellKey(studentId: number, subjectId: number) {
    return `${studentId}_${subjectId}`;
}

function initEntries() {
    const map: Record<string, { obtained_marks: string; is_absent: boolean }> = {};
    for (const row of props.rows) {
        for (const sub of row.subjects) {
            const key = cellKey(row.student.id, sub.subject_id);
            map[key] = {
                obtained_marks: sub.obtained_marks !== null ? String(sub.obtained_marks) : '',
                is_absent: sub.is_absent,
            };
        }
    }
    entries.value = map;
}

initEntries();

function entryFor(studentId: number, subjectId: number) {
    const key = cellKey(studentId, subjectId);
    if (!entries.value[key]) {
        entries.value[key] = { obtained_marks: '', is_absent: false };
    }
    return entries.value[key];
}

const form = useForm({
    exam_id: 0,
    class_id: 0,
    entries: [] as Array<{ student_id: number; subject_id: number; obtained_marks: number | null; is_absent: boolean }>,
});

function save() {
    const collected: Array<{ student_id: number; subject_id: number; obtained_marks: number | null; is_absent: boolean }> = [];
    for (const row of props.rows) {
        for (const sub of row.subjects) {
            const e = entries.value[cellKey(row.student.id, sub.subject_id)];
            if (!e) continue;
            collected.push({
                student_id: row.student.id,
                subject_id: sub.subject_id,
                obtained_marks: e.is_absent ? null : (e.obtained_marks !== '' ? Number(e.obtained_marks) : null),
                is_absent: e.is_absent,
            });
        }
    }
    form.exam_id = Number(selectedExamId.value);
    form.class_id = Number(selectedClassId.value);
    form.entries = collected;
    form.post('/teacher/results/save', { preserveScroll: true });
}
</script>

<template>
    <div>
        <Head title="Results" />
        <PageHeader title="Enter Results" :subtitle="sessionName" />

        <Card class="mb-6">
            <div class="flex flex-wrap items-end gap-4">
                <div class="w-full sm:w-64">
                    <label class="mb-1.5 block text-[13px] font-medium text-slate-700 dark:text-slate-300">Class / Section</label>
                    <SelectInput v-model="selectedClassId" :options="classOptions" placeholder="Select class" @change="applyFilters" />
                </div>
                <div class="w-full sm:w-64">
                    <label class="mb-1.5 block text-[13px] font-medium text-slate-700 dark:text-slate-300">Exam</label>
                    <SelectInput v-model="selectedExamId" :options="examOptions" placeholder="Select exam" :allow-empty="true" @change="applyFilters" />
                </div>
            </div>
        </Card>

        <template v-if="rows.length === 0">
            <EmptyState message="Select a class and exam to enter results." icon="results" />
        </template>

        <template v-else>
            <div class="mb-4 flex justify-end">
                <SubmitButton :processing="form.processing" @click="save">Save Results</SubmitButton>
            </div>

            <Card :padded="false">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800/50">
                                <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300">Student</th>
                                <th v-for="sub in rows[0]?.subjects ?? []" :key="sub.subject_id" class="px-4 py-3 text-center font-semibold text-slate-700 dark:text-slate-300">
                                    <div>{{ sub.name }}</div>
                                    <div class="text-[10px] font-normal text-slate-400">{{ sub.full_marks }} | {{ sub.pass_marks }}P</div>
                                </th>
                                <th class="px-4 py-3 text-center font-semibold text-slate-700 dark:text-slate-300">Link</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in rows" :key="row.student.id" class="border-b border-slate-100 last:border-0 dark:border-slate-800">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-slate-800 dark:text-slate-200">{{ row.student.name }}</p>
                                    <p class="text-[11px] text-slate-400">#{{ row.student.admission_no }}</p>
                                </td>
                                <td v-for="sub in row.subjects" :key="sub.subject_id" class="px-4 py-3">
                                    <div class="flex items-center gap-1.5">
                                        <input
                                            v-model="entryFor(row.student.id, sub.subject_id).obtained_marks"
                                            type="number"
                                            :disabled="entryFor(row.student.id, sub.subject_id).is_absent"
                                            :min="0"
                                            :max="sub.full_marks"
                                            step="0.5"
                                            class="w-20 rounded-lg border border-slate-200 bg-white px-2 py-1 text-center text-sm text-slate-800 outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/20 disabled:opacity-40 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                                        />
                                        <label class="flex items-center gap-1 text-[10px] text-slate-400">
                                            <input
                                                type="checkbox"
                                                v-model="entryFor(row.student.id, sub.subject_id).is_absent"
                                                class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                            />
                                            AB
                                        </label>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <Link
                                        :href="`/teacher/results/marksheet/${row.student.id}/${filters.exam_id}`"
                                        class="inline-flex items-center gap-1 text-xs font-medium text-indigo-600 hover:text-indigo-800 dark:text-indigo-400"
                                    >
                                        <Icon name="results" class="h-3.5 w-3.5" />
                                        Marks
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </Card>
        </template>
    </div>
</template>
