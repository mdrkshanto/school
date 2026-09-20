<script setup lang="ts" generic="T">
import { computed } from 'vue';

interface DataColumn<T> {
    key: string;
    label: string;
    value?: (row: T) => unknown;
    align?: 'left' | 'center' | 'right';
    headerClass?: string;
    cellClass?: string;
    hideOnMobile?: boolean;
    truncate?: boolean;
    numeric?: boolean;
}

const props = withDefaults(
    defineProps<{
        rows: T[];
        columns: DataColumn<T>[];
        rowKey: (row: T) => string | number;
        titleKey?: string;
        titleValue?: (row: T) => unknown;
        subtitleValue?: (row: T) => unknown;
        actionsLabel?: string;
    }>(),
    { actionsLabel: 'Actions' },
);

function cellValue(row: T, column: DataColumn<T>): unknown {
    return column.value ? column.value(row) : (row as any)[column.key];
}

function display(value: unknown): string {
    if (value === null || value === undefined || value === '' || typeof value === 'object') return '—';
    return String(value);
}

function titleText(row: T): string {
    if (props.titleValue) return display(props.titleValue(row));
    if (props.titleKey) return display((row as any)[props.titleKey]);
    if (props.columns.length > 0) return display(cellValue(row, props.columns[0]));
    return '';
}

function subtitleText(row: T): string | null {
    if (!props.subtitleValue) return null;
    const value = props.subtitleValue(row);
    if (value === null || value === undefined || value === '') return null;
    return String(value);
}

function alignClass(column: DataColumn<T>): string {
    if (column.align === 'center') return 'text-center';
    if (column.align === 'right') return 'text-right';
    return 'text-left';
}

const mobileColumns = computed(() => props.columns.filter((column) => !column.hideOnMobile));
</script>

<template>
    <div>
        <div class="hidden overflow-x-auto md:block">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-200 text-[11px] uppercase tracking-wider text-slate-500 dark:border-slate-800 dark:text-slate-400">
                    <tr>
                        <th
                            v-for="(column, index) in columns"
                            :key="column.key"
                            class="px-5 py-3 font-semibold"
                            :class="[alignClass(column), column.headerClass]"
                        >
                            {{ column.label }}
                        </th>
                        <th v-if="$slots.actions" class="px-5 py-3 text-right font-semibold">{{ actionsLabel }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr v-for="row in rows" :key="rowKey(row)" class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                        <td
                            v-for="(column, index) in columns"
                            :key="column.key"
                            class="px-5 py-3 align-middle"
                            :class="[
                                alignClass(column),
                                column.cellClass,
                                index === 0 ? 'font-medium text-slate-900 dark:text-slate-100' : 'text-slate-600 dark:text-slate-300',
                                column.numeric || column.align === 'right' ? 'font-medium text-slate-800 dark:text-slate-200' : '',
                            ]"
                        >
                            <slot :name="`cell-${column.key}`" :row="row" :value="cellValue(row, column)">
                                <span :class="column.truncate ? 'block max-w-xs truncate' : ''">{{ display(cellValue(row, column)) }}</span>
                            </slot>
                        </td>
                        <td v-if="$slots.actions" class="px-5 py-3 align-middle">
                            <div class="flex items-center justify-end gap-1">
                                <slot name="actions" :row="row" />
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="space-y-3 md:hidden">
            <div
                v-for="row in rows"
                :key="rowKey(row)"
                class="rounded-xl border border-slate-200 bg-slate-50/50 p-4 dark:border-slate-800 dark:bg-slate-900/40"
            >
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <slot name="title" :row="row">
                            <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ titleText(row) }}</p>
                        </slot>
                        <slot name="subtitle" :row="row">
                            <p v-if="subtitleText(row)" class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ subtitleText(row) }}</p>
                        </slot>
                    </div>
                    <div v-if="$slots.actions" class="flex shrink-0 items-center gap-1" @click.stop>
                        <slot name="actions" :row="row" />
                    </div>
                </div>

                <dl v-if="mobileColumns.length > 0" class="mt-3 divide-y divide-slate-100 dark:divide-slate-800">
                    <div v-for="column in mobileColumns" :key="column.key" class="flex items-center justify-between gap-4 py-2">
                        <dt class="shrink-0 text-xs font-medium uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ column.label }}</dt>
                        <dd class="min-w-0 text-right text-sm font-medium text-slate-700 dark:text-slate-300">
                            <slot :name="`cell-${column.key}`" :row="row" :value="cellValue(row, column)">
                                <span :class="column.truncate ? 'block text-right' : ''">{{ display(cellValue(row, column)) }}</span>
                            </slot>
                        </dd>
                    </div>
                </dl>

                <slot name="mobile-extra" :row="row" />
            </div>
        </div>
    </div>
</template>