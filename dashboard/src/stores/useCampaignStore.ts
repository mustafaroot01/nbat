import { defineStore } from 'pinia'
import axios from '@axios'

export const useCampaignStore = defineStore('CampaignStore', {
  actions: {
    fetchCampaigns(params: Record<string, any>) {
      return axios.get('/admin/campaigns', { params })
    },

    createCampaign(data: FormData | Record<string, any>) {
      return axios.post('/admin/campaigns', data)
    },

    updateCampaign(id: number, data: FormData | Record<string, any>) {
      // Use POST because of multipart/form-data
      return axios.post(`/admin/campaigns/${id}`, data)
    },

    deleteCampaign(id: number) {
      return axios.delete(`/admin/campaigns/${id}`)
    },
  },
})
