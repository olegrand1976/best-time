export const useAuth = () => {
  const authStore = useAuthStore()

  const login = async (email: string, password: string) => {
    const { apiFetch } = useApi()

    try {
      const response = await apiFetch<{ user: any; token: string }>(
        '/auth/login',
        {
          method: 'POST',
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
    const { apiFetch } = useApi()

    try {
      await apiFetch('/auth/logout', {
        method: 'POST',
      })
    } catch (error) {
      console.error('Logout error:', error)
    } finally {
      authStore.logout()
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
    isResponsable: computed(() => authStore.isResponsable),
    isManager: computed(() => authStore.isManager),
    isEmployee: computed(() => authStore.isEmployee),
  }
}
