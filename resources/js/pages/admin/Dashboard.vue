<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Bar, Line } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    BarElement,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler,
} from 'chart.js';
import AppLayout from '@/components/ui/AppLayout.vue';
import Card from '@/components/ui/Card.vue';
import Icon from '@/components/ui/Icon.vue';
import Badge from '@/components/ui/Badge.vue';
import { formatDate, formatMoney, titleCase } from '@/lib/helpers';

ChartJS.register(CategoryScale, LinearScale, BarElement, PointElement, LineElement, Title, Tooltip, Legend, Filler);

defineOptions({ layout: AppLayout });

const props = defineProps<{
    data: {
        totals: {
            students: number;
            teachers: number;
            guardians: number;
            classes: number;
            sessions: number;
            books: number;
        };
        attendance_today: {
            total: number;
            present: number;
            absent: number;
        };
        students_per_class: Record<string, number>;
        collections: Array<{ month: string; amount: number }>;
        fee_summary: {
            collected: number;
            outstanding: number;
        };
        recent_notices: Array<{
            id: number;
            title: string;
            category: string;
            audience: string;
            published_at: string | null;
        }>;
        upcoming_events: Array<{
            id: number;
            title: string;
            starts_at: string;
            location: string | null;
            color: string | null;
        }>;
    };
}>();

const totals = computed(() => props.data.totals);

const attendance = computed(() => props.data.attendance_today);

const attendanceRate = computed(() => {
    if (attendance.value.total === 0) return 0;
    return Math.round((attendance.value.present / attendance.value.total) * 100);
});

const statCards = computed(() => [
    { label: 'Students', value: totals.value.students, icon: 'students', color: 'bg-indigo-100 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400' },
    { label: 'Teachers', value: totals.value.teachers, icon: 'teachers', color: 'bg-emerald-100 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400' },
    { label: 'Parents', value: totals.value.guardians, icon: 'parents', color: 'bg-amber-100 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400' },
    { label: 'Classes', value: totals.value.classes, icon: 'classes', color: 'bg-sky-100 text-sky-600 dark:bg-sky-950/50 dark:text-sky-400' },
    { label: 'Sessions', value: totals.value.sessions, icon: 'sessions', color: 'bg-purple-100 text-purple-600 dark:bg-purple-950/50 dark:text-purple-400' },
    { label: 'Books', value: totals.value.books, icon: 'library', color: 'bg-rose-100 text-rose-600 dark:bg-rose-950/50 dark:text-rose-400' },
]);

const barChartData = computed(() => ({
    labels: Object.keys(props.data.students_per_class),
    datasets: [
        {
            label: 'Students',
            data: Object.values(props.data.students_per_class),
            backgroundColor: 'rgba(99, 102, 241, 0.8)',
            borderColor: 'rgb(99, 102, 241)',
            borderWidth: 1,
            borderRadius: 6,
        },
    ],
}));

const barChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: { stepSize: 1, color: '#94a3b8' },
            grid: { color: 'rgba(148,163,184,0.1)' },
        },
        x: {
            ticks: { color: '#94a3b8' },
            grid: { display: false },
        },
    },
};

const lineChartData = computed(() => ({
    labels: props.data.collections.map((c) => c.month),
    datasets: [
        {
            label: 'Collections',
            data: props.data.collections.map((c) => c.amount),
            borderColor: 'rgb(16, 185, 129)',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            fill: true,
            tension: 0.4,
            pointBackgroundColor: 'rgb(16, 185, 129)',
            pointBorderWidth: 0,
            pointRadius: 4,
        },
    ],
}));

const lineChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                color: '#94a3b8',
                callback: (value: number | string) => '$' + Number(value).toLocaleString(),
            },
            grid: { color: 'rgba(148,163,184,0.1)' },
        },
        x: {
            ticks: { color: '#94a3b8' },
            grid: { display: false },
        },
    },
};

const eventColorMap: Record<string, string> = {
    blue: 'bg-blue-100 text-blue-700',
    red: 'bg-red-100 text-red-700',
    green: 'bg-emerald-100 text-emerald-700',
    yellow: 'bg-amber-100 text-amber-700',
    purple: 'bg-purple-100 text-purple-700',
};

function getEventBadge(color: string | null): string {
    return eventColorMap[color ?? 'blue'] ?? eventColorMap.blue!;
}
</script>

<template>
    <div>
        <Head title="Admin Dashboard" />

        <div class="mb-6">
            <h1 class="text-xl font-bold text-slate-900 dark:text-slate-50">Admin Dashboard</h1>
            <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">Overview of your school</p>
        </div>

        <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
            <Card v-for="card in statCards" :key="card.label">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl" :class="card.color">
                        <Icon :name="card.icon" class="h-5 w-5" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ card.label }}</p>
                        <p class="text-lg font-bold text-slate-900 dark:text-slate-100">{{ card.value }}</p>
                    </div>
                </div>
            </Card>
        </div>

        <div class="mb-6 grid gap-6 lg:grid-cols-3">
            <Card>
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400">
                        <Icon name="attendance" class="h-6 w-6" />
                    </div>
                    <div>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Today's Attendance</p>
                        <p class="text-lg font-bold text-slate-900 dark:text-slate-100">
                            {{ attendance.present }} / {{ attendance.total }}
                        </p>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="mb-1 flex items-center justify-between text-xs">
                        <span class="text-slate-500 dark:text-slate-400">Present rate</span>
                        <span class="font-semibold text-slate-700 dark:text-slate-200">{{ attendanceRate }}%</span>
                    </div>
                    <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                        <div class="h-full rounded-full bg-indigo-600 transition-all" :style="{ width: attendanceRate + '%' }" />
                    </div>
                </div>
                <div class="mt-3 flex gap-4 text-xs">
                    <span class="text-emerald-600 dark:text-emerald-400">Present: {{ attendance.present }}</span>
                    <span class="text-red-600 dark:text-red-400">Absent: {{ attendance.absent }}</span>
                </div>
            </Card>

            <Card>
                <p class="mb-1 text-xs font-medium text-slate-500 dark:text-slate-400">Fee Collected</p>
                <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ formatMoney(props.data.fee_summary.collected) }}</p>
                <div class="mt-3 border-t border-slate-100 pt-3 dark:border-slate-800">
                    <p class="mb-1 text-xs font-medium text-slate-500 dark:text-slate-400">Outstanding</p>
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ formatMoney(props.data.fee_summary.outstanding) }}</p>
                </div>
            </Card>

            <Card>
                <p class="mb-3 text-xs font-medium text-slate-500 dark:text-slate-400">Recent Notices</p>
                <div v-if="props.data.recent_notices.length === 0" class="py-4 text-center text-xs text-slate-400">
                    No recent notices
                </div>
                <ul v-else class="space-y-2">
                    <li v-for="notice in props.data.recent_notices" :key="notice.id" class="flex items-start gap-2">
                        <Badge :label="titleCase(notice.category)" tone="indigo" />
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-slate-800 dark:text-slate-200">{{ notice.title }}</p>
                            <p class="text-xs text-slate-400">{{ formatDate(notice.published_at) }}</p>
                        </div>
                    </li>
                </ul>
            </Card>
        </div>

        <div class="mb-6 grid gap-6 lg:grid-cols-2">
            <Card>
                <p class="mb-4 text-sm font-semibold text-slate-800 dark:text-slate-200">Students per Class</p>
                <div class="h-64">
                    <Bar :data="barChartData" :options="barChartOptions" />
                </div>
            </Card>

            <Card>
                <p class="mb-4 text-sm font-semibold text-slate-800 dark:text-slate-200">Fee Collections (6 months)</p>
                <div class="h-64">
                    <Line :data="lineChartData" :options="lineChartOptions" />
                </div>
            </Card>
        </div>

        <Card>
            <p class="mb-4 text-sm font-semibold text-slate-800 dark:text-slate-200">Upcoming Events</p>
            <div v-if="props.data.upcoming_events.length === 0" class="py-4 text-center text-xs text-slate-400">
                No upcoming events
            </div>
            <div v-else class="divide-y divide-slate-100 dark:divide-slate-800">
                <div v-for="event in props.data.upcoming_events" :key="event.id" class="flex items-center gap-4 py-3 first:pt-0 last:pb-0">
                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold" :class="getEventBadge(event.color)">
                        {{ new Date(event.starts_at).getDate() }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-slate-800 dark:text-slate-200">{{ event.title }}</p>
                        <p class="text-xs text-slate-400">
                            {{ formatDate(event.starts_at) }}
                            <span v-if="event.location"> · {{ event.location }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </Card>
    </div>
</template>
