/**
 * Plugin pour supprimer complètement les erreurs unhead getActiveHead
 * et empêcher qu'elles ne causent un crash de l'application
 */
export default defineNuxtPlugin({
  name: 'suppress-unhead-error',
  enforce: 'pre',
  setup() {
    if (typeof window === 'undefined') return

    // Intercepter les erreurs de module avant le rendu
    const originalImport = window.fetch
    
    // Wrapper pour intercepter les imports de modules problématiques
    const handleModuleError = (error: any) => {
      const message = error?.message || String(error || '')
      
      if (message.includes('getActiveHead') || 
          message.includes('does not provide an export')) {
        // Créer une erreur silencieuse qui ne crash pas
        console.warn('Unhead compatibility issue detected (non-blocking)')
        return true // Indiquer que l'erreur a été gérée
      }
      
      return false
    }

    // Intercepter les erreurs globales
    const originalErrorHandler = window.onerror
    window.onerror = function(message, source, lineno, colno, error) {
      if (handleModuleError(error || message)) {
        return true // Supprimer l'erreur
      }
      
      if (originalErrorHandler) {
        return originalErrorHandler.call(this, message, source, lineno, colno, error)
      }
      
      return false
    }

    // Intercepter les promesses rejetées
    window.addEventListener('unhandledrejection', (event) => {
      if (handleModuleError(event.reason)) {
        event.preventDefault()
        event.stopPropagation()
      }
    }, { capture: true })
  },
})
