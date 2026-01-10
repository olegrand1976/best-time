<template>
  <UCard>
    <div class="flex justify-between items-start">
      <div class="flex-1">
        <div class="flex items-center space-x-2 mb-2">
          <span class="font-semibold text-gray-900">
            {{ formatDate(entry.start_time) }}
          </span>
          <UBadge v-if="entry.is_active" color="green">{{ $t('timeEntries.card.inProgress') }}</UBadge>
        </div>

        <div class="space-y-1 text-sm text-gray-600">
          <div class="flex items-center space-x-2">
            <UIcon name="i-heroicons-clock" class="w-4 h-4" />
            <span>
              {{ formatTime(entry.start_time) }}
              <span v-if="entry.end_time"> - {{ formatTime(entry.end_time) }}</span>
            </span>
          </div>

          <div v-if="entry.duration_formatted" class="flex items-center space-x-2">
            <UIcon name="i-heroicons-hourglass" class="w-4 h-4" />
            <span class="font-medium">{{ entry.duration_formatted }}</span>
          </div>

          <div v-if="entry.project" class="flex items-center space-x-2">
            <UIcon name="i-heroicons-folder" class="w-4 h-4" />
            <span>{{ entry.project.name }}</span>
          </div>

          <div v-if="entry.description" class="mt-2">
            <p class="text-gray-700">{{ entry.description }}</p>
          </div>
        </div>
      </div>
    </div>
  </UCard>
</template>

<script setup lang="ts">
defineProps<{
  entry: any
}>()

defineEmits<{
  deleted: []
}>()

const { locale } = useI18n()

const formatDate = (dateString: string): string => {
  const date = new Date(dateString)
  return date.toLocaleDateString(locale.value, {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
}

const formatTime = (dateString: string): string => {
  const date = new Date(dateString)
  return date.toLocaleTimeString(locale.value, {
    hour: '2-digit',
    minute: '2-digit',
  })
}
</script>
