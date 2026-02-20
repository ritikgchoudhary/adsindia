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
              <h3 class="tw-text-2xl tw-font-extrabold tw-mb-1">{{ currencySymbol }}{{ formatAmount(income.today) }}</h3>
              <p class="tw-text-indigo-100 tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-m-0">Today Income</p>
            </div>
            
            <div class="tw-bg-gradient-to-br tw-from-cyan-500 tw-to-blue-600 tw-rounded-2xl tw-p-6 tw-text-white tw-shadow-lg tw-shadow-cyan-500/20 tw-relative tw-overflow-hidden hover:-tw-translate-y-1 tw-transition-transform tw-duration-300">
              <div class="tw-absolute tw-top-0 tw-left-0 tw-w-full tw-h-full tw-bg-white/5 tw-pointer-events-none"></div>
              <div class="tw-w-12 tw-h-12 tw-rounded-xl tw-bg-white/20 tw-flex tw-items-center tw-justify-center tw-mb-4 tw-backdrop-blur-sm">
                <i class="fas fa-calendar-week tw-text-xl"></i>
              </div>
              <h3 class="tw-text-2xl tw-font-extrabold tw-mb-1">{{ currencySymbol }}{{ formatAmount(income.this_week) }}</h3>
              <p class="tw-text-cyan-100 tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-m-0">This Week</p>
            </div>
            
            <div class="tw-bg-gradient-to-br tw-from-amber-400 tw-to-orange-500 tw-rounded-2xl tw-p-6 tw-text-white tw-shadow-lg tw-shadow-amber-500/20 tw-relative tw-overflow-hidden hover:-tw-translate-y-1 tw-transition-transform tw-duration-300">
              <div class="tw-absolute tw-top-0 tw-left-0 tw-w-full tw-h-full tw-bg-white/5 tw-pointer-events-none"></div>
              <div class="tw-w-12 tw-h-12 tw-rounded-xl tw-bg-white/20 tw-flex tw-items-center tw-justify-center tw-mb-4 tw-backdrop-blur-sm">
                <i class="fas fa-calendar-alt tw-text-xl"></i>
              </div>
              <h3 class="tw-text-2xl tw-font-extrabold tw-mb-1">{{ currencySymbol }}{{ formatAmount(income.this_month) }}</h3>
              <p class="tw-text-amber-100 tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-m-0">This Month</p>
            </div>
            
            <div class="tw-bg-gradient-to-br tw-from-emerald-500 tw-to-teal-600 tw-rounded-2xl tw-p-6 tw-text-white tw-shadow-lg tw-shadow-emerald-500/20 tw-relative tw-overflow-hidden hover:-tw-translate-y-1 tw-transition-transform tw-duration-300">
              <div class="tw-absolute tw-top-0 tw-left-0 tw-w-full tw-h-full tw-bg-white/5 tw-pointer-events-none"></div>
              <div class="tw-w-12 tw-h-12 tw-rounded-xl tw-bg-white/20 tw-flex tw-items-center tw-justify-center tw-mb-4 tw-backdrop-blur-sm">
                <i class="fas fa-coins tw-text-xl"></i>
              </div>
              <h3 class="tw-text-2xl tw-font-extrabold tw-mb-1">{{ currencySymbol }}{{ formatAmount(income.total) }}</h3>
              <p class="tw-text-emerald-100 tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-m-0">Total Income</p>
            </div>

            <!-- Moved down (as requested): Affiliate Wallet Balance -->
            <div class="tw-bg-gradient-to-br tw-from-emerald-500 tw-to-green-600 tw-rounded-2xl tw-p-6 tw-text-white tw-shadow-lg tw-shadow-emerald-500/20 tw-relative tw-overflow-hidden hover:-tw-translate-y-1 tw-transition-transform tw-duration-300">
              <div class="tw-absolute tw-top-0 tw-left-0 tw-w-full tw-h-full tw-bg-white/5 tw-pointer-events-none"></div>
              <div class="tw-w-12 tw-h-12 tw-rounded-xl tw-bg-white/20 tw-flex tw-items-center tw-justify-center tw-mb-4 tw-backdrop-blur-sm">
                <i class="fas fa-wallet tw-text-xl"></i>
              </div>
              <h3 class="tw-text-2xl tw-font-extrabold tw-mb-1">{{ currencySymbol }}{{ formatAmount(affiliateBalance) }}</h3>
              <p class="tw-text-emerald-100 tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-m-0">Affiliate Wallet Balance</p>
            </div>

          </div>

          <!-- Breakdown -->
          <div v-if="breakdown" class="tw-mt-8">
            <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
              <h6 class="tw-text-slate-900 tw-font-extrabold tw-m-0 tw-flex tw-items-center tw-gap-2">
                <i class="fas fa-layer-group tw-text-indigo-600"></i> Commission Breakdown
              </h6>
              <div class="tw-text-xs tw-text-slate-500">Credited after successful confirmations</div>
            </div>
            <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 xl:tw-grid-cols-4 tw-gap-4">
              <div class="tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-slate-50 tw-p-5">
                <div class="tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-500">Direct Registration Income</div>
                <div class="tw-mt-2 tw-text-2xl tw-font-extrabold tw-text-slate-900">{{ currencySymbol }}{{ formatAmount(breakdown.direct_registration_income) }}</div>
              </div>
              <div class="tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-slate-50 tw-p-5">
                <div class="tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-500">KYC Income</div>
                <div class="tw-mt-2 tw-text-2xl tw-font-extrabold tw-text-slate-900">{{ currencySymbol }}{{ formatAmount(breakdown.kyc_income) }}</div>
              </div>
              <div class="tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-slate-50 tw-p-5">
                <div class="tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-500">Withdrawal Fee Income</div>
                <div class="tw-mt-2 tw-text-2xl tw-font-extrabold tw-text-slate-900">{{ currencySymbol }}{{ formatAmount(breakdown.withdrawal_fee_income) }}</div>
              </div>
              <div class="tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-slate-50 tw-p-5">
                <div class="tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-500">Upgrade Income</div>
                <div class="tw-mt-2 tw-text-2xl tw-font-extrabold tw-text-slate-900">{{ currencySymbol }}{{ formatAmount(breakdown.upgrade_income) }}</div>
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
                <th class="tw-px-6 tw-py-4 tw-text-left tw-text-xs tw-font-bold tw-text-slate-500 tw-uppercase tw-tracking-wider bg-transparent">User Name</th>
                <th class="tw-px-6 tw-py-4 tw-text-left tw-text-xs tw-font-bold tw-text-slate-500 tw-uppercase tw-tracking-wider bg-transparent">Transaction ID</th>
                <th class="tw-px-6 tw-py-4 tw-text-right tw-text-xs tw-font-bold tw-text-slate-500 tw-uppercase tw-tracking-wider bg-transparent">Amount</th>
                <th class="tw-px-6 tw-py-4 tw-text-right tw-text-xs tw-font-bold tw-text-slate-500 tw-uppercase tw-tracking-wider bg-transparent">Commission</th>
                <th class="tw-px-6 tw-py-4 tw-text-left tw-text-xs tw-font-bold tw-text-slate-500 tw-uppercase tw-tracking-wider bg-transparent">Type</th>
                <th class="tw-px-6 tw-py-4 tw-text-left tw-text-xs tw-font-bold tw-text-slate-500 tw-uppercase tw-tracking-wider bg-transparent">Date</th>
              </tr>
            </thead>
            <tbody class="tw-divide-y tw-divide-slate-200 tw-bg-white">
              <template v-for="item in commissionLog" :key="item?.id || Math.random()">
                <tr v-show="item && item.id" class="tw-transition-colors hover:tw-bg-slate-50 hover:tw-text-slate-800">
                  <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-font-bold tw-text-slate-900 tw-text-sm">{{ item.user_name || '–' }}</td>
                  <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-slate-700 tw-text-sm">
                    <code class="tw-font-mono">{{ item.transaction_id || '–' }}</code>
                  </td>
                  <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-right tw-font-bold tw-text-slate-800 tw-text-sm">
                    <span v-if="item.amount != null">{{ currencySymbol }}{{ formatAmount(item.amount) }}</span>
                    <span v-else class="tw-text-slate-400">–</span>
                  </td>
                  <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-right tw-font-bold tw-text-emerald-700 tw-text-sm">
                    {{ currencySymbol }}{{ formatAmount(item.commission_amount ?? 0) }}
                  </td>
                  <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-slate-700 tw-text-sm">{{ item.type || '–' }}</td>
                  <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-slate-700 tw-text-sm">{{ formatDateTime(item.date) }}</td>
                </tr>
              </template>
              
              <tr v-if="commissionLog.length === 0">
                <td colspan="6" class="tw-px-6 tw-py-16 tw-text-center">
                  <div class="tw-w-16 tw-h-16 tw-bg-slate-50 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-4 tw-text-slate-300">
                    <i class="fas fa-wallet tw-text-3xl"></i>
                  </div>
                  <p class="tw-text-slate-500 tw-mb-0">No income history yet. Refer friends to start earning!</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Withdrawal History -->
      <div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-slate-200 tw-overflow-hidden">
        <div class="tw-p-5 tw-border-b tw-border-slate-200 tw-bg-gradient-to-r tw-from-slate-50 tw-to-white">
          <h5 class="tw-text-slate-900 tw-font-bold tw-text-lg tw-m-0 tw-flex tw-items-center">
            <i class="fas fa-hand-holding-usd tw-mr-2 tw-text-emerald-600"></i>Withdrawal History
          </h5>
        </div>
        <div class="tw-overflow-x-auto">
          <table class="tw-w-full tw-border-collapse">
            <thead>
              <tr class="tw-bg-slate-50 tw-border-b tw-border-slate-200">
                <th class="tw-px-6 tw-py-4 tw-text-left tw-text-xs tw-font-bold tw-text-slate-500 tw-uppercase tw-tracking-wider bg-transparent">Date</th>
                <th class="tw-px-6 tw-py-4 tw-text-left tw-text-xs tw-font-bold tw-text-slate-500 tw-uppercase tw-tracking-wider bg-transparent">Amount</th>
                <th class="tw-px-6 tw-py-4 tw-text-left tw-text-xs tw-font-bold tw-text-slate-500 tw-uppercase tw-tracking-wider bg-transparent">Fee</th>
                <th class="tw-px-6 tw-py-4 tw-text-left tw-text-xs tw-font-bold tw-text-slate-500 tw-uppercase tw-tracking-wider bg-transparent">Net</th>
                <th class="tw-px-6 tw-py-4 tw-text-left tw-text-xs tw-font-bold tw-text-slate-500 tw-uppercase tw-tracking-wider bg-transparent">Status</th>
                <th class="tw-px-6 tw-py-4 tw-text-left tw-text-xs tw-font-bold tw-text-slate-500 tw-uppercase tw-tracking-wider bg-transparent">Reason</th>
              </tr>
            </thead>
            <tbody class="tw-divide-y tw-divide-slate-200 tw-bg-white">
              <tr v-for="w in withdrawals" :key="w?.id || Math.random()" class="tw-transition-colors hover:tw-bg-slate-50" v-show="w && w.id">
                <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-slate-600 tw-text-sm">
                  <div class="tw-font-semibold">{{ formatDateTime(w.created_at) }}</div>
                  <div class="tw-text-xs tw-text-slate-400">{{ w.created_at_human }}</div>
                </td>
                <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-font-bold tw-text-slate-800 tw-text-sm">{{ currencySymbol }}{{ formatAmount(w.amount) }}</td>
                <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-red-600 tw-text-sm">− {{ currencySymbol }}{{ formatAmount(w.fee) }}</td>
                <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-emerald-700 tw-font-bold tw-text-sm">{{ currencySymbol }}{{ formatAmount(w.net_amount) }}</td>
                <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-slate-700 tw-font-bold tw-text-sm">{{ w.status_text || '–' }}</td>
                <td class="tw-px-6 tw-py-4 tw-text-slate-600 tw-text-sm">
                  <span v-if="w.reason">{{ w.reason }}</span>
                  <span v-else class="tw-text-slate-400">–</span>
                </td>
              </tr>
              <tr v-if="withdrawals.length === 0">
                <td colspan="6" class="tw-px-6 tw-py-16 tw-text-center">
                  <div class="tw-w-16 tw-h-16 tw-bg-slate-50 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-4 tw-text-slate-300">
                    <i class="fas fa-hand-holding-usd tw-text-3xl"></i>
                  </div>
                  <p class="tw-text-slate-500 tw-mb-0">No withdrawal history yet.</p>
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
    const incomeHistory = ref([])
    const commissionLog = ref([])
    const affiliateEarning = ref(0)
    const referralCount = ref(0)
    const referralEarnings = ref({
      referral_commission: 0,
      downline_commission: 0,
      affiliate_commission: 0
    })
    const withdrawals = ref([])
    const breakdown = ref(null)
    const currencySymbol = ref('₹')

    const referralTotal = computed(() => {
      const r = referralEarnings.value || {}
      return (Number(r.referral_commission) || 0) + (Number(r.downline_commission) || 0) + (Number(r.affiliate_commission) || 0)
    })

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
          affiliateEarning.value = response.data.data.affiliate_earning || 0
          referralCount.value = response.data.data.referral_count || 0
          referralEarnings.value = response.data.data.referral_earnings || referralEarnings.value
          incomeHistory.value = response.data.data.history || []
          commissionLog.value = response.data.data.commission_log || []
          breakdown.value = response.data.data.breakdown || null
          withdrawals.value = response.data.data.withdrawals || []
          currencySymbol.value = response.data.data?.currency_symbol || response.data.currency_symbol || '₹'
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
      affiliateEarning,
      referralCount,
      referralEarnings,
      referralTotal,
      incomeHistory,
      commissionLog,
      withdrawals,
      breakdown,
      currencySymbol,
      formatAmount,
      formatDateTime
    }
  }
}
</script>
