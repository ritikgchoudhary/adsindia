<template>
  <DashboardLayout page-title="Deposit History" :dark-theme="true">
    <div class="tw-grid tw-grid-cols-1 tw-gap-6">
      <div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-slate-200">
        
        <!-- Header -->
        <div class="tw-p-5 tw-border-b tw-border-slate-200 tw-bg-slate-50/50 tw-flex tw-flex-col md:tw-flex-row tw-items-center tw-justify-between tw-gap-4">
          <h5 class="tw-text-slate-900 tw-font-bold tw-text-lg tw-m-0 tw-flex tw-items-center">
            <i class="fas fa-history tw-mr-2 tw-text-indigo-600"></i>Deposit History
          </h5>
          <div class="tw-flex tw-gap-2 tw-w-full md:tw-w-auto">
            <select v-model="filterStatus" @change="handleSearch" class="tw-bg-white tw-border tw-border-slate-300 tw-rounded-xl tw-px-3 tw-py-2 tw-text-sm focus:tw-outline-none">
              <option value="1">Successful</option>
              <option value="2">Pending</option>
              <option value="3">Rejected</option>
              <option value="">All</option>
            </select>
            <form class="tw-flex-1 md:tw-flex-none" @submit.prevent="handleSearch">
              <div class="tw-relative tw-flex tw-items-center">
                <input 
                  type="search" 
                  v-model="searchQuery" 
                  placeholder="Search trx..." 
                  class="tw-w-full md:tw-w-48 tw-pl-4 tw-pr-10 tw-py-2 tw-bg-white tw-border tw-border-slate-300 tw-rounded-xl tw-text-sm focus:tw-outline-none"
                >
                <button type="submit" class="tw-absolute tw-right-2 tw-w-7 tw-h-7 tw-flex tw-items-center tw-justify-center tw-bg-indigo-600 tw-text-white tw-rounded-lg tw-border-0 tw-cursor-pointer">
                  <i class="fas fa-search tw-text-xs"></i>
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Table -->
        <div class="tw-overflow-x-auto">
          <table class="deposit-history-table tw-w-full tw-text-sm tw-border-collapse">
            <thead>
              <tr class="tw-bg-slate-50 tw-border-b tw-border-slate-200">
                <th class="tw-px-6 tw-py-4 tw-text-left tw-text-xs tw-font-bold tw-uppercase tw-text-slate-500 tw-tracking-wider">Gateway / Transaction</th>
                <th class="tw-px-6 tw-py-4 tw-text-left tw-text-xs tw-font-bold tw-uppercase tw-text-slate-500 tw-tracking-wider">Date</th>
                <th class="tw-px-6 tw-py-4 tw-text-left tw-text-xs tw-font-bold tw-uppercase tw-text-slate-500 tw-tracking-wider">Amount</th>
                <th class="tw-px-6 tw-py-4 tw-text-left tw-text-xs tw-font-bold tw-uppercase tw-text-slate-500 tw-tracking-wider">Status</th>
                <th class="tw-px-6 tw-py-4 tw-text-center tw-text-xs tw-font-bold tw-uppercase tw-text-slate-500 tw-tracking-wider">Action</th>
              </tr>
            </thead>
            <tbody class="tw-divide-y tw-divide-slate-100">
              <tr v-if="loading">
                <td colspan="5" class="tw-px-6 tw-py-10 tw-text-center">
                  <div class="tw-flex tw-items-center tw-justify-center tw-gap-2">
                    <div class="tw-w-5 tw-h-5 tw-border-2 tw-border-indigo-600/30 tw-border-t-indigo-600 tw-rounded-full tw-animate-spin"></div>
                    <span class="tw-text-slate-500">Loading deposits...</span>
                  </div>
                </td>
              </tr>
              <tr 
                v-else
                v-for="deposit in deposits" 
                :key="deposit.id" 
                class="hover:tw-bg-slate-50/80 tw-transition-colors"
              >
                <td class="tw-px-6 tw-py-4">
                  <span class="tw-block tw-font-bold tw-text-slate-900 tw-mb-1">{{ deposit.gateway?.name || deposit.method_name || '–' }}</span>
                  <span class="tw-block tw-text-xs tw-font-mono tw-text-slate-500 tw-bg-slate-100 tw-px-2 tw-py-0.5 tw-rounded tw-w-fit">{{ deposit.trx }}</span>
                </td>
                <td class="tw-px-6 tw-py-4">
                  <div class="tw-font-semibold tw-text-slate-800">{{ formatDate(deposit.created_at) }}</div>
                  <div class="tw-text-xs tw-text-slate-500">{{ deposit.time_human }}</div>
                </td>
                <td class="tw-px-6 tw-py-4">
                  <div class="tw-font-bold tw-text-slate-900">₹{{ formatAmount(deposit.amount) }}</div>
                  <div class="tw-text-xs tw-text-slate-500">+ ₹{{ formatAmount(deposit.charge) }} charge</div>
                </td>
                <td class="tw-px-6 tw-py-4">
                   <span class="tw-px-2.5 tw-py-1 tw-rounded-lg tw-text-xs tw-font-bold tw-uppercase" :class="getStatusClass(deposit.status)">
                     {{ getStatusText(deposit.status) }}
                   </span>
                </td>
                <td class="tw-px-6 tw-py-4 tw-text-center">
                  <button 
                    v-if="deposit.detail"
                    type="button" 
                    class="tw-w-9 tw-h-9 tw-inline-flex tw-items-center tw-justify-center tw-rounded-lg tw-bg-slate-100 tw-text-indigo-600 hover:tw-bg-indigo-600 hover:tw-text-white tw-transition-all tw-border-0 tw-cursor-pointer"
                    @click="showDetails(deposit)" 
                    title="View details"
                  >
                    <i class="fas fa-eye"></i>
                  </button>
                  <span v-else class="tw-text-slate-400 tw-text-xs">No Details</span>
                </td>
              </tr>
              <tr v-if="!loading && deposits.length === 0">
                <td colspan="5" class="tw-px-6 tw-py-16 tw-text-center">
                  <div class="tw-w-16 tw-h-16 tw-bg-slate-50 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-4">
                    <i class="fas fa-receipt tw-text-3xl tw-text-slate-300"></i>
                  </div>
                  <h3 class="tw-text-slate-900 tw-font-bold tw-text-lg tw-mb-1">No deposits found</h3>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Detail Modal -->
    <div v-if="showModal" class="tw-fixed tw-inset-0 tw-z-[60] tw-flex tw-items-center tw-justify-center tw-px-4">
      <div class="tw-absolute tw-inset-0 tw-bg-black/60 tw-backdrop-blur-sm" @click="showModal = false"></div>
      <div class="tw-bg-white tw-rounded-2xl tw-shadow-2xl tw-w-full tw-max-w-md tw-relative tw-z-10 tw-overflow-hidden tw-animate-fade-in-up">
        <div class="tw-bg-indigo-600 tw-p-5 tw-flex tw-items-center tw-justify-between">
          <h5 class="tw-text-white tw-font-bold tw-text-lg tw-m-0">Deposit Details</h5>
          <button @click="showModal = false" class="tw-text-white/80 hover:tw-text-white tw-bg-transparent tw-border-0 tw-cursor-pointer">
            <i class="fas fa-times tw-text-xl"></i>
          </button>
        </div>
        
        <div class="tw-p-6">
          <ul class="tw-space-y-3 tw-mb-0 tw-list-none tw-p-0">
            <li v-for="(val, key) in selectedDeposit?.detail" :key="key" class="tw-flex tw-items-center tw-justify-between tw-border-b tw-border-slate-100 tw-pb-2 last:tw-border-0 last:tw-pb-0">
              <span class="tw-font-semibold tw-text-slate-600 tw-text-sm">{{ formatKey(key) }}</span>
              <span v-if="val && typeof val === 'object' && val.type === 'file'" class="tw-font-bold tw-text-slate-900 tw-text-sm">
                <a :href="val.value" target="_blank" class="tw-text-indigo-600 hover:tw-underline">View Image</a>
              </span>
              <span v-else class="tw-font-bold tw-text-slate-900 tw-text-sm tw-text-right">{{ val.value || val }}</span>
            </li>
          </ul>

          <div v-if="selectedDeposit?.admin_feedback" class="tw-mt-6 tw-bg-blue-50 tw-border-l-4 tw-border-blue-500 tw-p-4 tw-rounded-r-lg">
            <strong class="tw-block tw-text-blue-800 tw-text-sm tw-mb-1">Admin Feedback</strong>
            <p class="tw-text-blue-700 tw-text-sm tw-m-0">{{ selectedDeposit.admin_feedback }}</p>
          </div>
          
          <div class="tw-mt-6">
             <button @click="showModal = false" class="tw-w-full tw-py-3 tw-bg-slate-100 hover:tw-bg-slate-200 tw-text-slate-700 tw-font-bold tw-rounded-xl tw-border-0 tw-cursor-pointer transition-colors">Close</button>
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
  name: 'DepositHistory',
  components: { DashboardLayout },
  setup() {
    const deposits = ref([])
    const loading = ref(false)
    const searchQuery = ref('')
    const filterStatus = ref('1') // Default to Successful as requested
    const showModal = ref(false)
    const selectedDeposit = ref(null)

    const formatAmount = (n) => {
      if (!n && n !== 0) return '0.00'
      return parseFloat(n).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }

    const formatDate = (d) => {
      if (!d) return '-'
      const date = new Date(d)
      return date.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' })
    }

    const getStatusText = (s) => {
      if (Number(s) === 1) return 'Successful'
      if (Number(s) === 2) return 'Pending'
      if (Number(s) === 3) return 'Rejected'
      return 'Initiated'
    }

    const getStatusClass = (s) => {
      if (Number(s) === 1) return 'tw-bg-emerald-100 tw-text-emerald-700'
      if (Number(s) === 2) return 'tw-bg-amber-100 tw-text-amber-700'
      if (Number(s) === 3) return 'tw-bg-red-100 tw-text-red-700'
      return 'tw-bg-slate-100 tw-text-slate-700'
    }

    const formatKey = (k) => k.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ')

    const showDetails = (d) => {
      selectedDeposit.value = d
      showModal.value = true
    }

    const handleSearch = () => fetchDeposits()

    const fetchDeposits = async () => {
      loading.value = true
      try {
        const params = { 
          search: searchQuery.value,
          status: filterStatus.value
        }
        const res = await api.get('/deposit/history', { params })
        if (res.data?.status === 'success') {
          // Flatten data if nested
          let d = res.data.data?.data || res.data.data || []
          // Ensure it's an array
          deposits.value = Array.isArray(d) ? d : []
        }
      } catch (e) {
        console.error('Error loading deposits:', e)
      } finally {
        loading.value = false
      }
    }

    onMounted(() => fetchDeposits())

    return {
      deposits, loading, searchQuery, filterStatus, showModal, selectedDeposit,
      formatAmount, formatDate, getStatusText, getStatusClass, formatKey, showDetails, handleSearch
    }
  }
}
</script>

<style scoped>
.deposit-history-table tbody tr:hover td,
.deposit-history-table tbody tr:hover td * {
  color: rgb(15 23 42) !important;
}
</style>
