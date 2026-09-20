export function formatDate(value: string | number | Date | null | undefined): string {
    if (!value) return '—';

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) return '—';

    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

export function formatDateTime(value: string | number | Date | null | undefined): string {
    if (!value) return '—';

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) return '—';

    return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
}

export function formatMoney(value: number | string | null | undefined): string {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        maximumFractionDigits: 2,
    }).format(Number(value ?? 0));
}

export function titleCase(value: string | null | undefined): string {
    if (!value) return '';
    return value
        .replace(/[_-]+/g, ' ')
        .replace(/\b\w/g, (letter) => letter.toUpperCase());
}

export function initials(name: string | null | undefined): string {
    if (!name) return '?';
    return name
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase() ?? '')
        .join('');
}

export function avatarUrl(path: string | null | undefined): string {
    if (!path) return '';
    return `/media/${path.replace(/^\/+/, '')}`;
}

export function todayIso(): string {
    return new Date().toISOString().split('T')[0];
}