<script setup lang="ts">
import { reactive } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/components/ui/AppLayout.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import Card from '@/components/ui/Card.vue';
import Field from '@/components/ui/Field.vue';
import TextInput from '@/components/ui/TextInput.vue';
import TextareaInput from '@/components/ui/TextareaInput.vue';
import SubmitButton from '@/components/ui/SubmitButton.vue';
import SettingController from '@/actions/App/Http/Controllers/Admin/SettingController';

defineOptions({ layout: AppLayout });

type SettingGroup = {
    key: string;
    label: string;
    fields: Array<{ key: string; label: string; value: string | null }>;
};

type ExtraSetting = {
    key: string;
    value: string | null;
    group: string;
};

const props = defineProps<{
    groups: SettingGroup[];
    extra: ExtraSetting[];
}>();

const values = reactive<Record<string, string>>({});

for (const group of props.groups) {
    for (const field of group.fields) {
        values[field.key] = field.value ?? '';
    }
}

for (const item of props.extra) {
    values[item.key] = item.value ?? '';
}

const isMultiline: Record<string, boolean> = {
    school_address: true,
};

const form = useForm<{ settings: Array<{ key: string; value: string }> }>({ settings: [] });

function save() {
    form.settings = Object.entries(values).map(([key, value]) => ({ key, value }));

    form.put(SettingController.update.url(), {
        preserveScroll: true,
    });
}
</script>

<template>
    <div>
        <Head title="Settings" />
        <PageHeader title="Settings" subtitle="Configure school-wide application settings.">
            <template #actions>
                <SubmitButton :processing="form.processing" @click.prevent="save">Save Settings</SubmitButton>
            </template>
        </PageHeader>

        <div class="mb-5 grid gap-6 lg:grid-cols-2">
            <Card v-for="group in groups" :key="group.key">
                <h2 class="mb-4 text-base font-semibold text-slate-900 dark:text-slate-100">{{ group.label }}</h2>
                <div class="space-y-4">
                    <Field v-for="field in group.fields" :key="field.key" :label="field.label">
                        <TextareaInput v-if="isMultiline[field.key]" v-model="values[field.key]" :rows="3" />
                        <TextInput v-else v-model="values[field.key]" />
                    </Field>
                </div>
            </Card>
        </div>

        <Card v-if="extra.length > 0">
            <h2 class="mb-4 text-base font-semibold text-slate-900 dark:text-slate-100">Additional Settings</h2>
            <div class="space-y-4">
                <Field v-for="item in extra" :key="item.key" :label="item.key">
                    <TextInput v-model="values[item.key]" />
                </Field>
            </div>
        </Card>

        <div class="flex justify-end pt-4">
            <SubmitButton :processing="form.processing" @click.prevent="save">Save Settings</SubmitButton>
        </div>
    </div>
</template>