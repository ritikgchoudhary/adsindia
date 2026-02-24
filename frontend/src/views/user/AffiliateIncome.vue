<template>
  <DashboardLayout page-title="Affiliate Income" :dark-theme="true">
    <div class="income-container">
      <div class="tw-grid tw-grid-cols-1 tw-gap-8">
      
      <!-- Income Overview -->
      <div class="tw-bg-white tw-rounded-xl sm:tw-rounded-2xl tw-shadow-sm tw-border tw-border-slate-200 tw-overflow-hidden">
        <div class="tw-p-3 sm:tw-p-5 tw-border-b tw-border-slate-200 tw-bg-gradient-to-r tw-from-slate-50 tw-to-white">
          <div class="tw-flex tw-flex-col md:tw-flex-row md:tw-items-center md:tw-justify-between tw-gap-2 sm:tw-gap-3">
            <h5 class="tw-text-slate-900 tw-font-bold tw-text-base sm:tw-text-lg tw-m-0 tw-flex tw-items-center">
              <i class="fas fa-chart-line tw-mr-2 tw-text-indigo-600"></i>Affiliate Dashboard
            </h5>
            <div class="tw-flex tw-gap-2">
              <router-link
                to="/user/account-kyc"
                class="tw-flex-1 sm:tw-flex-none tw-inline-flex tw-items-center tw-justify-center tw-gap-1 sm:tw-gap-2 tw-px-3 sm:tw-px-4 tw-py-1.5 sm:tw-py-2 tw-rounded-lg sm:tw-rounded-xl tw-bg-slate-900 tw-text-white tw-font-bold tw-text-[10px] sm:tw-text-sm tw-no-underline"
              >
                <i class="fas fa-user-shield"></i> KYC
              </router-link>
              <router-link
                to="/user/affiliate-withdraw"
                class="tw-flex-1 sm:tw-flex-none tw-inline-flex tw-items-center tw-justify-center tw-gap-1 sm:tw-gap-2 tw-px-3 sm:tw-px-4 tw-py-1.5 sm:tw-py-2 tw-rounded-lg sm:tw-rounded-xl tw-bg-emerald-600 tw-text-white tw-font-bold tw-text-[10px] sm:tw-text-sm tw-no-underline"
              >
                <i class="fas fa-hand-holding-usd"></i> Withdraw
              </router-link>
            </div>
          </div>
        </div>
        
        <div class="tw-p-4 sm:tw-p-6">
          <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-4 tw-gap-3 sm:tw-gap-6">
            
            <!-- Today -->
            <div class="tw-bg-gradient-to-r tw-from-indigo-600 tw-to-violet-700 tw-rounded-2xl tw-p-4 sm:tw-p-6 tw-text-white tw-shadow-lg tw-flex tw-items-center tw-justify-between tw-relative tw-overflow-hidden">
               <div class="tw-absolute -tw-left-2 tw-top-1/2 -tw-translate-y-1/2 tw-text-[4rem] sm:tw-text-[5rem] tw-font-serif tw-opacity-10 tw-pointer-events-none">₹</div>
               <div class="tw-z-10 tw-pl-4">
                 <div class="tw-text-[9px] sm:tw-text-[10px] tw-font-bold tw-uppercase tw-tracking-widest tw-opacity-80">Today's Income</div>
                 <div class="tw-text-xl sm:tw-text-3xl tw-font-black tw-leading-none tw-mt-1">{{ currencySymbol }}{{ formatAmount(incomeTodayAnim) }}</div>
               </div>
            </div>

            <!-- Week -->
            <div class="tw-bg-gradient-to-r tw-from-cyan-500 tw-to-blue-600 tw-rounded-2xl tw-p-4 sm:tw-p-6 tw-text-white tw-shadow-lg tw-flex tw-items-center tw-justify-between tw-relative tw-overflow-hidden">
               <div class="tw-absolute -tw-left-2 tw-top-1/2 -tw-translate-y-1/2 tw-text-[4rem] sm:tw-text-[5rem] tw-font-serif tw-opacity-10 tw-pointer-events-none">₹</div>
               <div class="tw-z-10 tw-pl-4">
                 <div class="tw-text-[9px] sm:tw-text-[10px] tw-font-bold tw-uppercase tw-tracking-widest tw-opacity-80">This Week</div>
                 <div class="tw-text-xl sm:tw-text-3xl tw-font-black tw-leading-none tw-mt-1">{{ currencySymbol }}{{ formatAmount(incomeWeekAnim) }}</div>
               </div>
            </div>

            <!-- Month -->
            <div class="tw-bg-gradient-to-r tw-from-amber-400 tw-to-orange-500 tw-rounded-2xl tw-p-4 sm:tw-p-6 tw-text-white tw-shadow-lg tw-flex tw-items-center tw-justify-between tw-relative tw-overflow-hidden">
               <div class="tw-absolute -tw-left-2 tw-top-1/2 -tw-translate-y-1/2 tw-text-[4rem] sm:tw-text-[5rem] tw-font-serif tw-opacity-10 tw-pointer-events-none">₹</div>
               <div class="tw-z-10 tw-pl-4">
                 <div class="tw-text-[9px] sm:tw-text-[10px] tw-font-bold tw-uppercase tw-tracking-widest tw-opacity-80">This Month</div>
                 <div class="tw-text-xl sm:tw-text-3xl tw-font-black tw-leading-none tw-mt-1">{{ currencySymbol }}{{ formatAmount(incomeMonthAnim) }}</div>
               </div>
            </div>

            <!-- Total -->
            <div class="tw-bg-gradient-to-r tw-from-emerald-500 tw-to-teal-600 tw-rounded-2xl tw-p-4 sm:tw-p-6 tw-text-white tw-shadow-lg tw-flex tw-items-center tw-justify-between tw-relative tw-overflow-hidden">
               <div class="tw-absolute -tw-left-2 tw-top-1/2 -tw-translate-y-1/2 tw-text-[4rem] sm:tw-text-[5rem] tw-font-serif tw-opacity-10 tw-pointer-events-none">₹</div>
               <div class="tw-z-10 tw-pl-4">
                 <div class="tw-text-[9px] sm:tw-text-[10px] tw-font-bold tw-uppercase tw-tracking-widest tw-opacity-80">Total Income</div>
                 <div class="tw-text-xl sm:tw-text-3xl tw-font-black tw-leading-none tw-mt-1">{{ currencySymbol }}{{ formatAmount(incomeTotalAnim) }}</div>
               </div>
            </div>

            <!-- Balance -->
            <div class="tw-bg-gradient-to-r tw-from-[#2e1065] tw-to-[#0f172a] tw-rounded-2xl tw-p-4 sm:tw-p-6 tw-text-white tw-shadow-lg tw-flex tw-items-center tw-justify-between tw-relative tw-overflow-hidden tw-col-span-1 md:tw-col-span-1">
               <div class="tw-absolute -tw-left-2 tw-top-1/2 -tw-translate-y-1/2 tw-text-[4rem] sm:tw-text-[5rem] tw-font-serif tw-opacity-10 tw-pointer-events-none">₹</div>
               <div class="tw-z-10 tw-pl-4">
                 <div class="tw-text-[9px] sm:tw-text-[10px] tw-font-bold tw-uppercase tw-tracking-widest tw-opacity-80">Affiliate Balance</div>
                 <div class="tw-text-xl sm:tw-text-3xl tw-font-black tw-leading-none tw-mt-1">{{ currencySymbol }}{{ formatAmount(affiliateBalanceAnim) }}</div>
               </div>
            </div>
          </div>

          <!-- Breakdown -->
          <div class="tw-mt-6 sm:tw-mt-8">
            <div class="tw-flex tw-flex-col md:tw-flex-row md:tw-items-center tw-justify-between tw-mb-4 sm:tw-mb-6 tw-gap-3">
              <h6 class="tw-text-slate-900 tw-font-extrabold tw-m-0 tw-flex tw-items-center tw-gap-2 tw-text-sm sm:tw-text-base">
                <i class="fas fa-layer-group tw-text-indigo-600"></i> Breakdown
              </h6>
              
              <div class="tw-grid tw-grid-cols-4 tw-bg-slate-100 tw-p-1 tw-rounded-xl tw-gap-1 tw-w-full sm:tw-w-auto">
                <button 
                  v-for="p in ['today', 'this_week', 'this_month', 'all_time']" :key="p"
                  @click="selectPeriod(p)"
                  :class="[
                    'tw-px-1 sm:tw-px-4 tw-py-2 sm:tw-py-1.5 tw-text-[10px] sm:tw-text-xs tw-font-extrabold tw-rounded-lg tw-transition-all tw-cursor-pointer tw-border-0 tw-flex tw-items-center tw-justify-center',
                    selectedPeriod === p ? 'tw-bg-indigo-600 tw-text-white tw-shadow-sm' : 'tw-bg-transparent tw-text-slate-600'
                  ]"
                >{{ p === 'today' ? 'Today' : p === 'this_week' ? 'Week' : p === 'this_month' ? 'Month' : 'All Time' }}</button>
              </div>
            </div>

            <div v-if="currentBreakdown" class="tw-grid tw-grid-cols-2 lg:tw-grid-cols-4 tw-gap-3 sm:tw-gap-4">
              <div v-for="(val, label) in { 'Direct': bdDirectAnim, 'KYC': bdKycAnim, 'Withdraw': bdWithdrawAnim, 'Upgrade': bdUpgradeAnim, 'Certificate': bdCertificateAnim, 'Ads Plan': bdAdsPlanAnim, 'Partner': bdPartnerAnim, 'Total': bdTotalAnim }" :key="label" 
                   class="tw-rounded-xl tw-border tw-border-slate-100 tw-bg-slate-50/50 tw-p-4 sm:tw-p-5">
                <div class="tw-text-[10px] sm:tw-text-[11px] tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-400 tw-mb-1">{{ label }}</div>
                <div class="tw-text-base sm:tw-text-xl tw-font-extrabold tw-text-slate-900">₹{{ formatAmount(val) }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Income History -->
      <div class="tw-bg-white tw-rounded-xl sm:tw-rounded-2xl tw-shadow-sm tw-border tw-border-slate-200 tw-overflow-hidden">
        <div class="tw-p-3 sm:tw-p-5 tw-border-b tw-border-slate-200 tw-bg-gradient-to-r tw-from-slate-50 tw-to-white">
          <h5 class="tw-text-slate-900 tw-font-bold tw-text-base sm:tw-text-lg tw-m-0 tw-flex tw-items-center">
            <i class="fas fa-history tw-mr-2 tw-text-indigo-600"></i>Income History
          </h5>
        </div>
        
        <!-- Desktop Table -->
        <div class="tw-hidden md:tw-block tw-overflow-x-auto">
          <table class="tw-w-full tw-border-collapse">
            <thead>
              <tr class="tw-bg-slate-50 tw-border-b tw-border-slate-200">
                <th class="tw-px-6 tw-py-4 tw-text-left tw-text-xs tw-font-bold tw-text-slate-500 tw-uppercase tw-tracking-wider bg-transparent tw-whitespace-nowrap">Affiliate ID</th>
                <th class="tw-px-6 tw-py-4 tw-text-left tw-text-xs tw-font-bold tw-text-slate-500 tw-uppercase tw-tracking-wider bg-transparent tw-whitespace-nowrap">Name</th>
                <th class="tw-px-6 tw-py-4 tw-text-center tw-text-xs tw-font-bold tw-text-slate-500 tw-uppercase tw-tracking-wider bg-transparent tw-whitespace-nowrap">Type</th>
                <th class="tw-px-6 tw-py-4 tw-text-center tw-text-xs tw-font-bold tw-text-slate-500 tw-uppercase tw-tracking-wider bg-transparent tw-whitespace-nowrap">Commission</th>
                <th class="tw-px-6 tw-py-4 tw-text-left tw-text-xs tw-font-bold tw-text-slate-500 tw-uppercase tw-tracking-wider bg-transparent tw-whitespace-nowrap">Date</th>
              </tr>
            </thead>
            <tbody class="tw-divide-y tw-divide-slate-200 tw-bg-white">
              <template v-for="item in (commissionLog || [])" :key="item?.id || Math.random()">
                <tr v-show="item && item.id" class="hover:tw-bg-slate-50 tw-transition-colors">
                  <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-font-mono tw-text-slate-600 tw-text-sm">{{ item.affiliate_id || '—' }}</td>
                  <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-font-bold tw-text-slate-900 tw-text-sm">{{ item.name || '—' }}</td>
                  <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-center tw-text-slate-700 tw-text-sm">
                    <span class="tw-px-2 tw-py-1 tw-rounded-lg tw-bg-slate-100 tw-text-[10px] tw-font-bold tw-text-slate-600">{{ item.type || '—' }}</span>
                  </td>
                  <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-center tw-font-black tw-text-emerald-600 tw-text-sm">
                    {{ currencySymbol }}{{ formatAmount(item.commission_amount ?? 0) }}
                  </td>
                  <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-slate-700 tw-text-sm">{{ formatDateTime(item.date) }}</td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>

        <!-- Mobile view List -->
        <div class="md:tw-hidden tw-p-3 tw-space-y-3">
          <div v-for="item in commissionLog" :key="item.id" class="tw-bg-white tw-border tw-border-slate-100 tw-rounded-xl tw-p-3">
            <div class="tw-flex tw-justify-between tw-items-start tw-mb-2">
              <div>
                <div class="tw-text-slate-900 tw-font-bold tw-text-sm">{{ item.name }}</div>
                <div class="tw-text-[10px] tw-text-slate-400 tw-font-mono">ID: {{ item.affiliate_id }}</div>
              </div>
              <div class="tw-text-right">
                <div class="tw-text-emerald-600 tw-font-bold tw-text-sm">{{ currencySymbol }}{{ formatAmount(item.commission_amount) }}</div>
                <span class="tw-text-[9px] tw-font-bold tw-uppercase tw-text-slate-400">{{ item.type }}</span>
              </div>
            </div>
            <div class="tw-text-[10px] tw-text-slate-500 tw-border-t tw-border-slate-50 tw-pt-2">
              {{ formatDateTime(item.date) }}
            </div>
          </div>
          <div v-if="commissionLog.length === 0" class="tw-py-10 tw-text-center">
             <p class="tw-text-slate-400 tw-text-xs">No records found</p>
          </div>
        </div>
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

<style scoped>
.income-container {
  max-width: 1200px;
  margin: 0 auto;
  padding-bottom: 3rem;
}

@media (max-width: 640px) {
  /* Cards & Spacing */
  .tw-p-4.sm\:tw-p-6 { padding: 1rem !important; }
  .tw-rounded-[32px], .tw-rounded-2xl { border-radius: 1.25rem !important; }
  .tw-gap-8 { gap: 1rem !important; }
  
  /* Overview Cards */
  .tw-grid-cols-4 { gap: 0.75rem !important; }
  .tw-text-xl.sm\:tw-text-3xl { font-size: 1.35rem !important; }
  .tw-text-\[9px\] { font-size: 8px !important; }
  .tw-p-4.sm\:tw-p-6 { padding: 0.85rem 1rem !important; }
  
  /* Breakdown Grid */
  .tw-grid-cols-2 { gap: 0.75rem !important; }
  .tw-p-4.sm\:tw-p-5 { padding: 0.85rem !important; border-radius: 1rem !important; }
  .tw-text-base.sm\:tw-text-xl { font-size: 1rem !important; }
  
  /* History Card */
  .tw-p-3.sm\:tw-p-5 { padding: 0.85rem 1rem !important; }
  h5.tw-text-base { font-size: 1rem !important; }
}
</style>
