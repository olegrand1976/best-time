<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <UCard>
      <div class="p-4">
        <p class="text-sm text-gray-600 mb-1">Heures aujourd'hui</p>
        <p class="text-2xl font-bold">{{ stats.today_hours || 0 }}h</p>
      </div>
    </UCard>

    <UCard>
      <div class="p-4">
        <p class="text-sm text-gray-600 mb-1">Heures cette semaine</p>
        <p class="text-2xl font-bold">{{ stats.week_hours || 0 }}h</p>
      </div>
    </UCard>

    <UCard>
      <div class="p-4">
        <p class="text-sm text-gray-600 mb-1">Total employés</p>
        <p class="text-2xl font-bold">{{ stats.total_employees || 0 }}</p>
      </div>
    </UCard>

    <UCard>
      <div class="p-4">
        <p class="text-sm text-gray-600 mb-1">Pointages actifs</p>
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
