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
      const { apiFetch } = useApi()

      try {
        const response = await apiFetch<any>('/dashboard')
        this.activeEntry = response?.active_entry || null
        return this.activeEntry
      } catch (error) {
        console.error('Error fetching active entry:', error)
        this.activeEntry = null
        return null
      }
    },

    async fetchEntries(params?: { date?: string; week?: boolean }) {
      const { apiFetch } = useApi()
      this.loading = true

      try {
        const queryParams = new URLSearchParams()
        if (params?.date) queryParams.append('date', params.date)
        if (params?.week) queryParams.append('week', 'true')

        const endpoint = `/time-entries${queryParams.toString() ? `?${queryParams}` : ''}`
        const response = await apiFetch<any>(endpoint)

        this.entries = response?.data || []
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
      const { apiFetch } = useApi()

      try {
        const response = await apiFetch<Project[]>('/projects')
        this.projects = response
        return this.projects
      } catch (error) {
        console.error('Error fetching projects:', error)
        this.projects = []
        return []
      }
    },

    async startEntry(projectId?: number, description?: string) {
      const { apiFetch } = useApi()

      try {
        const response = await apiFetch<TimeEntry>('/time-entries/start', {
          method: 'POST',
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
      const { apiFetch } = useApi()

      try {
        const response = await apiFetch<TimeEntry>('/time-entries/stop', {
          method: 'POST',
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
      const { apiFetch } = useApi()

      try {
        const response = await apiFetch<TimeEntry>('/time-entries', {
          method: 'POST',
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
