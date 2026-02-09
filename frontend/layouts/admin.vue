<template>
  <div v-if="!isDeviceAllowed" class="min-h-screen">
    <DeviceRestrictionMessage :message="restrictionMessage" :required-device="requiredDevice || 'desktop'" />
  </div>
  <div v-else class="min-h-screen bg-gray-50 flex flex-col w-full">
    <ClientOnly>
      <UNotifications />
    </ClientOnly>

    <nav class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-50">
      <div class="w-full px-6 sm:px-8 lg:px-12 xl:px-16">
        <div class="flex justify-between h-16">
          <div class="flex items-center space-x-8">
            <NuxtLink to="/dashboard" class="flex items-center space-x-2">
              <img src="/logo-icon.svg" alt="Best Time" class="h-8 w-8" />
              <span
                class="text-xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                Best Time
              </span>
            </NuxtLink>

            <!-- Desktop Menu -->
            <div class="hidden lg:flex items-center space-x-4">
              <NuxtLink to="/admin" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium"
                :class="{ 'text-blue-600 font-semibold': $route.path === '/admin' }">
                {{ $t('admin.nav.dashboard') }}
              </NuxtLink>

              <!-- Team (Responsable only) - Right after Dashboard -->
              <NuxtLink v-if="isResponsable" to="/admin/team"
                class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium"
                :class="{ 'text-blue-600 font-semibold': $route.path.startsWith('/admin/team') }">
                {{ $t('admin.nav.team') }}
              </NuxtLink>

              <NuxtLink to="/admin/users"
                class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium"
                :class="{ 'text-blue-600 font-semibold': $route.path.startsWith('/admin/users') }">
                {{ $t('admin.nav.users') }}
              </NuxtLink>
              <NuxtLink to="/admin/projects"
                class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium"
                :class="{ 'text-blue-600 font-semibold': $route.path.startsWith('/admin/projects') }">
                {{ $t('admin.nav.projects') }}
              </NuxtLink>
              <NuxtLink to="/admin/clients"
                class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium"
                :class="{ 'text-blue-600 font-semibold': $route.path.startsWith('/admin/clients') }">
                {{ $t('admin.nav.clients') }}
              </NuxtLink>

              <!-- Logs (Admin only) -->
              <div v-if="isAdmin" class="relative group">
                <NuxtLink to="/admin/logs/activity"
                  class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium"
                  :class="{ 'text-blue-600 font-semibold': $route.path.startsWith('/admin/logs') }">
                  {{ $t('admin.nav.logs') }}
                </NuxtLink>
                <div
                  class="absolute left-0 mt-1 w-48 bg-white rounded-md shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                  <div class="py-1">
                    <NuxtLink to="/admin/logs/activity" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                      {{ $t('admin.nav.activityLogs') }}
                    </NuxtLink>
                    <NuxtLink to="/admin/logs/technical"
                      class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                      {{ $t('admin.nav.technicalLogs') }}
                    </NuxtLink>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Right side: Notifications, Language, User -->
          <div class="flex items-center space-x-2 sm:space-x-4">
            <LanguageSwitcher />

            <ClientOnly>
              <div v-if="user" class="hidden sm:flex items-center space-x-2">
                <div class="text-right mr-2">
                  <p class="text-xs font-semibold text-gray-900 leading-none">{{ user.name }}</p>
                  <p class="text-[10px] text-gray-500 uppercase">{{ $t(`admin.users.roles.${user.role}`) }}</p>
                </div>
                <UButton color="gray" variant="ghost" icon="i-heroicons-arrow-right-on-rectangle" size="sm"
                  @click="handleLogout" />
              </div>

              <!-- Mobile menu button -->
              <UButton class="lg:hidden" color="gray" variant="ghost"
                :icon="isMenuOpen ? 'i-heroicons-x-mark' : 'i-heroicons-bars-3'" @click="isMenuOpen = !isMenuOpen" />
            </ClientOnly>
          </div>
        </div>
      </div>

      <!-- Mobile Menu -->
      <transition enter-active-class="transition duration-200 ease-out"
        enter-from-class="transform -translate-y-4 opacity-0" enter-to-class="transform translate-y-0 opacity-100"
        leave-active-class="transition duration-150 ease-in" leave-from-class="transform translate-y-0 opacity-100"
        leave-to-class="transform -translate-y-4 opacity-0">
        <div v-if="isMenuOpen" class="lg:hidden bg-white border-b border-gray-200 py-2 px-4 space-y-1">
          <NuxtLink to="/admin" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100"
            :class="{ 'bg-blue-50 text-blue-600': $route.path === '/admin' }" @click="isMenuOpen = false">
            {{ $t('admin.nav.dashboard') }}
          </NuxtLink>
          <NuxtLink to="/admin/users"
            class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100"
            :class="{ 'bg-blue-50 text-blue-600': $route.path.startsWith('/admin/users') }" @click="isMenuOpen = false">
            {{ $t('admin.nav.users') }}
          </NuxtLink>
          <NuxtLink to="/admin/projects"
            class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100"
            :class="{ 'bg-blue-50 text-blue-600': $route.path.startsWith('/admin/projects') }"
            @click="isMenuOpen = false">
            {{ $t('admin.nav.projects') }}
          </NuxtLink>
          <NuxtLink to="/admin/clients"
            class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100"
            :class="{ 'bg-blue-50 text-blue-600': $route.path.startsWith('/admin/clients') }"
            @click="isMenuOpen = false">
            {{ $t('admin.nav.clients') }}
          </NuxtLink>

          <template v-if="isAdmin">
            <div class="border-t border-gray-100 my-1 pt-1"></div>
            <NuxtLink to="/admin/logs/activity"
              class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100"
              @click="isMenuOpen = false">
              {{ $t('admin.nav.activityLogs') }}
            </NuxtLink>
            <NuxtLink to="/admin/logs/technical"
              class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100"
              @click="isMenuOpen = false">
              {{ $t('admin.nav.technicalLogs') }}
            </NuxtLink>
          </template>

          <div class="border-t border-gray-100 my-1 pt-1"></div>
          <div v-if="user" class="px-3 py-3 flex items-center justify-between">
            <div>
              <p class="text-sm font-bold text-gray-900">{{ user.name }}</p>
              <p class="text-xs text-gray-500 uppercase">{{ $t(`admin.users.roles.${user.role}`) }}</p>
            </div>
            <UButton color="red" variant="ghost" size="sm" @click="handleLogout">
              {{ $t('common.logout') }}
            </UButton>
          </div>
        </div>
      </transition>
    </nav>

    <main class="w-full px-6 sm:px-8 lg:px-12 xl:px-16 py-8">
      <slot />
    </main>
  </div>
</template>

<script setup lang="ts">
const { user, logout, isAdmin, isResponsable, isManager } = useAuth()
const { isDeviceAllowed, restrictionMessage, requiredDevice } = useDeviceType()
const router = useRouter()
const isMenuOpen = ref(false)

// Redirect if not manager
onMounted(() => {
  if (!isManager.value) {
    router.push('/dashboard')
  }
})

const handleLogout = async () => {
  isMenuOpen.value = false
  await logout()
  await navigateTo('/')
}

// Close menu on route change
watch(() => router.currentRoute.value.path, () => {
  isMenuOpen.value = false
})
</script>
