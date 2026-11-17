import {
    BarElement,
    CategoryScale,
    Chart,
    Filler,
    LinearScale,
    LineElement,
    PointElement,
    Tooltip,
} from 'chart.js';

Chart.register(
    BarElement,
    CategoryScale,
    Filler,
    LinearScale,
    LineElement,
    PointElement,
    Tooltip,
);

export { Chart };
