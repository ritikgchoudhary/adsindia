<template>
  <div class="campaign-section mb-120 mt-60">
    <div class="container">
      <div class="row gy-4 align-items-start justify-content-center campaign-list">
        <div class="col-xl-9 col-lg-8">
          <div class="loader-wrapper" :class="{ 'd-none': !loading }">
            <div class="loader"></div>
          </div>
          <div class="row gy-4 justify-content-center" id="campaigns">
            <template v-for="campaign in campaigns" :key="campaign?.id || Math.random()">
              <div v-if="campaign && campaign.id" class="col-lg-6 col-md-6">
              <div class="campaign-card">
                <router-link :to="`/campaign/${campaign.slug}`">
                  <h4>{{ campaign.title }}</h4>
                  <p>{{ campaign.description }}</p>
                </router-link>
              </div>
              </div>
            </template>
          </div>
        </div>
        <div class="col-xl-3 col-lg-4">
          <div class="campaign-sidebar">
            <div class="sidebar-item">
              <h5 class="sidebar-item__title">Searching for</h5>
              <form @submit.prevent="fetchCampaigns" class="search-form">
                <input v-model="filters.search" type="text" name="search" class="form--control" placeholder="Search..">
                <button class="search-form__icon" type="button" @click="fetchCampaigns">
                  <i class="las la-search"></i>
                </button>
              </form>
            </div>
            <!-- Add more sidebar filters as needed -->
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { campaignService } from '../services/campaignService'

export default {
  name: 'Campaigns',
  setup() {
    const campaigns = ref([])
    const loading = ref(false)
    const filters = ref({
      search: '',
      category: [],
      traffic_type: [],
      date: 'All',
      sort: 'All'
    })

    const fetchCampaigns = async () => {
      loading.value = true
      try {
        const response = await campaignService.filterCampaigns(filters.value)
        campaigns.value = response.data || []
      } catch (error) {
        console.error('Error fetching campaigns:', error)
      } finally {
        loading.value = false
      }
    }

    onMounted(() => {
      fetchCampaigns()
    })

    return {
      campaigns,
      loading,
      filters,
      fetchCampaigns
    }
  }
}
</script>

<style>
body:not(:has(.d-none.loader-wrapper)) {
  overflow: hidden;
}
</style>
