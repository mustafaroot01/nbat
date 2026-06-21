import { defineStore } from 'pinia'
import axios from '@axios'

export const useAdminUserStore = defineStore('AdminUserStore', {
  actions: {
    fetchUsers(params: Record<string, any>) {
      return axios.get('/admin/users', { params })
    },

    fetchUser(id: number) {
      return axios.get(`/admin/users/${id}`)
    },

    updateUser(id: number, data: any) {
      return axios.put(`/admin/users/${id}`, data)
    },

    toggleUser(id: number) {
      return axios.patch(`/admin/users/${id}/toggle`)
    },

    toggleTrustedUser(id: number) {
      return axios.patch(`/admin/users/${id}/toggle-trusted`)
    },

    deleteUser(id: number) {
      return axios.delete(`/admin/users/${id}`)
    },
  },
})
