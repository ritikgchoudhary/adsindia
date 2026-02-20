<template>
  <div class="campaign-section my-120">
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
        <span class="section-heading__subtitle">{{ campaignContent?.subtitle ?? '' }}</span>
        <h3 class="section-heading__title">{{ campaignContent?.heading ?? '' }}</h3>
        <p class="section-heading__desc">{{ campaignContent?.description ?? '' }}</p>
      </div>

      <div class="row gy-4 justify-content-center">
        <template v-if="campaigns.length">
          <div
            v-for="(campaign, index) in campaigns"
            :key="campaign.id"
            class="col-xl-3 col-lg-4 col-sm-6"
          >
            <CampaignCard :campaign="campaign" :iteration="index + 1" />
          </div>
        </template>
        <template v-else>
          <div class="col-xl-3 col-lg-4 col-sm-6">
            <div class="text-center">
              <i class="las la-2x la-clipboard-list"></i>
              <br>
              No campaigns found yet!
            </div>
          </div>
        </template>
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
        campaignContent.value = appService.getSectionContent(contentRes)
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
