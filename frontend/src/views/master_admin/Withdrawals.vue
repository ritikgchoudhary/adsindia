<template>
  <MasterAdminLayout page-title="Withdrawals">
    <div class="ma-withdrawals">
      <div class="ma-card">
        <div class="ma-card__header ma-card__header--gradient">
          <div>
            <h5 class="ma-card__title"><i class="fas fa-arrow-up me-2"></i>All Withdrawals</h5>
            <p class="ma-card__subtitle">Manage and monitor all withdrawal requests</p>
          </div>
        </div>
        <div class="ma-card__body">
          <!-- Stats -->
          <div class="row g-3 mb-4">
            <div class="col-md-3 col-6">
              <div class="ma-stat-mini" @click="filterStatus = ''">
                <div class="ma-stat-mini__icon ma-stat-mini__icon--blue"><i class="fas fa-file-invoice-dollar"></i></div>
                <div><span class="ma-stat-mini__val">{{ summary.total || 0 }}</span><span class="ma-stat-mini__lbl">Total</span></div>
              </div>
            </div>
            <div class="col-md-3 col-6">
              <div class="ma-stat-mini" @click="filterStatus = 'processing'">
                <div class="ma-stat-mini__icon ma-stat-mini__icon--amber"><i class="fas fa-clock"></i></div>
                <div><span class="ma-stat-mini__val">₹{{ formatAmount(summary.processing || 0) }}</span><span class="ma-stat-mini__lbl">Processing</span></div>
              </div>
            </div>
            <div class="col-md-3 col-6">
              <div class="ma-stat-mini" @click="filterStatus = 'success'">
                <div class="ma-stat-mini__icon ma-stat-mini__icon--green"><i class="fas fa-check-circle"></i></div>
                <div><span class="ma-stat-mini__val">₹{{ formatAmount(summary.success || 0) }}</span><span class="ma-stat-mini__lbl">Success</span></div>
              </div>
            </div>
            <div class="col-md-3 col-6">
              <div class="ma-stat-mini" @click="filterStatus = 'rejected'">
                <div class="ma-stat-mini__icon ma-stat-mini__icon--red"><i class="fas fa-times-circle"></i></div>
                <div><span class="ma-stat-mini__val">₹{{ formatAmount(summary.rejected || 0) }}</span><span class="ma-stat-mini__lbl">Rejected</span></div>
              </div>
            </div>
          </div>

          <!-- Filters -->
          <div class="ma-card mb-4">
            <div class="ma-card__body d-flex flex-wrap gap-3 align-items-center">
              <div class="flex-grow-1" style="min-width: 220px;">
                <div class="ma-search-box">
                  <i class="fas fa-search"></i>
                  <input
                    type="text"
                    v-model="searchQuery"
                    placeholder="Search trx, username, email..."
                    @input="debounceSearch"
                    class="ma-search-input"
                  >
                </div>
              </div>

              <select v-model="filterStatus" @change="fetchWithdrawals(1)" class="ma-select">
                <option value="">All Status</option>
                <option value="processing">Processing</option>
                <option value="success">Success</option>
                <option value="rejected">Rejected</option>
              </select>

              <select v-model="filterWallet" @change="fetchWithdrawals(1)" class="ma-select">
                <option value="">All Wallets</option>
                <option value="main">Main</option>
                <option value="affiliate">Affiliate</option>
              </select>

              <div class="d-flex gap-2 align-items-center">
                <input type="date" v-model="startDate" class="ma-date">
                <span class="ma-date-sep">to</span>
                <input type="date" v-model="endDate" class="ma-date">
              </div>

              <button class="ma-btn-refresh" @click="fetchWithdrawals()">
                <i class="fas fa-sync-alt"></i>
              </button>
            </div>
          </div>

          <!-- Table -->
          <div class="ma-card ma-table-card">
            <div class="ma-card__header ma-card__header--gradient">
              <div>
                <h5 class="ma-card__title"><i class="fas fa-arrow-up me-2"></i>Withdrawal History</h5>
                <p class="ma-card__subtitle">Filter by Processing / Success / Rejected</p>
              </div>
              <span class="ma-card__count">{{ total || 0 }} records</span>
            </div>

            <div class="table-responsive ma-table-wrapper">
              <table class="ma-table">
                <thead>
                  <tr>
                    <th><i class="fas fa-hashtag me-1"></i>ID</th>
                    <th><i class="fas fa-barcode me-1"></i>TRX</th>
                    <th><i class="fas fa-user me-1"></i>User</th>
                    <th><i class="fas fa-wallet me-1"></i>Wallet</th>
                    <th><i class="fas fa-university me-1"></i>Method</th>
                    <th><i class="fas fa-rupee-sign me-1"></i>Gross</th>
                    <th><i class="fas fa-percentage me-1"></i>Charge</th>
                    <th><i class="fas fa-hand-holding-usd me-1"></i>Net</th>
                    <th><i class="fas fa-info-circle me-1"></i>Status</th>
                    <th><i class="fas fa-calendar me-1"></i>Date</th>
                    <th><i class="fas fa-cog me-1"></i>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="loading">
                    <td colspan="11" class="ma-table__loading"><div class="ma-spinner"></div></td>
                  </tr>
                  <tr v-else-if="withdrawals.length === 0">
                    <td colspan="11" class="ma-table__empty">
                      <i class="fas fa-inbox"></i>
                      <p>No withdrawals found</p>
                    </td>
                  </tr>
                  <tr v-else v-for="w in withdrawals" :key="w.id" class="ma-table-row">
                    <td>
                      <div class="ma-id-badge">
                        <i class="fas fa-hashtag"></i>
                        <span>{{ w.id }}</span>
                      </div>
                    </td>
                    <td>
                      <div class="ma-trx-cell">
                        <code class="ma-trx-code">{{ w.trx }}</code>
                        <button class="ma-btn-copy-small" @click="copyTrx(w.trx)" title="Copy">
                          <i class="fas fa-copy"></i>
                        </button>
                      </div>
                    </td>
                    <td>
                      <div v-if="w.user" class="ma-user-cell">
                        <div class="ma-user-avatar">{{ getInitials(w.user) }}</div>
                        <div>
                          <span class="ma-user-name">{{ w.user.firstname }} {{ w.user.lastname }}</span>
                          <span class="ma-user-username">@{{ w.user.username }}</span>
                        </div>
                      </div>
                      <span v-else class="text-muted">N/A</span>
                    </td>
                    <td>
                      <span class="ma-badge" :class="walletBadgeClass(w.wallet)">
                        <i :class="walletIcon(w.wallet)" class="me-1"></i>{{ String(w.wallet || 'main').toUpperCase() }}
                      </span>
                    </td>
                    <td>
                      <span class="ma-badge ma-badge--secondary">{{ w.method?.name || 'N/A' }}</span>
                    </td>
                    <td class="ma-amount">₹{{ formatAmount(w.amount) }}</td>
                    <td class="ma-amount ma-amount--charge">₹{{ formatAmount(w.charge || 0) }}</td>
                    <td class="ma-amount ma-amount--net">₹{{ formatAmount(w.after_charge || 0) }}</td>
                    <td>
                      <span class="ma-badge" :class="statusInfo(w).badgeClass">
                        <i :class="statusInfo(w).icon" class="me-1"></i>{{ statusInfo(w).text }}
                      </span>
                    </td>
                    <td>
                      <div class="ma-date-cell">
                        <div class="ma-date-main">{{ formatDateTime(w.created_at) }}</div>
                        <div class="ma-date-sub">{{ w.created_at_human }}</div>
                      </div>
                    </td>
                    <td>
                      <div class="ma-actions">
                        <button class="ma-action-btn ma-action-btn--view" @click="viewDetails(w)" title="View details">
                          <i class="fas fa-eye"></i><span class="ma-action-tooltip">View</span>
                        </button>
                        <button v-if="isPending(w)" class="ma-action-btn ma-action-btn--success" @click="approve(w)" title="Approve">
                          <i class="fas fa-check"></i><span class="ma-action-tooltip">Approve</span>
                        </button>
                        <button v-if="isPending(w)" class="ma-action-btn ma-action-btn--danger" @click="reject(w)" title="Reject">
                          <i class="fas fa-times"></i><span class="ma-action-tooltip">Reject</span>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <div class="ma-pagination" v-if="lastPage > 1">
              <button class="ma-page-btn" :disabled="currentPage === 1 || loading" @click="fetchWithdrawals(currentPage - 1)">
                <i class="fas fa-chevron-left"></i>
              </button>
              <button
                v-for="p in paginationPages"
                :key="String(p)"
                class="ma-page-btn"
                :class="{ active: p === currentPage, 'ma-page-dots': p === '...' }"
                :disabled="p === '...' || loading"
                @click="p !== '...' && fetchWithdrawals(p)"
              >{{ p }}</button>
              <button class="ma-page-btn" :disabled="currentPage === lastPage || loading" @click="fetchWithdrawals(currentPage + 1)">
                <i class="fas fa-chevron-right"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </MasterAdminLayout>
</template>

<script>
import MasterAdminLayout from '../../components/master_admin/MasterAdminLayout.vue'
import api from '../../services/api'
import { ref, computed, onMounted } from 'vue'

export default {
  name: 'MasterAdminWithdrawals',
  components: { MasterAdminLayout },
  setup() {
    const withdrawals = ref([])
    const summary = ref({ total: 0, processing: 0, success: 0, rejected: 0 })
    const searchQuery = ref('')
    const filterStatus = ref('')
    const filterWallet = ref('')
    const startDate = ref('')
    const endDate = ref('')

    const loading = ref(false)
    const total = ref(0)
    const currentPage = ref(1)
    const lastPage = ref(1)

    let searchTimeout = null

    const formatAmount = (n) => {
      if (n === null || n === undefined) return '0.00'
      return parseFloat(n).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }

    const formatDateTime = (d) => {
      if (!d) return '—'
      try {
        const date = new Date(d)
        return date.toLocaleString('en-IN', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
      } catch (_) {
        return d
      }
    }

    const getInitials = (u) => {
      if (!u) return 'U'
      return ((u.firstname?.[0] || '') + (u.lastname?.[0] || '') || u.username?.[0] || 'U').toUpperCase()
    }

    const isPending = (w) => {
      // Status::PAYMENT_PENDING == 2 in this project
      return Number(w?.status) === 2 || String(w?.status_badge || '').toLowerCase().includes('pending')
    }

    const statusInfo = (w) => {
      const s = Number(w?.status)
      if (s === 1) return { text: 'Success', badgeClass: 'ma-badge--success', icon: 'fas fa-check-circle' }
      if (s === 2) return { text: 'Processing', badgeClass: 'ma-badge--warning', icon: 'fas fa-clock' }
      if (s === 3) return { text: 'Rejected', badgeClass: 'ma-badge--danger', icon: 'fas fa-times-circle' }
      // Some APIs send text only
      const t = String(w?.status_text || '').toLowerCase()
      if (t.includes('pending') || t.includes('processing')) return { text: w?.status_text || 'Processing', badgeClass: 'ma-badge--warning', icon: 'fas fa-clock' }
      if (t.includes('success') || t.includes('approved')) return { text: w?.status_text || 'Success', badgeClass: 'ma-badge--success', icon: 'fas fa-check-circle' }
      if (t.includes('reject')) return { text: w?.status_text || 'Rejected', badgeClass: 'ma-badge--danger', icon: 'fas fa-times-circle' }
      return { text: w?.status_text || 'Unknown', badgeClass: 'ma-badge--secondary', icon: 'fas fa-circle' }
    }

    const walletBadgeClass = (wallet) => {
      const w = String(wallet || 'main').toLowerCase()
      if (w === 'affiliate') return 'ma-badge--green'
      return 'ma-badge--secondary'
    }

    const walletIcon = (wallet) => {
      const w = String(wallet || 'main').toLowerCase()
      if (w === 'affiliate') return 'fas fa-users'
      return 'fas fa-wallet'
    }

    const paginationPages = computed(() => {
      const pages = []
      const totalPages = lastPage.value
      const cur = currentPage.value
      if (totalPages <= 7) { for (let i = 1; i <= totalPages; i++) pages.push(i); return pages }
      pages.push(1)
      if (cur > 3) pages.push('...')
      for (let i = Math.max(2, cur - 1); i <= Math.min(totalPages - 1, cur + 1); i++) pages.push(i)
      if (cur < totalPages - 2) pages.push('...')
      pages.push(totalPages)
      return pages
    })

    const fetchWithdrawals = async (page) => {
      if (page) currentPage.value = page
      loading.value = true
      try {
        const params = { page: currentPage.value, per_page: 20 }
        if (searchQuery.value) params.search = searchQuery.value
        if (filterStatus.value) params.status = filterStatus.value
        if (filterWallet.value) params.wallet = filterWallet.value
        if (startDate.value) params.start_date = startDate.value
        if (endDate.value) params.end_date = endDate.value

        const res = await api.get('/admin/withdrawals', { params })
        if (res.data?.status === 'success' && res.data.data) {
          withdrawals.value = res.data.data.withdrawals || []
          total.value = res.data.data.total || 0
          currentPage.value = res.data.data.current_page || 1
          lastPage.value = res.data.data.last_page || 1
          if (res.data.data.summary) summary.value = res.data.data.summary
        }
      } catch (e) {
        console.error('Error fetching withdrawals:', e)
        if (window.notify) window.notify('error', 'Failed to load withdrawals')
      } finally {
        loading.value = false
      }
    }

    const debounceSearch = () => {
      clearTimeout(searchTimeout)
      searchTimeout = setTimeout(() => fetchWithdrawals(1), 400)
    }

    const copyTrx = async (trx) => {
      if (!trx) return
      try {
        await navigator.clipboard.writeText(trx)
        if (window.notify) window.notify('success', 'Transaction ID copied')
      } catch (_) {
        if (window.notify) window.notify('success', 'Copied')
      }
    }

    const viewDetails = (w) => {
      const info = Array.isArray(w.withdraw_information) ? w.withdraw_information : []
      const pretty = info.map(i => `${i.name}: ${i.value}`).join('\n')
      alert(
        `Withdrawal #${w.id}\n\n` +
        `TRX: ${w.trx}\n` +
        `User: ${w.user?.username || 'N/A'}\n` +
        `Wallet: ${(w.wallet || 'main').toUpperCase()}\n` +
        `Gross: ₹${formatAmount(w.amount)}\n` +
        `Charge: ₹${formatAmount(w.charge || 0)}\n` +
        `Net: ₹${formatAmount(w.after_charge || 0)}\n\n` +
        (pretty ? `Info:\n${pretty}\n\n` : '') +
        (w.admin_feedback ? `Admin Feedback:\n${w.admin_feedback}\n\n` : '')
      )
    }

    const approve = async (w) => {
      if (!isPending(w)) return
      if (!confirm(`Approve withdrawal #${w.id} (TRX ${w.trx})?`)) return
      try {
        const res = await api.post('/admin/withdraw/approve', { id: w.id })
        if (res.data?.status === 'success') {
          if (window.notify) window.notify('success', 'Withdrawal approved')
          fetchWithdrawals()
        } else if (window.notify) window.notify('error', res.data?.message || 'Failed')
      } catch (e) {
        if (window.notify) window.notify('error', 'Failed to approve')
      }
    }

    const reject = async (w) => {
      if (!isPending(w)) return
      const reason = prompt(`Enter rejection reason for withdrawal #${w.id}:`)
      if (!reason) return
      try {
        const res = await api.post('/admin/withdraw/reject', { id: w.id, details: reason })
        if (res.data?.status === 'success') {
          if (window.notify) window.notify('success', 'Withdrawal rejected & refunded')
          fetchWithdrawals()
        } else if (window.notify) window.notify('error', res.data?.message || 'Failed')
      } catch (e) {
        if (window.notify) window.notify('error', 'Failed to reject')
      }
    }

    onMounted(() => fetchWithdrawals(1))

    return {
      withdrawals,
      summary,
      searchQuery,
      filterStatus,
      filterWallet,
      startDate,
      endDate,
      loading,
      total,
      currentPage,
      lastPage,
      paginationPages,
      formatAmount,
      formatDateTime,
      getInitials,
      isPending,
      statusInfo,
      walletBadgeClass,
      walletIcon,
      fetchWithdrawals,
      debounceSearch,
      copyTrx,
      viewDetails,
      approve,
      reject,
    }
  }
}
</script>

<style scoped>
.ma-withdrawals { animation: maFade 0.4s ease-out; }
@keyframes maFade { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }

/* Reuse same visual language as Deposits/Users pages */
.ma-stat-mini {
  display: flex; align-items: center; gap: 0.75rem;
  background: rgba(30, 41, 59, 0.8); border: 1px solid rgba(255,255,255,0.08);
  border-radius: 14px; padding: 1rem 1.15rem; cursor: pointer;
  transition: all 0.25s ease;
}
.ma-stat-mini:hover { background: rgba(30, 41, 59, 1); transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.3); }
.ma-stat-mini__icon {
  width: 42px; height: 42px; border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.1rem; color: #fff; flex-shrink: 0;
}
.ma-stat-mini__icon--blue { background: linear-gradient(135deg, #3b82f6, #6366f1); }
.ma-stat-mini__icon--green { background: linear-gradient(135deg, #10b981, #059669); }
.ma-stat-mini__icon--amber { background: linear-gradient(135deg, #f59e0b, #d97706); }
.ma-stat-mini__icon--red { background: linear-gradient(135deg, #ef4444, #dc2626); }
.ma-stat-mini__val { display: block; color: #f1f5f9; font-size: 1.1rem; font-weight: 800; line-height: 1.2; }
.ma-stat-mini__lbl { display: block; color: #94a3b8; font-size: 0.78rem; font-weight: 600; }

.ma-date { height: 40px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.10); background: rgba(15,23,42,0.55); color: #e2e8f0; padding: 0 0.75rem; }
.ma-date-sep { color: rgba(255,255,255,0.5); font-weight: 700; }

.ma-amount { font-weight: 800; color: #e2e8f0; }
.ma-amount--charge { color: #fca5a5; }
.ma-amount--net { color: #86efac; }
.ma-date-cell .ma-date-main { color: rgba(255,255,255,0.88); font-weight: 700; }
.ma-date-cell .ma-date-sub { color: rgba(255,255,255,0.55); font-size: 0.78rem; }

.ma-actions { display:flex; gap:8px; justify-content:flex-end; }
.ma-action-btn { width:34px; height:34px; border-radius:10px; border: 1px solid rgba(255,255,255,0.10); background: rgba(15,23,42,0.55); color:#e2e8f0; position:relative; }
.ma-action-btn:hover { transform: translateY(-1px); }
.ma-action-btn--view:hover { background: rgba(99,102,241,0.30); border-color: rgba(99,102,241,0.55); }
.ma-action-btn--success:hover { background: rgba(16,185,129,0.25); border-color: rgba(16,185,129,0.55); }
.ma-action-btn--danger:hover { background: rgba(239,68,68,0.25); border-color: rgba(239,68,68,0.55); }
.ma-action-tooltip { display:none; }

/* Status badge wrapper from WithdrawHistory styles */
/* Local badges (avoid v-html rendering issues) */
.ma-badge { display:inline-flex; align-items:center; gap:6px; padding: 6px 10px; border-radius: 10px; font-size: 12px; font-weight: 800; text-transform: uppercase; border: 1px solid rgba(255,255,255,0.10); }
.ma-badge--success { background: rgba(16,185,129,0.15); color: #34d399; border-color: rgba(16,185,129,0.25); }
.ma-badge--warning { background: rgba(245,158,11,0.18); color: #fbbf24; border-color: rgba(245,158,11,0.28); }
.ma-badge--danger { background: rgba(239,68,68,0.18); color: #fca5a5; border-color: rgba(239,68,68,0.28); }
.ma-badge--secondary { background: rgba(148,163,184,0.10); color: #cbd5e1; border-color: rgba(148,163,184,0.18); }
.ma-badge--green { background: rgba(16,185,129,0.12); color: #86efac; border-color: rgba(16,185,129,0.20); }

.ma-id-badge { display:inline-flex; align-items:center; gap:6px; padding: 6px 10px; border-radius: 12px; background: rgba(99,102,241,0.15); border: 1px solid rgba(99,102,241,0.25); color:#e2e8f0; font-weight: 900; }
.ma-id-badge i { color: #a5b4fc; font-size: 0.8rem; }

.ma-pagination { display:flex; gap:8px; justify-content:center; padding: 14px 10px; }
.ma-page-btn { background: rgba(15,23,42,0.55); border: 1px solid rgba(255,255,255,0.10); color:#e2e8f0; border-radius: 12px; padding: 8px 12px; font-weight: 800; }
.ma-page-btn.active { background: rgba(99,102,241,0.35); border-color: rgba(99,102,241,0.55); }
.ma-page-btn.ma-page-dots { opacity: 0.7; }
</style>
