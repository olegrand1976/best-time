<template>
  <div>
    <div class="mb-6 flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ $t('admin.users.title') }}</h1>
        <p class="text-gray-600 mt-1">{{ $t('admin.users.subtitle') }}</p>
      </div>
      <UButton @click="showCreateModal = true" color="primary">
        {{ $t('admin.users.createButton') }}
      </UButton>
    </div>

    <!-- Search and Filters -->
    <UCard class="mb-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <UInput v-model="searchQuery" :placeholder="$t('admin.users.searchPlaceholder')"
          icon="i-heroicons-magnifying-glass" @input="loadUsers" />
        <USelect v-model="roleFilter" :options="roleOptions" :placeholder="$t('admin.users.filterByRole')"
          @change="loadUsers" />
      </div>
    </UCard>

    <!-- Users Table -->
    <UCard>
      <div v-if="loading" class="flex justify-center py-12">
        <UIcon name="i-heroicons-arrow-path" class="w-8 h-8 animate-spin text-gray-400" />
      </div>

      <UTable v-else-if="users.length > 0" :rows="users" :columns="columns">
        <template #role-data="{ row }">
          <UBadge :color="getRoleColor(row.role)">
            {{ $t(`admin.users.roles.${row.role}`) }}
          </UBadge>
        </template>

        <template #actions-data="{ row }">
          <div class="flex space-x-2">
            <UButton size="sm" variant="ghost" @click="viewUser(row)">
              {{ $t('common.view') }}
            </UButton>
            <UButton size="sm" variant="ghost" @click="editUser(row)">
              {{ $t('common.edit') }}
            </UButton>
            <UButton size="sm" variant="ghost" color="red" @click="confirmDelete(row)">
              {{ $t('common.delete') }}
            </UButton>
          </div>
        </template>
      </UTable>

      <div v-else class="text-center py-12 text-gray-500">
        {{ $t('admin.users.noUsers') }}
      </div>
    </UCard>

    <!-- Create/Edit Modal -->
    <UModal v-model="showCreateModal"
      :title="editingUser ? $t('admin.users.editTitle') : $t('admin.users.createTitle')">
      <div v-if="showCreateModal">
        <AdminUserForm :user="editingUser" @saved="handleUserSaved" @cancel="showCreateModal = false" />
      </div>
    </UModal>

    <!-- Delete Confirmation -->
    <UModal v-model="showDeleteModal" :title="$t('admin.users.deleteConfirm')">
      <div class="space-y-4">
        <p>{{ $t('admin.users.deleteMessage', { name: userToDelete?.name }) }}</p>
        <div class="flex justify-end space-x-3">
          <UButton variant="ghost" @click="showDeleteModal = false">
            {{ $t('common.cancel') }}
          </UButton>
          <UButton color="red" @click="deleteUser">
            {{ $t('common.delete') }}
          </UButton>
        </div>
      </div>
    </UModal>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'admin',
  middleware: 'auth',
})

const { t } = useI18n()
const config = useRuntimeConfig()
const authStore = useAuthStore()

const loading = ref(false)
const users = ref<any[]>([])
const searchQuery = ref('')
const roleFilter = ref('')
const showCreateModal = ref(false)
const showDeleteModal = ref(false)
const editingUser = ref<any>(null)
const userToDelete = ref<any>(null)

const roleOptions = [
  { label: t('admin.users.allRoles'), value: '' },
  { label: t('auth.admin'), value: 'admin' },
  { label: t('admin.users.roles.responsable'), value: 'responsable' },
  { label: t('admin.users.roles.gestionnaire'), value: 'gestionnaire' },
  { label: t('admin.users.roles.team_leader'), value: 'team_leader' },
  { label: t('admin.users.roles.ouvrier'), value: 'ouvrier' },
]

const columns = [
  { key: 'name', label: t('admin.users.name') },
  { key: 'email', label: t('auth.email') },
  { key: 'role', label: t('admin.users.role') },
  { key: 'created_at', label: t('admin.users.createdAt') },
  { key: 'actions', label: t('common.actions') },
]

const formatDate = (dateString: string) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return new Intl.RelativeTimeFormat('fr', { numeric: 'auto' }).format(
    Math.round((date.getTime() - new Date().getTime()) / (1000 * 60 * 60 * 24)),
    'day'
  )
}

const loadUsers = async () => {
  loading.value = true
  try {
    const params: any = {}
    if (searchQuery.value) params.search = searchQuery.value
    if (roleFilter.value) params.role = roleFilter.value

    const { apiFetch } = useApi()
    const response = await apiFetch<any>('/admin/users', {
      params,
    })

    users.value = response.data || response
  } catch (error) {
    console.error('Error loading users:', error)
  } finally {
    loading.value = false
  }
}

const viewUser = (user: any) => {
  navigateTo(`/admin/users/${user.id}`)
}

const editUser = (user: any) => {
  editingUser.value = user
  showCreateModal.value = true
}

const confirmDelete = (user: any) => {
  userToDelete.value = user
  showDeleteModal.value = true
}

const deleteUser = async () => {
  if (!userToDelete.value) return

  try {
    const { apiFetch } = useApi()
    await apiFetch(`/admin/users/${userToDelete.value.id}`, {
      method: 'DELETE',
    })

    showDeleteModal.value = false
    userToDelete.value = null
    await loadUsers()
  } catch (error: any) {
    console.error('Error deleting user:', error)
  }
}

const handleUserSaved = () => {
  showCreateModal.value = false
  editingUser.value = null
  loadUsers()
}

const getRoleColor = (role: string) => {
  switch (role) {
    case 'admin': return 'blue'
    case 'responsable': return 'purple'
    case 'gestionnaire': return 'indigo'
    case 'team_leader': return 'orange'
    case 'ouvrier': return 'green'
    default: return 'gray'
  }
}

onMounted(() => {
  loadUsers()
})
</script>
