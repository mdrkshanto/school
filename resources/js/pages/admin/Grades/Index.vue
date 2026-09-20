<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { useToast } from '@/lib/toast';
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
import SubmitButton from '@/components/ui/SubmitButton.vue';
import Icon from '@/components/ui/Icon.vue';
import DataTable from '@/components/ui/DataTable.vue';
import GradeRuleController from '@/actions/App/Http/Controllers/Admin/GradeRuleController';

type Paginator<T> = {
    data: T[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
};

type GradeRule = {
    id: number;
    name: string;
    grade_point: number;
    min_mark: number;
    max_mark: number;
    remark: string | null;
};

const props = defineProps<{
    gradeRules: Paginator<GradeRule>;
}>();

defineOptions({ layout: AppLayout });

const toast = useToast();

const columns = [
    { key: 'name', label: 'Name' },
    { key: 'grade_point', label: 'Grade Point' },
    { key: 'min_mark', label: 'Min Mark' },
    { key: 'max_mark', label: 'Max Mark' },
    { key: 'remark', label: 'Remark', truncate: true },
];

const modalOpen = ref(false);
const editing = ref<GradeRule | null>(null);

const form = useForm<{
    name: string;
    grade_point: string | null;
    min_mark: string | null;
    max_mark: string | null;
    remark: string;
}>({
    name: '',
    grade_point: null,
    min_mark: null,
    max_mark: null,
    remark: '',
});

function openCreate() {
    editing.value = null;
    form.clearErrors();
    form.reset();
    modalOpen.value = true;
}

function openEdit(rule: GradeRule) {
    editing.value = rule;
    form.clearErrors();
    form.name = rule.name;
    form.grade_point = String(rule.grade_point);
    form.min_mark = String(rule.min_mark);
    form.max_mark = String(rule.max_mark);
    form.remark = rule.remark ?? '';
    modalOpen.value = true;
}

function submit() {
    if (editing.value) {
        form.put(GradeRuleController.update.url({ gradeRule: editing.value.id }), {
            preserveScroll: true,
            onSuccess: () => (modalOpen.value = false),
        });
    } else {
        form.post(GradeRuleController.store.url(), {
            preserveScroll: true,
            onSuccess: () => (modalOpen.value = false),
        });
    }
}

async function destroy(rule: GradeRule) {
    const result = await toast.confirm({
        title: 'Delete grade rule?',
        text: `"${rule.name}" will be permanently removed.`,
        confirmText: 'Delete',
    });

    if (result.isConfirmed) {
        router.delete(GradeRuleController.destroy.url({ gradeRule: rule.id }), { preserveScroll: true });
    }
}
</script>

<template>
    <div>
        <Head title="Grade Rules" />

        <PageHeader title="Grade Rules" subtitle="Define grading scales used to compute results.">
            <template #actions>
                <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700" @click="openCreate">
                    <Icon name="plus" class="h-4 w-4" />
                    Add Grade
                </button>
            </template>
        </PageHeader>

        <Card :padded="false">
            <div v-if="gradeRules.data.length === 0">
                <EmptyState message="No grade rules found." icon="grades" />
            </div>
            <DataTable
                v-else
                :rows="gradeRules.data"
                :columns="columns"
                :row-key="(rule) => rule.id"
                :subtitle-value="(rule) => `Min ${rule.min_mark} — Max ${rule.max_mark}`"
            >
                <template #title="{ row }">
                    <Badge :label="row.name" tone="indigo" />
                </template>
                <template #cell-name="{ row }">
                    <Badge :label="row.name" tone="indigo" />
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
            <Pagination :paginator="gradeRules" />
        </Card>

        <Modal :open="modalOpen" :title="editing ? 'Edit Grade Rule' : 'Add Grade Rule'" @close="modalOpen = false">
            <form @submit.prevent="submit" class="space-y-4">
                <Field label="Name" :error="form.errors.name" required>
                    <TextInput v-model="form.name" placeholder="e.g. A+" />
                </Field>
                <div class="grid gap-4 sm:grid-cols-3">
                    <Field label="Grade Point" :error="form.errors.grade_point" required>
                        <TextInput v-model="form.grade_point" type="number" min="0" max="5" step="0.01" />
                    </Field>
                    <Field label="Min Mark" :error="form.errors.min_mark" required>
                        <TextInput v-model="form.min_mark" type="number" min="0" />
                    </Field>
                    <Field label="Max Mark" :error="form.errors.max_mark" required>
                        <TextInput v-model="form.max_mark" type="number" min="0" max="1000" />
                    </Field>
                </div>
                <Field label="Remark" :error="form.errors.remark">
                    <TextareaInput v-model="form.remark" placeholder="Optional details..." />
                </Field>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800" @click="modalOpen = false">
                        Cancel
                    </button>
                    <SubmitButton :processing="form.processing">{{ editing ? 'Save Changes' : 'Create Grade' }}</SubmitButton>
                </div>
            </form>
        </Modal>
    </div>
</template>