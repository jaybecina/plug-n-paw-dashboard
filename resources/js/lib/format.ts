import type { KpiFormat } from '@/types';

const integer = new Intl.NumberFormat('en-US');
const oneDecimal = new Intl.NumberFormat('en-US', { maximumFractionDigits: 1 });
const usd = new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    maximumFractionDigits: 0,
});
const usdCents = new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
});
const shortDate = new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: 'numeric',
});
const dateTime = new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
});
const monthName = new Intl.DateTimeFormat('en-US', {
    month: 'short',
    year: 'numeric',
});
const regions = new Intl.DisplayNames(['en'], { type: 'region' });

export function formatNumber(value: number): string {
    return Number.isInteger(value)
        ? integer.format(value)
        : oneDecimal.format(value);
}

export function formatDuration(seconds: number): string {
    const minutes = Math.floor(seconds / 60);
    const rest = Math.round(seconds % 60);

    return minutes > 0 ? `${minutes}m ${rest}s` : `${rest}s`;
}

export function formatCents(cents: number, exact = false): string {
    return (exact ? usdCents : usd).format(cents / 100);
}

export function formatValue(value: number, format: KpiFormat): string {
    switch (format) {
        case 'percent':
            return `${oneDecimal.format(value)}%`;
        case 'duration':
            return formatDuration(value);
        case 'currency':
            return formatCents(value);
        case 'ms':
            return `${integer.format(value)} ms`;
        default:
            return formatNumber(value);
    }
}

/** Parses Y-m-d as a local calendar day (not UTC midnight). */
function parseDay(day: string): Date {
    const [y, m, d] = day.split('-').map(Number);

    return new Date(y, m - 1, d);
}

export function formatDay(day: string): string {
    return shortDate.format(parseDay(day));
}

export function formatMonth(month: string): string {
    return monthName.format(parseDay(`${month}-01`));
}

export function formatDateTime(iso: string): string {
    return dateTime.format(new Date(iso));
}

export function countryName(code: string): string {
    return regions.of(code) ?? code;
}

/** Regional-indicator emoji flag for an ISO 3166 alpha-2 code. */
export function countryFlag(code: string): string {
    return code
        .toUpperCase()
        .replace(/./g, (char) =>
            String.fromCodePoint(127397 + char.charCodeAt(0)),
        );
}
