<template>
  <div class="min-h-screen bg-gray-50">
    <UNotifications />
    
    <nav class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <NuxtLink to="/dashboard" class="flex items-center space-x-2">
              <img src="/logo-icon.svg" alt="Best Time" class="h-8 w-8" />
              <span class="text-xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                Best Time
              </span>
            </NuxtLink>
          </div>

          <div class="flex items-center space-x-4">
            <NuxtLink
              v-if="isAuthenticated"
              to="/dashboard"
              class="text-gray-700 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium"
            >
              {{ $t('nav.dashboard') }}
            </NuxtLink>
            <NuxtLink
              v-if="isAuthenticated"
              to="/time-entries"
              class="text-gray-700 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium"
            >
              {{ $t('nav.timeEntries') }}
            </NuxtLink>
            
            <LanguageSwitcher />
            
            <div v-if="user" class="flex items-center space-x-2">
              <span class="text-sm text-gray-700">{{ user.name }}</span>
              <UButton
                color="gray"
                variant="ghost"
                size="sm"
                @click="handleLogout"
              >
                {{ $t('common.logout') }}
              </UButton>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <slot />
    </main>
  </div>
</template>

<script setup lang="ts">
const { user, isAuthenticated, logout } = useAuth()

const handleLogout = async () => {
  await logout()
}
</script>
