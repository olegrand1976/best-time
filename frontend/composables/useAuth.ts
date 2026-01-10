export const useAuth = () => {
  const authStore = useAuthStore()

  const login = async (email: string, password: string) => {
    const config = useRuntimeConfig()
    const { apiFetch } = useApi()

    try {
      const response = await $fetch<{ user: any; token: string }>(
        `${config.public.apiUrl}/auth/login`,
        {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
          },
          body: {
            email,
            password,
          },
        }
      )

      authStore.setAuth(response.user, response.token)
      return response
    } catch (error: any) {
      console.error('Login error:', error)
      throw error
    }
  }

  const logout = async () => {
    const config = useRuntimeConfig()
    const { apiFetch } = useApi()

    try {
      await apiFetch(`${config.public.apiUrl}/auth/logout`, {
        method: 'POST',
      })
    } catch (error) {
      console.error('Logout error:', error)
    } finally {
      authStore.logout()
      await navigateTo('/login')
    }
  }

  const checkAuth = async () => {
    const config = useRuntimeConfig()
    const { apiFetch } = useApi()

    try {
      const response = await apiFetch<{ user: any }>('/auth/me')
      authStore.setAuth(response.user, authStore.token || '')
      return response.user
    } catch (error) {
      authStore.logout()
      return null
    }
  }

  return {
    login,
    logout,
    checkAuth,
    isAuthenticated: computed(() => authStore.isAuthenticated),
    user: computed(() => authStore.user),
    isAdmin: computed(() => authStore.isAdmin),
    isEmployee: computed(() => authStore.isEmployee),
  }
}
