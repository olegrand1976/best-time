<template>
  <div class="relative">
    <USelect
      v-model="selectedLocale"
      :options="localeOptions"
      value-attribute="code"
      option-attribute="code"
      @update:model-value="switchLanguage"
    >
      <template #label>
        <span class="flex items-center space-x-2">
          <span class="text-lg">{{ getFlagIcon(selectedLocale) }}</span>
          <span class="hidden sm:inline">{{ getLocaleLabel(selectedLocale) }}</span>
        </span>
      </template>
      <template #option="{ option }">
        <span class="flex items-center">
          <span>{{ option.name }}</span>
        </span>
      </template>
    </USelect>
  </div>
</template>

<script setup lang="ts">
const { locale, locales, setLocale } = useI18n()

// Récupérer dynamiquement les langues configurées depuis i18n
const localeOptions = computed(() => {
  if (!locales.value || locales.value.length === 0) {
    // Fallback si locales n'est pas disponible
    return [
      { code: 'fr', name: '🇫🇷 Français', label: 'Français' },
      { code: 'en', name: '🇬🇧 English', label: 'English' },
      { code: 'de', name: '🇩🇪 Deutsch', label: 'Deutsch' },
      { code: 'nl', name: '🇳🇱 Nederlands', label: 'Nederlands' },
      { code: 'it', name: '🇮🇹 Italiano', label: 'Italiano' },
      { code: 'pt', name: '🇵🇹 Português', label: 'Português' },
    ]
  }
  return locales.value.map((loc: any) => {
    const code = typeof loc === 'string' ? loc : loc.code
    const label = typeof loc === 'string' ? loc : loc.name || loc.code
    const flag = getFlagIcon(code)
    return {
      code,
      name: `${flag} ${label}`,
      label,
    }
  })
})

const selectedLocale = ref(locale.value || 'fr')

const getFlagIcon = (code: string): string => {
  const flags: Record<string, string> = {
    fr: '🇫🇷',
    en: '🇬🇧',
    de: '🇩🇪',
    nl: '🇳🇱',
    it: '🇮🇹',
    pt: '🇵🇹',
  }
  return flags[code] || '🌐'
}

const getLocaleLabel = (code: string): string => {
  const localeObj = localeOptions.value.find((l) => l.code === code)
  return localeObj?.label || localeObj?.name || code.toUpperCase()
}

const switchLanguage = async (newLocale: string) => {
  if (newLocale && newLocale !== locale.value) {
    try {
      await setLocale(newLocale)
      selectedLocale.value = newLocale
      // Les traductions se mettent à jour automatiquement via Vue reactivity
      // Pas besoin de recharger la page
    } catch (error) {
      console.error('Error switching language:', error)
    }
  }
}

// Synchroniser avec le changement de locale (au cas où il changerait ailleurs)
watch(() => locale.value, (newLocale) => {
  if (newLocale && newLocale !== selectedLocale.value) {
    selectedLocale.value = newLocale
  }
}, { immediate: true })
</script>
