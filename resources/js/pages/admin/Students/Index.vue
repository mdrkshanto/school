<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
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
    students: {
        data: Array<{
            id: number;
            name: string;
            admission_no: string;
            gender: string;
            dob: string | null;
            blood_group: string | null;
            phone: string | null;
            email: string | null;
            address: string | null;
            status: string;
            avatar: string | null;
            class_id: number | null;
            section_id: number | null;
            session_id: number | null;
            class: { id: number; name: string } | null;
            section: { id: number; name: string } | null;
            session: { id: number; name: string } | null;
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
        total: number;
        per_page: number;
        current_page: number;
        last_page: number;
    };
    classes: Array<{ id: number; name: string }>;
    sections: Array<{ id: number; name: string; class_name: string }>;
    sessions: Array<{ id: number; name: string }>;
    filters: {
        search?: string;
        class_id?: number;
        section_id?: number;
        status?: string;
    };
}>();

const toast = useToast();
const showModal = ref(false);
const editingId = ref<number | null>(null);

const statusOptions = [
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' },
    { value: 'alumni', label: 'Alumni' },
];

const genderOptions = [
    { value: 'male', label: 'Male' },
    { value: 'female', label: 'Female' },
    { value: 'other', label: 'Other' },
];

const classOptions = computed(() => props.classes.map((c) => ({ value: c.id, label: c.name })));

const sectionOptions = computed(() => {
    const classId = form.class_id;
    const filtered = classId ? props.sections.filter((s) => s.class_name === props.classes.find((c) => c.id === classId)?.name) : props.sections;
    return filtered.map((s) => ({ value: s.id, label: s.name + (s.class_name ? ` (${s.class_name})` : '') }));
});

const sessionOptions = computed(() => props.sessions.map((s) => ({ value: s.id, label: s.name })));

const filterClassOptions = computed(() => [
    { value: '', label: 'All Classes' },
    ...props.classes.map((c) => ({ value: c.id, label: c.name })),
]);

const filterSectionOptions = computed(() => [
    { value: '', label: 'All Sections' },
    ...props.sections.map((s) => ({ value: s.id, label: s.name + (s.class_name ? ` (${s.class_name})` : '') })),
]);

const filterStatusOptions = [
    { value: '', label: 'All Statuses' },
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' },
    { value: 'alumni', label: 'Alumni' },
];

const filterClass = ref<string | number>(props.filters.class_id ?? '');
const filterSection = ref<string | number>(props.filters.section_id ?? '');
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

function onFilterClass(v: string | number | null | undefined) {
    filterClass.value = v ?? '';
    applyFilter('class_id', filterClass.value);
}

function onFilterSection(v: string | number | null | undefined) {
    filterSection.value = v ?? '';
    applyFilter('section_id', filterSection.value);
}

function onFilterStatus(v: string | number | null | undefined) {
    filterStatus.value = v ?? '';
    applyFilter('status', filterStatus.value);
}

function statusTone(status: string): 'green' | 'red' | 'amber' | 'slate' {
    const tones: Record<string, 'green' | 'red' | 'amber' | 'slate'> = { active: 'green', inactive: 'red', alumni: 'amber' };

    return tones[status] ?? 'slate';
}

const columns = [
    { key: 'name', label: 'Name' },
    { key: 'admission_no', label: 'Adm. No' },
    { key: 'class', label: 'Class', value: (s: any) => s.class?.name ?? null },
    { key: 'section', label: 'Section', value: (s: any) => s.section?.name ?? null },
    { key: 'gender', label: 'Gender', value: (s: any) => titleCase(s.gender) },
    { key: 'status', label: 'Status', value: (s: any) => titleCase(s.status) },
];

function openCreate() {
    editingId.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
}

function openEdit(student: (typeof props.students.data)[0]) {
    editingId.value = student.id;
    form.name = student.name;
    form.admission_no = student.admission_no;
    form.gender = student.gender;
    form.dob = student.dob ? new Date(student.dob).toISOString().split('T')[0] : '';
    form.blood_group = student.blood_group ?? '';
    form.phone = student.phone ?? '';
    form.email = student.email ?? '';
    form.address = student.address ?? '';
    form.class_id = student.class_id ?? '';
    form.section_id = student.section_id ?? '';
    form.session_id = student.session_id ?? '';
    form.status = student.status;
    form.avatar = null;
    form.clearErrors();
    showModal.value = true;
}

const form = useForm({
    name: '',
    admission_no: '',
    gender: 'male',
    dob: '',
    blood_group: '',
    phone: '',
    email: '',
    address: '',
    class_id: '' as string | number,
    section_id: '' as string | number,
    session_id: '' as string | number,
    status: 'active',
    avatar: null as File | null,
});

function submit() {
    if (editingId.value) {
        form.transform((data) => ({
            ...data,
            class_id: data.class_id || undefined,
            section_id: data.section_id || undefined,
            session_id: data.session_id || undefined,
        })).put(`/admin/students/${editingId.value}`, {
            onSuccess: () => {
                showModal.value = false;
            },
        });
    } else {
        form.transform((data) => ({
            ...data,
            class_id: data.class_id || undefined,
            section_id: data.section_id || undefined,
            session_id: data.session_id || undefined,
        })).post('/admin/students', {
            onSuccess: () => {
                showModal.value = false;
            },
        });
    }
}

async function destroy(id: number) {
    const result = await toast.confirm();
    if (result.isConfirmed) {
        form.delete(`/admin/students/${id}`);
    }
}

function onAvatarChange(event: Event) {
    const target = event.target as HTMLInputElement;
    form.avatar = target.files?.[0] ?? null;
}
</script>

<template>
    <div>
        <Head title="Students" />

        <PageHeader title="Students" subtitle="Manage student records">
            <template #actions>
                <button class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700" @click="openCreate">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                    Add Student
                </button>
            </template>
        </PageHeader>

        <Card>
            <div class="mb-4 flex flex-wrap items-center gap-3">
                <SearchInput :value="filters.search ?? ''" placeholder="Search by name or admission no..." />
                <SelectInput :model-value="filterClass" :options="filterClassOptions" placeholder="All Classes" allow-empty @update:model-value="onFilterClass" />
                <SelectInput :model-value="filterSection" :options="filterSectionOptions" placeholder="All Sections" allow-empty @update:model-value="onFilterSection" />
                <SelectInput :model-value="filterStatus" :options="filterStatusOptions" placeholder="All Statuses" allow-empty @update:model-value="onFilterStatus" />
            </div>

            <div v-if="students.data.length === 0">
                <EmptyState message="No students found." />
            </div>
            <template v-else>
                <DataTable
                    :rows="students.data"
                    :columns="columns"
                    :row-key="(student) => student.id"
                    :subtitle-value="(student) => [student.class?.name, student.section?.name].filter(Boolean).join(' · ') || null"
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
                <Pagination :paginator="students" />
            </template>
        </Card>

        <Modal :open="showModal" :title="editingId ? 'Edit Student' : 'Add Student'" @close="showModal = false">
            <form @submit.prevent="submit" class="space-y-4">
                <Field label="Name" required :error="form.errors.name">
                    <TextInput v-model="form.name" placeholder="Full name" />
                </Field>

                <div class="grid grid-cols-2 gap-4">
                    <Field label="Admission No" required :error="form.errors.admission_no">
                        <TextInput v-model="form.admission_no" placeholder="e.g. ADM001" />
                    </Field>
                    <Field label="Gender" required :error="form.errors.gender">
                        <SelectInput v-model="form.gender" :options="genderOptions" />
                    </Field>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <Field label="Date of Birth" :error="form.errors.dob">
                        <TextInput v-model="form.dob" type="date" />
                    </Field>
                    <Field label="Blood Group" :error="form.errors.blood_group">
                        <TextInput v-model="form.blood_group" placeholder="e.g. A+" />
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

                <Field label="Address" :error="form.errors.address">
                    <TextareaInput v-model="form.address" :rows="2" placeholder="Full address" />
                </Field>

                <div class="grid grid-cols-3 gap-4">
                    <Field label="Class" required :error="form.errors.class_id">
                        <SelectInput v-model="form.class_id" :options="classOptions" placeholder="Select class" />
                    </Field>
                    <Field label="Section" :error="form.errors.section_id">
                        <SelectInput v-model="form.section_id" :options="sectionOptions" placeholder="Select section" allow-empty />
                    </Field>
                    <Field label="Session" :error="form.errors.session_id">
                        <SelectInput v-model="form.session_id" :options="sessionOptions" placeholder="Select session" allow-empty />
                    </Field>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <Field label="Status" required :error="form.errors.status">
                        <SelectInput v-model="form.status" :options="statusOptions" />
                    </Field>
                    <Field label="Avatar" :error="form.errors.avatar">
                        <input type="file" accept="image/*" class="w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100" @change="onAvatarChange" />
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
