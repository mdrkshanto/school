<script setup lang="ts">
import { computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/components/ui/AppLayout.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import Card from '@/components/ui/Card.vue';
import Badge from '@/components/ui/Badge.vue';
import Icon from '@/components/ui/Icon.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import DataTable from '@/components/ui/DataTable.vue';
import { formatDate, formatMoney, titleCase } from '@/lib/helpers';

defineOptions({ layout: AppLayout });

type StatusRow = {
    status: string;
    total: number;
    amount: number;
    paid: number;
};

type RecentInvoice = {
    id: number;
    invoice_no: string;
    amount: string;
    paid_amount: string;
    status: string;
    created_at: string;
    student: { id: number; name: string; admission_no: string };
    fee_structure: { id: number; name: string };
};

const props = defineProps<{
    summary: { collected: number; pending: number };
    byStatus: StatusRow[];
    recent: RecentInvoice[];
    month: string;
}>();

const statusTones: Record<string, 'green' | 'amber' | 'red' | 'slate'> = {
    paid: 'green',
    partial: 'amber',
    pending: 'red',
    cancelled: 'slate',
};

const totalInvoices = computed(() => props.byStatus.reduce((sum, row) => sum + row.total, 0));
const maxTotal = computed(() => Math.max(1, ...props.byStatus.map((row) => row.total)));
const totalAmount = computed(() => props.byStatus.reduce((sum, row) => sum + Number(row.amount), 0));

function changeMonth(event: Event) {
    const value = (event.target as HTMLInputElement).value;
    router.get(window.location.pathname, { month: value || undefined }, { preserveState: true, preserveScroll: true, replace: true });
}

const columns = [
    { key: 'invoice', label: 'Invoice', hideOnMobile: true },
    { key: 'student', label: 'Student', hideOnMobile: true },
    { key: 'structure', label: 'Structure', value: (i: any) => i.fee_structure?.name ?? null },
    { key: 'amount', label: 'Amount', value: (i: any) => formatMoney(i.amount) },
    { key: 'paid', label: 'Paid', value: (i: any) => formatMoney(i.paid_amount) },
    { key: 'status', label: 'Status' },
];
</script>

<template>
    <div>
        <Head title="Fees Report" />
        <PageHeader title="Fees Report" subtitle="Collections, pending amounts, and invoice status distribution.">
            <template #actions>
                <input
                    type="month"
                    class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm outline-none transition-colors focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                    :value="month"
                    @change="changeMonth"
                />
            </template>
        </PageHeader>

        <div class="mb-6 grid gap-4 sm:grid-cols-3">
            <Card>
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400">
                        <Icon name="money" class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-slate-900 dark:text-slate-50">{{ formatMoney(summary.collected) }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Collected ({{ month }})</p>
                    </div>
                </div>
            </Card>
            <Card>
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400">
                        <Icon name="receipt" class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-slate-900 dark:text-slate-50">{{ formatMoney(summary.pending) }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Outstanding Balance</p>
                    </div>
                </div>
            </Card>
            <Card>
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400">
                        <Icon name="invoices" class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-slate-900 dark:text-slate-50">{{ totalInvoices }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Total billed · {{ formatMoney(totalAmount) }}</p>
                    </div>
                </div>
            </Card>
        </div>

        <div class="mb-6 grid gap-6 lg:grid-cols-2">
            <Card>
                <h3 class="mb-4 text-base font-semibold text-slate-900 dark:text-slate-100">Status Distribution</h3>
                <div v-if="byStatus.length === 0" class="py-6 text-center text-sm text-slate-400">No invoices recorded.</div>
                <div v-else class="space-y-4">
                    <div v-for="row in byStatus" :key="row.status">
                        <div class="mb-1.5 flex items-center justify-between text-sm">
                            <div class="flex items-center gap-2">
                                <Badge :label="row.status" :tone="statusTones[row.status] ?? 'slate'" />
                                <span class="text-slate-500 dark:text-slate-400">{{ row.total }} invoice{{ row.total !== 1 ? 's' : '' }}</span>
                            </div>
                            <div class="text-right">
                                <div class="font-medium text-slate-800 dark:text-slate-200">{{ formatMoney(row.amount) }}</div>
                                <div class="text-xs text-slate-400">{{ formatMoney(row.paid) }} paid</div>
                            </div>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                            <div
                                class="h-full rounded-full transition-all"
                                :class="row.status === 'paid' ? 'bg-emerald-500' : row.status === 'partial' ? 'bg-amber-500' : row.status === 'pending' ? 'bg-red-400' : 'bg-slate-400'"
                                :style="{ width: `${(row.total / maxTotal) * 100}%` }"
                            />
                        </div>
                    </div>
                </div>
            </Card>

            <Card>
                <h3 class="mb-4 text-base font-semibold text-slate-900 dark:text-slate-100">Collection Breakdown</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2 text-sm dark:border-slate-800">
                        <span class="text-slate-500 dark:text-slate-400">{{ titleCase(month) }}</span>
                        <span class="font-medium text-slate-800 dark:text-slate-200">{{ formatMoney(summary.collected) }}</span>
                    </div>
                    <div v-for="row in byStatus" :key="`paid-${row.status}`" class="flex items-center justify-between text-sm">
                        <span class="text-slate-600 dark:text-slate-300">{{ titleCase(row.status) }} collection</span>
                        <span class="font-medium text-slate-800 dark:text-slate-200">{{ formatMoney(row.paid) }}</span>
                    </div>
                </div>
            </Card>
        </div>

        <Card :padded="false">
            <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">Recent Invoices</h3>
            </div>
            <div v-if="recent.length === 0">
                <EmptyState message="No recent invoices." icon="invoices" />
            </div>
            <DataTable
                v-else
                :rows="recent"
                :columns="columns"
                :row-key="(invoice) => invoice.id"
                :title-value="(invoice) => invoice.invoice_no"
                :subtitle-value="(invoice) => invoice.student?.name ?? null"
            >
                <template #cell-invoice="{ row }">
                    <div class="font-medium text-slate-900 dark:text-slate-100">{{ row.invoice_no }}</div>
                    <div class="text-xs text-slate-400">{{ formatDate(row.created_at) }}</div>
                </template>
                <template #cell-student="{ row }">
                    <div class="font-medium text-slate-700 dark:text-slate-200">{{ row.student?.name ?? '—' }}</div>
                    <div class="text-xs text-slate-400">{{ row.student?.admission_no ?? '' }}</div>
                </template>
                <template #cell-status="{ row }">
                    <Badge :label="row.status" :tone="statusTones[row.status] ?? 'slate'" />
                </template>
            </DataTable>
        </Card>
    </div>
</template>