<template>
  <div class="campaign-details">
    <div class="container">
      <div v-if="loading">Loading...</div>
      <div v-else-if="campaign">
        <h1>{{ campaign.title }}</h1>
        <p>{{ campaign.description }}</p>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { campaignService } from '../services/campaignService'

export default {
  name: 'CampaignDetails',
  setup() {
    const route = useRoute()
    const campaign = ref(null)
    const loading = ref(true)

    const fetchCampaign = async () => {
      try {
        const response = await campaignService.getCampaignDetails(route.params.slug)
        campaign.value = response.data
      } catch (error) {
        console.error('Error fetching campaign:', error)
      } finally {
        loading.value = false
      }
    }

    onMounted(() => {
      fetchCampaign()
    })

    return {
      campaign,
      loading
    }
  }
}
</script>

<style scoped>
.campaign-details {
  min-height: 100vh;
  padding: 2rem;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
}
</style>
