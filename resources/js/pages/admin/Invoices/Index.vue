<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { useToast } from '@/lib/toast';
import { formatDate, formatDateTime, formatMoney, todayIso } from '@/lib/helpers';
import AppLayout from '@/components/ui/AppLayout.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import Card from '@/components/ui/Card.vue';
import Badge from '@/components/ui/Badge.vue';
import Modal from '@/components/ui/Modal.vue';
import Pagination from '@/components/ui/Pagination.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import Field from '@/components/ui/Field.vue';
import TextInput from '@/components/ui/TextInput.vue';
import SelectInput from '@/components/ui/SelectInput.vue';
import SubmitButton from '@/components/ui/SubmitButton.vue';
import SearchInput from '@/components/ui/SearchInput.vue';
import Icon from '@/components/ui/Icon.vue';
import DataTable from '@/components/ui/DataTable.vue';
import InvoiceController from '@/actions/App/Http/Controllers/Admin/InvoiceController';

defineOptions({ layout: AppLayout });

type Paginator<T> = {
    data: T[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
};

type Invoice = {
    id: number;
    invoice_no: string;
    amount: string;
    paid_amount: string;
    status: string;
    due_date: string | null;
    created_at: string;
    session_id: number;
    student: { id: number; name: string; admission_no: string; class: { id: number; name: string } | null };
    fee_structure: { id: number; name: string };
};

const props = defineProps<{
    invoices: Paginator<Invoice>;
    structures: Array<{ id: number; name: string }>;
    sessions?: Array<{ id: number; name: string }>;
    filters: { search: string | null; status: string | null; structure_id: string | null; month: string | null };
}>();

const toast = useToast();

const structureOptions = props.structures.map((structure) => ({ value: structure.id, label: structure.name }));

const statusOptions = [
    { value: 'pending', label: 'Pending' },
    { value: 'partial', label: 'Partial' },
    { value: 'paid', label: 'Paid' },
    { value: 'cancelled', label: 'Cancelled' },
];

const methodOptions = [
    { value: 'cash', label: 'Cash' },
    { value: 'bank', label: 'Bank' },
    { value: 'card', label: 'Card' },
    { value: 'online', label: 'Online' },
    { value: 'cheque', label: 'Cheque' },
];

const sessionOptions = computed(() => {
    if (props.sessions && props.sessions.length > 0) {
        return props.sessions.map((session) => ({ value: session.id, label: session.name }));
    }

    const ids = [...new Set(props.invoices.data.map((invoice) => invoice.session_id).filter(Boolean))];

    return ids.map((id) => ({ value: id, label: `Session ${id}` }));
});

const generateOpen = ref(false);
const generateForm = useForm<{ fee_structure_id: number | null; session_id: number | null; due_date: string }>({
    fee_structure_id: null,
    session_id: null,
    due_date: '',
});

function openGenerate() {
    generateForm.clearErrors();
    generateForm.reset();
    if (sessionOptions.value.length === 1) {
        generateForm.session_id = Number(sessionOptions.value[0]?.value);
    }
    generateOpen.value = true;
}

function generate() {
    generateForm.post(InvoiceController.generate.url(), {
        preserveScroll: true,
        onSuccess: () => (generateOpen.value = false),
    });
}

const payOpen = ref(false);
const payingInvoice = ref<Invoice | null>(null);
const payForm = useForm<{
    amount: string | null;
    method: string;
    transaction_id: string;
    note: string;
    paid_at: string;
}>({
    amount: null,
    method: 'cash',
    transaction_id: '',
    note: '',
    paid_at: '',
});

function openPay(invoice: Invoice) {
    payingInvoice.value = invoice;
    payForm.clearErrors();
    payForm.reset();
    payForm.amount = balance(invoice).toString();
    payForm.paid_at = todayIso();
    payOpen.value = true;
}

function pay(invoice: Invoice) {
    payForm.post(InvoiceController.pay.url({ invoice: invoice.id }), {
        preserveScroll: true,
        onSuccess: () => (payOpen.value = false),
    });
}

function balance(invoice: Invoice): number {
    return Math.max(0, Number(invoice.amount) - Number(invoice.paid_amount));
}

function statusTone(status: string): 'green' | 'amber' | 'red' | 'slate' {
    if (status === 'paid') return 'green';
    if (status === 'partial') return 'amber';
    if (status === 'pending') return 'red';
    return 'slate';
}

async function destroy(invoice: Invoice) {
    const result = await toast.confirm({
        title: 'Delete invoice?',
        text: `Invoice ${invoice.invoice_no} for ${invoice.student?.name ?? 'this student'} will be removed.`,
        confirmText: 'Delete',
    });

    if (result.isConfirmed) {
        router.delete(InvoiceController.destroy.url({ invoice: invoice.id }), { preserveScroll: true });
    }
}

function go(field: string, value: string | number | null | undefined) {
    router.get(window.location.pathname, { [field]: value || undefined }, { preserveState: true, preserveScroll: true, replace: true });
}

const columns = [
    { key: 'invoice', label: 'Invoice' },
    { key: 'student', label: 'Student' },
    { key: 'structure', label: 'Structure', value: (i: any) => i.fee_structure?.name ?? null },
    { key: 'amount', label: 'Amount' },
    { key: 'balance', label: 'Balance' },
    { key: 'due_date', label: 'Due date', value: (i: any) => formatDate(i.due_date) },
    { key: 'status', label: 'Status' },
];
</script>

<template>
    <div>
        <Head title="Invoices" />
        <PageHeader title="Invoices" subtitle="View fee invoices, record payments, and generate new invoices.">
            <template #actions>
                <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700" @click="openGenerate">
                    <Icon name="plus" class="h-4 w-4" />
                    Generate invoices
                </button>
            </template>
        </PageHeader>

        <Card class="mb-4" :padded="false">
            <div class="grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-4">
                <SearchInput :value="filters.search ?? ''" placeholder="Search student or admission no..." />
                <SelectInput
                    :options="statusOptions"
                    :allow-empty="true"
                    placeholder="All statuses"
                    :model-value="filters.status ?? ''"
                    @update:model-value="go('status', $event ?? '')"
                />
                <SelectInput
                    :options="structureOptions"
                    :allow-empty="true"
                    placeholder="All structures"
                    :model-value="filters.structure_id ?? ''"
                    @update:model-value="go('structure_id', $event ?? '')"
                />
                <input
                    type="month"
                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm outline-none transition-colors focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                    :value="filters.month ?? ''"
                    @change="go('month', ($event.target as HTMLInputElement).value)"
                />
            </div>
        </Card>

        <Card :padded="false">
            <div v-if="invoices.data.length === 0">
                <EmptyState message="No invoices found." icon="invoices" />
            </div>
            <DataTable
                v-else
                :rows="invoices.data"
                :columns="columns"
                :row-key="(invoice) => invoice.id"
                :subtitle-value="(invoice) => invoice.student?.admission_no ?? null"
            >
                <template #cell-invoice="{ row }">
                    <div class="font-medium text-slate-900 dark:text-slate-100">{{ row.invoice_no }}</div>
                    <div class="text-xs text-slate-400">{{ formatDateTime(row.created_at) }}</div>
                </template>
                <template #cell-student="{ row }">
                    <div class="font-medium text-slate-700 dark:text-slate-200">{{ row.student?.name ?? '—' }}</div>
                    <div class="text-xs text-slate-400">
                        {{ row.student?.admission_no ?? '' }}<span v-if="row.student?.class?.name"> · {{ row.student.class.name }}</span>
                    </div>
                </template>
                <template #cell-amount="{ row }">
                    <div class="font-medium text-slate-700 dark:text-slate-200">{{ formatMoney(row.amount) }}</div>
                    <div class="text-xs text-slate-400">{{ formatMoney(row.paid_amount) }} paid</div>
                </template>
                <template #cell-balance="{ row }">
                    <span class="font-medium text-slate-900 dark:text-slate-100">{{ formatMoney(balance(row)) }}</span>
                </template>
                <template #cell-status="{ row }">
                    <Badge :label="row.status" :tone="statusTone(row.status)" />
                </template>
                <template #actions="{ row }">
                    <button class="rounded-lg px-2 py-1.5 text-xs font-semibold text-indigo-600 transition-colors hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-950/40" @click="openPay(row)">
                        Record payment
                    </button>
                    <button class="rounded-lg p-2 text-slate-400 transition-colors hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/40" @click="destroy(row)">
                        <Icon name="trash" class="h-4 w-4" />
                    </button>
                </template>
            </DataTable>
            <Pagination :paginator="invoices" />
        </Card>

        <Modal :open="generateOpen" title="Generate Invoices" @close="generateOpen = false">
            <form @submit.prevent="generate" class="space-y-4">
                <Field label="Fee structure" :error="generateForm.errors.fee_structure_id" required>
                    <SelectInput v-model="generateForm.fee_structure_id" :options="structureOptions" :allow-empty="true" placeholder="Select structure" />
                </Field>
                <Field label="Session" :error="generateForm.errors.session_id" required>
                    <SelectInput v-model="generateForm.session_id" :options="sessionOptions" :allow-empty="true" placeholder="Select session" />
                </Field>
                <Field label="Due date" :error="generateForm.errors.due_date">
                    <TextInput v-model="generateForm.due_date" type="date" />
                </Field>
                <p class="text-xs text-slate-400">Invoices will be created for every active student matching the selected structure.</p>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800" @click="generateOpen = false">
                        Cancel
                    </button>
                    <SubmitButton :processing="generateForm.processing">Generate</SubmitButton>
                </div>
            </form>
        </Modal>

        <Modal :open="payOpen" :title="`Record Payment — ${payingInvoice?.invoice_no ?? ''}`" @close="payOpen = false">
            <form v-if="payingInvoice" @submit.prevent="pay(payingInvoice)" class="space-y-4">
                <div class="rounded-lg bg-slate-50 px-4 py-3 text-sm dark:bg-slate-800/50">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Outstanding balance</span>
                        <span class="font-semibold text-slate-900 dark:text-slate-100">{{ formatMoney(balance(payingInvoice)) }}</span>
                    </div>
                </div>
                <Field label="Amount" :error="payForm.errors.amount" required>
                    <TextInput v-model="payForm.amount" type="number" min="0.01" step="0.01" />
                </Field>
                <div class="grid gap-4 sm:grid-cols-2">
                    <Field label="Method" :error="payForm.errors.method" required>
                        <SelectInput v-model="payForm.method" :options="methodOptions" />
                    </Field>
                    <Field label="Paid at" :error="payForm.errors.paid_at">
                        <TextInput v-model="payForm.paid_at" type="date" />
                    </Field>
                </div>
                <Field label="Transaction ID" :error="payForm.errors.transaction_id">
                    <TextInput v-model="payForm.transaction_id" placeholder="Reference/transaction ID" />
                </Field>
                <Field label="Note" :error="payForm.errors.note">
                    <TextInput v-model="payForm.note" placeholder="Optional note" />
                </Field>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800" @click="payOpen = false">
                        Cancel
                    </button>
                    <SubmitButton :processing="payForm.processing">Record Payment</SubmitButton>
                </div>
            </form>
        </Modal>
    </div>
</template>