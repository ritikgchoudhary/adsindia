<template>
  <MasterAdminLayout page-title="Deposits History">
    <div class="ma-deposits">
      <!-- Stats Row -->
      <div class="row g-3 mb-4">
        <div class="col-md-4 col-12">
          <div class="ma-stat-mini">
            <div class="ma-stat-mini__icon ma-stat-mini__icon--blue"><i class="fas fa-history"></i></div>
            <div><span class="ma-stat-mini__val">{{ summary.total || 0 }}</span><span class="ma-stat-mini__lbl">Records Found</span></div>
          </div>
        </div>
        <div class="col-md-4 col-12">
          <div class="ma-stat-mini">
            <div class="ma-stat-mini__icon ma-stat-mini__icon--green"><i class="fas fa-check-double"></i></div>
            <div><span class="ma-stat-mini__val">₹{{ formatAmount(summary.successful || 0) }}</span><span class="ma-stat-mini__lbl">Successful Volume</span></div>
          </div>
        </div>
        <div class="col-md-4 col-12">
          <div class="ma-stat-mini" @click="fetchDeposits(1)" style="cursor: pointer;">
            <div class="ma-stat-mini__icon ma-stat-mini__icon--indigo"><i class="fas fa-sync-alt"></i></div>
            <div><span class="ma-stat-mini__val">Refresh</span><span class="ma-stat-mini__lbl">Update View</span></div>
          </div>
        </div>
      </div>

      <!-- Search & Advanced Filter -->
      <div class="ma-card mb-4">
        <div class="ma-card__body d-flex flex-wrap gap-3 align-items-center">
          <div class="flex-grow-1" style="min-width: 200px;">
            <div class="ma-search-box">
              <i class="fas fa-search"></i>
              <input type="text" v-model="searchQuery" placeholder="Search TRX, Name, Email..." @input="debounceSearch" class="ma-search-input">
            </div>
          </div>
          
          <!-- Gateway Filter -->
          <select v-model="filterGateway" @change="fetchDeposits(1)" class="ma-select">
            <option value="">All Gateways</option>
            <option value="63">WatchPay</option>
            <option value="62">SimplyPay</option>
            <option value="61">Rupee Rush</option>
            <option value="manual">Manually / Admin</option>
          </select>

          <!-- Type Filter -->
          <select v-model="filterRemark" @change="fetchDeposits(1)" class="ma-select">
            <option value="">All Types</option>
            <option value="registration_fee">Registration</option>
            <option value="kyc_fee">KYC Fees</option>
            <option value="ad_plan_purchase">Ad Plan</option>
            <option value="withdrawal_fee">Withdrawal Fees</option>
            <option value="ad_certificate_fee">Ad Certificate</option>
            <option value="partner_program_gateway">Partner Program</option>
            <option value="course_plan_purchase_gateway">Course Package</option>
            <option value="package_upgrade_gateway">Package Upgrade</option>
          </select>

          <div class="d-flex gap-2 align-items-center">
            <input type="date" v-model="startDate" class="ma-date" @change="fetchDeposits(1)">
            <span class="ma-date-sep">to</span>
            <input type="date" v-model="endDate" class="ma-date" @change="fetchDeposits(1)">
          </div>
        </div>
      </div>

      <!-- Deposits Table -->
      <div class="ma-card ma-table-card">
        <div class="ma-card__header ma-card__header--gradient">
          <div>
            <h5 class="ma-card__title"><i class="fas fa-clipboard-check me-2"></i>Approved Deposits</h5>
            <p class="ma-card__subtitle">History of successful transaction payments</p>
          </div>
          <span class="ma-card__count">{{ totalDeposits }} records</span>
        </div>
        <div class="table-responsive ma-table-wrapper">
          <table class="ma-table">
            <thead>
              <tr>
                <th><i class="fas fa-barcode me-1"></i>TRX ID</th>
                <th><i class="fas fa-user me-1"></i>User</th>
                <th><i class="fas fa-tag me-1"></i>Type</th>
                <th><i class="fas fa-rupee-sign me-1"></i>Amount</th>
                <th><i class="fas fa-university me-1"></i>Gateway</th>
                <th><i class="fas fa-info-circle me-1"></i>Status</th>
                <th><i class="fas fa-calendar me-1"></i>Date</th>
                <th><i class="fas fa-cog me-1"></i>Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading">
                <td colspan="8" class="ma-table__loading"><div class="ma-spinner"></div></td>
              </tr>
              <tr v-else-if="deposits.length === 0">
                <td colspan="8" class="ma-table__empty">
                  <i class="fas fa-search text-muted"></i>
                  <p>No successful deposits found matching filters</p>
                </td>
              </tr>
              <tr v-else v-for="deposit in deposits" :key="deposit.id" class="ma-table-row">
                <td>
                  <code class="ma-trx-code">{{ deposit.trx }}</code>
                </td>
                <td>
                  <div v-if="deposit.user" class="ma-user-cell">
                    <div class="ma-user-avatar">{{ getInitials(deposit.user) }}</div>
                    <div>
                      <span class="ma-user-name">{{ deposit.user.firstname }} {{ deposit.user.lastname }}</span>
                      <span class="ma-user-adsid">{{ deposit.user.ads_id }}</span>
                    </div>
                  </div>
                  <span v-else class="text-muted">N/A</span>
                </td>
                <td>
                   <span class="badge-type">{{ deposit.order_type }}</span>
                </td>
                <td>
                  <span class="fw-bold text-success-bright">₹{{ formatAmount(deposit.after_charge) }}</span>
                </td>
                <td>
                  <span class="text-faded fw-600">{{ deposit.method_name || 'N/A' }}</span>
                </td>
                <td>
                  <span :class="'badge-solid badge-solid--' + deposit.status_class">
                    {{ deposit.status_text.toUpperCase() }}
                  </span>
                </td>
                <td>
                   <span class="text-muted" style="font-size:0.8rem">{{ formatDateTime(deposit.created_at) }}</span>
                </td>
                <td>
                  <div class="ma-action-buttons">
                    <button class="ma-action-btn ma-action-btn--view" @click="selectedOrder = deposit">
                      <i class="fas fa-eye"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="lastPage > 1" class="ma-pagination">
          <button class="ma-page-btn" :disabled="currentPage === 1" @click="fetchDeposits(currentPage - 1)">
            <i class="fas fa-chevron-left"></i>
          </button>
          <template v-for="p in paginationPages" :key="p">
            <button v-if="p === '...'" class="ma-page-btn ma-page-dots" disabled>...</button>
            <button v-else class="ma-page-btn" :class="{ 'ma-page-btn--active': p === currentPage }" @click="fetchDeposits(p)">{{ p }}</button>
          </template>
          <button class="ma-page-btn" :disabled="currentPage === lastPage" @click="fetchDeposits(currentPage + 1)">
            <i class="fas fa-chevron-right"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- View Details Modal (Unified Style) -->
    <div v-if="selectedOrder" class="ma-modal-overlay" @click.self="selectedOrder = null">
      <div class="ma-modal">
        <div class="ma-modal__header">
          <div class="d-flex align-items-center gap-2">
             <i class="fas fa-receipt text-primary"></i>
             <h5 class="m-0 text-white">Deposit #{{ selectedOrder.id }}</h5>
          </div>
          <button class="ma-modal__close" @click="selectedOrder = null"><i class="fas fa-times"></i></button>
        </div>
        <div class="ma-modal__body p-4">
          <div class="row g-3">
             <div class="col-md-7">
                <div class="ma-modal-card">
                  <h6 class="ma-modal-card__title"><i class="fas fa-info-circle me-2"></i>Transaction Details</h6>
                  <div class="ma-modal-info">
                    <div class="ma-modal-info__row"><span><i class="fas fa-hashtag me-2"></i>TRX ID:</span><code class="text-amber-bright fw-bold">{{ selectedOrder.trx }}</code></div>
                    <div class="ma-modal-info__row"><span><i class="fas fa-tag me-2"></i>Type:</span><span class="badge-solid badge-solid--primary">{{ selectedOrder.order_type }}</span></div>
                    <div class="ma-modal-info__row"><span><i class="fas fa-check-double me-2"></i>Status:</span><span :class="'badge-solid badge-solid--' + selectedOrder.status_class">{{ selectedOrder.status_text.toUpperCase() }}</span></div>
                    <div class="ma-modal-info__row"><span><i class="fas fa-university me-2"></i>Gateway:</span><span class="text-faded">{{ selectedOrder.method_name }}</span></div>
                    <div class="ma-modal-info__row"><span><i class="fas fa-calendar-alt me-2"></i>Date:</span><span class="text-faded">{{ formatDateTime(selectedOrder.created_at) }}</span></div>
                  </div>
                </div>
              </div>
              <div class="col-md-5">
                <div class="ma-modal-card">
                  <h6 class="ma-modal-card__title"><i class="fas fa-file-invoice-dollar me-2"></i>Amount Summary</h6>
                  <div class="ma-modal-info">
                    <div class="ma-modal-info__row"><span>Amount:</span><span class="text-white fw-bold" style="font-size:1.1rem">₹{{ formatAmount(selectedOrder.amount) }}</span></div>
                    <div class="ma-modal-info__row"><span>Charge:</span><span class="text-danger-bright fw-bold">₹{{ formatAmount(selectedOrder.charge) }}</span></div>
                    <div class="ma-modal-info__row"><span>Final:</span><span class="text-success-bright fw-extrabold" style="font-size:1.3rem">₹{{ formatAmount(selectedOrder.after_charge) }}</span></div>
                  </div>
                </div>
              </div>
             <div class="col-12">
                <div class="ma-modal-card" style="background: rgba(99,102,241,0.12); border: 1px solid rgba(99,102,241,0.25);">
                   <h6 class="ma-modal-card__title" style="color: #c7d2fe; margin-bottom: 1.5rem;"><i class="fas fa-user-circle me-2"></i>User Details</h6>
                   <div class="ma-modal-info ma-modal-info--horizontal">
                      <div class="ma-modal-info__row"><span><i class="fas fa-id-card me-2"></i>Name:</span><span class="text-white fw-bold">{{ selectedOrder.user?.firstname }} {{ selectedOrder.user?.lastname }}</span></div>
                      <div class="ma-modal-info__row"><span><i class="fas fa-envelope me-2"></i>Email:</span><span class="text-white">{{ selectedOrder.user?.email }}</span></div>
                      <div class="ma-modal-info__row"><span><i class="fas fa-mobile-alt me-2"></i>Number:</span><span class="text-white">{{ selectedOrder.user?.mobile || '—' }}</span></div>
                      <div class="ma-modal-info__row"><span><i class="fas fa-fingerprint me-2"></i>Ads ID:</span><span class="text-success-bright fw-bold" style="font-size: 1.15rem;">{{ selectedOrder.user?.ads_id }}</span></div>
                   </div>
                </div>
             </div>
          </div>
        </div>
        <div class="ma-modal__footer d-flex justify-content-end p-3 border-top border-white-5">
           <button class="ma-btn ma-btn--secondary" style="background: rgba(255,255,255,0.05); color: #fff;" @click="selectedOrder = null">Close</button>
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
  name: 'MasterAdminDeposits',
  components: { MasterAdminLayout },
  setup() {
    const deposits = ref([])
    const loading = ref(false)
    const searchQuery = ref('')
    const filterGateway = ref('')
    const filterRemark = ref('')
    const startDate = ref('')
    const endDate = ref('')
    const totalDeposits = ref(0)
    const currentPage = ref(1)
    const lastPage = ref(1)
    const selectedOrder = ref(null)
    const summary = ref({ total: 0, successful: 0 })
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

    const fetchDeposits = async (page) => {
      if (page) currentPage.value = page
      loading.value = true
      try {
        const params = { page: currentPage.value, per_page: 20 }
        if (searchQuery.value) params.search = searchQuery.value
        if (filterGateway.value) params.method_code = filterGateway.value
        if (filterRemark.value) params.remark = filterRemark.value
        if (startDate.value) params.start_date = startDate.value
        if (endDate.value) params.end_date = endDate.value

        const res = await api.get('/admin/deposits', { params })
        if (res.data?.status === 'success' && res.data.data) {
          deposits.value = res.data.data.deposits || []
          totalDeposits.value = res.data.data.total || 0
          currentPage.value = res.data.data.current_page || 1
          lastPage.value = res.data.data.last_page || 1
          summary.value = res.data.data.summary || { total: 0, successful: 0 }
        }
      } catch (e) {
        console.error('Error fetching deposits:', e)
      } finally {
        loading.value = false
      }
    }

    const debounceSearch = () => {
      clearTimeout(searchTimeout)
      searchTimeout = setTimeout(() => fetchDeposits(1), 400)
    }

    onMounted(() => {
      fetchDeposits(1)
    })

    return {
      deposits, loading, searchQuery, filterGateway, filterRemark,
      startDate, endDate,
      totalDeposits, currentPage, lastPage, paginationPages, summary,
      selectedOrder,
      formatAmount, formatDateTime, getInitials,
      fetchDeposits, debounceSearch
    }
  }
}
</script>

<style scoped>
.ma-deposits { animation: maFade 0.4s ease-out; }
@keyframes maFade { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }

.ma-stat-mini { display: flex; align-items: center; gap: 0.75rem; background: rgba(30, 41, 59, 0.8); border: 1px solid rgba(255,255,255,0.08); border-radius: 14px; padding: 1.25rem; }
.ma-stat-mini__icon { width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; color: #fff; flex-shrink: 0; }
.ma-stat-mini__icon--blue { background: linear-gradient(135deg, #3b82f6, #6366f1); }
.ma-stat-mini__icon--green { background: linear-gradient(135deg, #10b981, #059669); }
.ma-stat-mini__icon--indigo { background: linear-gradient(135deg, #6366f1, #4f46e5); }
.ma-stat-mini__val { display: block; color: #f1f5f9; font-size: 1.1rem; font-weight: 800; }
.ma-stat-mini__lbl { display: block; color: #94a3b8; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; }

.ma-card { background: rgba(30, 41, 59, 0.8); border: 1px solid rgba(255,255,255,0.08); border-radius: 18px; overflow: hidden; }
.ma-card__body { padding: 1rem 1.25rem; }
.ma-card__header { padding: 1.25rem 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.06); display: flex; align-items: center; justify-content: space-between; }
.ma-card__header--gradient { background: linear-gradient(135deg, rgba(99,102,241,0.1) 0%, rgba(139,92,246,0.1) 100%); border-bottom: 1px solid rgba(99,102,241,0.2); }
.ma-card__title { margin: 0; color: #f1f5f9; font-weight: 800; font-size: 1.1rem; }
.ma-card__subtitle { margin: 0.25rem 0 0; color: #64748b; font-size: 0.8rem; }
.ma-card__count { color: #818cf8; background: rgba(99,102,241,0.1); padding: 0.3rem 0.7rem; border-radius: 8px; font-size: 0.8rem; font-weight: 700; border: 1px solid rgba(99,102,241,0.2); }

.ma-search-box { display: flex; align-items: center; gap: 0.75rem; background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 0 1rem; }
.ma-search-input { flex: 1; background: none; border: none; outline: none; color: #fff; padding: 0.7rem 0; font-size: 0.9rem; }
.ma-select { background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 0.6rem 1rem; color: #f1f5f9; font-size: 0.88rem; outline: none; cursor: pointer; }
.ma-select option { background: #1e293b; color: #f1f5f9; }
.ma-date { background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; color: #fff; padding: 0.5rem 0.75rem; font-size: 0.85rem; }
.ma-date-sep { color: #475569; font-weight: 700; }

.ma-table { width: 100%; border-collapse: collapse; }
.ma-table th { padding: 1rem 1.25rem; color: #f1f5f9; font-weight: 800; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.08em; border-bottom: 2px solid rgba(99,102,241,0.3); background: rgba(15, 23, 42, 0.5); text-align: left; }
.ma-table td { padding: 1.25rem; color: #cbd5e1; border-bottom: 1px solid rgba(255,255,255,0.05); }
.ma-trx-code { background: rgba(15, 23, 42, 0.6); padding: 0.4rem 0.6rem; border-radius: 6px; color: #fbbf24; font-family: monospace; font-size: 0.85rem; border: 1px solid rgba(255,255,255,0.05); }

.ma-user-cell { display: flex; align-items: center; gap: 0.75rem; }
.ma-user-avatar { width: 32px; height: 32px; border-radius: 8px; background: #6366f1; color: #fff; font-weight: 700; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; }
.ma-user-name { display: block; color: #fff; font-weight: 600; font-size: 0.9rem; }
.ma-user-adsid { display: block; color: #818cf8; font-size: 0.75rem; font-weight: 700; }

.badge-type { background: rgba(99, 102, 241, 0.2); color: #c7d2fe; padding: 0.3rem 0.6rem; border-radius: 6px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; border: 1px solid rgba(99, 102, 241, 0.3); letter-spacing: 0.03em; }

.badge-solid { padding: 0.35rem 0.75rem; border-radius: 6px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; display: inline-block; }
.badge-solid--primary { background: rgba(96, 165, 250, 0.15); color: #60a5fa; border: 1px solid rgba(96, 165, 250, 0.3); }
.badge-solid--success { background: rgba(52, 211, 153, 0.15); color: #34d399; border: 1px solid rgba(52, 211, 153, 0.3); }
.badge-solid--danger { background: rgba(248, 113, 113, 0.15); color: #f87171; border: 1px solid rgba(248, 113, 113, 0.3); }

.ma-action-btn { width: 36px; height: 36px; border-radius: 10px; border: 1px solid rgba(59, 130, 246, 0.3); background: rgba(59, 130, 246, 0.1); color: #60a5fa; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; }
.ma-action-btn:hover { background: #3b82f6; color: #fff; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4); }

.ma-modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); backdrop-filter: blur(8px); display: flex; align-items: center; justify-content: center; z-index: 9999; padding: 1.5rem; }
.ma-modal { background: #1e293b; border: 1px solid rgba(255,255,255,0.1); border-radius: 20px; width: 100%; max-width: 650px; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); }
.ma-modal__header { padding: 1.25rem 1.5rem; background: rgba(15,23,42,0.4); display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.05); }
.ma-modal__close { background: none; border: none; color: #64748b; cursor: pointer; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.2s; }
.ma-modal__close:hover { background: rgba(255,255,255,0.05); color: #fff; }

.ma-modal-card { background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 14px; padding: 1.25rem; height: 100%; }
.ma-modal-card__title { color: #818cf8; font-size: 0.8rem; font-weight: 800; text-transform: uppercase; margin-bottom: 1.25rem; display: flex; align-items: center; }
.ma-modal-info__row { display: flex; align-items: center; justify-content: space-between; font-size: 0.95rem; margin-bottom: 0.8rem; }
.ma-modal-info__row span:first-child { color: #e2e8f0; font-weight: 700; opacity: 0.9; }
.ma-modal-info__row span:last-child { color: #ffffff; }
.ma-modal-info--horizontal { display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem 1.5rem; }

.text-amber-bright { color: #fbbf24 !important; }
.text-success-bright { color: #34d399 !important; }
.text-danger-bright { color: #f87171 !important; }
.fw-600 { font-weight: 600; }
.fw-extrabold { font-weight: 900; }

.ma-pagination { display: flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 1.25rem; border-top: 1px solid rgba(255,255,255,0.05); }
.ma-page-btn { padding: 0.5rem 1rem; border-radius: 8px; background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); color: #94a3b8; cursor: pointer; }
.ma-page-btn--active { background: #6366f1; color: #fff; border-color: #6366f1; }

.ma-spinner { width: 30px; height: 30px; border: 3px solid rgba(255,255,255,0.1); border-top-color: #6366f1; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto; }
@keyframes spin { to { transform: rotate(360deg); } }

.ma-table__loading, .ma-table__empty { text-align: center; padding: 4rem 0; color: #64748b; }
</style>
