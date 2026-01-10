<template>
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
        <ActiveEntriesList />
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
</template>

<script setup lang="ts">
definePageMeta({
  middleware: 'auth',
})

const { isAdmin } = useAuth()
const timeEntryStore = useTimeEntryStore()

const loading = ref(true)
const stats = ref<any>({})
const activeEntry = ref<any>(null)

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
