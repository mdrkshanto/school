import type { createHeadManager, Page, Router } from '@inertiajs/core';
import type { Flash, Auth } from '@/types/auth';

declare module 'vite/client' {
    interface ImportMetaEnv {
        readonly VITE_APP_NAME: string;
        [key: string]: string | boolean | undefined;
    }

    interface ImportMeta {
        readonly env: ImportMetaEnv;
    }
}

declare module '@inertiajs/core' {
    export interface SharedPageProps extends Record<string, unknown> {
        name: string;
        panel?: string;
        auth?: Auth;
        flash?: Flash;
        publicSettings?: Record<string, string>;
    }
}

declare module 'vue' {
    interface ComponentCustomProperties {
        $inertia: typeof Router;
        $page: Page;
        $headManager: ReturnType<typeof createHeadManager>;
    }
}

export {};