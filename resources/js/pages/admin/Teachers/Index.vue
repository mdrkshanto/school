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
import { titleCase, formatDate } from '@/lib/helpers';
import { useToast } from '@/lib/toast';

defineOptions({ layout: AppLayout });

const props = defineProps<{
    teachers: {
        data: Array<{
            id: number;
            name: string;
            employee_no: string;
            gender: string;
            dob: string | null;
            phone: string | null;
            email: string | null;
            qualification: string | null;
            specialization: string | null;
            address: string | null;
            join_date: string | null;
            leave_date: string | null;
            status: string;
            user: { id: number; phone: string | null } | null;
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

const genderOptions = [
    { value: 'male', label: 'Male' },
    { value: 'female', label: 'Female' },
    { value: 'other', label: 'Other' },
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
    { key: 'employee_no', label: 'Emp. No' },
    { key: 'phone', label: 'Phone' },
    { key: 'qualification', label: 'Qualification' },
    { key: 'join_date', label: 'Join Date', value: (t: any) => formatDate(t.join_date) },
    { key: 'status', label: 'Status', value: (t: any) => titleCase(t.status) },
];

function openCreate() {
    editingId.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
}

function openEdit(teacher: (typeof props.teachers.data)[0]) {
    editingId.value = teacher.id;
    form.name = teacher.name;
    form.employee_no = teacher.employee_no;
    form.gender = teacher.gender;
    form.dob = teacher.dob ? new Date(teacher.dob).toISOString().split('T')[0] : '';
    form.phone = teacher.phone ?? '';
    form.email = teacher.email ?? '';
    form.qualification = teacher.qualification ?? '';
    form.specialization = teacher.specialization ?? '';
    form.address = teacher.address ?? '';
    form.join_date = teacher.join_date ? new Date(teacher.join_date).toISOString().split('T')[0] : '';
    form.leave_date = teacher.leave_date ? new Date(teacher.leave_date).toISOString().split('T')[0] : '';
    form.status = teacher.status;
    form.clearErrors();
    showModal.value = true;
}

const form = useForm({
    name: '',
    employee_no: '',
    gender: 'male',
    dob: '',
    phone: '',
    email: '',
    qualification: '',
    specialization: '',
    address: '',
    join_date: '',
    leave_date: '',
    status: 'active',
});

function submit() {
    if (editingId.value) {
        form.transform((data) => ({
            ...data,
            dob: data.dob || undefined,
            join_date: data.join_date || undefined,
            leave_date: data.leave_date || undefined,
        })).put(`/admin/teachers/${editingId.value}`, {
            onSuccess: () => {
                showModal.value = false;
            },
        });
    } else {
        form.transform((data) => ({
            ...data,
            dob: data.dob || undefined,
            join_date: data.join_date || undefined,
            leave_date: data.leave_date || undefined,
        })).post('/admin/teachers', {
            onSuccess: () => {
                showModal.value = false;
            },
        });
    }
}

async function destroy(id: number) {
    const result = await toast.confirm();
    if (result.isConfirmed) {
        form.delete(`/admin/teachers/${id}`);
    }
}
</script>

<template>
    <div>
        <Head title="Teachers" />

        <PageHeader title="Teachers" subtitle="Manage teacher records">
            <template #actions>
                <button class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700" @click="openCreate">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                    Add Teacher
                </button>
            </template>
        </PageHeader>

        <Card>
            <div class="mb-4 flex flex-wrap items-center gap-3">
                <SearchInput :value="filters.search ?? ''" placeholder="Search by name or employee no..." />
                <SelectInput :model-value="filterStatus" :options="filterStatusOptions" placeholder="All Statuses" allow-empty @update:model-value="onFilterStatus" />
            </div>

            <div v-if="teachers.data.length === 0">
                <EmptyState message="No teachers found." />
            </div>
            <template v-else>
                <DataTable
                    :rows="teachers.data"
                    :columns="columns"
                    :row-key="(teacher) => teacher.id"
                    :subtitle-value="(teacher) => teacher.phone ?? null"
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
                <Pagination :paginator="teachers" />
            </template>
        </Card>

        <Modal :open="showModal" :title="editingId ? 'Edit Teacher' : 'Add Teacher'" @close="showModal = false">
            <form @submit.prevent="submit" class="space-y-4">
                <Field label="Name" required :error="form.errors.name">
                    <TextInput v-model="form.name" placeholder="Full name" />
                </Field>

                <div class="grid grid-cols-2 gap-4">
                    <Field label="Employee No" required :error="form.errors.employee_no">
                        <TextInput v-model="form.employee_no" placeholder="e.g. EMP001" />
                    </Field>
                    <Field label="Gender" required :error="form.errors.gender">
                        <SelectInput v-model="form.gender" :options="genderOptions" />
                    </Field>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <Field label="Date of Birth" :error="form.errors.dob">
                        <TextInput v-model="form.dob" type="date" />
                    </Field>
                    <Field label="Status" required :error="form.errors.status">
                        <SelectInput v-model="form.status" :options="statusOptions" />
                    </Field>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <Field label="Phone" :error="form.errors.phone">
                        <TextInput v-model="form.phone" placeholder="Phone number" />
                    </Field>
                    <Field label="Email" :error="form.errors.email">
                        <TextInput v-model="form.email" type="email" placeholder="Email address" />
                    </Field>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <Field label="Qualification" :error="form.errors.qualification">
                        <TextInput v-model="form.qualification" placeholder="e.g. B.Ed" />
                    </Field>
                    <Field label="Specialization" :error="form.errors.specialization">
                        <TextInput v-model="form.specialization" placeholder="e.g. Mathematics" />
                    </Field>
                </div>

                <Field label="Address" :error="form.errors.address">
                    <TextareaInput v-model="form.address" :rows="2" placeholder="Full address" />
                </Field>

                <div class="grid grid-cols-2 gap-4">
                    <Field label="Join Date" :error="form.errors.join_date">
                        <TextInput v-model="form.join_date" type="date" />
                    </Field>
                    <Field label="Leave Date" :error="form.errors.leave_date">
                        <TextInput v-model="form.leave_date" type="date" />
                    </Field>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-200 pt-4 dark:border-slate-800">
                    <button type="button" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-700 ring-1 ring-inset ring-slate-300 hover:bg-slate-50 dark:text-slate-200 dark:ring-slate-700 dark:hover:bg-slate-800" @click="showModal = false">Cancel</button>
                    <SubmitButton :processing="form.processing">{{ editingId ? 'Update' : 'Create' }}</SubmitButton>
                </div>
            </form>
        </Modal>
    </div>
</template>
