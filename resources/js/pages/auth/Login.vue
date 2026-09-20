<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import Icon from '@/components/ui/Icon.vue';
import TextInput from '@/components/ui/TextInput.vue';
import SubmitButton from '@/components/ui/SubmitButton.vue';

const props = defineProps<{
    panel: string;
    panelLabel: string;
    errors?: Record<string, string>;
}>();

const form = useForm<{ email: string; password: string; remember: boolean }>({
    email: '',
    password: '',
    remember: false,
});

const demoAccounts: Record<string, { email: string; label: string }> = {
    admin: { email: 'admin@school.test', label: 'Admin' },
    teacher: { email: 'teacher@school.test', label: 'Teacher' },
    student: { email: 'student@school.test', label: 'Student' },
    parent: { email: 'parent@school.test', label: 'Parent' },
};

const demo = computed(() => demoAccounts[props.panel]);

function submit() {
    form.post(`/${props.panel}/login`, {
        onSuccess: () => form.reset('password'),
    });
}

function fillDemo() {
    form.email = demo.value?.email ?? '';
    form.password = 'password';
    form.remember = false;
}
</script>

<template>
    <Head :title="`${panelLabel} Login`" />

    <div class="flex min-h-screen flex-col items-center justify-center bg-gradient-to-br from-slate-100 via-white to-indigo-50 px-4 py-12 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950">
        <div class="w-full max-w-md">
            <div class="mb-8 text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-600 text-xl font-bold text-white shadow-lg shadow-indigo-600/30">
                    S
                </div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-50">Sign in to {{ panelLabel }} Portal</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">School Management System</p>
            </div>

            <form class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/50 dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/30" @submit.prevent="submit">
                <div class="mb-4">
                    <label class="mb-1.5 block text-[13px] font-medium text-slate-700 dark:text-slate-300">Email</label>
                    <input
                        v-model="form.email"
                        type="email"
                        autocomplete="email"
                        required
                        placeholder="you@school.test"
                        class="w-full rounded-lg border bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm outline-none transition-colors placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                        :class="form.errors.email ? 'border-red-400' : 'border-slate-300 dark:border-slate-700'"
                    />
                    <span v-if="form.errors.email" class="mt-1 block text-xs font-medium text-red-500">{{ form.errors.email }}</span>
                </div>

                <div class="mb-5">
                    <label class="mb-1.5 block text-[13px] font-medium text-slate-700 dark:text-slate-300">Password</label>
                    <TextInput
                        v-model="form.password"
                        type="password"
                        placeholder="••••••••"
                        autocomplete="current-password"
                        required
                        :error="form.errors.password"
                    />
                </div>

                <label class="mb-5 flex cursor-pointer items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                    <input v-model="form.remember" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" />
                    Remember me
                </label>

                <SubmitButton :processing="form.processing" class="w-full">Sign in</SubmitButton>
            </form>

            <div class="mt-5 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 text-center dark:border-slate-700 dark:bg-slate-900/50">
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Demo credentials</p>
                <button type="button" class="mt-1.5 inline-flex items-center gap-1.5 text-sm font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400" @click="fillDemo">
                    <Icon name="sparkles" class="h-4 w-4" />
                    {{ demo?.email }} / password
                </button>
            </div>

            <div class="mt-6 text-center">
                <button type="button" class="inline-flex items-center gap-1 text-xs font-medium text-slate-500 hover:text-slate-700 dark:text-slate-400" @click="router.visit('/')">
                    <Icon name="arrow-left" class="h-3.5 w-3.5" />
                    Back to portal home
                </button>
            </div>
        </div>
    </div>
</template>