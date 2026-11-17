<script setup lang="ts">
defineProps<{
    title: string;
    description: string;
}>();
</script>

<template>
    <div
        class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"
    >
        <div class="max-w-2xl">
            <h1
                class="text-2xl font-semibold text-foreground sm:text-[1.75rem]"
            >
                {{ title }}
            </h1>
            <p class="mt-1 text-sm text-muted-foreground">{{ description }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <slot />
        </div>
    </div>
</template>
