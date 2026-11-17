<script setup lang="ts">
defineProps<{
    title: string;
    description?: string;
}>();
</script>

<template>
    <section
        class="flex min-w-0 flex-col rounded-xl border border-border bg-card"
    >
        <header
            class="flex flex-wrap items-start justify-between gap-3 px-5 pt-4 pb-3"
        >
            <div class="min-w-0">
                <h2 class="text-base font-semibold text-foreground">
                    {{ title }}
                </h2>
                <p
                    v-if="description"
                    class="mt-0.5 text-sm text-muted-foreground"
                >
                    {{ description }}
                </p>
            </div>
            <div
                v-if="$slots.actions"
                class="flex flex-wrap items-center gap-2"
            >
                <slot name="actions" />
            </div>
        </header>
        <div class="min-w-0 flex-1 px-5 pb-5">
            <slot />
        </div>
    </section>
</template>
