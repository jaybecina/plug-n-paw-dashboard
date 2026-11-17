<script setup lang="ts">
import { ChevronDown } from '@lucide/vue';
import type { Option } from '@/types';

defineProps<{
    label: string;
    options: Option[];
    allLabel: string;
}>();

const model = defineModel<string | null>({ required: true });
</script>

<template>
    <label class="relative inline-flex items-center text-sm">
        <span class="sr-only">{{ label }}</span>
        <select
            :value="model ?? ''"
            class="h-9 appearance-none rounded-lg border border-input bg-card py-1 pr-8 pl-3 text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
            @change="model = ($event.target as HTMLSelectElement).value || null"
        >
            <option value="">{{ allLabel }}</option>
            <option
                v-for="option in options"
                :key="option.value"
                :value="option.value"
            >
                {{ option.label }}
            </option>
        </select>
        <ChevronDown
            class="pointer-events-none absolute right-2.5 size-4 text-muted-foreground"
            aria-hidden="true"
        />
    </label>
</template>
