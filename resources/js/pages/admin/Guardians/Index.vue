<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/components/ui/AppLayout.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import Card from '@/components/ui/Card.vue';
import Modal from '@/components/ui/Modal.vue';
import Pagination from '@/components/ui/Pagination.vue';
import Badge from '@/components/ui/Badge.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import Field from '@/components/ui/Field.vue';
import TextInput from '@/components/ui/TextInput.vue';
import SelectInput from '@/components/ui/SelectInput.vue';
import TextareaInput from '@/components/ui/TextareaInput.vue';
import SubmitButton from '@/components/ui/SubmitButton.vue';
import SearchInput from '@/components/ui/SearchInput.vue';
import DataTable from '@/components/ui/DataTable.vue';
import { titleCase } from '@/lib/helpers';
import { useToast } from '@/lib/toast';

defineOptions({ layout: AppLayout });

const props = defineProps<{
    guardians: {
        data: Array<{
            id: number;
            name: string;
            email: string | null;
            phone: string | null;
            occupation: string | null;
            address: string | null;
            status: string;
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
        total: number;
        per_page: number;
        current_page: number;
        last_page: number;
    };
    filters: {
        search?: string;
        status?: string;
    };
}>();

const toast = useToast();
const showModal = ref(false);
const editingId = ref<number | null>(null);

const statusOptions = [
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' },
];

const filterStatusOptions = [
    { value: '', label: 'All Statuses' },
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' },
];

const filterStatus = ref<string | number>(props.filters.status ?? '');

function applyFilter(param: string, value: string | number) {
    const current = new URLSearchParams(window.location.search);
    if (value === '' || value === null || value === undefined) {
        current.delete(param);
    } else {
        current.set(param, String(value));
    }
    router.get(window.location.pathname, Object.fromEntries(current), { preserveState: true, preserveScroll: true, replace: true });
}

function onFilterStatus(v: string | number | null | undefined) {
    filterStatus.value = v ?? '';
    applyFilter('status', filterStatus.value);
}

function statusTone(status: string): 'green' | 'red' {
    return status === 'active' ? 'green' : 'red';
}

const columns = [
    { key: 'name', label: 'Name' },
    { key: 'email', label: 'Email' },
    { key: 'phone', label: 'Phone' },
    { key: 'occupation', label: 'Occupation' },
    { key: 'status', label: 'Status', value: (g: any) => titleCase(g.status) },
];

function openCreate() {
    editingId.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
}

function openEdit(guardian: (typeof props.guardians.data)[0]) {
    editingId.value = guardian.id;
    form.name = guardian.name;
    form.email = guardian.email ?? '';
    form.phone = guardian.phone ?? '';
    form.occupation = guardian.occupation ?? '';
    form.address = guardian.address ?? '';
    form.status = guardian.status;
    form.student_ids = [];
    form.clearErrors();
    showModal.value = true;
}

const form = useForm({
    name: '',
    email: '',
    phone: '',
    occupation: '',
    address: '',
    status: 'active',
    student_ids: [] as number[],
});

function submit() {
    if (editingId.value) {
        form.transform((data) => ({
            ...data,
            student_ids: data.student_ids.length > 0 ? data.student_ids : undefined,
        })).put(`/admin/guardians/${editingId.value}`, {
            onSuccess: () => {
                showModal.value = false;
            },
        });
    } else {
        form.transform((data) => ({
            ...data,
            student_ids: data.student_ids.length > 0 ? data.student_ids : undefined,
        })).post('/admin/guardians', {
            onSuccess: () => {
                showModal.value = false;
            },
        });
    }
}

async function destroy(id: number) {
    const result = await toast.confirm();
    if (result.isConfirmed) {
        form.delete(`/admin/guardians/${id}`);
    }
}
</script>

<template>
    <div>
        <Head title="Parents / Guardians" />

        <PageHeader title="Parents / Guardians" subtitle="Manage parent and guardian records">
            <template #actions>
                <button class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700" @click="openCreate">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                    Add Parent
                </button>
            </template>
        </PageHeader>

        <Card>
            <div class="mb-4 flex flex-wrap items-center gap-3">
                <SearchInput :value="filters.search ?? ''" placeholder="Search by name or email..." />
                <SelectInput :model-value="filterStatus" :options="filterStatusOptions" placeholder="All Statuses" allow-empty @update:model-value="onFilterStatus" />
            </div>

            <div v-if="guardians.data.length === 0">
                <EmptyState message="No parents/guardians found." />
            </div>
            <template v-else>
                <DataTable
                    :rows="guardians.data"
                    :columns="columns"
                    :row-key="(guardian) => guardian.id"
                    :subtitle-value="(guardian) => guardian.email ?? null"
                >
                    <template #cell-status="{ row }">
                        <Badge :label="titleCase(row.status)" :tone="statusTone(row.status)" />
                    </template>
                    <template #actions="{ row }">
                        <button class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800" @click="openEdit(row)">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                        </button>
                        <button class="rounded-lg p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/40" @click="destroy(row.id)">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        </button>
                    </template>
                </DataTable>
                <Pagination :paginator="guardians" />
            </template>
        </Card>

        <Modal :open="showModal" :title="editingId ? 'Edit Parent' : 'Add Parent'" @close="showModal = false">
            <form @submit.prevent="submit" class="space-y-4">
                <Field label="Name" required :error="form.errors.name">
                    <TextInput v-model="form.name" placeholder="Full name" />
                </Field>

                <div class="grid grid-cols-2 gap-4">
                    <Field label="Email" :error="form.errors.email">
                        <TextInput v-model="form.email" type="email" placeholder="Email address" />
                    </Field>
                    <Field label="Phone" :error="form.errors.phone">
                        <TextInput v-model="form.phone" placeholder="Phone number" />
                    </Field>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <Field label="Occupation" :error="form.errors.occupation">
                        <TextInput v-model="form.occupation" placeholder="e.g. Engineer" />
                    </Field>
                    <Field label="Status" required :error="form.errors.status">
                        <SelectInput v-model="form.status" :options="statusOptions" />
                    </Field>
                </div>

                <Field label="Address" :error="form.errors.address">
                    <TextareaInput v-model="form.address" :rows="2" placeholder="Full address" />
                </Field>

                <div class="flex items-center justify-end gap-3 border-t border-slate-200 pt-4 dark:border-slate-800">
                    <button type="button" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-700 ring-1 ring-inset ring-slate-300 hover:bg-slate-50 dark:text-slate-200 dark:ring-slate-700 dark:hover:bg-slate-800" @click="showModal = false">Cancel</button>
                    <SubmitButton :processing="form.processing">{{ editingId ? 'Update' : 'Create' }}</SubmitButton>
                </div>
            </form>
        </Modal>
    </div>
</template>
