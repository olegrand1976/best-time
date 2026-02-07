<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Mon Équipe</h1>
                <p class="text-gray-600 mt-1">Gérez vos gestionnaires assignés</p>
            </div>
            <UButton icon="i-heroicons-user-plus" @click="showAddModal = true">
                Ajouter un gestionnaire
            </UButton>
        </div>

        <!-- Team Members List -->
        <UCard>
            <div v-if="loading" class="flex justify-center py-12">
                <UIcon name="i-heroicons-arrow-path" class="w-8 h-8 animate-spin text-gray-400" />
            </div>

            <div v-else-if="team.length > 0">
                <UTable :rows="team" :columns="columns">
                    <template #name-data="{ row }">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <span class="text-blue-600 font-medium text-sm">{{ row.name.charAt(0) }}</span>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ row.name }}</p>
                                <p class="text-sm text-gray-500">{{ row.email }}</p>
                            </div>
                        </div>
                    </template>

                    <template #is_active-data="{ row }">
                        <UBadge :color="row.is_active ? 'green' : 'gray'" variant="soft">
                            {{ row.is_active ? 'Actif' : 'Inactif' }}
                        </UBadge>
                    </template>

                    <template #actions-data="{ row }">
                        <div class="flex items-center space-x-2">
                            <UButton :icon="row.is_active ? 'i-heroicons-pause-circle' : 'i-heroicons-play-circle'"
                                :color="row.is_active ? 'amber' : 'green'" variant="ghost" size="sm"
                                :title="row.is_active ? 'Désactiver' : 'Activer'" @click="toggleActive(row)" />
                            <UButton icon="i-heroicons-trash" color="red" variant="ghost" size="sm"
                                title="Retirer de l'équipe" @click="confirmRemove(row)" />
                        </div>
                    </template>
                </UTable>
            </div>

            <div v-else class="text-center py-12 text-gray-500">
                <UIcon name="i-heroicons-users" class="w-12 h-12 mx-auto mb-4 text-gray-300" />
                <p>Aucun gestionnaire dans votre équipe</p>
                <UButton class="mt-4" @click="showAddModal = true">
                    Ajouter un gestionnaire
                </UButton>
            </div>
        </UCard>

        <!-- Add Member Modal -->
        <UModal v-model="showAddModal">
            <UCard>
                <template #header>
                    <h3 class="text-lg font-semibold">Ajouter un gestionnaire</h3>
                </template>

                <div v-if="loadingAvailable" class="flex justify-center py-8">
                    <UIcon name="i-heroicons-arrow-path" class="w-6 h-6 animate-spin" />
                </div>

                <div v-else-if="availableGestionnaires.length > 0" class="space-y-2 max-h-80 overflow-y-auto">
                    <div v-for="g in availableGestionnaires" :key="g.id"
                        class="flex items-center justify-between p-3 border rounded-lg hover:bg-gray-50 cursor-pointer"
                        @click="addMember(g)">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                <span class="text-green-600 font-medium text-sm">{{ g.name.charAt(0) }}</span>
                            </div>
                            <div>
                                <p class="font-medium">{{ g.name }}</p>
                                <p class="text-sm text-gray-500">{{ g.email }}</p>
                            </div>
                        </div>
                        <UIcon name="i-heroicons-plus-circle" class="w-5 h-5 text-green-500" />
                    </div>
                </div>

                <div v-else class="text-center py-8 text-gray-500">
                    Aucun gestionnaire disponible
                </div>
            </UCard>
        </UModal>

        <!-- Remove Confirmation Modal -->
        <UModal v-model="showRemoveModal">
            <UCard>
                <template #header>
                    <div class="flex items-center space-x-2">
                        <UIcon name="i-heroicons-exclamation-triangle" class="w-6 h-6 text-red-500" />
                        <h3 class="text-lg font-semibold">Retirer de l'équipe</h3>
                    </div>
                </template>

                <p class="text-gray-600">
                    Êtes-vous sûr de vouloir retirer <strong>{{ memberToRemove?.name }}</strong> de votre équipe ?
                </p>

                <template #footer>
                    <div class="flex justify-end space-x-2">
                        <UButton color="gray" variant="ghost" @click="showRemoveModal = false">
                            Annuler
                        </UButton>
                        <UButton color="red" :loading="removing" @click="removeMember">
                            Retirer
                        </UButton>
                    </div>
                </template>
            </UCard>
        </UModal>
    </div>
</template>

<script setup lang="ts">
definePageMeta({
    layout: 'admin',
    middleware: 'auth',
})

interface TeamMember {
    id: number
    name: string
    email: string
    phone?: string
    is_active: boolean
}

const authStore = useAuthStore()
const config = useRuntimeConfig()
const toast = useToast()

const loading = ref(true)
const loadingAvailable = ref(false)
const removing = ref(false)
const team = ref<TeamMember[]>([])
const availableGestionnaires = ref<TeamMember[]>([])
const showAddModal = ref(false)
const showRemoveModal = ref(false)
const memberToRemove = ref<TeamMember | null>(null)

const columns = [
    { key: 'name', label: 'Nom' },
    { key: 'phone', label: 'Téléphone' },
    { key: 'is_active', label: 'Statut' },
    { key: 'actions', label: '' },
]

const loadTeam = async () => {
    loading.value = true
    try {
        const response = await $fetch(`${config.public.apiUrl}/team`, {
            headers: { Authorization: `Bearer ${authStore.token}` }
        }) as any
        team.value = response.gestionnaires || []
    } catch (error) {
        console.error('Error loading team:', error)
    } finally {
        loading.value = false
    }
}

const loadAvailable = async () => {
    loadingAvailable.value = true
    try {
        const response = await $fetch(`${config.public.apiUrl}/team/available`, {
            headers: { Authorization: `Bearer ${authStore.token}` }
        }) as any
        availableGestionnaires.value = response.gestionnaires || []
    } catch (error) {
        console.error('Error loading available gestionnaires:', error)
    } finally {
        loadingAvailable.value = false
    }
}

const addMember = async (member: TeamMember) => {
    try {
        await $fetch(`${config.public.apiUrl}/team/${member.id}`, {
            method: 'POST',
            headers: { Authorization: `Bearer ${authStore.token}` }
        })
        toast.add({
            title: 'Gestionnaire ajouté',
            icon: 'i-heroicons-check-circle',
            color: 'green'
        })
        showAddModal.value = false
        loadTeam()
    } catch (error: any) {
        toast.add({
            title: 'Erreur',
            description: error?.data?.message || 'Impossible d\'ajouter le gestionnaire',
            icon: 'i-heroicons-x-circle',
            color: 'red'
        })
    }
}

const confirmRemove = (member: TeamMember) => {
    memberToRemove.value = member
    showRemoveModal.value = true
}

const removeMember = async () => {
    if (!memberToRemove.value) return

    removing.value = true
    try {
        await $fetch(`${config.public.apiUrl}/team/${memberToRemove.value.id}`, {
            method: 'DELETE',
            headers: { Authorization: `Bearer ${authStore.token}` }
        })
        toast.add({
            title: 'Gestionnaire retiré',
            icon: 'i-heroicons-check-circle',
            color: 'green'
        })
        showRemoveModal.value = false
        memberToRemove.value = null
        loadTeam()
    } catch (error: any) {
        toast.add({
            title: 'Erreur',
            description: error?.data?.message || 'Impossible de retirer le gestionnaire',
            icon: 'i-heroicons-x-circle',
            color: 'red'
        })
    } finally {
        removing.value = false
    }
}

const toggleActive = async (member: TeamMember) => {
    try {
        const response = await $fetch(`${config.public.apiUrl}/team/${member.id}/toggle-active`, {
            method: 'PATCH',
            headers: { Authorization: `Bearer ${authStore.token}` }
        }) as any
        toast.add({
            title: response.message,
            icon: response.is_active ? 'i-heroicons-check-circle' : 'i-heroicons-pause-circle',
            color: response.is_active ? 'green' : 'amber'
        })
        loadTeam()
    } catch (error: any) {
        toast.add({
            title: 'Erreur',
            description: error?.data?.message || 'Impossible de changer le statut',
            icon: 'i-heroicons-x-circle',
            color: 'red'
        })
    }
}

watch(showAddModal, (val) => {
    if (val) loadAvailable()
})

onMounted(loadTeam)
</script>
