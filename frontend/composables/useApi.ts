export const useApi = () => {
  const config = useRuntimeConfig()
  const authStore = useAuthStore()

  const apiFetch = async <T>(endpoint: string, options: any = {}): Promise<T> => {
    const url = endpoint.startsWith('http') ? endpoint : `${config.public.apiUrl}${endpoint}`

    const headers: Record<string, string> = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      ...options.headers,
    }

    // Add auth token if available
    if (authStore.token) {
      headers.Authorization = `Bearer ${authStore.token}`
    }

    try {
      const response = await $fetch<T>(url, {
        ...options,
        headers,
      })

      return response
    } catch (error: any) {
      // Handle 401 Unauthorized
      if ((error.statusCode === 401 || error.status === 401) && process.client) {
        authStore.logout()
        const route = useRoute()
        if (route.path !== '/login' && route.path !== '/') {
          await navigateTo('/login')
        }
      }

      throw error
    }
  }

  return {
    apiFetch,
  }
}
