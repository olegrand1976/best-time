import { defineStore } from 'pinia'

interface User {
  id: number
  name: string
  email: string
  role: 'admin' | 'employee'
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
    isEmployee: (state): boolean => state.user?.role === 'employee',
  },

  actions: {
    setAuth(user: User, token: string) {
      this.user = user
      this.token = token
      this.isAuthenticated = true
      
      // Store token in localStorage
      if (process.client) {
        localStorage.setItem('auth_token', token)
        localStorage.setItem('auth_user', JSON.stringify(user))
      }
    },

    logout() {
      this.user = null
      this.token = null
      this.isAuthenticated = false

      if (process.client) {
        localStorage.removeItem('auth_token')
        localStorage.removeItem('auth_user')
      }
    },

    initAuth() {
      if (process.client) {
        const token = localStorage.getItem('auth_token')
        const userStr = localStorage.getItem('auth_user')

        if (token && userStr) {
          try {
            const user = JSON.parse(userStr)
            this.setAuth(user, token)
          } catch (e) {
            this.logout()
          }
        }
      }
    },
  },
})
