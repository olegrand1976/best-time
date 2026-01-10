<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
    <div class="max-w-md w-full text-center">
      <div class="mb-6">
        <svg class="mx-auto h-16 w-16 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
      </div>
      <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $t('error.title', 'Une erreur est survenue') }}</h1>
      <p class="text-gray-600 mb-6">{{ $t('error.message', 'Une erreur inattendue s\'est produite. Veuillez rafraîchir la page.') }}</p>
      <div class="space-y-3">
        <UButton @click="handleError" color="primary" block>
          {{ $t('error.refresh', 'Rafraîchir la page') }}
        </UButton>
        <UButton @click="goHome" variant="ghost" block>
          {{ $t('error.home', 'Retour à l\'accueil') }}
        </UButton>
      </div>
      
      <!-- Supprimer les erreurs non-bloquantes -->
      <div v-if="isNonBlockingError" class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
        <p class="text-sm text-blue-800">
          {{ $t('error.nonBlocking', 'Cette erreur est non-bloquante et n\'affecte pas le fonctionnement de l\'application.') }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
const props = defineProps<{
  error: any
}>()

const isNonBlockingError = computed(() => {
  const errorMessage = props.error?.message || String(props.error || '')
  return errorMessage.includes('getActiveHead') || 
         errorMessage.includes('does not provide an export')
})

const handleError = () => {
  // Supprimer l'erreur si elle est non-bloquante
  if (isNonBlockingError.value) {
    clearError({ redirect: '/' })
  } else {
    window.location.reload()
  }
}

const goHome = () => {
  clearError({ redirect: '/' })
}

// Masquer l'erreur automatiquement si elle est non-bloquante
onMounted(() => {
  if (isNonBlockingError.value) {
    // Rediriger immédiatement sans afficher l'erreur
    clearError({ redirect: '/' })
  }
})
</script>
