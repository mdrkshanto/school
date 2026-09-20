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
import SelectInput from '@/components/ui/SelectInput.vue';
import SubmitButton from '@/components/ui/SubmitButton.vue';
import SearchInput from '@/components/ui/SearchInput.vue';
import Icon from '@/components/ui/Icon.vue';
import DataTable from '@/components/ui/DataTable.vue';
import VehicleController from '@/actions/App/Http/Controllers/Admin/VehicleController';

defineOptions({ layout: AppLayout });

type Paginator<T> = {
    data: T[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
};

type Vehicle = {
    id: number;
    registration_no: string;
    model: string | null;
    capacity: number;
    driver_name: string | null;
    driver_phone: string | null;
    status: string;
    routes_count: number;
};

const props = defineProps<{
    vehicles: Paginator<Vehicle>;
    filters: { search: string | null; status: string | null };
}>();

const toast = useToast();

const statusOptions = [
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' },
];

const modalOpen = ref(false);
const editing = ref<Vehicle | null>(null);

const form = useForm<{
    registration_no: string;
    model: string;
    capacity: number | null;
    driver_name: string;
    driver_phone: string;
    status: string;
}>({
    registration_no: '',
    model: '',
    capacity: null,
    driver_name: '',
    driver_phone: '',
    status: 'active',
});

function openCreate() {
    editing.value = null;
    form.clearErrors();
    form.reset();
    form.capacity = 4;
    modalOpen.value = true;
}

function openEdit(vehicle: Vehicle) {
    editing.value = vehicle;
    form.clearErrors();
    form.registration_no = vehicle.registration_no;
    form.model = vehicle.model ?? '';
    form.capacity = vehicle.capacity;
    form.driver_name = vehicle.driver_name ?? '';
    form.driver_phone = vehicle.driver_phone ?? '';
    form.status = vehicle.status;
    modalOpen.value = true;
}

function submit() {
    if (editing.value) {
        form.put(VehicleController.update.url({ vehicle: editing.value.id }), {
            preserveScroll: true,
            onSuccess: () => (modalOpen.value = false),
        });
    } else {
        form.post(VehicleController.store.url(), {
            preserveScroll: true,
            onSuccess: () => (modalOpen.value = false),
        });
    }
}

async function destroy(vehicle: Vehicle) {
    const result = await toast.confirm({
        title: 'Remove vehicle?',
        text: `Vehicle "${vehicle.registration_no}" will be permanently removed.`,
        confirmText: 'Remove',
    });

    if (result.isConfirmed) {
        router.delete(VehicleController.destroy.url({ vehicle: vehicle.id }), { preserveScroll: true });
    }
}

function go(field: string, value: string | number | null | undefined) {
    router.get(window.location.pathname, { [field]: value || undefined }, { preserveState: true, preserveScroll: true, replace: true });
}

const columns = [
    { key: 'registration_no', label: 'Vehicle' },
    { key: 'model', label: 'Model' },
    { key: 'capacity', label: 'Capacity' },
    { key: 'driver', label: 'Driver' },
    { key: 'routes', label: 'Routes' },
    { key: 'status', label: 'Status' },
];
</script>

<template>
    <div>
        <Head title="Vehicles" />
        <PageHeader title="Vehicles" subtitle="Manage the transport fleet.">
            <template #actions>
                <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700" @click="openCreate">
                    <Icon name="plus" class="h-4 w-4" />
                    Add Vehicle
                </button>
            </template>
        </PageHeader>

        <Card class="mb-4" :padded="false">
            <div class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center">
                <SearchInput :value="filters.search ?? ''" placeholder="Search registration or name..." />
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
            <div v-if="vehicles.data.length === 0">
                <EmptyState message="No vehicles found." icon="vehicles" />
            </div>
            <DataTable
                v-else
                :rows="vehicles.data"
                :columns="columns"
                :row-key="(vehicle) => vehicle.id"
                :subtitle-value="(vehicle) => vehicle.model ?? null"
            >
                <template #cell-driver="{ row }">
                    <div class="text-slate-600 dark:text-slate-300">{{ row.driver_name ?? '—' }}</div>
                    <div v-if="row.driver_phone" class="text-xs text-slate-400">{{ row.driver_phone }}</div>
                </template>
                <template #cell-routes="{ row }">
                    <Badge :label="`${row.routes_count} route${row.routes_count !== 1 ? 's' : ''}`" tone="indigo" />
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
            <Pagination :paginator="vehicles" />
        </Card>

        <Modal :open="modalOpen" :title="editing ? 'Edit Vehicle' : 'Add Vehicle'" @close="modalOpen = false">
            <form @submit.prevent="submit" class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <Field label="Registration no." :error="form.errors.registration_no" required>
                        <TextInput v-model="form.registration_no" placeholder="e.g. Dhaka Metro-Ga 1234" />
                    </Field>
                    <Field label="Model" :error="form.errors.model">
                        <TextInput v-model="form.model" placeholder="e.g. Hino AK1J" />
                    </Field>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <Field label="Capacity" :error="form.errors.capacity" required>
                        <TextInput v-model="form.capacity" type="number" min="1" />
                    </Field>
                    <Field label="Status" :error="form.errors.status" required>
                        <SelectInput v-model="form.status" :options="statusOptions" />
                    </Field>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <Field label="Driver name" :error="form.errors.driver_name">
                        <TextInput v-model="form.driver_name" placeholder="Driver name" />
                    </Field>
                    <Field label="Driver phone" :error="form.errors.driver_phone">
                        <TextInput v-model="form.driver_phone" placeholder="Driver phone" />
                    </Field>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800" @click="modalOpen = false">
                        Cancel
                    </button>
                    <SubmitButton :processing="form.processing">{{ editing ? 'Save Changes' : 'Add Vehicle' }}</SubmitButton>
                </div>
            </form>
        </Modal>
    </div>
</template>