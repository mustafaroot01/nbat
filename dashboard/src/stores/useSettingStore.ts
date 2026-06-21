import axios from '@axios'
import { defineStore } from 'pinia'

export const useSettingStore = defineStore('SettingStore', {
  actions: {
    fetchSettings() {
      return axios.get('/admin/settings')
    },

    fetchStatistics() {
      return axios.get('/admin/statistics')
    },

    fetchGovernorateStats() {
      return axios.get('/admin/statistics/governorates')
    },

    updateSettings(data: FormData | Record<string, any>) {
      if (data instanceof FormData) {
        data.append('_method', 'PUT')
      } else {
        data._method = 'PUT'
      }
      return axios.post('/admin/settings', data, {
        headers: {
          'Content-Type': data instanceof FormData ? 'multipart/form-data' : 'application/json',
        },
      })
    },

    sendNotification(data: FormData | Record<string, any>) {
      return axios.post('/admin/notifications/send', data, {
        headers: {
          'Content-Type': data instanceof FormData ? 'multipart/form-data' : 'application/json',
        },
      })
    },

    editNotification(id: number, data: FormData | Record<string, any>) {
      return axios.post(`/admin/notifications/${id}`, data, {
        headers: {
          'Content-Type': data instanceof FormData ? 'multipart/form-data' : 'application/json',
        },
      })
    },

    deleteNotification(id: number) {
      return axios.delete(`/admin/notifications/${id}`)
    },

    fetchNotifications(params?: Record<string, any>) {
      return axios.get('/admin/notifications', { params })
    },

    fetchAppVersions() {
      return axios.get('/admin/app-versions')
    },

    createAppVersion(data: Record<string, any>) {
      return axios.post('/admin/app-versions', data)
    },

    updateAppVersion(id: number, data: Record<string, any>) {
      return axios.put(`/admin/app-versions/${id}`, data)
    },

    deleteAppVersion(id: number) {
      return axios.delete(`/admin/app-versions/${id}`)
    },

    fetchGovernorates() {
      return axios.get('/admin/governorates')
    },

    toggleGovernorateCoverage(id: number) {
      return axios.patch(`/admin/governorates/${id}/toggle-coverage`)
    },

    updateCoverageMode(data: { coverage_mode: string; governorate_ids?: number[] }) {
      return axios.put('/admin/settings/coverage', data)
    },
  },
})
