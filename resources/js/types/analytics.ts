export type RangeKey = '7d' | '30d' | '90d';

export type KpiFormat = 'number' | 'percent' | 'duration' | 'currency' | 'ms';

export type Kpi = {
    key: string;
    label: string;
    value: number;
    previous: number;
    delta: number | null;
    format: KpiFormat;
    higherIsBetter: boolean;
};

export type Breakdown = { key: string; label: string; value: number };

export type Option = { value: string; label: string };

export type PaginatorLink = {
    url: string | null;
    label: string;
    active: boolean;
};

export type Paginator<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
    per_page: number;
    prev_page_url: string | null;
    next_page_url: string | null;
    links: PaginatorLink[];
};

/** Index into the fixed categorical order defined in app.css. */
export type SeriesColor = 1 | 2 | 3 | 4 | 5 | 'failed' | 'neutral';

export type ChartSeries = {
    key: string;
    label: string;
    data: number[];
    color: SeriesColor;
};

export type LeadStatus = 'new' | 'contacted' | 'converted' | 'lost';
