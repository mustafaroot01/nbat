import axios from '@axios'
import { defineStore } from 'pinia'

export const useReportStore = defineStore('report', {
  actions: {
    fetchReports(params: Record<string, any>) {
      return axios.get('/admin/reports', { params })
    },
    resolveReport(id: number) {
      return axios.patch(`/admin/reports/${id}/resolve`)
    },
  },
})
