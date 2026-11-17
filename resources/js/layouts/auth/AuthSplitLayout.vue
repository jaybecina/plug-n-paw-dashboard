<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import ClinicPin from '@/components/analytics/ClinicPin.vue';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="grid min-h-dvh bg-background lg:grid-cols-[minmax(0,5fr)_minmax(0,6fr)]"
    >
        <aside
            class="relative hidden flex-col justify-between overflow-hidden bg-pine p-10 text-pine-foreground lg:flex"
        >
            <Link
                :href="home()"
                class="relative z-10 flex items-center gap-2.5"
            >
                <span
                    class="grid size-9 place-items-center rounded-lg bg-primary text-primary-foreground"
                >
                    <AppLogoIcon class="size-5" />
                </span>
                <span class="font-heading text-lg font-semibold"
                    >Plug N Paw</span
                >
            </Link>

            <!-- The website's map motif: dotted routes between ranked clinic pins. -->
            <div
                aria-hidden="true"
                class="relative mx-auto my-10 w-full max-w-sm"
            >
                <svg
                    viewBox="0 0 320 220"
                    class="w-full text-pine-foreground/25"
                    fill="none"
                >
                    <path
                        d="M10 190 C 70 160, 90 100, 150 85 S 260 50, 305 18"
                        stroke="currentColor"
                        stroke-width="3"
                        stroke-dasharray="2 10"
                        stroke-linecap="round"
                    />
                    <path
                        d="M28 34 C 88 64, 118 120, 90 172 S 200 210, 292 168"
                        stroke="currentColor"
                        stroke-width="3"
                        stroke-dasharray="2 10"
                        stroke-linecap="round"
                    />
                </svg>
                <ClinicPin
                    :rank="1"
                    highlight
                    class="absolute top-[52%] left-[44%] scale-125"
                />
                <ClinicPin
                    :rank="2"
                    inverse
                    class="absolute top-[16%] left-[16%]"
                />
                <ClinicPin
                    :rank="3"
                    inverse
                    class="absolute top-[4%] left-[78%]"
                />
            </div>

            <div class="relative z-10 max-w-md">
                <p class="font-heading text-3xl leading-tight font-semibold">
                    The back office for Plug N Paw.
                </p>
                <p class="mt-3 text-sm leading-6 text-pine-foreground/75">
                    Traffic, vet searches and the clinic leads they turn into,
                    for the vet finder at plug-n-paw-web.vercel.app.
                </p>
            </div>
        </aside>

        <main class="flex items-center justify-center px-6 py-12 sm:px-10">
            <div class="flex w-full max-w-sm flex-col gap-6">
                <div class="flex flex-col gap-2">
                    <span
                        class="grid size-10 place-items-center rounded-lg bg-primary text-primary-foreground lg:hidden"
                    >
                        <AppLogoIcon class="size-6" />
                    </span>
                    <h1
                        v-if="title"
                        class="text-2xl font-semibold text-foreground"
                    >
                        {{ title }}
                    </h1>
                    <p v-if="description" class="text-sm text-muted-foreground">
                        {{ description }}
                    </p>
                </div>
                <slot />
            </div>
        </main>
    </div>
</template>
