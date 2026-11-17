<script setup lang="ts">
import ClinicPin from '@/components/analytics/ClinicPin.vue';
import { countryFlag, formatNumber } from '@/lib/format';

defineProps<{
    clinics: {
        id: number;
        name: string;
        city: string;
        countryCode: string;
        leads: number;
    }[];
}>();
</script>

<template>
    <p v-if="clinics.length === 0" class="py-6 text-sm text-muted-foreground">
        No clinic leads in this period yet.
    </p>
    <ol v-else class="flex flex-col divide-y divide-border">
        <li
            v-for="(clinic, index) in clinics"
            :key="clinic.id"
            class="flex items-center gap-3 py-2.5 first:pt-0 last:pb-0"
        >
            <ClinicPin :rank="index + 1" :highlight="index === 0" />
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-medium text-foreground">
                    {{ clinic.name }}
                </p>
                <p class="truncate text-xs text-muted-foreground">
                    <span aria-hidden="true">{{
                        countryFlag(clinic.countryCode)
                    }}</span>
                    {{ clinic.city }}
                </p>
            </div>
            <p class="shrink-0 text-right text-sm tabular-nums">
                <span class="font-semibold text-foreground">{{
                    formatNumber(clinic.leads)
                }}</span>
                <span class="sr-only"> leads</span>
                <span
                    class="block text-xs text-muted-foreground"
                    aria-hidden="true"
                    >leads</span
                >
            </p>
        </li>
    </ol>
</template>
