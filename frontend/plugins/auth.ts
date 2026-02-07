export default defineNuxtPlugin(() => {
  const authStore = useAuthStore()

  // Initialize auth from cookies on both server and client side
  authStore.initAuth()
})
