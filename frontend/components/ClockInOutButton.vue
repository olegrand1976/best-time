<template>
  <ClientOnly>
    <div class="space-y-4">
      <div v-if="activeEntry" class="space-y-2">
        <p class="text-sm text-gray-600">{{ $t('timeEntries.clocking.activeSince') }}</p>
        <p class="text-xl font-bold">{{ formatDuration(activeDuration) }}</p>
        <UButton @click="handleStop" color="red" size="lg" block :loading="loading">
          {{ $t('timeEntries.clocking.stop') }}
        </UButton>
      </div>

      <div v-else class="space-y-4">
        <UFormGroup :label="$t('timeEntries.clocking.project') + ' (' + $t('common.optional') + ')'" name="project">
          <USelect v-model="selectedProject" :options="projectOptions" option-attribute="label" value-attribute="value"
            :placeholder="$t('timeEntries.clocking.projectPlaceholder')" />
        </UFormGroup>

        <UFormGroup :label="$t('timeEntries.clocking.description') + ' (' + $t('common.optional') + ')'"
          name="description">
          <UTextarea v-model="description" :placeholder="$t('timeEntries.clocking.descriptionPlaceholder')" :rows="3" />
        </UFormGroup>

        <UButton @click="handleStart" color="green" size="lg" block :loading="loading">
          {{ $t('timeEntries.clocking.start') }}
        </UButton>
      </div>
    </div>
  </ClientOnly>
</template>

<script setup lang="ts">
const timeEntryStore = useTimeEntryStore()
const { t } = useI18n()

const loading = ref(false)
const selectedProject = ref<number | undefined>(undefined)
const description = ref('')
const activeDuration = ref(0)
let interval: any = null

const activeEntry = computed(() => timeEntryStore.activeEntry)
const projects = computed(() => timeEntryStore.projects || [])

const projectOptions = computed(() => {
  const options: { label: string; value: number | null }[] = [
    { label: t('timeEntries.form.noProject'), value: null }
  ]

  if (Array.isArray(projects.value)) {
    const projectList = projects.value
      .filter(p => p && typeof p === 'object' && p.id && p.name)
      .map(p => ({ label: p.name, value: p.id }))
    options.push(...projectList)
  }

  return options
})

const formatDuration = (seconds: number): string => {
  if (isNaN(seconds) || seconds < 0) return '00:00:00'
  const hours = Math.floor(seconds / 3600)
  const minutes = Math.floor((seconds % 3600) / 60)
  const secs = seconds % 60
  return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`
}

const startTimer = () => {
  if (interval) clearInterval(interval)

  const updateDuration = () => {
    if (activeEntry.value?.start_time) {
      const start = new Date(activeEntry.value.start_time).getTime()
      const now = Date.now()
      activeDuration.value = Math.max(0, Math.floor((now - start) / 1000))
    }
  }

  updateDuration()
  interval = setInterval(updateDuration, 1000)
}

const stopTimer = () => {
  if (interval) {
    clearInterval(interval)
    interval = null
  }
  activeDuration.value = 0
}

const handleStart = async () => {
  loading.value = true
  try {
    await timeEntryStore.startEntry(selectedProject.value, description.value || undefined)
    selectedProject.value = undefined
    description.value = ''
  } catch (error: any) {
    console.error('Error starting entry:', error)
  } finally {
    loading.value = false
  }
}

const handleStop = async () => {
  loading.value = true
  try {
    await timeEntryStore.stopEntry()
  } catch (error) {
    console.error('Error stopping entry:', error)
  } finally {
    loading.value = false
  }
}

// Watch for active entry changes to start/stop timer
watch(() => activeEntry.value, (newEntry) => {
  if (newEntry) {
    startTimer()
  } else {
    stopTimer()
  }
}, { immediate: true })

onMounted(async () => {
  await timeEntryStore.fetchProjects()
  await timeEntryStore.fetchActiveEntry()
})

onUnmounted(() => {
  stopTimer()
})
</script>
