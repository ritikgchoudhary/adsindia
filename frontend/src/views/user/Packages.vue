<template>
  <DashboardLayout page-title="Courses Packages" :dark-theme="true">
    <!-- Current Course Plan - always show this section (with plan or "no plan" message) -->
      <div v-if="currentPlan" class="tw-bg-white tw-rounded-2xl tw-shadow-lg tw-overflow-hidden tw-border-2 tw-transition-all" :class="currentPlan.is_active === false ? 'tw-border-amber-300 tw-shadow-amber-500/10' : 'tw-border-indigo-300 tw-shadow-indigo-500/10'">
        <div class="tw-p-5 tw-text-white tw-flex tw-items-center tw-justify-between tw-flex-wrap tw-gap-2" :class="currentPlan.is_active === false ? 'tw-bg-gradient-to-r tw-from-amber-500 tw-to-orange-500' : 'tw-bg-gradient-to-r tw-from-indigo-600 tw-to-violet-600'">
          <h5 class="tw-font-bold tw-text-lg tw-m-0 tw-flex tw-items-center">
            <i class="fas fa-graduation-cap tw-mr-2"></i>Your Current Course Plan
          </h5>
          <span v-if="currentPlan.is_active === false" class="tw-bg-white/20 tw-px-3 tw-py-1 tw-rounded-lg tw-text-sm tw-font-bold">Expired</span>
          <span v-else class="tw-bg-green-500/90 tw-px-3 tw-py-1.5 tw-rounded-lg tw-text-sm tw-font-bold tw-shadow-sm">Active</span>
        </div>
        <div class="tw-p-6 tw-bg-gradient-to-b tw-from-indigo-50 tw-to-white">
          <div class="tw-flex tw-items-start md:tw-items-center tw-gap-4 tw-mb-4">
            <div class="tw-w-14 tw-h-14 tw-rounded-xl tw-bg-indigo-100 tw-text-indigo-600 tw-flex tw-items-center tw-justify-center tw-shrink-0">
              <i class="fas fa-book-open tw-text-2xl"></i>
            </div>
            <div>
              <h4 class="tw-text-xl tw-font-bold tw-text-indigo-700 tw-mb-1">{{ currentPlan.name }}</h4>
              <p class="tw-text-slate-500 tw-text-sm tw-m-0" v-if="currentPlan.expires_at">
                <template v-if="currentPlan.is_active === false">Expired on: </template>
                <template v-else>Valid until: </template>
                <span class="tw-font-semibold tw-text-slate-700">{{ formatDate(currentPlan.expires_at) }}</span>
              </p>
              <p class="tw-text-slate-500 tw-text-sm tw-m-0" v-else>Lifetime access</p>
            </div>
          </div>
          
          <p class="tw-text-slate-700 tw-mb-6">
            <strong>Courses unlocked:</strong> {{ currentPlanDisplayCount }} courses. Complete them to earn certificates.
          </p>
          <ul v-if="currentPlanCourseList.length > 0" class="tw-text-slate-600 tw-text-sm tw-space-y-2 tw-mb-6 tw-ml-1 tw-pl-4 tw-border-l-2 tw-border-indigo-200">
            <li v-for="(courseName, idx) in currentPlanCourseList" :key="idx" class="tw-flex tw-items-center tw-gap-2">
              <i class="fas fa-check tw-text-green-500 tw-text-xs tw-shrink-0"></i>
              <span>{{ courseName }}</span>
            </li>
          </ul>
          
          <router-link v-if="currentPlan.is_active !== false" to="/user/courses" class="tw-inline-flex tw-items-center tw-px-6 tw-py-2.5 tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-font-bold tw-rounded-xl tw-shadow-lg tw-shadow-indigo-500/20 tw-transition-all tw-no-underline">
            <i class="fas fa-play tw-mr-2"></i>View Courses
          </router-link>
          <p v-else class="tw-text-amber-700 tw-font-medium tw-m-0">Renew or buy a new package below to access courses again.</p>
        </div>
      </div>
      <div v-else class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-slate-200 tw-overflow-hidden">
        <div class="tw-bg-gradient-to-r tw-from-slate-500 tw-to-slate-600 tw-p-5 tw-text-white">
          <h5 class="tw-font-bold tw-text-lg tw-m-0 tw-flex tw-items-center">
            <i class="fas fa-graduation-cap tw-mr-2"></i>Your Current Course Plan
          </h5>
        </div>
        <div class="tw-p-6 tw-bg-slate-50">
          <p class="tw-text-slate-600 tw-m-0">You don't have an active course plan. Choose a package below to access courses and earn certificates.</p>
        </div>
      </div>

    <!-- Course Plans (Learning) -->
    <div class="tw-mb-8">
      <div class="tw-mb-6">
        <h4 class="tw-text-xl tw-font-bold tw-text-slate-900 tw-mb-1 flex tw-items-center">
          <i class="fas fa-graduation-cap tw-mr-2 tw-text-indigo-600"></i>Courses Packages
        </h4>
        <p class="tw-text-slate-500 tw-m-0">Buy a package to access courses. Complete courses to learn and get certificates.</p>
      </div>

      <!-- Terms/Privacy checkbox only when coming from Home initial purchase -->
      <div
        v-if="requiresHomeBuyAgree"
        class="tw-mb-5 tw-bg-amber-50 tw-border tw-border-amber-200 tw-rounded-2xl tw-p-4"
      >
        <div class="tw-flex tw-items-start tw-gap-3">
          <input
            id="home_buy_agree"
            type="checkbox"
            class="tw-mt-1"
            v-model="homeBuyAgree"
          >
          <label for="home_buy_agree" class="tw-text-sm tw-text-amber-900 tw-leading-relaxed tw-m-0">
            <strong>I agree to the</strong>
            <a href="/policy/terms-of-service" target="_blank" class="tw-text-indigo-700 tw-font-bold hover:tw-underline">Terms of Service</a>
            and
            <a href="/policy/privacy-policy" target="_blank" class="tw-text-indigo-700 tw-font-bold hover:tw-underline">Privacy Policy</a>.
            <div class="tw-text-xs tw-text-amber-700 tw-mt-1">Required to buy your first package from the Home page.</div>
          </label>
        </div>
      </div>


      <div v-else-if="plans.length === 0" class="tw-bg-amber-50 tw-text-amber-700 tw-p-4 tw-rounded-xl tw-flex tw-items-center tw-justify-center">
        <i class="fas fa-exclamation-triangle tw-mr-2"></i>No courses packages available. Please contact support.
      </div>

      <div v-else class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-3 tw-gap-6">
        <template v-for="plan in plans" :key="plan?.id">
          <div v-if="plan && plan.id" 
            class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-transition-all tw-duration-300 tw-flex tw-flex-col hover:-tw-translate-y-1 hover:tw-shadow-xl"
            :class="currentPlan && plan.id === currentPlan.plan_id ? (currentPlan.is_active !== false ? 'tw-border-indigo-500 tw-ring-2 tw-ring-indigo-500/20' : 'tw-border-amber-400 tw-ring-2 tw-ring-amber-400/20') : 'tw-border-slate-200'"
          >
            <div v-if="currentPlan && plan.id === currentPlan.plan_id && currentPlan.is_active !== false" class="tw-bg-green-600 tw-text-white tw-py-2 tw-px-4 tw-text-center tw-font-bold tw-text-sm uppercase tracking-wide">
              <i class="fas fa-check-circle tw-mr-1"></i>Activated
            </div>
            <div v-else-if="currentPlan && plan.id === currentPlan.plan_id && currentPlan.is_active === false" class="tw-bg-amber-500 tw-text-white tw-py-2 tw-px-4 tw-text-center tw-font-bold tw-text-sm uppercase tracking-wide">
              <i class="fas fa-clock tw-mr-1"></i>Expired
            </div>

            <div class="tw-p-6 tw-flex-1 tw-flex tw-flex-col">
              <h4 class="tw-text-xl tw-font-bold tw-text-slate-800 tw-text-center tw-mb-4">{{ plan.name }}</h4>
              
              <div class="tw-text-center tw-mb-6">
                <h2 class="tw-text-3xl tw-font-extrabold tw-text-indigo-600 tw-mb-1">{{ currencySymbol }}{{ formatAmount(plan.price) }}</h2>
                <p class="tw-text-slate-400 tw-text-sm tw-m-0">Lifetime access</p>
              </div>

              <p v-if="plan.description" class="tw-text-slate-500 tw-text-sm tw-mb-4 tw-text-center">{{ plan.description }}</p>
              
              <div class="tw-mb-6 tw-flex-1">
                <div class="tw-flex tw-items-center tw-justify-center tw-mb-3">
                   <span class="tw-bg-indigo-50 tw-text-indigo-700 tw-px-3 tw-py-1 tw-rounded-lg tw-text-sm tw-font-semibold">
                     {{ plan.displayCount || plan.courses_count }} Courses Included
                   </span>
                </div>
                <ul class="tw-space-y-2">
                  <li v-for="(course, idx) in plan.courseList" :key="idx" class="tw-flex tw-items-start tw-gap-2">
                    <i class="fas fa-check tw-text-green-500 tw-mt-1 tw-text-xs"></i>
                    <span class="tw-text-slate-600 tw-text-sm">{{ course }}</span>
                  </li>
                </ul>
              </div>

              <div class="tw-flex tw-items-center tw-justify-center tw-mb-4" v-if="!plan.courseList || plan.courseList.length === 0">
                 <span class="tw-text-slate-400 tw-text-xs">Course list updating...</span>
              </div>

              <button
                type="button"
                class="tw-w-full tw-py-3 tw-font-bold tw-rounded-xl tw-flex tw-items-center tw-justify-center tw-gap-2 tw-transition-all tw-border-0 tw-cursor-pointer"
                :class="(currentPlan && plan.id === currentPlan.plan_id && currentPlan.is_active !== false) ? 'tw-bg-green-600 tw-text-white tw-cursor-default' : 'tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-shadow-lg tw-shadow-indigo-500/20'"
                :disabled="(currentPlan && plan.id === currentPlan.plan_id && currentPlan.is_active !== false)"
                @click="purchasePlan(plan)">
                <span v-if="currentPlan && plan.id === currentPlan.plan_id && currentPlan.is_active !== false">
                  <i class="fas fa-check-circle"></i> Activated
                </span>
                <span v-else>
                  <i class="fas fa-shopping-cart"></i> {{ (currentPlan && plan.id === currentPlan.plan_id && currentPlan.is_active === false) ? 'Renew' : 'Buy' }} Package – {{ currencySymbol }}{{ formatAmount(plan.price) }}
                </span>
              </button>
            </div>
          </div>
        </template>
      </div>
    </div>

    <div v-if="!currentPlan && plans.length > 0">
      <div class="tw-bg-sky-50 tw-border tw-border-sky-100 tw-rounded-xl tw-p-4 tw-flex tw-items-start tw-gap-3">
        <i class="fas fa-info-circle tw-text-sky-500 tw-mt-1"></i>
        <div class="tw-text-sky-800 tw-text-sm">
          <strong>Step:</strong> Choose a courses package → Access courses → Complete courses → Get certificates.
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import DashboardLayout from '../../components/DashboardLayout.vue'
import api from '../../services/api'
import { openPaymentInNewTab } from '../../services/paymentWindow'

// Same course count & list by plan name as used in plan cards (LEARN Lite, Pro, Supreme, Premium, Premium+)
function getPlanDisplayInfo(planName) {
  const name = (planName || '').toLowerCase()
  if (name.includes('lite')) return { count: 4, list: ['Affiliate Marketing Mastery', 'Social Media Optimization', 'Canva Designing', 'Communication Skills'] }
  if (name.includes('pro')) return { count: 6, list: ['All Lite Courses', 'Facebook Ads Mastery', 'Instagram Growth Secrets'] }
  if (name.includes('supreme')) return { count: 9, list: ['All Pro Courses', 'Google Ads Mastery', 'YouTube Domination', 'Video Editing Mastery'] }
  if (name.includes('premium') && !name.includes('plus') && !name.includes('+')) return { count: 12, list: ['All Supreme Courses', 'Stock Market Basics', 'Crypto Currency Trading', 'Public Speaking Mastery'] }
  if (name.includes('premium') && (name.includes('plus') || name.includes('+'))) return { count: 15, list: ['All Premium Courses', 'Advanced Digital Marketing', 'Website Development', 'Freelancing Mastery'] }
  return { count: 0, list: [] }
}

export default {
  name: 'Packages',
  components: {
    DashboardLayout
  },
  setup() {
    const route = useRoute()
    const router = useRouter()
    const plans = ref([])
    const currentPlan = ref(null)
    const currencySymbol = ref('₹')
    const isLoading = ref(true)
    const homeBuyAgree = ref(false)

    const requiresHomeBuyAgree = computed(() => {
      const fromHome = String(route.query?.from_home_buy ?? '') === '1'
      // "Initial purchase": user doesn't have any plan yet
      return fromHome && !currentPlan.value
    })

    onMounted(() => {
      try {
        homeBuyAgree.value = sessionStorage.getItem('home_buy_agree') === '1'
      } catch (_) {}
    })

    watch(homeBuyAgree, (v) => {
      try {
        sessionStorage.setItem('home_buy_agree', v ? '1' : '0')
      } catch (_) {}
    })

    const currentPlanDisplayCount = computed(() => {
      if (!currentPlan.value || !currentPlan.value.name) return currentPlan.value?.courses_count ?? 0
      const info = getPlanDisplayInfo(currentPlan.value.name)
      return info.count || currentPlan.value.courses_count || 0
    })
    const currentPlanCourseList = computed(() => {
      if (!currentPlan.value || !currentPlan.value.name) return []
      return getPlanDisplayInfo(currentPlan.value.name).list
    })

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
      if (requiresHomeBuyAgree.value && !homeBuyAgree.value) {
        if (window.notify) window.notify('error', 'Please accept the Terms of Service and Privacy Policy to continue.')
        return
      }
      if (!confirm(`Buy "${plan.name}" for ${currencySymbol.value}${formatAmount(plan.price)}? You will be redirected to the payment gateway.`)) return

      // Open same-site redirect page in new tab (reliable in all browsers)
      const redirectUrl = `/user/payment-redirect?flow=course_plan&plan_id=${encodeURIComponent(plan.id)}&amount=${plan.price}&plan_name=${encodeURIComponent(plan.name)}&back=${encodeURIComponent('/user/packages')}`
      const w = window.open(redirectUrl, '_blank')
      if (!w) {
        // Popup blocked: fallback to same tab
        router.push(redirectUrl)
      } else if (window.notify) {
        window.notify('info', 'Payment tab opened. Complete payment to activate your package.')
      }
    }

    function parseCurrentPlanFromResponse(res) {
      if (!res || res.status !== 'success') return null
      const d = res.data
      if (!d) return null
      // Shape 1: { data: { has_plan, data: { plan_id, name, ... } } }
      if (d.data && typeof d.data === 'object' && (d.data.plan_id != null || d.data.name)) {
        const p = d.data
        if (p.is_active === undefined) p.is_active = true
        return p
      }
      // Shape 2: { data: { plan_id, name, ... } } (flat)
      if (d.plan_id != null && d.name) {
        if (d.is_active === undefined) d.is_active = true
        return d
      }
      return null
    }

    const fetchPlans = async () => {
      isLoading.value = true
      currentPlan.value = null
      try {
        const plansRes = await api.get('/course-plans')
        if (plansRes.data?.status === 'success') {
          const d = plansRes.data.data
          let fetchedPlans = d?.data ?? d ?? []
          if (!Array.isArray(fetchedPlans)) fetchedPlans = []

          fetchedPlans = fetchedPlans.map(p => {
            const info = getPlanDisplayInfo(p.name)
            const count = info.count || p.courses_count
            const courseList = info.list.length ? info.list : []
            return { ...p, displayCount: count, courseList }
          })
          plans.value = fetchedPlans
          if (d?.currency_symbol) currencySymbol.value = d.currency_symbol
        } else {
          plans.value = []
        }
      } catch (err) {
        plans.value = []
        if (window.notify) window.notify('error', 'Failed to load packages')
      }

      try {
        const currentRes = await api.get('/course-plans/current')
        currentPlan.value = parseCurrentPlanFromResponse(currentRes.data)
      } catch (_) {
        currentPlan.value = null
      } finally {
        isLoading.value = false
      }
    }

    onMounted(() => {
      ;(async () => {
        const trx = route.query.watchpay_trx || route.query.simplypay_trx || route.query.rupeerush_trx
        const coursePlanId = route.query.course_plan_id
        if (trx && coursePlanId) {
          try {
            const gateway = route.query.simplypay_trx ? 'simplypay' : (route.query.rupeerush_trx ? 'rupeerush' : 'watchpay')
            const confirmRes = await api.post('/course-plans/payment/confirm', {
              trx,
              plan_id: parseInt(coursePlanId),
              gateway: gateway
            })
            if (confirmRes.data.status === 'success') {
              if (window.notify) window.notify('success', 'Courses package purchased! You can now access courses.')
              // Avoid back-button confusion by redirecting to Courses
              setTimeout(() => {
                router.replace('/user/courses')
              }, 600)
            }
          } catch (e) {
            // ignore
          }
        }
        fetchPlans()
      })()
    })

    return {
      plans,
      currentPlan,
      currentPlanDisplayCount,
      currentPlanCourseList,
      currencySymbol,
      isLoading,
      formatAmount,
      formatDate,
      purchasePlan,
      requiresHomeBuyAgree,
      homeBuyAgree
    }
  }
}
</script>

<style scoped>
@media (max-width: 640px) {
  /* Current Plan Alert */
  .tw-rounded-2xl { border-radius: 1rem !important; }
  .tw-p-5 { padding: 0.75rem !important; }
  .tw-p-6 { padding: 0.85rem !important; }
  h5.tw-text-lg { font-size: 0.95rem !important; }
  .tw-w-14.tw-h-14 { width: 2.5rem !important; height: 2.5rem !important; }
  .tw-w-14.tw-h-14 i { font-size: 1.25rem !important; }
  h4.tw-text-xl { font-size: 1.1rem !important; }
  .tw-text-sm { font-size: 0.75rem !important; }
  p.tw-text-slate-700 { font-size: 0.8rem !important; margin-bottom: 0.75rem !important; }
  ul.tw-space-y-2 { space-y: 1 !important; margin-bottom: 1rem !important; }
  .tw-px-6.tw-py-2\.5 { padding: 0.5rem 1rem !important; font-size: 0.85rem !important; border-radius: 0.75rem !important; }

  /* Main Header */
  .tw-mb-8 { margin-bottom: 1.5rem !important; }
  .tw-mb-6 { margin-bottom: 1rem !important; }
  h4.tw-text-xl { font-size: 1.1rem !important; }
  p.tw-text-slate-500 { font-size: 0.8rem !important; }

  /* Terms Alert */
  .tw-bg-amber-50 { padding: 0.75rem !important; border-radius: 1rem !important; }
  label.tw-text-sm { font-size: 0.75rem !important; }

  /* Package Cards Grid */
  .tw-grid.tw-gap-6 { gap: 1rem !important; }
  
  /* Package Cards */
  .tw-p-6.tw-flex-1 { padding: 1.15rem !important; }
  h4.tw-text-xl { font-size: 1.2rem !important; margin-bottom: 0.5rem !important; }
  h2.tw-text-3xl { font-size: 1.75rem !important; }
  .tw-text-3xl.tw-font-extrabold { font-size: 1.75rem !important; }
  p.tw-text-slate-500.tw-text-sm { font-size: 0.75rem !important; margin-bottom: 0.75rem !important; }
  
  /* Features list in cards */
  .tw-mb-6.tw-flex-1 { margin-bottom: 1rem !important; }
  .tw-space-y-2 { space-y: 1 !important; }
  span.tw-text-slate-600.tw-text-sm { font-size: 0.75rem !important; }
  
  /* Buy Button */
  .tw-py-3 { padding-top: 0.65rem !important; padding-bottom: 0.65rem !important; font-size: 0.9rem !important; border-radius: 0.75rem !important; }
  
  /* Step box */
  .tw-bg-sky-50 { padding: 0.75rem !important; border-radius: 0.85rem !important; }
  .tw-text-sky-800.tw-text-sm { font-size: 0.75rem !important; }
}
</style>
