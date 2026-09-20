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
import Icon from '@/components/ui/Icon.vue';
import { titleCase } from '@/lib/helpers';
import { useToast } from '@/lib/toast';

defineOptions({ layout: AppLayout });

const props = defineProps<{
    classes: {
        data: Array<{
            id: number;
            name: string;
            numeric_value: number | null;
            description: string | null;
            status: string;
            sections: Array<{
                id: number;
                name: string;
                class_id: number;
            }>;
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
        total: number;
        per_page: number;
        current_page: number;
        last_page: number;
    };
    filters: {
        search?: string;
    };
}>();

const toast = useToast();
const showClassModal = ref(false);
const editingClassId = ref<number | null>(null);

const showSectionModal = ref(false);
const editingSectionId = ref<number | null>(null);
const sectionClassId = ref<number | null>(null);
const sectionClassName = ref('');

const statusOptions = [
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' },
];

function statusTone(status: string): 'green' | 'red' {
    return status === 'active' ? 'green' : 'red';
}

function openCreateClass() {
    editingClassId.value = null;
    classForm.reset();
    classForm.clearErrors();
    showClassModal.value = true;
}

function openEditClass(cls: (typeof props.classes.data)[0]) {
    editingClassId.value = cls.id;
    classForm.name = cls.name;
    classForm.numeric_value = cls.numeric_value ?? '';
    classForm.description = cls.description ?? '';
    classForm.status = cls.status;
    classForm.clearErrors();
    showClassModal.value = true;
}

const classForm = useForm({
    name: '',
    numeric_value: '' as string | number,
    description: '',
    status: 'active',
});

function submitClass() {
    if (editingClassId.value) {
        classForm.transform((data) => ({
            ...data,
            numeric_value: data.numeric_value !== '' ? Number(data.numeric_value) : undefined,
            description: data.description || undefined,
        })).put(`/admin/classes/${editingClassId.value}`, {
            onSuccess: () => {
                showClassModal.value = false;
            },
        });
    } else {
        classForm.transform((data) => ({
            ...data,
            numeric_value: data.numeric_value !== '' ? Number(data.numeric_value) : undefined,
            description: data.description || undefined,
        })).post('/admin/classes', {
            onSuccess: () => {
                showClassModal.value = false;
            },
        });
    }
}

async function destroyClass(id: number) {
    const result = await toast.confirm();
    if (result.isConfirmed) {
        classForm.delete(`/admin/classes/${id}`);
    }
}

function openAddSection(classId: number, className: string) {
    editingSectionId.value = null;
    sectionClassId.value = classId;
    sectionClassName.value = className;
    sectionForm.reset();
    sectionForm.class_id = classId;
    sectionForm.status = 'active';
    sectionForm.clearErrors();
    showSectionModal.value = true;
}

function openEditSection(section: { id: number; name: string; class_id: number }, className: string) {
    editingSectionId.value = section.id;
    sectionClassId.value = section.class_id;
    sectionClassName.value = className;
    sectionForm.class_id = section.class_id;
    sectionForm.name = section.name;
    sectionForm.capacity = '';
    sectionForm.status = 'active';
    sectionForm.clearErrors();
    showSectionModal.value = true;
}

const sectionForm = useForm({
    class_id: 0 as number,
    name: '',
    capacity: '' as string | number,
    status: 'active',
});

function submitSection() {
    if (editingSectionId.value) {
        sectionForm.transform((data) => ({
            ...data,
            capacity: data.capacity !== '' ? Number(data.capacity) : undefined,
        })).put(`/admin/sections/${editingSectionId.value}`, {
            onSuccess: () => {
                showSectionModal.value = false;
            },
        });
    } else {
        sectionForm.transform((data) => ({
            ...data,
            capacity: data.capacity !== '' ? Number(data.capacity) : undefined,
        })).post(`/admin/classes/${sectionClassId.value}/sections`, {
            onSuccess: () => {
                showSectionModal.value = false;
            },
        });
    }
}

async function destroySection(id: number) {
    const result = await toast.confirm();
    if (result.isConfirmed) {
        sectionForm.delete(`/admin/sections/${id}`);
    }
}
</script>

<template>
    <div>
        <Head title="Classes" />

        <PageHeader title="Classes" subtitle="Manage classes and sections">
            <template #actions>
                <button class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700" @click="openCreateClass">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                    Add Class
                </button>
            </template>
        </PageHeader>

        <Card>
            <div class="mb-4 flex flex-wrap items-center gap-3">
                <SearchInput :value="filters.search ?? ''" placeholder="Search by class name..." />
            </div>

            <div v-if="classes.data.length === 0">
                <EmptyState message="No classes found." />
            </div>
            <div v-else>
                <div class="hidden overflow-x-auto md:block">
                    <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-800">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Order</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Sections</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <template v-for="cls in classes.data" :key="cls.id">
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50">
                                <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-slate-800 dark:text-slate-200">{{ cls.name }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600 dark:text-slate-400">{{ cls.numeric_value ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-1.5">
                                        <Badge v-for="section in cls.sections" :key="section.id" :label="section.name" tone="sky" />
                                        <span v-if="cls.sections.length === 0" class="text-xs text-slate-400">No sections</span>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3"><Badge :label="titleCase(cls.status)" :tone="statusTone(cls.status)" /></td>
                                <td class="whitespace-nowrap px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button class="rounded-lg p-1.5 text-indigo-400 hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-indigo-950/40" @click="openAddSection(cls.id, cls.name)" title="Add section">
                                            <Icon name="plus" class="h-4 w-4" />
                                        </button>
                                        <button class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800" @click="openEditClass(cls)">
                                            <Icon name="edit" class="h-4 w-4" />
                                        </button>
                                        <button class="rounded-lg p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/40" @click="destroyClass(cls.id)">
                                            <Icon name="trash" class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="cls.sections.length > 0">
                                <td colspan="5" class="bg-slate-50/80 px-4 py-3 dark:bg-slate-900/50">
                                    <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Sections</p>
                                    <div class="flex flex-wrap gap-2">
                                        <div v-for="section in cls.sections" :key="section.id" class="flex items-center gap-1.5 rounded-lg bg-white px-3 py-1.5 text-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-slate-700">
                                            <span class="text-slate-700 dark:text-slate-300">{{ section.name }}</span>
                                            <button class="text-slate-400 hover:text-indigo-600" @click="openEditSection(section, cls.name)">
                                                <Icon name="edit" class="h-3 w-3" />
                                            </button>
                                            <button class="text-slate-400 hover:text-red-600" @click="destroySection(section.id)">
                                                <Icon name="trash" class="h-3 w-3" />
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                </div>

                <div class="space-y-3 md:hidden">
                    <div
                        v-for="cls in classes.data"
                        :key="cls.id"
                        class="rounded-xl border border-slate-200 bg-slate-50/50 p-4 dark:border-slate-800 dark:bg-slate-900/40"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ cls.name }}</p>
                                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Order {{ cls.numeric_value ?? '—' }}</p>
                            </div>
                            <div class="flex shrink-0 items-center gap-1">
                                <button class="rounded-lg p-1.5 text-indigo-400 hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-indigo-950/40" title="Add section" @click="openAddSection(cls.id, cls.name)">
                                    <Icon name="plus" class="h-4 w-4" />
                                </button>
                                <button class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800" @click="openEditClass(cls)">
                                    <Icon name="edit" class="h-4 w-4" />
                                </button>
                                <button class="rounded-lg p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/40" @click="destroyClass(cls.id)">
                                    <Icon name="trash" class="h-4 w-4" />
                                </button>
                            </div>
                        </div>

                        <dl class="mt-3 divide-y divide-slate-100 dark:divide-slate-800">
                            <div class="flex items-center justify-between gap-4 py-2">
                                <dt class="shrink-0 text-xs font-medium uppercase tracking-wider text-slate-400 dark:text-slate-500">Status</dt>
                                <dd class="min-w-0 text-right text-sm font-medium text-slate-700 dark:text-slate-300">
                                    <Badge :label="titleCase(cls.status)" :tone="statusTone(cls.status)" />
                                </dd>
                            </div>
                        </dl>

                        <div class="mt-2">
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Sections</p>
                            <div v-if="cls.sections.length > 0" class="flex flex-wrap gap-2">
                                <div v-for="section in cls.sections" :key="section.id" class="flex items-center gap-1.5 rounded-lg bg-white px-3 py-1.5 text-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-slate-700">
                                    <span class="text-slate-700 dark:text-slate-300">{{ section.name }}</span>
                                    <button class="text-slate-400 hover:text-indigo-600" @click="openEditSection(section, cls.name)">
                                        <Icon name="edit" class="h-3 w-3" />
                                    </button>
                                    <button class="text-slate-400 hover:text-red-600" @click="destroySection(section.id)">
                                        <Icon name="trash" class="h-3 w-3" />
                                    </button>
                                </div>
                            </div>
                            <p v-else class="text-xs text-slate-400">No sections</p>
                        </div>
                    </div>
                </div>

                <Pagination :paginator="classes" />
            </div>
        </Card>

        <Modal :open="showClassModal" :title="editingClassId ? 'Edit Class' : 'Add Class'" @close="showClassModal = false">
            <form @submit.prevent="submitClass" class="space-y-4">
                <Field label="Class Name" required :error="classForm.errors.name">
                    <TextInput v-model="classForm.name" placeholder="e.g. Grade 1" />
                </Field>

                <div class="grid grid-cols-2 gap-4">
                    <Field label="Numeric Order" :error="classForm.errors.numeric_value">
                        <TextInput v-model="classForm.numeric_value" type="number" placeholder="e.g. 1" />
                    </Field>
                    <Field label="Status" required :error="classForm.errors.status">
                        <SelectInput v-model="classForm.status" :options="statusOptions" />
                    </Field>
                </div>

                <Field label="Description" :error="classForm.errors.description">
                    <TextareaInput v-model="classForm.description" :rows="2" placeholder="Optional description" />
                </Field>

                <div class="flex items-center justify-end gap-3 border-t border-slate-200 pt-4 dark:border-slate-800">
                    <button type="button" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-700 ring-1 ring-inset ring-slate-300 hover:bg-slate-50 dark:text-slate-200 dark:ring-slate-700 dark:hover:bg-slate-800" @click="showClassModal = false">Cancel</button>
                    <SubmitButton :processing="classForm.processing">{{ editingClassId ? 'Update' : 'Create' }}</SubmitButton>
                </div>
            </form>
        </Modal>

        <Modal :open="showSectionModal" :title="(editingSectionId ? 'Edit' : 'Add') + ' Section — ' + sectionClassName" @close="showSectionModal = false">
            <form @submit.prevent="submitSection" class="space-y-4">
                <Field label="Section Name" required :error="sectionForm.errors.name">
                    <TextInput v-model="sectionForm.name" placeholder="e.g. A" />
                </Field>

                <div class="grid grid-cols-2 gap-4">
                    <Field label="Capacity" :error="sectionForm.errors.capacity">
                        <TextInput v-model="sectionForm.capacity" type="number" placeholder="Max students" />
                    </Field>
                    <Field label="Status" required :error="sectionForm.errors.status">
                        <SelectInput v-model="sectionForm.status" :options="statusOptions" />
                    </Field>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-200 pt-4 dark:border-slate-800">
                    <button type="button" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-700 ring-1 ring-inset ring-slate-300 hover:bg-slate-50 dark:text-slate-200 dark:ring-slate-700 dark:hover:bg-slate-800" @click="showSectionModal = false">Cancel</button>
                    <SubmitButton :processing="sectionForm.processing">{{ editingSectionId ? 'Update' : 'Create' }}</SubmitButton>
                </div>
            </form>
        </Modal>
    </div>
</template>
