<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import Icon from '@/components/ui/Icon.vue';

type PanelCard = {
    id: string;
    label: string;
    loginUrl: string;
    icon: string;
    description: string;
};

defineProps<{
    panels: PanelCard[];
}>();
</script>

<template>
    <Head title="Welcome" />

    <div class="relative flex min-h-screen flex-col overflow-hidden bg-slate-950">
        <div class="pointer-events-none absolute -top-40 left-1/2 h-[500px] w-[900px] -translate-x-1/2 rounded-full bg-indigo-600/20 blur-3xl" />
        <div class="pointer-events-none absolute bottom-0 right-0 h-80 w-80 rounded-full bg-fuchsia-600/10 blur-3xl" />

        <header class="relative z-10 flex h-16 items-center justify-between px-6 lg:px-10">
            <div class="flex items-center gap-2.5">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-600 text-sm font-bold text-white">S</div>
                <span class="text-sm font-semibold text-white">{{ $page.props.name }}</span>
            </div>
        </header>

        <main class="relative z-10 flex flex-1 flex-col items-center justify-center px-6 py-16">
            <div class="mx-auto max-w-2xl text-center">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-500/10 px-3 py-1 text-xs font-medium text-indigo-300 ring-1 ring-inset ring-indigo-500/30">
                    <Icon name="sparkles" class="h-3.5 w-3.5" />
                    Secure · Fast · Installable
                </span>
                <h1 class="mt-5 text-4xl font-extrabold tracking-tight text-white sm:text-5xl">
                    {{ $page.props.name }}
                </h1>
                <p class="mt-4 text-base text-slate-400">
                    A complete platform to manage students, teachers, academics, fees, library and transport — from one place.
                </p>
            </div>

            <div class="mt-12 grid w-full max-w-3xl grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Link
                    v-for="panel in panels"
                    :key="panel.id"
                    :href="panel.loginUrl"
                    class="group rounded-2xl border border-slate-800 bg-slate-900/60 p-5 text-left backdrop-blur transition-all hover:-translate-y-0.5 hover:border-indigo-500/60 hover:bg-slate-900"
                >
                    <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600/20 text-indigo-300 transition-colors group-hover:bg-indigo-600 group-hover:text-white">
                        <Icon :name="panel.icon" class="h-5 w-5" />
                    </div>
                    <div class="text-base font-semibold text-white">{{ panel.label }}</div>
                    <div class="mt-0.5 text-xs text-slate-400">{{ panel.description }}</div>
                </Link>
            </div>
        </main>

        <footer class="relative z-10 flex flex-col items-center gap-1 pb-8 text-center text-xs text-slate-600">
            <span>© {{ new Date().getFullYear() }} {{ $page.props.name }}</span>
            <span>All rights reserved.</span>
        </footer>
    </div>
</template>