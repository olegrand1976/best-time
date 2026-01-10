<template>
  <div class="min-h-screen bg-gray-50">
    <UNotifications />
    
    <nav class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center space-x-8">
            <NuxtLink to="/dashboard" class="flex items-center space-x-2">
              <img src="/logo-icon.svg" alt="Best Time" class="h-8 w-8" />
              <span class="text-xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                Best Time
              </span>
            </NuxtLink>

            <div class="hidden md:flex items-center space-x-4">
              <NuxtLink
                to="/admin"
                class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium"
                :class="{ 'text-blue-600 font-semibold': $route.path === '/admin' }"
              >
                {{ $t('admin.nav.dashboard') }}
              </NuxtLink>
              <NuxtLink
                to="/admin/users"
                class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium"
                :class="{ 'text-blue-600 font-semibold': $route.path.startsWith('/admin/users') }"
              >
                {{ $t('admin.nav.users') }}
              </NuxtLink>
              <NuxtLink
                to="/admin/projects"
                class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium"
                :class="{ 'text-blue-600 font-semibold': $route.path.startsWith('/admin/projects') }"
              >
                {{ $t('admin.nav.projects') }}
              </NuxtLink>
              <NuxtLink
                to="/admin/statistics"
                class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium"
                :class="{ 'text-blue-600 font-semibold': $route.path.startsWith('/admin/statistics') }"
              >
                {{ $t('admin.nav.statistics') }}
              </NuxtLink>
              <div class="relative group">
                <NuxtLink
                  to="/admin/logs/activity"
                  class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium"
                  :class="{ 'text-blue-600 font-semibold': $route.path.startsWith('/admin/logs') }"
                >
                  {{ $t('admin.nav.logs') }}
                </NuxtLink>
                <div class="absolute left-0 mt-1 w-48 bg-white rounded-md shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                  <div class="py-1">
                    <NuxtLink
                      to="/admin/logs/activity"
                      class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                    >
                      {{ $t('admin.nav.activityLogs') }}
                    </NuxtLink>
                    <NuxtLink
                      to="/admin/logs/technical"
                      class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                    >
                      {{ $t('admin.nav.technicalLogs') }}
                    </NuxtLink>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="flex items-center space-x-4">
            <LanguageSwitcher />
            
            <div v-if="user" class="flex items-center space-x-2">
              <span class="text-sm text-gray-700">{{ user.name }}</span>
              <UBadge color="blue">{{ $t('common.admin') }}</UBadge>
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
definePageMeta({
  middleware: 'auth',
})

const { user, logout, isAdmin } = useAuth()
const router = useRouter()

// Redirect if not admin
onMounted(() => {
  if (!isAdmin.value) {
    router.push('/dashboard')
  }
})

const handleLogout = async () => {
  await logout()
  await router.push('/login')
}
</script>
