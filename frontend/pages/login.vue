<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 flex">
        <div
            class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-blue-600 via-indigo-600 to-purple-600 p-12 flex-col justify-between text-white relative overflow-hidden">
            <!-- Decorative elements -->
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-0 left-0 w-96 h-96 bg-white rounded-full -translate-x-1/2 -translate-y-1/2">
                </div>
                <div class="absolute bottom-0 right-0 w-96 h-96 bg-white rounded-full translate-x-1/2 translate-y-1/2">
                </div>
            </div>

            <div class="relative z-10">
                <div class="mb-12">
                    <img src="/logo-icon.svg" alt="Best Time" class="h-16 mb-4" />
                    <p class="text-xl text-blue-100">{{ $t('login.tagline') }}</p>
                </div>

                <div class="space-y-8">
                    <div class="flex items-start space-x-4">
                        <div
                            class="flex-shrink-0 w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center backdrop-blur-sm">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold mb-2">{{ $t('login.features.realtime.title') }}</h3>
                            <p class="text-blue-100">{{ $t('login.features.realtime.description') }}</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-4">
                        <div
                            class="flex-shrink-0 w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center backdrop-blur-sm">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold mb-2">{{ $t('login.features.reports.title') }}</h3>
                            <p class="text-blue-100">{{ $t('login.features.reports.description') }}</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-4">
                        <div
                            class="flex-shrink-0 w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center backdrop-blur-sm">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold mb-2">{{ $t('login.features.team.title') }}</h3>
                            <p class="text-blue-100">{{ $t('login.features.team.description') }}</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-4">
                        <div
                            class="flex-shrink-0 w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center backdrop-blur-sm">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold mb-2">{{ $t('login.features.security.title') }}</h3>
                            <p class="text-blue-100">{{ $t('login.features.security.description') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="relative z-10 text-blue-100 text-sm">
                <p>© 2024 Best Time. Tous droits réservés.</p>
            </div>
        </div>

        <!-- Right side - Login Form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8">
            <div class="w-full max-w-md">
                <!-- Mobile Logo -->
                <div class="lg:hidden text-center mb-8">
                    <img src="/logo-icon.svg" alt="Best Time" class="h-14 mx-auto mb-2" />
                    <p class="text-gray-600">{{ $t('login.mobileTagline') }}</p>
                </div>

                <!-- Desktop Logo -->
                <div class="hidden lg:block mb-8">
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">{{ $t('common.welcome') }}</h2>
                    <p class="text-gray-600">{{ $t('auth.subtitle') }}</p>
                </div>

                <UCard class="shadow-xl border-0">
                    <template #header>
                        <div class="text-center">
                            <h3 class="text-2xl font-bold text-gray-900">{{ $t('auth.title') }}</h3>
                        </div>
                    </template>

                    <form @submit.prevent="handleLogin" class="space-y-6">
                        <div>
                            <UFormGroup :label="$t('auth.email')" name="email" required>
                                <UInput v-model="email" type="email" required autocomplete="email"
                                    placeholder="votre@email.com" size="lg" icon="i-heroicons-envelope" :ui="{
                                        base: 'relative block w-full disabled:cursor-not-allowed disabled:opacity-75 focus:outline-none border-0',
                                        rounded: 'rounded-lg',
                                        placeholder: 'placeholder-gray-400',
                                        size: {
                                            lg: 'text-base',
                                        },
                                        gap: {
                                            lg: 'gap-3',
                                        },
                                        padding: {
                                            lg: 'px-4 py-3',
                                        },
                                    }" />
                            </UFormGroup>
                        </div>

                        <div>
                            <UFormGroup :label="$t('auth.password')" name="password" required>
                                <UInput v-model="password" type="password" required autocomplete="current-password"
                                    :placeholder="$t('auth.passwordPlaceholder')" size="lg"
                                    icon="i-heroicons-lock-closed" :ui="{
                                        base: 'relative block w-full disabled:cursor-not-allowed disabled:opacity-75 focus:outline-none border-0',
                                        rounded: 'rounded-lg',
                                        placeholder: 'placeholder-gray-400',
                                        size: {
                                            lg: 'text-base',
                                        },
                                        gap: {
                                            lg: 'gap-3',
                                        },
                                        padding: {
                                            lg: 'px-4 py-3',
                                        },
                                    }" />
                            </UFormGroup>
                        </div>

                        <UAlert v-if="error" color="red" variant="soft" :title="error"
                            icon="i-heroicons-exclamation-circle" class="mb-4" />

                        <div>
                            <UButton type="submit" block :loading="loading" size="lg" color="primary"
                                class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold py-3 rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl">
                                <template v-if="!loading">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1">
                                        </path>
                                    </svg>
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

                <div class="lg:hidden mt-8 space-y-4">
                    <div class="bg-white/50 backdrop-blur-sm rounded-lg p-4 border border-gray-200">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">{{ $t('login.features.realtime.title') }}</h4>
                                <p class="text-sm text-gray-600">{{
                                    $t('login.features.realtime.description').split('.')[0] }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white/50 backdrop-blur-sm rounded-lg p-4 border border-gray-200">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">{{ $t('login.features.reports.title') }}</h4>
                                <p class="text-sm text-gray-600">{{
                                    $t('login.features.reports.description').split('.')[0] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
definePageMeta({
    middleware: 'guest',
    layout: false,
})

const { t } = useI18n()
const email = ref('')
const password = ref('')
const loading = ref(false)
const error = ref('')

const { login } = useAuth()
const router = useRouter()

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
