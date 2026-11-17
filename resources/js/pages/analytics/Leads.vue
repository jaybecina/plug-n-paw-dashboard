<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Download, Search } from '@lucide/vue';
import { useDebounceFn } from '@vueuse/core';
import { computed, ref } from 'vue';
import DataPagination from '@/components/analytics/DataPagination.vue';
import DataTable from '@/components/analytics/DataTable.vue';
import EmptyState from '@/components/analytics/EmptyState.vue';
import FilterSelect from '@/components/analytics/FilterSelect.vue';
import KpiStrip from '@/components/analytics/KpiStrip.vue';
import PageHeader from '@/components/analytics/PageHeader.vue';
import Panel from '@/components/analytics/Panel.vue';
import RangeFilter from '@/components/analytics/RangeFilter.vue';
import StatusBadge from '@/components/analytics/StatusBadge.vue';
import { useQueryFilters } from '@/composables/useQueryFilters';
import { countryFlag, formatDateTime } from '@/lib/format';
import leadsRoutes from '@/routes/leads';
import type { Kpi, LeadStatus, Option, Paginator, RangeKey } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Clinic leads', href: leadsRoutes.index() }],
    },
});

type LeadRow = {
    id: number;
    occurredAt: string;
    clinic: string;
    city: string;
    countryCode: string;
    action: string;
    actionLabel: string;
    status: LeadStatus;
};

const props = defineProps<{
    range: RangeKey;
    kpis: Kpi[];
    leads: Paginator<LeadRow>;
    filters: {
        status: string | null;
        action: string | null;
        clinic: string | null;
    };
    statuses: Option[];
    actions: Option[];
}>();

const { apply } = useQueryFilters();

const status = computed({
    get: () => props.filters.status,
    set: (value) => apply({ status: value }),
});
const action = computed({
    get: () => props.filters.action,
    set: (value) => apply({ action: value }),
});

const clinic = ref(props.filters.clinic ?? '');
const searchClinic = useDebounceFn(
    (value: string) => apply({ clinic: value.trim() || null }),
    350,
);

const exportUrl = computed(() =>
    leadsRoutes.export.url({
        query: {
            range: props.range,
            status: props.filters.status ?? undefined,
            action: props.filters.action ?? undefined,
            clinic: props.filters.clinic ?? undefined,
        },
    }),
);

const updating = ref<number | null>(null);

function changeStatus(lead: LeadRow, value: string) {
    updating.value = lead.id;
    router.patch(
        leadsRoutes.update(lead.id).url,
        { status: value },
        { preserveScroll: true, onFinish: () => (updating.value = null) },
    );
}
</script>

<template>
    <Head title="Clinic leads" />

    <div class="flex flex-col gap-5 p-4 sm:p-6">
        <PageHeader
            title="Clinic leads"
            description="A lead is a visitor who opened a clinic in Maps or tapped to call it. Track whether the clinic saw them."
        >
            <RangeFilter :range="range" />
        </PageHeader>

        <KpiStrip :kpis="kpis" :range="range" />

        <Panel title="All leads">
            <template #actions>
                <label class="relative inline-flex items-center">
                    <span class="sr-only">Search clinics</span>
                    <Search
                        class="pointer-events-none absolute left-2.5 size-4 text-muted-foreground"
                        aria-hidden="true"
                    />
                    <input
                        v-model="clinic"
                        type="search"
                        placeholder="Search clinics"
                        class="h-9 w-48 rounded-lg border border-input bg-card pr-3 pl-8 text-sm text-foreground placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                        @input="searchClinic(clinic)"
                    />
                </label>
                <FilterSelect
                    v-model="status"
                    label="Status"
                    all-label="All statuses"
                    :options="statuses"
                />
                <FilterSelect
                    v-model="action"
                    label="Action"
                    all-label="All actions"
                    :options="actions"
                />
                <a
                    :href="exportUrl"
                    class="inline-flex h-9 items-center gap-1.5 rounded-lg bg-primary px-3 text-sm font-medium text-primary-foreground transition-opacity hover:opacity-90 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                >
                    <Download class="size-4" aria-hidden="true" />
                    Export CSV
                </a>
            </template>

            <EmptyState
                v-if="leads.data.length === 0"
                title="No leads match these filters"
                description="Clear the search or pick another status, or widen the date range."
            />
            <template v-else>
                <DataTable caption="Clinic leads">
                    <template #head>
                        <tr>
                            <th scope="col" class="px-5 py-2 font-medium">
                                When
                            </th>
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
                    <tr v-for="lead in leads.data" :key="lead.id">
                        <td
                            class="px-5 py-2.5 whitespace-nowrap text-muted-foreground"
                        >
                            {{ formatDateTime(lead.occurredAt) }}
                        </td>
                        <td class="px-5 py-2.5">
                            <span class="font-medium text-foreground">{{
                                lead.clinic
                            }}</span>
                            <span class="block text-xs text-muted-foreground">
                                <span aria-hidden="true">{{
                                    countryFlag(lead.countryCode)
                                }}</span>
                                {{ lead.city }}
                            </span>
                        </td>
                        <td class="px-5 py-2.5 text-muted-foreground">
                            {{ lead.actionLabel }}
                        </td>
                        <td class="px-5 py-2.5">
                            <div class="flex items-center gap-2">
                                <StatusBadge :status="lead.status" />
                                <label class="text-xs">
                                    <span class="sr-only"
                                        >Change status for lead {{ lead.id }} at
                                        {{ lead.clinic }}</span
                                    >
                                    <select
                                        value=""
                                        :aria-label="`Change status for lead ${lead.id} at ${lead.clinic}`"
                                        :disabled="updating === lead.id"
                                        class="h-7 rounded-md border border-input bg-card px-1.5 text-xs text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none disabled:opacity-50"
                                        @change="
                                            changeStatus(
                                                lead,
                                                (
                                                    $event.target as HTMLSelectElement
                                                ).value,
                                            )
                                        "
                                    >
                                        <option value="" disabled>
                                            Change status
                                        </option>
                                        <option
                                            v-for="option in statuses"
                                            :key="option.value"
                                            :value="option.value"
                                            :disabled="
                                                option.value === lead.status
                                            "
                                        >
                                            {{ option.label }}
                                        </option>
                                    </select>
                                </label>
                            </div>
                        </td>
                    </tr>
                </DataTable>
                <DataPagination :paginator="leads" noun="leads" />
            </template>
        </Panel>
    </div>
</template>
