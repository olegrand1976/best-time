<template>
  <div class="space-y-4">
    <h3 class="text-lg font-semibold">{{ $t('timeEntries.form.title') }}</h3>

    <UFormGroup :label="$t('timeEntries.form.date')" name="date" required>
      <UInput v-model="date" type="date" required />
    </UFormGroup>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <UFormGroup :label="$t('timeEntries.form.startTime')" name="start_time" required>
        <UInput v-model="startTime" type="time" required />
      </UFormGroup>

      <UFormGroup :label="$t('timeEntries.form.endTime')" name="end_time" required>
        <UInput v-model="endTime" type="time" required />
      </UFormGroup>
    </div>

    <UFormGroup :label="$t('timeEntries.form.project') + ' (' + $t('common.optional') + ')'" name="project">
      <USelect
        v-model="selectedProject"
        :options="projectOptions"
        option-attribute="label"
        value-attribute="value"
        :placeholder="$t('timeEntries.form.projectPlaceholder')"
      />
    </UFormGroup>

    <UFormGroup :label="$t('timeEntries.form.description')" name="description">
      <UTextarea
        v-model="description"
        :placeholder="$t('timeEntries.form.descriptionPlaceholder')"
        rows="4"
      />
    </UFormGroup>

    <UAlert v-if="error" color="red" variant="soft" :title="error" />

    <div class="flex justify-end space-x-3">
      <UButton color="gray" variant="ghost" @click="$emit('cancel')">
        {{ $t('common.cancel') }}
      </UButton>
      <UButton @click="handleSubmit" :loading="loading">
        {{ $t('common.save') }}
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

const { t } = useI18n()

const projectOptions = computed(() => {
  return [
    { label: t('timeEntries.form.noProject'), value: null },
    ...projects.value.map((p: any) => ({ label: p.name, value: p.id })),
  ]
})

const handleSubmit = async () => {
  error.value = ''
  
  if (!date.value || !startTime.value || !endTime.value) {
    error.value = t('timeEntries.form.requiredFieldsError')
    return
  }

  const startDateTime = new Date(`${date.value}T${startTime.value}`)
  const endDateTime = new Date(`${date.value}T${endTime.value}`)

  if (endDateTime <= startDateTime) {
    error.value = t('timeEntries.form.invalidTimeRange')
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
    error.value = err.data?.message || t('timeEntries.form.createError')
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  await timeEntryStore.fetchProjects()
})
</script>
