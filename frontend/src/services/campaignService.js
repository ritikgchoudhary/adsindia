import api from './api'

export const campaignService = {
  async getCampaigns(categoryId = null) {
    const params = categoryId ? { category_id: categoryId } : {}
    const response = await api.get('/campaigns', { params })
    return response.data
  },

  async filterCampaigns(filters) {
    const response = await api.get('/campaign/filter', { params: filters })
    return response.data
  },

  async getCampaignDetails(slug) {
    const response = await api.get(`/campaign/details/${slug}`)
    return response.data
  }
}
