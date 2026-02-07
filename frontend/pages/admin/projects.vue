<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ $t('admin.projects.title') }}</h1>
        <p class="text-gray-600 mt-1">{{ $t('admin.projects.subtitle') }}</p>
      </div>
      <UButton icon="i-heroicons-plus" @click="openCreateModal">
        {{ $t('admin.projects.createButton') }}
      </UButton>
    </div>

    <!-- Filters & Search -->
    <UCard class="mb-6">
      <div class="flex flex-col md:flex-row gap-4">
        <div class="flex-1">
          <UInput v-model="search" icon="i-heroicons-magnifying-glass"
            :placeholder="$t('admin.projects.searchPlaceholder')" @update:model-value="loadProjects" />
        </div>
        <div class="w-full md:w-48">
          <USelect v-model="statusFilter" :options="statusOptions" @update:model-value="loadProjects" />
        </div>
      </div>
    </UCard>

    <!-- Projects Table -->
    <UCard>
      <UTable :loading="loading" :rows="projects" :columns="columns">
        <template #status-data="{ row }">
          <UBadge :color="row.status === 'active' ? 'green' : 'gray'">
            {{ row.status === 'active' ? 'Actif' : 'Archivé' }}
          </UBadge>
        </template>

        <template #actions-data="{ row }">
          <div class="flex space-x-2">
            <UButton icon="i-heroicons-qr-code" color="indigo" variant="soft" size="xs" @click="downloadQR(row)"
              title="QR Code" />
            <UButton icon="i-heroicons-pencil-square" color="blue" variant="soft" size="xs" @click="editProject(row)" />
            <UButton icon="i-heroicons-trash" color="red" variant="soft" size="xs" @click="confirmDelete(row)" />
          </div>
        </template>
      </UTable>
    </UCard>

    <!-- Create/Edit Modal -->
    <UModal v-model="showModal">
      <UCard>
        <template #header>
          <div class="flex items-center justify-between">
            <h3 class="text-base font-semibold">{{ editingProject ? 'Modifier le projet' : 'Nouveau projet' }}</h3>
            <UButton color="gray" variant="ghost" icon="i-heroicons-x-mark" @click="showModal = false" />
          </div>
        </template>

        <form @submit.prevent="saveProject" class="space-y-4">
          <UFormGroup label="Nom du projet" required>
            <UInput v-model="form.name" placeholder="Ex: Rénovation Façade" />
          </UFormGroup>
          <UFormGroup label="Client" required>
            <USelect v-model="form.client_id as any" :options="clientOptions" placeholder="Sélectionner un client" />
          </UFormGroup>
          <UFormGroup label="Description">
            <UTextarea v-model="form.description" />
          </UFormGroup>
          <div class="grid grid-cols-2 gap-4">
            <UFormGroup label="Latitude">
              <UInput v-model="form.latitude as any" type="number" step="any" />
            </UFormGroup>
            <UFormGroup label="Longitude">
              <UInput v-model="form.longitude as any" type="number" step="any" />
            </UFormGroup>
          </div>
          <UFormGroup label="Statut">
            <USelect v-model="form.status" :options="statusOptions.filter(o => o.value)" />
          </UFormGroup>

          <div class="flex justify-end space-x-3 mt-6">
            <UButton color="gray" variant="ghost" @click="showModal = false">Annuler</UButton>
            <UButton type="submit" :loading="saving">Enregistrer</UButton>
          </div>
        </form>
      </UCard>
    </UModal>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'admin',
  middleware: 'auth',
})

const { t } = useI18n()
const loading = ref(false)
const saving = ref(false)
const showModal = ref(false)
const search = ref('')
const statusFilter = ref('active')
interface Project {
  id: number
  name: string
  client: string
  description?: string
  status: 'active' | 'archived'
  latitude?: number | null
  longitude?: number | null
}

const projects = ref<Project[]>([])
const clients = ref<any[]>([])
const editingProject = ref<Project | null>(null)

const clientOptions = computed(() => [
  { label: 'Aucun client', value: null },
  ...clients.value.map(c => ({ label: c.name, value: c.id }))
])

const statusOptions = [
  { label: 'Tous les statuts', value: '' },
  { label: 'Actif', value: 'active' },
  { label: 'Archivé', value: 'archived' },
]

const columns = [
  { key: 'name', label: 'Projet' },
  { key: 'client', label: 'Client' },
  { key: 'status', label: 'Statut' },
  { key: 'actions', label: 'Actions' },
]

const form = ref<Partial<Project & { client_id: number | null }>>({
  name: '',
  client_id: null,
  description: '',
  status: 'active',
  latitude: null,
  longitude: null,
})

const loadProjects = async () => {
  loading.value = true
  try {
    const { apiFetch } = useApi()

    // Fetch clients first
    const clientResponse = await apiFetch<any>('/clients')
    clients.value = clientResponse.data || clientResponse || []

    // Then projects
    const response = await apiFetch<any>('/projects')

    // Simple frontend filtering for now if backend doesn't support it
    let data = (response.data || response || []) as Project[]
    if (search.value) {
      data = data.filter((p: Project) => p.name.toLowerCase().includes(search.value.toLowerCase()) || p.client.toLowerCase().includes(search.value.toLowerCase()))
    }
    if (statusFilter.value) {
      data = data.filter((p: Project) => p.status === statusFilter.value)
    }
    projects.value = data
  } catch (error) {
    console.error('Error loading projects:', error)
  } finally {
    loading.value = false
  }
}

const openCreateModal = () => {
  editingProject.value = null
  form.value = {
    name: '',
    client: '',
    description: '',
    status: 'active',
    latitude: null,
    longitude: null,
  }
  showModal.value = true
}

const editProject = (project: Project) => {
  editingProject.value = project
  form.value = { ...project }
  showModal.value = true
}

const saveProject = async () => {
  saving.value = true
  try {
    const { apiFetch } = useApi()
    const endpoint = editingProject.value
      ? `/admin/projects/${editingProject.value.id}`
      : '/projects'

    await apiFetch(endpoint, {
      method: editingProject.value ? 'PUT' : 'POST',
      body: form.value
    })

    showModal.value = false
    loadProjects()
  } catch (error) {
    console.error('Error saving project:', error)
  } finally {
    saving.value = false
  }
}

const downloadQR = (project: Project) => {
  try {
    const { apiFetch } = useApi()
    apiFetch<any>(`/admin/projects/${project.id}/qr-code`).then(response => {
      // In a real app, this would trigger a download of the QR image
      alert(`QR Code Token: ${response.qr_code_token}\nURL: ${response.qr_code_url}`)
    })
  } catch (error) {
    console.error('Error getting QR code:', error)
  }
}

const confirmDelete = async (project: Project) => {
  if (confirm(`Supprimer le projet ${project.name} ?`)) {
    try {
      const { apiFetch } = useApi()
      await apiFetch(`/admin/projects/${project.id}`, {
        method: 'DELETE'
      })
      loadProjects()
    } catch (error) {
      console.error('Error deleting project:', error)
    }
  }
}

onMounted(loadProjects)
</script>
