export default defineNuxtRouteMiddleware(async (to, from) => {
  const authStore = useAuthStore()
  const { checkAuth } = useAuth()

  // Initialize auth if not already done
  if (!authStore.isAuthenticated) {
    authStore.initAuth()
  }

  // Check authentication
  if (!authStore.isAuthenticated) {
    // Try to verify token with backend
    const user = await checkAuth()
    
    if (!user) {
      return navigateTo('/')
    }
  }
})
