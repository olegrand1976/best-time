<template>
    <div class="h-64 flex justify-center">
        <Doughnut :data="chartData" :options="chartOptions" />
    </div>
</template>

<script setup lang="ts">
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    ArcElement,
    CategoryScale
} from 'chart.js'
import { Doughnut } from 'vue-chartjs'

ChartJS.register(Title, Tooltip, Legend, ArcElement, CategoryScale)

const props = defineProps<{
    data: { project_name: string; total_hours: number }[]
}>()

const chartData = computed(() => ({
    labels: props.data.map(p => p.project_name),
    datasets: [{
        backgroundColor: [
            '#3B82F6', '#10B981', '#F59E0B', '#EF4444',
            '#8B5CF6', '#EC4899', '#6366F1', '#14B8A6'
        ],
        data: props.data.map(p => p.total_hours)
    }]
}))

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'right' as const
        }
    }
}
</script>
