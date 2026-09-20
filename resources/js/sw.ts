function register() {
    if (!('serviceWorker' in navigator)) {
        return;
    }

    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {
            // No-op: SW is only expected to exist after a production build.
        });
    });
}

if (import.meta.env.PROD) {
    register();
}