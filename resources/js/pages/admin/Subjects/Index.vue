<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
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
    subjects: {
        data: Array<{
            id: number;
            name: string;
            code: string | null;
            description: string | null;
            status: string;
            classes: Array<{ id: number; name: string }>;
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
const showModal = ref(false);
const editingId = ref<number | null>(null);

const statusOptions = [
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' },
];

function statusTone(status: string): 'green' | 'red' {
    return status === 'active' ? 'green' : 'red';
}

const columns = [
    { key: 'name', label: 'Name' },
    { key: 'code', label: 'Code' },
    { key: 'classes', label: 'Classes' },
    { key: 'status', label: 'Status', value: (s: any) => titleCase(s.status) },
];

const allClasses = computed(() => {
    const set = new Map<number, string>();
    for (const subject of props.subjects.data) {
        for (const cls of subject.classes) {
            set.set(cls.id, cls.name);
        }
    }
    return Array.from(set.entries()).map(([id, name]) => ({ id, name }));
});

function openCreate() {
    editingId.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
}

function openEdit(subject: (typeof props.subjects.data)[0]) {
    editingId.value = subject.id;
    form.name = subject.name;
    form.code = subject.code ?? '';
    form.description = subject.description ?? '';
    form.status = subject.status;
    form.class_ids = subject.classes.map((c) => c.id);
    form.clearErrors();
    showModal.value = true;
}

const form = useForm({
    name: '',
    code: '',
    description: '',
    status: 'active',
    class_ids: [] as number[],
});

function toggleClassId(classId: number) {
    const index = form.class_ids.indexOf(classId);
    if (index === -1) {
        form.class_ids.push(classId);
    } else {
        form.class_ids.splice(index, 1);
    }
}

function submit() {
    if (editingId.value) {
        form.transform((data) => ({
            ...data,
            code: data.code || undefined,
            description: data.description || undefined,
            class_ids: data.class_ids.length > 0 ? data.class_ids : undefined,
        })).put(`/admin/subjects/${editingId.value}`, {
            onSuccess: () => {
                showModal.value = false;
            },
        });
    } else {
        form.transform((data) => ({
            ...data,
            code: data.code || undefined,
            description: data.description || undefined,
            class_ids: data.class_ids.length > 0 ? data.class_ids : undefined,
        })).post('/admin/subjects', {
            onSuccess: () => {
                showModal.value = false;
            },
        });
    }
}

async function destroy(id: number) {
    const result = await toast.confirm();
    if (result.isConfirmed) {
        form.delete(`/admin/subjects/${id}`);
    }
}
</script>

<template>
    <div>
        <Head title="Subjects" />

        <PageHeader title="Subjects" subtitle="Manage subjects">
            <template #actions>
                <button class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700" @click="openCreate">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                    Add Subject
                </button>
            </template>
        </PageHeader>

        <Card>
            <div class="mb-4 flex flex-wrap items-center gap-3">
                <SearchInput :value="filters.search ?? ''" placeholder="Search by subject name..." />
            </div>

            <div v-if="subjects.data.length === 0">
                <EmptyState message="No subjects found." />
            </div>
            <template v-else>
                <DataTable
                    :rows="subjects.data"
                    :columns="columns"
                    :row-key="(subject) => subject.id"
                    :subtitle-value="(subject) => subject.code ?? null"
                >
                    <template #cell-classes="{ row }">
                        <div class="flex flex-wrap gap-1.5">
                            <Badge v-for="cls in row.classes" :key="cls.id" :label="cls.name" tone="indigo" />
                            <span v-if="row.classes.length === 0" class="text-xs text-slate-400">None</span>
                        </div>
                    </template>
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
                <Pagination :paginator="subjects" />
            </template>
        </Card>

        <Modal :open="showModal" :title="editingId ? 'Edit Subject' : 'Add Subject'" wide @close="showModal = false">
            <form @submit.prevent="submit" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <Field label="Subject Name" required :error="form.errors.name">
                        <TextInput v-model="form.name" placeholder="e.g. Mathematics" />
                    </Field>
                    <Field label="Code" :error="form.errors.code">
                        <TextInput v-model="form.code" placeholder="e.g. MATH101" />
                    </Field>
                </div>

                <Field label="Description" :error="form.errors.description">
                    <TextareaInput v-model="form.description" :rows="2" placeholder="Optional description" />
                </Field>

                <Field label="Status" required :error="form.errors.status">
                    <SelectInput v-model="form.status" :options="statusOptions" />
                </Field>

                <Field label="Assign Classes" :error="form.errors.class_ids">
                    <div class="flex flex-wrap gap-3">
                        <label
                            v-for="cls in allClasses"
                            :key="cls.id"
                            class="flex cursor-pointer items-center gap-2 rounded-lg border px-3 py-2 text-sm transition-colors"
                            :class="form.class_ids.includes(cls.id) ? 'border-indigo-400 bg-indigo-50 text-indigo-700 dark:border-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-300' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400'"
                        >
                            <input
                                type="checkbox"
                                :checked="form.class_ids.includes(cls.id)"
                                class="hidden"
                                @change="toggleClassId(cls.id)"
                            />
                            <span class="flex h-4 w-4 items-center justify-center rounded border" :class="form.class_ids.includes(cls.id) ? 'border-indigo-500 bg-indigo-600' : 'border-slate-300 dark:border-slate-600'">
                                <svg v-if="form.class_ids.includes(cls.id)" class="h-3 w-3 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                            </span>
                            {{ cls.name }}
                        </label>
                        <span v-if="allClasses.length === 0" class="text-xs text-slate-400">No classes available</span>
                    </div>
                </Field>

                <div class="flex items-center justify-end gap-3 border-t border-slate-200 pt-4 dark:border-slate-800">
                    <button type="button" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-700 ring-1 ring-inset ring-slate-300 hover:bg-slate-50 dark:text-slate-200 dark:ring-slate-700 dark:hover:bg-slate-800" @click="showModal = false">Cancel</button>
                    <SubmitButton :processing="form.processing">{{ editingId ? 'Update' : 'Create' }}</SubmitButton>
                </div>
            </form>
        </Modal>
    </div>
</template>
