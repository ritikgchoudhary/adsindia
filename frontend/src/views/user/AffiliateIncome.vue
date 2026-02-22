<template>
  <DashboardLayout page-title="Affiliate Income" :dark-theme="true">
    <div class="tw-grid tw-grid-cols-1 tw-gap-8">
      
      <!-- Income Overview -->
      <div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-slate-200 tw-overflow-hidden">
        <div class="tw-p-5 tw-border-b tw-border-slate-200 tw-bg-gradient-to-r tw-from-slate-50 tw-to-white">
          <div class="tw-flex tw-flex-col md:tw-flex-row md:tw-items-center md:tw-justify-between tw-gap-3">
            <h5 class="tw-text-slate-900 tw-font-bold tw-text-lg tw-m-0 tw-flex tw-items-center">
              <i class="fas fa-chart-line tw-mr-2 tw-text-indigo-600"></i>Affiliate Income Dashboard
            </h5>
            <div class="tw-flex tw-flex-wrap tw-gap-2">
              <router-link
                to="/user/account-kyc"
                class="tw-inline-flex tw-items-center tw-gap-2 tw-px-4 tw-py-2 tw-rounded-xl tw-bg-slate-900 tw-text-white tw-font-bold tw-text-sm tw-no-underline hover:tw-bg-slate-800 tw-transition-colors"
              >
                <i class="fas fa-user-shield"></i> KYC
              </router-link>
              <router-link
                to="/user/affiliate-withdraw"
                class="tw-inline-flex tw-items-center tw-gap-2 tw-px-4 tw-py-2 tw-rounded-xl tw-bg-emerald-600 tw-text-white tw-font-bold tw-text-sm tw-no-underline hover:tw-bg-emerald-700 tw-transition-colors"
              >
                <i class="fas fa-hand-holding-usd"></i> Affiliate Withdraw
              </router-link>
            </div>
          </div>
        </div>
        
        <div class="tw-p-6">
          <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 xl:tw-grid-cols-4 tw-gap-6">
            
            <div class="tw-bg-gradient-to-br tw-from-indigo-500 tw-to-violet-600 tw-rounded-2xl tw-p-6 tw-text-white tw-shadow-lg tw-shadow-indigo-500/20 tw-relative tw-overflow-hidden hover:-tw-translate-y-1 tw-transition-transform tw-duration-300">
              <div class="tw-absolute tw-top-0 tw-left-0 tw-w-full tw-h-full tw-bg-white/5 tw-pointer-events-none"></div>
              <div class="tw-w-12 tw-h-12 tw-rounded-xl tw-bg-white/20 tw-flex tw-items-center tw-justify-center tw-mb-4 tw-backdrop-blur-sm">
                <i class="fas fa-calendar-day tw-text-xl"></i>
              </div>
              <h3 class="tw-text-2xl tw-font-extrabold tw-mb-1">{{ currencySymbol }}{{ formatAmount(incomeTodayAnim) }}</h3>
              <p class="tw-text-indigo-100 tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-m-0">Today Income</p>
            </div>
            
            <div class="tw-bg-gradient-to-br tw-from-cyan-500 tw-to-blue-600 tw-rounded-2xl tw-p-6 tw-text-white tw-shadow-lg tw-shadow-cyan-500/20 tw-relative tw-overflow-hidden hover:-tw-translate-y-1 tw-transition-transform tw-duration-300">
              <div class="tw-absolute tw-top-0 tw-left-0 tw-w-full tw-h-full tw-bg-white/5 tw-pointer-events-none"></div>
              <div class="tw-w-12 tw-h-12 tw-rounded-xl tw-bg-white/20 tw-flex tw-items-center tw-justify-center tw-mb-4 tw-backdrop-blur-sm">
                <i class="fas fa-calendar-week tw-text-xl"></i>
              </div>
              <h3 class="tw-text-2xl tw-font-extrabold tw-mb-1">{{ currencySymbol }}{{ formatAmount(incomeWeekAnim) }}</h3>
              <p class="tw-text-cyan-100 tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-m-0">This Week</p>
            </div>
            
            <div class="tw-bg-gradient-to-br tw-from-amber-400 tw-to-orange-500 tw-rounded-2xl tw-p-6 tw-text-white tw-shadow-lg tw-shadow-amber-500/20 tw-relative tw-overflow-hidden hover:-tw-translate-y-1 tw-transition-transform tw-duration-300">
              <div class="tw-absolute tw-top-0 tw-left-0 tw-w-full tw-h-full tw-bg-white/5 tw-pointer-events-none"></div>
              <div class="tw-w-12 tw-h-12 tw-rounded-xl tw-bg-white/20 tw-flex tw-items-center tw-justify-center tw-mb-4 tw-backdrop-blur-sm">
                <i class="fas fa-calendar-alt tw-text-xl"></i>
              </div>
              <h3 class="tw-text-2xl tw-font-extrabold tw-mb-1">{{ currencySymbol }}{{ formatAmount(incomeMonthAnim) }}</h3>
              <p class="tw-text-amber-100 tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-m-0">This Month</p>
            </div>
            
            <div class="tw-bg-gradient-to-br tw-from-emerald-500 tw-to-teal-600 tw-rounded-2xl tw-p-6 tw-text-white tw-shadow-lg tw-shadow-emerald-500/20 tw-relative tw-overflow-hidden hover:-tw-translate-y-1 tw-transition-transform tw-duration-300">
              <div class="tw-absolute tw-top-0 tw-left-0 tw-w-full tw-h-full tw-bg-white/5 tw-pointer-events-none"></div>
              <div class="tw-w-12 tw-h-12 tw-rounded-xl tw-bg-white/20 tw-flex tw-items-center tw-justify-center tw-mb-4 tw-backdrop-blur-sm">
                <i class="fas fa-coins tw-text-xl"></i>
              </div>
              <h3 class="tw-text-2xl tw-font-extrabold tw-mb-1">{{ currencySymbol }}{{ formatAmount(incomeTotalAnim) }}</h3>
              <p class="tw-text-emerald-100 tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-m-0">Total Income</p>
            </div>

            <!-- Moved down (as requested): Affiliate Wallet Balance -->
            <div class="tw-bg-gradient-to-br tw-from-emerald-500 tw-to-green-600 tw-rounded-2xl tw-p-6 tw-text-white tw-shadow-lg tw-shadow-emerald-500/20 tw-relative tw-overflow-hidden hover:-tw-translate-y-1 tw-transition-transform tw-duration-300">
              <div class="tw-absolute tw-top-0 tw-left-0 tw-w-full tw-h-full tw-bg-white/5 tw-pointer-events-none"></div>
              <div class="tw-w-12 tw-h-12 tw-rounded-xl tw-bg-white/20 tw-flex tw-items-center tw-justify-center tw-mb-4 tw-backdrop-blur-sm">
                <i class="fas fa-wallet tw-text-xl"></i>
              </div>
              <h3 class="tw-text-2xl tw-font-extrabold tw-mb-1">{{ currencySymbol }}{{ formatAmount(affiliateBalanceAnim) }}</h3>
              <p class="tw-text-emerald-100 tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-m-0">Affiliate Wallet Balance</p>
            </div>

          </div>

          <!-- Breakdown -->
          <div class="tw-mt-8">
            <div class="tw-flex tw-flex-col md:tw-flex-row md:tw-items-center tw-justify-between tw-mb-6 tw-gap-4">
              <h6 class="tw-text-slate-900 tw-font-extrabold tw-m-0 tw-flex tw-items-center tw-gap-2">
                <i class="fas fa-layer-group tw-text-indigo-600"></i> Commission Breakdown
              </h6>
              
              <div class="tw-flex tw-items-center tw-bg-slate-100 tw-p-1 tw-rounded-xl tw-gap-1">
                <button 
                  @click="selectPeriod('today')"
                  :class="[
                    'tw-px-4 tw-py-1.5 tw-text-xs tw-font-bold tw-rounded-lg tw-transition-all',
                    selectedPeriod === 'today' ? 'tw-bg-indigo-600 tw-text-white tw-shadow-sm' : 'tw-text-slate-600 hover:tw-bg-slate-200'
                  ]"
                >Today</button>
                <button 
                  @click="selectPeriod('this_week')"
                  :class="[
                    'tw-px-4 tw-py-1.5 tw-text-xs tw-font-bold tw-rounded-lg tw-transition-all',
                    selectedPeriod === 'this_week' ? 'tw-bg-indigo-600 tw-text-white tw-shadow-sm' : 'tw-text-slate-600 hover:tw-bg-slate-200'
                  ]"
                >This Week</button>
                <button 
                  @click="selectPeriod('this_month')"
                  :class="[
                    'tw-px-4 tw-py-1.5 tw-text-xs tw-font-bold tw-rounded-lg tw-transition-all',
                    selectedPeriod === 'this_month' ? 'tw-bg-indigo-600 tw-text-white tw-shadow-sm' : 'tw-text-slate-600 hover:tw-bg-slate-200'
                  ]"
                >This Month</button>
                <button 
                  @click="selectPeriod('all_time')"
                  :class="[
                    'tw-px-4 tw-py-1.5 tw-text-xs tw-font-bold tw-rounded-lg tw-transition-all',
                    selectedPeriod === 'all_time' ? 'tw-bg-indigo-600 tw-text-white tw-shadow-sm' : 'tw-text-slate-600 hover:tw-bg-slate-200'
                  ]"
                >All Time</button>
              </div>
            </div>

            <div v-if="currentBreakdown" class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-3 xl:tw-grid-cols-4 tw-gap-4">
              <!-- Registration cards -->
              <div class="tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-slate-50 tw-p-5 tw-transition-all hover:tw-shadow-md">
                <div class="tw-text-[10px] tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-500">Direct Registration</div>
                <div class="tw-mt-2 tw-text-xl tw-font-extrabold tw-text-slate-900">{{ currencySymbol }}{{ formatAmount(bdDirectAnim) }}</div>
              </div>
              <div class="tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-slate-50 tw-p-5 tw-transition-all hover:tw-shadow-md">
                <div class="tw-text-[10px] tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-500">KYC Income</div>
                <div class="tw-mt-2 tw-text-xl tw-font-extrabold tw-text-slate-900">{{ currencySymbol }}{{ formatAmount(bdKycAnim) }}</div>
              </div>
              <div class="tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-slate-50 tw-p-5 tw-transition-all hover:tw-shadow-md">
                <div class="tw-text-[10px] tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-500">Withdrawal Fee</div>
                <div class="tw-mt-2 tw-text-xl tw-font-extrabold tw-text-slate-900">{{ currencySymbol }}{{ formatAmount(bdWithdrawAnim) }}</div>
              </div>
              <div class="tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-slate-50 tw-p-5 tw-transition-all hover:tw-shadow-md">
                <div class="tw-text-[10px] tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-500">Upgrade Income</div>
                <div class="tw-mt-2 tw-text-xl tw-font-extrabold tw-text-slate-900">{{ currencySymbol }}{{ formatAmount(bdUpgradeAnim) }}</div>
              </div>
              <div class="tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-slate-50 tw-p-5 tw-transition-all hover:tw-shadow-md">
                <div class="tw-text-[10px] tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-500">Ad Certificate Income</div>
                <div class="tw-mt-2 tw-text-xl tw-font-extrabold tw-text-slate-900">{{ currencySymbol }}{{ formatAmount(bdCertificateAnim) }}</div>
              </div>
              <div class="tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-slate-50 tw-p-5 tw-transition-all hover:tw-shadow-md">
                <div class="tw-text-[10px] tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-500">Ads Plan Income</div>
                <div class="tw-mt-2 tw-text-xl tw-font-extrabold tw-text-slate-900">{{ currencySymbol }}{{ formatAmount(bdAdsPlanAnim) }}</div>
              </div>
              <div class="tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-slate-50 tw-p-5 tw-transition-all hover:tw-shadow-md">
                <div class="tw-text-[10px] tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-500">Partner Program</div>
                <div class="tw-mt-2 tw-text-xl tw-font-extrabold tw-text-slate-900">{{ currencySymbol }}{{ formatAmount(bdPartnerAnim) }}</div>
              </div>
              <div class="tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-slate-50 tw-p-5 tw-transition-all hover:tw-shadow-md">
                <div class="tw-text-[10px] tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-500">Period Total</div>
                <div class="tw-mt-2 tw-text-xl tw-font-extrabold tw-text-slate-900">{{ currencySymbol }}{{ formatAmount(bdTotalAnim) }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Income History -->
      <div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-slate-200 tw-overflow-hidden">
        <div class="tw-p-5 tw-border-b tw-border-slate-200 tw-bg-gradient-to-r tw-from-slate-50 tw-to-white">
          <h5 class="tw-text-slate-900 tw-font-bold tw-text-lg tw-m-0 tw-flex tw-items-center">
            <i class="fas fa-history tw-mr-2 tw-text-indigo-600"></i>Income History
          </h5>
        </div>
        
        <div class="tw-overflow-x-auto">
          <table class="tw-w-full tw-border-collapse">
            <thead>
              <tr class="tw-bg-slate-50 tw-border-b tw-border-slate-200">
                <th class="tw-px-4 tw-py-4 tw-text-left tw-text-xs tw-font-bold tw-text-slate-500 tw-uppercase tw-tracking-wider bg-transparent tw-whitespace-nowrap">Affiliate ID</th>
                <th class="tw-px-4 tw-py-4 tw-text-left tw-text-xs tw-font-bold tw-text-slate-500 tw-uppercase tw-tracking-wider bg-transparent tw-whitespace-nowrap">Name</th>
                <th class="tw-px-4 tw-py-4 tw-text-left tw-text-xs tw-font-bold tw-text-slate-500 tw-uppercase tw-tracking-wider bg-transparent tw-whitespace-nowrap">Email</th>
                <th class="tw-px-4 tw-py-4 tw-text-left tw-text-xs tw-font-bold tw-text-slate-500 tw-uppercase tw-tracking-wider bg-transparent tw-whitespace-nowrap">Phone</th>
                <th class="tw-px-4 tw-py-4 tw-text-left tw-text-xs tw-font-bold tw-text-slate-500 tw-uppercase tw-tracking-wider bg-transparent tw-whitespace-nowrap">Type</th>
                <th class="tw-px-4 tw-py-4 tw-text-left tw-text-xs tw-font-bold tw-text-slate-500 tw-uppercase tw-tracking-wider bg-transparent tw-whitespace-nowrap">Sponsor ID</th>
                <th class="tw-px-4 tw-py-4 tw-text-right tw-text-xs tw-font-bold tw-text-slate-500 tw-uppercase tw-tracking-wider bg-transparent tw-whitespace-nowrap">Commission</th>
                <th class="tw-px-4 tw-py-4 tw-text-left tw-text-xs tw-font-bold tw-text-slate-500 tw-uppercase tw-tracking-wider bg-transparent tw-whitespace-nowrap">Date</th>
              </tr>
            </thead>
            <tbody class="tw-divide-y tw-divide-slate-200 tw-bg-white">
              <template v-for="item in (commissionLog || [])" :key="item?.id || Math.random()">
                <tr v-show="item && item.id" class="tw-transition-colors hover:tw-bg-slate-50 hover:tw-text-slate-800">
                  <td class="tw-px-4 tw-py-4 tw-whitespace-nowrap tw-font-mono tw-text-slate-600 tw-text-sm">{{ item.affiliate_id || '—' }}</td>
                  <td class="tw-px-4 tw-py-4 tw-whitespace-nowrap tw-font-bold tw-text-slate-900 tw-text-sm">{{ item.name || '—' }}</td>
                  <td class="tw-px-4 tw-py-4 tw-whitespace-nowrap tw-text-slate-600 tw-text-sm">{{ item.email || '—' }}</td>
                  <td class="tw-px-4 tw-py-4 tw-whitespace-nowrap tw-text-slate-700 tw-text-sm">{{ item.phone || '—' }}</td>
                  <td class="tw-px-4 tw-py-4 tw-whitespace-nowrap tw-text-slate-700 tw-text-sm">
                    <span class="tw-px-2 tw-py-1 tw-rounded-lg tw-bg-slate-100 tw-text-[10px] tw-font-bold tw-text-slate-600 tw-whitespace-nowrap">{{ item.type || '—' }}</span>
                  </td>
                  <td class="tw-px-4 tw-py-4 tw-whitespace-nowrap tw-font-mono tw-text-slate-600 tw-text-sm">{{ item.sponsor_id || '—' }}</td>
                  <td class="tw-px-4 tw-py-4 tw-whitespace-nowrap tw-text-right tw-font-bold tw-text-emerald-700 tw-text-sm">
                    {{ currencySymbol }}{{ formatAmount(item.commission_amount ?? 0) }}
                  </td>
                  <td class="tw-px-4 tw-py-4 tw-whitespace-nowrap tw-text-slate-700 tw-text-sm">{{ formatDateTime(item.date) }}</td>
                </tr>
              </template>
              
              <tr v-if="commissionLog.length === 0">
                <td colspan="6" class="tw-px-6 tw-py-16 tw-text-center">
                  <div class="tw-w-16 tw-h-16 tw-bg-slate-50 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-4 tw-text-slate-300">
                    <i class="fas fa-wallet tw-text-3xl"></i>
                  </div>
                  <p class="tw-text-slate-500 tw-mb-0">No income history yet.</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>



    </div>
  </DashboardLayout>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import DashboardLayout from '../../components/DashboardLayout.vue'
import api from '../../services/api'

export default {
  name: 'AffiliateIncome',
  components: {
    DashboardLayout
  },
  setup() {
    const income = ref({
      today: 0,
      this_week: 0,
      this_month: 0,
      total: 0
    })
    const affiliateBalance = ref(0)
    const commissionLog = ref([])
    const breakdowns = ref(null)
    const selectedPeriod = ref('all_time')
    const currencySymbol = ref('₹')

    // Count-up animated values
    const incomeTodayAnim = ref(0)
    const incomeWeekAnim = ref(0)
    const incomeMonthAnim = ref(0)
    const incomeTotalAnim = ref(0)
    const affiliateBalanceAnim = ref(0)

    const bdDirectAnim = ref(0)
    const bdKycAnim = ref(0)
    const bdWithdrawAnim = ref(0)
    const bdUpgradeAnim = ref(0)
    const bdCertificateAnim = ref(0)
    const bdAdsPlanAnim = ref(0)
    const bdPartnerAnim = ref(0)
    const bdTotalAnim = ref(0)

    const animSeqMap = new WeakMap()
    const animateTo = (targetRef, to, duration = 1000) => {
      const seq = (animSeqMap.get(targetRef) || 0) + 1
      animSeqMap.set(targetRef, seq)
      const toNum = Number(to) || 0
      const start = performance.now()
      const step = (now) => {
        if (animSeqMap.get(targetRef) !== seq) return
        const p = Math.min(1, (now - start) / duration)
        const eased = 1 - Math.pow(1 - p, 3) // easeOutCubic
        targetRef.value = toNum * eased
        if (p < 1) requestAnimationFrame(step)
        else targetRef.value = toNum
      }
      requestAnimationFrame(step)
    }

    const animateBreakdown = () => {
      if (!currentBreakdown.value) return
      animateTo(bdDirectAnim, currentBreakdown.value.direct_registration_income)
      animateTo(bdKycAnim, currentBreakdown.value.kyc_income)
      animateTo(bdWithdrawAnim, currentBreakdown.value.withdrawal_fee_income)
      animateTo(bdUpgradeAnim, currentBreakdown.value.upgrade_income)
      animateTo(bdCertificateAnim, currentBreakdown.value.ad_certificate_income)
      animateTo(bdAdsPlanAnim, currentBreakdown.value.ads_plan_income)
      animateTo(bdPartnerAnim, currentBreakdown.value.partner_program_income)
      animateTo(bdTotalAnim, currentBreakdown.value.total)
    }

    const currentBreakdown = computed(() => {
      if (!breakdowns.value) return null
      const bd = breakdowns.value[selectedPeriod.value] || null
      return bd
    })

    const selectPeriod = (period) => {
      selectedPeriod.value = period
      // Short delay as currentBreakdown computed needs to update
      setTimeout(animateBreakdown, 0)
    }

    const formatAmount = (amount) => {
      if (!amount && amount !== 0) return '0.00'
      return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }

    const formatDateTime = (dateString) => {
      if (!dateString) return '-'
      return new Date(dateString).toLocaleString('en-IN')
    }

    const fetchAffiliateIncome = async () => {
      try {
        const response = await api.get('/affiliate-income')
        if (response.data.status === 'success' && response.data.data) {
          income.value = response.data.data.income || income.value
          affiliateBalance.value = response.data.data.affiliate_balance ?? response.data.data.affiliateBalance ?? 0
          commissionLog.value = response.data.data.commission_log || []
          breakdowns.value = response.data.data.breakdowns || null
          currencySymbol.value = response.data.data?.currency_symbol || response.data.currency_symbol || '₹'

          // Dashboard animations
          animateTo(incomeTodayAnim, income.value.today)
          animateTo(incomeWeekAnim, income.value.this_week)
          animateTo(incomeMonthAnim, income.value.this_month)
          animateTo(incomeTotalAnim, income.value.total)
          animateTo(affiliateBalanceAnim, affiliateBalance.value)

          // Breakdown animations
          setTimeout(animateBreakdown, 50)
        }
      } catch (error) {
        console.error('Error loading affiliate income:', error)
      }
    }

    onMounted(() => {
      fetchAffiliateIncome()
    })

    return {
      income,
      affiliateBalance,
      commissionLog,
      breakdowns,
      selectedPeriod,
      currentBreakdown,
      currencySymbol,
      formatAmount,
      formatDateTime,
      incomeTodayAnim,
      incomeWeekAnim,
      incomeMonthAnim,
      incomeTotalAnim,
      affiliateBalanceAnim,
      bdDirectAnim,
      bdKycAnim,
      bdWithdrawAnim,
      bdUpgradeAnim,
      bdCertificateAnim,
      bdAdsPlanAnim,
      bdPartnerAnim,
      bdTotalAnim,
      selectPeriod
    }
  }
}
</script>
