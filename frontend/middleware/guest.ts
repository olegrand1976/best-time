export default defineNuxtRouteMiddleware((to, from) => {
  const authStore = useAuthStore()

  // Initialize auth if not already done
  if (!authStore.isAuthenticated) {
    authStore.initAuth()
  }

  // Redirect to dashboard if already authenticated
  if (authStore.isAuthenticated) {
    return navigateTo('/dashboard')
  }
  
  // Allow access to home page (/)
  if (to.path === '/') {
    return
  }
})
