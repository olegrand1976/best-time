<template>
  <div class="space-y-4">
    <h3 class="text-lg font-semibold">Ajouter une entrée de temps</h3>

    <UFormGroup label="Date" name="date" required>
      <UInput v-model="date" type="date" required />
    </UFormGroup>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <UFormGroup label="Heure de début" name="start_time" required>
        <UInput v-model="startTime" type="time" required />
      </UFormGroup>

      <UFormGroup label="Heure de fin" name="end_time" required>
        <UInput v-model="endTime" type="time" required />
      </UFormGroup>
    </div>

    <UFormGroup label="Projet (optionnel)" name="project">
      <USelect
        v-model="selectedProject"
        :options="projectOptions"
        option-attribute="label"
        value-attribute="value"
        placeholder="Sélectionner un projet"
      />
    </UFormGroup>

    <UFormGroup label="Description" name="description">
      <UTextarea
        v-model="description"
        placeholder="Description du travail effectué..."
        rows="4"
      />
    </UFormGroup>

    <UAlert v-if="error" color="red" variant="soft" :title="error" />

    <div class="flex justify-end space-x-3">
      <UButton color="gray" variant="ghost" @click="$emit('cancel')">
        Annuler
      </UButton>
      <UButton @click="handleSubmit" :loading="loading">
        Enregistrer
      </UButton>
    </div>
  </div>
</template>

<script setup lang="ts">
const emit = defineEmits<{
  created: []
  cancel: []
}>()

const timeEntryStore = useTimeEntryStore()

const loading = ref(false)
const error = ref('')
const date = ref(new Date().toISOString().split('T')[0])
const startTime = ref('09:00')
const endTime = ref('17:00')
const selectedProject = ref<number | null>(null)
const description = ref('')

const projects = computed(() => timeEntryStore.projects)

const projectOptions = computed(() => {
  return [
    { label: 'Aucun projet', value: null },
    ...projects.value.map((p: any) => ({ label: p.name, value: p.id })),
  ]
})

const handleSubmit = async () => {
  error.value = ''
  
  if (!date.value || !startTime.value || !endTime.value) {
    error.value = 'Veuillez remplir tous les champs requis'
    return
  }

  const startDateTime = new Date(`${date.value}T${startTime.value}`)
  const endDateTime = new Date(`${date.value}T${endTime.value}`)

  if (endDateTime <= startDateTime) {
    error.value = 'L\'heure de fin doit être postérieure à l\'heure de début'
    return
  }

  loading.value = true

  try {
    await timeEntryStore.createEntry({
      project_id: selectedProject.value || undefined,
      start_time: startDateTime.toISOString(),
      end_time: endDateTime.toISOString(),
      description: description.value || undefined,
    })

    emit('created')
  } catch (err: any) {
    error.value = err.data?.message || 'Erreur lors de la création de l\'entrée'
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  await timeEntryStore.fetchProjects()
})
</script>
