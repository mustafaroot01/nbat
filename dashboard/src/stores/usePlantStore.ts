import { defineStore } from 'pinia'
import axios from '@axios'

export const usePlantStore = defineStore('PlantStore', {
  actions: {
    fetchPlants(params: Record<string, any>) {
      return axios.get('/admin/plants', { params })
    },

    fetchGeoJson(params?: Record<string, any>) {
      // Fetch all approved plants as GeoJSON from the app endpoint (no auth needed)
      return axios.get('/plants/geojson', { params })
    },

    fetchAllPlantsForMap(params?: Record<string, any>) {
      // Fetch admin plants without pagination for map display
      return axios.get('/admin/plants', { params: { ...params, itemsPerPage: 1000 } })
    },

    pendingPlant(id: number) {
      return axios.patch(`/admin/plants/${id}/pending`)
    },

    approvePlant(id: number) {
      return axios.patch(`/admin/plants/${id}/approve`)
    },

    rejectPlant(id: number, reason: string) {
      return axios.patch(`/admin/plants/${id}/reject`, { rejection_reason: reason })
    },

    deletePlant(id: number) {
      return axios.delete(`/admin/plants/${id}`)
    },

    fetchStatistics() {
      return axios.get('/admin/statistics')
    },
  },
})
