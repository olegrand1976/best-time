<template>
  <div>
    <div class="mb-6 flex justify-between items-center">
      <h1 class="text-2xl font-bold text-gray-900">Mes temps</h1>
      <UButton @click="showForm = !showForm" color="primary">
        Ajouter une entrée
      </UButton>
    </div>

    <UCard v-if="showForm" class="mb-6">
      <TimeEntryForm @created="handleEntryCreated" @cancel="showForm = false" />
    </UCard>

    <TimeEntryList :entries="entries" :loading="loading" @refresh="loadEntries" />
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  middleware: 'auth',
})

const timeEntryStore = useTimeEntryStore()
const showForm = ref(false)
const entries = ref([])
const loading = ref(false)

const loadEntries = async () => {
  loading.value = true
  entries.value = await timeEntryStore.fetchEntries({ week: true })
  loading.value = false
}

const handleEntryCreated = () => {
  showForm.value = false
  loadEntries()
}

onMounted(() => {
  loadEntries()
})
</script>
