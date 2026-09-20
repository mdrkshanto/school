<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { useToast } from '@/lib/toast';
import { formatDateTime } from '@/lib/helpers';
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
import EventController from '@/actions/App/Http/Controllers/Admin/EventController';

defineOptions({ layout: AppLayout });

type Paginator<T> = {
    data: T[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
};

type Event = {
    id: number;
    title: string;
    description: string | null;
    location: string | null;
    starts_at: string;
    ends_at: string | null;
    color: string;
    is_public: boolean;
};

const props = defineProps<{
    events: Paginator<Event>;
    filters: { search: string | null; scope: string | null };
}>();

const toast = useToast();

const scopes = [
    { value: 'all', label: 'All' },
    { value: 'upcoming', label: 'Upcoming' },
    { value: 'past', label: 'Past' },
];

const colorChoices = [
    { value: 'indigo', label: 'Indigo' },
    { value: 'emerald', label: 'Emerald' },
    { value: 'rose', label: 'Rose' },
    { value: 'amber', label: 'Amber' },
    { value: 'sky', label: 'Sky' },
];

const colorClasses: Record<string, string> = {
    indigo: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-950/60 dark:text-indigo-300',
    emerald: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300',
    rose: 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300',
    amber: 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300',
    sky: 'bg-sky-100 text-sky-800 dark:bg-sky-950/60 dark:text-sky-300',
};

const modalOpen = ref(false);
const editing = ref<Event | null>(null);

const form = useForm<{
    title: string;
    description: string;
    starts_at: string;
    ends_at: string;
    location: string;
    color: string;
    is_public: boolean;
}>({
    title: '',
    description: '',
    starts_at: '',
    ends_at: '',
    location: '',
    color: 'indigo',
    is_public: true,
});

function toLocal(value: string | null | undefined): string {
    return value ? value.slice(0, 16) : '';
}

function openCreate() {
    editing.value = null;
    form.clearErrors();
    form.reset();
    modalOpen.value = true;
}

function openEdit(event: Event) {
    editing.value = event;
    form.clearErrors();
    form.title = event.title;
    form.description = event.description ?? '';
    form.starts_at = toLocal(event.starts_at);
    form.ends_at = toLocal(event.ends_at);
    form.location = event.location ?? '';
    form.color = event.color;
    form.is_public = event.is_public;
    modalOpen.value = true;
}

function submit() {
    if (editing.value) {
        form.put(EventController.update.url({ event: editing.value.id }), {
            preserveScroll: true,
            onSuccess: () => (modalOpen.value = false),
        });
    } else {
        form.post(EventController.store.url(), {
            preserveScroll: true,
            onSuccess: () => (modalOpen.value = false),
        });
    }
}

async function destroy(event: Event) {
    const result = await toast.confirm({
        title: 'Delete event?',
        text: `"${event.title}" will be permanently removed.`,
        confirmText: 'Delete',
    });

    if (result.isConfirmed) {
        router.delete(EventController.destroy.url({ event: event.id }), { preserveScroll: true });
    }
}

function go(field: string, value: string | number | null | undefined) {
    router.get(window.location.pathname, { [field]: value || undefined }, { preserveState: true, preserveScroll: true, replace: true });
}
</script>

<template>
    <div>
        <Head title="Events" />
        <PageHeader title="Events" subtitle="Schedule school events, holidays, and public occasions.">
            <template #actions>
                <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700" @click="openCreate">
                    <Icon name="plus" class="h-4 w-4" />
                    New Event
                </button>
            </template>
        </PageHeader>

        <Card class="mb-4" :padded="false">
            <div class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center">
                <div class="inline-flex rounded-lg bg-slate-200/70 p-1 dark:bg-slate-800">
                    <button
                        v-for="scope in scopes"
                        :key="scope.value"
                        type="button"
                        class="rounded-md px-4 py-1.5 text-xs font-semibold transition-colors"
                        :class="(filters.scope ?? 'upcoming') === scope.value ? 'bg-white text-indigo-600 shadow-sm dark:bg-slate-900 dark:text-indigo-400' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200'"
                        @click="go('scope', scope.value)"
                    >
                        {{ scope.label }}
                    </button>
                </div>
                <div class="flex-1 sm:flex sm:justify-end">
                    <SearchInput :value="filters.search ?? ''" placeholder="Search by title..." />
                </div>
            </div>
        </Card>

        <Card :padded="false">
            <div v-if="events.data.length === 0">
                <EmptyState message="No events found." icon="events" />
            </div>
            <div v-else class="divide-y divide-slate-100 dark:divide-slate-800">
                <div v-for="event in events.data" :key="event.id" class="flex items-start gap-4 p-5 hover:bg-slate-50 dark:hover:bg-slate-800/50">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg" :class="colorClasses[event.color] ?? colorClasses.indigo">
                        <Icon name="events" class="h-5 w-5" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ event.title }}</h3>
                            <Badge v-if="event.is_public" label="Public" tone="green" />
                            <Badge v-else label="Private" tone="slate" />
                        </div>
                        <p v-if="event.description" class="mt-1 line-clamp-2 text-sm text-slate-500 dark:text-slate-400">{{ event.description }}</p>
                        <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-400">
                            <span>{{ formatDateTime(event.starts_at) }}</span>
                            <span v-if="event.ends_at">until {{ formatDateTime(event.ends_at) }}</span>
                            <span v-if="event.location">{{ event.location }}</span>
                        </div>
                    </div>
                    <div class="flex shrink-0 items-center gap-1">
                        <button class="rounded-lg p-2 text-slate-400 transition-colors hover:bg-slate-100 hover:text-indigo-600 dark:hover:bg-slate-800" @click="openEdit(event)">
                            <Icon name="edit" class="h-4 w-4" />
                        </button>
                        <button class="rounded-lg p-2 text-slate-400 transition-colors hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/40" @click="destroy(event)">
                            <Icon name="trash" class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </div>
            <Pagination :paginator="events" />
        </Card>

        <Modal :open="modalOpen" :title="editing ? 'Edit Event' : 'New Event'" @close="modalOpen = false">
            <form @submit.prevent="submit" class="space-y-4">
                <Field label="Title" :error="form.errors.title" required>
                    <TextInput v-model="form.title" placeholder="Event title" />
                </Field>
                <Field label="Description" :error="form.errors.description">
                    <TextareaInput v-model="form.description" :rows="4" placeholder="Optional details..." />
                </Field>
                <div class="grid gap-4 sm:grid-cols-2">
                    <Field label="Starts at" :error="form.errors.starts_at" required>
                        <TextInput v-model="form.starts_at" type="datetime-local" />
                    </Field>
                    <Field label="Ends at" :error="form.errors.ends_at">
                        <TextInput v-model="form.ends_at" type="datetime-local" />
                    </Field>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <Field label="Location" :error="form.errors.location">
                        <TextInput v-model="form.location" placeholder="Venue / location" />
                    </Field>
                    <Field label="Color" :error="form.errors.color" required>
                        <SelectInput v-model="form.color" :options="colorChoices" />
                    </Field>
                </div>
                <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                    <input v-model="form.is_public" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" />
                    This event is public
                </label>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800" @click="modalOpen = false">
                        Cancel
                    </button>
                    <SubmitButton :processing="form.processing">{{ editing ? 'Save Changes' : 'Create Event' }}</SubmitButton>
                </div>
            </form>
        </Modal>
    </div>
</template>