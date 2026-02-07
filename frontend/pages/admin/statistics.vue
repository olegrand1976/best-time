<template>
    <div>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">{{ $t('admin.statistics.title') }}</h1>
            <p class="text-gray-600 mt-1">{{ $t('admin.statistics.subtitle') }}</p>
        </div>

        <!-- Filters -->
        <UCard class="mb-6">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1 grid grid-cols-2 gap-4">
                    <UInput v-model="startDate" type="date" :label="$t('admin.statistics.from')"
                        @update:model-value="loadStats" />
                    <UInput v-model="endDate" type="date" :label="$t('admin.statistics.to')"
                        @update:model-value="loadStats" />
                </div>
                <div class="flex items-end space-x-2">
                    <UButton icon="i-heroicons-arrow-down-tray" variant="soft" @click="exportData">{{
                        $t('admin.statistics.exportCsv') }}
                    </UButton>
                    <UButton icon="i-heroicons-arrow-path" color="gray" variant="ghost" @click="loadStats"
                        :loading="loading" />
                </div>
            </div>
        </UCard>

        <div v-if="loading" class="flex justify-center py-12">
            <UIcon name="i-heroicons-arrow-path" class="w-8 h-8 animate-spin text-gray-400" />
        </div>

        <div v-else class="space-y-6">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <UCard>
                    <div class="text-center p-4">
                        <p class="text-sm text-gray-500 uppercase font-semibold">{{ $t('admin.statistics.totalHours') }}
                        </p>
                        <p class="text-3xl font-bold text-blue-600 mt-1">{{ summary.total_hours || 0 }}h</p>
                    </div>
                </UCard>
                <UCard>
                    <div class="text-center p-4">
                        <p class="text-sm text-gray-500 uppercase font-semibold">{{ $t('admin.statistics.avgPerDay') }}
                        </p>
                        <p class="text-3xl font-bold text-indigo-600 mt-1">{{ summary.avg_per_day || 0 }}h</p>
                    </div>
                </UCard>
                <UCard>
                    <div class="text-center p-4">
                        <p class="text-sm text-gray-500 uppercase font-semibold">{{ $t('admin.statistics.totalEntries')
                            }}</p>
                        <p class="text-3xl font-bold text-green-600 mt-1">{{ summary.total_entries || 0 }}</p>
                    </div>
                </UCard>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Chart 1: Hours per Project -->
                <UCard>
                    <template #header>
                        <h3 class="font-semibold">{{ $t('admin.statistics.byProject') }}</h3>
                    </template>
                    <div class="h-64 flex flex-col justify-end space-y-4">
                        <div v-for="item in projectStats" :key="item.name" class="relative">
                            <div class="flex justify-between text-xs mb-1">
                                <span>{{ item.name }}</span>
                                <span>{{ item.hours }}h</span>
                            </div>
                            <UMeter :value="item.hours" :max="summary.total_hours" color="blue" />
                        </div>
                        <div v-if="projectStats.length === 0" class="text-center text-gray-500 py-8">
                            Aucune donnée disponible
                        </div>
                    </div>
                </UCard>

                <!-- List: Top Employees -->
                <UCard>
                    <template #header>
                        <h3 class="font-semibold">{{ $t('admin.statistics.topEmployees') }}</h3>
                    </template>
                    <div class="space-y-4">
                        <div v-for="emp in employeeStats" :key="emp.name" class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <UAvatar :alt="emp.name" size="xs" />
                                <span class="text-sm">{{ emp.name }}</span>
                            </div>
                            <span class="text-sm font-semibold">{{ emp.hours }}h</span>
                        </div>
                        <div v-if="employeeStats.length === 0" class="text-center text-gray-500 py-8">
                            Aucune donnée disponible
                        </div>
                    </div>
                </UCard>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
definePageMeta({
    layout: 'admin',
    middleware: 'auth',
})

interface StatItem {
    name: string
    hours: number
}

interface SummaryStats {
    total_hours: number
    avg_per_day: number
    total_entries: number
    total_users: number
    total_projects: number
}

const loading = ref(true)
const startDate = ref(new Date(new Date().setDate(new Date().getDate() - 30)).toISOString().split('T')[0])
const endDate = ref(new Date().toISOString().split('T')[0])

const summary = ref<SummaryStats>({
    total_hours: 0,
    avg_per_day: 0,
    total_entries: 0,
    total_users: 0,
    total_projects: 0
})
const projectStats = ref<StatItem[]>([])
const employeeStats = ref<StatItem[]>([])

const loadStats = async () => {
    loading.value = true
    try {
        const authStore = useAuthStore()
        const config = useRuntimeConfig()

        if (!authStore.token) {
            console.warn('No auth token available')
            useToast().add({
                title: 'Non authentifié',
                description: 'Veuillez vous reconnecter',
                color: 'red'
            })
            return
        }

        const url = `${config.public.apiUrl}/admin/statistics?start_date=${startDate.value}&end_date=${endDate.value}`
        console.log('Fetching statistics from:', url)

        const response = await $fetch(url, {
            headers: { Authorization: `Bearer ${authStore.token}` }
        }) as any

        console.log('Statistics response:', response)

        summary.value = response.summary || { total_hours: 0, avg_per_day: 0, total_entries: 0 }
        projectStats.value = response.by_project || []
        employeeStats.value = response.by_employee || []
    } catch (error: any) {
        console.error('Error loading statistics:', error)
        useToast().add({
            title: 'Erreur de chargement',
            description: error?.data?.message || error?.message || 'Impossible de charger les statistiques',
            color: 'red'
        })
    } finally {
        loading.value = false
    }
}

const exportData = () => {
    alert('Fonctionnalité d\'exportation en cours de développement...')
}

onMounted(loadStats)
</script>
