<script setup lang="ts">
defineProps<{ caption: string }>();
</script>

<template>
    <div class="-mx-5 overflow-x-auto">
        <table class="w-full min-w-[36rem] text-left text-sm">
            <caption class="sr-only">
                {{
                    caption
                }}
            </caption>
            <thead
                class="border-y border-border bg-muted/60 text-xs text-muted-foreground"
            >
                <slot name="head" />
            </thead>
            <tbody class="divide-y divide-border tabular-nums">
                <slot />
            </tbody>
        </table>
    </div>
</template>
