/**
 * Plugin to manually load i18n messages
 */
export default defineNuxtPlugin(async (nuxtApp) => {
  const { $i18n } = nuxtApp
  
  // Wait for i18n to be ready
  if ($i18n && !$i18n.messages.value.fr) {
    try {
      // Try to load messages dynamically
      const messages = await import(`~/locales/fr.json`)
      if (messages.default) {
        $i18n.messages.value.fr = messages.default
      }
    } catch (error) {
      console.warn('Failed to load i18n messages:', error)
    }
  }
})
