<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import AnalyticsChart from '@/components/analytics/AnalyticsChart.vue';
import DataTable from '@/components/analytics/DataTable.vue';
import KpiStrip from '@/components/analytics/KpiStrip.vue';
import PageHeader from '@/components/analytics/PageHeader.vue';
import Panel from '@/components/analytics/Panel.vue';
import RangeFilter from '@/components/analytics/RangeFilter.vue';
import RankedBars from '@/components/analytics/RankedBars.vue';
import {
    countryFlag,
    countryName,
    formatDay,
    formatNumber,
} from '@/lib/format';
import { traffic } from '@/routes';
import type { Breakdown, Kpi, RangeKey } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Traffic', href: traffic() }],
    },
});

const props = defineProps<{
    range: RangeKey;
    kpis: Kpi[];
    daily: { date: string; visits: number }[];
    sources: Breakdown[];
    devices: Breakdown[];
    countries: { code: string; value: number }[];
    landingPages: { path: string; visits: number; bounceRate: number }[];
}>();

const labels = computed(() => props.daily.map((day) => formatDay(day.date)));
const series = computed(() => [
    {
        key: 'visits',
        label: 'Visits',
        data: props.daily.map((d) => d.visits),
        color: 2 as const,
    },
]);
const countryItems = computed(() =>
    props.countries.map((c) => ({
        key: c.code,
        label: countryName(c.code),
        value: c.value,
    })),
);
const pageNames: Record<string, string> = {
    '/': 'Home',
    '/find-vet': 'Find a vet',
};
</script>

<template>
    <Head title="Traffic" />

    <div class="flex flex-col gap-5 p-4 sm:p-6">
        <PageHeader
            title="Traffic"
            description="Who visited plug-n-paw-web.vercel.app, where they came from and how long they stayed."
        >
            <RangeFilter :range="range" />
        </PageHeader>

        <KpiStrip :kpis="kpis" :range="range" />

        <Panel title="Visits per day">
            <AnalyticsChart
                type="line"
                label="Daily visits"
                :labels="labels"
                :series="series"
            />
        </Panel>

        <div class="grid gap-5 lg:grid-cols-3">
            <Panel title="Sources">
                <RankedBars :items="sources" unit="visits" />
            </Panel>
            <Panel title="Devices">
                <RankedBars :items="devices" unit="visits" />
            </Panel>
            <Panel title="Countries">
                <RankedBars :items="countryItems" unit="visits">
                    <template #label="{ item }">
                        <span aria-hidden="true">{{
                            countryFlag(item.key)
                        }}</span>
                        {{ item.label }}
                    </template>
                </RankedBars>
            </Panel>
        </div>

        <Panel
            title="Landing pages"
            description="The first page people saw. A bounce is a visit that left without a second page or a search."
        >
            <DataTable caption="Landing pages">
                <template #head>
                    <tr>
                        <th scope="col" class="px-5 py-2 font-medium">Page</th>
                        <th
                            scope="col"
                            class="px-5 py-2 text-right font-medium"
                        >
                            Visits
                        </th>
                        <th
                            scope="col"
                            class="px-5 py-2 text-right font-medium"
                        >
                            Bounce rate
                        </th>
                    </tr>
                </template>
                <tr v-for="page in landingPages" :key="page.path">
                    <td class="px-5 py-2.5">
                        <span class="font-medium text-foreground">{{
                            pageNames[page.path] ?? page.path
                        }}</span>
                        <span
                            class="ml-2 font-mono text-xs text-muted-foreground"
                            >{{ page.path }}</span
                        >
                    </td>
                    <td class="px-5 py-2.5 text-right">
                        {{ formatNumber(page.visits) }}
                    </td>
                    <td class="px-5 py-2.5 text-right">
                        {{ page.bounceRate }}%
                    </td>
                </tr>
                <tr v-if="landingPages.length === 0">
                    <td colspan="3" class="px-5 py-6 text-muted-foreground">
                        No visits in this period yet.
                    </td>
                </tr>
            </DataTable>
        </Panel>
    </div>
</template>
