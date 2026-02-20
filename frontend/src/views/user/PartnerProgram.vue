<template>
  <DashboardLayout page-title="Partner Program" :dark-theme="true">
    
    <!-- Terms gate -->
    <div class="tw-mb-6">
      <div class="tw-bg-amber-500/10 tw-border tw-border-amber-500/30 tw-rounded-2xl tw-p-5">
        <div class="tw-text-white tw-font-bold tw-mb-2">
          <i class="fas fa-check-circle tw-mr-2 tw-text-amber-400"></i>Terms Required
        </div>
        <label class="tw-flex tw-items-start tw-gap-3 tw-cursor-pointer tw-select-none">
          <input type="checkbox" v-model="termsAccepted" class="tw-mt-1">
          <span class="tw-text-white/90 tw-text-sm tw-leading-relaxed">
            I agree with
            <router-link to="/policy/terms-of-service" class="tw-font-bold tw-text-indigo-200 tw-no-underline hover:tw-underline">Terms of Service</router-link>
            and
            <router-link to="/policy/privacy-policy" class="tw-font-bold tw-text-indigo-200 tw-no-underline hover:tw-underline">Privacy Policy</router-link>
            before payment.
          </span>
        </label>
      </div>
    </div>

    <!-- Info Alert -->
    <div class="tw-mb-8">
      <div class="tw-bg-slate-800/80 tw-backdrop-blur-sm tw-border tw-border-slate-700 tw-rounded-2xl tw-p-6 tw-flex tw-items-start tw-gap-4 tw-shadow-xl">
        <div class="tw-bg-indigo-500/20 tw-rounded-full tw-p-3 tw-text-indigo-400 tw-shrink-0">
          <i class="fas fa-info-circle tw-text-2xl"></i>
        </div>
        <div>
          <h5 class="tw-text-white tw-font-bold tw-text-xl tw-mb-3">How Partner Program Works</h5>
          <p class="tw-text-slate-300 tw-m-0 tw-leading-relaxed tw-mb-3">
            Join our Partner Program to earn extra commission and downline earning benefits. Higher plans provide better earning potential on your referrals.
          </p>
          <ul class="tw-text-slate-300 tw-m-0 tw-pl-5 tw-space-y-2">
            <li>Referral commission depends on the plan you join.</li>
            <li>Downline earning benefits are available on selected plans.</li>
            <li>Earnings are credited automatically after eligible actions.</li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Plans Grid -->
    <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-3 xl:tw-grid-cols-4 tw-gap-6 tw-mb-10">
      <template v-for="plan in partnerPlans" :key="plan?.id || Math.random()">
        <div v-if="plan && plan.id" 
          class="tw-bg-white tw-rounded-2xl tw-border tw-transition-all tw-duration-300 tw-flex tw-flex-col tw-h-full tw-overflow-hidden tw-relative"
          :class="[
            currentPartnerPlanId === plan.id ? 'tw-border-emerald-500 tw-shadow-lg tw-shadow-emerald-500/10' : (plan.is_recommended ? 'tw-border-amber-400 tw-shadow-lg tw-shadow-amber-500/10' : 'tw-border-slate-200 tw-shadow-sm hover:tw-shadow-xl hover:-tw-translate-y-1')
          ]"
        >
          <!-- Badge -->
          <div v-if="currentPartnerPlanId === plan.id" class="tw-bg-emerald-500 tw-text-white tw-text-center tw-py-2 tw-font-bold tw-text-sm tw-uppercase tw-tracking-wide">
            <i class="fas fa-check-circle tw-mr-1"></i> Current Plan
          </div>
          <div v-else-if="plan.is_recommended" class="tw-bg-amber-400 tw-text-white tw-text-center tw-py-2 tw-font-bold tw-text-sm tw-uppercase tw-tracking-wide">
            <i class="fas fa-star tw-mr-1"></i> Recommended
          </div>

          <div class="tw-p-6 tw-flex-1 tw-flex tw-flex-col">
            <div class="tw-text-center tw-mb-6">
              <h4 class="tw-text-xl tw-font-bold tw-text-slate-800 tw-mb-2">{{ plan.name }}</h4>
              <h2 class="tw-text-3xl tw-font-extrabold tw-text-indigo-600">{{ currencySymbol }}{{ formatAmount(plan.price) }}</h2>
            </div>
            
            <ul class="tw-space-y-3 tw-mb-8 tw-flex-1">
              <li v-for="(bullet, index) in getPlanBullets(plan)" :key="index" class="tw-flex tw-items-start tw-gap-3">
                <div class="tw-bg-emerald-100 tw-text-emerald-600 tw-rounded-full tw-p-1 tw-w-6 tw-h-6 tw-flex tw-items-center tw-justify-center tw-shrink-0 tw-mt-0.5">
                  <i class="fas fa-check tw-text-xs"></i>
                </div>
                <span class="tw-text-slate-600 tw-text-sm" v-html="bullet"></span>
              </li>
            </ul>

            <button
              @click="joinPartnerProgram(plan)"
              :disabled="plan.id === currentPartnerPlanId || !termsAccepted"
              class="tw-w-full tw-py-3 tw-font-bold tw-rounded-xl tw-transition-all tw-flex tw-items-center tw-justify-center tw-gap-2 tw-border-0 tw-cursor-pointer"
              :class="[
                plan.id === currentPartnerPlanId 
                  ? 'tw-bg-emerald-500 tw-text-white tw-cursor-not-allowed' 
                  : (!termsAccepted ? 'tw-bg-slate-500 tw-text-white tw-opacity-70 tw-cursor-not-allowed' : 'tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-shadow-lg tw-shadow-indigo-500/20')
              ]"
            >
              <i v-if="plan.id === currentPartnerPlanId" class="fas fa-check-circle"></i>
              <i v-else class="fas fa-handshake"></i>
              {{ plan.id === currentPartnerPlanId ? 'Current Plan' : 'Join Program' }}
            </button>
          </div>
        </div>
      </template>
    </div>
  </DashboardLayout>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import DashboardLayout from '../../components/DashboardLayout.vue'
import api from '../../services/api'

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

    const getPlanBullets = (plan) => {
      const bullets = [
        `Direct Referral Income: <strong class="tw-text-slate-800">${currencySymbol.value}${formatAmount(plan.referral_commission)}</strong> per joining`,
      ]
      
      if (plan.downline_commission > 0) {
        bullets.push(`Downline Team Earning: <strong class="tw-text-slate-800">${plan.downline_commission}%</strong> of team ads income`)
      } else {
        bullets.push('<span class="tw-text-slate-400">Indirect Income not available</span>')
      }

      bullets.push('Exclusive <strong class="tw-text-slate-800">Premium Partner Badge</strong>')
      bullets.push('Unlock <strong class="tw-text-slate-800">Advanced Affiliate</strong> Features')
      bullets.push('Instant <strong class="tw-text-slate-800">Commission Settlements</strong>')
      bullets.push('Dedicated <strong class="tw-text-slate-800">24/7 Priority Support</strong>')
      
      return bullets
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
          const responseData = response.data.data || {}
          partnerPlans.value = responseData.data || []
          currencySymbol.value = responseData.currency_symbol || '₹'
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
      termsAccepted,
      getPlanBullets
    }
  }
}
</script>
