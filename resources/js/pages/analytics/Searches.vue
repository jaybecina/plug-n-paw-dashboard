<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import AnalyticsChart from '@/components/analytics/AnalyticsChart.vue';
import DataPagination from '@/components/analytics/DataPagination.vue';
import DataTable from '@/components/analytics/DataTable.vue';
import EmptyState from '@/components/analytics/EmptyState.vue';
import FilterSelect from '@/components/analytics/FilterSelect.vue';
import KpiStrip from '@/components/analytics/KpiStrip.vue';
import PageHeader from '@/components/analytics/PageHeader.vue';
import Panel from '@/components/analytics/Panel.vue';
import RangeFilter from '@/components/analytics/RangeFilter.vue';
import { useQueryFilters } from '@/composables/useQueryFilters';
import {
    countryFlag,
    formatDateTime,
    formatDay,
    formatNumber,
} from '@/lib/format';
import { searches as searchesRoute } from '@/routes';
import type { Kpi, Option, Paginator, RangeKey } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Vet searches', href: searchesRoute() }],
    },
});

type SearchRow = {
    id: number;
    searchedAt: string;
    type: string;
    typeLabel: string;
    query: string | null;
    countryCode: string;
    results: number;
    latencyMs: number;
    status: string;
    statusLabel: string;
    isSuccess: boolean;
};

const props = defineProps<{
    range: RangeKey;
    kpis: Kpi[];
    daily: { date: string; ok: number; no_results: number; failed: number }[];
    topQueries: { query: string; searches: number; avgResults: number }[];
    searches: Paginator<SearchRow>;
    filters: { status: string | null; type: string | null };
    statuses: Option[];
    types: Option[];
}>();

const { apply } = useQueryFilters();

const status = computed({
    get: () => props.filters.status,
    set: (value) => apply({ status: value }),
});
const type = computed({
    get: () => props.filters.type,
    set: (value) => apply({ type: value }),
});

const labels = computed(() => props.daily.map((day) => formatDay(day.date)));
const series = computed(() => [
    {
        key: 'ok',
        label: 'Clinics found',
        data: props.daily.map((d) => d.ok),
        color: 2 as const,
    },
    {
        key: 'no_results',
        label: 'No clinics nearby',
        data: props.daily.map((d) => d.no_results),
        color: 'neutral' as const,
    },
    {
        key: 'failed',
        label: 'Failed',
        data: props.daily.map((d) => d.failed),
        color: 'failed' as const,
    },
]);
</script>

<template>
    <Head title="Vet searches" />

    <div class="flex flex-col gap-5 p-4 sm:p-6">
        <PageHeader
            title="Vet searches"
            description="Every search on the Find a vet page: what people typed, how fast the map provider answered and what went wrong."
        >
            <RangeFilter :range="range" />
        </PageHeader>

        <KpiStrip :kpis="kpis" :range="range" />

        <div class="grid gap-5 xl:grid-cols-3">
            <Panel
                class="xl:col-span-2"
                title="Search outcomes per day"
                description="Failures are map-provider timeouts or outages, places that couldn't be found, and invalid input."
            >
                <AnalyticsChart
                    type="bar"
                    stacked
                    label="Daily vet searches by outcome"
                    :labels="labels"
                    :series="series"
                />
            </Panel>

            <Panel
                title="Most searched"
                description="Typed searches, grouped regardless of capitals."
            >
                <p
                    v-if="topQueries.length === 0"
                    class="py-6 text-sm text-muted-foreground"
                >
                    No typed searches in this period yet.
                </p>
                <ol v-else class="flex flex-col divide-y divide-border text-sm">
                    <li
                        v-for="item in topQueries"
                        :key="item.query"
                        class="flex items-center justify-between gap-3 py-2 first:pt-0"
                    >
                        <span class="truncate text-foreground">{{
                            item.query
                        }}</span>
                        <span
                            class="shrink-0 text-muted-foreground tabular-nums"
                        >
                            <span class="font-medium text-foreground">{{
                                formatNumber(item.searches)
                            }}</span>
                            · {{ item.avgResults }} clinics avg
                        </span>
                    </li>
                </ol>
            </Panel>
        </div>

        <Panel title="Search log">
            <template #actions>
                <FilterSelect
                    v-model="status"
                    label="Outcome"
                    all-label="All outcomes"
                    :options="statuses"
                />
                <FilterSelect
                    v-model="type"
                    label="Search type"
                    all-label="All search types"
                    :options="types"
                />
            </template>

            <EmptyState
                v-if="searches.data.length === 0"
                title="No searches match these filters"
                description="Try another outcome or search type, or widen the date range."
            />
            <template v-else>
                <DataTable caption="Search log">
                    <template #head>
                        <tr>
                            <th scope="col" class="px-5 py-2 font-medium">
                                When
                            </th>
                            <th scope="col" class="px-5 py-2 font-medium">
                                Search
                            </th>
                            <th scope="col" class="px-5 py-2 font-medium">
                                Outcome
                            </th>
                            <th
                                scope="col"
                                class="px-5 py-2 text-right font-medium"
                            >
                                Clinics
                            </th>
                            <th
                                scope="col"
                                class="px-5 py-2 text-right font-medium"
                            >
                                Response time
                            </th>
                        </tr>
                    </template>
                    <tr v-for="row in searches.data" :key="row.id">
                        <td
                            class="px-5 py-2.5 whitespace-nowrap text-muted-foreground"
                        >
                            {{ formatDateTime(row.searchedAt) }}
                        </td>
                        <td class="px-5 py-2.5">
                            <span aria-hidden="true">{{
                                countryFlag(row.countryCode)
                            }}</span>
                            <span
                                v-if="row.query"
                                class="ml-1.5 text-foreground"
                                >“{{ row.query }}”</span
                            >
                            <span v-else class="ml-1.5 text-muted-foreground">{{
                                row.typeLabel
                            }}</span>
                        </td>
                        <td class="px-5 py-2.5">
                            <span
                                class="inline-flex items-center gap-1.5"
                                :class="
                                    row.isSuccess
                                        ? 'text-foreground'
                                        : 'font-medium text-destructive'
                                "
                            >
                                <span
                                    class="size-1.5 rounded-full"
                                    :class="
                                        row.isSuccess
                                            ? row.status === 'ok'
                                                ? 'bg-[var(--chart-2)]'
                                                : 'bg-[var(--chart-neutral)]'
                                            : 'bg-[var(--chart-failed)]'
                                    "
                                    aria-hidden="true"
                                />
                                {{ row.statusLabel }}
                            </span>
                        </td>
                        <td class="px-5 py-2.5 text-right">
                            {{ row.results }}
                        </td>
                        <td class="px-5 py-2.5 text-right whitespace-nowrap">
                            {{ formatNumber(row.latencyMs) }} ms
                        </td>
                    </tr>
                </DataTable>
                <DataPagination :paginator="searches" noun="searches" />
            </template>
        </Panel>
    </div>
</template>
