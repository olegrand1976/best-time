import { defineStore } from 'pinia'

interface User {
  id: number
  name: string
  email: string
  role: 'admin' | 'responsable' | 'gestionnaire' | 'team_leader' | 'ouvrier' | 'employee'
}

interface AuthState {
  user: User | null
  token: string | null
  isAuthenticated: boolean
}

export const useAuthStore = defineStore('auth', {
  state: (): AuthState => ({
    user: null,
    token: null,
    isAuthenticated: false,
  }),

  getters: {
    isAdmin: (state): boolean => state.user?.role === 'admin',
    isResponsable: (state): boolean => state.user?.role === 'responsable',
    isManager: (state): boolean => ['admin', 'responsable', 'gestionnaire', 'team_leader'].includes(state.user?.role || ''),
    isGestionnaire: (state): boolean => state.user?.role === 'gestionnaire',
    isEmployee: (state): boolean => ['ouvrier', 'employee'].includes(state.user?.role || ''),
  },

  actions: {
    setAuth(user: User, token: string) {
      this.user = user
      this.token = token
      this.isAuthenticated = true

      // Store in cookies with global path for SSR and cross-route consistency
      const tokenCookie = useCookie<string | null>('auth_token', { maxAge: 60 * 60 * 24 * 7, path: '/' })
      const userCookie = useCookie<User | null>('auth_user', { maxAge: 60 * 60 * 24 * 7, path: '/' })
      tokenCookie.value = token
      userCookie.value = user // useCookie handles serialization automatically
    },

    logout() {
      this.user = null
      this.token = null
      this.isAuthenticated = false

      // Clear cookies with exact same path options
      const tokenCookie = useCookie<string | null>('auth_token', { path: '/' })
      const userCookie = useCookie<User | null>('auth_user', { path: '/' })
      tokenCookie.value = null
      userCookie.value = null
    },

    initAuth() {
      const tokenCookie = useCookie<string | null>('auth_token', { path: '/' })
      const userCookie = useCookie<User | null>('auth_user', { path: '/' })

      if (tokenCookie.value && userCookie.value) {
        try {
          // useCookie handles de-serialization automatically
          this.user = userCookie.value
          this.token = tokenCookie.value
          this.isAuthenticated = true
        } catch (e) {
          this.logout()
        }
      }
    },
  },
})
