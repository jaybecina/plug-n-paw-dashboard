<script setup lang="ts">
/**
 * The numbered map pin from the public site's results list.
 */
defineProps<{
    rank: number;
    highlight?: boolean;
    /** Light pins for use on the pine-teal panel. */
    inverse?: boolean;
}>();
</script>

<template>
    <span
        aria-hidden="true"
        class="inline-grid size-7 shrink-0 rotate-45 place-items-center rounded-full rounded-br-[3px] shadow-sm"
        :class="
            highlight
                ? 'bg-primary text-primary-foreground'
                : inverse
                  ? 'bg-pine-foreground text-pine'
                  : 'bg-pine text-pine-foreground'
        "
    >
        <span class="-rotate-45 font-heading text-xs font-semibold">{{
            rank
        }}</span>
    </span>
</template>
