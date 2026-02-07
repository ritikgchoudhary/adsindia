<template>
  <div class="campaign-section my-120" v-if="campaigns.length">
    <div class="container">
      <div class="section-heading">
        <div class="left-thumb-wrapper">
          <div class="left-thumb"></div>
          <div class="border-shape"></div>
        </div>
        <div class="right-thumb-wrapper">
          <div class="right-thumb"></div>
          <div class="border-shape"></div>
        </div>
        <span class="section-heading__subtitle" v-if="campaignContent">{{ campaignContent.subtitle }}</span>
        <h3 class="section-heading__title" v-if="campaignContent">{{ campaignContent.heading }}</h3>
        <p class="section-heading__desc" v-if="campaignContent">{{ campaignContent.description }}</p>
      </div>

      <div class="row gy-4 justify-content-center">
        <div 
          v-for="(campaign, index) in campaigns" 
          :key="campaign.id" 
          class="col-xl-3 col-lg-4 col-sm-6"
        >
          <CampaignCard :campaign="campaign" :iteration="index + 1" />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { campaignService } from '../../services/campaignService'
import { appService } from '../../services/appService'
import CampaignCard from '../CampaignCard.vue'

export default {
  name: 'CampaignsSection',
  components: {
    CampaignCard
  },
  setup() {
    const campaigns = ref([])
    const campaignContent = ref(null)

    onMounted(async () => {
      try {
        const [contentRes, campaignsRes] = await Promise.all([
          appService.getSections('campaigns'),
          campaignService.getCampaigns()
        ])
        campaignContent.value = contentRes.data?.content?.data_values || null
        campaigns.value = campaignsRes.data?.slice(0, 8) || []
      } catch (error) {
        console.error('Error loading campaigns:', error)
      }
    })

    return {
      campaigns,
      campaignContent
    }
  }
}
</script>
