<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import type { RangeKey } from '@/types';

const props = defineProps<{ range: RangeKey }>();

const options: { value: RangeKey; label: string; long: string }[] = [
    { value: '7d', label: '7 days', long: 'Last 7 days' },
    { value: '30d', label: '30 days', long: 'Last 30 days' },
    { value: '90d', label: '90 days', long: 'Last 90 days' },
];

const page = usePage();

function select(range: RangeKey) {
    if (range === props.range) {
        return;
    }

    const url = new URL(page.url, window.location.origin);
    url.searchParams.set('range', range);
    url.searchParams.delete('page');

    router.get(
        url.pathname + url.search,
        {},
        { preserveScroll: true, preserveState: true, replace: true },
    );
}
</script>

<template>
    <div
        role="radiogroup"
        aria-label="Date range"
        class="inline-flex rounded-lg border border-border bg-card p-0.5 text-sm"
    >
        <button
            v-for="option in options"
            :key="option.value"
            type="button"
            role="radio"
            :aria-checked="option.value === range"
            :aria-label="option.long"
            class="rounded-md px-3 py-1.5 font-medium text-muted-foreground transition-colors hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none aria-checked:bg-pine aria-checked:text-pine-foreground"
            @click="select(option.value)"
        >
            {{ option.label }}
        </button>
    </div>
</template>
