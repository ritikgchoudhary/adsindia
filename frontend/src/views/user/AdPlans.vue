<template>
  <DashboardLayout page-title="Ad Plans">
    <div class="row mb-4">
      <div class="col-12">
        <div class="alert alert-info" role="alert">
          <h5 class="alert-heading"><i class="fas fa-info-circle me-2"></i>How Ad Plans Work</h5>
          <p class="mb-0">Purchase an ad plan to unlock the ability to watch ads and earn money. After purchase, you can watch {{ currencySymbol }}5,000 - {{ currencySymbol }}6,000 per ad. Each ad takes 30 minutes to watch completely.</p>
        </div>
      </div>
    </div>
    <div class="row">
      <template v-for="plan in adPlans" :key="plan?.id || Math.random()">
        <div v-if="plan && plan.id" class="col-lg-3 col-md-6 mb-4">
          <div class="card custom--card h-100" :class="{ 'border-warning': plan.is_recommended }" style="border-radius: 15px; transition: all 0.3s ease;" @mouseenter="$event.currentTarget.style.transform = 'translateY(-5px)'" @mouseleave="$event.currentTarget.style.transform = 'translateY(0)'">
            <div v-if="plan.is_recommended" class="card-header bg-warning text-dark text-center" style="border-radius: 15px 15px 0 0;">
              <strong><i class="fas fa-star me-2"></i>Recommended</strong>
            </div>
            <div class="card-body text-center">
              <h4 class="mb-3" style="font-weight: 600;">{{ plan.name }}</h4>
              <h2 class="text-primary mb-3" style="font-weight: 700;">{{ currencySymbol }}{{ formatAmount(plan.price) }}</h2>
              <ul class="list-unstyled mb-4 text-start">
                <li class="mb-2"><i class="fas fa-check text-success me-2"></i><strong>{{ plan.ads_count }}</strong> Ads Available</li>
                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Valid for <strong>{{ plan.validity_days }} days</strong></li>
                <li class="mb-2"><i class="fas fa-check text-success me-2"></i><strong>{{ plan.daily_ad_limit }}</strong> ads per day</li>
                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Earning per ad: <strong>{{ currencySymbol }}{{ formatAmount(plan.reward_per_ad) }}</strong></li>
                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Total Potential: <strong>{{ currencySymbol }}{{ formatAmount(plan.total_earning) }}</strong></li>
              </ul>
              <button class="btn btn--base w-100 btn-lg" @click="purchasePlan(plan)" style="border-radius: 10px; font-weight: 600;">
                <i class="fas fa-shopping-cart me-2"></i>Buy Now
              </button>
            </div>
          </div>
        </div>
      </template>
    </div>
  </DashboardLayout>
</template>

<script>
import { ref, onMounted } from 'vue'
import DashboardLayout from '../../components/DashboardLayout.vue'
import api from '../../services/api'

export default {
  name: 'AdPlans',
  components: {
    DashboardLayout
  },
  setup() {
    const adPlans = ref([])
    const currencySymbol = ref('₹')

    const formatAmount = (amount) => {
      if (!amount && amount !== 0) return '0.00'
      return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }

    const purchasePlan = async (plan) => {
      if (!plan || !plan.id) return
      
      if (!confirm(`Are you sure you want to purchase ${plan.name} for ${currencySymbol.value}${formatAmount(plan.price)}?\n\nAfter purchase, you can watch ads and earn ${currencySymbol.value}${formatAmount(plan.reward_per_ad)} per ad!`)) {
        return
      }

      try {
        const response = await api.post('/ad-plans/purchase', { plan_id: plan.id })
        if (response.data.status === 'success') {
          if (window.notify) {
            window.notify('success', `Ad plan purchased successfully! You can now watch ads and earn ${currencySymbol.value}${formatAmount(plan.reward_per_ad)} per ad.`)
          }
          // Redirect to ads work page
          setTimeout(() => {
            window.location.href = '/user/ads-work'
          }, 1500)
        }
      } catch (error) {
        console.error('Error purchasing ad plan:', error)
        if (window.notify) {
          window.notify('error', error.response?.data?.message?.error?.[0] || error.response?.data?.message || 'Failed to purchase ad plan')
        }
      }
    }

    const fetchAdPlans = async () => {
      try {
        const response = await api.get('/ad-plans')
        if (response.data.status === 'success') {
          adPlans.value = response.data.data || []
          currencySymbol.value = response.data.currency_symbol || '₹'
        }
      } catch (error) {
        console.error('Error loading ad plans:', error)
      }
    }

    onMounted(() => {
      fetchAdPlans()
    })

    return {
      adPlans,
      currencySymbol,
      formatAmount,
      purchasePlan
    }
  }
}
</script>
