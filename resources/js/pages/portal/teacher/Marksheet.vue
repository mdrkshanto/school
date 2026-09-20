<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/components/ui/AppLayout.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import Card from '@/components/ui/Card.vue';
import Badge from '@/components/ui/Badge.vue';

defineOptions({ layout: AppLayout });

defineProps<{
    student: { id: number; name: string; admission_no: string; class: { id: number; name: string }; section: { id: number; name: string } };
    exam: { id: number; name: string; session: { id: number; name: string } };
    sheet: {
        rows: Array<{ subject: string; full_marks: number; pass_marks: number; obtained: number | null; grade: string | null; grade_point: string | null; is_absent: boolean }>;
        total_marks: number;
        total_full: number;
        gpa: number;
        passed: boolean;
        grade: string;
    };
}>();
</script>

<template>
    <div>
        <Head :title="`Marksheet - ${student.name}`" />
        <PageHeader :title="`${student.name} — Marksheet`" :subtitle="`${exam.session.name} / ${exam.name}`">
            <template #actions>
                <Link href="/teacher/results" class="inline-flex items-center gap-1.5 rounded-lg bg-white px-3 py-2 text-sm font-medium text-slate-700 ring-1 ring-inset ring-slate-300 transition-colors hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-200 dark:ring-slate-700">
                    Back
                </Link>
            </template>
        </PageHeader>

        <Card>
            <div class="mb-4 flex flex-wrap items-center gap-4">
                <div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Admission No.</p>
                    <p class="font-medium text-slate-800 dark:text-slate-200">#{{ student.admission_no }}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Class / Section</p>
                    <p class="font-medium text-slate-800 dark:text-slate-200">{{ student.class.name }} - {{ student.section.name }}</p>
                </div>
                <div class="ml-auto text-right">
                    <p class="text-sm text-slate-500 dark:text-slate-400">GPA</p>
                    <p class="text-lg font-bold text-slate-900 dark:text-slate-100">{{ sheet.gpa }}</p>
                </div>
                <Badge :label="sheet.passed ? 'Pass' : 'Fail'" :tone="sheet.passed ? 'green' : 'red'" />
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800/50">
                            <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300">Subject</th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-700 dark:text-slate-300">Full Marks</th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-700 dark:text-slate-300">Pass Marks</th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-700 dark:text-slate-300">Obtained</th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-700 dark:text-slate-300">Grade</th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-700 dark:text-slate-300">GP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(row, i) in sheet.rows" :key="i" class="border-b border-slate-100 last:border-0 dark:border-slate-800">
                            <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-200">{{ row.subject }}</td>
                            <td class="px-4 py-3 text-center text-slate-600 dark:text-slate-400">{{ row.full_marks }}</td>
                            <td class="px-4 py-3 text-center text-slate-600 dark:text-slate-400">{{ row.pass_marks }}</td>
                            <td class="px-4 py-3 text-center font-medium text-slate-800 dark:text-slate-200">
                                <span v-if="row.is_absent" class="text-red-500">AB</span>
                                <span v-else>{{ row.obtained ?? '—' }}</span>
                            </td>
                            <td class="px-4 py-3 text-center text-slate-600 dark:text-slate-400">{{ row.grade ?? '—' }}</td>
                            <td class="px-4 py-3 text-center text-slate-600 dark:text-slate-400">{{ row.grade_point ?? '—' }}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800/50">
                            <td class="px-4 py-3 font-bold text-slate-900 dark:text-slate-100">Total</td>
                            <td class="px-4 py-3 text-center font-bold text-slate-900 dark:text-slate-100">{{ sheet.total_full }}</td>
                            <td></td>
                            <td class="px-4 py-3 text-center font-bold text-slate-900 dark:text-slate-100">{{ sheet.total_marks }}</td>
                            <td class="px-4 py-3 text-center font-bold text-slate-900 dark:text-slate-100">{{ sheet.grade }}</td>
                            <td class="px-4 py-3 text-center font-bold text-slate-900 dark:text-slate-100">{{ sheet.gpa }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </Card>
    </div>
</template>
