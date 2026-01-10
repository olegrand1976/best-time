<template>
  <div>
    <div v-if="loading" class="flex justify-center py-12">
      <UIcon name="i-heroicons-arrow-path" class="w-8 h-8 animate-spin text-gray-400" />
    </div>

    <div v-else-if="entries.length === 0" class="text-center py-12">
      <p class="text-gray-500">Aucune entrée de temps trouvée</p>
    </div>

    <div v-else class="space-y-4">
      <TimeEntryCard
        v-for="entry in entries"
        :key="entry.id"
        :entry="entry"
        @deleted="$emit('refresh')"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  entries: any[]
  loading: boolean
}>()

defineEmits<{
  refresh: []
}>()
</script>
