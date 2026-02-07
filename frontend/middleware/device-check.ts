export default defineNuxtRouteMiddleware((to, from) => {
    const { user } = useAuth()

    // Skip check on server-side
    if (process.server) return

    // Skip if no user
    if (!user.value) return

    const { isDeviceAllowed, restrictionMessage } = useDeviceType()

    // If device is not allowed for this role, redirect to restriction page
    if (!isDeviceAllowed.value) {
        // Store the restriction message for display
        const toast = useToast()
        toast.add({
            title: 'Appareil non compatible',
            description: restrictionMessage.value,
            color: 'red',
            timeout: 0, // Don't auto-dismiss
        })

        // Prevent navigation
        return abortNavigation()
    }
})
