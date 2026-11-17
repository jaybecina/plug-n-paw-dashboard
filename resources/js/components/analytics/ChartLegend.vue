<script setup lang="ts">
import type { ChartSeries } from '@/types';

defineProps<{
    series: Pick<ChartSeries, 'key' | 'label' | 'color'>[];
    shape?: 'line' | 'square';
}>();
</script>

<template>
    <ul class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-muted-foreground">
        <li
            v-for="item in series"
            :key="item.key"
            class="inline-flex items-center gap-1.5"
        >
            <span
                aria-hidden="true"
                :class="
                    shape === 'square'
                        ? 'size-2.5 rounded-[3px]'
                        : 'h-0.5 w-3.5 rounded-full'
                "
                :style="{ background: `var(--chart-${item.color})` }"
            />
            {{ item.label }}
        </li>
    </ul>
</template>
