import api from './api'

export const campaignService = {
  async getTrafficTypes() {
    const response = await api.get('/traffic-types')
    return response.data
  },

  async getCampaigns(params = {}) {
    const response = await api.get('/campaigns', { params })
    return response.data
  },

  async getCampaignDetails(slug) {
    const response = await api.get(`/campaign/details/${slug}`)
    return response.data
  }
}
