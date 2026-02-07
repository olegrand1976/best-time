<template>
    <div class="h-64">
        <Line :data="chartData" :options="chartOptions" />
    </div>
</template>

<script setup lang="ts">
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler
} from 'chart.js'
import { Line } from 'vue-chartjs'

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler
)

const props = defineProps<{
    data: { date: string; hours: number; label: string }[]
}>()

const chartData = computed(() => ({
    labels: props.data.map(d => d.label),
    datasets: [
        {
            label: 'Heures totales',
            backgroundColor: 'rgba(59, 130, 246, 0.2)',
            borderColor: 'rgb(59, 130, 246)',
            pointBackgroundColor: 'rgb(59, 130, 246)',
            pointBorderColor: '#fff',
            pointHoverBackgroundColor: '#fff',
            pointHoverBorderColor: 'rgb(59, 130, 246)',
            fill: true,
            tension: 0.4,
            data: props.data.map(d => d.hours)
        }
    ]
}))

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false
        },
        tooltip: {
            callbacks: {
                label: (context: any) => `${context.parsed.y} heures`
            }
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            grid: {
                color: '#f3f4f6'
            }
        },
        x: {
            grid: {
                display: false
            }
        }
    }
}
</script>
