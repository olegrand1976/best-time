// https://nuxt.com/docs/api/configuration/nuxt-config
import unheadAliasPlugin from './plugins/vite-unhead-alias'

export default defineNuxtConfig({
  devtools: { enabled: true },
  modules: ['@pinia/nuxt', '@nuxt/ui', '@nuxtjs/tailwindcss', '@nuxtjs/i18n'],
  css: ['~/assets/css/main.css'],

  // Optimize build to avoid unhead conflicts
  experimental: {
    payloadExtraction: false,
  },

  vite: {
    plugins: [unheadAliasPlugin()],
    server: {
      hmr: {
        protocol: 'ws',
        host: 'localhost',
        port: 3000,
      },
    },
    optimizeDeps: {
      exclude: ['unhead', '@unhead/vue'],
    },
  },

  devServer: {
    host: '0.0.0.0',
    port: 3000,
  },
  
  runtimeConfig: {
    public: {
      apiUrl: process.env.NUXT_PUBLIC_API_URL || 'http://localhost:9081/api',
    },
  },

  i18n: {
    locales: [
      { code: 'fr', name: 'Français', file: 'fr.json' },
      { code: 'en', name: 'English', file: 'en.json' },
      { code: 'de', name: 'Deutsch', file: 'de.json' },
      { code: 'nl', name: 'Nederlands', file: 'nl.json' },
      { code: 'it', name: 'Italiano', file: 'it.json' },
      { code: 'pt', name: 'Português', file: 'pt.json' },
    ],
    lazy: false, // Chargement synchrone via i18n.config.ts
    langDir: 'locales',
    defaultLocale: 'fr',
    strategy: 'no_prefix',
    detectBrowserLanguage: {
      useCookie: true,
      cookieKey: 'i18n_redirected',
      alwaysRedirect: false,
      fallbackLocale: 'fr',
    },
    vueI18n: './i18n.config.ts',
    compilation: {
      strictMessage: false,
      escapeHtml: false,
      jit: false,
    },
  },

  app: {
    head: {
      title: 'Best Time - Time Tracking',
      link: [
        { rel: 'icon', type: 'image/svg+xml', href: '/logo-icon.svg' },
      ],
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
      ],
    },
  },

  // Suppress unhead and preload warnings
  hooks: {
    'vite:extendConfig': (config: any) => {
      // Add alias to bypass unhead issues
      if (!config.resolve) config.resolve = {}
      if (!config.resolve.alias) config.resolve.alias = {}
    },
  },
})
