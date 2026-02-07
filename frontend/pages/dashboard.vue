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
          <!-- Stats Row -->
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <UCard>
              <div class="text-center">
                <p class="text-sm text-gray-600">Mes heures aujourd'hui</p>
                <p class="text-2xl font-bold text-blue-600">{{ stats.today_hours }}h</p>
              </div>
            </UCard>
            <UCard>
              <div class="text-center">
                <p class="text-sm text-gray-600">Mes heures cette semaine</p>
                <p class="text-2xl font-bold text-blue-600">{{ stats.week_hours }}h</p>
              </div>
            </UCard>
            <UCard>
              <div class="text-center">
                <p class="text-sm text-gray-600">Équipe aujourd'hui</p>
                <p class="text-2xl font-bold text-green-600">{{ stats.team_today_hours }}h</p>
              </div>
            </UCard>
            <UCard>
              <div class="text-center">
                <p class="text-sm text-gray-600">Membres équipe</p>
                <p class="text-2xl font-bold text-purple-600">{{ stats.team_count }}</p>
              </div>
            </UCard>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- My Clocking -->
            <UCard>
              <template #header>
                <h3 class="text-lg font-semibold">Mon Pointage</h3>
              </template>
              <ClockInOutButton />
            </UCard>

            <!-- Team Active Entries -->
            <UCard>
              <template #header>
                <div class="flex justify-between items-center">
                  <h3 class="text-lg font-semibold">Équipe en cours</h3>
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
          </div>

          <!-- Team Statistics Table -->
          <UCard>
            <template #header>
              <h3 class="text-lg font-semibold">Statistiques de l'équipe</h3>
            </template>
            <UTable v-if="stats.team_stats?.length" :rows="stats.team_stats" :columns="teamColumns">
              <template #is_active-data="{ row }">
                <UBadge :color="row.is_active ? 'green' : 'gray'" variant="soft">
                  {{ row.is_active ? 'Actif' : 'Inactif' }}
                </UBadge>
              </template>
              <template #hours_today-data="{ row }">
                <span class="font-semibold">{{ row.hours_today }}h</span>
              </template>
            </UTable>
            <div v-else class="text-center py-8 text-gray-500">
              Aucun membre dans votre équipe
            </div>
          </UCard>
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

const teamColumns = [
  { key: 'name', label: 'Nom' },
  { key: 'email', label: 'Email' },
  { key: 'is_active', label: 'Statut' },
  { key: 'hours_today', label: 'Heures aujourd\'hui' },
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
    })

    stats.value = response
    activeEntry.value = response.active_entry || null
  } catch (error) {
    console.error('Error loading dashboard:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadDashboard()
})
</script>
