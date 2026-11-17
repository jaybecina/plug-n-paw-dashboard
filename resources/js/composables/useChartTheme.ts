import { onBeforeUnmount, onMounted, ref } from 'vue';
import type { SeriesColor } from '@/types';

export type ChartTheme = {
    series: (color: SeriesColor) => string;
    text: string;
    muted: string;
    grid: string;
    surface: string;
    font: string;
};

function read(): ChartTheme {
    const styles = getComputedStyle(document.documentElement);
    const token = (name: string) => styles.getPropertyValue(name).trim();

    return {
        series: (color) => token(`--chart-${color}`),
        text: token('--foreground'),
        muted: token('--muted-foreground'),
        grid: token('--chart-grid'),
        surface: token('--card'),
        font: getComputedStyle(document.body).fontFamily,
    };
}

/**
 * Chart.js draws on canvas, so it can't use CSS variables directly. This
 * reads the tokens and re-reads them whenever light/dark mode flips.
 */
export function useChartTheme() {
    const theme = ref<ChartTheme | null>(null);
    let observer: MutationObserver | null = null;

    onMounted(() => {
        theme.value = read();
        observer = new MutationObserver(() => (theme.value = read()));
        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class'],
        });
    });

    onBeforeUnmount(() => observer?.disconnect());

    return theme;
}
