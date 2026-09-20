<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
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
import SubmitButton from '@/components/ui/SubmitButton.vue';
import DataTable from '@/components/ui/DataTable.vue';
import { formatDate } from '@/lib/helpers';
import { useToast } from '@/lib/toast';

defineOptions({ layout: AppLayout });

const props = defineProps<{
    sessions: {
        data: Array<{
            id: number;
            name: string;
            starts_at: string;
            ends_at: string;
            is_active: boolean;
            students_count: number;
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
        total: number;
        per_page: number;
        current_page: number;
        last_page: number;
    };
    filters: Record<string, never>;
}>();

const toast = useToast();
const showModal = ref(false);
const editingId = ref<number | null>(null);

const activeOptions = [
    { value: '1', label: 'Active' },
    { value: '0', label: 'Inactive' },
];

const columns = [
    { key: 'name', label: 'Name' },
    { key: 'starts_at', label: 'Start Date', value: (s: any) => formatDate(s.starts_at) },
    { key: 'ends_at', label: 'End Date', value: (s: any) => formatDate(s.ends_at) },
    { key: 'students_count', label: 'Students', align: 'right' as const },
    { key: 'status', label: 'Status', value: (s: any) => (s.is_active ? 'Active' : 'Inactive') },
];

function openCreate() {
    editingId.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
}

function openEdit(session: (typeof props.sessions.data)[0]) {
    editingId.value = session.id;
    form.name = session.name;
    form.starts_at = new Date(session.starts_at).toISOString().split('T')[0];
    form.ends_at = new Date(session.ends_at).toISOString().split('T')[0];
    form.is_active = session.is_active ? '1' : '0';
    form.clearErrors();
    showModal.value = true;
}

const form = useForm({
    name: '',
    starts_at: '',
    ends_at: '',
    is_active: '0' as string,
});

function submit() {
    const payload = {
        name: form.name,
        starts_at: form.starts_at,
        ends_at: form.ends_at,
        is_active: form.is_active === '1',
    };

    if (editingId.value) {
        form.transform(() => payload).put(`/admin/sessions/${editingId.value}`, {
            onSuccess: () => {
                showModal.value = false;
            },
        });
    } else {
        form.transform(() => payload).post('/admin/sessions', {
            onSuccess: () => {
                showModal.value = false;
            },
        });
    }
}

async function destroy(id: number) {
    const result = await toast.confirm();
    if (result.isConfirmed) {
        form.delete(`/admin/sessions/${id}`);
    }
}
</script>

<template>
    <div>
        <Head title="Academic Sessions" />

        <PageHeader title="Academic Sessions" subtitle="Manage academic sessions">
            <template #actions>
                <button class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700" @click="openCreate">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                    Add Session
                </button>
            </template>
        </PageHeader>

        <Card>
            <div v-if="sessions.data.length === 0">
                <EmptyState message="No sessions found." />
            </div>
            <template v-else>
                <DataTable
                    :rows="sessions.data"
                    :columns="columns"
                    :row-key="(session) => session.id"
                    :subtitle-value="(session) => formatDate(session.starts_at) + ' — ' + formatDate(session.ends_at)"
                >
                    <template #cell-status="{ row }">
                        <Badge :label="row.is_active ? 'Active' : 'Inactive'" :tone="row.is_active ? 'green' : 'slate'" />
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
                <Pagination :paginator="sessions" />
            </template>
        </Card>

        <Modal :open="showModal" :title="editingId ? 'Edit Session' : 'Add Session'" @close="showModal = false">
            <form @submit.prevent="submit" class="space-y-4">
                <Field label="Session Name" required :error="form.errors.name">
                    <TextInput v-model="form.name" placeholder="e.g. 2025-2026" />
                </Field>

                <div class="grid grid-cols-2 gap-4">
                    <Field label="Start Date" required :error="form.errors.starts_at">
                        <TextInput v-model="form.starts_at" type="date" />
                    </Field>
                    <Field label="End Date" required :error="form.errors.ends_at">
                        <TextInput v-model="form.ends_at" type="date" />
                    </Field>
                </div>

                <Field label="Active" required :error="form.errors.is_active">
                    <SelectInput v-model="form.is_active" :options="activeOptions" />
                </Field>

                <div class="flex items-center justify-end gap-3 border-t border-slate-200 pt-4 dark:border-slate-800">
                    <button type="button" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-700 ring-1 ring-inset ring-slate-300 hover:bg-slate-50 dark:text-slate-200 dark:ring-slate-700 dark:hover:bg-slate-800" @click="showModal = false">Cancel</button>
                    <SubmitButton :processing="form.processing">{{ editingId ? 'Update' : 'Create' }}</SubmitButton>
                </div>
            </form>
        </Modal>
    </div>
</template>
