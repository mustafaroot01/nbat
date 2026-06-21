import { defineStore } from 'pinia'
import axios from '@axios'

export const useScreenAdStore = defineStore('ScreenAdStore', {
  actions: {
    fetchScreenAds(params: Record<string, any>) {
      return axios.get('/admin/screen-ads', { params })
    },

    createScreenAd(formData: FormData) {
      return axios.post('/admin/screen-ads', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
    },

    updateScreenAd(id: number, formData: FormData) {
      formData.append('_method', 'PUT')

      return axios.post(`/admin/screen-ads/${id}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
    },

    toggleScreenAd(id: number) {
      return axios.patch(`/admin/screen-ads/${id}/toggle-status`)
    },

    deleteScreenAd(id: number) {
      return axios.delete(`/admin/screen-ads/${id}`)
    },
  },
})
