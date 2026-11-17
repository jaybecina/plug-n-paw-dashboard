<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
    <div
        class="flex aspect-square size-8 items-center justify-center rounded-lg bg-sidebar-primary text-sidebar-primary-foreground"
    >
        <AppLogoIcon class="size-5" />
    </div>
    <div class="ml-1 grid flex-1 text-left leading-tight">
        <span class="truncate font-heading text-[0.95rem] font-semibold"
            >Plug N Paw</span
        >
        <span class="truncate text-xs text-sidebar-foreground/70">Admin</span>
    </div>
</template>
