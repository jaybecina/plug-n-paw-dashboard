<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from '@lucide/vue';
import { formatNumber } from '@/lib/format';
import type { Paginator } from '@/types';

defineProps<{
    paginator: Paginator<unknown>;
    noun: string;
}>();

const button =
    'inline-flex h-8 items-center gap-1 rounded-lg border border-border bg-card px-2.5 text-sm font-medium text-foreground transition-colors hover:bg-muted focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none';
</script>

<template>
    <nav
        v-if="paginator.total > 0"
        class="mt-4 flex flex-wrap items-center justify-between gap-3 text-sm text-muted-foreground"
        :aria-label="`${noun} pages`"
    >
        <p class="tabular-nums">
            Showing {{ formatNumber(paginator.from ?? 0) }}–{{
                formatNumber(paginator.to ?? 0)
            }}
            of {{ formatNumber(paginator.total) }} {{ noun }}
        </p>
        <div class="flex items-center gap-2">
            <Link
                v-if="paginator.prev_page_url"
                :href="paginator.prev_page_url"
                preserve-scroll
                preserve-state
                :class="button"
                rel="prev"
            >
                <ChevronLeft class="size-4" aria-hidden="true" /> Previous
            </Link>
            <span class="tabular-nums">
                Page {{ paginator.current_page }} of {{ paginator.last_page }}
            </span>
            <Link
                v-if="paginator.next_page_url"
                :href="paginator.next_page_url"
                preserve-scroll
                preserve-state
                :class="button"
                rel="next"
            >
                Next <ChevronRight class="size-4" aria-hidden="true" />
            </Link>
        </div>
    </nav>
</template>
