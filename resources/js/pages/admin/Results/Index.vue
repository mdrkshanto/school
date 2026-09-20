<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/components/ui/AppLayout.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import Card from '@/components/ui/Card.vue';
import Modal from '@/components/ui/Modal.vue';
import Pagination from '@/components/ui/Pagination.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import Field from '@/components/ui/Field.vue';
import SelectInput from '@/components/ui/SelectInput.vue';
import SubmitButton from '@/components/ui/SubmitButton.vue';
import SearchInput from '@/components/ui/SearchInput.vue';
import Icon from '@/components/ui/Icon.vue';
import DataTable from '@/components/ui/DataTable.vue';
import ResultController from '@/actions/App/Http/Controllers/Admin/ResultController';

type Paginator<T> = {
    data: T[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
};

type StudentRow = {
    id: number;
    name: string;
    admission_no: string;
    class: { id: number; name: string } | null;
    section: { id: number; name: string } | null;
    session: { id: number; name: string } | null;
};

const props = defineProps<{
    students: Paginator<StudentRow>;
    exams: Array<{ id: number; name: string }>;
    classes: Array<{ id: number; name: string }>;
    sections: Array<{ id: number; name: string; class_id: number }>;
    filters: { search: string | null; exam_id: string | null; class_id: string | null; section_id: string | null };
}>();

defineOptions({ layout: AppLayout });

const examOptions = props.exams.map((exam) => ({ value: exam.id, label: exam.name }));
const classOptions = props.classes.map((item) => ({ value: item.id, label: item.name }));

const examId = ref(props.filters.exam_id ?? '');
const classId = ref(props.filters.class_id ?? '');
const sectionId = ref(props.filters.section_id ?? '');

watch(
    () => props.filters,
    (filters) => {
        examId.value = filters.exam_id ?? '';
        classId.value = filters.class_id ?? '';
        sectionId.value = filters.section_id ?? '';
    },
);

function sectionsFor(classValue: string | number | null): Array<{ value: number; label: string }> {
    return props.sections
        .filter((section) => Number(classValue as string) === section.class_id)
        .map((section) => ({ value: section.id, label: section.name }));
}

function go(field: string, value: string | number | null | undefined) {
    router.get(window.location.pathname, { ...props.filters, [field]: value || undefined }, { preserveState: true, preserveScroll: true, replace: true });
}

const columns = [
    { key: 'student', label: 'Student', hideOnMobile: true },
    { key: 'class', label: 'Class', value: (s: any) => s.class?.name ?? null },
    { key: 'section', label: 'Section', value: (s: any) => s.section?.name ?? null },
    { key: 'session', label: 'Session', value: (s: any) => s.session?.name ?? null },
];

const generateModalOpen = ref(false);

const generateForm = useForm<{
    exam_id: number | null;
    class_id: number | null;
    section_id: number | null;
}>({
    exam_id: null,
    class_id: null,
    section_id: null,
});

const generateSections = computed(() => sectionsFor(generateForm.class_id));

function openGenerate() {
    generateForm.clearErrors();
    generateForm.reset();
    generateModalOpen.value = true;
}

function submitGenerate() {
    generateForm.clearErrors();
    generateForm.post(ResultController.generate.url(), {
        preserveScroll: true,
        onSuccess: () => (generateModalOpen.value = false),
    });
}

const entryModalOpen = ref(false);

const entryForm = useForm<{
    exam_id: number | null;
    class_id: number | null;
    section_id: number | null;
}>({
    exam_id: null,
    class_id: null,
    section_id: null,
});

const entrySections = computed(() => sectionsFor(entryForm.class_id));

function openEntry() {
    entryForm.clearErrors();
    entryForm.reset();
    entryModalOpen.value = true;
}

function goToEntry() {
    router.visit(
        ResultController.entry.url({
            query: {
                exam_id: String(entryForm.exam_id ?? ''),
                class_id: String(entryForm.class_id ?? ''),
                section_id: entryForm.section_id ? String(entryForm.section_id) : undefined,
            },
        }),
        { preserveScroll: true },
    );
}
</script>

<template>
    <div>
        <Head title="Results" />

        <PageHeader title="Results" subtitle="Generate result slots, enter marks, and view marksheets.">
            <template #actions>
                <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-700 ring-1 ring-inset ring-slate-300 transition-colors hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-200 dark:ring-slate-700 dark:hover:bg-slate-700" @click="openGenerate">
                    <Icon name="sparkles" class="h-4 w-4" />
                    Generate Slots
                </button>
                <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700" @click="openEntry">
                    <Icon name="edit" class="h-4 w-4" />
                    Score Entry
                </button>
            </template>
        </PageHeader>

        <Card class="mb-4" :padded="false">
            <div class="grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="lg:col-span-1">
                    <SearchInput :value="filters.search ?? ''" placeholder="Search name or admission no..." />
                </div>
                <div>
                    <SelectInput :options="examOptions" :allow-empty="true" placeholder="All exams" :model-value="examId" @update:model-value="examId = String($event ?? ''); go('exam_id', examId)" />
                </div>
                <div>
                    <SelectInput :options="classOptions" :allow-empty="true" placeholder="All classes" :model-value="classId" @update:model-value="classId = String($event ?? ''); go('class_id', classId)" />
                </div>
                <div>
                    <SelectInput :options="sectionsFor(classId)" :allow-empty="true" placeholder="All sections" :model-value="sectionId" @update:model-value="sectionId = String($event ?? ''); go('section_id', sectionId)" />
                </div>
            </div>
        </Card>

        <Card :padded="false">
            <div v-if="students.data.length === 0">
                <EmptyState message="No students found." icon="results" />
            </div>
            <DataTable
                v-else
                :rows="students.data"
                :columns="columns"
                :row-key="(student) => student.id"
                :title-value="(student) => student.name"
                :subtitle-value="(student) => 'Adm. ' + student.admission_no"
                actions-label="Marksheet"
            >
                <template #cell-student="{ row }">
                    <div class="font-medium text-slate-900 dark:text-slate-100">{{ row.name }}</div>
                    <div class="text-xs text-slate-400">Adm. {{ row.admission_no }}</div>
                </template>
                <template #actions="{ row }">
                    <Link
                        v-if="filters.exam_id"
                        :href="ResultController.marksheet.url({ student: row.id, exam: Number(filters.exam_id) })"
                        class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-semibold text-indigo-600 transition-colors hover:bg-indigo-50 dark:hover:bg-indigo-950/40"
                    >
                        <Icon name="pdf" class="h-3.5 w-3.5" />
                        Marksheet
                    </Link>
                    <span v-else class="text-xs text-slate-300">—</span>
                </template>
            </DataTable>
            <Pagination :paginator="students" />
        </Card>

        <Modal :open="generateModalOpen" title="Generate Result Slots" @close="generateModalOpen = false">
            <form @submit.prevent="submitGenerate" class="space-y-4">
                <Field label="Exam" :error="generateForm.errors.exam_id" required>
                    <SelectInput v-model="generateForm.exam_id" :options="examOptions" :allow-empty="true" placeholder="Select exam" />
                </Field>
                <Field label="Class" :error="generateForm.errors.class_id" required>
                    <SelectInput v-model="generateForm.class_id" :options="classOptions" :allow-empty="true" placeholder="Select class" @update:model-value="generateForm.section_id = null" />
                </Field>
                <Field label="Section" :error="generateForm.errors.section_id">
                    <SelectInput v-model="generateForm.section_id" :options="generateSections" :allow-empty="true" placeholder="All sections" />
                </Field>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800" @click="generateModalOpen = false">
                        Cancel
                    </button>
                    <SubmitButton :processing="generateForm.processing">Generate</SubmitButton>
                </div>
            </form>
        </Modal>

        <Modal :open="entryModalOpen" title="Score Entry" @close="entryModalOpen = false">
            <div class="space-y-4">
                <Field label="Exam" required>
                    <SelectInput v-model="entryForm.exam_id" :options="examOptions" :allow-empty="true" placeholder="Select exam" />
                </Field>
                <Field label="Class" required>
                    <SelectInput v-model="entryForm.class_id" :options="classOptions" :allow-empty="true" placeholder="Select class" @update:model-value="entryForm.section_id = null" />
                </Field>
                <Field label="Section">
                    <SelectInput v-model="entryForm.section_id" :options="entrySections" :allow-empty="true" placeholder="All sections" />
                </Field>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800" @click="entryModalOpen = false">
                        Cancel
                    </button>
                    <button type="button" class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700" :disabled="!entryForm.exam_id || !entryForm.class_id" @click="goToEntry">
                        Continue
                    </button>
                </div>
            </div>
        </Modal>
    </div>
</template>