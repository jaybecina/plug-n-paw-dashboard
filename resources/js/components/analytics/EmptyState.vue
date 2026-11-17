<script setup lang="ts">
import { SearchX } from '@lucide/vue';

defineProps<{ title: string; description: string }>();
</script>

<template>
    <div class="flex flex-col items-center gap-2 py-10 text-center">
        <SearchX class="size-6 text-muted-foreground" aria-hidden="true" />
        <p class="text-sm font-medium text-foreground">{{ title }}</p>
        <p class="max-w-sm text-sm text-muted-foreground">{{ description }}</p>
        <slot />
    </div>
</template>
