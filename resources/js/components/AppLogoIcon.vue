<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

defineProps<{
    className?: HTMLAttributes['class'];
}>();
</script>

<template>
    <!-- Paw print: the Plug N Paw mark. -->
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 24 24"
        fill="currentColor"
        aria-hidden="true"
        :class="className"
        v-bind="$attrs"
    >
        <ellipse
            cx="6.2"
            cy="9.4"
            rx="2.1"
            ry="2.6"
            transform="rotate(-18 6.2 9.4)"
        />
        <ellipse
            cx="10"
            cy="5.6"
            rx="2.1"
            ry="2.7"
            transform="rotate(-6 10 5.6)"
        />
        <ellipse
            cx="14.4"
            cy="5.6"
            rx="2.1"
            ry="2.7"
            transform="rotate(6 14.4 5.6)"
        />
        <ellipse
            cx="18.1"
            cy="9.4"
            rx="2.1"
            ry="2.6"
            transform="rotate(18 18.1 9.4)"
        />
        <path
            d="M12.2 11.4c-2.6 0-5.6 3.4-5.6 5.9 0 1.6 1.2 2.5 2.7 2.5 1.2 0 1.9-.6 2.9-.6s1.7.6 2.9.6c1.5 0 2.7-.9 2.7-2.5 0-2.5-3-5.9-5.6-5.9Z"
        />
    </svg>
</template>
