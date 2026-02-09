<template>
  <DashboardLayout page-title="Courses Packages">
    <!-- Current Course Plan -->
    <div v-if="currentPlan" class="row mb-4">
      <div class="col-12">
        <div class="card custom--card" style="border: 3px solid #0d6efd; border-radius: 12px; box-shadow: 0 4px 12px rgba(13, 110, 253, 0.2);">
          <div class="card-header" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); color: #fff; padding: 16px 24px; border-radius: 12px 12px 0 0;">
            <h5 class="mb-0 fw-bold"><i class="fas fa-graduation-cap me-2"></i>Your Current Course Plan</h5>
          </div>
          <div class="card-body" style="background: linear-gradient(to bottom, #e7f1ff 0%, #fff 100%); padding: 24px;">
            <div class="d-flex align-items-center mb-3">
              <div class="me-4">
                <i class="fas fa-book-open fa-3x" style="color: #0d6efd;"></i>
              </div>
              <div>
                <h4 class="mb-1 fw-bold" style="color: #0d6efd;">{{ currentPlan.name }}</h4>
                <p class="mb-0 text-muted" v-if="currentPlan.expires_at">Valid until: {{ formatDate(currentPlan.expires_at) }}</p>
                <p class="mb-0 text-muted" v-else>Lifetime access</p>
              </div>
            </div>
            <p class="mb-0"><strong>Courses unlocked:</strong> {{ currentPlan.courses_count }} courses. Complete them to earn certificates.</p>
            <router-link to="/user/courses" class="btn btn-primary mt-3">
              <i class="fas fa-play me-2"></i>View Courses
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <!-- Course Plans (Learning) -->
    <div class="row">
      <div class="col-12 mb-3">
        <h4><i class="fas fa-graduation-cap me-2"></i>Courses Packages</h4>
        <p class="text-muted mb-0">Buy a package to access courses. Complete courses to learn and get certificates.</p>
      </div>
      <div v-if="isLoading" class="col-12">
        <div class="alert alert-info text-center">
          <i class="fas fa-spinner fa-spin me-2"></i>Loading courses packages...
        </div>
      </div>
      <div v-else-if="plans.length === 0" class="col-12">
        <div class="alert alert-warning text-center">
          <i class="fas fa-exclamation-triangle me-2"></i>No courses packages available. Please contact support.
        </div>
      </div>
      <template v-for="plan in plans" :key="plan?.id">
        <div v-if="plan && plan.id" class="col-lg-4 col-md-6 mb-4">
          <div class="card custom--card" :class="{ 'border-primary': currentPlan && plan.id === currentPlan.plan_id }" style="border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); min-height: 100%;">
            <div v-if="currentPlan && plan.id === currentPlan.plan_id" class="card-header bg-primary text-white" style="border-radius: 12px 12px 0 0;">
              <h5 class="mb-0 fw-bold"><i class="fas fa-check-circle me-2"></i>Current Plan</h5>
            </div>
            <div class="card-body">
              <h4 class="text-center mb-3 fw-bold">{{ plan.name }}</h4>
              <div class="text-center mb-3">
                <h2 class="mb-2 fw-bold text-primary" style="font-size: 2rem;">{{ currencySymbol }}{{ formatAmount(plan.price) }}</h2>
                <p class="mb-0 text-muted">{{ plan.validity_days }} days access</p>
              </div>
              <p v-if="plan.description" class="text-muted small mb-3">{{ plan.description }}</p>
              <ul class="list-unstyled mb-4">
                <li class="mb-2"><i class="fas fa-check text-success me-2"></i><strong>{{ plan.courses_count }}</strong> courses included</li>
                <li class="mb-2"><i class="fas fa-certificate text-success me-2"></i>Certificate on each course completion</li>
              </ul>
              <button
                class="btn w-100 fw-bold"
                :class="currentPlan && plan.id === currentPlan.plan_id ? 'btn-secondary' : 'btn-primary'"
                :disabled="currentPlan && plan.id === currentPlan.plan_id"
                @click="purchasePlan(plan)"
                style="border-radius: 8px; padding: 12px;">
                <span v-if="currentPlan && plan.id === currentPlan.plan_id">
                  <i class="fas fa-check-circle me-2"></i>Current Package
                </span>
                <span v-else>
                  <i class="fas fa-shopping-cart me-2"></i>Buy Package – {{ currencySymbol }}{{ formatAmount(plan.price) }}
                </span>
              </button>
            </div>
          </div>
        </div>
      </template>
    </div>

    <div v-if="!currentPlan && plans.length > 0" class="row mt-3">
      <div class="col-12">
        <div class="alert alert-info">
          <i class="fas fa-info-circle me-2"></i>
          <strong>Step:</strong> Choose a courses package → Access courses → Complete courses → Get certificates.
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
  name: 'Packages',
  components: {
    DashboardLayout
  },
  setup() {
    const plans = ref([])
    const currentPlan = ref(null)
    const currencySymbol = ref('₹')
    const isLoading = ref(true)

    const formatAmount = (amount) => {
      if (!amount && amount !== 0) return '0.00'
      return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }

    const formatDate = (dateString) => {
      if (!dateString) return '-'
      return new Date(dateString).toLocaleDateString('en-IN', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      })
    }

    const purchasePlan = async (plan) => {
      if (!plan || !plan.id || (currentPlan.value && plan.id === currentPlan.value.plan_id)) return
      if (!confirm(`Buy "${plan.name}" for ${currencySymbol.value}${formatAmount(plan.price)}? Amount will be deducted from your balance.`)) return

      try {
        const response = await api.post('/course-plans/purchase', { plan_id: plan.id })
        if (response.data.status === 'success') {
          if (window.notify) {
            window.notify('success', 'Courses package purchased! You can now access courses.')
          }
          fetchPlans()
        }
      } catch (error) {
        const msg = error.response?.data?.message?.error?.[0] || error.response?.data?.message || 'Purchase failed'
        if (window.notify) window.notify('error', Array.isArray(msg) ? msg[0] : msg)
      }
    }

    const fetchPlans = async () => {
      isLoading.value = true
      try {
        const [plansRes, currentRes] = await Promise.all([
          api.get('/course-plans'),
          api.get('/course-plans/current')
        ])

        if (plansRes.data.status === 'success') {
          const d = plansRes.data.data
          plans.value = d?.data || d || []
          if (d?.currency_symbol) currencySymbol.value = d.currency_symbol
        } else {
          plans.value = []
        }

        if (currentRes.data.status === 'success' && currentRes.data.data?.has_plan && currentRes.data.data?.data) {
          currentPlan.value = currentRes.data.data.data
        } else {
          currentPlan.value = null
        }
      } catch (error) {
        plans.value = []
        currentPlan.value = null
        if (window.notify) window.notify('error', 'Failed to load courses packages')
      } finally {
        isLoading.value = false
      }
    }

    onMounted(() => {
      fetchPlans()
    })

    return {
      plans,
      currentPlan,
      currencySymbol,
      isLoading,
      formatAmount,
      formatDate,
      purchasePlan
    }
  }
}
</script>

<style scoped>
.card:hover {
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
}
</style>
