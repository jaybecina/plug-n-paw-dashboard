<script setup lang="ts">
import type { LeadStatus } from '@/types';

defineProps<{ status: LeadStatus; label?: string }>();

const styles: Record<LeadStatus, string> = {
    new: 'bg-accent text-accent-foreground',
    contacted:
        'bg-[color-mix(in_oklab,var(--chart-3)_18%,transparent)] text-foreground',
    converted:
        'bg-[color-mix(in_oklab,var(--good)_18%,transparent)] text-foreground',
    lost: 'bg-muted text-muted-foreground',
};

const dots: Record<LeadStatus, string> = {
    new: 'bg-[var(--chart-2)]',
    contacted: 'bg-[var(--chart-3)]',
    converted: 'bg-good',
    lost: 'bg-muted-foreground',
};
</script>

<template>
    <span
        class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-xs font-medium capitalize"
        :class="styles[status]"
    >
        <span
            class="size-1.5 rounded-full"
            :class="dots[status]"
            aria-hidden="true"
        />
        {{ label ?? status }}
    </span>
</template>
