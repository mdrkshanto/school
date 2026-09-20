<script setup lang="ts">
import { computed, nextTick, onMounted, ref, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Icon from '@/components/ui/Icon.vue';
import Modal from '@/components/ui/Modal.vue';
import { useToast } from '@/lib/toast';
import { useInstallPrompt } from '@/lib/install';
import { avatarUrl, initials } from '@/lib/helpers';

type NavItem = {
    label: string;
    icon: string;
    href: string;
    permission?: string;
};

type NavSection = {
    label: string;
    items: NavItem[];
};

const panelNavs: Record<string, NavSection[]> = {
    admin: [
        {
            label: '',
            items: [{ label: 'Dashboard', icon: 'dashboard', href: '/admin/dashboard' }],
        },
        {
            label: 'Academic',
            items: [
                { label: 'Students', icon: 'students', href: '/admin/students', permission: 'students.view' },
                { label: 'Teachers', icon: 'teachers', href: '/admin/teachers', permission: 'teachers.view' },
                { label: 'Parents', icon: 'parents', href: '/admin/guardians', permission: 'parents.view' },
                { label: 'Classes', icon: 'classes', href: '/admin/classes', permission: 'classes.view' },
                { label: 'Subjects', icon: 'subjects', href: '/admin/subjects', permission: 'subjects.view' },
                { label: 'Sessions', icon: 'sessions', href: '/admin/sessions', permission: 'sessions.view' },
                { label: 'Attendance', icon: 'attendance', href: '/admin/attendance', permission: 'attendance.view' },
                { label: 'Exams', icon: 'exams', href: '/admin/exams', permission: 'exams.view' },
                { label: 'Grades', icon: 'grades', href: '/admin/grade-rules', permission: 'grades.view' },
                { label: 'Results', icon: 'results', href: '/admin/results', permission: 'results.view' },
            ],
        },
        {
            label: 'Finance & Library',
            items: [
                { label: 'Fee Structures', icon: 'fees', href: '/admin/fee-structures', permission: 'fees.view' },
                { label: 'Invoices', icon: 'invoices', href: '/admin/invoices', permission: 'invoices.view' },
                { label: 'Notices', icon: 'notices', href: '/admin/notices', permission: 'notices.view' },
                { label: 'Events', icon: 'events', href: '/admin/events', permission: 'events.view' },
                { label: 'Library', icon: 'library', href: '/admin/books', permission: 'library.view' },
                { label: 'Book Issues', icon: 'issues', href: '/admin/book-issues', permission: 'library.view' },
                { label: 'Transport Routes', icon: 'routes', href: '/admin/routes', permission: 'transport.view' },
                { label: 'Vehicles', icon: 'vehicles', href: '/admin/vehicles', permission: 'transport.view' },
            ],
        },
        {
            label: 'System',
            items: [
                { label: 'Users', icon: 'users', href: '/admin/users', permission: 'users.manage' },
                { label: 'Roles', icon: 'roles', href: '/admin/roles', permission: 'roles.manage' },
                { label: 'Settings', icon: 'settings', href: '/admin/settings', permission: 'settings.manage' },
                { label: 'Reports', icon: 'reports', href: '/admin/reports', permission: 'reports.view' },
            ],
        },
    ],
    teacher: [
        {
            label: '',
            items: [{ label: 'Dashboard', icon: 'dashboard', href: '/teacher/dashboard' }],
        },
        {
            label: '',
            items: [
                { label: 'Attendance', icon: 'attendance', href: '/teacher/attendance' },
                { label: 'Results', icon: 'results', href: '/teacher/results' },
                { label: 'Notices', icon: 'notices', href: '/teacher/notices' },
                { label: 'Profile', icon: 'user', href: '/teacher/profile' },
            ],
        },
    ],
    student: [
        {
            label: '',
            items: [{ label: 'Dashboard', icon: 'dashboard', href: '/student/dashboard' }],
        },
        {
            label: '',
            items: [
                { label: 'Results', icon: 'results', href: '/student/results' },
                { label: 'Fees', icon: 'fees', href: '/student/fees' },
                { label: 'Attendance', icon: 'attendance', href: '/student/attendance' },
                { label: 'Notices', icon: 'notices', href: '/student/notices' },
                { label: 'Profile', icon: 'user', href: '/student/profile' },
            ],
        },
    ],
    parent: [
        {
            label: '',
            items: [{ label: 'Dashboard', icon: 'dashboard', href: '/parent/dashboard' }],
        },
        {
            label: '',
            items: [
                { label: 'Notices', icon: 'notices', href: '/parent/notices' },
                { label: 'Profile', icon: 'user', href: '/parent/profile' },
            ],
        },
    ],
};

const page = usePage<any>();
const props = computed(() => page.props);
const panel = computed(() => String(props.value.panel ?? 'admin'));
const permissions = computed(() => props.value.auth?.permissions ?? []);

const sections = computed(() => {
    const navs = panelNavs[panel.value] ?? panelNavs.admin!;

    return navs
        .map((section) => ({
            ...section,
            items: panel.value === 'admin' ? section.items.filter((item) => !item.permission || permissions.value.includes(item.permission)) : section.items,
        }))
        .filter((section) => section.items.length > 0);
});

const toast = useToast();

watch(
    () => props.value.flash,
    (flash) => {
        if (flash?.success) toast.success(flash.success.message);
        if (flash?.error) toast.error(flash.error.message);
        if (flash?.warning) toast.warning(flash.warning.message);
    },
    { immediate: true },
);

const user = computed(() => props.value.auth?.user);

const panelTitle = computed(() => ({
    admin: 'Admin Panel',
    teacher: 'Teacher Portal',
    student: 'Student Portal',
    parent: 'Parent Portal',
})[panel.value] ?? 'Portal');

const isHome = computed(() => {
    const dash = `/${panel.value}/dashboard`;
    return page.url === dash || page.url === `/${panel.value}`;
});

/* ---------- Mobile sheets ---------- */
const modulesOpen = ref(false);
const moreOpen = ref(false);

/* ---------- PWA install ---------- */
const install = useInstallPrompt();
const iosGuideOpen = ref(false);

const showInstall = computed(() => (install.canInstall.value || install.isIos.value) && !install.isInstalled.value);

function onInstallClick() {
    if (install.isIos.value && !install.canInstall.value) {
        iosGuideOpen.value = true;
        return;
    }

    install.promptInstall();
}

/* ---------- Mobile table -> card enhancer ----------
   Converts simple list tables into stacked app cards on small screens by
   stamping each cell with the matching <th> label. Dense/grid tables stay
   horizontally scrollable. Runs after every Inertia navigation. */
function enhanceMobileTables() {
    document.querySelectorAll('main table').forEach((table) => {
        table.classList.remove('table-card', 'table-scroll');

        const thead = table.querySelector('thead');
        const tfoot = table.querySelector('tfoot');
        if (!thead) return;

        const labels = Array.from(thead.querySelectorAll('th')).map((th) => (th.textContent ?? '').trim());
        const hasInputs = Array.from(table.querySelectorAll('td')).some((td) => td.querySelector('input, select'));
        const hasSpan = table.querySelector('[colspan], [rowspan]');

        const isDense = labels.length > 8 || Boolean(tfoot) || Boolean(hasSpan) || hasInputs;

        if (isDense) {
            table.classList.add('table-scroll');
            return;
        }

        table.classList.add('table-card');

        table.querySelectorAll('tbody tr').forEach((tr) => {
            Array.from(tr.querySelectorAll(':scope > td')).forEach((td, index) => {
                const interactive = td.querySelector('button, a, form, input, select');
                const empty = (td.textContent ?? '').trim() === '';

                (td as HTMLElement).dataset.label = interactive && (empty || labels[index] === 'Actions') ? '' : (labels[index] ?? '');
            });
        });
    });
}

watch(
    () => page.url,
    async () => {
        await nextTick();
        enhanceMobileTables();
    },
);

onMounted(() => {
    enhanceMobileTables();
});
</script>

<template>
    <div class="min-h-screen bg-slate-100 dark:bg-slate-950 lg:pb-0">
        <aside
            class="fixed inset-y-0 left-0 z-40 hidden w-64 flex-col bg-slate-900 text-slate-200 lg:flex"
        >
            <div class="flex h-16 shrink-0 items-center gap-3 border-b border-slate-800 px-5">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-600 font-bold text-white">S</div>
                <div class="min-w-0">
                    <div class="truncate text-sm font-semibold leading-tight">{{ props.name }}</div>
                    <div class="text-[11px] text-slate-400">{{ panelTitle }}</div>
                </div>
            </div>

            <nav class="flex-1 space-y-5 overflow-y-auto px-3 py-5">
                <div v-for="section in sections" :key="section.label || 'top'" class="space-y-0.5">
                    <div v-if="section.label" class="px-3 pb-1.5 pt-2 text-[10px] font-semibold uppercase tracking-wider text-slate-500">
                        {{ section.label }}
                    </div>
                    <Link
                        v-for="item in section.items"
                        :key="item.href"
                        :href="item.href"
                        class="group flex items-center gap-3 rounded-lg px-3 py-2 text-[13px] font-medium transition-colors"
                        :class="$page.url.startsWith(item.href) ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white'"
                    >
                        <Icon :name="item.icon" class="h-[18px] w-[18px] shrink-0" />
                        <span>{{ item.label }}</span>
                    </Link>
                </div>
            </nav>

            <div class="shrink-0 space-y-3 border-t border-slate-800 p-3">
                <button
                    v-if="showInstall"
                    class="flex w-full items-center justify-center gap-2 rounded-lg bg-indigo-600 px-3 py-2 text-[13px] font-semibold text-white transition-colors hover:bg-indigo-500"
                    @click="onInstallClick"
                >
                    <Icon name="download" class="h-4 w-4" />
                    Install App
                </button>
                <div class="px-1 text-[11px] text-slate-500">
                    © {{ new Date().getFullYear() }} {{ props.name }}
                </div>
            </div>
        </aside>

        <div class="lg:pl-64">
            <!-- Mobile app header -->
            <header
                class="sticky top-0 z-30 border-b border-slate-200 bg-white/85 pt-[env(safe-area-inset-top)] backdrop-blur-md dark:border-slate-800 dark:bg-slate-900/85 lg:hidden"
            >
                <div class="flex h-14 items-center gap-3 px-4">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-indigo-600 font-bold text-white">S</div>
                <div class="min-w-0 flex-1">
                    <div class="truncate text-sm font-semibold leading-tight text-slate-800 dark:text-slate-100">{{ props.name }}</div>
                    <div class="truncate text-[11px] text-slate-500 dark:text-slate-400">{{ panelTitle }}</div>
                </div>
                <button class="shrink-0 rounded-full p-1" @click="moreOpen = true">
                    <template v-if="user?.avatar">
                        <img :src="avatarUrl(String(user.avatar))" :alt="String(user.name)" class="h-8 w-8 rounded-full object-cover" />
                    </template>
                    <span
                        v-else
                        class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 text-xs font-semibold text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-300"
                    >
                        {{ initials(String(user?.name ?? '')) }}
                    </span>
                </button>
                </div>
            </header>

            <!-- Desktop header -->
            <header
                class="sticky top-0 z-30 hidden h-16 items-center gap-3 border-b border-slate-200 bg-white/80 px-4 backdrop-blur dark:border-slate-800 dark:bg-slate-900/80 lg:flex"
            >
                <h1 class="truncate text-base font-semibold text-slate-800 dark:text-slate-100">
                    <slot name="title" />
                </h1>

                <div class="ml-auto flex items-center gap-2">
                    <div class="relative" id="user-menu">
                        <button class="flex items-center gap-2 rounded-lg p-1.5 hover:bg-slate-100 dark:hover:bg-slate-800" @click="moreOpen = true">
                            <template v-if="user?.avatar">
                                <img :src="avatarUrl(String(user.avatar))" :alt="String(user.name)" class="h-8 w-8 rounded-full object-cover" />
                            </template>
                            <span v-else class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 text-xs font-semibold text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-300">
                                {{ initials(String(user?.name ?? '')) }}
                            </span>
                            <Icon name="chevron-down" class="h-4 w-4 text-slate-400" />
                        </button>
                    </div>
                </div>
            </header>

            <main class="mx-auto w-full max-w-7xl px-4 py-5 pb-28 lg:px-8 lg:py-6 lg:pb-6">
                <slot />
            </main>
        </div>

        <!-- Mobile bottom tab bar -->
        <nav
            class="fixed inset-x-0 bottom-0 z-40 border-t border-slate-200 bg-white/95 backdrop-blur-md dark:border-slate-800 dark:bg-slate-900/95 lg:hidden"
        >
            <div class="mx-auto grid max-w-lg grid-cols-3 pb-[env(safe-area-inset-bottom)]">
                <Link
                    :href="`/${panel}/dashboard`"
                    class="flex flex-col items-center gap-0.5 py-2 text-[11px] font-medium"
                    :class="isHome ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-500 dark:text-slate-400'"
                >
                    <Icon name="home" class="h-6 w-6" :stroke-width="isHome ? 2.4 : 2" />
                    Home
                </Link>
                <button
                    class="flex flex-col items-center gap-0.5 py-2 text-[11px] font-medium"
                    :class="modulesOpen ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-500 dark:text-slate-400'"
                    @click="modulesOpen = true"
                >
                    <Icon name="grid" class="h-6 w-6" :stroke-width="modulesOpen ? 2.4 : 2" />
                    Modules
                </button>
                <button
                    class="flex flex-col items-center gap-0.5 py-2 text-[11px] font-medium"
                    :class="moreOpen ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-500 dark:text-slate-400'"
                    @click="moreOpen = true"
                >
                    <Icon name="dots" class="h-6 w-6" :stroke-width="moreOpen ? 2.4 : 2" />
                    More
                </button>
            </div>
        </nav>

        <!-- Modules bottom sheet -->
        <Modal :open="modulesOpen" :wide="true" title="Modules" @close="modulesOpen = false">
            <div class="-m-1 space-y-5">
                <div v-for="section in sections" :key="section.label || 'top'" class="space-y-1">
                    <div v-if="section.label" class="px-1 pb-1 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                        {{ section.label }}
                    </div>
                    <Link
                        v-for="item in section.items"
                        :key="item.href"
                        :href="item.href"
                        class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800"
                        :class="$page.url.startsWith(item.href) ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300' : ''"
                        @click="modulesOpen = false"
                    >
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                            <Icon :name="item.icon" class="h-5 w-5" />
                        </span>
                        <span>{{ item.label }}</span>
                    </Link>
                </div>
            </div>
        </Modal>

        <!-- More bottom sheet -->
        <Modal :open="moreOpen" title="Account" @close="moreOpen = false">
            <div class="-m-1 space-y-1">
                <div class="flex items-center gap-3 rounded-xl bg-slate-50 px-3 py-4 dark:bg-slate-800/60">
                    <template v-if="user?.avatar">
                        <img :src="avatarUrl(String(user.avatar))" :alt="String(user.name)" class="h-12 w-12 rounded-full object-cover" />
                    </template>
                    <span v-else class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-sm font-semibold text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-300">
                        {{ initials(String(user?.name ?? '')) }}
                    </span>
                    <div class="min-w-0">
                        <div class="truncate text-sm font-semibold text-slate-800 dark:text-slate-100">{{ user?.name }}</div>
                        <div class="truncate text-xs text-slate-500">{{ user?.email }}</div>
                    </div>
                </div>

                <Link
                    :href="`/${panel}/profile`"
                    class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800"
                    @click="moreOpen = false"
                >
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                        <Icon name="user" class="h-5 w-5" />
                    </span>
                    Profile
                </Link>
                <button
                    v-if="showInstall"
                    class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800"
                    @click="onInstallClick"
                >
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                        <Icon name="download" class="h-5 w-5" />
                    </span>
                    Install App
                </button>
                <Link
                    :href="`/${panel}/logout`"
                    method="post"
                    class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium text-red-600 transition-colors hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/40"
                >
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-red-50 text-red-500 dark:bg-red-950/40 dark:text-red-400">
                        <Icon name="logout" class="h-5 w-5" />
                    </span>
                    Sign out
                </Link>
            </div>
        </Modal>

        <!-- Install App -->
        <Modal :open="iosGuideOpen" title="Install the App" @close="iosGuideOpen = false">
            <div class="space-y-4 text-sm text-slate-600 dark:text-slate-300">
                <p>
                    Install <span class="font-semibold text-slate-800 dark:text-slate-100">{{ props.name }}</span> on your home
                    screen for quick, full-screen access.
                </p>
                <ol class="space-y-3">
                    <li class="flex items-start gap-3">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-semibold text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-300">1</span>
                        <span>Tap the <b>Share</b> button <Icon name="share" class="inline h-4 w-4" /> in Safari.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-semibold text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-300">2</span>
                        <span>Choose <b>Add to Home Screen</b>.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-semibold text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-300">3</span>
                        <span>Tap <b>Add</b> — the App icon will appear on your home screen.</span>
                    </li>
                </ol>
            </div>
        </Modal>
    </div>
</template>