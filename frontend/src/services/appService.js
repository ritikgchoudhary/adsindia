import api from './api'

export const appService = {
  async getGeneralSettings() {
    const response = await api.get('/general-setting')
    return response.data
  },

  async getCountries() {
    const response = await api.get('/get-countries')
    return response.data
  },

  async getLanguage(key) {
    const response = await api.get(`/language/${key || ''}`)
    return response.data
  },

  async getPolicies() {
    const response = await api.get('/policies')
    return response.data
  },

  async getPolicy(slug) {
    const response = await api.get(`/policy/${slug}`)
    return response.data
  },

  async getFAQ() {
    const response = await api.get('/faq')
    return response.data
  },

  async getSEO() {
    const response = await api.get('/seo')
    return response.data
  },

  async submitContact(data) {
    const response = await api.post('/contact', data)
    return response.data
  },

  async getSections(key) {
    const response = await api.get(`/sections/${key || ''}`)
    return response.data
  },

  async getCustomPages() {
    const response = await api.get('/custom-pages')
    return response.data
  },

  async getCustomPage(slug) {
    const response = await api.get(`/custom-page/${slug}`)
    return response.data
  }
}
