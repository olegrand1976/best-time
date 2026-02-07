<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50">
    <!-- Navigation -->
    <nav class="bg-white/80 backdrop-blur-md shadow-sm border-b border-gray-200 sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
          <div class="flex items-center space-x-2">
            <img src="/logo-icon.svg" alt="Best Time" class="h-8 w-8" />
            <span class="text-xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
              {{ $t('appName') }}
            </span>
          </div>
          <div class="flex items-center space-x-4">
            <LanguageSwitcher />
            <ClientOnly>
              <NuxtLink v-if="!isAuthenticated" to="/login"
                class="text-gray-700 hover:text-blue-600 px-4 py-2 rounded-md text-sm font-medium transition-colors">
                {{ $t('auth.loginButton') }}
              </NuxtLink>
              <NuxtLink v-else to="/dashboard"
                class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition-colors">
                {{ $t('nav.dashboard') }}
              </NuxtLink>
            </ClientOnly>
          </div>
        </div>
      </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative overflow-hidden py-20 lg:py-32">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
          <!-- Left: Content -->
          <div class="text-center lg:text-left">
            <h1 class="text-4xl lg:text-6xl font-bold text-gray-900 mb-6 leading-tight">
              {{ $t('home.hero.title') }}
            </h1>
            <p class="text-xl text-gray-600 mb-8 leading-relaxed">
              {{ $t('home.hero.subtitle') }}
            </p>

            <ClientOnly>
              <div v-if="!isAuthenticated" class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                <NuxtLink to="/login"
                  class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-8 py-4 rounded-lg font-semibold hover:from-blue-700 hover:to-indigo-700 transition-all shadow-lg hover:shadow-xl">
                  {{ $t('home.hero.cta') }}
                </NuxtLink>
              </div>
            </ClientOnly>
          </div>

          <ClientOnly>
            <!-- Right: Login Form (only if not authenticated) -->
            <div v-if="!isAuthenticated" class="w-full max-w-md mx-auto lg:mx-0">
              <UCard class="shadow-2xl border-0">
                <template #header>
                  <div class="text-center">
                    <h3 class="text-2xl font-bold text-gray-900">{{ $t('auth.title') }}</h3>
                    <p class="text-sm text-gray-500 mt-1">{{ $t('auth.subtitle') }}</p>
                  </div>
                </template>

                <form @submit.prevent="handleLogin" class="space-y-6">
                  <div>
                    <UFormGroup :label="$t('auth.email')" name="email" required>
                      <UInput v-model="email" type="email" required autocomplete="email" placeholder="votre@email.com"
                        size="lg" icon="i-heroicons-envelope" />
                    </UFormGroup>
                  </div>

                  <div>
                    <UFormGroup :label="$t('auth.password')" name="password" required>
                      <UInput v-model="password" type="password" required autocomplete="current-password"
                        :placeholder="$t('auth.passwordPlaceholder')" size="lg" icon="i-heroicons-lock-closed" />
                    </UFormGroup>
                  </div>

                  <UAlert v-if="error" color="red" variant="soft" :title="error" icon="i-heroicons-exclamation-circle"
                    class="mb-4" />

                  <div>
                    <UButton type="submit" block :loading="loading" size="lg" color="primary"
                      class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold">
                      <template v-if="!loading">
                        {{ $t('auth.loginButton') }}
                      </template>
                      <template v-else>
                        {{ $t('auth.loggingIn') }}
                      </template>
                    </UButton>
                  </div>
                </form>

                <template #footer>
                  <div class="text-center text-sm text-gray-500">
                    <p>{{ $t('auth.demoAccounts') }}</p>
                    <div class="mt-3 space-y-1 text-xs">
                      <p><strong>{{ $t('auth.admin') }}:</strong> admin@besttime.test / password</p>
                      <p><strong>{{ $t('auth.employee') }}:</strong> john@besttime.test / password</p>
                    </div>
                  </div>
                </template>
              </UCard>
            </div>

            <!-- Right: User info (if authenticated) -->
            <div v-else class="w-full max-w-md mx-auto lg:mx-0">
              <UCard class="shadow-2xl border-0 bg-gradient-to-br from-blue-600 to-indigo-600">
                <div class="text-white text-center">
                  <div class="mb-4">
                    <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                      <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                      </svg>
                    </div>
                    <h3 class="text-2xl font-bold mb-2">{{ user?.name }}</h3>
                    <p class="text-blue-100">{{ user?.email }}</p>
                    <UBadge :color="user?.role === 'admin' ? 'yellow' : 'blue'"
                      class="mt-2 text-xs uppercase font-bold px-3">
                      {{ $t('admin.users.roles.' + user?.role) }}
                    </UBadge>
                  </div>
                  <NuxtLink to="/dashboard"
                    class="inline-block bg-white text-blue-600 px-6 py-3 rounded-lg font-semibold hover:bg-blue-50 transition-colors">
                    {{ $t('nav.dashboard') }}
                  </NuxtLink>
                </div>
              </UCard>
            </div>
          </ClientOnly>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section class="py-20 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">
            {{ $t('home.features.title') }}
          </h2>
          <p class="text-xl text-gray-600 max-w-2xl mx-auto">
            {{ $t('home.features.subtitle') }}
          </p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
          <div class="bg-gradient-to-br from-blue-50 to-indigo-50 p-6 rounded-xl">
            <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center mb-4">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ $t('login.features.realtime.title') }}</h3>
            <p class="text-gray-600">{{ $t('login.features.realtime.description') }}</p>
          </div>

          <div class="bg-gradient-to-br from-purple-50 to-pink-50 p-6 rounded-xl">
            <div class="w-12 h-12 bg-purple-600 rounded-lg flex items-center justify-center mb-4">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                </path>
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ $t('login.features.reports.title') }}</h3>
            <p class="text-gray-600">{{ $t('login.features.reports.description') }}</p>
          </div>

          <div class="bg-gradient-to-br from-green-50 to-emerald-50 p-6 rounded-xl">
            <div class="w-12 h-12 bg-green-600 rounded-lg flex items-center justify-center mb-4">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                </path>
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ $t('login.features.team.title') }}</h3>
            <p class="text-gray-600">{{ $t('login.features.team.description') }}</p>
          </div>

          <div class="bg-gradient-to-br from-orange-50 to-red-50 p-6 rounded-xl">
            <div class="w-12 h-12 bg-orange-600 rounded-lg flex items-center justify-center mb-4">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                </path>
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ $t('login.features.security.title') }}</h3>
            <p class="text-gray-600">{{ $t('login.features.security.description') }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-3 gap-8">
          <div>
            <div class="flex items-center space-x-2 mb-4">
              <img src="/logo-icon.svg" alt="Best Time" class="h-8 w-8" />
              <span class="text-xl font-bold">{{ $t('appName') }}</span>
            </div>
            <p class="text-gray-400">{{ $t('login.tagline') }}</p>
          </div>
          <div>
            <h4 class="font-semibold mb-4">{{ $t('home.footer.links') }}</h4>
            <ul class="space-y-2 text-gray-400">
              <li>
                <NuxtLink to="/dashboard" class="hover:text-white transition-colors">
                  {{ $t('nav.dashboard') }}
                </NuxtLink>
              </li>
              <li>
                <NuxtLink to="/time-entries" class="hover:text-white transition-colors">
                  {{ $t('nav.timeEntries') }}
                </NuxtLink>
              </li>
            </ul>
          </div>
          <div>
            <h4 class="font-semibold mb-4">{{ $t('home.footer.legal') }}</h4>
            <p class="text-gray-400 text-sm">{{ $t('login.footer') }}</p>
          </div>
        </div>
        <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400 text-sm">
          <p>{{ $t('login.footer') }}</p>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: false,
  // Accessible aux visiteurs
})

const { t } = useI18n()
const { user, isAuthenticated, login } = useAuth()
const router = useRouter()

const email = ref('')
const password = ref('')
const loading = ref(false)
const error = ref('')

const handleLogin = async () => {
  error.value = ''
  loading.value = true

  try {
    await login(email.value, password.value)
    await router.push('/dashboard')
  } catch (err: any) {
    error.value = err.data?.message || t('auth.loginError')
  } finally {
    loading.value = false
  }
}
</script>
