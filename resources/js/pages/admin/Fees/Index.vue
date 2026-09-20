<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { useToast } from '@/lib/toast';
import { formatMoney, titleCase } from '@/lib/helpers';
import AppLayout from '@/components/ui/AppLayout.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import Card from '@/components/ui/Card.vue';
import Badge from '@/components/ui/Badge.vue';
import Modal from '@/components/ui/Modal.vue';
import Pagination from '@/components/ui/Pagination.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import Field from '@/components/ui/Field.vue';
import TextInput from '@/components/ui/TextInput.vue';
import TextareaInput from '@/components/ui/TextareaInput.vue';
import SelectInput from '@/components/ui/SelectInput.vue';
import SubmitButton from '@/components/ui/SubmitButton.vue';
import SearchInput from '@/components/ui/SearchInput.vue';
import Icon from '@/components/ui/Icon.vue';
import DataTable from '@/components/ui/DataTable.vue';
import FeeStructureController from '@/actions/App/Http/Controllers/Admin/FeeStructureController';

defineOptions({ layout: AppLayout });

type Paginator<T> = {
    data: T[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
};

type FeeStructure = {
    id: number;
    name: string;
    type: string;
    amount: string;
    is_optional: boolean;
    description: string | null;
    status: string;
    session_id: number;
    class_id: number | null;
    session: { id: number; name: string };
    class: { id: number; name: string } | null;
};

type Option = { id: number; name: string };

const props = defineProps<{
    structures: Paginator<FeeStructure>;
    sessions: Option[];
    classes: Option[];
    filters: { search: string | null; session_id: string | null };
}>();

const toast = useToast();

const sessionOptions = props.sessions.map((session) => ({ value: session.id, label: session.name }));
const classOptions = props.classes.map((item) => ({ value: item.id, label: item.name }));

const typeOptions = [
    { value: 'admission', label: 'Admission' },
    { value: 'monthly', label: 'Monthly' },
    { value: 'one_time', label: 'One Time' },
    { value: 'transport', label: 'Transport' },
];

const statusOptions = [
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' },
];

const modalOpen = ref(false);
const editing = ref<FeeStructure | null>(null);

const form = useForm<{
    name: string;
    type: string;
    amount: string | null;
    session_id: number | null;
    class_id: number | null;
    is_optional: boolean;
    description: string;
    status: string;
}>({
    name: '',
    type: 'monthly',
    amount: null,
    session_id: null,
    class_id: null,
    is_optional: false,
    description: '',
    status: 'active',
});

function openCreate() {
    editing.value = null;
    form.clearErrors();
    form.reset();
    modalOpen.value = true;
}

function openEdit(structure: FeeStructure) {
    editing.value = structure;
    form.clearErrors();
    form.name = structure.name;
    form.type = structure.type;
    form.amount = structure.amount;
    form.session_id = structure.session_id;
    form.class_id = structure.class_id ?? null;
    form.is_optional = structure.is_optional;
    form.description = structure.description ?? '';
    form.status = structure.status;
    modalOpen.value = true;
}

function submit() {
    if (editing.value) {
        form.put(FeeStructureController.update.url({ feeStructure: editing.value.id }), {
            preserveScroll: true,
            onSuccess: () => (modalOpen.value = false),
        });
    } else {
        form.post(FeeStructureController.store.url(), {
            preserveScroll: true,
            onSuccess: () => (modalOpen.value = false),
        });
    }
}

async function destroy(structure: FeeStructure) {
    const result = await toast.confirm({
        title: 'Delete fee structure?',
        text: `"${structure.name}" will be permanently removed.`,
        confirmText: 'Delete',
    });

    if (result.isConfirmed) {
        router.delete(FeeStructureController.destroy.url({ feeStructure: structure.id }), { preserveScroll: true });
    }
}

function go(field: string, value: string | number | null | undefined) {
    router.get(window.location.pathname, { [field]: value || undefined }, { preserveState: true, preserveScroll: true, replace: true });
}

const columns = [
    { key: 'name', label: 'Name' },
    { key: 'type', label: 'Type' },
    { key: 'amount', label: 'Amount', value: (s: any) => formatMoney(s.amount) },
    { key: 'session', label: 'Session', value: (s: any) => s.session?.name ?? null },
    { key: 'class', label: 'Class', value: (s: any) => s.class?.name ?? 'All classes' },
    { key: 'status', label: 'Status' },
];
</script>

<template>
    <div>
        <Head title="Fee Structures" />
        <PageHeader title="Fee Structures" subtitle="Manage fee structures across sessions and classes.">
            <template #actions>
                <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700" @click="openCreate">
                    <Icon name="plus" class="h-4 w-4" />
                    Add Structure
                </button>
            </template>
        </PageHeader>

        <Card class="mb-4" :padded="false">
            <div class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center">
                <SearchInput :value="filters.search ?? ''" placeholder="Search by name..." />
                <div class="w-full sm:w-56">
                    <SelectInput :options="sessionOptions" :allow-empty="true" placeholder="All sessions" :model-value="filters.session_id ?? ''" @update:model-value="go('session_id', $event ?? '')" />
                </div>
            </div>
        </Card>

        <Card :padded="false">
            <div v-if="structures.data.length === 0">
                <EmptyState message="No fee structures found." icon="fees" />
            </div>
            <DataTable
                v-else
                :rows="structures.data"
                :columns="columns"
                :row-key="(structure) => structure.id"
                :subtitle-value="(structure) => structure.description ?? null"
            >
                <template #cell-name="{ row }">
                    <div class="font-medium text-slate-900 dark:text-slate-100">{{ row.name }}</div>
                    <div v-if="row.description" class="mt-0.5 max-w-xs truncate text-xs text-slate-400">{{ row.description }}</div>
                </template>
                <template #cell-type="{ row }">
                    <Badge v-if="row.is_optional" label="Optional" tone="sky" />
                    <span class="ml-2 text-xs text-slate-500">{{ titleCase(row.type) }}</span>
                </template>
                <template #cell-status="{ row }">
                    <Badge :label="row.status" :tone="row.status === 'active' ? 'green' : 'slate'" />
                </template>
                <template #actions="{ row }">
                    <button class="rounded-lg p-2 text-slate-400 transition-colors hover:bg-slate-100 hover:text-indigo-600 dark:hover:bg-slate-800" @click="openEdit(row)">
                        <Icon name="edit" class="h-4 w-4" />
                    </button>
                    <button class="rounded-lg p-2 text-slate-400 transition-colors hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/40" @click="destroy(row)">
                        <Icon name="trash" class="h-4 w-4" />
                    </button>
                </template>
            </DataTable>
            <Pagination :paginator="structures" />
        </Card>

        <Modal :open="modalOpen" :title="editing ? 'Edit Fee Structure' : 'Add Fee Structure'" @close="modalOpen = false">
            <form @submit.prevent="submit" class="space-y-4">
                <Field label="Name" :error="form.errors.name" required>
                    <TextInput v-model="form.name" placeholder="e.g. Tuition Fee - Monthly" />
                </Field>
                <Field label="Session" :error="form.errors.session_id" required>
                    <SelectInput v-model="form.session_id" :options="sessionOptions" :allow-empty="true" placeholder="Select session" />
                </Field>
                <div class="grid gap-4 sm:grid-cols-2">
                    <Field label="Type" :error="form.errors.type" required>
                        <SelectInput v-model="form.type" :options="typeOptions" />
                    </Field>
                    <Field label="Amount" :error="form.errors.amount" required>
                        <TextInput v-model="form.amount" type="number" min="0" step="0.01" />
                    </Field>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <Field label="Class" hint="Leave blank to apply to all classes" :error="form.errors.class_id">
                        <SelectInput v-model="form.class_id" :options="classOptions" :allow-empty="true" placeholder="All classes" />
                    </Field>
                    <Field label="Status" :error="form.errors.status" required>
                        <SelectInput v-model="form.status" :options="statusOptions" />
                    </Field>
                </div>
                <Field label="Description" :error="form.errors.description">
                    <TextareaInput v-model="form.description" placeholder="Optional details..." />
                </Field>
                <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                    <input v-model="form.is_optional" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" />
                    This fee is optional
                </label>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800" @click="modalOpen = false">
                        Cancel
                    </button>
                    <SubmitButton :processing="form.processing">{{ editing ? 'Save Changes' : 'Create Structure' }}</SubmitButton>
                </div>
            </form>
        </Modal>
    </div>
</template>