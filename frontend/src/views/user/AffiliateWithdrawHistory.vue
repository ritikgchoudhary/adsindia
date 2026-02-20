<template>
  <DashboardLayout page-title="Affiliate Withdraw Log" :dark-theme="true">
    <div class="tw-grid tw-grid-cols-1 tw-gap-6">
      <div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-slate-200">

        <div class="tw-p-5 tw-border-b tw-border-slate-200 tw-bg-slate-50/50 tw-flex tw-flex-col md:tw-flex-row tw-items-center tw-justify-between tw-gap-4">
          <h5 class="tw-text-slate-900 tw-font-bold tw-text-lg tw-m-0 tw-flex tw-items-center">
            <i class="fas fa-history tw-mr-2 tw-text-emerald-600"></i>Affiliate Withdraw History
          </h5>
          <div class="tw-flex tw-items-center tw-gap-3 tw-w-full md:tw-w-auto">
            <router-link to="/user/affiliate-withdraw" class="tw-inline-flex tw-items-center tw-gap-2 tw-px-4 tw-py-2 tw-bg-emerald-600 tw-text-white tw-font-bold tw-rounded-xl tw-shadow-lg tw-shadow-emerald-500/30 hover:tw-bg-emerald-700 tw-no-underline tw-transition-all">
              Withdraw (Affiliate)
            </router-link>
            <form class="tw-flex-1 md:tw-flex-none" @submit.prevent="handleSearch">
              <div class="tw-relative tw-flex tw-items-center">
                <input
                  type="search"
                  v-model="searchQuery"
                  placeholder="Search by transaction..."
                  class="tw-w-full md:tw-w-64 tw-pl-4 tw-pr-10 tw-py-2.5 tw-bg-white tw-border tw-border-slate-300 tw-rounded-xl tw-text-sm focus:tw-outline-none focus:tw-border-emerald-500 focus:tw-ring-4 focus:tw-ring-emerald-500/10 tw-transition-all"
                >
                <button
                  type="submit"
                  class="tw-absolute tw-right-2 tw-w-8 tw-h-8 tw-flex tw-items-center tw-justify-center tw-bg-emerald-600 tw-text-white tw-rounded-lg tw-transition-colors hover:tw-bg-emerald-700 tw-border-0 tw-cursor-pointer"
                >
                  <i class="fas fa-search tw-text-xs"></i>
                </button>
              </div>
            </form>
          </div>
        </div>

        <div class="tw-overflow-x-auto">
          <table class="tw-w-full tw-text-sm tw-border-collapse">
            <thead>
              <tr class="tw-bg-slate-50 tw-border-b tw-border-slate-200">
                <th class="tw-px-6 tw-py-4 tw-text-left tw-text-xs tw-font-bold tw-uppercase tw-text-slate-500 tw-tracking-wider">Gateway / Transaction</th>
                <th class="tw-px-6 tw-py-4 tw-text-left tw-text-xs tw-font-bold tw-uppercase tw-text-slate-500 tw-tracking-wider">Initiated</th>
                <th class="tw-px-6 tw-py-4 tw-text-left tw-text-xs tw-font-bold tw-uppercase tw-text-slate-500 tw-tracking-wider">Amount</th>
                <th class="tw-px-6 tw-py-4 tw-text-left tw-text-xs tw-font-bold tw-uppercase tw-text-slate-500 tw-tracking-wider">Conversion</th>
                <th class="tw-px-6 tw-py-4 tw-text-left tw-text-xs tw-font-bold tw-uppercase tw-text-slate-500 tw-tracking-wider">Status</th>
                <th class="tw-px-6 tw-py-4 tw-text-center tw-text-xs tw-font-bold tw-uppercase tw-text-slate-500 tw-tracking-wider">Action</th>
              </tr>
            </thead>
            <tbody class="tw-divide-y tw-divide-slate-100">
              <tr
                v-for="withdraw in withdraws"
                :key="withdraw?.id || Math.random()"
                v-show="withdraw && withdraw.id"
                class="hover:tw-bg-slate-50/80 hover:tw-text-slate-900 tw-transition-colors"
              >
                <td class="tw-px-6 tw-py-4">
                  <span class="tw-block tw-font-bold tw-text-slate-900 tw-mb-1">{{ withdraw.method?.name || '–' }}</span>
                  <span class="tw-block tw-text-xs tw-font-mono tw-text-slate-500 tw-bg-slate-100 tw-px-2 tw-py-0.5 tw-rounded tw-w-fit">{{ withdraw.trx }}</span>
                </td>
                <td class="tw-px-6 tw-py-4">
                  <div class="tw-font-semibold tw-text-slate-800">{{ formatDateTime(withdraw.created_at) }}</div>
                  <div class="tw-text-xs tw-text-slate-500">{{ withdraw.created_at_human }}</div>
                </td>
                <td class="tw-px-6 tw-py-4">
                  <div class="tw-font-bold tw-text-slate-900">{{ currencySymbol }}{{ formatAmount(withdraw.amount) }}</div>
                  <div class="tw-text-xs tw-text-red-500" :title="'Charge: ' + currencySymbol + formatAmount(withdraw.charge)">
                    − {{ currencySymbol }}{{ formatAmount(withdraw.charge) }}
                  </div>
                  <div class="tw-text-xs tw-font-semibold tw-text-emerald-600 tw-mt-0.5">
                    Net: {{ currencySymbol }}{{ formatAmount((withdraw.amount || 0) - (withdraw.charge || 0)) }}
                  </div>
                </td>
                <td class="tw-px-6 tw-py-4">
                  <div class="tw-text-xs tw-text-slate-500 tw-mb-0.5">1 = {{ formatAmount(withdraw.rate, false) }} {{ withdraw.currency }}</div>
                  <div class="tw-font-bold tw-text-slate-900">{{ formatAmount(withdraw.final_amount, false) }} {{ withdraw.currency }}</div>
                </td>
                <td class="tw-px-6 tw-py-4">
                  <div class="status-badge-wrapper" v-html="withdraw.status_badge"></div>
                </td>
                <td class="tw-px-6 tw-py-4 tw-text-center">
                  <button
                    type="button"
                    class="tw-w-9 tw-h-9 tw-inline-flex tw-items-center tw-justify-center tw-rounded-lg tw-bg-slate-100 tw-text-emerald-700 hover:tw-bg-emerald-600 hover:tw-text-white tw-transition-all tw-border-0 tw-cursor-pointer"
                    @click="showDetails(withdraw)"
                    title="View details"
                  >
                    <i class="fas fa-eye"></i>
                  </button>
                </td>
              </tr>
              <tr v-if="withdraws.length === 0">
                <td colspan="6" class="tw-px-6 tw-py-16 tw-text-center">
                  <div class="tw-w-16 tw-h-16 tw-bg-slate-50 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-4">
                    <i class="fas fa-wallet tw-text-3xl tw-text-slate-300"></i>
                  </div>
                  <h3 class="tw-text-slate-900 tw-font-bold tw-text-lg tw-mb-1">No affiliate withdrawal history yet</h3>
                  <div class="tw-mt-4">
                    <router-link to="/user/affiliate-withdraw" class="tw-inline-flex tw-items-center tw-gap-2 tw-px-5 tw-py-2.5 tw-bg-emerald-600 tw-text-white tw-font-bold tw-rounded-xl tw-shadow-lg tw-shadow-emerald-500/30 hover:tw-bg-emerald-700 tw-no-underline tw-transition-all">
                      Withdraw (Affiliate)
                    </router-link>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div v-if="showModal" class="tw-fixed tw-inset-0 tw-z-[60] tw-flex tw-items-center tw-justify-center tw-px-4">
      <div class="tw-absolute tw-inset-0 tw-bg-black/60 tw-backdrop-blur-sm" @click="showModal = false"></div>
      <div class="tw-bg-white tw-rounded-2xl tw-shadow-2xl tw-w-full tw-max-w-md tw-relative tw-z-10 tw-overflow-hidden tw-animate-fade-in-up">
        <div class="tw-bg-emerald-600 tw-p-5 tw-flex tw-items-center tw-justify-between">
          <h5 class="tw-text-white tw-font-bold tw-text-lg tw-m-0">Withdrawal Details</h5>
          <button @click="showModal = false" class="tw-text-white/80 hover:tw-text-white tw-bg-transparent tw-border-0 tw-cursor-pointer">
            <i class="fas fa-times tw-text-xl"></i>
          </button>
        </div>

        <div class="tw-p-6">
          <ul class="tw-space-y-3 tw-mb-0 tw-list-none tw-p-0">
            <li v-if="selectedWithdrawDetails.length === 0" class="tw-text-center tw-text-slate-500 tw-italic">
              No additional details available.
            </li>
            <li v-for="(info, index) in selectedWithdrawDetails" :key="index" class="tw-flex tw-items-center tw-justify-between tw-border-b tw-border-slate-100 tw-pb-2 last:tw-border-0 last:tw-pb-0">
              <span class="tw-font-semibold tw-text-slate-600 tw-text-sm">{{ info.name }}</span>
              <span v-if="info.type !== 'file'" class="tw-font-bold tw-text-slate-900 tw-text-sm tw-text-right">{{ info.value }}</span>
              <a v-else :href="info.value" target="_blank" class="tw-text-emerald-700 hover:tw-underline tw-text-sm tw-font-medium">
                <i class="far fa-file tw-mr-1"></i>Attachment
              </a>
            </li>
          </ul>

          <div v-if="selectedWithdrawFeedback" class="tw-mt-6 tw-bg-blue-50 tw-border-l-4 tw-border-blue-500 tw-p-4 tw-rounded-r-lg">
            <strong class="tw-block tw-text-blue-800 tw-text-sm tw-mb-1">Admin Feedback / Rejection Reason</strong>
            <p class="tw-text-blue-700 tw-text-sm tw-m-0 tw-leading-relaxed">{{ selectedWithdrawFeedback }}</p>
          </div>

          <div class="tw-mt-6">
            <button
              @click="showModal = false"
              class="tw-w-full tw-py-3 tw-bg-slate-100 hover:tw-bg-slate-200 tw-text-slate-700 tw-font-bold tw-rounded-xl tw-transition-colors tw-border-0 tw-cursor-pointer"
            >
              Close
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
  name: 'AffiliateWithdrawHistory',
  components: { DashboardLayout },
  setup() {
    const withdraws = ref([])
    const searchQuery = ref('')
    const showModal = ref(false)
    const selectedWithdrawDetails = ref([])
    const selectedWithdrawFeedback = ref('')
    const currencySymbol = ref('₹')

    const formatAmount = (amount) => {
      if (!amount && amount !== 0) return '0.00'
      return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }

    const formatDateTime = (dateString) => {
      if (!dateString) return '-'
      const date = new Date(dateString)
      const year = date.getFullYear()
      const month = String(date.getMonth() + 1).padStart(2, '0')
      const day = String(date.getDate()).padStart(2, '0')
      const hour12 = date.getHours() % 12 || 12
      const minutes = String(date.getMinutes()).padStart(2, '0')
      const ampm = date.getHours() >= 12 ? 'PM' : 'AM'
      return `${year}-${month}-${day} ${hour12}:${minutes} ${ampm}`
    }

    const showDetails = (withdraw) => {
      selectedWithdrawDetails.value = withdraw.withdraw_information || []
      selectedWithdrawFeedback.value = withdraw.admin_feedback || ''
      showModal.value = true
    }

    const fetchWithdraws = async () => {
      try {
        const params = {}
        if (searchQuery.value) params.search = searchQuery.value
        const response = await api.get('/affiliate/withdraw/history', { params })
        if (response.data.status === 'success') {
          withdraws.value = response.data.data?.data ?? response.data.data ?? []
          if (response.data.data?.currency_symbol) currencySymbol.value = response.data.data.currency_symbol
        }
      } catch (error) {
        console.error('Error loading affiliate withdraws:', error)
      }
    }

    const handleSearch = () => fetchWithdraws()

    onMounted(() => {
      fetchWithdraws()
    })

    return {
      withdraws,
      searchQuery,
      showModal,
      selectedWithdrawDetails,
      selectedWithdrawFeedback,
      currencySymbol,
      formatAmount,
      formatDateTime,
      showDetails,
      handleSearch
    }
  }
}
</script>

<style>
.status-badge-wrapper .badge {
  @apply tw-px-3 tw-py-1 tw-rounded-lg tw-text-xs tw-font-bold tw-uppercase;
}
.status-badge-wrapper .badge-success {
  @apply tw-bg-emerald-100 tw-text-emerald-700;
}
.status-badge-wrapper .badge-warning {
  @apply tw-bg-amber-100 tw-text-amber-700;
}
.status-badge-wrapper .badge-danger {
  @apply tw-bg-red-100 tw-text-red-700;
}
.status-badge-wrapper .badge-primary, .status-badge-wrapper .badge-info {
  @apply tw-bg-blue-100 tw-text-blue-700;
}
</style>

