import { defineStore } from 'pinia'

interface Project {
  id: number
  name: string
  client: string | null
  status: string
}

interface TimeEntry {
  id: number
  user_id: number
  project_id: number | null
  project: Project | null
  start_time: string
  end_time: string | null
  description: string | null
  duration: number | null
  duration_formatted: string | null
  is_active: boolean
}

interface TimeEntryState {
  activeEntry: TimeEntry | null
  entries: TimeEntry[]
  projects: Project[]
  loading: boolean
}

export const useTimeEntryStore = defineStore('timeEntry', {
  state: (): TimeEntryState => ({
    activeEntry: null,
    entries: [],
    projects: [],
    loading: false,
  }),

  actions: {
    async fetchActiveEntry() {
      const config = useRuntimeConfig()
      const authStore = useAuthStore()

      try {
        const response = await $fetch<any>(`${config.public.apiUrl}/dashboard`, {
          headers: {
            Authorization: `Bearer ${authStore.token}`,
          },
        })

        this.activeEntry = response.active_entry || null
        return this.activeEntry
      } catch (error) {
        console.error('Error fetching active entry:', error)
        this.activeEntry = null
        return null
      }
    },

    async fetchEntries(params?: { date?: string; week?: boolean }) {
      const config = useRuntimeConfig()
      const authStore = useAuthStore()
      this.loading = true

      try {
        const queryParams = new URLSearchParams()
        if (params?.date) queryParams.append('date', params.date)
        if (params?.week) queryParams.append('week', 'true')

        const url = `${config.public.apiUrl}/time-entries${queryParams.toString() ? `?${queryParams}` : ''}`
        
        const response = await $fetch<any>(url, {
          headers: {
            Authorization: `Bearer ${authStore.token}`,
          },
        })

        this.entries = response.data || []
        return this.entries
      } catch (error) {
        console.error('Error fetching entries:', error)
        this.entries = []
        return []
      } finally {
        this.loading = false
      }
    },

    async fetchProjects() {
      const config = useRuntimeConfig()
      const authStore = useAuthStore()

      try {
        const response = await $fetch<Project[]>(`${config.public.apiUrl}/projects`, {
          headers: {
            Authorization: `Bearer ${authStore.token}`,
          },
        })

        this.projects = response
        return this.projects
      } catch (error) {
        console.error('Error fetching projects:', error)
        this.projects = []
        return []
      }
    },

    async startEntry(projectId?: number, description?: string) {
      const config = useRuntimeConfig()
      const authStore = useAuthStore()

      try {
        const response = await $fetch<TimeEntry>(`${config.public.apiUrl}/time-entries/start`, {
          method: 'POST',
          headers: {
            Authorization: `Bearer ${authStore.token}`,
          },
          body: {
            project_id: projectId || null,
            description: description || null,
          },
        })

        this.activeEntry = response
        return response
      } catch (error: any) {
        console.error('Error starting entry:', error)
        throw error
      }
    },

    async stopEntry() {
      const config = useRuntimeConfig()
      const authStore = useAuthStore()

      try {
        const response = await $fetch<TimeEntry>(`${config.public.apiUrl}/time-entries/stop`, {
          method: 'POST',
          headers: {
            Authorization: `Bearer ${authStore.token}`,
          },
        })

        this.activeEntry = null
        await this.fetchEntries()
        return response
      } catch (error) {
        console.error('Error stopping entry:', error)
        throw error
      }
    },

    async createEntry(data: {
      project_id?: number
      start_time: string
      end_time: string
      description?: string
    }) {
      const config = useRuntimeConfig()
      const authStore = useAuthStore()

      try {
        const response = await $fetch<TimeEntry>(`${config.public.apiUrl}/time-entries`, {
          method: 'POST',
          headers: {
            Authorization: `Bearer ${authStore.token}`,
          },
          body: data,
        })

        await this.fetchEntries()
        return response
      } catch (error) {
        console.error('Error creating entry:', error)
        throw error
      }
    },
  },
})
