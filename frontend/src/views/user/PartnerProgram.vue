<template>
  <DashboardLayout page-title="Partner Program" :dark-theme="true">
    <div class="row">
      <div class="col-12 mb-4">
        <div class="card custom--card">
          <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-handshake me-2"></i>Partner Program Plans</h5>
          </div>
          <div class="card-body">
            <p class="text-muted">Join our Partner Program to earn extra commission and downline earning benefits.</p>

            <div class="alert alert-warning mt-3 mb-0">
              <label class="d-flex gap-2 align-items-start mb-0" style="cursor:pointer;">
                <input type="checkbox" v-model="termsAccepted" style="margin-top:4px;">
                <span>
                  I agree with
                  <router-link to="/policy/terms-of-service"><b>Terms of Service</b></router-link>
                  and
                  <router-link to="/policy/privacy-policy"><b>Privacy Policy</b></router-link>
                  before payment.
                </span>
              </label>
            </div>
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
            <button class="btn btn--base w-100" @click="joinPartnerProgram(plan)" :disabled="plan.id === currentPartnerPlanId || !termsAccepted">
              <span v-if="plan.id === currentPartnerPlanId">Current Plan</span>
              <span v-else>Join Now</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Info Sections -->
      <div class="col-12">
        <div class="card custom--card">
          <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>About Partner Program</h5>
          </div>
          <div class="card-body">
            <div class="mb-4">
              <h6 class="fw-bold mb-2">What is Partner Program?</h6>
              <p class="text-muted mb-0">
                Partner Program is an upgraded affiliate level that gives you higher earning benefits on referrals and downline commissions (as per the plan you join).
              </p>
            </div>

            <div class="mb-4">
              <h6 class="fw-bold mb-2">Commission Structure</h6>
              <ul class="text-muted mb-0">
                <li><b>Referral commission</b> depends on the plan you join (shown above).</li>
                <li><b>Downline earning (%)</b> is available on selected plans.</li>
                <li>Earnings are credited automatically after eligible actions.</li>
              </ul>
            </div>

            <div class="mb-4">
              <h6 class="fw-bold mb-2">Benefits</h6>
              <ul class="text-muted mb-0">
                <li>Higher commission on referrals</li>
                <li>Downline earning benefits on selected plans</li>
                <li>Priority access to partner promotions (when available)</li>
              </ul>
            </div>

            <div>
              <h6 class="fw-bold mb-3">FAQ</h6>
              <div class="d-grid gap-3">
                <details class="border rounded p-3">
                  <summary class="fw-bold">When will my partner plan become active?</summary>
                  <div class="text-muted mt-2">After successful payment confirmation, your plan becomes active automatically.</div>
                </details>
                <details class="border rounded p-3">
                  <summary class="fw-bold">Can I change or upgrade my partner plan later?</summary>
                  <div class="text-muted mt-2">Yes. You can join a different plan anytime by completing payment for that plan.</div>
                </details>
                <details class="border rounded p-3">
                  <summary class="fw-bold">Where can I see my referral earnings?</summary>
                  <div class="text-muted mt-2">Go to <b>Affiliate Income</b> page to see earnings, referral count, and withdrawal history.</div>
                </details>
                <details class="border rounded p-3">
                  <summary class="fw-bold">Payment completed but not updated?</summary>
                  <div class="text-muted mt-2">Wait 1–2 minutes and refresh. If it still doesn’t update, contact support from the Customer Support page.</div>
                </details>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import DashboardLayout from '../../components/DashboardLayout.vue'
import api from '../../services/api'
import { openPaymentInNewTab } from '../../services/paymentWindow'

export default {
  name: 'PartnerProgram',
  components: {
    DashboardLayout
  },
  setup() {
    const route = useRoute()
    const router = useRouter()
    const partnerPlans = ref([])
    const currentPartnerPlanId = ref(null)
    const currencySymbol = ref('₹')
    const termsAccepted = ref(false)

    const formatAmount = (amount) => {
      if (!amount && amount !== 0) return '0.00'
      return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }

    const joinPartnerProgram = async (plan) => {
      if (!plan || !plan.id || plan.id === currentPartnerPlanId.value) return
      if (!termsAccepted.value) {
        if (window.notify) window.notify('error', 'Please accept Terms & Privacy Policy before payment.')
        return
      }

      if (!confirm(`Are you sure you want to join ${plan.name} for ${currencySymbol.value}${formatAmount(plan.price)}?`)) {
        return
      }

      const redirectUrl = `/user/payment-redirect?flow=partner_plan&plan_id=${encodeURIComponent(plan.id)}&back=${encodeURIComponent('/user/partner-program')}`
      const w = window.open(redirectUrl, '_blank')
      if (!w) {
        router.push(redirectUrl)
      } else if (window.notify) {
        window.notify('info', 'Payment tab opened. Complete payment to activate your partner plan.')
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
      ;(async () => {
        const trx = route.query.watchpay_trx || route.query.simplypay_trx
        const planId = route.query.partner_plan_id
        if (trx && planId) {
          try {
            const gateway = route.query.simplypay_trx ? 'simplypay' : 'watchpay'
            const confirmRes = await api.post('/partner-program/payment/confirm', {
              trx,
              plan_id: parseInt(planId),
              gateway: gateway
            })
            if (confirmRes.data.status === 'success' && window.notify) {
              window.notify('success', 'Partner program joined successfully!')
              setTimeout(() => {
                router.replace('/dashboard')
              }, 600)
            }
          } catch (e) {
            // ignore
          }
        }

        fetchPartnerPlans()
        fetchCurrentPlan()
      })()
    })

    return {
      partnerPlans,
      currentPartnerPlanId,
      currencySymbol,
      formatAmount,
      joinPartnerProgram,
      termsAccepted
    }
  }
}
</script>
