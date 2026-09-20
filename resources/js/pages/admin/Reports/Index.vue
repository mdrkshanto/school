<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/components/ui/AppLayout.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import Card from '@/components/ui/Card.vue';
import Icon from '@/components/ui/Icon.vue';
import ReportController from '@/actions/App/Http/Controllers/Admin/ReportController';

defineOptions({ layout: AppLayout });

defineProps<{
    stats: {
        students: number;
        teachers: number;
        defaulters: number;
        overdue_books: number;
    };
    permissionGroups: string[];
}>();
</script>

<template>
    <div>
        <Head title="Reports" />
        <PageHeader title="Reports" subtitle="Overview and analytics across the school." />

        <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <Card>
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400">
                        <Icon name="students" class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-slate-900 dark:text-slate-50">{{ stats.students }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Total Students</p>
                    </div>
                </div>
            </Card>
            <Card>
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-sky-50 text-sky-600 dark:bg-sky-950/50 dark:text-sky-400">
                        <Icon name="teachers" class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-slate-900 dark:text-slate-50">{{ stats.teachers }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Total Teachers</p>
                    </div>
                </div>
            </Card>
            <Card>
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400">
                        <Icon name="money" class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-slate-900 dark:text-slate-50">{{ stats.defaulters }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Fee Defaulters</p>
                    </div>
                </div>
            </Card>
            <Card>
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-red-50 text-red-600 dark:bg-red-950/50 dark:text-red-400">
                        <Icon name="issues" class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-slate-900 dark:text-slate-50">{{ stats.overdue_books }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Overdue Books</p>
                    </div>
                </div>
            </Card>
        </div>

        <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Explore Reports</h2>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <Link
                :href="ReportController.students.url()"
                class="group rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-colors hover:border-indigo-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-indigo-900"
            >
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400">
                    <Icon name="students" class="h-5 w-5" />
                </div>
                <h3 class="mt-3 text-sm font-semibold text-slate-900 dark:text-slate-100">Student Report</h3>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Enrollment details by class</p>
            </Link>
            <Link
                :href="ReportController.fees.url()"
                class="group rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-colors hover:border-indigo-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-indigo-900"
            >
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400">
                    <Icon name="money" class="h-5 w-5" />
                </div>
                <h3 class="mt-3 text-sm font-semibold text-slate-900 dark:text-slate-100">Fees Report</h3>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Collections and pending balances</p>
            </Link>
            <Link
                :href="ReportController.attendance.url()"
                class="group rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-colors hover:border-indigo-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-indigo-900"
            >
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400">
                    <Icon name="attendance" class="h-5 w-5" />
                </div>
                <h3 class="mt-3 text-sm font-semibold text-slate-900 dark:text-slate-100">Attendance Report</h3>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Daily attendance summaries</p>
            </Link>
            <Link
                :href="ReportController.results.url()"
                class="group rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-colors hover:border-indigo-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-indigo-900"
            >
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-rose-50 text-rose-600 dark:bg-rose-950/50 dark:text-rose-400">
                    <Icon name="results" class="h-5 w-5" />
                </div>
                <h3 class="mt-3 text-sm font-semibold text-slate-900 dark:text-slate-100">Results Report</h3>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Exam marks and grades</p>
            </Link>
        </div>
    </div>
</template>