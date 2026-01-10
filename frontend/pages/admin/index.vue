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

if (!isAdmin) {
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
