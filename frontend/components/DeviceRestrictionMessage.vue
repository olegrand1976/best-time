<template>
    <div class="min-h-screen bg-gradient-to-br from-red-50 to-orange-50 flex items-center justify-center p-4">
        <UCard class="max-w-md w-full">
            <template #header>
                <div class="flex items-center space-x-3">
                    <UIcon
                        :name="requiredDevice === 'desktop' ? 'i-heroicons-computer-desktop' : 'i-heroicons-device-phone-mobile'"
                        class="w-8 h-8 text-red-500" />
                    <h2 class="text-xl font-bold text-gray-900">Appareil non compatible</h2>
                </div>
            </template>

            <div class="space-y-4">
                <p class="text-gray-700">{{ message }}</p>

                <div v-if="requiredDevice === 'desktop'" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start space-x-3">
                        <UIcon name="i-heroicons-information-circle" class="w-5 h-5 text-blue-600 mt-0.5" />
                        <div class="text-sm text-blue-900">
                            <p class="font-semibold mb-1">Pourquoi cette restriction ?</p>
                            <p>Les fonctionnalités d'administration nécessitent un écran plus large pour une expérience
                                optimale.
                            </p>
                        </div>
                    </div>
                </div>

                <div v-if="requiredDevice === 'mobile'" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start space-x-3">
                        <UIcon name="i-heroicons-information-circle" class="w-5 h-5 text-blue-600 mt-0.5" />
                        <div class="text-sm text-blue-900">
                            <p class="font-semibold mb-1">Pourquoi cette restriction ?</p>
                            <p>Le pointage et la gestion des heures sont optimisés pour une utilisation mobile sur le
                                terrain.</p>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-200">
                    <UButton color="gray" variant="ghost" block @click="handleLogout">
                        Se déconnecter
                    </UButton>
                </div>
            </div>
        </UCard>
    </div>
</template>

<script setup lang="ts">
defineProps<{
    message: string
    requiredDevice: 'desktop' | 'mobile' | null
}>()

const { logout } = useAuth()

const handleLogout = async () => {
    await logout()
    await navigateTo('/')
}
</script>
