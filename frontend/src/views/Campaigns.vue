<template>
  <div class="campaign-section mb-120 mt-60">
    <div class="container">
      <div class="row gy-4 align-items-start justify-content-center campaign-list">
        <div class="col-xl-9 col-lg-8">
          <div class="loader-wrapper" :class="{ 'd-none': !loading }">
            <div class="loader"></div>
          </div>
          <div class="row gy-4 justify-content-center" id="campaigns">
            <template v-for="(campaign, index) in campaigns" :key="campaign?.id || index">
              <div v-if="campaign && campaign.id" class="col-xl-4 col-sm-6">
                <CampaignCard :campaign="campaign" :iteration="index + 1" />
              </div>
            </template>
            <div v-if="!loading && campaigns.length === 0" class="col-12 text-center mt-4">
              <p class="text-muted">No campaigns found.</p>
            </div>
          </div>
          <nav v-if="pagination.last_page > 1" aria-label="Page navigation example" class="mt-5">
            <ul class="pagination justify-content-center flex-wrap">
              <li class="page-item" :class="{ disabled: pagination.current_page <= 1 }">
                <a class="page-link" href="javascript:void(0)" @click.prevent="goToPage(pagination.current_page - 1)">&laquo;</a>
              </li>
              <li v-for="p in pagination.last_page" :key="p" class="page-item" :class="{ active: p === pagination.current_page }">
                <a class="page-link" href="javascript:void(0)" @click.prevent="goToPage(p)">{{ p }}</a>
              </li>
              <li class="page-item" :class="{ disabled: pagination.current_page >= pagination.last_page }">
                <a class="page-link" href="javascript:void(0)" @click.prevent="goToPage(pagination.current_page + 1)">&raquo;</a>
              </li>
            </ul>
          </nav>
        </div>
        <div class="col-xl-3 col-lg-4">
          <div class="campaign-sidebar">
            <div class="sidebar-item">
              <h5 class="sidebar-item__title">Searching for</h5>
              <form @submit.prevent="applyFilters" class="search-form">
                <input v-model="filters.search" type="text" name="search" class="form--control" placeholder="Search..">
                <button class="search-form__icon" type="button" @click="applyFilters">
                  <i class="las la-search"></i>
                </button>
              </form>
            </div>
            <div class="sidebar-item">
              <h5 class="sidebar-item__title">Categories</h5>
              <div class="sidebar-item__content">
                <div class="sidebar-item__text">
                  <div class="form--check">
                    <input class="form-check-input sortCategory" type="checkbox" id="category-all" :checked="isAllCategories" @change="toggleAllCategories">
                    <label class="form-check-label" for="category-all">All ({{ categoriesTotal }})</label>
                  </div>
                </div>
                <template v-for="cat in categories" :key="cat.id">
                  <div class="sidebar-item__text">
                    <div class="form--check">
                      <input class="form-check-input sortCategory" type="checkbox" :value="cat.id" :id="'category-' + cat.id" v-model="filters.category" @change="applyFilters">
                      <label class="form-check-label" :for="'category-' + cat.id">{{ cat.name }} ({{ cat.campaigns_count ?? 0 }})</label>
                    </div>
                  </div>
                </template>
                <button type="button" class="load-more-button">Load more</button>
              </div>
            </div>
            <div class="sidebar-item">
              <h5 class="sidebar-item__title">Traffic Type</h5>
              <div class="sidebar-item__content">
                <div class="sidebar-item__text">
                  <div class="form--check">
                    <input class="form-check-input sortTraffic" type="checkbox" id="traffic-all" :checked="isAllTraffic" @change="toggleAllTraffic">
                    <label class="form-check-label" for="traffic-all">All</label>
                  </div>
                </div>
                <template v-for="(traffic, tIdx) in trafficTypes" :key="traffic.id">
                  <div class="sidebar-item__text">
                    <div class="form--check">
                      <input class="form-check-input sortTraffic" type="checkbox" :value="traffic.id" :id="'traffic-' + tIdx" v-model="filters.traffic_type" @change="applyFilters">
                      <label class="form-check-label" :for="'traffic-' + tIdx">{{ traffic.name }}</label>
                    </div>
                  </div>
                </template>
                <button type="button" class="load-more-button">Load more</button>
              </div>
            </div>
            <div class="sidebar-item">
              <h5 class="sidebar-item__title">Date Range</h5>
              <div class="sidebar-item__content">
                <div class="sidebar-item__text">
                  <div class="form--radio">
                    <input class="form-check-input sortDate" type="radio" name="date" id="date-all" value="All" v-model="filters.date" @change="applyFilters">
                    <label class="form-check-label" for="date-all">All</label>
                  </div>
                </div>
                <template v-for="(dateOpt, dIdx) in dateOptions" :key="dateOpt">
                  <div class="sidebar-item__text">
                    <div class="form--radio">
                      <input class="form-check-input sortDate" type="radio" name="date" :value="dateOpt" :id="'date-' + dIdx" v-model="filters.date" @change="applyFilters">
                      <label class="form-check-label" :for="'date-' + dIdx">{{ dateOpt }}</label>
                    </div>
                  </div>
                </template>
                <button type="button" class="load-more-button">Load more</button>
              </div>
            </div>
            <div class="sidebar-item">
              <h5 class="sidebar-item__title">Sorting</h5>
              <div class="sidebar-item__content">
                <div class="sidebar-item__text">
                  <div class="form--radio">
                    <input class="form-check-input sortCampaign" type="radio" name="sort" id="sort-all" value="All" v-model="filters.sort" @change="applyFilters">
                    <label class="form-check-label" for="sort-all">All</label>
                  </div>
                </div>
                <template v-for="(sortOpt, sIdx) in sortOptions" :key="sortOpt">
                  <div class="sidebar-item__text">
                    <div class="form--radio">
                      <input class="form-check-input sortCampaign" type="radio" name="sort" :value="sortOpt" :id="'sort-' + sIdx" v-model="filters.sort" @change="applyFilters">
                      <label class="form-check-label" :for="'sort-' + sIdx">{{ sortOpt }}</label>
                    </div>
                  </div>
                </template>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import api from '../services/api'
import { campaignService } from '../services/campaignService'
import CampaignCard from '../components/CampaignCard.vue'

const dateOptions = ['Today', 'Yesterday', 'Last 7 Days', 'Last 30 Days']
const sortOptions = ['Most Recent', 'Highest Budget', 'Lowest Budget', 'Most conversions']

export default {
  name: 'Campaigns',
  components: { CampaignCard },
  setup() {
    const campaigns = ref([])
    const loading = ref(false)
    const categories = ref([])
    const trafficTypes = ref([])
    const pagination = ref({ current_page: 1, last_page: 1, per_page: 12, total: 0 })
    const filters = ref({
      search: '',
      category: [],
      traffic_type: [],
      date: 'All',
      sort: 'All'
    })

    const categoriesTotal = computed(() => categories.value.reduce((s, c) => s + (c.campaigns_count || 0), 0))
    const isAllCategories = computed(() => filters.value.category.length === 0)
    const isAllTraffic = computed(() => filters.value.traffic_type.length === 0)

    function toggleAllCategories() {
      filters.value.category = []
      applyFilters()
    }
    function toggleAllTraffic() {
      filters.value.traffic_type = []
      applyFilters()
    }

    async function loadCategories() {
      try {
        const res = await api.get('/categories')
        const data = res.data?.data ?? res.data
        categories.value = Array.isArray(data) ? data : (data ? [data] : [])
      } catch (_) {
        categories.value = []
      }
    }
    async function loadTrafficTypes() {
      try {
        const res = await campaignService.getTrafficTypes()
        const data = res.data ?? res.traffic_types ?? res
        trafficTypes.value = Array.isArray(data) ? data : (data?.data ?? [])
      } catch (_) {
        trafficTypes.value = []
      }
    }

    async function fetchCampaigns() {
      loading.value = true
      try {
        const params = {
          search: filters.value.search || undefined,
          date: filters.value.date !== 'All' ? filters.value.date : undefined,
          sort: filters.value.sort !== 'All' ? filters.value.sort : undefined,
          page: pagination.value.current_page
        }
        if (filters.value.category.length) params.category = filters.value.category
        if (filters.value.traffic_type.length) params.traffic_type = filters.value.traffic_type
        const response = await campaignService.getCampaigns(params)
        const payload = response.data ?? response
        if (payload && typeof payload.data !== 'undefined') {
          campaigns.value = payload.data || []
          pagination.value = {
            current_page: payload.current_page ?? 1,
            last_page: payload.last_page ?? 1,
            per_page: payload.per_page ?? 12,
            total: payload.total ?? 0
          }
        } else {
          campaigns.value = Array.isArray(payload) ? payload : (payload?.campaigns ?? [])
          pagination.value = { current_page: 1, last_page: 1, per_page: 12, total: campaigns.value.length }
        }
      } catch (error) {
        console.error('Error fetching campaigns:', error)
        campaigns.value = []
      } finally {
        loading.value = false
      }
    }

    function applyFilters() {
      pagination.value.current_page = 1
      fetchCampaigns()
    }
    function goToPage(page) {
      if (page < 1 || page > pagination.value.last_page) return
      pagination.value.current_page = page
      fetchCampaigns()
    }

    onMounted(async () => {
      await Promise.all([loadCategories(), loadTrafficTypes()])
      fetchCampaigns()
    })

    return {
      campaigns,
      loading,
      filters,
      categories,
      trafficTypes,
      pagination,
      dateOptions,
      sortOptions,
      categoriesTotal,
      isAllCategories,
      isAllTraffic,
      toggleAllCategories,
      toggleAllTraffic,
      applyFilters,
      goToPage
    }
  }
}
</script>

<style scoped>
.campaign-section {
  padding: 60px 0;
  min-height: 100vh;
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
  background-attachment: fixed;
}

@media (max-width: 991px) {
  .campaign-section { padding: 40px 0; margin-top: 0 !important; }
  .campaign-sidebar { margin-top: 40px; }
}

@media (max-width: 767px) {
  .campaign-section { padding: 30px 0; }
  .sidebar-item__title { font-size: 1rem !important; margin-bottom: 12px !important; }
  .form--control { font-size: 0.85rem !important; padding: 0.65rem 1rem !important; }
  .form-check-label { font-size: 0.8rem !important; }
  .page-link { padding: 0.5rem 0.85rem !important; font-size: 0.75rem !important; }
  
  .loader-wrapper { padding: 40px 0 !important; }
}
</style>
