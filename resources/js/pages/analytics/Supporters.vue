<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ExternalLink, Repeat } from '@lucide/vue';
import { computed } from 'vue';
import AnalyticsChart from '@/components/analytics/AnalyticsChart.vue';
import DataPagination from '@/components/analytics/DataPagination.vue';
import DataTable from '@/components/analytics/DataTable.vue';
import EmptyState from '@/components/analytics/EmptyState.vue';
import KpiStrip from '@/components/analytics/KpiStrip.vue';
import PageHeader from '@/components/analytics/PageHeader.vue';
import Panel from '@/components/analytics/Panel.vue';
import RangeFilter from '@/components/analytics/RangeFilter.vue';
import { formatCents, formatDateTime, formatMonth } from '@/lib/format';
import { supporters as supportersRoute } from '@/routes';
import type { Kpi, Paginator, RangeKey } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Supporters', href: supportersRoute() }],
    },
});

type SupporterRow = {
    id: number;
    name: string | null;
    amountCents: number;
    currency: string;
    message: string | null;
    isMonthly: boolean;
    donatedAt: string;
};

const props = defineProps<{
    range: RangeKey;
    kpis: Kpi[];
    monthly: { month: string; amount: number }[];
    supporters: Paginator<SupporterRow>;
}>();

const labels = computed(() => props.monthly.map((m) => formatMonth(m.month)));
const series = computed(() => [
    {
        key: 'raised',
        label: 'Raised',
        data: props.monthly.map((m) => m.amount),
        color: 1 as const,
    },
]);
</script>

<template>
    <Head title="Supporters" />

    <div class="flex flex-col gap-5 p-4 sm:p-6">
        <PageHeader
            title="Supporters"
            description="Ko-fi gifts that keep the site free for pet owners."
        >
            <a
                href="https://ko-fi.com/jaybecina"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-border bg-card px-3 text-sm font-medium text-foreground hover:bg-muted focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
            >
                Ko-fi page <ExternalLink class="size-3.5" aria-hidden="true" />
            </a>
            <RangeFilter :range="range" />
        </PageHeader>

        <KpiStrip :kpis="kpis" :range="range" />

        <div class="grid gap-5 xl:grid-cols-5">
            <Panel
                class="xl:col-span-2"
                title="Raised per month"
                description="Last six months, whatever range is picked."
            >
                <AnalyticsChart
                    type="bar"
                    label="Amount raised per month"
                    :labels="labels"
                    :series="series"
                    format="currency"
                    :height="240"
                />
            </Panel>

            <Panel class="xl:col-span-3" title="Recent gifts">
                <EmptyState
                    v-if="supporters.data.length === 0"
                    title="No gifts in this period"
                    description="Pick a longer date range to see earlier supporters."
                />
                <template v-else>
                    <DataTable caption="Recent gifts">
                        <template #head>
                            <tr>
                                <th scope="col" class="px-5 py-2 font-medium">
                                    Supporter
                                </th>
                                <th
                                    scope="col"
                                    class="px-5 py-2 text-right font-medium"
                                >
                                    Amount
                                </th>
                                <th scope="col" class="px-5 py-2 font-medium">
                                    Message
                                </th>
                                <th scope="col" class="px-5 py-2 font-medium">
                                    When
                                </th>
                            </tr>
                        </template>
                        <tr
                            v-for="supporter in supporters.data"
                            :key="supporter.id"
                        >
                            <td class="px-5 py-2.5 font-medium text-foreground">
                                {{ supporter.name ?? 'Someone' }}
                            </td>
                            <td
                                class="px-5 py-2.5 text-right whitespace-nowrap"
                            >
                                {{ formatCents(supporter.amountCents, true) }}
                                <span
                                    v-if="supporter.isMonthly"
                                    class="ml-1 inline-flex items-center gap-0.5 text-xs text-muted-foreground"
                                >
                                    <Repeat
                                        class="size-3"
                                        aria-hidden="true"
                                    />monthly
                                </span>
                            </td>
                            <td
                                class="max-w-xs px-5 py-2.5 text-muted-foreground"
                            >
                                <span class="line-clamp-2">{{
                                    supporter.message ?? '—'
                                }}</span>
                            </td>
                            <td
                                class="px-5 py-2.5 whitespace-nowrap text-muted-foreground"
                            >
                                {{ formatDateTime(supporter.donatedAt) }}
                            </td>
                        </tr>
                    </DataTable>
                    <DataPagination :paginator="supporters" noun="gifts" />
                </template>
            </Panel>
        </div>
    </div>
</template>
