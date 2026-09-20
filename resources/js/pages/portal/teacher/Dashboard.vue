<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppLayout from '@/components/ui/AppLayout.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import Card from '@/components/ui/Card.vue';
import Badge from '@/components/ui/Badge.vue';
import Icon from '@/components/ui/Icon.vue';
import { formatDate } from '@/lib/helpers';

defineOptions({ layout: AppLayout });

const props = defineProps<{
    data: {
        assignments: Array<{ class: string; section: string; is_class_teacher: boolean; students: number }>;
        today: string;
        recent_notices: Array<{ id: number; title: string; body: string; published_at: string }>;
        upcoming_events: Array<{ id: number; title: string; starts_at: string; location: string }>;
    };
}>();

const user = computed(() => usePage<any>().props.auth?.user);
</script>

<template>
    <div>
        <Head title="Dashboard" />
        <PageHeader :title="`Welcome, ${user?.name ?? 'Teacher'}`" />

        <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <Card v-for="(assignment, i) in data.assignments" :key="i">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ assignment.class }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Section: {{ assignment.section }}</p>
                    </div>
                    <Badge v-if="assignment.is_class_teacher" label="Class Teacher" tone="indigo" />
                </div>
                <div class="mt-3 flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                    <Icon name="students" class="h-4 w-4 text-slate-400" />
                    {{ assignment.students }} student{{ assignment.students !== 1 ? 's' : '' }}
                </div>
            </Card>
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
