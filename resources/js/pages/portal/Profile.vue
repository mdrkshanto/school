<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/components/ui/AppLayout.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import Card from '@/components/ui/Card.vue';
import Field from '@/components/ui/Field.vue';
import TextInput from '@/components/ui/TextInput.vue';
import SubmitButton from '@/components/ui/SubmitButton.vue';
import Icon from '@/components/ui/Icon.vue';
import { avatarUrl, initials, formatDate } from '@/lib/helpers';

defineOptions({ layout: AppLayout });

const page = usePage<any>();
const panel = computed(() => String(page.props.panel ?? 'teacher'));
const user = computed(() => page.props.auth?.user ?? {});

const form = useForm({
    name: user.value.name ?? '',
    email: user.value.email ?? '',
    phone: user.value.phone ?? '',
    password: '',
    password_confirmation: '',
});

function submit() {
    form.put(`/${panel.value}/profile`, {
        preserveScroll: true,
    });
}
</script>

<template>
    <div>
        <Head title="Profile" />
        <PageHeader title="My Profile" />

        <div class="grid gap-6 lg:grid-cols-3">
            <Card class="flex flex-col items-center py-8">
                <template v-if="user.avatar">
                    <img :src="avatarUrl(String(user.avatar))" :alt="user.name" class="h-24 w-24 rounded-full object-cover ring-4 ring-slate-100 dark:ring-slate-800" />
                </template>
                <div v-else class="flex h-24 w-24 items-center justify-center rounded-full bg-indigo-100 text-2xl font-bold text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-300">
                    {{ initials(user.name) }}
                </div>
                <h2 class="mt-4 text-lg font-semibold text-slate-900 dark:text-slate-100">{{ user.name }}</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ user.email }}</p>
                <div v-if="user.phone" class="mt-1 flex items-center gap-1.5 text-sm text-slate-500 dark:text-slate-400">
                    <Icon name="phone" class="h-3.5 w-3.5" />
                    {{ user.phone }}
                </div>
                <div class="mt-4 border-t border-slate-100 pt-4 dark:border-slate-800">
                    <p class="text-xs text-slate-400 dark:text-slate-500">Member since</p>
                    <p class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ formatDate(user.created_at) }}</p>
                </div>
            </Card>

            <Card class="lg:col-span-2">
                <h3 class="mb-4 text-base font-semibold text-slate-900 dark:text-slate-100">Edit Profile</h3>
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <Field label="Full name" :error="form.errors.name" required>
                            <TextInput v-model="form.name" />
                        </Field>
                        <Field label="Email address" :error="form.errors.email" required>
                            <TextInput v-model="form.email" type="email" />
                        </Field>
                    </div>
                    <Field label="Phone number" :error="form.errors.phone">
                        <TextInput v-model="form.phone" type="tel" />
                    </Field>
                    <div class="border-t border-slate-100 pt-4 dark:border-slate-800">
                        <h4 class="mb-3 text-sm font-medium text-slate-700 dark:text-slate-300">Change Password</h4>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <Field label="New password" :error="form.errors.password" hint="Leave blank to keep current">
                                <TextInput v-model="form.password" type="password" />
                            </Field>
                            <Field label="Confirm password" :error="form.errors.password_confirmation">
                                <TextInput v-model="form.password_confirmation" type="password" />
                            </Field>
                        </div>
                    </div>
                    <div class="flex justify-end pt-2">
                        <SubmitButton :processing="form.processing">Update Profile</SubmitButton>
                    </div>
                </form>
            </Card>
        </div>
    </div>
</template>
