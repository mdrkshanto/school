<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { useToast } from '@/lib/toast';
import { formatDateTime, titleCase } from '@/lib/helpers';
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
import NoticeController from '@/actions/App/Http/Controllers/Admin/NoticeController';

defineOptions({ layout: AppLayout });

type Paginator<T> = {
    data: T[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
};

type Notice = {
    id: number;
    title: string;
    body: string;
    category: string;
    audience: string;
    published_at: string;
    author: { id: number; name: string };
};

const props = defineProps<{
    notices: Paginator<Notice>;
    filters: { search: string | null; audience: string | null };
}>();

const toast = useToast();

const categoryOptions = [
    { value: 'general', label: 'General' },
    { value: 'academic', label: 'Academic' },
    { value: 'urgent', label: 'Urgent' },
];

const audienceOptions = [
    { value: 'all', label: 'All' },
    { value: 'students', label: 'Students' },
    { value: 'teachers', label: 'Teachers' },
    { value: 'parents', label: 'Parents' },
];

const modalOpen = ref(false);
const editing = ref<Notice | null>(null);

const form = useForm<{
    title: string;
    body: string;
    category: string;
    audience: string;
    published_at: string;
}>({
    title: '',
    body: '',
    category: 'general',
    audience: 'all',
    published_at: '',
});

function openCreate() {
    editing.value = null;
    form.clearErrors();
    form.reset();
    form.published_at = new Date().toISOString().slice(0, 16);
    modalOpen.value = true;
}

function openEdit(notice: Notice) {
    editing.value = notice;
    form.clearErrors();
    form.title = notice.title;
    form.body = notice.body;
    form.category = notice.category;
    form.audience = notice.audience;
    form.published_at = notice.published_at ? notice.published_at.slice(0, 16) : '';
    modalOpen.value = true;
}

function submit() {
    if (editing.value) {
        form.put(NoticeController.update.url({ notice: editing.value.id }), {
            preserveScroll: true,
            onSuccess: () => (modalOpen.value = false),
        });
    } else {
        form.post(NoticeController.store.url(), {
            preserveScroll: true,
            onSuccess: () => (modalOpen.value = false),
        });
    }
}

async function destroy(notice: Notice) {
    const result = await toast.confirm({
        title: 'Delete notice?',
        text: `"${notice.title}" will be permanently removed.`,
        confirmText: 'Delete',
    });

    if (result.isConfirmed) {
        router.delete(NoticeController.destroy.url({ notice: notice.id }), { preserveScroll: true });
    }
}

function go(field: string, value: string | number | null | undefined) {
    router.get(window.location.pathname, { [field]: value || undefined }, { preserveState: true, preserveScroll: true, replace: true });
}
</script>

<template>
    <div>
        <Head title="Notices" />
        <PageHeader title="Notices" subtitle="Publish and manage notices for students, teachers, and parents.">
            <template #actions>
                <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700" @click="openCreate">
                    <Icon name="plus" class="h-4 w-4" />
                    New Notice
                </button>
            </template>
        </PageHeader>

        <Card class="mb-4" :padded="false">
            <div class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center">
                <SearchInput :value="filters.search ?? ''" placeholder="Search by title..." />
                <div class="w-full sm:w-56">
                    <SelectInput
                        :options="audienceOptions"
                        :allow-empty="true"
                        placeholder="All audiences"
                        :model-value="filters.audience ?? ''"
                        @update:model-value="go('audience', $event ?? '')"
                    />
                </div>
            </div>
        </Card>

        <Card :padded="false">
            <div v-if="notices.data.length === 0">
                <EmptyState message="No notices published yet." icon="notices" />
            </div>
            <div v-else class="divide-y divide-slate-100 dark:divide-slate-800">
                <div v-for="notice in notices.data" :key="notice.id" class="flex items-start gap-4 p-5 hover:bg-slate-50 dark:hover:bg-slate-800/50">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400">
                        <Icon name="notices" class="h-5 w-5" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ notice.title }}</h3>
                            <Badge :label="notice.category" :tone="notice.category === 'urgent' ? 'red' : notice.category === 'academic' ? 'indigo' : 'slate'" />
                            <Badge :label="notice.audience" :tone="notice.audience === 'all' ? 'green' : 'sky'" />
                        </div>
                        <p class="mt-1 line-clamp-2 text-sm text-slate-500 dark:text-slate-400">{{ notice.body }}</p>
                        <div class="mt-2 text-xs text-slate-400">
                            {{ formatDateTime(notice.published_at) }}<span v-if="notice.author?.name"> · {{ notice.author.name }}</span>
                        </div>
                    </div>
                    <div class="flex shrink-0 items-center gap-1">
                        <button class="rounded-lg p-2 text-slate-400 transition-colors hover:bg-slate-100 hover:text-indigo-600 dark:hover:bg-slate-800" @click="openEdit(notice)">
                            <Icon name="edit" class="h-4 w-4" />
                        </button>
                        <button class="rounded-lg p-2 text-slate-400 transition-colors hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/40" @click="destroy(notice)">
                            <Icon name="trash" class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </div>
            <Pagination :paginator="notices" />
        </Card>

        <Modal :open="modalOpen" :title="editing ? 'Edit Notice' : 'New Notice'" @close="modalOpen = false">
            <form @submit.prevent="submit" class="space-y-4">
                <Field label="Title" :error="form.errors.title" required>
                    <TextInput v-model="form.title" placeholder="Notice title" />
                </Field>
                <Field label="Body" :error="form.errors.body" required>
                    <TextareaInput v-model="form.body" :rows="5" placeholder="Notice content..." />
                </Field>
                <div class="grid gap-4 sm:grid-cols-2">
                    <Field label="Category" :error="form.errors.category" required>
                        <SelectInput v-model="form.category" :options="categoryOptions" />
                    </Field>
                    <Field label="Audience" :error="form.errors.audience" required>
                        <SelectInput v-model="form.audience" :options="audienceOptions" />
                    </Field>
                </div>
                <Field label="Published at" :error="form.errors.published_at">
                    <TextInput v-model="form.published_at" type="datetime-local" />
                </Field>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800" @click="modalOpen = false">
                        Cancel
                    </button>
                    <SubmitButton :processing="form.processing">{{ editing ? 'Save Changes' : 'Publish Notice' }}</SubmitButton>
                </div>
            </form>
        </Modal>
    </div>
</template>