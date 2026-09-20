<script setup lang="ts">
import { computed, ref } from 'vue';
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
import SelectInput from '@/components/ui/SelectInput.vue';
import SubmitButton from '@/components/ui/SubmitButton.vue';
import SearchInput from '@/components/ui/SearchInput.vue';
import Icon from '@/components/ui/Icon.vue';
import DataTable from '@/components/ui/DataTable.vue';
import BookController from '@/actions/App/Http/Controllers/Admin/BookController';

defineOptions({ layout: AppLayout });

type Paginator<T> = {
    data: T[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
};

type Book = {
    id: number;
    title: string;
    author: string | null;
    isbn: string | null;
    publisher: string | null;
    category: string | null;
    description: string | null;
    quantity: number;
    available_count: number;
    status: string;
};

const props = defineProps<{
    books: Paginator<Book>;
    categories: string[];
    filters: { search: string | null; category: string | null };
}>();

const toast = useToast();

const categoryOptions = computed(() => props.categories.map((category) => ({ value: category, label: category })));

const statusOptions = [
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' },
];

const modalOpen = ref(false);
const editing = ref<Book | null>(null);

const form = useForm<{
    title: string;
    author: string;
    isbn: string;
    publisher: string;
    category: string;
    description: string;
    quantity: number | null;
    status: string;
}>({
    title: '',
    author: '',
    isbn: '',
    publisher: '',
    category: '',
    description: '',
    quantity: 1,
    status: 'active',
});

function openCreate() {
    editing.value = null;
    form.clearErrors();
    form.reset();
    form.quantity = 1;
    modalOpen.value = true;
}

function openEdit(book: Book) {
    editing.value = book;
    form.clearErrors();
    form.title = book.title;
    form.author = book.author ?? '';
    form.isbn = book.isbn ?? '';
    form.publisher = book.publisher ?? '';
    form.category = book.category ?? '';
    form.description = book.description ?? '';
    form.quantity = book.quantity;
    form.status = book.status;
    modalOpen.value = true;
}

function submit() {
    if (editing.value) {
        form.put(BookController.update.url({ book: editing.value.id }), {
            preserveScroll: true,
            onSuccess: () => (modalOpen.value = false),
        });
    } else {
        form.post(BookController.store.url(), {
            preserveScroll: true,
            onSuccess: () => (modalOpen.value = false),
        });
    }
}

async function destroy(book: Book) {
    const result = await toast.confirm({
        title: 'Remove book?',
        text: `"${book.title}" will be permanently removed from the library.`,
        confirmText: 'Remove',
    });

    if (result.isConfirmed) {
        router.delete(BookController.destroy.url({ book: book.id }), { preserveScroll: true });
    }
}

function go(field: string, value: string | number | null | undefined) {
    router.get(window.location.pathname, { [field]: value || undefined }, { preserveState: true, preserveScroll: true, replace: true });
}

const columns = [
    { key: 'title', label: 'Title' },
    { key: 'author', label: 'Author' },
    { key: 'category', label: 'Category' },
    { key: 'isbn', label: 'ISBN' },
    { key: 'copies', label: 'Copies' },
    { key: 'status', label: 'Status' },
];
</script>

<template>
    <div>
        <Head title="Library" />
        <PageHeader title="Library" subtitle="Manage the school library's book collection.">
            <template #actions>
                <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700" @click="openCreate">
                    <Icon name="plus" class="h-4 w-4" />
                    Add Book
                </button>
            </template>
        </PageHeader>

        <Card class="mb-4" :padded="false">
            <div class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center">
                <SearchInput :value="filters.search ?? ''" placeholder="Search title, author, or ISBN..." />
                <div class="w-full sm:w-56">
                    <SelectInput
                        :options="categoryOptions"
                        :allow-empty="true"
                        placeholder="All categories"
                        :model-value="filters.category ?? ''"
                        @update:model-value="go('category', $event ?? '')"
                    />
                </div>
            </div>
        </Card>

        <Card :padded="false">
            <div v-if="books.data.length === 0">
                <EmptyState message="No books found." icon="library" />
            </div>
            <DataTable
                v-else
                :rows="books.data"
                :columns="columns"
                :row-key="(book) => book.id"
                :subtitle-value="(book) => book.publisher ?? null"
            >
                <template #cell-title="{ row }">
                    <div class="font-medium text-slate-900 dark:text-slate-100">{{ row.title }}</div>
                    <div v-if="row.publisher" class="text-xs text-slate-400">{{ row.publisher }}</div>
                </template>
                <template #cell-category="{ row }">
                    <Badge v-if="row.category" :label="row.category" tone="sky" />
                    <span v-else class="text-slate-400">—</span>
                </template>
                <template #cell-copies="{ row }">
                    <div class="font-medium text-slate-700 dark:text-slate-200">{{ row.quantity }}</div>
                    <div class="text-xs text-slate-400">{{ row.available_count }} available</div>
                </template>
                <template #cell-status="{ row }">
                    <Badge :label="row.status" :tone="row.status === 'active' ? 'green' : 'slate'" />
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
            <Pagination :paginator="books" />
        </Card>

        <Modal :open="modalOpen" :title="editing ? 'Edit Book' : 'Add Book'" @close="modalOpen = false">
            <form @submit.prevent="submit" class="space-y-4">
                <Field label="Title" :error="form.errors.title" required>
                    <TextInput v-model="form.title" placeholder="Book title" />
                </Field>
                <div class="grid gap-4 sm:grid-cols-2">
                    <Field label="Author" :error="form.errors.author">
                        <TextInput v-model="form.author" placeholder="Author name" />
                    </Field>
                    <Field label="ISBN" :error="form.errors.isbn">
                        <TextInput v-model="form.isbn" placeholder="ISBN" />
                    </Field>
                </div>
                <div class="grid gap-4 sm:grid-cols-3">
                    <Field label="Publisher" :error="form.errors.publisher">
                        <TextInput v-model="form.publisher" placeholder="Publisher" />
                    </Field>
                    <Field label="Category" :error="form.errors.category">
                        <SelectInput v-model="form.category" :options="categoryOptions" :allow-empty="true" placeholder="Select category" />
                    </Field>
                    <Field label="Quantity" :error="form.errors.quantity" required>
                        <TextInput v-model="form.quantity" type="number" min="1" />
                    </Field>
                </div>
                <div class="grid gap-4 sm:grid-cols-1">
                    <Field label="Status" :error="form.errors.status" required>
                        <SelectInput v-model="form.status" :options="statusOptions" />
                    </Field>
                </div>
                <Field label="Description" :error="form.errors.description">
                    <TextareaInput v-model="form.description" :rows="3" placeholder="Optional description..." />
                </Field>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800" @click="modalOpen = false">
                        Cancel
                    </button>
                    <SubmitButton :processing="form.processing">{{ editing ? 'Save Changes' : 'Add Book' }}</SubmitButton>
                </div>
            </form>
        </Modal>
    </div>
</template>