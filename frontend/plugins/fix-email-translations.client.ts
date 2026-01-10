/**
 * Plugin to replace [at] with @ in translations
 */
export default defineNuxtPlugin((nuxtApp) => {
  const { $i18n } = nuxtApp

  if ($i18n && $i18n.messages.value) {
    // Replace [at] with @ in all messages
    const replaceAt = (obj: any): any => {
      if (typeof obj === 'string') {
        return obj.replace(/\[at\]/g, '@')
      }
      if (Array.isArray(obj)) {
        return obj.map(replaceAt)
      }
      if (obj && typeof obj === 'object') {
        const result: any = {}
        for (const key in obj) {
          result[key] = replaceAt(obj[key])
        }
        return result
      }
      return obj
    }

    // Transform messages for all locales
    Object.keys($i18n.messages.value).forEach((locale) => {
      if ($i18n.messages.value[locale]) {
        $i18n.messages.value[locale] = replaceAt($i18n.messages.value[locale])
      }
    })
  }
})
