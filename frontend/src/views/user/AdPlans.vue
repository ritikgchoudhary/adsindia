<template>
  <DashboardLayout page-title="Ad Plans">
    <div class="row mb-4">
      <div class="col-12">
        <div class="alert alert-info" role="alert" style="background: #e0f2fe; border-color: #0ea5e9; color: #0c4a6e;">
          <h5 class="alert-heading" style="color: #075985; font-weight: 600;"><i class="fas fa-info-circle me-2"></i>How Ad Plans Work</h5>
          <p class="mb-0" style="color: #0c4a6e;">Purchase an ad plan to unlock the ability to watch ads and earn money. After purchase, you can watch {{ currencySymbol }}5,000 - {{ currencySymbol }}6,000 per ad. Each ad takes 30 minutes to watch completely.</p>
        </div>
      </div>
    </div>
    <div class="row">
      <template v-for="plan in adPlans" :key="plan?.id || Math.random()">
        <div v-if="plan && plan.id" class="col-lg-3 col-md-6 mb-4">
          <div class="card custom--card h-100" :class="{ 'border-warning': plan.is_recommended }" style="border-radius: 15px; transition: all 0.3s ease; background: #fff; border: 1px solid #e2e8f0;" @mouseenter="$event.currentTarget.style.transform = 'translateY(-5px)'; $event.currentTarget.style.boxShadow = '0 10px 25px rgba(0,0,0,0.1)'" @mouseleave="$event.currentTarget.style.transform = 'translateY(0)'; $event.currentTarget.style.boxShadow = '0 2px 10px rgba(0,0,0,0.05)'">
            <div v-if="plan.is_recommended" class="card-header bg-warning text-dark text-center" style="border-radius: 15px 15px 0 0;">
              <strong><i class="fas fa-star me-2"></i>Recommended</strong>
            </div>
            <div class="card-body text-center" style="background: #fff; color: #2d3748;">
              <h4 class="mb-3" style="font-weight: 600; color: #2d3748;">{{ plan.name }}</h4>
              <h2 class="mb-3" style="font-weight: 700; color: #667eea;">{{ currencySymbol }}{{ formatAmount(plan.price) }}</h2>
              <ul class="list-unstyled mb-4 text-start" style="color: #4a5568;">
                <li class="mb-2" style="color: #4a5568;"><i class="fas fa-check text-success me-2"></i><strong style="color: #2d3748;">{{ plan.ads_count }}</strong> <span style="color: #4a5568;">Ads Available</span></li>
                <li class="mb-2" style="color: #4a5568;"><i class="fas fa-check text-success me-2"></i>Valid for <strong style="color: #2d3748;">{{ plan.validity_days }} days</strong></li>
                <li class="mb-2" style="color: #4a5568;"><i class="fas fa-check text-success me-2"></i><strong style="color: #2d3748;">{{ plan.daily_ad_limit }}</strong> <span style="color: #4a5568;">ads per day</span></li>
                <li class="mb-2" style="color: #4a5568;"><i class="fas fa-check text-success me-2"></i>Earning per ad: <strong style="color: #2d3748;">{{ currencySymbol }}{{ formatAmount(plan.reward_per_ad) }}</strong></li>
                <li class="mb-2" style="color: #4a5568;"><i class="fas fa-check text-success me-2"></i>Total Potential: <strong style="color: #2d3748;">{{ currencySymbol }}{{ formatAmount(plan.total_earning) }}</strong></li>
              </ul>
              <button class="btn btn--base w-100 btn-lg" @click="purchasePlan(plan)" style="border-radius: 10px; font-weight: 600; color: #fff;">
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
      if (!plan || !plan.id) {
        console.error('Invalid plan:', plan)
        return
      }
      
      const confirmMessage = `Are you sure you want to purchase ${plan.name} for ${currencySymbol.value}${formatAmount(plan.price)}?\n\nAfter purchase, you can watch ads and earn ${currencySymbol.value}${formatAmount(plan.reward_per_ad)} per ad!`
      
      if (!confirm(confirmMessage)) {
        return
      }

      console.log('Purchasing plan:', plan)
      
      try {
        const response = await api.post('/ad-plans/purchase', { plan_id: plan.id })
        console.log('Purchase response:', response.data)
        
        if (response.data.status === 'success') {
          const successMsg = `Ad plan purchased successfully! You can now watch ads and earn ${currencySymbol.value}${formatAmount(plan.reward_per_ad)} per ad.`
          
          if (window.notify) {
            window.notify('success', successMsg)
          }
          
          console.log('Purchase successful, redirecting to ads-work...')
          
          // Redirect to ads work page
          setTimeout(() => {
            window.location.href = '/user/ads-work'
          }, 1500)
        } else {
          console.error('Purchase failed:', response.data)
          const errorMsg = response.data.message?.error?.[0] || response.data.message?.success?.[0] || 'Failed to purchase ad plan'
          if (window.notify) {
            window.notify('error', errorMsg)
          }
        }
      } catch (error) {
        console.error('Error purchasing ad plan:', error)
        console.error('Error response:', error.response?.data)
        
        let errorMsg = 'Failed to purchase ad plan'
        if (error.response?.data) {
          if (error.response.data.message?.error) {
            errorMsg = Array.isArray(error.response.data.message.error) 
              ? error.response.data.message.error[0] 
              : error.response.data.message.error
          } else if (error.response.data.message) {
            errorMsg = error.response.data.message
          }
        }
        
        if (window.notify) {
          window.notify('error', errorMsg)
        }
      }
    }

    const fetchAdPlans = async () => {
      try {
        const response = await api.get('/ad-plans')
        console.log('Ad Plans API Response:', response.data)
        
        if (response.data.status === 'success') {
          // Response structure: { status: 'success', data: { data: [...], currency_symbol: '₹' } }
          const responseData = response.data.data || {}
          const plans = responseData.data || []
          
          adPlans.value = Array.isArray(plans) ? plans : []
          currencySymbol.value = responseData.currency_symbol || '₹'
          
          console.log('Loaded ad plans:', adPlans.value)
          console.log('Currency symbol:', currencySymbol.value)
          
          if (adPlans.value.length === 0) {
            console.warn('No ad plans found in response')
            if (window.notify) {
              window.notify('warning', 'No ad plans available at the moment')
            }
          }
        } else {
          console.error('API returned error:', response.data)
          const errorMsg = response.data.message?.error?.[0] || response.data.message?.success?.[0] || 'Failed to load ad plans'
          if (window.notify) {
            window.notify('error', errorMsg)
          }
        }
      } catch (error) {
        console.error('Error loading ad plans:', error)
        console.error('Error response:', error.response?.data)
        const errorMsg = error.response?.data?.message?.error?.[0] || error.response?.data?.message || 'Failed to load ad plans. Please try again.'
        if (window.notify) {
          window.notify('error', errorMsg)
        }
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
