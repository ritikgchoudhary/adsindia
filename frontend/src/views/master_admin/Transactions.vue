<template>
  <MasterAdminLayout page-title="Transactions">
    <div class="ma-transactions">
      <!-- Sleek Command Header -->
      <div class="tw-bg-gradient-to-b tw-from-slate-900 tw-to-slate-950 tw-border tw-border-white/10 tw-rounded-3xl tw-p-8 tw-mb-8 tw-flex tw-flex-col lg:tw-flex-row tw-justify-between tw-items-center tw-gap-8 tw-shadow-2xl">
        <div>
          <h1 class="tw-text-3xl tw-font-black tw-text-white tw-flex tw-items-center tw-gap-3 tw-m-0">
            <span class="tw-p-3 tw-bg-indigo-500/10 tw-rounded-2xl tw-text-indigo-400">
              <i class="fas fa-exchange-alt"></i>
            </span>
            Transaction Ledger
          </h1>
          <p class="tw-text-slate-400 tw-mt-2 tw-text-sm tw-font-medium">Monitor and audit all digital movements across the platform.</p>
        </div>

        <!-- Compact Search & Filter Row -->
        <div class="tw-flex tw-flex-wrap tw-items-center tw-gap-4 tw-w-full lg:tw-w-auto">
          <div class="tw-relative tw-flex-grow lg:tw-w-80">
            <div class="tw-absolute tw-left-5 tw-top-1/2 tw--translate-y-1/2 tw-text-slate-500">
              <i class="fas fa-search"></i>
            </div>
            <input 
              type="text" 
              v-model="searchQuery" 
              placeholder="Search ID, User, or Email..." 
              @input="debounceSearch"
              class="tw-w-full tw-bg-white/5 tw-border tw-border-white/10 tw-rounded-2xl tw-pl-12 tw-pr-5 tw-py-3.5 tw-text-white tw-text-sm tw-font-bold focus:tw-border-indigo-500 tw-outline-none tw-transition-all"
            >
          </div>
          
          <div class="tw-flex tw-items-center tw-gap-2">
            <select v-model="filterTrxType" @change="fetchTransactions(1)" class="tw-bg-white/5 tw-border tw-border-white/10 tw-rounded-2xl tw-px-4 tw-py-3.5 tw-text-white tw-text-xs tw-font-black tw-uppercase tw-tracking-widest focus:tw-border-indigo-500 tw-outline-none">
              <option value="" class="tw-bg-slate-950">All Flows</option>
              <option value="+" class="tw-bg-slate-950">Credits (+)</option>
              <option value="-" class="tw-bg-slate-950">Debits (-)</option>
            </select>

            <select v-model="filterRemark" @change="fetchTransactions(1)" class="tw-bg-white/5 tw-border tw-border-white/10 tw-rounded-2xl tw-px-4 tw-py-3.5 tw-text-white tw-text-xs tw-font-black tw-uppercase tw-tracking-widest focus:tw-border-indigo-500 tw-outline-none">
              <option value="" class="tw-bg-slate-950">All Remarks</option>
              <option v-for="remark in remarks" :key="remark" :value="remark" class="tw-bg-slate-950">{{ formatRemark(remark) }}</option>
            </select>

            <button @click="fetchTransactions(1)" class="tw-p-4 tw-bg-white/5 hover:tw-bg-white/10 tw-border tw-border-white/10 tw-rounded-2xl tw-text-white tw-transition-all active:tw-scale-95">
              <i class="fas fa-sync-alt" :class="{ 'tw-animate-spin': loading }"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Main Audit Card -->
      <div class="tw-bg-slate-900 tw-border tw-border-white/10 tw-rounded-[2.5rem] tw-overflow-hidden tw-relative tw-shadow-2xl">
        <div class="tw-p-8 tw-border-b tw-border-white/5 tw-flex tw-justify-between tw-items-center">
          <h5 class="tw-text-xl tw-font-black tw-text-white tw-m-0 tw-flex tw-items-center tw-gap-3">
             <i class="fas fa-stream tw-text-indigo-400"></i> Audit Logs
          </h5>
          <div class="tw-px-4 tw-py-1.5 tw-bg-indigo-500/10 tw-border tw-border-indigo-500/20 tw-rounded-full tw-text-[10px] tw-text-indigo-400 tw-font-black tw-uppercase tw-tracking-tighter">
             Total: {{ totalTransactions }} Recorded
          </div>
        </div>

        <div class="tw-overflow-x-auto">
          <table class="tw-w-full tw-border-collapse">
            <thead>
              <tr class="tw-bg-white/[0.02]">
                <th class="tw-px-8 tw-py-6 tw-text-left tw-text-[11px] tw-font-black tw-text-slate-500 tw-uppercase tw-tracking-widest">Transaction Info</th>
                <th class="tw-px-8 tw-py-6 tw-text-left tw-text-[11px] tw-font-black tw-text-slate-500 tw-uppercase tw-tracking-widest">Subscriber</th>
                <th class="tw-px-8 tw-py-6 tw-text-left tw-text-[11px] tw-font-black tw-text-slate-500 tw-uppercase tw-tracking-widest">Amount & Balance</th>
                <th class="tw-px-8 tw-py-6 tw-text-left tw-text-[11px] tw-font-black tw-text-slate-500 tw-uppercase tw-tracking-widest">Event Audit</th>
                <th class="tw-px-8 tw-py-6 tw-text-right tw-text-[11px] tw-font-black tw-text-slate-500 tw-uppercase tw-tracking-widest">Timestamp</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading">
                <td colspan="5" class="tw-py-20 text-center">
                  <div class="tw-inline-flex tw-flex-col tw-items-center tw-gap-4">
                    <div class="tw-w-12 tw-h-12 tw-border-4 tw-border-indigo-500/20 tw-border-t-indigo-500 tw-rounded-full tw-animate-spin"></div>
                    <span class="tw-text-slate-500 tw-text-sm tw-font-bold tw-uppercase">Processing Ledger...</span>
                  </div>
                </td>
              </tr>
              <tr v-else-if="transactions.length === 0" class="tw-border-b tw-border-white/5">
                <td colspan="5" class="tw-py-24 text-center">
                  <div class="tw-w-20 tw-h-20 tw-bg-white/5 tw-rounded-3xl tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-6">
                    <i class="fas fa-ghost tw-text-4xl tw-text-slate-700"></i>
                  </div>
                  <h4 class="tw-text-white tw-font-black tw-text-xl tw-mb-2">Zero Transactions Found</h4>
                  <p class="tw-text-slate-500 tw-text-sm">No movements recorded for the selected criteria.</p>
                </td>
              </tr>
              <tr 
                v-else 
                v-for="trx in transactions" 
                :key="trx.id" 
                class="tw-group hover:tw-bg-indigo-500/[0.04] tw-transition-all tw-border-b tw-border-white/5 last:tw-border-0"
              >
                <!-- Transaction Info -->
                <td class="tw-px-8 tw-py-7 tw-align-middle">
                  <div class="tw-flex tw-flex-col">
                    <div class="tw-flex tw-items-center tw-gap-2">
                       <code class="tw-text-indigo-300 tw-font-mono tw-text-sm tw-bg-indigo-500/10 tw-px-2 tw-py-0.5 tw-rounded-md tw-border tw-border-indigo-500/20">{{ trx.trx }}</code>
                       <button @click="copyTrx(trx.trx)" class="tw-text-slate-600 hover:tw-text-indigo-400 tw-transition-colors">
                          <i class="fas fa-copy tw-text-[10px]"></i>
                       </button>
                    </div>
                    <span class="tw-text-slate-500 tw-text-[10px] tw-font-bold tw-uppercase tw-tracking-widest tw-mt-1.5 tw-ml-1">Internal ID: #{{ trx.id }}</span>
                  </div>
                </td>

                <!-- Subscriber -->
                <td class="tw-px-8 tw-py-7 tw-align-middle">
                  <div v-if="trx.user" class="tw-flex tw-items-center tw-gap-3">
                    <div class="tw-w-10 tw-h-10 tw-bg-slate-800 tw-border tw-border-white/10 tw-rounded-xl tw-flex tw-items-center tw-justify-center tw-text-indigo-400 tw-font-black tw-text-xs">
                      {{ getInitials(trx.user) }}
                    </div>
                    <div>
                      <div class="tw-text-white tw-font-bold tw-text-sm tw-leading-tight">{{ trx.user.firstname }} {{ trx.user.lastname }}</div>
                      <div class="tw-text-slate-500 tw-text-[11px] tw-font-medium">@{{ trx.user.username }}</div>
                    </div>
                  </div>
                  <div v-else class="tw-flex tw-items-center tw-gap-2 tw-text-slate-600">
                    <i class="fas fa-microchip tw-text-xs"></i>
                    <span class="tw-text-[11px] tw-font-bold tw-uppercase tw-tracking-tighter">System Engine</span>
                  </div>
                </td>

                <!-- Amount & Balance -->
                <td class="tw-px-8 tw-py-7 tw-align-middle">
                  <div class="tw-flex tw-flex-col">
                    <span :class="`tw-text-lg tw-font-black ${
                      trx.trx_type === '+' ? 'tw-text-emerald-400 amount-glow-credit' : 'tw-text-rose-400 amount-glow-debit'
                    }`">
                      {{ trx.trx_type }}₹{{ formatAmount(trx.amount) }}
                    </span>
                    <div class="tw-flex tw-items-center tw-gap-1.5 tw-mt-1.5">
                       <i class="fas fa-wallet tw-text-slate-600 tw-text-[10px]"></i>
                       <span class="tw-text-slate-500 tw-text-[11px] tw-font-bold">Post Balance: <span class="tw-text-slate-300">₹{{ formatAmount(trx.post_balance) }}</span></span>
                    </div>
                  </div>
                </td>

                <!-- Event Audit -->
                <td class="tw-px-8 tw-py-6 tw-align-middle">
                  <div class="tw-flex tw-items-center tw-gap-4">
                    <div class="tw-w-1 tw-h-8 tw-bg-indigo-500/30 tw-rounded-full"></div>
                    <div class="tw-flex tw-flex-col">
                       <span class="tw-text-slate-500 tw-text-[9px] tw-font-black tw-uppercase tw-tracking-widest">
                          {{ formatRemark(trx.remark) }}
                       </span>
                       <p class="tw-text-white tw-text-sm tw-font-bold tw-m-0 tw-line-clamp-1 tw-max-w-sm" :title="trx.details">
                          {{ trx.details || 'System Audit Trail' }}
                       </p>
                    </div>
                  </div>
                </td>

                <!-- Timestamp -->
                <td class="tw-px-8 tw-py-6 tw-text-right tw-align-middle">
                  <div class="tw-flex tw-flex-col tw-items-end">
                    <span class="tw-text-white tw-font-bold tw-text-sm">{{ formatDateOnly(trx.created_at) }}</span>
                    <span class="tw-text-slate-500 tw-text-[10px] tw-font-bold tw-uppercase tw-mt-0.5">{{ formatTimeOnly(trx.created_at) }}</span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Modern Pagination -->
        <div v-if="lastPage > 1" class="tw-p-8 tw-bg-white/[0.02] tw-border-t tw-border-white/5 tw-flex tw-flex-col md:tw-flex-row tw-items-center tw-justify-between tw-gap-4">
          <div class="tw-text-slate-500 tw-text-xs tw-font-bold">
            Displaying page <span class="tw-text-indigo-400">{{ currentPage }}</span> of <span class="tw-text-white">{{ lastPage }}</span>
          </div>
          <div class="tw-flex tw-items-center tw-gap-2">
            <button 
              @click="fetchTransactions(currentPage - 1)" 
              :disabled="currentPage === 1"
              class="tw-w-10 tw-h-10 tw-bg-white/5 tw-border tw-border-white/10 tw-rounded-xl tw-text-slate-400 tw-transition-all hover:tw-bg-indigo-500/20 hover:tw-text-indigo-400 disabled:tw-opacity-30 disabled:tw-cursor-not-allowed"
            >
              <i class="fas fa-chevron-left"></i>
            </button>

            <template v-for="p in paginationPages" :key="p">
              <span v-if="p === '...'" class="tw-text-slate-700 tw-px-2">•••</span>
              <button 
                v-else 
                @click="fetchTransactions(p)"
                :class="`tw-w-10 tw-h-10 tw-rounded-xl tw-text-sm tw-font-black tw-transition-all ${
                  p === currentPage ? 'tw-bg-indigo-500 tw-text-white tw-shadow-lg tw-shadow-indigo-500/30' : 'tw-bg-white/5 tw-border tw-border-white/10 tw-text-slate-400 hover:tw-bg-white/10 hover:tw-text-white'
                }`"
              >
                {{ p }}
              </button>
            </template>

            <button 
              @click="fetchTransactions(currentPage + 1)" 
              :disabled="currentPage === lastPage"
              class="tw-w-10 tw-h-10 tw-bg-white/5 tw-border tw-border-white/10 tw-rounded-xl tw-text-slate-400 tw-transition-all hover:tw-bg-indigo-500/20 hover:tw-text-indigo-400 disabled:tw-opacity-30 disabled:tw-cursor-not-allowed"
            >
              <i class="fas fa-chevron-right"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
  </MasterAdminLayout>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import MasterAdminLayout from '../../components/master_admin/MasterAdminLayout.vue'
import api from '../../services/api'

export default {
  name: 'MasterAdminTransactions',
  components: { MasterAdminLayout },
  setup() {
    const transactions = ref([])
    const loading = ref(false)
    const searchQuery = ref('')
    const filterTrxType = ref('')
    const filterRemark = ref('')
    const totalTransactions = ref(0)
    const currentPage = ref(1)
    const lastPage = ref(1)
    const remarks = ref([])
    let searchTimeout = null

    const formatAmount = (n) => {
      if (!n && n !== 0) return '0.00'
      return parseFloat(n).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }

    const formatDateTime = (d) => {
      if (!d) return '—'
      const date = new Date(d)
      return date.toLocaleString('en-IN', { 
        day: '2-digit', 
        month: 'short', 
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    }

    const formatDateOnly = (d) => {
      if (!d) return '—'
      return new Date(d).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' })
    }

    const formatTimeOnly = (d) => {
      if (!d) return '—'
      return new Date(d).toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit', hour12: true })
    }

    const formatRemark = (remark) => {
      if (!remark || typeof remark !== 'string') return remark || '—'
      return remark.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
    }

    const getInitials = (u) => {
      if (!u) return 'U'
      return ((u.firstname?.[0] || '') + (u.lastname?.[0] || '') || u.username?.[0] || 'U').toUpperCase()
    }

    const paginationPages = computed(() => {
      const pages = []
      const total = lastPage.value
      const cur = currentPage.value
      if (total <= 7) { for (let i = 1; i <= total; i++) pages.push(i); return pages }
      pages.push(1)
      if (cur > 3) pages.push('...')
      for (let i = Math.max(2, cur - 1); i <= Math.min(total - 1, cur + 1); i++) pages.push(i)
      if (cur < total - 2) pages.push('...')
      pages.push(total)
      return pages
    })

    const fetchTransactions = async (page) => {
      if (page) currentPage.value = page
      loading.value = true
      try {
        const params = { page: currentPage.value, per_page: 20 }
        if (searchQuery.value) params.search = searchQuery.value
        if (filterTrxType.value) params.trx_type = filterTrxType.value
        if (filterRemark.value) params.remark = filterRemark.value

        const res = await api.get('/admin/transactions', { params })
        if (res.data?.status === 'success' && res.data.data) {
          transactions.value = res.data.data.transactions || []
          totalTransactions.value = res.data.data.total || 0
          currentPage.value = res.data.data.current_page || 1
          lastPage.value = res.data.data.last_page || 1
          remarks.value = res.data.data.remarks || []
        }
      } catch (e) {
        console.error('Error fetching transactions:', e)
        if (window.notify) window.notify('error', 'Failed to load transactions')
      } finally {
        loading.value = false
      }
    }

    const debounceSearch = () => {
      clearTimeout(searchTimeout)
      searchTimeout = setTimeout(() => fetchTransactions(1), 400)
    }

    const copyTrx = async (trx) => {
      if (!trx) return
      try {
        await navigator.clipboard.writeText(trx)
        if (window.notify) window.notify('success', 'Transaction ID copied to clipboard')
      } catch (e) {
        const textArea = document.createElement('textarea')
        textArea.value = trx
        textArea.style.position = 'fixed'
        textArea.style.opacity = '0'
        document.body.appendChild(textArea)
        textArea.select()
        document.execCommand('copy')
        document.body.removeChild(textArea)
        if (window.notify) window.notify('success', 'Transaction ID copied to clipboard')
      }
    }

    onMounted(() => {
      fetchTransactions(1)
    })

    return {
      transactions, loading, searchQuery, filterTrxType, filterRemark,
      totalTransactions, currentPage, lastPage, paginationPages, remarks,
      formatAmount, formatDateTime, formatDateOnly, formatTimeOnly, formatRemark, getInitials,
      fetchTransactions, debounceSearch, copyTrx
    }
  }
}
</script>

<style scoped>
.text-glow {
  text-shadow: 0 0 10px rgba(99, 102, 241, 0.4);
}
.amount-glow-credit {
  text-shadow: 0 0 12px rgba(16, 185, 129, 0.4);
}
.amount-glow-debit {
  text-shadow: 0 0 12px rgba(244, 63, 94, 0.4);
}
.custom-scrollbar::-webkit-scrollbar { width: 5px; }
.custom-scrollbar::-webkit-scrollbar-track { background: rgba(255,255,255,0.02); }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.2); }
</style>

