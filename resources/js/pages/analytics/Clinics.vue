<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import ClinicPin from '@/components/analytics/ClinicPin.vue';
import DataTable from '@/components/analytics/DataTable.vue';
import PageHeader from '@/components/analytics/PageHeader.vue';
import Panel from '@/components/analytics/Panel.vue';
import RangeFilter from '@/components/analytics/RangeFilter.vue';
import { countryFlag, countryName, formatNumber } from '@/lib/format';
import { clinics as clinicsRoute } from '@/routes';
import type { RangeKey } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Clinics', href: clinicsRoute() }],
    },
});

const props = defineProps<{
    range: RangeKey;
    clinics: {
        id: number;
        name: string;
        city: string;
        countryCode: string;
        leads: number;
        calls: number;
        converted: number;
        conversionRate: number;
    }[];
}>();

const max = computed(() => Math.max(1, ...props.clinics.map((c) => c.leads)));
</script>

<template>
    <Head title="Clinics" />

    <div class="flex flex-col gap-5 p-4 sm:p-6">
        <PageHeader
            title="Clinics"
            description="Every clinic that appeared in search results, ranked by the leads Plug N Paw sent it."
        >
            <RangeFilter :range="range" />
        </PageHeader>

        <Panel
            title="Clinic ranking"
            :description="`${clinics.length} clinics across ${new Set(clinics.map((c) => c.countryCode)).size} countries`"
        >
            <DataTable caption="Clinics ranked by leads">
                <template #head>
                    <tr>
                        <th scope="col" class="w-12 px-5 py-2 font-medium">
                            <span class="sr-only">Rank</span>
                        </th>
                        <th scope="col" class="px-5 py-2 font-medium">
                            Clinic
                        </th>
                        <th scope="col" class="w-1/3 px-5 py-2 font-medium">
                            Leads
                        </th>
                        <th
                            scope="col"
                            class="px-5 py-2 text-right font-medium"
                        >
                            Calls
                        </th>
                        <th
                            scope="col"
                            class="px-5 py-2 text-right font-medium"
                        >
                            Converted
                        </th>
                        <th
                            scope="col"
                            class="px-5 py-2 text-right font-medium"
                        >
                            Rate
                        </th>
                    </tr>
                </template>
                <tr v-for="(clinic, index) in clinics" :key="clinic.id">
                    <td class="px-5 py-2.5">
                        <ClinicPin
                            :rank="index + 1"
                            :highlight="index === 0 && clinic.leads > 0"
                        />
                        <span class="sr-only">Rank {{ index + 1 }}</span>
                    </td>
                    <td class="px-5 py-2.5">
                        <span class="font-medium text-foreground">{{
                            clinic.name
                        }}</span>
                        <span class="block text-xs text-muted-foreground">
                            <span aria-hidden="true">{{
                                countryFlag(clinic.countryCode)
                            }}</span>
                            {{ clinic.city }},
                            {{ countryName(clinic.countryCode) }}
                        </span>
                    </td>
                    <td class="px-5 py-2.5">
                        <div class="flex items-center gap-3">
                            <span
                                class="w-10 text-right font-medium text-foreground"
                                >{{ formatNumber(clinic.leads) }}</span
                            >
                            <div
                                class="h-1.5 flex-1 overflow-hidden rounded-full bg-muted"
                                role="presentation"
                            >
                                <div
                                    class="h-full rounded-full bg-[var(--chart-2)]"
                                    :style="{
                                        width: `${(clinic.leads / max) * 100}%`,
                                    }"
                                />
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-2.5 text-right">
                        {{ formatNumber(clinic.calls) }}
                    </td>
                    <td class="px-5 py-2.5 text-right">
                        {{ formatNumber(clinic.converted) }}
                    </td>
                    <td class="px-5 py-2.5 text-right">
                        {{ clinic.leads ? `${clinic.conversionRate}%` : '—' }}
                    </td>
                </tr>
            </DataTable>
        </Panel>
    </div>
</template>
