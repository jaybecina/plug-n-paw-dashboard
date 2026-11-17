<script setup lang="ts">
import { formatValue } from '@/lib/format';
import type { ChartSeries, KpiFormat } from '@/types';

defineProps<{
    caption: string;
    labels: string[];
    series: ChartSeries[];
    format: KpiFormat;
}>();
</script>

<template>
    <details class="group mt-3 text-sm">
        <summary
            class="w-fit cursor-pointer rounded text-xs text-muted-foreground hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
        >
            View as table
        </summary>
        <div
            class="mt-2 max-h-64 overflow-auto rounded-lg border border-border"
        >
            <table class="w-full text-left text-xs tabular-nums">
                <caption class="sr-only">
                    {{
                        caption
                    }}
                </caption>
                <thead class="sticky top-0 bg-muted text-muted-foreground">
                    <tr>
                        <th scope="col" class="px-3 py-2 font-medium">Date</th>
                        <th
                            v-for="item in series"
                            :key="item.key"
                            scope="col"
                            class="px-3 py-2 text-right font-medium"
                        >
                            {{ item.label }}
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <tr v-for="(label, index) in labels" :key="label">
                        <th scope="row" class="px-3 py-1.5 font-normal">
                            {{ label }}
                        </th>
                        <td
                            v-for="item in series"
                            :key="item.key"
                            class="px-3 py-1.5 text-right"
                        >
                            {{ formatValue(item.data[index] ?? 0, format) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </details>
</template>
