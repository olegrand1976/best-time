// Import translations synchronously
import fr from '~/locales/fr.json'
import en from '~/locales/en.json'
import de from '~/locales/de.json'
import nl from '~/locales/nl.json'
import it from '~/locales/it.json'
import pt from '~/locales/pt.json'

export default defineI18nConfig(() => ({
  legacy: false,
  locale: 'fr',
  fallbackLocale: 'en',
  messages: {
    fr,
    en,
    de,
    nl,
    it,
    pt,
  },
  // Disable linked message format completely
  compilerOptions: {
    linkedAttribute: false,
  },
}))
