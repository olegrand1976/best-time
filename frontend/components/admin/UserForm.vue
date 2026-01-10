<template>
  <div class="space-y-4">
    <UFormGroup :label="$t('admin.users.name')" name="name" required>
      <UInput v-model="form.name" required />
    </UFormGroup>

    <UFormGroup :label="$t('auth.email')" name="email" required>
      <UInput v-model="form.email" type="email" required />
    </UFormGroup>

    <UFormGroup :label="$t('auth.password')" name="password" :required="!user">
      <UInput v-model="form.password" type="password" :required="!user" />
      <template #hint>
        <span v-if="user" class="text-sm text-gray-500">{{ $t('admin.users.passwordHint') }}</span>
      </template>
    </UFormGroup>

    <UFormGroup :label="$t('admin.users.role')" name="role" required>
      <USelect
        v-model="form.role"
        :options="roleOptions"
        required
      />
    </UFormGroup>

    <UAlert v-if="error" color="red" variant="soft" :title="error" />

    <div class="flex justify-end space-x-3 pt-4">
      <UButton variant="ghost" @click="$emit('cancel')">
        {{ $t('common.cancel') }}
      </UButton>
      <UButton @click="handleSubmit" :loading="loading">
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

const loading = ref(false)
const error = ref('')

const form = ref({
  name: props.user?.name || '',
  email: props.user?.email || '',
  password: '',
  role: props.user?.role || 'employee',
})

const roleOptions = [
  { label: t('auth.admin'), value: 'admin' },
  { label: t('auth.employee'), value: 'employee' },
]

const handleSubmit = async () => {
  error.value = ''
  loading.value = true

  try {
    const url = props.user
      ? `${config.public.apiUrl}/admin/users/${props.user.id}`
      : `${config.public.apiUrl}/admin/users`

    const method = props.user ? 'PUT' : 'POST'
    const body: any = {
      name: form.value.name,
      email: form.value.email,
      role: form.value.role,
    }

    if (form.value.password || !props.user) {
      body.password = form.value.password
    }

    await $fetch(url, {
      method,
      headers: {
        Authorization: `Bearer ${authStore.token}`,
      },
      body,
    })

    emit('saved')
  } catch (err: any) {
    error.value = err.data?.message || t('errors.generic')
  } finally {
    loading.value = false
  }
}
</script>
