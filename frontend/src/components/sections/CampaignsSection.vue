<template>
  <div class="campaigns-section my-120" v-if="campaigns.length">
    <div class="container">
      <div class="row gy-4">
        <div class="col-lg-4 col-md-6" v-for="campaign in campaigns" :key="campaign.id">
          <div class="campaign-card">
            <!-- Campaign card content will be loaded from API -->
            <router-link :to="`/campaign/${campaign.slug}`">
              <h4>{{ campaign.title }}</h4>
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { campaignService } from '../../services/campaignService'

export default {
  name: 'CampaignsSection',
  setup() {
    const campaigns = ref([])

    onMounted(async () => {
      try {
        const response = await campaignService.getCampaigns()
        campaigns.value = response.data?.slice(0, 6) || [] // Show first 6
      } catch (error) {
        console.error('Error loading campaigns:', error)
      }
    })

    return {
      campaigns
    }
  }
}
</script>
