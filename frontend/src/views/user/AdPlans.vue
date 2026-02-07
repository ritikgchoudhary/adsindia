<template>
  <DashboardLayout page-title="Ad Plans">
    <div class="row">
      <div v-for="plan in adPlans" :key="plan?.id || Math.random()" class="col-lg-4 col-md-6 mb-4" v-if="plan && plan.id">
        <div class="card custom--card">
          <div class="card-body text-center">
            <h4 class="mb-3">{{ plan.name }}</h4>
            <h2 class="text-primary mb-3">{{ currencySymbol }}{{ formatAmount(plan.price) }}</h2>
            <ul class="list-unstyled mb-4 text-start">
              <li class="mb-2"><i class="fas fa-check text-success me-2"></i>{{ plan.ads_count }} Ads</li>
              <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Valid for {{ plan.validity_days }} days</li>
              <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Total Earning: {{ currencySymbol }}{{ formatAmount(plan.total_earning) }}</li>
            </ul>
            <button class="btn btn--base w-100" @click="purchasePlan(plan)">
              <i class="fas fa-shopping-cart me-2"></i>Buy Plan
            </button>
          </div>
        </div>
      </div>
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
      
      if (!confirm(`Are you sure you want to purchase ${plan.name} for ${currencySymbol.value}${formatAmount(plan.price)}?`)) {
        return
      }

      try {
        const response = await api.post('/ad-plans/purchase', { plan_id: plan.id })
        if (response.data.status === 'success') {
          if (window.notify) {
            window.notify('success', 'Ad plan purchased successfully!')
          }
          fetchAdPlans()
        }
      } catch (error) {
        console.error('Error purchasing ad plan:', error)
        if (window.notify) {
          window.notify('error', error.response?.data?.message || 'Failed to purchase ad plan')
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
