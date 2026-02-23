import api from './api'

export const userService = {
  async getDashboard() {
    const response = await api.get('/dashboard')
    return response.data
  },

  async getUserInfo() {
    const response = await api.get('/user-info')
    return response.data
  },

  async getTransactions(params = {}) {
    const response = await api.get('/transactions', { params })
    return response.data
  },
  async getConversionLog(search = '') {
    const params = {}
    if (search) params.search = search
    const response = await api.get('/conversion/log', { params })
    return response.data
  }
}
