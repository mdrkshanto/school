<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        name: string;
        strokeWidth?: number;
    }>(),
    { strokeWidth: 2 },
);

const paths: Record<string, string[]> = {
    dashboard: [
        '<rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/>',
    ],
    students: [
        '<path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>',
    ],
    teachers: [
        '<path d="M12 22v-5"/><path d="M9 10V4h6v6"/><path d="M8 22h8"/>',
    ],
    users: [
        '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
    ],
    user: ['<circle cx="12" cy="7" r="4"/><path d="M5 21v-2a4 4 0 0 1 4-4h6a4 4 0 0 1 4 4v2"/>'],
    parents: [
        '<path d="M8 2v4"/><path d="M16 2v4"/><rect x="2" y="6" width="20" height="4" rx="1"/>',
        '<path d="M12 21 8 13h8l-4 8Z"/>',
    ],
    classes: [
        '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/>',
    ],
    subjects: [
        '<path d="m16 18 6-6-6-6"/><path d="m8 6-6 6 6 6"/>',
    ],
    sessions: [
        '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/>',
    ],
    attendance: [
        '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/>',
    ],
    exams: [
        '<rect x="3" y="4" width="18" height="16" rx="2"/>',
        '<circle cx="8" cy="10" r="1"/><circle cx="12" cy="10" r="1"/><circle cx="16" cy="10" r="1"/>',
        '<path d="M8 15h8"/>',
    ],
    results: [
        '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/><path d="M10 8h6"/><path d="M10 12h6"/>',
    ],
    grades: [
        '<circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/>',
    ],
    fees: [
        '<path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/><path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/>',
    ],
    invoices: [
        '<path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/><path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/>',
        '<path d="m9 12 2 2 4-4"/>',
    ],
    notices: ['<path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>'],
    events: [
        '<path d="M21.73 18l-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 20h16a2 2 0 0 0 1.73-2Z"/><path d="M12 9v4"/><path d="M12 17h.01"/>',
    ],
    library: [
        '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>',
    ],
    issues: [
        '<path d="M2 12h5l2-5 3 10 2-5h8"/>',
    ],
    routes: [
        '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
    ],
    vehicles: [
        '<path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/>',
    ],
    roles: [
        '<path d="m16 18 6-6-6-6"/><path d="m8 6-6 6 6 6"/>',
        '<path d="M14 12c0-4-2-6-6-6s-6 2-6 6 2 6 6 6 6-2 6-6Z"/>',
    ],
    settings: [
        '<path d="M4 21v-7M4 10V3M12 21v-9M12 8V3M20 21v-5M20 12V3M1 14h6M9 8h6M17 16h6"/>',
    ],
    reports: ['<path d="M3 3v16a2 2 0 0 0 2 2h16"/><path d="M7 16v-5"/><path d="M12 16V8"/><path d="M17 16v-3"/>'],
    search: ['<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>'],
    plus: ['<path d="M5 12h14"/><path d="M12 5v14"/>'],
    edit: ['<path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>'],
    trash: ['<path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>'],
    'chevron-down': ['<path d="m6 9 6 6 6-6"/>'],
    'chevron-right': ['<path d="m9 18 6-6-6-6"/>'],
    'chevron-left': ['<path d="m15 18-6-6 6-6"/>'],
    'chevron-up': ['<path d="m18 15-6-6-6 6"/>'],
    menu: ['<path d="M4 6h16"/><path d="M4 12h16"/><path d="M4 18h16"/>'],
    x: ['<path d="M18 6 6 18"/><path d="m6 6 12 12"/>'],
    check: ['<path d="M20 6 9 17l-5-5"/>'],
    logout: ['<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/>'],
    home: ['<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/>'],
    clock: ['<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>'],
    mail: [
        '<rect x="2" y="4" width="20" height="16" rx="2"/>',
        '<path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
    ],
    phone: [
        '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>',
    ],
    'arrow-left': ['<path d="M19 12H5"/><path d="m12 19-7-7 7-7"/>'],
    'arrow-down': ['<path d="M12 5v14"/><path d="m19 12-7 7-7-7"/>'],
    receipt: [
        '<path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 17.5v-11"/>',
    ],
    'file-down': ['<path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M12 18v-6"/><path d="m9 15 3 3 3-3"/>'],
    pdf: ['<path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M9 13h6"/><path d="M9 17h6"/>'],
    'circle-check': ['<circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>'],
    'circle-x': ['<circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/>'],
    'circle-alert': ['<circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/>'],
    'book-open': [
        '<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>',
    ],
    eye: ['<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>'],
    'eye-off': ['<path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/>'],
    filter: ['<path d="M22 3H2l8 9.46V19l4 2v-8.54Z"/>'],
    refresh: [
        '<path d="M21 12a9 9 0 1 1-2.64-6.36L21 8"/><path d="M21 3v5h-5"/>',
    ],
    download: ['<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/><path d="M12 15V3"/>'],
    money: [
        '<circle cx="12" cy="12" r="10"/><path d="M16 8H8v8h8z"/><path d="M12 10v4"/><path d="M10 12h4"/>',
    ],
    bus: [
        '<path d="M17 5H7a2 2 0 0 0-2 2v9a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V7a2 2 0 0 0-2-2Z"/><path d="M9 17v3"/><path d="M15 17v3"/><circle cx="8" cy="12" r="1"/><circle cx="16" cy="12" r="1"/><path d="M7 5V3h10v2"/>',
    ],
    sparkles: [
        '<path d="m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3Z"/>',
    ],
    school: [
        '<path d="M3 21h18"/><path d="M5 21V7a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v14"/><path d="M9 7V4h6v3"/><path d="M9 21v-4h6v4"/><path d="M7 11h2"/><path d="M15 11h2"/><path d="M7 15h2"/><path d="M15 15h2"/>',
    ],
    grid: [
        '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
    ],
    'dots': ['<circle cx="5" cy="12" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/>'],
    'share': ['<path d="M12 3v12"/><path d="m8 7 4-4 4 4"/><path d="M20 21H4a2 2 0 0 1-2-2v-4"/>'],
};

const svgContent = computed(() => {
    const group = paths[props.name] ?? paths.sparkles!;

    return group.join('');
});
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        :stroke-width="props.strokeWidth"
        stroke-linecap="round"
        stroke-linejoin="round"
        aria-hidden="true"
        v-html="svgContent"
    />
</template>