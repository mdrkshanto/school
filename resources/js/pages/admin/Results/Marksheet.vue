<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/components/ui/AppLayout.vue';
import Card from '@/components/ui/Card.vue';
import Badge from '@/components/ui/Badge.vue';
import Icon from '@/components/ui/Icon.vue';
import { formatDate } from '@/lib/helpers';
import ResultController from '@/actions/App/Http/Controllers/Admin/ResultController';

type SheetRow = {
    subject: string;
    full_marks: number;
    pass_marks: number;
    obtained: number | null;
    grade: string | null;
    grade_point: number | null;
    is_absent: boolean;
};

const props = defineProps<{
    student: { id: number; name: string; admission_no: string; class: { id: number; name: string } | null; section: { id: number; name: string } | null };
    exam: { id: number; name: string; session: { id: number; name: string } | null };
    sheet: {
        rows: SheetRow[];
        total_marks: number;
        total_full: number;
        gpa: number | null;
        passed: boolean | null;
        grade: string | null;
    };
}>();

defineOptions({ layout: AppLayout });

const page = usePage<any>();
const schoolName = computed(() => String(page.props.name ?? ''));

const serial = computed(() => props.student.id);
</script>

<template>
    <div>
        <Head :title="`Marksheet - ${student.name ?? ''}`" />

        <div class="mb-6 flex flex-col items-center justify-between gap-3 sm:flex-row">
            <div>
                <h1 class="text-xl font-bold text-slate-900 dark:text-slate-50">Marksheet</h1>
                <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">Official academic record for {{ student.name }}</p>
            </div>
            <div class="flex items-center gap-2">
                <Link :href="ResultController.index.url()" class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-700 ring-1 ring-inset ring-slate-300 transition-colors hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-200 dark:ring-slate-700 dark:hover:bg-slate-700">
                    <Icon name="arrow-left" class="h-4 w-4" />
                    Back
                </Link>
                <Link
                    :href="ResultController.marksheetPdf.url({ student: student.id, exam: exam.id })"
                    method="post"
                    as="button"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700"
                >
                    <Icon name="download" class="h-4 w-4" />
                    Download PDF
                </Link>
            </div>
        </div>

        <Card>
            <div class="mb-6 text-center">
                <div class="mx-auto mb-2 flex h-14 w-14 items-center justify-center rounded-xl bg-indigo-600 text-2xl font-bold text-white">
                    {{ (schoolName ?? 'S').charAt(0).toUpperCase() }}
                </div>
                <h2 class="text-2xl font-bold text-slate-900 dark:text-slate-50">{{ schoolName }}</h2>
                <p class="mt-1 text-sm font-semibold uppercase tracking-widest text-slate-500">Academic Marksheet</p>
            </div>

            <div class="mx-auto mb-6 grid max-w-2xl gap-3 text-sm sm:grid-cols-2">
                <div class="rounded-lg bg-slate-50 px-4 py-3 dark:bg-slate-800/50">
                    <span class="block text-[11px] font-medium uppercase tracking-wider text-slate-400">Student</span>
                    <span class="font-medium text-slate-800 dark:text-slate-100">{{ student.name }} ({{ student.admission_no }})</span>
                </div>
                <div class="rounded-lg bg-slate-50 px-4 py-3 dark:bg-slate-800/50">
                    <span class="block text-[11px] font-medium uppercase tracking-wider text-slate-400">Class</span>
                    <span class="font-medium text-slate-800 dark:text-slate-100">
                        {{ student.class?.name ?? '—' }}
                        <span v-if="student.section">· Section {{ student.section.name }}</span>
                    </span>
                </div>
                <div class="rounded-lg bg-slate-50 px-4 py-3 dark:bg-slate-800/50">
                    <span class="block text-[11px] font-medium uppercase tracking-wider text-slate-400">Exam</span>
                    <span class="font-medium text-slate-800 dark:text-slate-100">{{ exam.name }}</span>
                </div>
                <div class="rounded-lg bg-slate-50 px-4 py-3 dark:bg-slate-800/50">
                    <span class="block text-[11px] font-medium uppercase tracking-wider text-slate-400">Session</span>
                    <span class="font-medium text-slate-800 dark:text-slate-100">{{ exam.session?.name ?? '—' }}</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-slate-200 text-[11px] uppercase tracking-wider text-slate-500 dark:border-slate-800 dark:text-slate-400">
                        <tr>
                            <th class="px-4 py-3 font-semibold">#</th>
                            <th class="px-4 py-3 font-semibold">Subject</th>
                            <th class="px-4 py-3 font-semibold">Full Marks</th>
                            <th class="px-4 py-3 font-semibold">Pass Marks</th>
                            <th class="px-4 py-3 font-semibold">Obtained</th>
                            <th class="px-4 py-3 font-semibold">Grade</th>
                            <th class="px-4 py-3 font-semibold">Point</th>
                            <th class="px-4 py-3 font-semibold">Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr v-for="(row, index) in sheet.rows" :key="row.subject + index">
                            <td class="px-4 py-3 text-slate-500">{{ index + 1 }}</td>
                            <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-200">{{ row.subject }}</td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ row.full_marks }}</td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ row.pass_marks }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-800 dark:text-slate-200">
                                <span v-if="row.is_absent" class="text-red-500">Absent</span>
                                <span v-else>{{ row.obtained ?? '—' }}</span>
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ row.grade ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ row.grade_point ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <Badge v-if="row.is_absent" label="Absent" tone="red" />
                                <Badge v-else-if="Number(row.obtained) < Number(row.pass_marks)" label="Fail" tone="red" />
                                <Badge v-else label="Pass" tone="green" />
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-800/50">
                            <td class="px-4 py-3" colspan="3">
                                <span class="font-semibold text-slate-800 dark:text-slate-100">Total</span>
                            </td>
                            <td class="px-4 py-3 text-slate-500">{{ sheet.total_full }}</td>
                            <td class="px-4 py-3">
                                <span class="font-bold text-slate-900 dark:text-slate-50">{{ sheet.total_marks }} / {{ sheet.total_full }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sm font-semibold text-indigo-600">{{ sheet.grade ?? '—' }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sm font-semibold text-indigo-600">{{ sheet.gpa ?? '—' }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <Badge :label="sheet.passed === true ? 'Passed' : sheet.passed === false ? 'Failed' : '—'" :tone="sheet.passed === true ? 'green' : sheet.passed === false ? 'red' : 'slate'" />
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="mt-8 grid grid-cols-2 gap-8 border-t border-dashed border-slate-200 pt-6 text-center text-xs text-slate-500 dark:border-slate-700 sm:grid-cols-3">
                <div>
                    <span class="block uppercase tracking-wider">Class Teacher</span>
                    <span class="mt-8 block border-t border-slate-300 dark:border-slate-600">Signature</span>
                </div>
                <div>
                    <span class="block uppercase tracking-wider">Result Controller</span>
                    <span class="mt-8 block border-t border-slate-300 dark:border-slate-600">Signature</span>
                </div>
                <div>
                    <span class="block uppercase tracking-wider">Principal / Head</span>
                    <span class="mt-8 block border-t border-slate-300 dark:border-slate-600">Signature</span>
                </div>
            </div>

            <div class="mt-6 text-center text-xs text-slate-400">
                Generated on {{ formatDate(new Date().toISOString()) }} · Sl. No. {{ serial }}
            </div>
        </Card>
    </div>
</template>