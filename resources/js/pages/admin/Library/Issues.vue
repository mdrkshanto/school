<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { useToast } from '@/lib/toast';
import { formatDate, todayIso } from '@/lib/helpers';
import AppLayout from '@/components/ui/AppLayout.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import Card from '@/components/ui/Card.vue';
import Badge from '@/components/ui/Badge.vue';
import Modal from '@/components/ui/Modal.vue';
import Pagination from '@/components/ui/Pagination.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import Field from '@/components/ui/Field.vue';
import TextInput from '@/components/ui/TextInput.vue';
import SelectInput from '@/components/ui/SelectInput.vue';
import SubmitButton from '@/components/ui/SubmitButton.vue';
import SearchInput from '@/components/ui/SearchInput.vue';
import Icon from '@/components/ui/Icon.vue';
import DataTable from '@/components/ui/DataTable.vue';
import BookIssueController from '@/actions/App/Http/Controllers/Admin/BookIssueController';

defineOptions({ layout: AppLayout });

type Paginator<T> = {
    data: T[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
};

type BookIssueRow = {
    id: number;
    issued_at: string;
    due_date: string;
    returned_at: string | null;
    status: string;
    book: { id: number; title: string; isbn: string };
    user: { id: number; name: string; email: string };
};

const props = defineProps<{
    issues: Paginator<BookIssueRow>;
    books: Array<{ id: number; title: string; available_count: number }>;
    users: Array<{ id: number; name: string; email: string }>;
    filters: { status: string | null; search: string | null };
}>();

const toast = useToast();

const bookOptions = props.books.map((book) => ({
    value: book.id,
    label: `${book.title} (${book.available_count} available)`,
}));

const userOptions = props.users.map((user) => ({
    value: user.id,
    label: `${user.name}${user.email ? ` — ${user.email}` : ''}`,
}));

const statusOptions = [
    { value: 'issued', label: 'Issued' },
    { value: 'returned', label: 'Returned' },
    { value: 'overdue', label: 'Overdue' },
];

const issueOpen = ref(false);
const issueForm = useForm<{ book_id: number | null; user_id: number | null; due_date: string }>({
    book_id: null,
    user_id: null,
    due_date: todayIso(),
});

function openIssue() {
    issueForm.clearErrors();
    issueForm.reset();
    issueForm.due_date = todayIso();
    issueOpen.value = true;
}

function issue() {
    issueForm.post(BookIssueController.store.url(), {
        preserveScroll: true,
        onSuccess: () => (issueOpen.value = false),
    });
}

function statusTone(status: string): 'indigo' | 'green' | 'red' {
    if (status === 'returned') return 'green';
    if (status === 'overdue') return 'red';
    return 'indigo';
}

async function returnBook(issue: BookIssueRow) {
    const result = await toast.confirm({
        title: 'Return this book?',
        text: `Mark "${issue.book?.title ?? 'this book'}" as returned.`,
        confirmText: 'Return',
    });

    if (result.isConfirmed) {
        router.post(BookIssueController.returnBook.url({ issue: issue.id }), { returned_at: todayIso() }, { preserveScroll: true });
    }
}

function go(field: string, value: string | number | null | undefined) {
    router.get(window.location.pathname, { [field]: value || undefined }, { preserveState: true, preserveScroll: true, replace: true });
}

const columns = [
    { key: 'book', label: 'Book' },
    { key: 'user', label: 'Issued to' },
    { key: 'issued_at', label: 'Issued at', value: (i: any) => formatDate(i.issued_at) },
    { key: 'due_date', label: 'Due date', value: (i: any) => formatDate(i.due_date) },
    { key: 'status', label: 'Status' },
];
</script>

<template>
    <div>
        <Head title="Book Issues" />
        <PageHeader title="Book Issues" subtitle="Track books issued to members and process returns.">
            <template #actions>
                <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700" @click="openIssue">
                    <Icon name="plus" class="h-4 w-4" />
                    Issue book
                </button>
            </template>
        </PageHeader>

        <Card class="mb-4" :padded="false">
            <div class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center">
                <SearchInput :value="filters.search ?? ''" placeholder="Search book or member..." />
                <div class="w-full sm:w-56">
                    <SelectInput
                        :options="statusOptions"
                        :allow-empty="true"
                        placeholder="All statuses"
                        :model-value="filters.status ?? ''"
                        @update:model-value="go('status', $event ?? '')"
                    />
                </div>
            </div>
        </Card>

        <Card :padded="false">
            <div v-if="issues.data.length === 0">
                <EmptyState message="No book issues found." icon="issues" />
            </div>
            <DataTable
                v-else
                :rows="issues.data"
                :columns="columns"
                :row-key="(issue) => issue.id"
                :title-value="(issue) => issue.book?.title ?? '—'"
                :subtitle-value="(issue) => issue.book?.isbn ?? null"
            >
                <template #cell-book="{ row }">
                    <div class="font-medium text-slate-900 dark:text-slate-100">{{ row.book?.title ?? '—' }}</div>
                    <div v-if="row.book?.isbn" class="text-xs text-slate-400">{{ row.book.isbn }}</div>
                </template>
                <template #cell-user="{ row }">
                    <div class="font-medium text-slate-700 dark:text-slate-200">{{ row.user?.name ?? '—' }}</div>
                    <div class="text-xs text-slate-400">{{ row.user?.email ?? '' }}</div>
                </template>
                <template #cell-status="{ row }">
                    <Badge :label="row.status" :tone="statusTone(row.status)" />
                </template>
                <template #actions="{ row }">
                    <button
                        v-if="row.status === 'issued' || row.status === 'overdue'"
                        class="rounded-lg px-2 py-1.5 text-xs font-semibold text-indigo-600 transition-colors hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-950/40"
                        @click="returnBook(row)"
                    >
                        Return
                    </button>
                    <span v-else class="text-xs text-slate-400">—</span>
                </template>
            </DataTable>
            <Pagination :paginator="issues" />
        </Card>

        <Modal :open="issueOpen" title="Issue Book" @close="issueOpen = false">
            <form @submit.prevent="issue" class="space-y-4">
                <Field label="Book" :error="issueForm.errors.book_id" required>
                    <SelectInput v-model="issueForm.book_id" :options="bookOptions" :allow-empty="true" placeholder="Select book" />
                </Field>
                <Field label="Member" :error="issueForm.errors.user_id" required>
                    <SelectInput v-model="issueForm.user_id" :options="userOptions" :allow-empty="true" placeholder="Select member" />
                </Field>
                <Field label="Due date" :error="issueForm.errors.due_date">
                    <TextInput v-model="issueForm.due_date" type="date" />
                </Field>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800" @click="issueOpen = false">
                        Cancel
                    </button>
                    <SubmitButton :processing="issueForm.processing">Issue Book</SubmitButton>
                </div>
            </form>
        </Modal>
    </div>
</template>