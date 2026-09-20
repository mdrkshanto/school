<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppLayout from '@/components/ui/AppLayout.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import Card from '@/components/ui/Card.vue';
import Badge from '@/components/ui/Badge.vue';
import Icon from '@/components/ui/Icon.vue';
import { formatDate, formatMoney } from '@/lib/helpers';

defineOptions({ layout: AppLayout });

const props = defineProps<{
    data: {
        student: { id: number; name: string; admission_no: string; class: { id: number; name: string }; section: { id: number; name: string }; session: { id: number; name: string } } | null;
        admission_no: string;
        attendance_rate: number | null;
        latest_exam: { id: number; name: string; starts_at: string } | null;
        marksheet: { rows: Array<{ subject: string; full_marks: number; pass_marks: number; obtained: number | null; grade: string | null; grade_point: string | null; is_absent: boolean }>; total_marks: number; total_full: number; gpa: number; passed: boolean; grade: string } | null;
        fee_balance: number;
        transport: { route: { name: string }; stop: { name: string } } | null;
        recent_notices: Array<{ id: number; title: string; body: string; published_at: string }>;
        upcoming_events: Array<{ id: number; title: string; starts_at: string; location: string; color: string }>;
    };
}>();

const user = computed(() => usePage<any>().props.auth?.user);
</script>

<template>
    <div>
        <Head title="Dashboard" />
        <PageHeader :title="`Welcome, ${data.student?.name ?? user?.name ?? 'Student'}`" :subtitle="data.student ? `${data.student.class.name} — ${data.student.section.name} · ${data.student.session.name}` : undefined" />

        <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <Card>
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400">
                        <Icon name="attendance" class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-xs text-slate-400">Attendance</p>
                        <p class="text-lg font-bold text-slate-900 dark:text-slate-100">{{ data.attendance_rate !== null ? data.attendance_rate + '%' : 'N/A' }}</p>
                    </div>
                </div>
                <div v-if="data.attendance_rate !== null" class="mt-3 h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                    <div class="h-full rounded-full transition-all" :class="data.attendance_rate >= 75 ? 'bg-emerald-500' : data.attendance_rate >= 50 ? 'bg-amber-500' : 'bg-red-500'" :style="{ width: `${data.attendance_rate}%` }" />
                </div>
            </Card>

            <Card>
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400">
                        <Icon name="fees" class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-xs text-slate-400">Fee Balance</p>
                        <p class="text-lg font-bold text-slate-900 dark:text-slate-100">{{ formatMoney(data.fee_balance) }}</p>
                    </div>
                </div>
            </Card>

            <Card v-if="data.transport">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-sky-50 text-sky-600 dark:bg-sky-950/50 dark:text-sky-400">
                        <Icon name="bus" class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-xs text-slate-400">Transport</p>
                        <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ data.transport.route?.name }}</p>
                        <p class="text-xs text-slate-500">{{ data.transport.stop?.name }}</p>
                    </div>
                </div>
            </Card>

            <Card v-if="data.latest_exam">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400">
                        <Icon name="exams" class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-xs text-slate-400">Latest Exam</p>
                        <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ data.latest_exam.name }}</p>
                        <Badge v-if="data.marksheet" :label="data.marksheet.passed ? 'Pass' : 'Fail'" :tone="data.marksheet.passed ? 'green' : 'red'" />
                    </div>
                </div>
            </Card>
        </div>

        <div class="mb-6 grid gap-6 lg:grid-cols-2">
            <Card v-if="data.marksheet">
                <h3 class="mb-3 text-sm font-semibold text-slate-900 dark:text-slate-100">Latest Marksheet — {{ data.latest_exam?.name }}</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-700">
                                <th class="pb-2 font-semibold text-slate-600 dark:text-slate-400">Subject</th>
                                <th class="pb-2 text-center font-semibold text-slate-600 dark:text-slate-400">Marks</th>
                                <th class="pb-2 text-center font-semibold text-slate-600 dark:text-slate-400">Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(row, i) in data.marksheet.rows" :key="i" class="border-b border-slate-100 last:border-0 dark:border-slate-800">
                                <td class="py-2 text-slate-700 dark:text-slate-300">{{ row.subject }}</td>
                                <td class="py-2 text-center text-slate-700 dark:text-slate-300">
                                    <span v-if="row.is_absent" class="text-red-500">AB</span>
                                    <span v-else>{{ row.obtained ?? '—' }} / {{ row.full_marks }}</span>
                                </td>
                                <td class="py-2 text-center text-slate-600 dark:text-slate-400">{{ row.grade ?? '—' }}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2 border-slate-200 dark:border-slate-700">
                                <td class="pt-2 font-bold text-slate-900 dark:text-slate-100">GPA: {{ data.marksheet.gpa }}</td>
                                <td class="pt-2 text-center font-bold text-slate-900 dark:text-slate-100">{{ data.marksheet.total_marks }} / {{ data.marksheet.total_full }}</td>
                                <td class="pt-2 text-center font-bold text-slate-900 dark:text-slate-100">{{ data.marksheet.grade }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </Card>

            <Card v-if="data.transport">
                <h3 class="mb-3 text-sm font-semibold text-slate-900 dark:text-slate-100">Transport</h3>
                <div class="flex items-center gap-3">
                    <Icon name="bus" class="h-5 w-5 text-sky-500" />
                    <div>
                        <p class="text-sm font-medium text-slate-800 dark:text-slate-200">{{ data.transport.route?.name }}</p>
                        <p class="text-xs text-slate-500">Stop: {{ data.transport.stop?.name }}</p>
                    </div>
                </div>
            </Card>

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
