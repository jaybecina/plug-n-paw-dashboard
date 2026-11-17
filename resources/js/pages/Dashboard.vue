<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import AnalyticsChart from '@/components/analytics/AnalyticsChart.vue';
import ClinicLeaderboard from '@/components/analytics/ClinicLeaderboard.vue';
import DataTable from '@/components/analytics/DataTable.vue';
import KpiStrip from '@/components/analytics/KpiStrip.vue';
import PageHeader from '@/components/analytics/PageHeader.vue';
import Panel from '@/components/analytics/Panel.vue';
import RangeFilter from '@/components/analytics/RangeFilter.vue';
import RankedBars from '@/components/analytics/RankedBars.vue';
import StatusBadge from '@/components/analytics/StatusBadge.vue';
import { formatDateTime, formatDay } from '@/lib/format';
import { clinics as clinicsRoute, dashboard } from '@/routes';
import leads from '@/routes/leads';
import type { Breakdown, Kpi, LeadStatus, RangeKey } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Overview', href: dashboard() }],
    },
});

const props = defineProps<{
    range: RangeKey;
    kpis: Kpi[];
    daily: { date: string; visits: number; searches: number; leads: number }[];
    sources: Breakdown[];
    topClinics: {
        id: number;
        name: string;
        city: string;
        countryCode: string;
        leads: number;
    }[];
    recentLeads: {
        id: number;
        clinic: string;
        city: string;
        action: string;
        status: LeadStatus;
        statusLabel: string;
        occurredAt: string;
    }[];
}>();

const labels = computed(() => props.daily.map((day) => formatDay(day.date)));
const series = computed(() => [
    {
        key: 'visits',
        label: 'Visits',
        data: props.daily.map((d) => d.visits),
        color: 2 as const,
    },
    {
        key: 'searches',
        label: 'Vet searches',
        data: props.daily.map((d) => d.searches),
        color: 1 as const,
    },
]);
const leadSeries = computed(() => [
    {
        key: 'leads',
        label: 'Clinic leads',
        data: props.daily.map((d) => d.leads),
        color: 3 as const,
    },
]);

const link =
    'text-sm font-medium text-primary hover:underline focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none rounded';
</script>

<template>
    <Head title="Overview" />

    <div class="flex flex-col gap-5 p-4 sm:p-6">
        <PageHeader
            title="Overview"
            description="How pet owners found vets through Plug N Paw, and which clinics they reached."
        >
            <RangeFilter :range="range" />
        </PageHeader>

        <KpiStrip :kpis="kpis" :range="range" />

        <div class="grid gap-5 xl:grid-cols-3">
            <Panel
                class="xl:col-span-2"
                title="Visits and vet searches"
                description="Each visit is one browsing session. Searches include typed places and “Use my location”."
            >
                <AnalyticsChart
                    type="line"
                    label="Daily visits and vet searches"
                    :labels="labels"
                    :series="series"
                />
            </Panel>

            <Panel
                title="Clinics getting the most leads"
                description="Map opens and calls from search results."
            >
                <template #actions>
                    <Link
                        :href="clinicsRoute({ query: { range } })"
                        :class="link"
                        >All clinics</Link
                    >
                </template>
                <ClinicLeaderboard :clinics="topClinics" />
            </Panel>
        </div>

        <div class="grid gap-5 xl:grid-cols-3">
            <Panel title="Where visitors come from">
                <RankedBars :items="sources" unit="visits" />
            </Panel>

            <Panel title="Clinic leads per day" class="xl:col-span-2">
                <AnalyticsChart
                    type="bar"
                    label="Daily clinic leads"
                    :labels="labels"
                    :series="leadSeries"
                    :height="200"
                />
            </Panel>
        </div>

        <Panel
            title="Latest clinic leads"
            description="Follow up on new ones from the leads page."
        >
            <template #actions>
                <Link :href="leads.index({ query: { range } })" :class="link"
                    >Manage leads</Link
                >
            </template>
            <p
                v-if="recentLeads.length === 0"
                class="py-6 text-sm text-muted-foreground"
            >
                No clinic leads in this period yet.
            </p>
            <DataTable v-else caption="Latest clinic leads">
                <template #head>
                    <tr>
                        <th scope="col" class="px-5 py-2 font-medium">When</th>
                        <th scope="col" class="px-5 py-2 font-medium">
                            Clinic
                        </th>
                        <th scope="col" class="px-5 py-2 font-medium">
                            What they did
                        </th>
                        <th scope="col" class="px-5 py-2 font-medium">
                            Status
                        </th>
                    </tr>
                </template>
                <tr v-for="lead in recentLeads" :key="lead.id">
                    <td
                        class="px-5 py-2.5 whitespace-nowrap text-muted-foreground"
                    >
                        {{ formatDateTime(lead.occurredAt) }}
                    </td>
                    <td class="px-5 py-2.5">
                        <span class="font-medium text-foreground">{{
                            lead.clinic
                        }}</span>
                        <span class="text-muted-foreground">
                            · {{ lead.city }}</span
                        >
                    </td>
                    <td class="px-5 py-2.5 text-muted-foreground">
                        {{ lead.action }}
                    </td>
                    <td class="px-5 py-2.5">
                        <StatusBadge
                            :status="lead.status"
                            :label="lead.statusLabel"
                        />
                    </td>
                </tr>
            </DataTable>
        </Panel>
    </div>
</template>
