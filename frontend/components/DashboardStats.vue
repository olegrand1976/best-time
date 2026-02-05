<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
    <UCard>
      <div class="p-4">
        <p class="text-sm text-gray-600 mb-1">{{ $t('dashboard.admin.todayHours') }}</p>
        <p class="text-2xl font-bold text-blue-600">{{ stats.today_hours || 0 }}h</p>
      </div>
    </UCard>

    <UCard>
      <div class="p-4">
        <p class="text-sm text-gray-600 mb-1">{{ $t('dashboard.admin.weekHours') }}</p>
        <p class="text-2xl font-bold text-indigo-600">{{ stats.week_hours || 0 }}h</p>
      </div>
    </UCard>

    <UCard>
      <div class="p-4">
        <p class="text-sm text-gray-600 mb-1">{{ $t('dashboard.admin.totalEmployees') }}</p>
        <p class="text-2xl font-bold">{{ stats.total_employees || 0 }}</p>
        <p class="text-xs text-gray-500 mt-1">{{ stats.total_managers || 0 }} managers</p>
      </div>
    </UCard>

    <UCard>
      <div class="p-4">
        <p class="text-sm text-gray-600 mb-1">{{ $t('dashboard.admin.activeEntries') }}</p>
        <p class="text-2xl font-bold text-green-600">{{ stats.active_entries?.length || 0 }}</p>
      </div>
    </UCard>

    <UCard>
      <div class="p-4">
        <p class="text-sm text-gray-600 mb-1">{{ $t('admin.nav.projects') }}</p>
        <p class="text-2xl font-bold">{{ stats.total_projects || 0 }}</p>
        <p class="text-xs text-gray-500 mt-1">{{ stats.active_projects_count || 0 }} actifs</p>
      </div>
    </UCard>

    <UCard>
      <div class="p-4 text-center cursor-pointer hover:bg-gray-50 transition-colors h-full flex flex-col justify-center"
        @click="navigateTo('/admin/users')">
        <UIcon name="i-heroicons-user-plus" class="w-6 h-6 mx-auto text-gray-400 mb-1" />
        <span class="text-xs font-medium text-gray-600">Gérer utilisateurs</span>
      </div>
    </UCard>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  stats: any
}>()
</script>
