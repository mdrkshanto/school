<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { useToast } from '@/lib/toast';
import { formatDate, initials } from '@/lib/helpers';
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
import UserController from '@/actions/App/Http/Controllers/Admin/UserController';

defineOptions({ layout: AppLayout });

type Paginator<T> = {
    data: T[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
};

type UserRow = {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    is_active: boolean;
    created_at: string;
    roles: Array<{ id: number; name: string }>;
};

const props = defineProps<{
    users: Paginator<UserRow>;
    roles: string[];
    filters: { search: string | null; role: string | null; active: string | null };
}>();

const page = usePage<any>();
const currentUser = page.props.auth?.user;

const toast = useToast();

const roleOptions = props.roles.map((role) => ({ value: role, label: role }));

const activeOptions = [
    { value: '1', label: 'Active' },
    { value: '0', label: 'Inactive' },
];

const modalOpen = ref(false);
const editing = ref<UserRow | null>(null);

const form = useForm<{
    name: string;
    email: string;
    password: string;
    phone: string;
    is_active: boolean;
    roles: string[];
}>({
    name: '',
    email: '',
    password: '',
    phone: '',
    is_active: true,
    roles: [],
});

function openCreate() {
    editing.value = null;
    form.clearErrors();
    form.reset();
    form.is_active = true;
    form.roles = [];
    modalOpen.value = true;
}

function openEdit(user: UserRow) {
    editing.value = user;
    form.clearErrors();
    form.name = user.name;
    form.email = user.email;
    form.password = '';
    form.phone = user.phone ?? '';
    form.is_active = user.is_active;
    form.roles = user.roles.map((role) => role.name);
    modalOpen.value = true;
}

function submit() {
    if (editing.value) {
        form.put(UserController.update.url({ user: editing.value.id }), {
            preserveScroll: true,
            onSuccess: () => (modalOpen.value = false),
        });
    } else {
        form.post(UserController.store.url(), {
            preserveScroll: true,
            onSuccess: () => (modalOpen.value = false),
        });
    }
}

function toggleRole(role: string) {
    const index = form.roles.indexOf(role);
    if (index >= 0) {
        form.roles.splice(index, 1);
    } else {
        form.roles.push(role);
    }
}

async function destroy(user: UserRow) {
    const result = await toast.confirm({
        title: 'Delete user?',
        text: `"${user.name}" will be permanently removed.`,
        confirmText: 'Delete',
    });

    if (result.isConfirmed) {
        router.delete(UserController.destroy.url({ user: user.id }), { preserveScroll: true });
    }
}

function go(field: string, value: string | number | null | undefined) {
    router.get(window.location.pathname, { [field]: value || undefined }, { preserveState: true, preserveScroll: true, replace: true });
}

const columns = [
    { key: 'user', label: 'User', hideOnMobile: true },
    { key: 'roles', label: 'Roles' },
    { key: 'status', label: 'Status', value: (u: any) => (u.is_active ? 'Active' : 'Inactive') },
    { key: 'created_at', label: 'Joined', value: (u: any) => formatDate(u.created_at) },
];
</script>

<template>
    <div>
        <Head title="Users" />
        <PageHeader title="Users" subtitle="Manage user accounts and their roles.">
            <template #actions>
                <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700" @click="openCreate">
                    <Icon name="plus" class="h-4 w-4" />
                    Add User
                </button>
            </template>
        </PageHeader>

        <Card class="mb-4" :padded="false">
            <div class="grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-3">
                <SearchInput :value="filters.search ?? ''" placeholder="Search name or email..." />
                <SelectInput
                    :options="roleOptions"
                    :allow-empty="true"
                    placeholder="All roles"
                    :model-value="filters.role ?? ''"
                    @update:model-value="go('role', $event ?? '')"
                />
                <SelectInput
                    :options="activeOptions"
                    :allow-empty="true"
                    placeholder="Any status"
                    :model-value="filters.active ?? ''"
                    @update:model-value="go('active', $event ?? '')"
                />
            </div>
        </Card>

        <Card :padded="false">
            <div v-if="users.data.length === 0">
                <EmptyState message="No users found." icon="users" />
            </div>
            <DataTable
                v-else
                :rows="users.data"
                :columns="columns"
                :row-key="(user) => user.id"
            >
                <template #title="{ row }">
                    <div class="flex items-center gap-2 font-semibold text-slate-900 dark:text-slate-100">
                        {{ row.name }}
                        <Badge v-if="row.id === currentUser?.id" label="You" tone="indigo" />
                    </div>
                </template>
                <template #subtitle="{ row }">
                    <div class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ row.email }}</div>
                </template>
                <template #cell-user="{ row }">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-semibold text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-300">
                            {{ initials(row.name) }}
                        </span>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 font-medium text-slate-900 dark:text-slate-100">
                                {{ row.name }}
                                <Badge v-if="row.id === currentUser?.id" label="You" tone="indigo" />
                            </div>
                            <div class="text-xs text-slate-400">{{ row.email }}</div>
                        </div>
                    </div>
                </template>
                <template #cell-roles="{ row }">
                    <div class="flex flex-wrap gap-1">
                        <Badge v-for="role in row.roles" :key="role.id" :label="role.name" tone="sky" />
                    </div>
                </template>
                <template #cell-status="{ row }">
                    <Badge :label="row.is_active ? 'Active' : 'Inactive'" :tone="row.is_active ? 'green' : 'red'" />
                </template>
                <template #actions="{ row }">
                    <button class="rounded-lg p-2 text-slate-400 transition-colors hover:bg-slate-100 hover:text-indigo-600 dark:hover:bg-slate-800" @click="openEdit(row)">
                        <Icon name="edit" class="h-4 w-4" />
                    </button>
                    <button
                        class="rounded-lg p-2 text-slate-400 transition-colors hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/40"
                        :disabled="row.id === currentUser?.id"
                        @click="destroy(row)"
                    >
                        <Icon name="trash" class="h-4 w-4" />
                    </button>
                </template>
            </DataTable>
            <Pagination :paginator="users" />
        </Card>

        <Modal :open="modalOpen" :title="editing ? 'Edit User' : 'Add User'" @close="modalOpen = false">
            <form @submit.prevent="submit" class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <Field label="Name" :error="form.errors.name" required>
                        <TextInput v-model="form.name" placeholder="Full name" />
                    </Field>
                    <Field label="Email" :error="form.errors.email" required>
                        <TextInput v-model="form.email" type="email" placeholder="user@school.test" />
                    </Field>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <Field label="Password" :error="form.errors.password" :hint="editing ? 'Leave blank to keep current password' : undefined" :required="!editing">
                        <TextInput v-model="form.password" type="password" placeholder="Minimum 8 characters" />
                    </Field>
                    <Field label="Phone" :error="form.errors.phone">
                        <TextInput v-model="form.phone" type="tel" placeholder="Phone number" />
                    </Field>
                </div>
                <Field label="Roles" :error="form.errors.roles" required>
                    <div class="space-y-2">
                        <label v-for="role in roles" :key="role" class="flex cursor-pointer items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                            <input
                                type="checkbox"
                                class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                :checked="form.roles.includes(role)"
                                @change="toggleRole(role)"
                            />
                            {{ role }}
                        </label>
                    </div>
                </Field>
                <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                    <input v-model="form.is_active" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" />
                    Account is active
                </label>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800" @click="modalOpen = false">
                        Cancel
                    </button>
                    <SubmitButton :processing="form.processing">{{ editing ? 'Save Changes' : 'Create User' }}</SubmitButton>
                </div>
            </form>
        </Modal>
    </div>
</template>