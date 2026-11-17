<script setup lang="ts">
import { ArrowDownRight, ArrowUpRight, Minus } from '@lucide/vue';
import { formatValue } from '@/lib/format';
import type { Kpi, RangeKey } from '@/types';

defineProps<{
    kpis: Kpi[];
    range: RangeKey;
}>();

function tone(kpi: Kpi): 'good' | 'bad' | 'flat' {
    if (kpi.delta === null || Math.abs(kpi.delta) < 0.5) {
        return 'flat';
    }

    return kpi.delta > 0 === kpi.higherIsBetter ? 'good' : 'bad';
}

const rangeDays = { '7d': 7, '30d': 30, '90d': 90 } as const;
</script>

<template>
    <dl
        class="grid grid-cols-2 overflow-hidden rounded-xl border border-border bg-card lg:grid-cols-4"
    >
        <div
            v-for="(kpi, index) in kpis"
            :key="kpi.key"
            class="flex flex-col gap-1 border-border p-4 sm:p-5"
            :class="{
                'border-l': index % 2 === 1,
                'lg:border-l': index > 0,
                'border-t lg:border-t-0': index > 1,
            }"
        >
            <dt class="text-sm text-muted-foreground">{{ kpi.label }}</dt>
            <dd
                class="font-heading text-[1.75rem] leading-tight font-semibold text-foreground tabular-nums sm:text-3xl"
            >
                {{ formatValue(kpi.value, kpi.format) }}
            </dd>
            <dd class="flex items-center gap-1 text-xs text-muted-foreground">
                <template v-if="kpi.delta !== null">
                    <span
                        class="inline-flex items-center gap-0.5 font-medium tabular-nums"
                        :class="{
                            'text-good': tone(kpi) === 'good',
                            'text-destructive': tone(kpi) === 'bad',
                        }"
                    >
                        <ArrowUpRight
                            v-if="kpi.delta >= 0.5"
                            class="size-3.5"
                            aria-hidden="true"
                        />
                        <ArrowDownRight
                            v-else-if="kpi.delta <= -0.5"
                            class="size-3.5"
                            aria-hidden="true"
                        />
                        <Minus v-else class="size-3.5" aria-hidden="true" />
                        {{ kpi.delta > 0 ? '+' : '' }}{{ kpi.delta }}%
                    </span>
                    <span>vs previous {{ rangeDays[range] }} days</span>
                </template>
                <span v-else>No earlier data to compare</span>
            </dd>
        </div>
    </dl>
</template>
