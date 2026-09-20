<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/components/ui/AppLayout.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import Card from '@/components/ui/Card.vue';
import Badge from '@/components/ui/Badge.vue';
import Icon from '@/components/ui/Icon.vue';
import { formatMoney, formatDate } from '@/lib/helpers';

defineOptions({ layout: AppLayout });

const props = defineProps<{
    data: {
        children: Array<{ id: number; name: string; admission_no: string; class: string; section: string; status: string; attendance_rate: number | null; fee_balance: number }>;
        recent_notices: Array<{ id: number; title: string; body: string; published_at: string }>;
        upcoming_events: Array<{ id: number; title: string; starts_at: string; location: string; color: string }>;
    };
}>();

const user = computed(() => usePage<any>().props.auth?.user);
</script>

<template>
    <div>
        <Head title="Dashboard" />
        <PageHeader :title="`Welcome, ${user?.name ?? 'Parent'}`" />

        <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <Link
                v-for="child in data.children"
                :key="child.id"
                :href="`/parent/children/${child.id}`"
                class="block rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-colors hover:border-indigo-300 hover:bg-indigo-50/50 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-indigo-700"
            >
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-base font-semibold text-slate-900 dark:text-slate-100">{{ child.name }}</p>
                        <p class="mt-0.5 text-xs text-slate-400">#{{ child.admission_no }}</p>
                    </div>
                    <Badge :label="child.status" :tone="child.status === 'active' ? 'green' : 'slate'" />
                </div>
                <div class="mt-3 space-y-1.5">
                    <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                        <Icon name="classes" class="h-4 w-4 text-slate-400" />
                        {{ child.class }} — {{ child.section }}
                    </div>
                    <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                        <Icon name="attendance" class="h-4 w-4 text-slate-400" />
                        Attendance: {{ child.attendance_rate !== null ? child.attendance_rate + '%' : 'N/A' }}
                    </div>
                    <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                        <Icon name="fees" class="h-4 w-4 text-slate-400" />
                        Balance: {{ formatMoney(child.fee_balance) }}
                    </div>
                </div>
            </Link>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <Card>
                <h3 class="mb-3 text-sm font-semibold text-slate-900 dark:text-slate-100">Recent Notices</h3>
                <div v-if="data.recent_notices.length === 0" class="py-4 text-center text-sm text-slate-400">No recent notices.</div>
                <ul v-else class="space-y-3">
                    <li v-for="notice in data.recent_notices" :key="notice.id" class="border-b border-slate-100 pb-3 last:border-0 last:pb-0 dark:border-slate-800">
                        <p class="text-sm font-medium text-slate-800 dark:text-slate-200">{{ notice.title }}</p>
                        <p class="mt-0.5 text-xs text-slate-400">{{ formatDate(notice.published_at) }}</p>
                    </li>
                </ul>
            </Card>

            <Card>
                <h3 class="mb-3 text-sm font-semibold text-slate-900 dark:text-slate-100">Upcoming Events</h3>
                <div v-if="data.upcoming_events.length === 0" class="py-4 text-center text-sm text-slate-400">No upcoming events.</div>
                <ul v-else class="space-y-3">
                    <li v-for="event in data.upcoming_events" :key="event.id" class="flex items-start gap-3 border-b border-slate-100 pb-3 last:border-0 last:pb-0 dark:border-slate-800">
                        <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400">
                            <Icon name="events" class="h-4 w-4" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-800 dark:text-slate-200">{{ event.title }}</p>
                            <p class="text-xs text-slate-400">{{ formatDate(event.starts_at) }}<span v-if="event.location"> &middot; {{ event.location }}</span></p>
                        </div>
                    </li>
                </ul>
            </Card>
        </div>
    </div>
</template>
