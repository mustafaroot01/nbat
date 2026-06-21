import { defineStore } from 'pinia'
import axios from '@axios'

export const useBannerStore = defineStore('BannerStore', {
  actions: {
    fetchBanners(params: Record<string, any>) {
      return axios.get('/admin/banners', { params })
    },

    createBanner(formData: FormData) {
      return axios.post('/admin/banners', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
    },

    updateBanner(id: number, formData: FormData) {
      formData.append('_method', 'PUT')

      return axios.post(`/admin/banners/${id}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
    },

    toggleBanner(id: number) {
      return axios.patch(`/admin/banners/${id}/toggle`)
    },

    deleteBanner(id: number) {
      return axios.delete(`/admin/banners/${id}`)
    },
  },
})
