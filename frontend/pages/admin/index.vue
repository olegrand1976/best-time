<template>
  <div>
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">{{ $t('admin.dashboard.title') }}</h1>
      <p class="text-gray-600 mt-1">{{ $t('admin.dashboard.subtitle') }}</p>
    </div>

    <div v-if="loading" class="flex justify-center py-12">
      <UIcon name="i-heroicons-arrow-path" class="w-8 h-8 animate-spin text-gray-400" />
    </div>

    <div v-else class="space-y-6">
      <DashboardStats :stats="stats" />

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Activity Feed -->
        <UCard class="lg:col-span-2">
          <template #header>
            <div class="flex justify-between items-center">
              <h3 class="text-lg font-semibold">Activité Récente</h3>
              <UButton to="/admin/logs/activity" variant="link" size="sm">Voir tout</UButton>
            </div>
          </template>

          <div v-if="stats.recent_activity?.length > 0" class="space-y-4">
            <div v-for="entry in stats.recent_activity" :key="entry.id"
              class="flex items-center space-x-4 pb-4 border-b last:border-0 last:pb-0">
              <UAvatar :alt="entry.user?.name" size="sm" />
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ entry.user?.name }}</p>
                <p class="text-xs text-gray-500 truncate">
                  {{ entry.project?.name || 'Sans projet' }} - {{ entry.description }}
                </p>
              </div>
              <div class="text-right">
                <p class="text-xs font-medium">{{ entry.duration_formatted || (entry.is_active ? 'En cours' : '') }}</p>
                <p class="text-[10px] text-gray-400">{{ formatDate(entry.start_time) }}</p>
              </div>
            </div>
          </div>
          <div v-else class="text-center py-8 text-gray-500">Aucune activité récente</div>
        </UCard>

        <!-- Stats by Project Today -->
        <UCard>
          <template #header>
            <h3 class="text-lg font-semibold">Heures par projet (Aujourd'hui)</h3>
          </template>
          <div v-if="stats.project_stats_today?.length > 0" class="space-y-4">
            <div v-for="proj in stats.project_stats_today" :key="proj.project_name">
              <div class="flex justify-between text-xs mb-1">
                <span class="font-medium">{{ proj.project_name }}</span>
                <span>{{ proj.hours }}h</span>
              </div>
              <UMeter :value="proj.hours" :max="Math.max(...stats.project_stats_today.map((p: any) => p.hours), 8)"
                color="blue" />
            </div>
          </div>
          <div v-else class="text-center py-8 text-gray-500">Aucune donnée aujourd'hui</div>
        </UCard>
      </div>

      <ActiveEntriesList :stats="stats" />
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'admin',
  middleware: 'auth',
})

const { isAdmin } = useAuth()
const router = useRouter()

const formatDate = (dateString: string) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  const now = new Date()
  const diffInMinutes = Math.floor((now.getTime() - date.getTime()) / (1000 * 60))

  if (diffInMinutes < 60) return `il y a ${diffInMinutes}m`
  if (diffInMinutes < 1440) return `il y a ${Math.floor(diffInMinutes / 60)}h`
  return date.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit' })
}

if (!isAdmin.value) {
  router.push('/dashboard')
}

const loading = ref(true)
const stats = ref<any>({})

const loadDashboard = async () => {
  loading.value = true
  try {
    const config = useRuntimeConfig()
    const authStore = useAuthStore()

    const response = await $fetch(`${config.public.apiUrl}/dashboard`, {
      headers: {
        Authorization: `Bearer ${authStore.token}`,
      },
    })

    stats.value = response
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
