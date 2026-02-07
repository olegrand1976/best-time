import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useAuth } from './useAuth'

export const useDeviceType = () => {
    const { user } = useAuth()
    const isMobile = ref(false)
    const isTablet = ref(false)

    const checkDevice = () => {
        if (typeof window === 'undefined') return

        const width = window.innerWidth
        isMobile.value = width < 768
        isTablet.value = width >= 768 && width < 1024
    }

    const isDesktop = computed(() => !isMobile.value && !isTablet.value)

    // Device compatibility based on role
    const deviceCompatibility = computed(() => {
        if (!user.value) return { allowed: true, message: '' }

        const role = user.value.role

        // Admin, Responsable, Gestionnaire: Desktop only
        if (['admin', 'responsable', 'gestionnaire'].includes(role)) {
            if (isMobile.value || isTablet.value) {
                return {
                    allowed: false,
                    message: 'Cette interface nécessite un ordinateur. Veuillez utiliser un appareil desktop.',
                    requiredDevice: 'desktop' as 'desktop' | 'mobile' | null
                }
            }
        }

        // Ouvrier: Mobile only
        if (role === 'ouvrier') {
            if (isDesktop.value) {
                return {
                    allowed: false,
                    message: 'Cette interface est conçue pour les appareils mobiles. Veuillez utiliser votre smartphone ou tablette.',
                    requiredDevice: 'mobile' as 'desktop' | 'mobile' | null
                }
            }
        }

        // Team Leader: Both allowed
        // No restriction for team_leader

        return { allowed: true, message: '', requiredDevice: null }
    })

    const isDeviceAllowed = computed(() => deviceCompatibility.value.allowed)
    const restrictionMessage = computed(() => deviceCompatibility.value.message)
    const requiredDevice = computed(() => deviceCompatibility.value.requiredDevice)

    onMounted(() => {
        checkDevice()
        window.addEventListener('resize', checkDevice)
    })

    onUnmounted(() => {
        if (typeof window !== 'undefined') {
            window.removeEventListener('resize', checkDevice)
        }
    })

    return {
        isMobile,
        isTablet,
        isDesktop,
        isDeviceAllowed,
        restrictionMessage,
        requiredDevice,
        checkDevice
    }
}
