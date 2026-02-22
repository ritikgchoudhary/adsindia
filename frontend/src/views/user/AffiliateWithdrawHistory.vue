<template>
  <DashboardLayout page-title="Affiliate Withdraw History" :dark-theme="true">
    <div class="ma-withdraw-wrap tw-animate-fade-in">
      
      <!-- Main Log Card -->
      <div class="ma-glass-card">
        <div class="ma-card-header">
          <div class="tw-flex tw-items-center tw-gap-3">
             <div class="header-icon-box text-glow-primary">
                <i class="fas fa-history"></i>
             </div>
             <div>
                <h5 class="tw-text-white tw-font-bold tw-text-lg tw-m-0">Affiliate Payout Log</h5>
                <p class="tw-text-slate-400 tw-text-sm tw-m-0">Your referral commission withdrawals</p>
             </div>
          </div>
          
          <div class="header-actions">
            <form @submit.prevent="handleSearch" class="tw-relative tw-w-full md:tw-w-auto">
              <input 
                type="search" 
                v-model="searchQuery" 
                placeholder="TRX..." 
                class="search-input"
              >
              <button type="submit" class="search-btn text-primary-color">
                <i class="fas fa-search"></i>
              </button>
            </form>
            <router-link to="/user/affiliate-withdraw" class="ma-btn-primary-sm">
               <i class="fas fa-plus tw-mr-1"></i> New Request
            </router-link>
          </div>
        </div>

        <!-- Desktop Table -->
        <div class="tw-hidden lg:tw-block">
          <table class="ma-modern-table">
            <thead>
              <tr>
                <th>Gateway & TRX</th>
                <th>Timeline</th>
                <th>Amount</th>
                <th>Status</th>
                <th class="tw-text-right">Reason</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="withdraw in withdraws" :key="withdraw?.id" class="ma-table-row">
                <td>
                  <div class="tw-flex tw-items-center tw-gap-3">
                    <div class="method-icon-primary">
                      <i :class="getMethodIcon(withdraw.method?.name)"></i>
                    </div>
                    <div>
                      <div class="tw-text-white tw-font-bold">{{ withdraw.method?.name || 'Gateway' }}</div>
                      <div class="tw-text-indigo-400/80 tw-text-xs tw-font-mono">{{ withdraw.trx }}</div>
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
                <td>
                   <div class="status-badge-container" v-html="withdraw.status_badge"></div>
                </td>
                <td class="tw-text-right">
                   <button @click="showDetails(withdraw)" class="ma-action-btn-primary" title="View Reason">
                      <i class="fas fa-eye"></i>
                   </button>
                </td>
              </tr>
              <tr v-if="withdraws.length === 0 && !loading">
                 <td colspan="5">
                    <div class="empty-state">
                       <i class="fas fa-folder-open empty-icon"></i>
                       <p>No affiliate payouts yet</p>
                    </div>
                 </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Mobile Card Layout -->
        <div class="lg:tw-hidden tw-p-3 tw-space-y-4">
           <div v-for="withdraw in withdraws" :key="withdraw.id" class="ma-mobile-card primary-border">
              <div class="tw-flex tw-justify-between tw-items-start tw-mb-4">
                 <div class="tw-flex tw-items-center tw-gap-2">
                    <div class="method-icon-mini-primary">
                       <i :class="getMethodIcon(withdraw.method?.name)"></i>
                    </div>
                    <div>
                       <div class="tw-text-white tw-font-bold tw-text-sm">{{ withdraw.method?.name }}</div>
                       <div class="tw-text-[10px] tw-text-indigo-500 tw-font-mono">{{ withdraw.trx }}</div>
                    </div>
                 </div>
                 <div class="status-badge-container scale-75" v-html="withdraw.status_badge"></div>
              </div>
              <div class="tw-flex tw-justify-between tw-items-end">
                 <div>
                    <div class="tw-text-white tw-font-black tw-text-xl">{{ currencySymbol }}{{ formatAmount(withdraw.amount) }}</div>
                    <div class="tw-text-[10px] tw-text-slate-500 tw-mt-1">{{ formatDateTime(withdraw.created_at) }}</div>
                 </div>
                 <button @click="showDetails(withdraw)" class="ma-action-payout-btn">
                    REASON
                 </button>
              </div>
           </div>
        </div>

        <!-- Loader -->
        <div v-if="loading" class="loading-overlay">
           <div class="primary-spinner"></div>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="ma-modal-overlay">
      <div class="ma-modal-content primary-modal-border tw-animate-modal-in">
        <div class="ma-modal-header primary-header-bg">
           <div class="modal-title-box">
              <i class="fas fa-shield-alt tw-text-indigo-300"></i>
              <h5 class="tw-m-0 tw-text-white tw-font-bold">Withdrawal Remark</h5>
           </div>
           <button @click="showModal = false" class="modal-close-btn">
              <i class="fas fa-times"></i>
           </button>
        </div>
        
        <div class="ma-modal-body text-center">
           <div v-if="selectedWithdrawFeedback" class="professional-remark-primary">
              <div class="remark-header">
                 <div class="remark-dot-primary"></div>
                 <span>Official Remark</span>
              </div>
              <div class="remark-content-primary">
                 {{ selectedWithdrawFeedback }}
              </div>
           </div>
           <div v-else class="tw-py-8">
              <div class="tw-w-16 tw-h-16 tw-bg-indigo-900/20 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-4">
                 <i class="fas fa-check-circle tw-text-indigo-600 tw-text-2xl"></i>
              </div>
              <p class="tw-text-slate-400 tw-m-0">Your request is being processed. No remarks available yet.</p>
           </div>
           
           <div class="tw-mt-8">
              <button @click="showModal = false" class="ma-btn-primary tw-w-full">
                 GOT IT
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
    const loading = ref(false)
    const showModal = ref(false)
    const selectedWithdrawFeedback = ref('')
    const currencySymbol = ref('₹')

    const formatAmount = (amount) => {
      if (!amount && amount !== 0) return '0.00'
      const val = parseFloat(amount)
      return val.toLocaleString('en-IN', { 
         minimumFractionDigits: 2, 
         maximumFractionDigits: 2 
      })
    }

    const formatDateTime = (dateString) => {
      if (!dateString) return '-'
      return new Date(dateString).toLocaleString('en-IN', { 
        month: 'short', day: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit', hour12: true 
      });
    }

    const getMethodIcon = (name) => {
      const n = (name || '').toLowerCase()
      if (n.includes('upi')) return 'fas fa-bolt'
      if (n.includes('bank')) return 'fas fa-university'
      return 'fas fa-wallet'
    }

    const showDetails = (withdraw) => {
      selectedWithdrawFeedback.value = withdraw.admin_feedback || ''
      showModal.value = true
    }

    const fetchWithdraws = async () => {
      loading.value = true
      try {
        const response = await api.get('/affiliate/withdraw/history', { 
           params: searchQuery.value ? { search: searchQuery.value } : {} 
        })
        if (response.data.status === 'success') {
          withdraws.value = response.data.data?.data ?? response.data.data ?? []
          if (response.data.data?.currency_symbol) currencySymbol.value = response.data.data.currency_symbol
        }
      } catch (error) {
        console.error('Fetch Error:', error)
      } finally {
        loading.value = false
      }
    }

    const handleSearch = () => fetchWithdraws()

    onMounted(() => fetchWithdraws())

    return {
      withdraws, searchQuery, loading, showModal,
      selectedWithdrawFeedback,
      currencySymbol,
      formatAmount, formatDateTime, showDetails,
      handleSearch, getMethodIcon, fetchWithdraws
    }
  }
}
</script>

<style scoped>
.ma-withdraw-wrap {
  --primary-main: #6366f1;
  --primary-glow: rgba(99, 102, 241, 0.4);
  --glass-bg: rgba(15, 23, 42, 0.7);
  --border-glass: rgba(255, 255, 255, 0.08);
}

.ma-glass-card {
  background: var(--glass-bg);
  backdrop-filter: blur(25px);
  -webkit-backdrop-filter: blur(25px);
  border: 1px solid var(--border-glass);
  border-radius: 28px;
  overflow: hidden;
  box-shadow: 0 10px 40px rgba(0,0,0,0.3);
}

.ma-card-header {
  padding: 24px 30px;
  border-bottom: 1px solid var(--border-glass);
  display: flex;
  flex-direction: column;
  gap: 20px;
}

@media (min-width: 768px) {
  .ma-card-header {
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
  }
}

.header-icon-box {
  width: 50px;
  height: 50px;
  background: linear-gradient(135deg, #4f46e5, #6366f1);
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 22px;
  box-shadow: 0 5px 20px rgba(79, 70, 229, 0.3);
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

.search-input {
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid var(--border-glass);
  border-radius: 12px;
  padding: 12px 18px;
  color: white;
  font-size: 14px;
  width: 100%;
}

.search-input:focus {
  outline: none;
  border-color: var(--primary-main);
  background: rgba(255, 255, 255, 0.07);
}

.ma-btn-primary-sm {
  background: var(--primary-main);
  color: white;
  padding: 10px 20px;
  border-radius: 12px;
  font-weight: 700;
  font-size: 13px;
  text-decoration: none;
  transition: all 0.3s;
  white-space: nowrap;
}

/* Modern Table */
.ma-modern-table { width: 100%; border-collapse: separate; border-spacing: 0; }
.ma-modern-table th { padding: 20px 30px; text-align: left; font-size: 11px; font-weight: 800; color: #64748b; letter-spacing: 1px; border-bottom: 1px solid var(--border-glass); }
.ma-modern-table td { padding: 24px 30px; border-bottom: 1px solid rgba(255,255,255,0.02); }
.ma-table-row:hover td { background: rgba(99, 102, 241, 0.05); }

.method-icon-primary {
  width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center;
  background: rgba(99, 102, 241, 0.1); color: #818cf8; font-size: 18px;
}

.ma-action-btn-primary {
  width: 40px; height: 40px; border-radius: 12px; background: rgba(255,255,255,0.03); border: 1px solid var(--border-glass);
  color: #94a3b8; cursor: pointer; transition: all 0.3s;
}

.ma-action-btn-primary:hover { background: var(--primary-main); color: white; transform: rotate(15deg); }

/* Mobile Cards */
.ma-mobile-card {
  background: rgba(255,255,255,0.03); border-radius: 20px; padding: 20px; border: 1px solid var(--border-glass);
}
.primary-border { border-left: 4px solid var(--primary-main); }

.ma-action-payout-btn {
  background: rgba(99, 102, 241, 0.1); color: #818cf8; border: 1px solid rgba(99, 102, 241, 0.2);
  padding: 8px 16px; border-radius: 10px; font-size: 10px; font-weight: 800;
}

/* Modal */
.ma-modal-overlay { position: fixed; inset: 0; z-index: 100; background: rgba(0,0,0,0.85); backdrop-filter: blur(10px); display: flex; align-items: center; justify-content: center; padding: 20px; }
.ma-modal-content { background: #0a0f1d; border-radius: 32px; width: 100%; max-width: 480px; overflow: hidden; }
.primary-modal-border { border: 1px solid rgba(99, 102, 241, 0.2); }
.primary-header-bg { background: linear-gradient(to right, #4338ca, #312e81); }
.ma-modal-header { padding: 28px; display: flex; justify-content: space-between; align-items: center; }
.modal-title-box { display: flex; align-items: center; gap: 12px; font-size: 18px; }
.modal-close-btn { background: rgba(255, 255, 255, 0.05); border: 0; width: 36px; height: 36px; border-radius: 10px; color: #94a3b8; cursor: pointer; }
.ma-modal-body { padding: 30px; }

.professional-remark-primary {
  background: rgba(99, 102, 241, 0.05);
  border: 1px solid rgba(99, 102, 241, 0.2);
  border-left: 4px solid #6366f1;
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

.remark-dot-primary {
  width: 6px;
  height: 6px;
  background: #6366f1;
  border-radius: 50%;
  box-shadow: 0 0 10px #6366f1;
}

.remark-header span {
  font-size: 10px;
  font-weight: 800;
  color: #818cf8;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.remark-content-primary {
  color: #e0e7ff;
  font-size: 14px;
  line-height: 1.5;
  font-weight: 500;
}

.ma-btn-primary {
  background: var(--primary-main); color: white; border: 0; padding: 16px; border-radius: 18px; font-weight: 800;
  box-shadow: 0 10px 20px -5px rgba(79, 70, 229, 0.4); cursor: pointer;
}

/* Spinners */
.primary-spinner { width: 34px; height: 34px; border: 3px solid rgba(99, 102, 241, 0.1); border-top-color: #6366f1; border-radius: 50%; animation: spin 0.8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

.loading-overlay { display: flex; align-items: center; justify-content: center; padding: 80px 0; }

/* Clean Status Badges - Remove Icons/Dots */
:deep(.status-badge-container .badge i),
:deep(.status-badge-container .badge::before) {
  display: none !important;
}

:deep(.status-badge-container .badge) {
  padding: 6px 14px !important; border-radius: 10px !important; font-size: 10px !important; font-weight: 900 !important;
  text-transform: uppercase; letter-spacing: 0.5px; border: 1px solid transparent; display: inline-flex;
}
:deep(.badge-success) { background: rgba(16, 185, 129, 0.1) !important; color: #10b981 !important; border-color: rgba(16, 185, 129, 0.2) !important; }
:deep(.badge-warning) { background: rgba(245, 158, 11, 0.1) !important; color: #fbbf24 !important; border-color: rgba(245, 158, 11, 0.2) !important; }
:deep(.badge-danger) { background: rgba(239, 68, 68, 0.1) !important; color: #fb7185 !important; border-color: rgba(239, 68, 68, 0.2) !important; }

.text-glow-primary { text-shadow: 0 0 15px rgba(99, 102, 241, 0.4); }
</style>
