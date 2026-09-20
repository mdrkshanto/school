import { onMounted, onUnmounted, ref } from 'vue';

type BeforeInstallPromptEvent = Event & {
    prompt: () => Promise<void>;
    userChoice: Promise<{ outcome: 'accepted' | 'dismissed'; platform: string }>;
};

function isStandaloneMode(): boolean {
    const standalones = window.matchMedia('(display-mode: standalone)').matches;

    return standalones || (navigator as unknown as { standalone?: boolean }).standalone === true;
}

function isIosDevice(): boolean {
    const ua = navigator.userAgent;

    return /iphone|ipad|ipod/i.test(ua) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
}

export function useInstallPrompt() {
    const deferredPrompt = ref<BeforeInstallPromptEvent | null>(null);
    const canInstall = ref(false);
    const isInstalled = ref(false);
    const isIos = ref(false);

    function promptInstall() {
        if (!deferredPrompt.value) return;

        void deferredPrompt.value.prompt();
        deferredPrompt.value = null;
        canInstall.value = false;
    }

    function onBeforeInstallPrompt(event: Event) {
        event.preventDefault();
        deferredPrompt.value = event as BeforeInstallPromptEvent;
        canInstall.value = true;
    }

    function onAppInstalled() {
        deferredPrompt.value = null;
        canInstall.value = false;
        isInstalled.value = true;
    }

    onMounted(() => {
        isIos.value = isIosDevice();
        isInstalled.value = isStandaloneMode();

        window.addEventListener('beforeinstallprompt', onBeforeInstallPrompt);
        window.addEventListener('appinstalled', onAppInstalled);
    });

    onUnmounted(() => {
        window.removeEventListener('beforeinstallprompt', onBeforeInstallPrompt);
        window.removeEventListener('appinstalled', onAppInstalled);
    });

    return { canInstall, isIos, isInstalled, promptInstall };
}