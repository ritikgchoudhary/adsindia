import api from './api'

export const authService = {
  async login(credentials) {
    const response = await api.post('/login', credentials)
    if (response.data.status === 'success') {
      localStorage.setItem('token', response.data.data.token)
    }
    return response.data
  },

  async register(userData) {
    const response = await api.post('/register', userData)
    if (response.data.status === 'success') {
      localStorage.setItem('token', response.data.data.token)
    }
    return response.data
  },

  async logout() {
    await api.post('/logout')
    localStorage.removeItem('token')
  },

  async checkToken() {
    const response = await api.post('/check-token')
    return response.data
  }
}
