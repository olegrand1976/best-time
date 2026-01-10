<template>
  <UCard>
    <template #header>
      <h3 class="text-lg font-semibold">{{ $t('dashboard.admin.activeEntriesTitle') }}</h3>
    </template>

    <div v-if="activeEntries.length === 0" class="text-center py-8 text-gray-500">
      <p>{{ $t('timeEntries.noActiveEntries') }}</p>
    </div>

    <div v-else class="space-y-4">
      <div
        v-for="entry in activeEntries"
        :key="entry.id"
        class="border-b border-gray-200 last:border-b-0 pb-4 last:pb-0"
      >
        <div class="flex justify-between items-start">
          <div>
            <p class="font-semibold">{{ entry.user?.name }}</p>
            <p class="text-sm text-gray-600">
              {{ entry.project?.name || $t('timeEntries.form.noProject') }}
            </p>
            <p class="text-xs text-gray-500 mt-1">
              {{ $t('timeEntries.card.started') }} : {{ formatDateTime(entry.start_time) }}
            </p>
          </div>
          <UBadge color="green">{{ $t('timeEntries.card.active') }}</UBadge>
        </div>
      </div>
    </div>
  </UCard>
</template>

<script setup lang="ts">
const props = defineProps<{
  stats?: any
}>()

const activeEntries = computed(() => {
  return props.stats?.active_entries || []
})

const { locale } = useI18n()

const formatDateTime = (dateString: string): string => {
  const date = new Date(dateString)
  return date.toLocaleString(locale.value, {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}
</script>
