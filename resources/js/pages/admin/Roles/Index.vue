<script setup lang="ts">
import { reactive, ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/components/ui/AppLayout.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import Card from '@/components/ui/Card.vue';
import Badge from '@/components/ui/Badge.vue';
import SubmitButton from '@/components/ui/SubmitButton.vue';
import Icon from '@/components/ui/Icon.vue';
import RoleController from '@/actions/App/Http/Controllers/Admin/RoleController';

defineOptions({ layout: AppLayout });

type Role = {
    id: number;
    name: string;
    guards: string;
    permissions: string[];
    users_count: number;
};

type PermissionGroup = {
    label: string;
    permissions: string[];
};

const props = defineProps<{
    roles: Role[];
    permissionGroups: PermissionGroup[];
}>();

const drafts = reactive<Record<number, string[]>>({});

const form = useForm<{ permissions: string[] }>({ permissions: [] });

const savingRoleId = ref<number | null>(null);

function draftFor(role: Role): string[] {
    if (!drafts[role.id]) {
        drafts[role.id] = [...role.permissions];
    }

    return drafts[role.id]!;
}

function toggle(role: Role, permission: string) {
    const draft = draftFor(role);
    const index = draft.indexOf(permission);

    if (index >= 0) {
        draft.splice(index, 1);
    } else {
        draft.push(permission);
    }
}

function toggleGroup(role: Role, group: PermissionGroup) {
    const draft = draftFor(role);
    const allSelected = group.permissions.every((permission) => draft.includes(permission));

    for (const permission of group.permissions) {
        const index = draft.indexOf(permission);
        if (allSelected && index >= 0) {
            draft.splice(index, 1);
        } else if (!allSelected && !draft.includes(permission)) {
            draft.push(permission);
        }
    }
}

async function save(role: Role) {
    form.permissions = draftFor(role);
    savingRoleId.value = role.id;

    form.put(
        RoleController.updatePermissions.url({ role: role.id }),
        {
            preserveScroll: true,
            onFinish: () => (savingRoleId.value = null),
        },
    );
}
</script>

<template>
    <div>
        <Head title="Roles & Permissions" />
        <PageHeader title="Roles & Permissions" subtitle="Assign granular permissions to each role." />

        <div v-if="roles.length === 0" class="grid gap-6">
            <Card>
                <div class="px-4 py-14 text-center text-sm text-slate-500 dark:text-slate-400">No roles found.</div>
            </Card>
        </div>
        <div v-else class="space-y-6">
            <Card v-for="role in roles" :key="role.id">
                <div class="mb-5 flex flex-wrap items-center gap-2">
                    <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100">{{ role.name }}</h2>
                    <Badge :label="role.guards" tone="indigo" />
                    <Badge :label="`${role.users_count} user${role.users_count !== 1 ? 's' : ''}`" tone="slate" />
                </div>

                <div class="space-y-5">
                    <div v-for="group in permissionGroups" :key="group.label" class="rounded-lg border border-slate-100 p-4 dark:border-slate-800">
                        <label class="mb-3 flex cursor-pointer items-center gap-2 text-[13px] font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">
                            <input
                                type="checkbox"
                                class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                :checked="group.permissions.every((permission) => draftFor(role).includes(permission))"
                                @change="toggleGroup(role, group)"
                            />
                            {{ group.label }}
                        </label>
                        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                            <label v-for="permission in group.permissions" :key="permission" class="flex cursor-pointer items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                                <input
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                    :checked="draftFor(role).includes(permission)"
                                    @change="toggle(role, permission)"
                                />
                                {{ permission }}
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <SubmitButton :processing="savingRoleId === role.id" @click.prevent="save(role)">Save permissions</SubmitButton>
                </div>
            </Card>
        </div>
    </div>
</template>