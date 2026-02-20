<template>
  <DashboardLayout page-title="Transactions" :dark-theme="true">
    <div class="tw-grid tw-grid-cols-1 tw-gap-6">
      <div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-slate-200">
        <!-- Header -->
        <div class="tw-p-5 tw-border-b tw-border-slate-200 tw-bg-slate-50/50 tw-flex tw-flex-col md:tw-flex-row tw-items-center tw-justify-between tw-gap-4">
          <h5 class="tw-text-slate-900 tw-font-bold tw-text-lg tw-m-0 tw-flex tw-items-center">
            <i class="fas fa-exchange-alt tw-mr-2 tw-text-indigo-600"></i>Transaction History
          </h5>
          <button 
            type="button" 
            class="tw-inline-flex tw-items-center tw-gap-2 tw-px-4 tw-py-2 tw-rounded-xl tw-text-sm tw-font-semibold tw-transition-all tw-border tw-cursor-pointer"
            :class="showFilter ? 'tw-bg-indigo-600 tw-text-white tw-border-indigo-600 tw-shadow-md tw-shadow-indigo-500/20' : 'tw-bg-white tw-text-slate-600 tw-border-slate-200 hover:tw-bg-slate-50 hover:tw-text-slate-900'"
            @click="toggleFilter"
          >
            <i class="fas fa-filter"></i> Filter
          </button>
        </div>

        <!-- Filter Panel -->
        <div v-show="showFilter" class="tw-bg-white tw-border-b tw-border-slate-200 tw-p-5 tw-bg-slate-50/30">
          <form @submit.prevent="handleFilter">
            <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-4 tw-gap-4">
              <div class="tw-flex tw-flex-col tw-gap-2">
                <label class="tw-text-xs tw-font-bold tw-uppercase tw-text-slate-400 tw-tracking-wide">Transaction Number</label>
                <input 
                  type="search" 
                  v-model="filters.search" 
                  placeholder="TRX-123..." 
                  class="tw-w-full tw-px-4 tw-py-2.5 tw-bg-white tw-border tw-border-slate-200 tw-rounded-lg tw-text-slate-700 tw-text-sm focus:tw-outline-none focus:tw-border-indigo-500 focus:tw-ring-2 focus:tw-ring-indigo-500/10 tw-transition-all"
                >
              </div>
              <div class="tw-flex tw-flex-col tw-gap-2">
                <label class="tw-text-xs tw-font-bold tw-uppercase tw-text-slate-400 tw-tracking-wide">Type</label>
                <select 
                  v-model="filters.trx_type" 
                  class="tw-w-full tw-px-4 tw-py-2.5 tw-bg-white tw-border tw-border-slate-200 tw-rounded-lg tw-text-slate-700 tw-text-sm focus:tw-outline-none focus:tw-border-indigo-500 focus:tw-ring-2 focus:tw-ring-indigo-500/10 tw-transition-all"
                >
                  <option value="">All Types</option>
                  <option value="+">Plus (+)</option>
                  <option value="-">Minus (−)</option>
                </select>
              </div>
              <div class="tw-flex tw-flex-col tw-gap-2">
                <label class="tw-text-xs tw-font-bold tw-uppercase tw-text-slate-400 tw-tracking-wide">Remark</label>
                <select 
                  v-model="filters.remark" 
                  class="tw-w-full tw-px-4 tw-py-2.5 tw-bg-white tw-border tw-border-slate-200 tw-rounded-lg tw-text-slate-700 tw-text-sm focus:tw-outline-none focus:tw-border-indigo-500 focus:tw-ring-2 focus:tw-ring-indigo-500/10 tw-transition-all"
                >
                  <option value="">All Remarks</option>
                  <option v-for="remark in remarks" :key="remark" :value="remark">{{ formatRemark(remark) }}</option>
                </select>
              </div>
              <div class="tw-flex tw-items-end">
                <button 
                  type="submit" 
                  class="tw-w-full tw-py-2.5 tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-font-semibold tw-rounded-lg tw-transition-all tw-border-0 tw-cursor-pointer tw-flex tw-items-center tw-justify-center tw-gap-2 tw-shadow-lg tw-shadow-indigo-500/20"
                >
                  <i class="fas fa-search"></i> Apply Filter
                </button>
              </div>
            </div>
          </form>
        </div>

        <!-- Table -->
        <div class="tw-overflow-x-auto">
          <table class="tw-w-full tw-text-sm tw-borer-collapse">
            <thead>
              <tr class="tw-bg-slate-50 tw-border-b tw-border-slate-200">
                <th class="tw-px-6 tw-py-4 tw-text-left tw-text-xs tw-font-bold tw-uppercase tw-text-slate-500 tw-tracking-wider">Transaction ID</th>
                <th class="tw-px-6 tw-py-4 tw-text-left tw-text-xs tw-font-bold tw-uppercase tw-text-slate-500 tw-tracking-wider">Date</th>
                <th class="tw-px-6 tw-py-4 tw-text-left tw-text-xs tw-font-bold tw-uppercase tw-text-slate-500 tw-tracking-wider">Amount</th>
                <th class="tw-px-6 tw-py-4 tw-text-left tw-text-xs tw-font-bold tw-uppercase tw-text-slate-500 tw-tracking-wider">Balance</th>
                <th class="tw-px-6 tw-py-4 tw-text-left tw-text-xs tw-font-bold tw-uppercase tw-text-slate-500 tw-tracking-wider">Details</th>
              </tr>
            </thead>
            <tbody class="tw-divide-y tw-divide-slate-100">
              <tr 
                v-for="trx in transactions" 
                :key="trx?.id || Math.random()" 
                v-show="trx && trx.id"
                class="hover:tw-bg-slate-50/80 tw-transition-colors"
              >
                <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                  <span class="tw-font-mono tw-text-xs tw-font-semibold tw-bg-slate-100 tw-text-slate-700 tw-px-2 tw-py-1 tw-rounded">{{ trx.trx }}</span>
                </td>
                <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                  <div class="tw-font-semibold tw-text-slate-900">{{ formatDateTime(trx.created_at) }}</div>
                  <div class="tw-text-xs tw-text-slate-500">{{ trx.created_at_human }}</div>
                </td>
                <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                  <span class="tw-font-bold tw-text-[15px]" :class="trx.trx_type === '+' ? 'tw-text-emerald-600' : 'tw-text-red-500'">
                    {{ trx.trx_type }} {{ currencySymbol }}{{ formatAmount(trx.amount) }}
                  </span>
                </td>
                <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                  <span class="tw-font-bold tw-text-slate-700">{{ currencySymbol }}{{ formatAmount(trx.post_balance) }}</span>
                </td>
                <td class="tw-px-6 tw-py-4">
                  <span class="tw-text-slate-600 tw-leading-snug tw-max-w-xs tw-block">{{ trx.details }}</span>
                </td>
              </tr>
              <tr v-if="transactions.length === 0">
                <td colspan="5" class="tw-px-6 tw-py-16 tw-text-center">
                  <div class="tw-w-16 tw-h-16 tw-bg-slate-50 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-4">
                    <i class="fas fa-receipt tw-text-3xl tw-text-slate-300"></i>
                  </div>
                  <h3 class="tw-text-slate-900 tw-font-bold tw-text-lg tw-mb-1">No transactions found</h3>
                  <p class="tw-text-slate-500 tw-text-sm">Try adjusting your filters or check back later.</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Pagination (Simple placeholder if needed later) -->
        <!-- <div class="tw-p-4 tw-border-t tw-border-slate-200 tw-bg-slate-50">...</div> -->
      </div>
    </div>
  </DashboardLayout>
</template>

<script>
import { ref, onMounted } from 'vue'
import DashboardLayout from '../../components/DashboardLayout.vue'
import { userService } from '../../services/userService'

export default {
  name: 'Transactions',
  components: {
    DashboardLayout
  },
  setup() {
    const transactions = ref([])
    const remarks = ref([])
    const showFilter = ref(false)
    const currencySymbol = ref('₹')
    const filters = ref({
      search: '',
      trx_type: '',
      remark: ''
    })

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

    const formatRemark = (remark) => {
      if (!remark || typeof remark !== 'string') return remark || ''
      return remark.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
    }

    const toggleFilter = () => {
      showFilter.value = !showFilter.value
    }

    const handleFilter = () => {
      fetchTransactions()
    }

    const fetchTransactions = async () => {
      try {
        const response = await userService.getTransactions(filters.value)
        if (response.status === 'success') {
          transactions.value = response.data?.data?.data ? response.data.data.data : (response.data?.data || [])
          
          if (response.data?.remarks) remarks.value = response.data.remarks
          if (response.data?.currency_symbol) currencySymbol.value = response.data.currency_symbol
        }
      } catch (error) {
        console.error('Error loading transactions:', error)
      }
    }

    onMounted(() => {
      fetchTransactions()
    })

    return {
      transactions,
      remarks,
      showFilter,
      filters,
      currencySymbol,
      formatAmount,
      formatDateTime,
      formatRemark,
      toggleFilter,
      handleFilter
    }
  }
}
</script>
