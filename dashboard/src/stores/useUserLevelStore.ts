import { defineStore } from 'pinia'
import axios from '@axios'

export const useUserLevelStore = defineStore('UserLevelStore', {
  actions: {
    fetchUserLevels() {
      return axios.get('/admin/user-levels')
    },

    createUserLevel(data: Record<string, any>) {
      return axios.post('/admin/user-levels', data)
    },

    updateUserLevel(id: number, data: Record<string, any>) {
      return axios.put(`/admin/user-levels/${id}`, data)
    },

    deleteUserLevel(id: number) {
      return axios.delete(`/admin/user-levels/${id}`)
    },
  },
})
