<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/components/ui/AppLayout.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import Card from '@/components/ui/Card.vue';
import Badge from '@/components/ui/Badge.vue';
import Icon from '@/components/ui/Icon.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import { formatDate, formatMoney } from '@/lib/helpers';

defineOptions({ layout: AppLayout });

defineProps<{
    student: { id: number; name: string; admission_no: string; class: { id: number; name: string }; section: { id: number; name: string }; session: { id: number; name: string } };
    attendance: Array<{ id: number; date: string; status: string; remark: string | null }>;
    attendance_rate: number | null;
    invoices: Array<{ id: number; invoice_no: string; structure: string | null; amount: number; paid_amount: number; balance: number; status: string }>;
    fee_balance: number;
    exams: Array<{ id: number; name: string; available: boolean; marksheet: { rows: Array<{ subject: string; full_marks: number; pass_marks: number; obtained: number | null; grade: string | null; grade_point: string | null; is_absent: boolean }>; total_marks: number; total_full: number; gpa: number; passed: boolean; grade: string } | null }>;
    transport: { route: string; stop: string } | null;
}>();

const activeTab = ref<'attendance' | 'fees' | 'results' | 'transport'>('attendance');

function statusTone(status: string): 'green' | 'red' | 'amber' | 'sky' {
    if (status === 'present') return 'green';
    if (status === 'absent') return 'red';
    if (status === 'late') return 'amber';
    return 'sky';
}

function invoiceStatusTone(status: string): 'green' | 'amber' | 'red' {
    if (status === 'paid') return 'green';
    if (status === 'partial') return 'amber';
    return 'red';
}

const expandedExam = ref<number | null>(null);

function toggleExam(id: number) {
    expandedExam.value = expandedExam.value === id ? null : id;
}
</script>

<template>
    <div>
        <Head :title="`${student.name} — Details`" />
        <PageHeader :title="student.name" :subtitle="`${student.class.name} - ${student.section.name} · ${student.session.name}`">
            <template #actions>
                <Link href="/parent/dashboard" class="inline-flex items-center gap-1.5 rounded-lg bg-white px-3 py-2 text-sm font-medium text-slate-700 ring-1 ring-inset ring-slate-300 transition-colors hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-200 dark:ring-slate-700">
                    Back
                </Link>
            </template>
        </PageHeader>

        <div class="mb-4 flex gap-1 border-b border-slate-200 dark:border-slate-800">
            <button
                v-for="tab in (['attendance', 'fees', 'results', 'transport'] as const)"
                :key="tab"
                type="button"
                class="border-b-2 px-4 py-2.5 text-sm font-medium capitalize transition-colors"
                :class="activeTab === tab ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400'"
                @click="activeTab = tab"
            >
                {{ tab }}
            </button>
        </div>

        <div v-if="activeTab === 'attendance'" class="space-y-4">
            <div class="flex items-center gap-4">
                <Badge v-if="attendance_rate !== null" :label="`${attendance_rate}% attendance`" tone="indigo" />
            </div>
            <template v-if="attendance.length === 0">
                <EmptyState message="No attendance records." icon="attendance" />
            </template>
            <template v-else>
                <Card :padded="false">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead>
                                <tr class="border-b border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800/50">
                                    <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300">Date</th>
                                    <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300">Status</th>
                                    <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300">Remark</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="record in attendance" :key="record.id" class="border-b border-slate-100 last:border-0 dark:border-slate-800">
                                    <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-200">{{ formatDate(record.date) }}</td>
                                    <td class="px-4 py-3">
                                        <Badge :label="record.status" :tone="statusTone(record.status)" />
                                    </td>
                                    <td class="px-4 py-3 text-slate-500 dark:text-slate-400">{{ record.remark ?? '—' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </Card>
            </template>
        </div>

        <div v-if="activeTab === 'fees'" class="space-y-4">
            <Card>
                <p class="text-sm text-slate-500 dark:text-slate-400">Outstanding Balance</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ formatMoney(fee_balance) }}</p>
            </Card>
            <Card :padded="false">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800/50">
                                <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300">Invoice</th>
                                <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300">Structure</th>
                                <th class="px-4 py-3 text-right font-semibold text-slate-700 dark:text-slate-300">Amount</th>
                                <th class="px-4 py-3 text-right font-semibold text-slate-700 dark:text-slate-300">Paid</th>
                                <th class="px-4 py-3 text-right font-semibold text-slate-700 dark:text-slate-300">Balance</th>
                                <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="inv in invoices" :key="inv.id" class="border-b border-slate-100 last:border-0 dark:border-slate-800">
                                <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-200">{{ inv.invoice_no }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ inv.structure ?? '—' }}</td>
                                <td class="px-4 py-3 text-right text-slate-700 dark:text-slate-300">{{ formatMoney(inv.amount) }}</td>
                                <td class="px-4 py-3 text-right text-slate-700 dark:text-slate-300">{{ formatMoney(inv.paid_amount) }}</td>
                                <td class="px-4 py-3 text-right font-medium text-slate-800 dark:text-slate-200">{{ formatMoney(inv.balance) }}</td>
                                <td class="px-4 py-3">
                                    <Badge :label="inv.status" :tone="invoiceStatusTone(inv.status)" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </Card>
        </div>

        <div v-if="activeTab === 'results'" class="space-y-4">
            <template v-if="exams.length === 0">
                <EmptyState message="No exam results available." icon="results" />
            </template>
            <template v-else>
                <Card v-for="exam in exams" :key="exam.id" :padded="false">
                    <button type="button" class="flex w-full items-center justify-between p-4 text-left" @click="exam.available && toggleExam(exam.id)">
                        <div>
                            <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ exam.name }}</p>
                            <Badge v-if="exam.marksheet" :label="exam.marksheet.passed ? 'Pass' : 'Fail'" :tone="exam.marksheet.passed ? 'green' : 'red'" />
                            <Badge v-else-if="!exam.available" label="No results" tone="slate" />
                        </div>
                        <Icon
                            :name="expandedExam === exam.id ? 'chevron-up' : 'chevron-down'"
                            class="h-5 w-5 text-slate-400 transition-transform"
                        />
                    </button>
                    <div v-if="expandedExam === exam.id && exam.marksheet" class="border-t border-slate-100 px-4 py-4 dark:border-slate-800">
                        <div class="mb-3 flex items-center gap-3">
                            <p class="text-sm text-slate-500 dark:text-slate-400">GPA: <span class="font-bold text-slate-900 dark:text-slate-100">{{ exam.marksheet.gpa }}</span></p>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Grade: <span class="font-bold text-slate-900 dark:text-slate-100">{{ exam.marksheet.grade }}</span></p>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Total: <span class="font-bold text-slate-900 dark:text-slate-100">{{ exam.marksheet.total_marks }} / {{ exam.marksheet.total_full }}</span></p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-xs">
                                <thead>
                                    <tr class="border-b border-slate-200 dark:border-slate-700">
                                        <th class="pb-2 font-semibold text-slate-600 dark:text-slate-400">Subject</th>
                                        <th class="pb-2 text-center font-semibold text-slate-600 dark:text-slate-400">Full</th>
                                        <th class="pb-2 text-center font-semibold text-slate-600 dark:text-slate-400">Obtained</th>
                                        <th class="pb-2 text-center font-semibold text-slate-600 dark:text-slate-400">Grade</th>
                                        <th class="pb-2 text-center font-semibold text-slate-600 dark:text-slate-400">GP</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(row, i) in exam.marksheet.rows" :key="i" class="border-b border-slate-100 last:border-0 dark:border-slate-800">
                                        <td class="py-2 text-slate-700 dark:text-slate-300">{{ row.subject }}</td>
                                        <td class="py-2 text-center text-slate-600 dark:text-slate-400">{{ row.full_marks }}</td>
                                        <td class="py-2 text-center font-medium text-slate-800 dark:text-slate-200">
                                            <span v-if="row.is_absent" class="text-red-500">AB</span>
                                            <span v-else>{{ row.obtained ?? '—' }}</span>
                                        </td>
                                        <td class="py-2 text-center text-slate-600 dark:text-slate-400">{{ row.grade ?? '—' }}</td>
                                        <td class="py-2 text-center text-slate-600 dark:text-slate-400">{{ row.grade_point ?? '—' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </Card>
            </template>
        </div>

        <div v-if="activeTab === 'transport'">
            <template v-if="transport">
                <Card>
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-sky-50 text-sky-600 dark:bg-sky-950/50 dark:text-sky-400">
                            <Icon name="bus" class="h-5 w-5" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-800 dark:text-slate-200">{{ transport.route }}</p>
                            <p class="text-xs text-slate-500">Stop: {{ transport.stop }}</p>
                        </div>
                    </div>
                </Card>
            </template>
            <template v-else>
                <EmptyState message="No transport information available." icon="bus" />
            </template>
        </div>
    </div>
</template>
