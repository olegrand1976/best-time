<template>
  <div v-if="!isDeviceAllowed" class="min-h-screen">
    <DeviceRestrictionMessage :message="restrictionMessage" :required-device="requiredDevice" />
  </div>
  <div v-else class="min-h-screen bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50 flex flex-col w-full">
    <ClientOnly>
      <UNotifications />
    </ClientOnly>

    <nav class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <NuxtLink to="/dashboard" class="flex items-center space-x-2">
              <img src="/logo-icon.svg" alt="Best Time" class="h-8 w-8" />
              <span
                class="text-xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                Best Time
              </span>
            </NuxtLink>
          </div>

          <div class="flex items-center space-x-4">
            <ClientOnly>
              <NuxtLink v-if="isAuthenticated" to="/dashboard"
                class="text-gray-700 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                {{ $t('nav.dashboard') }}
              </NuxtLink>
              <NuxtLink v-if="isAuthenticated" to="/time-entries"
                class="text-gray-700 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                {{ $t('nav.timeEntries') }}
              </NuxtLink>
            </ClientOnly>

            <LanguageSwitcher />

            <ClientOnly>
              <div v-if="user" class="flex items-center space-x-2">
                <div class="text-right mr-2">
                  <p class="text-xs font-semibold text-gray-900 leading-none">{{ user.name }}</p>
                  <p class="text-[10px] text-gray-500 uppercase">{{ $t(`admin.users.roles.${user.role}`) }}</p>
                </div>
                <UButton color="gray" variant="ghost" icon="i-heroicons-arrow-right-on-rectangle" size="sm"
                  @click="handleLogout" />
              </div>
            </ClientOnly>
          </div>
        </div>
      </div>
    </nav>

    <main class="w-full px-6 sm:px-8 lg:px-12 xl:px-16 py-8">
      <slot />
    </main>
  </div>
</template>

<script setup lang="ts">
const { user, isAuthenticated, logout } = useAuth()
const { isDeviceAllowed, restrictionMessage, requiredDevice } = useDeviceType()

const handleLogout = async () => {
  await logout()
  await navigateTo('/')
}
</script>
