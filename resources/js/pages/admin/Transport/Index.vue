<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { useToast } from '@/lib/toast';
import { formatMoney } from '@/lib/helpers';
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
import TransportRouteController from '@/actions/App/Http/Controllers/Admin/TransportRouteController';
import TransportStopController from '@/actions/App/Http/Controllers/Admin/TransportStopController';

defineOptions({ layout: AppLayout });

type Paginator<T> = {
    data: T[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
};

type Stop = {
    id: number;
    name: string;
    route_id: number;
    order_index?: number;
    order?: number;
    fare: string;
};

type TransportRoute = {
    id: number;
    name: string;
    description: string | null;
    status: string;
    stops: Stop[];
    vehicles: Array<{ id: number; name?: string; vehicle_number?: string; registration_no?: string }>;
};

const props = defineProps<{
    routes: Paginator<TransportRoute>;
    vehicles: Array<{ id: number; name?: string; vehicle_number?: string }>;
    filters: { search: string | null };
}>();

const toast = useToast();

const vehicleOptions = computed(() =>
    props.vehicles.map((vehicle) => ({
        value: vehicle.id,
        label: String(vehicle.name ?? vehicle.vehicle_number ?? `Vehicle ${vehicle.id}`),
    })),
);

const statusOptions = [
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' },
];

function vehicleLabel(vehicle: { id: number; name?: string; vehicle_number?: string; registration_no?: string }): string {
    return String(vehicle.name ?? vehicle.vehicle_number ?? vehicle.registration_no ?? `Vehicle ${vehicle.id}`);
}

function stopOrder(stop: Stop): number {
    return Number(stop.order_index ?? stop.order ?? 0);
}

const routeModalOpen = ref(false);
const editingRoute = ref<TransportRoute | null>(null);

const routeForm = useForm<{ name: string; description: string; status: string; vehicle_ids: number[] }>({
    name: '',
    description: '',
    status: 'active',
    vehicle_ids: [],
});

function openCreateRoute() {
    editingRoute.value = null;
    routeForm.clearErrors();
    routeForm.reset();
    routeForm.vehicle_ids = [];
    routeModalOpen.value = true;
}

function openEditRoute(route: TransportRoute) {
    editingRoute.value = route;
    routeForm.clearErrors();
    routeForm.name = route.name;
    routeForm.description = route.description ?? '';
    routeForm.status = route.status;
    routeForm.vehicle_ids = route.vehicles.map((vehicle) => vehicle.id);
    routeModalOpen.value = true;
}

function submitRoute() {
    if (editingRoute.value) {
        routeForm.put(TransportRouteController.update.url({ route: editingRoute.value.id }), {
            preserveScroll: true,
            onSuccess: () => (routeModalOpen.value = false),
        });
    } else {
        routeForm.post(TransportRouteController.store.url(), {
            preserveScroll: true,
            onSuccess: () => (routeModalOpen.value = false),
        });
    }
}

function toggleVehicle(id: number) {
    const index = routeForm.vehicle_ids.indexOf(id);
    if (index >= 0) {
        routeForm.vehicle_ids.splice(index, 1);
    } else {
        routeForm.vehicle_ids.push(id);
    }
}

async function destroyRoute(route: TransportRoute) {
    const result = await toast.confirm({
        title: 'Delete route?',
        text: `"${route.name}" and its stops will be permanently removed.`,
        confirmText: 'Delete',
    });

    if (result.isConfirmed) {
        router.delete(TransportRouteController.destroy.url({ route: route.id }), { preserveScroll: true });
    }
}

const stopModalOpen = ref(false);
const stopRoute = ref<TransportRoute | null>(null);
const editingStop = ref<Stop | null>(null);

const stopForm = useForm<{ route_id: number | null; name: string; fare: string | null; order_index: number | null }>({
    route_id: null,
    name: '',
    fare: null,
    order_index: null,
});

function openAddStop(route: TransportRoute) {
    stopRoute.value = route;
    editingStop.value = null;
    stopForm.clearErrors();
    stopForm.reset();
    stopForm.route_id = route.id;
    stopForm.order_index = route.stops.length + 1;
    stopModalOpen.value = true;
}

function openEditStop(route: TransportRoute, stop: Stop) {
    stopRoute.value = route;
    editingStop.value = stop;
    stopForm.clearErrors();
    stopForm.route_id = stop.route_id;
    stopForm.name = stop.name;
    stopForm.fare = stop.fare;
    stopForm.order_index = stopOrder(stop) || route.stops.length;
    stopModalOpen.value = true;
}

function submitStop() {
    if (!stopRoute.value) return;

    if (editingStop.value) {
        stopForm.put(TransportStopController.update.url({ stop: editingStop.value.id }), {
            preserveScroll: true,
            onSuccess: () => (stopModalOpen.value = false),
        });
    } else {
        stopForm.post(TransportStopController.store.url({ route: stopRoute.value.id }), {
            preserveScroll: true,
            onSuccess: () => (stopModalOpen.value = false),
        });
    }
}

async function destroyStop(stop: Stop) {
    const result = await toast.confirm({
        title: 'Remove stop?',
        text: `"${stop.name}" will be removed from this route.`,
        confirmText: 'Remove',
    });

    if (result.isConfirmed) {
        router.delete(TransportStopController.destroy.url({ stop: stop.id }), { preserveScroll: true });
    }
}
</script>

<template>
    <div>
        <Head title="Transport Routes" />
        <PageHeader title="Transport Routes" subtitle="Manage bus routes, stops, and assigned vehicles.">
            <template #actions>
                <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700" @click="openCreateRoute">
                    <Icon name="plus" class="h-4 w-4" />
                    New Route
                </button>
            </template>
        </PageHeader>

        <Card class="mb-4" :padded="false">
            <div class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center">
                <SearchInput :value="filters.search ?? ''" placeholder="Search by name..." />
            </div>
        </Card>

        <div v-if="routes.data.length === 0">
            <Card>
                <EmptyState message="No transport routes found." icon="routes" />
            </Card>
        </div>
        <div v-else class="space-y-4">
            <Card v-for="route in routes.data" :key="route.id">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">{{ route.name }}</h3>
                            <Badge :label="route.status" :tone="route.status === 'active' ? 'green' : 'slate'" />
                        </div>
                        <p v-if="route.description" class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ route.description }}</p>
                        <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-400">
                            <span class="flex items-center gap-1">
                                <Icon name="routes" class="h-3.5 w-3.5" />
                                {{ route.stops.length }} stop{{ route.stops.length !== 1 ? 's' : '' }}
                            </span>
                            <span class="flex items-center gap-1">
                                <Icon name="vehicles" class="h-3.5 w-3.5" />
                                {{ route.vehicles.length }} vehicle{{ route.vehicles.length !== 1 ? 's' : '' }}
                            </span>
                        </div>
                    </div>
                    <div class="flex shrink-0 items-center gap-1">
                        <button class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-indigo-600 transition-colors hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-950/40" @click="openAddStop(route)">
                            Add stop
                        </button>
                        <button class="rounded-lg p-2 text-slate-400 transition-colors hover:bg-slate-100 hover:text-indigo-600 dark:hover:bg-slate-800" @click="openEditRoute(route)">
                            <Icon name="edit" class="h-4 w-4" />
                        </button>
                        <button class="rounded-lg p-2 text-slate-400 transition-colors hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/40" @click="destroyRoute(route)">
                            <Icon name="trash" class="h-4 w-4" />
                        </button>
                    </div>
                </div>

                <div v-if="route.vehicles.length > 0" class="mt-4 flex flex-wrap gap-2">
                    <Badge v-for="vehicle in route.vehicles" :key="vehicle.id" :label="vehicleLabel(vehicle)" tone="indigo" />
                </div>

                <div class="mt-4 rounded-lg border border-slate-100 bg-slate-50/60 dark:border-slate-800 dark:bg-slate-800/40">
                    <div v-if="route.stops.length === 0" class="px-4 py-3 text-sm text-slate-400">No stops added yet.</div>
                    <ul v-else class="divide-y divide-slate-100 dark:divide-slate-800">
                        <li v-for="stop in [...route.stops].sort((a, b) => stopOrder(a) - stopOrder(b))" :key="stop.id" class="flex items-center gap-3 px-4 py-2.5">
                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-[11px] font-bold text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300">
                                {{ stopOrder(stop) }}
                            </span>
                            <span class="flex-1 text-sm font-medium text-slate-700 dark:text-slate-200">{{ stop.name }}</span>
                            <span class="text-sm text-slate-500 dark:text-slate-400">{{ formatMoney(stop.fare) }}</span>
                            <div class="flex items-center gap-0.5">
                                <button class="rounded-md p-1.5 text-slate-400 transition-colors hover:bg-white hover:text-indigo-600 dark:hover:bg-slate-800" @click="openEditStop(route, stop)">
                                    <Icon name="edit" class="h-3.5 w-3.5" />
                                </button>
                                <button class="rounded-md p-1.5 text-slate-400 transition-colors hover:bg-white hover:text-red-600 dark:hover:bg-slate-800" @click="destroyStop(stop)">
                                    <Icon name="trash" class="h-3.5 w-3.5" />
                                </button>
                            </div>
                        </li>
                    </ul>
                </div>
            </Card>
            <Pagination :paginator="routes" />
        </div>

        <Modal :open="routeModalOpen" :title="editingRoute ? 'Edit Route' : 'New Route'" @close="routeModalOpen = false">
            <form @submit.prevent="submitRoute" class="space-y-4">
                <Field label="Name" :error="routeForm.errors.name" required>
                    <TextInput v-model="routeForm.name" placeholder="e.g. Mirpur - Gulshan" />
                </Field>
                <Field label="Description" :error="routeForm.errors.description">
                    <TextareaInput v-model="routeForm.description" :rows="3" placeholder="Optional details..." />
                </Field>
                <Field label="Status" :error="routeForm.errors.status" required>
                    <SelectInput v-model="routeForm.status" :options="statusOptions" />
                </Field>
                <Field label="Assigned vehicles">
                    <div class="space-y-2">
                        <label v-for="option in vehicleOptions" :key="option.value" class="flex cursor-pointer items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                            <input
                                type="checkbox"
                                class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                :checked="routeForm.vehicle_ids.includes(Number(option.value))"
                                @change="toggleVehicle(Number(option.value))"
                            />
                            {{ option.label }}
                        </label>
                        <p v-if="vehicleOptions.length === 0" class="text-xs text-slate-400">No active vehicles available.</p>
                    </div>
                </Field>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800" @click="routeModalOpen = false">
                        Cancel
                    </button>
                    <SubmitButton :processing="routeForm.processing">{{ editingRoute ? 'Save Changes' : 'Create Route' }}</SubmitButton>
                </div>
            </form>
        </Modal>

        <Modal :open="stopModalOpen" :title="editingStop ? 'Edit Stop' : 'Add Stop'" @close="stopModalOpen = false">
            <form @submit.prevent="submitStop" class="space-y-4">
                <p class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ stopRoute?.name }}</p>
                <Field label="Stop name" :error="stopForm.errors.name" required>
                    <TextInput v-model="stopForm.name" placeholder="e.g. Gulshan 2 Bus Stand" />
                </Field>
                <div class="grid gap-4 sm:grid-cols-2">
                    <Field label="Order" :error="stopForm.errors.order_index" hint="Sequence along the route">
                        <TextInput v-model="stopForm.order_index" type="number" min="1" />
                    </Field>
                    <Field label="Fare" :error="stopForm.errors.fare" required>
                        <TextInput v-model="stopForm.fare" type="number" min="0" step="0.01" />
                    </Field>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800" @click="stopModalOpen = false">
                        Cancel
                    </button>
                    <SubmitButton :processing="stopForm.processing">{{ editingStop ? 'Save Changes' : 'Add Stop' }}</SubmitButton>
                </div>
            </form>
        </Modal>
    </div>
</template>