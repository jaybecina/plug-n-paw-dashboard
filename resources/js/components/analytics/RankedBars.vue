<script setup lang="ts">
import { computed } from 'vue';
import { formatNumber } from '@/lib/format';

const props = defineProps<{
    items: { key: string; label: string; value: number }[];
    /** Unit read after the value, e.g. "visits". */
    unit: string;
}>();

const total = computed(() =>
    props.items.reduce((sum, item) => sum + item.value, 0),
);
const max = computed(() =>
    Math.max(1, ...props.items.map((item) => item.value)),
);

function share(value: number): string {
    return total.value ? `${Math.round((value / total.value) * 100)}%` : '0%';
}
</script>

<template>
    <p v-if="items.length === 0" class="py-6 text-sm text-muted-foreground">
        No {{ unit }} in this period yet.
    </p>
    <ul v-else class="flex flex-col gap-3">
        <li v-for="item in items" :key="item.key" class="text-sm">
            <div class="flex items-baseline justify-between gap-3">
                <span
                    class="flex min-w-0 items-center gap-2 truncate text-foreground"
                >
                    <slot name="label" :item="item">{{ item.label }}</slot>
                </span>
                <span class="shrink-0 text-muted-foreground tabular-nums">
                    <span class="font-medium text-foreground">{{
                        formatNumber(item.value)
                    }}</span>
                    · {{ share(item.value) }}
                </span>
            </div>
            <div
                class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-muted"
                role="presentation"
            >
                <div
                    class="h-full rounded-full bg-[var(--chart-2)]"
                    :style="{ width: `${(item.value / max) * 100}%` }"
                />
            </div>
        </li>
    </ul>
</template>
