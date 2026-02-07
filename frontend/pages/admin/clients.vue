<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Gestion des Clients</h1>
                <p class="text-gray-600 mt-1">Gérez la liste de vos clients pour les lier à vos projets.</p>
            </div>
            <UButton icon="i-heroicons-plus" @click="openCreateModal">
                Nouveau client
            </UButton>
        </div>

        <!-- Filters & Search -->
        <UCard class="mb-6">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <UInput v-model="search" icon="i-heroicons-magnifying-glass" placeholder="Rechercher un client..."
                        @update:model-value="loadClients" />
                </div>
            </div>
        </UCard>

        <!-- Clients Table -->
        <UCard>
            <UTable :loading="loading" :rows="clients" :columns="columns">
                <template #is_active-data="{ row }">
                    <UBadge :color="row.is_active ? 'green' : 'gray'">
                        {{ row.is_active ? 'Actif' : 'Inactif' }}
                    </UBadge>
                </template>

                <template #actions-data="{ row }">
                    <div class="flex space-x-2">
                        <UButton icon="i-heroicons-pencil-square" color="blue" variant="soft" size="xs"
                            @click="editClient(row)" />
                        <UButton icon="i-heroicons-trash" color="red" variant="soft" size="xs"
                            @click="confirmDelete(row)" />
                    </div>
                </template>
            </UTable>
        </UCard>

        <!-- Create/Edit Modal -->
        <UModal v-model="showModal">
            <UCard>
                <template #header>
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-semibold">{{ editingClient ? 'Modifier le client' : 'Nouveau client'
                            }}</h3>
                        <UButton color="gray" variant="ghost" icon="i-heroicons-x-mark" @click="showModal = false" />
                    </div>
                </template>

                <form @submit.prevent="saveClient" class="space-y-4">
                    <UFormGroup label="Nom du client" required>
                        <UInput v-model="form.name" placeholder="Ex: Syndic de Copropriété" />
                    </UFormGroup>
                    <UFormGroup label="Personne de contact">
                        <UInput v-model="form.contact_person" />
                    </UFormGroup>
                    <UFormGroup label="Email">
                        <UInput v-model="form.email" type="email" />
                    </UFormGroup>
                    <UFormGroup label="Téléphone">
                        <UInput v-model="form.phone" />
                    </UFormGroup>
                    <UFormGroup label="Adresse">
                        <UTextarea v-model="form.address" :rows="2" />
                    </UFormGroup>
                    <UFormGroup label="Statut">
                        <USelect v-model="form.is_active as any" :options="[
                            { label: 'Actif', value: 1 },
                            { label: 'Inactif', value: 0 }
                        ]" @update:model-value="(val) => form.is_active = !!val" />
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

interface Client {
    id: number
    name: string
    contact_person?: string
    email?: string
    phone?: string
    address?: string
    is_active: boolean
}

const loading = ref(false)
const saving = ref(false)
const showModal = ref(false)
const search = ref('')
const clients = ref<Client[]>([])
const editingClient = ref<Client | null>(null)

const columns = [
    { key: 'name', label: 'Client' },
    { key: 'contact_person', label: 'Contact' },
    { key: 'email', label: 'Email' },
    { key: 'is_active', label: 'Statut' },
    { key: 'actions', label: 'Actions' },
]

const form = ref<Partial<Client>>({
    name: '',
    contact_person: '',
    email: '',
    phone: '',
    address: '',
    is_active: true,
})

const loadClients = async () => {
    loading.value = true
    try {
        const { apiFetch } = useApi()
        const response = await apiFetch<any>('/clients', {
            params: { search: search.value }
        })
        clients.value = response.data || response || []
    } catch (error) {
        console.error('Error loading clients:', error)
    } finally {
        loading.value = false
    }
}

const openCreateModal = () => {
    editingClient.value = null
    form.value = {
        name: '',
        contact_person: '',
        email: '',
        phone: '',
        address: '',
        is_active: true,
    }
    showModal.value = true
}

const editClient = (client: Client) => {
    editingClient.value = client
    form.value = { ...client }
    showModal.value = true
}

const saveClient = async () => {
    saving.value = true
    try {
        const { apiFetch } = useApi()
        const endpoint = editingClient.value
            ? `/clients/${editingClient.value.id}`
            : '/clients'

        await apiFetch(endpoint, {
            method: editingClient.value ? 'PUT' : 'POST',
            body: form.value
        })

        showModal.value = false
        loadClients()
    } catch (error: any) {
        if (error.data?.message) {
            alert(error.data.message)
        }
        console.error('Error saving client:', error)
    } finally {
        saving.value = false
    }
}

const confirmDelete = async (client: Client) => {
    if (confirm(`Supprimer le client ${client.name} ?`)) {
        try {
            const { apiFetch } = useApi()
            await apiFetch(`/clients/${client.id}`, {
                method: 'DELETE'
            })
            loadClients()
        } catch (error: any) {
            if (error.data?.message) {
                alert(error.data.message)
            }
            console.error('Error deleting client:', error)
        }
    }
}

onMounted(loadClients)
</script>
