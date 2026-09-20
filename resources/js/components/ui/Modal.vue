<script setup lang="ts">
import { watch } from 'vue';
import Icon from '@/components/ui/Icon.vue';

const props = defineProps<{
    open: boolean;
    title: string;
    wide?: boolean;
}>();

const emit = defineEmits<{ close: [] }>();

watch(
    () => props.open,
    (open) => {
        document.body.style.overflow = open ? 'hidden' : '';
    },
);

function close() {
    emit('close');
}
</script>

<template>
    <Teleport to="body">
        <Transition name="modal">
            <div v-if="open" class="fixed inset-0 z-[60] flex items-end justify-center sm:items-center" @keydown.esc="close">
                <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="close" />

                <div
                    class="relative z-10 max-h-[92vh] w-full overflow-y-auto rounded-t-2xl bg-white shadow-2xl sm:m-4 sm:rounded-2xl dark:bg-slate-900"
                    :class="wide ? 'sm:max-w-3xl' : 'sm:max-w-lg'"
                >
                    <div class="sticky top-0 flex items-center justify-between border-b border-slate-200 bg-white px-5 py-4 dark:border-slate-800 dark:bg-slate-900">
                        <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">{{ title }}</h3>
                        <button class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800" @click="close">
                            <Icon name="x" class="h-5 w-5" />
                        </button>
                    </div>

                    <div class="p-5">
                        <slot />
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.15s ease;
}
.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}
.modal-enter-active .relative,
.modal-leave-active .relative {
    transition: transform 0.2s ease, opacity 0.2s ease;
}
.modal-enter-from .relative,
.modal-leave-to .relative {
    transform: translateY(16px);
    opacity: 0;
}
</style>