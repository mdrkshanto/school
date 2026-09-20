import Swal from 'sweetalert2';

type ToastIcon = 'success' | 'error' | 'warning' | 'info';

export type ConfirmOptions = {
    title?: string;
    text?: string;
    confirmText?: string;
};

export function useToast() {
    const fire = (icon: ToastIcon, title: string, html?: string) =>
        Swal.fire({
            icon,
            title,
            html,
            toast: true,
            position: 'top-end',
            timer: 2800,
            timerProgressBar: true,
            showConfirmButton: false,
        });

    return {
        success: (title: string) => fire('success', title),
        error: (title: string) => fire('error', title),
        info: (title: string) => fire('info', title),
        warning: (title: string) => fire('warning', title),
        confirm: async (options: ConfirmOptions = {}) =>
            Swal.fire({
                title: options.title ?? 'Are you sure?',
                text: options.text ?? 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: options.confirmText ?? 'Yes, continue',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b',
                reverseButtons: true,
            }),
    };
}