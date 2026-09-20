<script setup lang="ts">
import AppLayout from '@/components/ui/AppLayout.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import Card from '@/components/ui/Card.vue';
import Badge from '@/components/ui/Badge.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import DataTable from '@/components/ui/DataTable.vue';
import { formatMoney, formatDate } from '@/lib/helpers';

defineOptions({ layout: AppLayout });

const props = defineProps<{
    invoices: Array<{ id: number; invoice_no: string; structure: string | null; session: string | null; amount: number; paid_amount: number; balance: number; due_date: string; status: string }>;
    payments: Array<{ id: number; invoice_no: string | null; amount: number; method: string; paid_at: string; transaction_id: string | null }>;
    total_balance: number;
}>();

function statusTone(status: string): 'green' | 'amber' | 'red' {
    if (status === 'paid') return 'green';
    if (status === 'partial') return 'amber';
    return 'red';
}

const invoiceColumns = [
    { key: 'invoice', label: 'Invoice' },
    { key: 'structure', label: 'Structure', value: (i: any) => i.structure ?? null },
    { key: 'session', label: 'Session', value: (i: any) => i.session ?? null },
    { key: 'amount', label: 'Amount', align: 'right' as const, value: (i: any) => formatMoney(i.amount) },
    { key: 'paid', label: 'Paid', align: 'right' as const, value: (i: any) => formatMoney(i.paid_amount) },
    { key: 'balance', label: 'Balance', align: 'right' as const, value: (i: any) => formatMoney(i.balance) },
    { key: 'due', label: 'Due', value: (i: any) => formatDate(i.due_date) },
    { key: 'status', label: 'Status' },
];

const paymentColumns = [
    { key: 'invoice', label: 'Invoice', value: (p: any) => p.invoice_no ?? null },
    { key: 'amount', label: 'Amount', align: 'right' as const, value: (p: any) => formatMoney(p.amount) },
    { key: 'method', label: 'Method' },
    { key: 'date', label: 'Date', value: (p: any) => formatDate(p.paid_at) },
    { key: 'transaction_id', label: 'Transaction ID', value: (p: any) => p.transaction_id ?? null },
];
</script>

<template>
    <div>
        <Head title="Fees" />
        <PageHeader title="Fee Details" />

        <Card class="mb-6">
            <div class="flex items-center gap-4">
                <div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Total Outstanding</p>
                    <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ formatMoney(total_balance) }}</p>
                </div>
            </div>
        </Card>

        <Card class="mb-6">
            <h3 class="mb-3 text-sm font-semibold text-slate-900 dark:text-slate-100">Invoices</h3>
            <template v-if="invoices.length === 0">
                <EmptyState message="No invoices found." icon="invoices" />
            </template>
            <template v-else>
                <DataTable
                    :rows="invoices"
                    :columns="invoiceColumns"
                    :row-key="(inv) => inv.id"
                    :subtitle-value="(inv) => inv.structure ?? null"
                >
                    <template #cell-status="{ row }">
                        <Badge :label="row.status" :tone="statusTone(row.status)" />
                    </template>
                </DataTable>
            </template>
        </Card>

        <Card>
            <h3 class="mb-3 text-sm font-semibold text-slate-900 dark:text-slate-100">Payment History</h3>
            <template v-if="payments.length === 0">
                <EmptyState message="No payments recorded." icon="money" />
            </template>
            <template v-else>
                <DataTable
                    :rows="payments"
                    :columns="paymentColumns"
                    :row-key="(payment) => payment.id"
                    :title-value="(payment) => payment.invoice_no ?? '—'"
                >
                    <template #cell-method="{ row }">
                        <span class="capitalize">{{ row.method }}</span>
                    </template>
                </DataTable>
            </template>
        </Card>
    </div>
</template>
