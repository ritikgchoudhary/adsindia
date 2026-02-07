<template>
  <DashboardLayout page-title="Partner Program">
    <div class="row">
      <div class="col-12 mb-4">
        <div class="card custom--card">
          <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-handshake me-2"></i>Partner Program Plans</h5>
          </div>
          <div class="card-body">
            <p class="text-muted">Join our Partner Program to earn extra commission and downline earning benefits.</p>
          </div>
        </div>
      </div>

      <div v-for="plan in partnerPlans" :key="plan?.id || Math.random()" class="col-lg-4 col-md-6 mb-4" v-if="plan && plan.id">
        <div class="card custom--card" :class="{ 'border-primary': plan.is_recommended }">
          <div v-if="plan.is_recommended" class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-star me-2"></i>Recommended</h5>
          </div>
          <div class="card-body text-center">
            <h4 class="mb-3">{{ plan.name }}</h4>
            <h2 class="text-primary mb-4">{{ currencySymbol }}{{ formatAmount(plan.price) }}</h2>
            <ul class="list-unstyled mb-4 text-start">
              <li class="mb-2"><i class="fas fa-check text-success me-2"></i>{{ plan.description }}</li>
              <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Per Referral: {{ currencySymbol }}{{ formatAmount(plan.referral_commission) }}</li>
              <li v-if="plan.downline_commission > 0" class="mb-2"><i class="fas fa-check text-success me-2"></i>Downline Earning: {{ plan.downline_commission }}%</li>
            </ul>
            <button class="btn btn--base w-100" @click="joinPartnerProgram(plan)" :disabled="plan.id === currentPartnerPlanId">
              <span v-if="plan.id === currentPartnerPlanId">Current Plan</span>
              <span v-else>Join Now</span>
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
  name: 'PartnerProgram',
  components: {
    DashboardLayout
  },
  setup() {
    const partnerPlans = ref([])
    const currentPartnerPlanId = ref(null)
    const currencySymbol = ref('₹')

    const formatAmount = (amount) => {
      if (!amount && amount !== 0) return '0.00'
      return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }

    const joinPartnerProgram = async (plan) => {
      if (!plan || !plan.id || plan.id === currentPartnerPlanId.value) return

      if (!confirm(`Are you sure you want to join ${plan.name} for ${currencySymbol.value}${formatAmount(plan.price)}?`)) {
        return
      }

      try {
        const response = await api.post('/partner-program/join', { plan_id: plan.id })
        if (response.data.status === 'success') {
          if (window.notify) {
            window.notify('success', 'Partner program joined successfully!')
          }
          fetchCurrentPlan()
        }
      } catch (error) {
        console.error('Error joining partner program:', error)
        if (window.notify) {
          window.notify('error', error.response?.data?.message || 'Failed to join partner program')
        }
      }
    }

    const fetchPartnerPlans = async () => {
      try {
        const response = await api.get('/partner-program/plans')
        if (response.data.status === 'success') {
          partnerPlans.value = response.data.data || []
          currencySymbol.value = response.data.currency_symbol || '₹'
        }
      } catch (error) {
        console.error('Error loading partner plans:', error)
      }
    }

    const fetchCurrentPlan = async () => {
      try {
        const response = await api.get('/partner-program/current')
        if (response.data.status === 'success' && response.data.data) {
          currentPartnerPlanId.value = response.data.data.plan_id
        }
      } catch (error) {
        console.error('Error loading current partner plan:', error)
      }
    }

    onMounted(() => {
      fetchPartnerPlans()
      fetchCurrentPlan()
    })

    return {
      partnerPlans,
      currentPartnerPlanId,
      currencySymbol,
      formatAmount,
      joinPartnerProgram
    }
  }
}
</script>
