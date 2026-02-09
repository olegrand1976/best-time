<template>
  <div class="space-y-6 p-4">
    <!-- Personal Information Section -->
    <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
      <h4 class="text-sm font-semibold text-gray-700 mb-4 border-b pb-2 flex items-center gap-2">
        <UIcon name="i-heroicons-user-circle" class="w-5 h-5 text-gray-500" />
        Informations Personnelles
      </h4>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <UFormGroup label="Prénom" name="first_name" required class="w-full">
          <UInput v-model="form.first_name" icon="i-heroicons-user" required />
        </UFormGroup>
        <UFormGroup label="Nom" name="last_name" required class="w-full">
          <UInput v-model="form.last_name" icon="i-heroicons-user" required />
        </UFormGroup>

        <UFormGroup :label="$t('auth.email')" name="email" class="w-full">
          <UInput v-model="form.email" type="email" icon="i-heroicons-envelope" placeholder="Facultatif" />
        </UFormGroup>

        <UFormGroup label="Téléphone" name="phone" class="w-full">
          <UInput v-model="form.phone" icon="i-heroicons-phone" placeholder="Facultatif" />
        </UFormGroup>
      </div>

      <!-- Address Section -->
      <div class="mt-4 grid grid-cols-1 md:grid-cols-6 gap-4">
        <div class="md:col-span-4">
          <UFormGroup label="Adresse" name="address">
            <UInput v-model="form.address" icon="i-heroicons-map-pin" placeholder="Rue et numéro" />
          </UFormGroup>
        </div>
        <div class="md:col-span-2">
          <UFormGroup label="Boîte" name="box">
            <UInput v-model="form.box" placeholder="Ex: A2" />
          </UFormGroup>
        </div>
        <div class="md:col-span-2">
          <UFormGroup label="Code Postal" name="zip_code">
            <UInput v-model="form.zip_code" />
          </UFormGroup>
        </div>
        <div class="md:col-span-4">
          <UFormGroup label="Ville" name="city">
            <UInput v-model="form.city" />
          </UFormGroup>
        </div>
      </div>
    </div>

    <!-- Access & Role Section -->
    <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
      <h4 class="text-sm font-semibold text-gray-700 mb-4 border-b pb-2 flex items-center gap-2">
        <UIcon name="i-heroicons-key" class="w-5 h-5 text-gray-500" />
        Accès & Rôle
      </h4>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <UFormGroup :label="$t('admin.users.role')" name="role" required class="w-full">
          <USelect v-model="form.role" :options="filteredRoleOptions" icon="i-heroicons-briefcase" required />
        </UFormGroup>

        <UFormGroup label="Projet par défaut" name="project_id" class="w-full">
          <USelect v-model="form.project_id" :options="projectOptions" icon="i-heroicons-folder"
            placeholder="Sélectionner un projet" />
        </UFormGroup>

        <UFormGroup :label="$t('auth.password')" name="password" :required="!user" class="w-full md:col-span-2">
          <div class="flex gap-2 items-start">
            <div class="flex-grow">
              <UInput v-model="form.password" type="password" :required="!user" icon="i-heroicons-lock-closed" />
            </div>
            <UTooltip text="Générer un mot de passe">
              <UButton color="gray" variant="soft" icon="i-heroicons-arrow-path" @click="generatePassword" />
            </UTooltip>
          </div>
          <template #hint>
            <div class="mt-1 text-xs text-gray-500">
              <span v-if="user">{{ $t('admin.users.passwordHint') }}</span>
              <span v-else>Requis pour la création</span>
            </div>
          </template>
        </UFormGroup>
      </div>
    </div>

    <UAlert v-if="error" color="red" variant="soft" icon="i-heroicons-exclamation-triangle" :title="error"
      class="mt-4" />

    <div class="flex justify-end space-x-3 pt-6 border-t mt-2">
      <UButton variant="ghost" @click="$emit('cancel')">
        {{ $t('common.cancel') }}
      </UButton>
      <UButton @click="handleSubmit" :loading="loading" color="primary" icon="i-heroicons-check">
        {{ $t('common.save') }}
      </UButton>
    </div>
  </div>
</template>

<script setup lang="ts">
const props = defineProps<{
  user?: any
}>()

const emit = defineEmits<{
  saved: []
  cancel: []
}>()

const { t } = useI18n()
const config = useRuntimeConfig()
const authStore = useAuthStore()
const { apiFetch } = useApi()

const loading = ref(false)
const error = ref('')
const projects = ref<any[]>([])

const form = ref({
  first_name: props.user?.first_name || (props.user?.name ? props.user.name.split(' ')[0] : ''),
  last_name: props.user?.last_name || (props.user?.name ? props.user.name.split(' ').slice(1).join(' ') : ''),
  email: props.user?.email || '',
  phone: props.user?.phone || '',
  address: props.user?.address || '',
  box: props.user?.box || '',
  zip_code: props.user?.zip_code || '',
  city: props.user?.city || '',
  password: '',
  role: props.user?.role || 'ouvrier', // Default to 'ouvrier' (employee) which is safer
  project_id: props.user?.project_id || null,
})

const roleOptions = [
  { label: t('auth.admin'), value: 'admin' },
  { label: t('admin.users.roles.responsable'), value: 'responsable' },
  { label: t('admin.users.roles.gestionnaire'), value: 'gestionnaire' },
  { label: t('admin.users.roles.team_leader'), value: 'team_leader' },
  { label: t('admin.users.roles.ouvrier'), value: 'ouvrier' },
]

const projectOptions = computed(() => {
  return [
    { label: 'Aucun projet', value: null },
    ...projects.value.map(p => ({ label: p.name, value: p.id }))
  ]
})

// Filter roles based on current user's role
const filteredRoleOptions = computed(() => {
  const currentUserRole = authStore.user?.role

  if (currentUserRole === 'admin') {
    return roleOptions
  }

  if (currentUserRole === 'responsable') {
    return roleOptions.filter(r => ['gestionnaire', 'team_leader', 'ouvrier'].includes(r.value))
  }

  // Default fallback (should verify permissions elsewhere too)
  return roleOptions.filter(r => r.value === 'ouvrier')
})

const loadProjects = async () => {
  try {
    const response = await apiFetch<any>('/projects')
    projects.value = response.data || response || []
  } catch (e) {
    console.error('Failed to load projects', e)
  }
}

const generatePassword = () => {
  const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*'
  let password = ''
  for (let i = 0; i < 12; i++) {
    password += chars.charAt(Math.floor(Math.random() * chars.length))
  }
  form.value.password = password
}

const handleSubmit = async () => {
  error.value = ''
  loading.value = true

  try {
    const url = props.user
      ? `${config.public.apiUrl}/admin/users/${props.user.id}`
      : `${config.public.apiUrl}/admin/users`

    const method = props.user ? 'PUT' : 'POST'

    // Clean up empty strings to null or expected format if needed
    const body: any = { ...form.value }
    if (!body.password) delete body.password

    await apiFetch(url, {
      method,
      body,
    })

    emit('saved')
  } catch (err: any) {
    error.value = err.data?.message || t('errors.generic')
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadProjects()
})
</script>
