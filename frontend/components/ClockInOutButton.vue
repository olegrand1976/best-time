<template>
  <div class="space-y-4">
    <div v-if="activeEntry" class="space-y-2">
      <p class="text-sm text-gray-600">Pointage en cours depuis :</p>
      <p class="text-xl font-bold">{{ formatDuration(activeDuration) }}</p>
      <UButton
        @click="handleStop"
        color="red"
        size="lg"
        block
        :loading="loading"
      >
        Arrêter le pointage
      </UButton>
    </div>

    <div v-else class="space-y-4">
      <UFormGroup label="Projet (optionnel)" name="project">
        <USelect
          v-model="selectedProject"
          :options="projectOptions"
          option-attribute="label"
          value-attribute="value"
          placeholder="Sélectionner un projet"
        />
      </UFormGroup>

      <UFormGroup label="Description (optionnelle)" name="description">
        <UTextarea
          v-model="description"
          placeholder="Description du travail..."
          rows="3"
        />
      </UFormGroup>

      <UButton
        @click="handleStart"
        color="green"
        size="lg"
        block
        :loading="loading"
      >
        Démarrer le pointage
      </UButton>
    </div>
  </div>
</template>

<script setup lang="ts">
const timeEntryStore = useTimeEntryStore()

const loading = ref(false)
const selectedProject = ref<number | null>(null)
const description = ref('')
const activeEntry = ref<any>(null)
const activeDuration = ref(0)
let interval: NodeJS.Timeout | null = null

const projects = computed(() => timeEntryStore.projects)

const projectOptions = computed(() => {
  return [
    { label: 'Aucun projet', value: null },
    ...projects.value.map((p: any) => ({ label: p.name, value: p.id })),
  ]
})

const loadActiveEntry = async () => {
  activeEntry.value = await timeEntryStore.fetchActiveEntry()
  
  if (activeEntry.value) {
    startTimer()
  } else {
    stopTimer()
  }
}

const formatDuration = (seconds: number): string => {
  const hours = Math.floor(seconds / 3600)
  const minutes = Math.floor((seconds % 3600) / 60)
  const secs = seconds % 60
  return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`
}

const startTimer = () => {
  if (interval) return

  const updateDuration = () => {
    if (activeEntry.value?.start_time) {
      const start = new Date(activeEntry.value.start_time).getTime()
      const now = Date.now()
      activeDuration.value = Math.floor((now - start) / 1000)
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
    await timeEntryStore.startEntry(selectedProject.value || undefined, description.value || undefined)
    selectedProject.value = null
    description.value = ''
    await loadActiveEntry()
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
    await loadActiveEntry()
  } catch (error) {
    console.error('Error stopping entry:', error)
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  await timeEntryStore.fetchProjects()
  await loadActiveEntry()
})

onUnmounted(() => {
  stopTimer()
})
</script>
