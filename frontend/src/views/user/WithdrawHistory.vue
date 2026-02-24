<template>
  <DashboardLayout page-title="Withdraw History" :dark-theme="true">
    <div class="ma-withdraw-wrap tw-animate-fade-in">
      
      <!-- Main Content Card -->
      <div class="ma-glass-card">
        <!-- Header Section -->
        <div class="ma-card-header tw-p-4 sm:tw-p-6">
          <div class="tw-flex tw-items-center tw-gap-2 sm:tw-gap-3">
             <div class="header-icon-box tw-w-9 tw-h-9 sm:tw-w-11 sm:tw-h-11 tw-text-lg sm:tw-text-xl">
                <i class="fas fa-file-invoice-dollar"></i>
             </div>
             <div>
                <h5 class="tw-text-white tw-font-bold tw-text-base sm:tw-text-lg tw-m-0">Withdrawal Log</h5>
                <p class="tw-text-slate-400 tw-text-[10px] sm:tw-text-sm tw-m-0">Your request history</p>
             </div>
          </div>
          
          <div class="header-actions tw-gap-2 sm:tw-gap-3">
            <form @submit.prevent="handleSearch" class="tw-relative tw-w-full md:tw-w-auto">
              <input 
                type="search" 
                v-model="searchQuery" 
                placeholder="TRX..." 
                class="search-input tw-py-2 tw-text-xs sm:tw-text-sm"
              >
              <button type="submit" class="search-btn">
                <i class="fas fa-search tw-text-xs"></i>
              </button>
            </form>
            <button class="ma-refresh-btn tw-w-9 tw-h-9 sm:tw-w-11 sm:tw-h-11" @click="fetchWithdraws" :class="{ 'tw-animate-spin': loading }">
               <i class="fas fa-sync-alt tw-text-xs"></i>
            </button>
          </div>
        </div>

        <!-- Desktop Table -->
        <div class="tw-hidden lg:tw-block">
          <table class="ma-modern-table">
            <thead>
              <tr>
                <th>Gateway & TRX</th>
                <th>Date & Time</th>
                <th>Amount</th>
                <th class="tw-text-left">Status</th>
                <th class="tw-text-right tw-pr-8">Reason</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="withdraw in withdraws" :key="withdraw?.id" class="ma-table-row">
                <td>
                  <div class="tw-flex tw-items-center tw-gap-3">
                    <div class="method-icon" :class="getMethodClass(withdraw.method?.name)">
                      <i :class="getMethodIcon(withdraw.method?.name)"></i>
                    </div>
                    <div>
                      <div class="tw-text-white tw-font-bold">{{ withdraw.method?.name || 'Gateway' }}</div>
                      <div class="tw-text-indigo-400 tw-text-xs tw-font-mono">{{ withdraw.trx }}</div>
                    </div>
                  </div>
                </td>
                <td>
                  <div class="tw-text-slate-200 tw-font-medium tw-text-sm">{{ formatDateTime(withdraw.created_at) }}</div>
                  <div class="tw-text-slate-500 tw-text-xs tw-mt-0.5">{{ withdraw.created_at_human }}</div>
                </td>
                <td>
                  <div class="tw-text-white tw-font-bold tw-text-lg">{{ currencySymbol }}{{ formatAmount(withdraw.amount) }}</div>
                </td>
                <td class="tw-text-left">
                   <div class="status-badge-container" v-html="withdraw.status_badge"></div>
                </td>
                <td class="tw-text-right tw-pr-8">
                   <button @click="showDetails(withdraw)" class="ma-action-btn" title="View Reason">
                      <i class="fas fa-comment-alt"></i>
                   </button>
                </td>
              </tr>
              <tr v-if="withdraws.length === 0 && !loading">
                 <td colspan="5">
                    <div class="empty-state">
                       <div class="empty-icon"><i class="fas fa-ghost"></i></div>
                       <p>No transactions found</p>
                    </div>
                 </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Mobile / Tablet List Layout -->
        <div class="lg:tw-hidden tw-p-3 tw-space-y-3">
           <div v-for="withdraw in withdraws" :key="withdraw.id" class="ma-mobile-card tw-p-3 tw-rounded-xl">
              <div class="mobile-card-header tw-mb-3">
                 <div class="tw-flex tw-items-center tw-gap-2">
                    <div class="method-icon-mini tw-w-7 tw-h-7 tw-text-xs" :class="getMethodClass(withdraw.method?.name)">
                       <i :class="getMethodIcon(withdraw.method?.name)"></i>
                    </div>
                    <div>
                       <div class="tw-text-white tw-font-bold tw-text-xs">{{ withdraw.method?.name }}</div>
                       <div class="tw-text-[9px] tw-text-indigo-400 tw-font-mono">{{ withdraw.trx }}</div>
                    </div>
                 </div>
                 <div class="status-badge-container tw-scale-90" v-html="withdraw.status_badge"></div>
              </div>
              <div class="mobile-card-body">
                 <div class="tw-flex tw-justify-between tw-items-center">
                    <div>
                       <div class="tw-text-white tw-font-black tw-text-lg">
                          {{ currencySymbol }}{{ formatAmount(withdraw.amount) }}
                       </div>
                       <div class="tw-text-[9px] tw-text-slate-500">
                            {{ withdraw.created_at_human }}
                       </div>
                    </div>
                    <div class="tw-text-right">
                       <button @click="showDetails(withdraw)" class="tw-bg-indigo-600 tw-text-white tw-border-0 tw-px-3 tw-py-1.5 tw-rounded-lg tw-text-[10px] tw-font-bold tw-cursor-pointer">
                          Reason <i class="fas fa-chevron-right tw-ml-1 tw-text-[8px]"></i>
                       </button>
                    </div>
                 </div>
              </div>
           </div>
           
           <div v-if="withdraws.length === 0 && !loading" class="empty-state">
              <div class="empty-icon"><i class="fas fa-search"></i></div>
              <p>No history records</p>
           </div>
        </div>

        <!-- Loading State Overlay -->
        <div v-if="loading" class="loading-overlay">
           <div class="ma-spinner-fancy"></div>
        </div>
      </div>
    </div>

    <!-- Detail Modal -->
    <div v-if="showModal" class="ma-modal-overlay">
      <div class="ma-modal-content tw-animate-modal-in">
        <div class="ma-modal-header">
           <div class="modal-title-box">
              <i class="fas fa-info-circle tw-text-indigo-400"></i>
              <h5 class="tw-m-0 tw-text-white tw-font-bold">Withdrawal Remark</h5>
           </div>
           <button @click="showModal = false" class="modal-close-btn">
              <i class="fas fa-times"></i>
           </button>
        </div>
        
        <div class="ma-modal-body text-center">
           <div v-if="selectedWithdrawFeedback" class="professional-remark">
              <div class="remark-header">
                 <div class="remark-dot"></div>
                 <span>Official Remark</span>
              </div>
              <div class="remark-content">
                 {{ selectedWithdrawFeedback }}
              </div>
           </div>
           <div v-else class="tw-py-8">
              <div class="tw-w-16 tw-h-16 tw-bg-slate-800 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-4">
                 <i class="fas fa-check-circle tw-text-slate-600 tw-text-2xl"></i>
              </div>
              <p class="tw-text-slate-400 tw-m-0">Your request is being processed. No remarks available yet.</p>
           </div>
           
           <div class="tw-mt-8">
              <button @click="showModal = false" class="ma-btn-primary tw-w-full">
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
  name: 'WithdrawHistory',
  components: {
    DashboardLayout
  },
  setup() {
    const withdraws = ref([])
    const searchQuery = ref('')
    const loading = ref(false)
    const showModal = ref(false)
    const selectedWithdrawFeedback = ref('')
    const currencySymbol = ref('₹')

    const formatAmount = (amount) => {
      if (!amount && amount !== 0) return '0.00'
      return parseFloat(amount).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
    }

    const formatDateTime = (dateString) => {
      if (!dateString) return '-'
      const date = new Date(dateString)
      return date.toLocaleString('en-IN', { 
        year: 'numeric', 
        month: 'short', 
        day: '2-digit', 
        hour: '2-digit', 
        minute: '2-digit', 
        hour12: true 
      });
    }

    const getMethodClass = (name) => {
      const n = (name || '').toLowerCase()
      if (n.includes('upi') || n.includes('phone') || n.includes('google')) return 'method-upi'
      if (n.includes('bank') || n.includes('transfer')) return 'method-bank'
      return 'method-other'
    }

    const getMethodIcon = (name) => {
      const n = (name || '').toLowerCase()
      if (n.includes('upi')) return 'fas fa-qrcode'
      if (n.includes('phonepe')) return 'fas fa-mobile-alt'
      if (n.includes('google')) return 'fab fa-google-pay'
      if (n.includes('bank')) return 'fas fa-university'
      return 'fas fa-wallet'
    }

    const showDetails = (withdraw) => {
      selectedWithdrawFeedback.value = withdraw.admin_feedback || ''
      showModal.value = true
    }

    const handleSearch = () => fetchWithdraws()

    const fetchWithdraws = async () => {
      loading.value = true
      try {
        const response = await api.get('/withdraw/history', { 
          params: searchQuery.value ? { search: searchQuery.value } : {} 
        })
        if (response.data.status === 'success') {
          withdraws.value = response.data.data?.data ?? response.data.data ?? []
          if (response.data.data?.currency_symbol) currencySymbol.value = response.data.data.currency_symbol
        }
      } catch (error) {
        console.error('Loading Error:', error)
      } finally {
        loading.value = false
      }
    }

    onMounted(() => fetchWithdraws())

    return {
      withdraws, searchQuery, loading, showModal,
      selectedWithdrawFeedback,
      currencySymbol,
      formatAmount, formatDateTime, showDetails,
      handleSearch, fetchWithdraws, getMethodClass, getMethodIcon
    }
  }
}
</script>

<style scoped>
.ma-withdraw-wrap {
  --primary-glow: rgba(79, 70, 229, 0.4);
  --glass-bg: rgba(15, 23, 42, 0.6);
  --border-light: rgba(255, 255, 255, 0.08);
}

/* Glass Card */
.ma-glass-card {
  background: var(--glass-bg);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border: 1px solid var(--border-light);
  border-radius: 24px;
  overflow: hidden;
  position: relative;
  box-shadow: 0 4px 30px rgba(0, 0, 0, 0.2);
}

.ma-card-header {
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 20px;
  border-bottom: 1px solid var(--border-light);
}

@media (min-width: 768px) {
  .ma-card-header {
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
  }
}

.header-icon-box {
  width: 44px;
  height: 44px;
  background: linear-gradient(135deg, #4f46e5, #818cf8);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 20px;
  box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 12px;
  width: 100%;
}

@media (min-width: 768px) {
  .header-actions {
    width: auto;
  }
}

/* Search */
.search-input {
  width: 100%;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid var(--border-light);
  border-radius: 14px;
  padding: 12px 48px 12px 18px;
  color: white;
  font-size: 14px;
  transition: all 0.3s ease;
}

.search-input:focus {
  outline: none;
  background: rgba(255, 255, 255, 0.08);
  border-color: #4f46e5;
  box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.15);
}

.search-btn {
  position: absolute;
  right: 6px;
  top: 50%;
  transform: translateY(-50%);
  width: 36px;
  height: 36px;
  background: transparent;
  border: 0;
  color: #818cf8;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.search-btn:hover {
  color: white;
  background: rgba(79, 70, 229, 0.4);
  border-radius: 10px;
}

.ma-refresh-btn {
  width: 44px;
  height: 44px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid var(--border-light);
  border-radius: 14px;
  color: white;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s;
}

.ma-refresh-btn:hover {
  background: rgba(255, 255, 255, 0.1);
  border-color: #4f46e5;
  color: #818cf8;
}

/* Modern Table */
.ma-modern-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
}

.ma-modern-table th {
  padding: 18px 24px;
  text-align: left;
  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
  color: #64748b;
  letter-spacing: 1px;
  border-bottom: 1px solid var(--border-light);
}

.ma-table-row td {
  padding: 24px;
  vertical-align: middle;
  border-bottom: 1px solid rgba(255, 255, 255, 0.03);
  transition: all 0.3s ease;
}

.ma-table-row:hover td {
  background: rgba(79, 70, 229, 0.03);
}

.method-icon {
  width: 48px;
  height: 48px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  background: rgba(255, 255, 255, 0.05);
}

.method-upi { color: #818cf8; box-shadow: inset 0 0 10px rgba(129, 140, 248, 0.1); }
.method-bank { color: #4ade80; box-shadow: inset 0 0 10px rgba(74, 222, 128, 0.1); }
.method-other { color: #fbbf24; }

.ma-modern-table th.tw-text-right,
.ma-modern-table td.tw-text-right {
  text-align: right !important;
  padding-right: 40px !important;
}

.ma-modern-table th.tw-text-left,
.ma-modern-table td.tw-text-left {
  text-align: left !important;
}

.ma-action-btn {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  background: transparent;
  border: 1px solid var(--border-light);
  color: #94a3b8;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s;
}

.ma-action-btn:hover {
  background: #4f46e5;
  border-color: #4f46e5;
  color: white;
  transform: scale(1.1);
}

/* Mobile Cards */
.ma-mobile-card {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid var(--border-light);
  border-radius: 18px;
  padding: 16px;
  overflow: hidden;
}

.mobile-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.method-icon-mini {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  background: rgba(255, 255, 255, 0.05);
}

.ma-mobile-action {
  background: #4f46e5;
  color: white;
  border: 0;
  padding: 6px 14px;
  border-radius: 10px;
  font-size: 11px;
  font-weight: 700;
  cursor: pointer;
}

/* Modal UI */
.ma-modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 100;
  background: rgba(0, 0, 0, 0.8);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.ma-modal-content {
  background: #0f172a;
  border: 1px solid var(--border-light);
  border-radius: 28px;
  width: 100%;
  max-width: 440px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
}

.ma-modal-header {
  padding: 24px 28px;
  border-bottom: 1px solid var(--border-light);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-title-box {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 18px;
}

.modal-close-btn {
  background: rgba(255, 255, 255, 0.05);
  border: 0;
  width: 36px;
  height: 36px;
  border-radius: 10px;
  color: #94a3b8;
  cursor: pointer;
}

.ma-modal-body {
  padding: 28px;
}

.professional-remark {
  background: rgba(79, 70, 229, 0.05);
  border: 1px solid rgba(79, 70, 229, 0.2);
  border-left: 4px solid #4f46e5;
  border-radius: 14px;
  padding: 20px;
  text-align: left;
}

.remark-header {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
}

.remark-dot {
  width: 6px;
  height: 6px;
  background: #4f46e5;
  border-radius: 50%;
  box-shadow: 0 0 10px #4f46e5;
}

.remark-header span {
  font-size: 10px;
  font-weight: 800;
  color: #818cf8;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.remark-content {
  color: #f1f5f9;
  font-size: 14px;
  line-height: 1.5;
  font-weight: 500;
}

@media (max-width: 640px) {
  /* Main Container */
  .ma-glass-card { border-radius: 1.25rem !important; }
  .ma-card-header { padding: 1rem !important; gap: 0.85rem !important; }
  .header-icon-box { width: 2.25rem !important; height: 2.25rem !important; border-radius: 0.65rem !important; font-size: 1rem !important; }
  h5.tw-text-base { font-size: 0.95rem !important; }
  p.tw-text-\[10px\] { font-size: 8px !important; }

  /* Search & Actions */
  .header-actions { gap: 0.5rem !important; }
  .search-input { padding: 0.65rem 1rem !important; font-size: 0.75rem !important; border-radius: 0.75rem !important; }
  .search-btn { right: 4px !important; width: 28px !important; height: 28px !important; }
  .ma-refresh-btn { width: 2.25rem !important; height: 2.25rem !important; border-radius: 0.75rem !important; }
  .ma-refresh-btn i { font-size: 0.75rem !important; }

  /* Mobile Cards */
  .lg\:tw-hidden.tw-p-3 { padding: 0.75rem !important; gap: 0.65rem !important; }
  .ma-mobile-card { padding: 0.85rem !important; border-radius: 1rem !important; }
  .mobile-card-header { margin-bottom: 0.75rem !important; }
  .method-icon-mini { width: 1.75rem !important; height: 1.75rem !important; border-radius: 0.5rem !important; font-size: 0.75rem !important; }
  .mobile-card-header .tw-text-white { font-size: 0.75rem !important; }
  .tw-text-white.tw-font-black.tw-text-lg { font-size: 1.15rem !important; }
  .tw-text-\[9px\] { font-size: 7px !important; }
  .status-badge-container { scale: 0.75 !important; transform-origin: right !important; }
  .tw-bg-indigo-600.tw-text-white { padding: 0.4rem 0.75rem !important; font-size: 9px !important; border-radius: 0.5rem !important; }

  /* Modal */
  .ma-modal-content { border-radius: 1.5rem !important; }
  .ma-modal-header { padding: 1rem 1.25rem !important; }
  .modal-title-box { font-size: 1rem !important; gap: 0.75rem !important; }
  .modal-close-btn { width: 1.75rem !important; height: 1.75rem !important; border-radius: 0.5rem !important; }
  .ma-modal-body { padding: 1.25rem !important; }
  .professional-remark { padding: 0.85rem !important; border-radius: 0.85rem !important; }
  .remark-content { font-size: 0.8rem !important; }
  .ma-btn-primary { padding: 0.75rem !important; border-radius: 0.75rem !important; font-size: 0.9rem !important; }
}

.ma-spinner-fancy {
  width: 40px;
  height: 40px;
  border: 3px solid rgba(79, 70, 229, 0.1);
  border-top-color: #4f46e5;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

.loading-overlay {
   display: flex;
   justify-content: center;
   padding: 60px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.empty-state {
   padding: 60px 24px;
   text-align: center;
   color: #64748b;
}

.empty-icon {
   font-size: 40px;
   margin-bottom: 16px;
   opacity: 0.2;
}
</style>
