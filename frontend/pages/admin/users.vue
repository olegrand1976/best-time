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
        <UInput
          v-model="searchQuery"
          :placeholder="$t('admin.users.searchPlaceholder')"
          icon="i-heroicons-magnifying-glass"
          @input="loadUsers"
        />
        <USelect
          v-model="roleFilter"
          :options="roleOptions"
          :placeholder="$t('admin.users.filterByRole')"
          @change="loadUsers"
        />
      </div>
    </UCard>

    <!-- Users Table -->
    <UCard>
      <div v-if="loading" class="flex justify-center py-12">
        <UIcon name="i-heroicons-arrow-path" class="w-8 h-8 animate-spin text-gray-400" />
      </div>

      <UTable
        v-else-if="users.length > 0"
        :rows="users"
        :columns="columns"
      >
        <template #role-data="{ row }">
          <UBadge :color="row.role === 'admin' ? 'blue' : 'gray'">
            {{ row.role === 'admin' ? $t('auth.admin') : $t('auth.employee') }}
          </UBadge>
        </template>

        <template #actions-data="{ row }">
          <div class="flex space-x-2">
            <UButton
              size="sm"
              variant="ghost"
              @click="viewUser(row)"
            >
              {{ $t('common.view') }}
            </UButton>
            <UButton
              size="sm"
              variant="ghost"
              @click="editUser(row)"
            >
              {{ $t('common.edit') }}
            </UButton>
            <UButton
              size="sm"
              variant="ghost"
              color="red"
              @click="confirmDelete(row)"
            >
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
    <UModal v-model="showCreateModal" :title="editingUser ? $t('admin.users.editTitle') : $t('admin.users.createTitle')">
      <div v-if="showCreateModal">
        <UserForm
          :user="editingUser"
          @saved="handleUserSaved"
          @cancel="showCreateModal = false"
        />
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
  { label: t('auth.employee'), value: 'employee' },
]

const columns = [
  { key: 'name', label: t('admin.users.name') },
  { key: 'email', label: t('auth.email') },
  { key: 'role', label: t('admin.users.role') },
  { key: 'created_at', label: t('admin.users.createdAt') },
  { key: 'actions', label: t('common.actions') },
]

const loadUsers = async () => {
  loading.value = true
  try {
    const params: any = {}
    if (searchQuery.value) params.search = searchQuery.value
    if (roleFilter.value) params.role = roleFilter.value

    const response = await $fetch(`${config.public.apiUrl}/admin/users`, {
      headers: {
        Authorization: `Bearer ${authStore.token}`,
      },
      params,
    })

    users.value = response.data
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
    await $fetch(`${config.public.apiUrl}/admin/users/${userToDelete.value.id}`, {
      method: 'DELETE',
      headers: {
        Authorization: `Bearer ${authStore.token}`,
      },
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

onMounted(() => {
  loadUsers()
})
</script>
