// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  devtools: { enabled: true },
  modules: ['@pinia/nuxt', '@nuxt/ui', '@nuxtjs/tailwindcss'],
  css: ['~/assets/css/main.css'],
  
  runtimeConfig: {
    public: {
      apiUrl: process.env.NUXT_PUBLIC_API_URL || 'http://localhost:9081/api',
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
})
