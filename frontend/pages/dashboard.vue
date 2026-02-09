<template>
  <NuxtLayout :name="dashboardLayout">
    <div>
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ $t('dashboard.title') }}</h1>
      </div>

      <div v-if="loading" class="flex justify-center py-12">
        <UIcon name="i-heroicons-arrow-path" class="w-8 h-8 animate-spin text-gray-400" />
      </div>

      <div v-else>
        <!-- Admin Dashboard -->
        <div v-if="isAdmin" class="space-y-6">
          <DashboardStats :stats="stats" />
          <ActiveEntriesList :stats="stats" />
        </div>

        <!-- Responsable Dashboard -->
        <div v-else-if="isResponsable && stats.role === 'responsable'" class="space-y-6">

          <!-- Controls: Period & Customization -->
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
            <!-- Period Selector -->
            <div class="w-full sm:w-64">
              <USelect v-model="selectedPeriod" :options="periodOptions" option-attribute="label"
                value-attribute="value" icon="i-heroicons-calendar" />
            </div>

            <!-- Customization Buttons -->
            <div class="flex gap-2">
              <UButton v-if="!isEditing" icon="i-heroicons-pencil-square" color="gray" variant="soft"
                label="Personnaliser" @click="isEditing = true" />
              <template v-else>
                <UButton icon="i-heroicons-arrow-path" color="red" variant="ghost" label="Réinitialiser"
                  @click="resetWidgets" />
                <UButton icon="i-heroicons-check" color="green" variant="solid" label="Terminer" @click="saveWidgets" />
              </template>
            </div>
          </div>

          <!-- Edit Mode Panel -->
          <UCard v-if="isEditing" class="mb-6 border-2 border-blue-100">
            <template #header>
              <h3 class="text-lg font-semibold text-blue-700">Configurer l'affichage</h3>
              <p class="text-sm text-gray-500">Cochez pour afficher, utilisez les flèches pour réorganiser.</p>
            </template>
            <div class="space-y-2">
              <div v-for="(widget, index) in userWidgets" :key="widget.id"
                class="flex items-center justify-between p-2 bg-gray-50 rounded hover:bg-gray-100 cursor-move"
                draggable="true" @dragstart="onDragStart($event, index)" @dragover="onDragOver($event)"
                @drop="onDrop($event, index)" :class="{ 'opacity-50': draggedIndex === index }">
                <div class="flex items-center gap-3">
                  <UIcon name="i-heroicons-bars-3" class="text-gray-400" />
                  <UCheckbox v-model="widget.visible" :name="widget.id" />
                  <span :class="{ 'opacity-50': !widget.visible, 'font-medium': true }">{{ widget.label }}</span>
                </div>
                <div class="flex gap-2 items-center">
                  <!-- Width Toggle -->
                  <UButton v-if="widget.canResize"
                    :icon="widget.width === 'full' ? 'i-heroicons-arrows-right-left' : 'i-heroicons-arrows-right-left'"
                    size="xs" :color="widget.width === 'full' ? 'blue' : 'gray'" variant="ghost"
                    :label="widget.width === 'full' ? '100%' : '50%'" @click="toggleWidth(widget)" />
                  <div v-else class="w-16"></div> <!-- Spacer for alignment -->
                </div>
              </div>
            </div>
          </UCard>

          <!-- Dynamic Widget Grid -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <template v-for="widget in userWidgets" :key="widget.id">

              <!-- Stats Cards (Full Width) -->
              <div v-if="widget.id === 'stats_cards' && widget.visible"
                :class="widget.width === 'full' ? 'col-span-1 lg:col-span-2' : ''">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <UCard>
                    <div class="text-center">
                      <p class="text-sm text-gray-600">Mes heures ({{ periodLabel }})</p>
                      <p class="text-2xl font-bold text-blue-600">{{ stats.my_period_hours }}h</p>
                    </div>
                  </UCard>
                  <UCard>
                    <div class="text-center">
                      <p class="text-sm text-gray-600">Équipe ({{ periodLabel }})</p>
                      <p class="text-2xl font-bold text-green-600">{{ stats.team_period_hours }}h</p>
                    </div>
                  </UCard>
                  <UCard>
                    <div class="text-center">
                      <p class="text-sm text-gray-600">Membres équipe</p>
                      <p class="text-2xl font-bold text-purple-600">{{ stats.team_count }}</p>
                    </div>
                  </UCard>
                </div>
              </div>

              <!-- Trends (Full Width) -->
              <UCard v-if="widget.id === 'daily_trend' && widget.visible"
                :class="widget.width === 'full' ? 'col-span-1 lg:col-span-2' : ''">
                <template #header>
                  <h3 class="text-lg font-semibold">Évolution ({{ periodLabel }})</h3>
                </template>
                <ChartsDailyTrendChart v-if="stats.period_trend?.length" :data="stats.period_trend" />
              </UCard>

              <!-- Project Distribution -->
              <UCard v-if="widget.id === 'project_distribution' && widget.visible"
                :class="widget.width === 'full' ? 'col-span-1 lg:col-span-2' : ''">
                <template #header>
                  <h3 class="text-lg font-semibold">Répartition Projets ({{ periodLabel }})</h3>
                </template>
                <ChartsProjectDistributionChart v-if="stats.project_stats?.length" :data="stats.project_stats" />
                <div v-else class="h-64 flex items-center justify-center text-gray-500">
                  Pas de données pour cette période
                </div>
              </UCard>

              <!-- Team Workload -->
              <UCard v-if="widget.id === 'team_workload' && widget.visible"
                :class="widget.width === 'full' ? 'col-span-1 lg:col-span-2' : ''">
                <template #header>
                  <h3 class="text-lg font-semibold">Charge de travail ({{ periodLabel }})</h3>
                </template>
                <ChartsTeamWorkloadChart v-if="stats.team_stats?.length" :data="stats.team_stats" />
                <div v-else class="h-64 flex items-center justify-center text-gray-500">
                  Pas de données pour cette période
                </div>
              </UCard>

              <!-- My Clocking (Always Real-time) -->
              <UCard v-if="widget.id === 'my_clocking' && widget.visible"
                :class="widget.width === 'full' ? 'col-span-1 lg:col-span-2' : ''">
                <template #header>
                  <h3 class="text-lg font-semibold">Mon Pointage (Temps réel)</h3>
                </template>
                <ClockInOutButton />
              </UCard>

              <!-- Team Active (Always Real-time) -->
              <UCard v-if="widget.id === 'team_active' && widget.visible"
                :class="widget.width === 'full' ? 'col-span-1 lg:col-span-2' : ''">
                <template #header>
                  <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold">Équipe en cours (Temps réel)</h3>
                    <UBadge color="green" v-if="stats.team_active_entries?.length">
                      {{ stats.team_active_entries.length }} actif(s)
                    </UBadge>
                  </div>
                </template>
                <div v-if="stats.team_active_entries?.length" class="space-y-3">
                  <div v-for="entry in stats.team_active_entries" :key="entry.id"
                    class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                    <div class="flex items-center space-x-3">
                      <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <span class="text-green-600 font-medium text-sm">{{ entry.user?.name?.charAt(0) }}</span>
                      </div>
                      <div>
                        <p class="font-medium text-gray-900">{{ entry.user?.name }}</p>
                        <p class="text-sm text-gray-500">{{ entry.project?.name || 'Sans projet' }}</p>
                      </div>
                    </div>
                    <div class="text-sm text-gray-600">
                      {{ formatTime(entry.start_time) }}
                    </div>
                  </div>
                </div>
                <div v-else class="text-center py-8 text-gray-500">
                  <UIcon name="i-heroicons-users" class="w-8 h-8 mx-auto mb-2 text-gray-300" />
                  <p>Aucun membre en cours de pointage</p>
                </div>
              </UCard>

              <!-- Team Stats Table (Full Width) -->
              <UCard v-if="widget.id === 'team_stats' && widget.visible"
                :class="widget.width === 'full' ? 'col-span-1 lg:col-span-2' : ''">
                <template #header>
                  <h3 class="text-lg font-semibold">Statistiques Équipe ({{ periodLabel }})</h3>
                </template>
                <UTable v-if="stats.team_stats?.length" :rows="stats.team_stats" :columns="teamColumns">
                  <template #is_active-data="{ row }">
                    <UBadge :color="row.is_active ? 'green' : 'gray'" variant="soft">
                      {{ row.is_active ? 'Actif' : 'Inactif' }}
                    </UBadge>
                  </template>
                  <template #hours_period-data="{ row }">
                    <span class="font-semibold">{{ row.hours_period }}h</span>
                  </template>
                </UTable>
                <div v-else class="text-center py-8 text-gray-500">
                  Aucun membre dans votre équipe
                </div>
              </UCard>

              <!-- Project Stats (Full Width) -->
              <UCard v-if="widget.id === 'project_stats' && widget.visible"
                :class="widget.width === 'full' ? 'col-span-1 lg:col-span-2' : ''">
                <template #header>
                  <h3 class="text-lg font-semibold">Détails Projets ({{ periodLabel }})</h3>
                </template>
                <div v-if="stats.project_stats?.length" class="space-y-4">
                  <div v-for="project in stats.project_stats" :key="project.project_id" class="border rounded-lg p-4">
                    <div class="flex justify-between items-center mb-3">
                      <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                          <UIcon name="i-heroicons-building-office" class="w-5 h-5 text-blue-600" />
                        </div>
                        <div>
                          <p class="font-semibold text-gray-900">{{ project.project_name }}</p>
                          <p class="text-sm text-gray-500">{{ project.users?.length || 0 }} intervenant(s)</p>
                        </div>
                      </div>
                      <div class="text-right">
                        <p class="text-xl font-bold text-blue-600">{{ project.total_hours }}h</p>
                        <p class="text-sm text-gray-500">total</p>
                      </div>
                    </div>
                    <div v-if="project.users?.length" class="flex flex-wrap gap-2">
                      <div v-for="user in project.users" :key="user.id"
                        class="flex items-center space-x-2 bg-gray-100 rounded-full px-3 py-1">
                        <span class="text-sm font-medium text-gray-700">{{ user.name }}</span>
                        <span class="text-xs text-gray-500">({{ user.hours }}h)</span>
                      </div>
                    </div>
                  </div>
                </div>
                <div v-else class="text-center py-8 text-gray-500">
                  <UIcon name="i-heroicons-briefcase" class="w-8 h-8 mx-auto mb-2 text-gray-300" />
                  <p>Aucun pointage projet pour cette période</p>
                </div>
              </UCard>

            </template>
          </div>
        </div>

        <!-- Employee Dashboard -->
        <div v-else class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <UCard>
              <template #header>
                <h3 class="text-lg font-semibold">{{ $t('dashboard.employee.clocking') }}</h3>
              </template>
              <ClockInOutButton />
            </UCard>

            <UCard>
              <template #header>
                <h3 class="text-lg font-semibold">{{ $t('dashboard.employee.statistics') }}</h3>
              </template>
              <div class="space-y-4">
                <div>
                  <p class="text-sm text-gray-600">{{ $t('dashboard.employee.today') }}</p>
                  <p class="text-2xl font-bold">{{ stats.today_hours }}h</p>
                </div>
                <div>
                  <p class="text-sm text-gray-600">{{ $t('dashboard.employee.thisWeek') }}</p>
                  <p class="text-2xl font-bold">{{ stats.week_hours }}h</p>
                </div>
              </div>
            </UCard>
          </div>

          <div v-if="activeEntry" class="mt-6">
            <UCard>
              <template #header>
                <h3 class="text-lg font-semibold">{{ $t('dashboard.employee.activeEntry') }}</h3>
              </template>
              <TimeEntryCard :entry="activeEntry" />
            </UCard>
          </div>
        </div>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup lang="ts">
import type { LayoutKey } from '#build/types/layouts'

const { isAdmin, isManager, isResponsable } = useAuth()
const dashboardLayout = computed(() => isManager.value ? 'admin' : 'default')

definePageMeta({
  middleware: 'auth',
  layout: false, // We'll handle layout dynamically
})

const timeEntryStore = useTimeEntryStore()

const loading = ref(true)
const stats = ref<any>({})
const activeEntry = ref<any>(null)

// --- Period Selection ---
const selectedPeriod = ref('week')
const periodOptions = [
  { label: 'Aujourd\'hui', value: 'today' },
  { label: 'Cette Semaine', value: 'week' },
  { label: 'Ce Mois', value: 'month' },
  { label: 'Ce Trimestre', value: 'quarter' },
  { label: 'Ce Semestre', value: 'semester' },
  { label: 'Cette Année', value: 'year' },
]

const periodLabel = computed(() => {
  return periodOptions.find(o => o.value === selectedPeriod.value)?.label || 'Période'
})

// --- Widget System ---
interface Widget {
  id: string
  label: string
  visible: boolean
  width: 'full' | 'half'
  canResize: boolean
}

const isEditing = ref(false)
// Default configuration
const availableWidgets: Widget[] = [
  { id: 'stats_cards', label: 'Indicateurs Clés', visible: true, width: 'full', canResize: false },
  { id: 'daily_trend', label: 'Tendance (Heures)', visible: true, width: 'full', canResize: true },
  { id: 'my_clocking', label: 'Mon Pointage', visible: true, width: 'half', canResize: true },
  { id: 'team_active', label: 'Équipe en cours', visible: true, width: 'half', canResize: true },
  { id: 'project_distribution', label: 'Répartition Projets', visible: true, width: 'half', canResize: true },
  { id: 'team_workload', label: 'Charge de travail', visible: true, width: 'half', canResize: true },
  { id: 'team_stats', label: 'Tableau Équipe', visible: true, width: 'full', canResize: true },
  { id: 'project_stats', label: 'Détail Projets', visible: true, width: 'full', canResize: true },
]

const userWidgets = ref<Widget[]>([...availableWidgets])

const loadWidgets = () => {
  if (process.client) {
    const saved = localStorage.getItem('dashboard_config')
    if (saved) {
      try {
        const parsed = JSON.parse(saved)
        // Merge with available to ensure new widgets appear if added later
        // and migrate old configs (missing width/canResize)
        const merged: Widget[] = []

        // 1. Keep order from saved config
        parsed.forEach((savedWidget: any) => {
          const defaultWidget = availableWidgets.find(aw => aw.id === savedWidget.id)
          if (defaultWidget) {
            merged.push({
              ...defaultWidget, // Get defaults (inc. width/resize)
              visible: savedWidget.visible, // Overwrite visibility from save
              width: savedWidget.width || defaultWidget.width // Overwrite width if saved, else default
            })
          }
        })

        // 2. Add any new widgets not in saved config
        availableWidgets.forEach(aw => {
          if (!merged.some(w => w.id === aw.id)) {
            merged.push({ ...aw })
          }
        })

        userWidgets.value = merged
      } catch (e) {
        console.error('Error parsing dashboard config', e)
        userWidgets.value = [...availableWidgets]
      }
    }
  }
}

const saveWidgets = () => {
  if (process.client) {
    localStorage.setItem('dashboard_config', JSON.stringify(userWidgets.value))
  }
  isEditing.value = false
}

const resetWidgets = () => {
  if (confirm('Réinitialiser la disposition ?')) {
    userWidgets.value = availableWidgets.map(w => ({ ...w })) // Deep copy
    saveWidgets()
  }
}

// --- Drag & Drop ---
const draggedIndex = ref<number | null>(null)

const onDragStart = (event: DragEvent, index: number) => {
  draggedIndex.value = index
  if (event.dataTransfer) {
    event.dataTransfer.effectAllowed = 'move'
    event.dataTransfer.dropEffect = 'move'
  }
}

const onDragOver = (event: DragEvent) => {
  event.preventDefault() // Necessary to allow dropping
}

const onDrop = (event: DragEvent, dropIndex: number) => {
  if (draggedIndex.value !== null && draggedIndex.value !== dropIndex) {
    const item = userWidgets.value[draggedIndex.value]
    if (item) { // TS Fix: Check if item exists
      userWidgets.value.splice(draggedIndex.value, 1)
      userWidgets.value.splice(dropIndex, 0, item)
    }
  }
  draggedIndex.value = null
}

const toggleWidth = (widget: Widget) => {
  if (widget.canResize) {
    widget.width = widget.width === 'full' ? 'half' : 'full'
  }
}

// --- End Widget System ---

const teamColumns = [
  { key: 'name', label: 'Nom' },
  { key: 'email', label: 'Email' },
  { key: 'is_active', label: 'Statut' },
  { key: 'hours_period', label: 'Heures' },
]

const formatTime = (dateString: string): string => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleTimeString('fr-BE', { hour: '2-digit', minute: '2-digit' })
}

const loadDashboard = async () => {
  loading.value = true

  try {
    const config = useRuntimeConfig()
    const authStore = useAuthStore()

    const response = await $fetch<any>(`${config.public.apiUrl}/dashboard`, {
      headers: {
        Authorization: `Bearer ${authStore.token}`,
      },
      query: {
        period: selectedPeriod.value // Pass selected period
      }
    })

    stats.value = response
    activeEntry.value = response.active_entry || null
  } catch (error) {
    console.error('Error loading dashboard:', error)
  } finally {
    loading.value = false
  }
}

watch(selectedPeriod, () => {
  loadDashboard()
})

onMounted(() => {
  loadDashboard()
  loadWidgets()
})
</script>
