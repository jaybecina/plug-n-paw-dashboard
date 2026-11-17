<script setup lang="ts">
import type { ChartData, ChartOptions, Plugin } from 'chart.js';
import { computed } from 'vue';
import { Bar, Line } from 'vue-chartjs';
import ChartLegend from '@/components/analytics/ChartLegend.vue';
import ChartTable from '@/components/analytics/ChartTable.vue';
import { useChartTheme } from '@/composables/useChartTheme';
import '@/lib/charts';
import { formatNumber, formatValue } from '@/lib/format';
import type { ChartSeries, KpiFormat } from '@/types';

const props = withDefaults(
    defineProps<{
        type: 'line' | 'bar';
        /** Accessible description of what the chart shows. */
        label: string;
        labels: string[];
        series: ChartSeries[];
        stacked?: boolean;
        format?: KpiFormat;
        height?: number;
    }>(),
    { stacked: false, format: 'number', height: 260 },
);

const theme = useChartTheme();

/** Translucent version of a hex colour for area fills. */
function withAlpha(hex: string, alpha: number): string {
    const value = hex.replace('#', '');
    const [r, g, b] = [0, 2, 4].map((i) => parseInt(value.slice(i, i + 2), 16));

    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}

const data = computed(() => {
    const t = theme.value;

    return {
        labels: props.labels,
        datasets: props.series.map((item, index) => {
            const color = t?.series(item.color) ?? '#888';
            const isLast = index === props.series.length - 1;

            return props.type === 'line'
                ? {
                      label: item.label,
                      data: item.data,
                      borderColor: color,
                      backgroundColor:
                          props.series.length === 1
                              ? withAlpha(color, 0.1)
                              : color,
                      fill: props.series.length === 1,
                      borderWidth: 2,
                      tension: 0.3,
                      pointRadius: 0,
                      pointHoverRadius: 4,
                      pointHoverBorderWidth: 2,
                      pointHoverBackgroundColor: color,
                      pointHoverBorderColor: t?.surface,
                  }
                : {
                      label: item.label,
                      data: item.data,
                      backgroundColor: color,
                      hoverBackgroundColor: color,
                      // A 2px surface gap separates stacked segments.
                      borderColor: t?.surface,
                      borderWidth: props.stacked ? { top: 2 } : 0,
                      borderRadius:
                          !props.stacked || isLast
                              ? { topLeft: 4, topRight: 4 }
                              : 0,
                      borderSkipped: 'bottom' as const,
                      maxBarThickness: 28,
                      categoryPercentage: 0.8,
                      barPercentage: 0.9,
                  };
        }),
    };
});

const options = computed<ChartOptions<'line' | 'bar'>>(() => {
    const t = theme.value;

    return {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 250 },
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: t?.surface,
                titleColor: t?.text,
                bodyColor: t?.text,
                borderColor: t?.grid,
                borderWidth: 1,
                padding: 10,
                cornerRadius: 8,
                boxPadding: 4,
                usePointStyle: true,
                titleFont: { family: t?.font, weight: 600 },
                bodyFont: { family: t?.font },
                callbacks: {
                    label: (ctx) =>
                        ` ${ctx.dataset.label}: ${formatValue(Number(ctx.parsed.y), props.format)}`,
                },
            },
        },
        scales: {
            x: {
                stacked: props.stacked,
                grid: { display: false },
                border: { color: t?.grid },
                ticks: {
                    color: t?.muted,
                    font: { family: t?.font, size: 11 },
                    maxRotation: 0,
                    autoSkip: true,
                    maxTicksLimit: 8,
                },
            },
            y: {
                stacked: props.stacked,
                beginAtZero: true,
                grid: { color: t?.grid },
                border: { display: false },
                ticks: {
                    color: t?.muted,
                    font: { family: t?.font, size: 11 },
                    maxTicksLimit: 5,
                    precision: 0,
                    callback: (value) =>
                        props.format === 'currency'
                            ? formatValue(Number(value), 'currency')
                            : formatNumber(Number(value)),
                },
            },
        },
    };
});

/** Vertical guide line under the hovered day (line charts only). */
const crosshair: Plugin<'line'> = {
    id: 'crosshair',
    afterDatasetsDraw(chart) {
        const active = chart.tooltip?.getActiveElements();

        if (!active?.length) {
            return;
        }

        const { ctx, chartArea } = chart;
        const x = active[0].element.x;

        ctx.save();
        ctx.beginPath();
        ctx.moveTo(x, chartArea.top);
        ctx.lineTo(x, chartArea.bottom);
        ctx.lineWidth = 1;
        ctx.strokeStyle = theme.value?.muted ?? '#999';
        ctx.globalAlpha = 0.35;
        ctx.stroke();
        ctx.restore();
    },
};
</script>

<template>
    <div>
        <ChartLegend
            v-if="series.length > 1"
            class="mb-3"
            :series="series"
            :shape="type === 'bar' ? 'square' : 'line'"
        />
        <div
            class="relative"
            :style="{ height: `${height}px` }"
            role="img"
            :aria-label="label"
        >
            <template v-if="theme">
                <Line
                    v-if="type === 'line'"
                    :data="data as ChartData<'line'>"
                    :options="options as ChartOptions<'line'>"
                    :plugins="[crosshair]"
                />
                <Bar
                    v-else
                    :data="data as ChartData<'bar'>"
                    :options="options as ChartOptions<'bar'>"
                />
            </template>
        </div>
        <ChartTable
            :caption="label"
            :labels="labels"
            :series="series"
            :format="format"
        />
    </div>
</template>
