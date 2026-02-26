<template>
  <DashboardLayout page-title="Affiliate Withdraw History" :dark-theme="true">
    <div class="ma-withdraw-wrap tw-animate-fade-in tw-py-6">
      
      <!-- Main Log Card -->
      <div class="ma-glass-card">
        <div class="ma-card-header">
          <div class="tw-flex tw-items-center tw-gap-3">
             <div class="header-icon-box text-glow-primary">
                <i class="fas fa-list-ul"></i>
             </div>
             <div>
                <h5 class="tw-text-white tw-font-black tw-text-xl tw-m-0">Payout Records</h5>
                <p class="tw-text-slate-400 tw-text-sm tw-m-0">Tracking your affiliate commission flow</p>
             </div>
          </div>
          
          <div class="header-actions">
            <form @submit.prevent="handleSearch" class="tw-relative tw-w-full md:tw-w-72">
              <input 
                type="search" 
                v-model="searchQuery" 
                placeholder="Search by Transaction ID..." 
                class="search-input"
              >
              <button type="submit" class="search-btn">
                <i class="fas fa-search"></i>
              </button>
            </form>
            <router-link to="/user/affiliate-withdraw" class="ma-btn-create">
               <i class="fas fa-plus"></i>
               <span>New Request</span>
            </router-link>
          </div>
        </div>

        <!-- Desktop Table -->
        <div class="tw-hidden lg:tw-block">
          <table class="ma-modern-table">
            <thead>
              <tr>
                <th>Gateway & TRX</th>
                <th>Requested At</th>
                <th>Amount</th>
                <th class="tw-text-left">Status</th>
                <th class="tw-text-right tw-pr-8">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="withdraw in withdraws" :key="withdraw?.id" class="ma-table-row">
                <td>
                  <div class="tw-flex tw-items-center tw-gap-4">
                    <div class="method-icon-wrapper">
                      <div class="method-icon-circle">
                        <i :class="getMethodIcon(withdraw.method?.name)"></i>
                      </div>
                    </div>
                    <div>
                      <div class="tw-text-white tw-font-bold tw-text-base">{{ withdraw.method?.name || 'Gateway' }}</div>
                      <div class="tw-text-indigo-400/70 tw-text-xs tw-font-mono tw-flex tw-items-center tw-gap-1">
                        <i class="fas fa-hashtag tw-text-[10px]"></i> {{ withdraw.trx }}
                      </div>
                    </div>
                  </div>
                </td>
                <td>
                  <div class="tw-text-slate-200 tw-font-semibold tw-text-sm">{{ formatDateTime(withdraw.created_at) }}</div>
                  <div class="tw-text-slate-500 tw-text-[11px] tw-mt-1 tw-flex tw-items-center tw-gap-1">
                    <i class="far fa-clock"></i> {{ withdraw.created_at_human }}
                  </div>
                </td>
                <td>
                  <div class="tw-text-white tw-font-black tw-text-xl">
                    <span class="tw-text-indigo-400 tw-text-sm tw-font-bold tw-mr-1">{{ currencySymbol }}</span>{{ formatAmount(withdraw.amount) }}
                  </div>
                </td>
                <td class="tw-text-left">
                   <div class="status-badge-container" v-html="withdraw.status_badge"></div>
                </td>
                <td class="tw-text-right tw-pr-8">
                   <button @click="showDetails(withdraw)" class="action-btn-view" title="View Details">
                      <i class="fas fa-info-circle"></i>
                   </button>
                </td>
              </tr>
              <tr v-if="withdraws.length === 0 && !loading">
                 <td colspan="5">
                    <div class="empty-state-container">
                       <div class="empty-state-icon">
                         <i class="fas fa-inbox"></i>
                       </div>
                       <h4>No Records Found</h4>
                       <p>You haven't made any withdrawal requests yet.</p>
                       <router-link to="/user/affiliate-withdraw" class="ma-btn-primary-ghost">
                         Start Payout
                       </router-link>
                    </div>
                 </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Mobile / Tablet Card Layout -->
        <div class="lg:tw-hidden tw-p-3 sm:tw-p-4 tw-space-y-4">
           <div v-for="withdraw in withdraws" :key="withdraw.id" class="mobile-payout-card">
              <div class="tw-flex tw-justify-between tw-items-center tw-mb-4">
                 <div class="tw-flex tw-items-center tw-gap-3">
                    <div class="method-icon-mini">
                       <i :class="getMethodIcon(withdraw.method?.name)"></i>
                    </div>
                    <div>
                       <div class="tw-text-white tw-font-bold tw-text-sm">{{ withdraw.method?.name }}</div>
                       <div class="tw-text-[10px] tw-text-indigo-400/60 tw-font-mono">{{ withdraw.trx }}</div>
                    </div>
                 </div>
                 <div class="status-badge-container tw-scale-90 tw-origin-right" v-html="withdraw.status_badge"></div>
              </div>
              
              <div class="tw-flex tw-justify-between tw-items-end">
                 <div>
                    <div class="tw-text-white tw-font-black tw-text-xl">
                       <span class="tw-text-indigo-400 tw-text-xs tw-font-bold tw-mr-0.5">{{ currencySymbol }}</span>{{ formatAmount(withdraw.amount) }}
                    </div>
                    <div class="tw-text-[8px] tw-text-slate-500 tw-mt-1">
                       <i class="far fa-clock"></i> {{ withdraw.created_at_human }}
                    </div>
                 </div>
                 <button @click="showDetails(withdraw)" class="mobile-action-btn">
                    Details <i class="fas fa-chevron-right tw-ml-0.5"></i>
                 </button>
              </div>
           </div>
           
           <div v-if="withdraws.length === 0 && !loading" class="empty-state-container">
              <div class="empty-state-icon"><i class="fas fa-search"></i></div>
              <h4 class="tw-text-white">No history records</h4>
              <p class="tw-text-slate-500">You haven't made any withdrawal requests yet.</p>
           </div>
        </div>

        <!-- Loader -->
        <div v-if="loading" class="loading-full-overlay">
           <div class="modern-loader">
             <div class="loader-ring"></div>
             <p>Syncing Payouts...</p>
           </div>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="ma-modal-overlay" @click.self="showModal = false">
      <div class="ma-modal-box tw-animate-modal-in">
        <div class="modal-glow"></div>
        <div class="modal-inner">
          <div class="ma-modal-header-modern">
             <div class="tw-flex tw-items-center tw-gap-3">
                <div class="modal-header-icon">
                  <i class="fas fa-file-invoice"></i>
                </div>
                <div>
                  <h5 class="tw-m-0 tw-text-white tw-font-black">Request Details</h5>
                  <p class="tw-m-0 tw-text-[10px] tw-text-slate-400 tw-uppercase tw-tracking-widest">Withdrawal Audit</p>
                </div>
             </div>
             <button @click="showModal = false" class="modal-x-btn">
                <i class="fas fa-times"></i>
             </button>
          </div>
          
          <div class="ma-modal-body text-center">
             <div v-if="selectedWithdrawFeedback" class="feedback-container">
                <div class="feedback-label">
                   <div class="feedback-pulse"></div>
                   <span>Official Feedback</span>
                </div>
                <div class="feedback-text">
                   {{ selectedWithdrawFeedback }}
                </div>
             </div>
             <div v-else class="tw-py-10">
                <div class="no-remark-icon">
                   <i class="fas fa-tasks"></i>
                </div>
                <h4 class="tw-text-white tw-font-bold tw-mb-2">Processing Request</h4>
                <p class="tw-text-slate-500 tw-text-sm tw-max-w-[280px] tw-mx-auto">Our financial team is currently auditing this withdrawal. No specific remarks generated yet.</p>
             </div>
             
             <div class="tw-mt-10">
                <button @click="showModal = false" class="ma-btn-confirm">
                   UNDERSTOOD
                </button>
             </div>
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
  --primary: #6366f1;
  --primary-bright: #818cf8;
  --primary-glow: rgba(99, 102, 241, 0.4);
  --dark-glass: rgba(15, 23, 42, 0.8);
  --border-glass: rgba(255, 255, 255, 0.08);
}

/* Stats Cards */
.ma-stat-card {
  background: var(--dark-glass);
  backdrop-filter: blur(20px);
  border: 1px solid var(--border-glass);
  border-radius: 24px;
  padding: 24px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}
.stat-icon-box {
  width: 52px; height: 52px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 20px;
}

/* Main Card */
.ma-glass-card {
  background: var(--dark-glass);
  backdrop-filter: blur(30px);
  -webkit-backdrop-filter: blur(30px);
  border: 1px solid var(--border-glass);
  border-radius: 32px;
  overflow: hidden;
  box-shadow: 0 20px 50px rgba(0,0,0,0.4);
}

.ma-card-header {
  padding: 30px;
  border-bottom: 1px solid var(--border-glass);
  display: flex;
  flex-direction: column;
  gap: 25px;
}
@media (min-width: 1024px) {
  .ma-card-header { flex-direction: row; justify-content: space-between; align-items: center; }
}

.header-icon-box {
  width: 54px; height: 54px;
  background: linear-gradient(135deg, #4f46e5, #6366f1);
  border-radius: 18px;
  display: flex; align-items: center; justify-content: center;
  color: white; font-size: 22px;
  box-shadow: 0 8px 25px rgba(79, 70, 229, 0.4);
}

.header-actions { display: flex; align-items: center; gap: 15px; flex-wrap: wrap; }

.search-input {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid var(--border-glass);
  border-radius: 15px;
  padding: 12px 20px 12px 45px;
  color: white; font-size: 14px; width: 100%;
  transition: all 0.3s;
}
.search-input:focus { outline: none; border-color: var(--primary); background: rgba(255,255,255,0.06); box-shadow: 0 0 15px var(--primary-glow); }
.search-btn { position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: #64748b; border: 0; background: transparent; }

.ma-btn-create {
  background: linear-gradient(to right, #6366f1, #4f46e5);
  color: white; padding: 12px 24px; border-radius: 15px; font-weight: 800; font-size: 14px;
  text-decoration: none; transition: all 0.3s; display: flex; align-items: center; gap: 10px;
  box-shadow: 0 10px 20px -5px rgba(79, 70, 229, 0.4);
}
.ma-btn-create:hover { transform: translateY(-2px); box-shadow: 0 15px 25px -5px rgba(79, 70, 229, 0.6); }

/* Modern Table */
.ma-modern-table { width: 100%; border-collapse: separate; border-spacing: 0; }
.ma-modern-table th { padding: 22px 30px; text-align: left; font-size: 12px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1.5px; border-bottom: 1px solid var(--border-glass); }
.ma-modern-table td { padding: 25px 30px; border-bottom: 1px solid rgba(255,255,255,0.03); vertical-align: middle; }
.ma-table-row:hover td { background: rgba(99, 102, 241, 0.05); }

.method-icon-wrapper { position: relative; width: 48px; height: 48px; }
.method-icon-circle { 
  width: 100%; height: 100%; border-radius: 16px; 
  background: rgba(99, 102, 241, 0.1); border: 1px solid rgba(99, 102, 241, 0.2);
  display: flex; align-items: center; justify-content: center; color: var(--primary-bright); font-size: 20px;
}

/* Force Column Alignments */
.ma-modern-table th.tw-text-right,
.ma-modern-table td.tw-text-right {
  text-align: right !important;
  padding-right: 40px !important;
}

.ma-modern-table th.tw-text-left,
.ma-modern-table td.tw-text-left {
  text-align: left !important;
}

.action-btn-view {
  width: 44px; height: 44px; border-radius: 14px; background: rgba(255,255,255,0.04); border: 1px solid var(--border-glass);
  color: #94a3b8; cursor: pointer; transition: all 0.3s;
}
.action-btn-view:hover { background: var(--primary); color: white; border-color: var(--primary); transform: scale(1.1); }

/* Empty State */
.empty-state-container { padding: 80px 0; text-align: center; }
.empty-state-icon { font-size: 60px; color: rgba(255,255,255,0.05); margin-bottom: 20px; }
.empty-state-container h4 { color: white; font-weight: 800; margin-bottom: 10px; }
.empty-state-container p { color: #64748b; margin-bottom: 25px; }
.ma-btn-primary-ghost {
  display: inline-block; padding: 12px 30px; border: 2px solid var(--primary); border-radius: 12px;
  color: var(--primary); font-weight: 800; text-decoration: none; transition: all 0.3s;
}
.ma-btn-primary-ghost:hover { background: var(--primary); color: white; }

/* Mobile Cards */
.mobile-payout-card {
  background: rgba(255,255,255,0.03); border: 1px solid var(--border-glass); border-radius: 24px; padding: 20px;
}
.method-icon-mini {
  width: 40px; height: 40px; border-radius: 12px; background: rgba(99, 102, 241, 0.1); 
  display: flex; align-items: center; justify-content: center; color: var(--primary-bright);
}
.mobile-action-btn {
  background: var(--primary); color: white; border: 0; padding: 10px 18px; border-radius: 12px; font-weight: 800; font-size: 11px;
}

/* Modal Modern */
.ma-modal-overlay { position: fixed; inset: 0; z-index: 1000; background: rgba(0,0,0,0.85); backdrop-filter: blur(15px); display: flex; align-items: center; justify-content: center; padding: 20px; }
.ma-modal-box { position: relative; width: 100%; max-width: 450px; }
.modal-glow { position: absolute; inset: -10px; background: var(--primary); border-radius: 40px; filter: blur(30px); opacity: 0.15; }
.modal-inner { background: #0c1120; border: 1px solid rgba(255,255,255,0.1); border-radius: 35px; overflow: hidden; position: relative; z-index: 10; }
.ma-modal-header-modern { padding: 30px; border-bottom: 1px solid var(--border-glass); display: flex; justify-content: space-between; align-items: center; }
.modal-header-icon { width: 45px; height: 45px; border-radius: 12px; background: rgba(99, 102, 241, 0.15); color: #818cf8; display: flex; align-items: center; justify-content: center; font-size: 18px; }
.modal-x-btn { background: rgba(255,255,255,0.05); color: #64748b; border: 0; width: 34px; height: 34px; border-radius: 10px; cursor: pointer; transition: all 0.3s; }
.modal-x-btn:hover { background: rgba(239, 68, 68, 0.2); color: #ef4444; }

.feedback-container { background: rgba(99, 102, 241, 0.05); border: 1px solid rgba(99, 102, 241, 0.1); border-radius: 20px; padding: 25px; text-align: left; }
.feedback-label { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
.feedback-pulse { width: 8px; height: 8px; border-radius: 50%; background: #6366f1; box-shadow: 0 0 10px #6366f1; animation: pulse 2s infinite; }
.feedback-label span { font-size: 11px; font-weight: 800; color: #818cf8; text-transform: uppercase; letter-spacing: 1px; }
.feedback-text { color: #e0e7ff; font-size: 15px; line-height: 1.6; font-weight: 500; }

.no-remark-icon { font-size: 40px; color: #334155; margin-bottom: 20px; }
.ma-btn-confirm {
  width: 100%; background: var(--primary); color: white; border: 0; padding: 18px; border-radius: 20px; font-weight: 900; 
  letter-spacing: 1px; cursor: pointer; transition: all 0.3s;
}
.ma-btn-confirm:hover { transform: translateY(-3px); box-shadow: 0 10px 25px -5px rgba(79, 70, 229, 0.5); }

/* Loader */
.loading-full-overlay { padding: 100px 0; display: flex; align-items: center; justify-content: center; }
.modern-loader { text-align: center; }
.loader-ring { width: 50px; height: 50px; border: 4px solid rgba(99, 102, 241, 0.1); border-top-color: var(--primary); border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 15px; }
.modern-loader p { color: #818cf8; font-weight: 700; font-size: 14px; letter-spacing: 1px; }

/* Status Badges Override */
:deep(.status-badge-container .badge) {
  padding: 8px 16px !important; border-radius: 12px !important; font-size: 10px !important; font-weight: 900 !important;
  text-transform: uppercase; letter-spacing: 0.5px; border: 1px solid transparent; display: inline-flex;
}
:deep(.badge-success) { background: rgba(56, 189, 248, 0.1) !important; color: #38bdf8 !important; border-color: rgba(56, 189, 248, 0.2) !important; }
:deep(.badge-warning) { background: rgba(244, 63, 94, 0.1) !important; color: #f43f5e !important; border-color: rgba(244, 63, 94, 0.2) !important; }
:deep(.badge-danger) { background: rgba(239, 68, 68, 0.1) !important; color: #ef4444 !important; border-color: rgba(239, 68, 68, 0.2) !important; }

@media (max-width: 640px) {
  /* Stats Cards */
  .tw-grid.tw-grid-cols-1.md\:tw-grid-cols-3 { gap: 0.75rem !important; margin-bottom: 1.25rem !important; }
  .ma-stat-card { padding: 1rem !important; border-radius: 1.25rem !important; }
  .stat-icon-box { width: 2.5rem !important; height: 2.5rem !important; border-radius: 0.75rem !important; font-size: 1rem !important; }
  h3.tw-text-2xl { font-size: 1.25rem !important; }
  p.tw-text-xs { font-size: 9px !important; }

  /* Main Card Header */
  .ma-glass-card { border-radius: 1.5rem !important; }
  .ma-card-header { padding: 1.25rem !important; gap: 1rem !important; }
  .header-icon-box { width: 2.75rem !important; height: 2.75rem !important; border-radius: 0.85rem !important; font-size: 1.25rem !important; }
  h5.tw-text-xl { font-size: 1.1rem !important; }
  p.tw-text-sm { font-size: 0.75rem !important; }

  /* Header Actions */
  .header-actions { gap: 0.75rem !important; }
  .search-input { padding: 0.65rem 1rem 0.65rem 2.25rem !important; font-size: 0.75rem !important; border-radius: 0.75rem !important; }
  .search-btn { left: 12px !important; }
  .ma-btn-create { padding: 0.65rem 1rem !important; font-size: 0.75rem !important; border-radius: 0.75rem !important; }

  /* Mobile Payout Cards - Compact */
  .lg\:tw-hidden.tw-p-4 { padding: 0.65rem !important; gap: 0.5rem !important; }
  .mobile-payout-card { padding: 0.85rem 1rem !important; border-radius: 1rem !important; }
  .method-icon-mini { width: 1.75rem !important; height: 1.75rem !important; border-radius: 0.5rem !important; font-size: 0.85rem !important; }
  .tw-text-white.tw-font-bold.tw-text-sm { font-size: 0.75rem !important; }
  .tw-text-white.tw-font-black.tw-text-2xl { font-size: 1.25rem !important; }
  .mobile-action-btn { padding: 0.4rem 0.75rem !important; border-radius: 0.5rem !important; font-size: 8px !important; }
  .tw-mt-4.tw-pt-4 { margin-top: 0.5rem !important; padding-top: 0.5rem !important; }

  /* Modal */
  .modal-inner { border-radius: 1.75rem !important; }
  .ma-modal-header-modern { padding: 1.25rem !important; }
  .modal-header-icon { width: 2.25rem !important; height: 2.25rem !important; border-radius: 0.65rem !important; font-size: 1rem !important; }
  h5.tw-text-white.tw-font-black { font-size: 1rem !important; }
  .ma-modal-body { padding: 1.25rem !important; }
  .feedback-container { padding: 1.15rem !important; border-radius: 1rem !important; }
  .feedback-text { font-size: 0.85rem !important; }
  .ma-btn-confirm { padding: 0.85rem !important; font-size: 0.95rem !important; border-radius: 1rem !important; }
  .tw-mt-10 { margin-top: 1.5rem !important; }
}
</style>
