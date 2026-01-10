<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <UCard>
      <div class="p-4">
        <p class="text-sm text-gray-600 mb-1">{{ $t('dashboard.admin.todayHours') }}</p>
        <p class="text-2xl font-bold">{{ stats.today_hours || 0 }}h</p>
      </div>
    </UCard>

    <UCard>
      <div class="p-4">
        <p class="text-sm text-gray-600 mb-1">{{ $t('dashboard.admin.weekHours') }}</p>
        <p class="text-2xl font-bold">{{ stats.week_hours || 0 }}h</p>
      </div>
    </UCard>

    <UCard>
      <div class="p-4">
        <p class="text-sm text-gray-600 mb-1">{{ $t('dashboard.admin.totalEmployees') }}</p>
        <p class="text-2xl font-bold">{{ stats.total_employees || 0 }}</p>
      </div>
    </UCard>

    <UCard>
      <div class="p-4">
        <p class="text-sm text-gray-600 mb-1">{{ $t('dashboard.admin.activeEntries') }}</p>
        <p class="text-2xl font-bold">{{ stats.active_entries?.length || 0 }}</p>
      </div>
    </UCard>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  stats: any
}>()
</script>
