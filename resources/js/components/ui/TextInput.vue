<script setup lang="ts">
import { computed, ref } from 'vue';
import Icon from '@/components/ui/Icon.vue';

defineOptions({ inheritAttrs: false });

const model = defineModel<string | number | null | undefined>();

const props = defineProps<{
    type?: string;
    error?: boolean | string;
    placeholder?: string;
}>();

const visible = ref(false);
const isPassword = computed(() => props.type === 'password');
const inputType = computed(() => (isPassword.value && visible.value ? 'text' : props.type ?? 'text'));
</script>

<template>
    <div class="relative">
        <input
            v-model="model"
            v-bind="$attrs"
            :type="inputType"
            :placeholder="placeholder"
            class="w-full rounded-lg border bg-white px-3 py-2 text-sm text-slate-800 shadow-sm outline-none transition-colors placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:placeholder:text-slate-500"
            :class="[
                error ? 'border-red-400 focus:border-red-500 focus:ring-red-500/20' : 'border-slate-300 dark:border-slate-700',
                isPassword ? 'pr-10' : '',
            ]"
        />
        <button
            v-if="isPassword"
            type="button"
            tabindex="-1"
            class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 transition-colors hover:text-slate-600 dark:hover:text-slate-300"
            @click="visible = !visible"
        >
            <Icon :name="visible ? 'eye-off' : 'eye'" class="h-4 w-4" />
        </button>
    </div>
</template>