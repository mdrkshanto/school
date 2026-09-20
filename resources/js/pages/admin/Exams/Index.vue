<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { useToast } from '@/lib/toast';
import { formatDate, todayIso, titleCase } from '@/lib/helpers';
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
import ExamController from '@/actions/App/Http/Controllers/Admin/ExamController';
import ExamScheduleController from '@/actions/App/Http/Controllers/Admin/ExamScheduleController';

type Paginator<T> = {
    data: T[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
};

type Exam = {
    id: number;
    name: string;
    description: string | null;
    session: { id: number; name: string } | null;
    session_id: number;
    starts_at: string;
    ends_at: string;
    status: string;
    schedules_count: number;
};

const props = defineProps<{
    exams: Paginator<Exam>;
    sessions: Array<{ id: number; name: string }>;
    filters: { search: string | null; status: string | null };
}>();

defineOptions({ layout: AppLayout });

const toast = useToast();

const sessionOptions = props.sessions.map((session) => ({ value: session.id, label: session.name }));

const statusOptions = [
    { value: 'draft', label: 'Draft' },
    { value: 'published', label: 'Published' },
    { value: 'completed', label: 'Completed' },
];

const status = ref(props.filters.status ?? '');

watch(
    () => props.filters,
    (filters) => {
        status.value = filters.status ?? '';
    },
);

function go(field: string, value: string | number | null | undefined) {
    router.get(window.location.pathname, { ...props.filters, [field]: value || undefined }, { preserveState: true, preserveScroll: true, replace: true });
}

function statusTone(status: string): 'slate' | 'indigo' | 'green' {
    const map: Record<string, 'slate' | 'indigo' | 'green'> = { draft: 'slate', published: 'indigo', completed: 'green' };
    return map[status] ?? 'slate';
}

function toLocal(value: string): string {
    return value ? value.replace(' ', 'T').slice(0, 16) : '';
}

const columns = [
    { key: 'name', label: 'Name' },
    { key: 'session', label: 'Session', value: (e: any) => e.session?.name ?? null },
    { key: 'starts_at', label: 'Starts', value: (e: any) => formatDate(e.starts_at) },
    { key: 'ends_at', label: 'Ends', value: (e: any) => formatDate(e.ends_at) },
    { key: 'schedules', label: 'Schedules' },
    { key: 'status', label: 'Status', value: (e: any) => titleCase(e.status) },
];

const examModalOpen = ref(false);
const editing = ref<Exam | null>(null);

const form = useForm<{
    name: string;
    description: string;
    session_id: number | null;
    starts_at: string;
    ends_at: string;
    status: string;
}>({
    name: '',
    description: '',
    session_id: null,
    starts_at: toLocal(new Date().toISOString()),
    ends_at: toLocal(new Date().toISOString()),
    status: 'draft',
});

function openCreate() {
    editing.value = null;
    form.clearErrors();
    form.reset();
    form.session_id = props.sessions[0]?.id ?? null;
    form.starts_at = toLocal(new Date().toISOString());
    form.ends_at = toLocal(new Date().toISOString());
    examModalOpen.value = true;
}

function openEdit(exam: Exam) {
    editing.value = exam;
    form.clearErrors();
    form.name = exam.name;
    form.description = exam.description ?? '';
    form.session_id = exam.session_id;
    form.starts_at = toLocal(exam.starts_at);
    form.ends_at = toLocal(exam.ends_at);
    form.status = exam.status;
    examModalOpen.value = true;
}

function submit() {
    if (editing.value) {
        form.put(ExamController.update.url({ exam: editing.value.id }), {
            preserveScroll: true,
            onSuccess: () => (examModalOpen.value = false),
        });
    } else {
        form.post(ExamController.store.url(), {
            preserveScroll: true,
            onSuccess: () => (examModalOpen.value = false),
        });
    }
}

async function destroy(exam: Exam) {
    const result = await toast.confirm({
        title: 'Delete exam?',
        text: `"${exam.name}" and its schedules will be permanently removed.`,
        confirmText: 'Delete',
    });

    if (result.isConfirmed) {
        router.delete(ExamController.destroy.url({ exam: exam.id }), { preserveScroll: true });
    }
}

const scheduleModalOpen = ref(false);
const scheduleFor = ref<Exam | null>(null);

const scheduleForm = useForm<{
    exam_id: number | null;
    subject_id: number | null;
    date: string;
    start_time: string;
    end_time: string;
    full_marks: number | null;
    pass_marks: number | null;
}>({
    exam_id: null,
    subject_id: null,
    date: todayIso(),
    start_time: '09:00',
    end_time: '12:00',
    full_marks: 100,
    pass_marks: 33,
});

function openSchedules(exam: Exam) {
    scheduleFor.value = exam;
    scheduleForm.clearErrors();
    scheduleForm.reset();
    scheduleForm.exam_id = exam.id;
    scheduleForm.date = todayIso();
    scheduleForm.start_time = '09:00';
    scheduleForm.end_time = '12:00';
    scheduleForm.full_marks = 100;
    scheduleForm.pass_marks = 33;
    scheduleModalOpen.value = true;
}

function submitSchedule() {
    scheduleForm.clearErrors();
    scheduleForm.post(ExamScheduleController.store.url({ exam: scheduleFor.value!.id }), {
        preserveScroll: true,
        onSuccess: () => {
            scheduleModalOpen.value = false;
            router.reload();
        },
    });
}
</script>

<template>
    <div>
        <Head title="Exams" />

        <PageHeader title="Exams" subtitle="Create and manage exam sessions.">
            <template #actions>
                <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700" @click="openCreate">
                    <Icon name="plus" class="h-4 w-4" />
                    Add Exam
                </button>
            </template>
        </PageHeader>

        <Card class="mb-4" :padded="false">
            <div class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center">
                <SearchInput :value="filters.search ?? ''" placeholder="Search by name..." />
                <div class="w-full sm:w-56">
                    <SelectInput :options="statusOptions" :allow-empty="true" placeholder="All statuses" :model-value="status" @update:model-value="status = String($event ?? ''); go('status', status)" />
                </div>
            </div>
        </Card>

        <Card :padded="false">
            <div v-if="exams.data.length === 0">
                <EmptyState message="No exams found." icon="exams" />
            </div>
            <DataTable
                v-else
                :rows="exams.data"
                :columns="columns"
                :row-key="(exam) => exam.id"
                :subtitle-value="(exam) => exam.description ?? null"
            >
                <template #cell-name="{ row }">
                    <div class="font-medium text-slate-900 dark:text-slate-100">{{ row.name }}</div>
                    <div v-if="row.description" class="mt-0.5 max-w-xs truncate text-xs text-slate-400">{{ row.description }}</div>
                </template>
                <template #cell-schedules="{ row }">
                    <Badge :label="`${row.schedules_count} schedules`" tone="sky" />
                </template>
                <template #cell-status="{ row }">
                    <Badge :label="titleCase(row.status)" :tone="statusTone(row.status)" />
                </template>
                <template #actions="{ row }">
                    <button class="rounded-lg px-2 py-1.5 text-xs font-semibold text-indigo-600 transition-colors hover:bg-indigo-50 dark:hover:bg-indigo-950/40" @click="openSchedules(row)">
                        Schedules
                    </button>
                    <button class="rounded-lg p-2 text-slate-400 transition-colors hover:bg-slate-100 hover:text-indigo-600 dark:hover:bg-slate-800" @click="openEdit(row)">
                        <Icon name="edit" class="h-4 w-4" />
                    </button>
                    <button class="rounded-lg p-2 text-slate-400 transition-colors hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/40" @click="destroy(row)">
                        <Icon name="trash" class="h-4 w-4" />
                    </button>
                </template>
            </DataTable>
            <Pagination :paginator="exams" />
        </Card>

        <Modal :open="examModalOpen" :title="editing ? 'Edit Exam' : 'Add Exam'" :wide="true" @close="examModalOpen = false">
            <form @submit.prevent="submit" class="space-y-4">
                <Field label="Name" :error="form.errors.name" required>
                    <TextInput v-model="form.name" placeholder="e.g. Final Term Examination 2025" />
                </Field>
                <div class="grid gap-4 sm:grid-cols-2">
                    <Field label="Session" :error="form.errors.session_id" required>
                        <SelectInput v-model="form.session_id" :options="sessionOptions" :allow-empty="true" placeholder="Select session" />
                    </Field>
                    <Field label="Status" :error="form.errors.status" required>
                        <SelectInput v-model="form.status" :options="statusOptions" />
                    </Field>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <Field label="Starts At" :error="form.errors.starts_at" required>
                        <TextInput v-model="form.starts_at" type="datetime-local" />
                    </Field>
                    <Field label="Ends At" :error="form.errors.ends_at" required>
                        <TextInput v-model="form.ends_at" type="datetime-local" />
                    </Field>
                </div>
                <Field label="Description" :error="form.errors.description">
                    <TextareaInput v-model="form.description" placeholder="Optional details..." />
                </Field>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800" @click="examModalOpen = false">
                        Cancel
                    </button>
                    <SubmitButton :processing="form.processing">{{ editing ? 'Save Changes' : 'Create Exam' }}</SubmitButton>
                </div>
            </form>
        </Modal>

        <Modal :open="scheduleModalOpen" :title="`Add Schedule - ${scheduleFor?.name ?? ''}`" :wide="true" @close="scheduleModalOpen = false">
            <form @submit.prevent="submitSchedule" class="space-y-4">
                <p class="text-xs text-slate-500">Add a new subject schedule for this exam. Provide the subject ID from the Subjects page.</p>
                <div class="grid gap-4 sm:grid-cols-2">
                    <Field label="Subject ID" :error="scheduleForm.errors.subject_id" required>
                        <TextInput v-model="scheduleForm.subject_id" type="number" min="1" />
                    </Field>
                    <Field label="Date" :error="scheduleForm.errors.date" required>
                        <TextInput v-model="scheduleForm.date" type="date" />
                    </Field>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <Field label="Start Time" :error="scheduleForm.errors.start_time" required>
                        <TextInput v-model="scheduleForm.start_time" type="time" />
                    </Field>
                    <Field label="End Time" :error="scheduleForm.errors.end_time" required>
                        <TextInput v-model="scheduleForm.end_time" type="time" />
                    </Field>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <Field label="Full Marks" :error="scheduleForm.errors.full_marks" required>
                        <TextInput v-model="scheduleForm.full_marks" type="number" min="1" max="1000" />
                    </Field>
                    <Field label="Pass Marks" :error="scheduleForm.errors.pass_marks" required>
                        <TextInput v-model="scheduleForm.pass_marks" type="number" min="0" max="1000" />
                    </Field>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800" @click="scheduleModalOpen = false">
                        Cancel
                    </button>
                    <SubmitButton :processing="scheduleForm.processing">Add Schedule</SubmitButton>
                </div>
            </form>
        </Modal>
    </div>
</template>