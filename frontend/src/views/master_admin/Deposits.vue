<template>
  <MasterAdminLayout page-title="Deposits">
    <div class="ma-deposits">
      <!-- Stats Row -->
      <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
          <div class="ma-stat-mini" @click="filterStatus = ''">
            <div class="ma-stat-mini__icon ma-stat-mini__icon--blue"><i class="fas fa-file-invoice-dollar"></i></div>
            <div><span class="ma-stat-mini__val">{{ summary.total || 0 }}</span><span class="ma-stat-mini__lbl">Total</span></div>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="ma-stat-mini" @click="filterStatus = 'successful'">
            <div class="ma-stat-mini__icon ma-stat-mini__icon--green"><i class="fas fa-check-circle"></i></div>
            <div><span class="ma-stat-mini__val">₹{{ formatAmount(summary.successful || 0) }}</span><span class="ma-stat-mini__lbl">Successful</span></div>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="ma-stat-mini" @click="filterStatus = 'pending'">
            <div class="ma-stat-mini__icon ma-stat-mini__icon--amber"><i class="fas fa-clock"></i></div>
            <div><span class="ma-stat-mini__val">₹{{ formatAmount(summary.pending || 0) }}</span><span class="ma-stat-mini__lbl">Pending</span></div>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="ma-stat-mini" @click="filterStatus = 'rejected'">
            <div class="ma-stat-mini__icon ma-stat-mini__icon--red"><i class="fas fa-times-circle"></i></div>
            <div><span class="ma-stat-mini__val">₹{{ formatAmount(summary.rejected || 0) }}</span><span class="ma-stat-mini__lbl">Rejected</span></div>
          </div>
        </div>
      </div>

      <!-- Search & Filter -->
      <div class="ma-card mb-4">
        <div class="ma-card__body d-flex flex-wrap gap-3 align-items-center">
          <div class="flex-grow-1" style="min-width: 200px;">
            <div class="ma-search-box">
              <i class="fas fa-search"></i>
              <input type="text" v-model="searchQuery" placeholder="Search by transaction ID, username, email..." @input="debounceSearch" class="ma-search-input">
            </div>
          </div>
          <select v-model="filterStatus" @change="fetchDeposits(1)" class="ma-select">
            <option value="">All Status</option>
            <option value="gateway_orders">Gateway Orders</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="successful">Successful</option>
            <option value="rejected">Rejected</option>
            <option value="initiated">Initiated</option>
          </select>
          <div class="d-flex gap-2 align-items-center">
            <input type="date" v-model="startDate" class="ma-date">
            <span class="ma-date-sep">to</span>
            <input type="date" v-model="endDate" class="ma-date">
          </div>
          <button class="ma-btn-refresh" @click="fetchDeposits()">
            <i class="fas fa-sync-alt"></i>
          </button>
        </div>
      </div>

      <!-- Deposits Table -->
      <div class="ma-card ma-table-card">
        <div class="ma-card__header ma-card__header--gradient">
          <div>
            <h5 class="ma-card__title"><i class="fas fa-arrow-down me-2"></i>All Deposits</h5>
            <p class="ma-card__subtitle">Manage and monitor all user deposits</p>
          </div>
          <span class="ma-card__count">{{ totalDeposits }} deposits</span>
        </div>
        <div class="table-responsive ma-table-wrapper">
          <table class="ma-table">
            <thead>
              <tr>
                <th><i class="fas fa-hashtag me-1"></i>ID</th>
                <th><i class="fas fa-barcode me-1"></i>Transaction ID</th>
                <th><i class="fas fa-user me-1"></i>User</th>
                <th><i class="fas fa-rupee-sign me-1"></i>Amount</th>
                <th><i class="fas fa-percentage me-1"></i>Charge</th>
                <th><i class="fas fa-wallet me-1"></i>After Charge</th>
                <th><i class="fas fa-credit-card me-1"></i>Method</th>
                <th><i class="fas fa-info-circle me-1"></i>Status</th>
                <th><i class="fas fa-calendar me-1"></i>Date</th>
                <th><i class="fas fa-cog me-1"></i>Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading">
                <td colspan="10" class="ma-table__loading"><div class="ma-spinner"></div></td>
              </tr>
              <tr v-else-if="deposits.length === 0">
                <td colspan="10" class="ma-table__empty">
                  <i class="fas fa-file-invoice-dollar"></i>
                  <p>No deposits found</p>
                </td>
              </tr>
              <tr v-else v-for="deposit in deposits" :key="deposit.id" class="ma-table-row">
                <td>
                  <div class="ma-id-badge">
                    <i class="fas fa-hashtag"></i>
                    <span>{{ deposit.id }}</span>
                  </div>
                </td>
                <td>
                  <div class="ma-trx-cell">
                    <code class="ma-trx-code">{{ deposit.trx }}</code>
                    <button class="ma-btn-copy-small" @click="copyTrx(deposit.trx)" title="Copy">
                      <i class="fas fa-copy"></i>
                    </button>
                  </div>
                </td>
                <td>
                  <div v-if="deposit.user" class="ma-user-cell">
                    <div class="ma-user-avatar">{{ getInitials(deposit.user) }}</div>
                    <div>
                      <span class="ma-user-name">{{ deposit.user.firstname }} {{ deposit.user.lastname }}</span>
                      <span class="ma-user-username">@{{ deposit.user.username }}</span>
                    </div>
                  </div>
                  <span v-else class="text-muted">N/A</span>
                </td>
                <td>
                  <div class="ma-amount-cell ma-amount-credit">
                    <i class="fas fa-arrow-down"></i>
                    <span>₹{{ formatAmount(deposit.amount) }}</span>
                  </div>
                </td>
                <td>
                  <span class="ma-table__charge">₹{{ formatAmount(deposit.charge) }}</span>
                </td>
                <td>
                  <div class="ma-balance-cell">
                    <i class="fas fa-wallet"></i>
                    <span class="ma-table__balance">₹{{ formatAmount(deposit.after_charge) }}</span>
                  </div>
                </td>
                <td>
                  <span class="ma-badge ma-badge--secondary">{{ deposit.method_name || 'N/A' }}</span>
                </td>
                <td>
                  <span class="ma-badge" :class="{
                    'ma-badge--success': deposit.status_class === 'success',
                    'ma-badge--warning': deposit.status_class === 'warning',
                    'ma-badge--danger': deposit.status_class === 'danger',
                    'ma-badge--secondary': deposit.status_class === 'secondary'
                  }">
                    <i :class="{
                      'fas fa-check-circle': deposit.status_class === 'success',
                      'fas fa-clock': deposit.status_class === 'warning',
                      'fas fa-times-circle': deposit.status_class === 'danger',
                      'fas fa-circle': deposit.status_class === 'secondary'
                    }"></i>
                    {{ deposit.status_text }}
                  </span>
                </td>
                <td>
                  <div class="ma-date-cell">
                    <i class="fas fa-clock"></i>
                    <span class="ma-table__date">{{ formatDateTime(deposit.created_at) }}</span>
                  </div>
                </td>
                <td>
                  <div class="ma-action-buttons">
                    <button 
                      class="ma-action-btn ma-action-btn--approve" 
                      @click="approveDeposit(deposit)"
                      :disabled="!isPendingDeposit(deposit)"
                      title="Approve Deposit"
                    >
                      <i class="fas fa-check"></i>
                      <span class="ma-action-tooltip">Approve</span>
                    </button>
                    <button 
                      class="ma-action-btn ma-action-btn--reject" 
                      @click="rejectDeposit(deposit)"
                      :disabled="!isPendingDeposit(deposit)"
                      title="Reject Deposit"
                    >
                      <i class="fas fa-times"></i>
                      <span class="ma-action-tooltip">Reject</span>
                    </button>
                    <button 
                      class="ma-action-btn ma-action-btn--view" 
                      @click="viewDeposit(deposit)"
                      title="View Details"
                    >
                      <i class="fas fa-eye"></i>
                      <span class="ma-action-tooltip">View</span>
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
          <span class="ma-page-info">Page {{ currentPage }} of {{ lastPage }}</span>
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
    const filterStatus = ref('')
    const startDate = ref('')
    const endDate = ref('')
    const totalDeposits = ref(0)
    const currentPage = ref(1)
    const lastPage = ref(1)
    const summary = ref({
      total: 0,
      successful: 0,
      pending: 0,
      rejected: 0,
      initiated: 0
    })
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

    const isPendingDeposit = (d) => {
      if (!d) return false
      if (Number(d.status) === 2) return true // Status::PAYMENT_PENDING
      const t = String(d.status_text || '').toLowerCase()
      if (t.includes('pending')) return true
      return String(d.status_class || '').toLowerCase() === 'warning'
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
        const isGatewayOrders = filterStatus.value === 'gateway_orders'
        if (filterStatus.value && !isGatewayOrders) params.status = filterStatus.value
        if (startDate.value) params.start_date = startDate.value
        if (endDate.value) params.end_date = endDate.value

        const endpoint = isGatewayOrders ? '/admin/gateway-orders' : '/admin/deposits'
        const res = await api.get(endpoint, { params })
        if (res.data?.status === 'success' && res.data.data) {
          deposits.value = res.data.data.deposits || []
          totalDeposits.value = res.data.data.total || 0
          currentPage.value = res.data.data.current_page || 1
          lastPage.value = res.data.data.last_page || 1
          if (res.data.data.summary) {
            summary.value = res.data.data.summary
          }
        }
      } catch (e) {
        console.error('Error fetching deposits:', e)
        if (window.notify) window.notify('error', 'Failed to load deposits')
      } finally {
        loading.value = false
      }
    }

    const debounceSearch = () => {
      clearTimeout(searchTimeout)
      searchTimeout = setTimeout(() => fetchDeposits(1), 400)
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

    const approveDeposit = async (deposit) => {
      if (!isPendingDeposit(deposit)) return
      if (!confirm(`Are you sure you want to approve deposit #${deposit.id} of ₹${formatAmount(deposit.amount)}?`)) return

      try {
        const res = await api.post(`/admin/deposit/approve/${deposit.id}`)
        if (res.data?.status === 'success') {
          if (window.notify) window.notify('success', 'Deposit approved successfully')
          fetchDeposits()
        } else {
          if (window.notify) window.notify('error', res.data?.message || 'Failed to approve deposit')
        }
      } catch (e) {
        console.error('Error approving deposit:', e)
        if (window.notify) window.notify('error', 'Failed to approve deposit')
      }
    }

    const rejectDeposit = async (deposit) => {
      if (!isPendingDeposit(deposit)) return
      const reason = prompt(`Enter rejection reason for deposit #${deposit.id}:`)
      if (!reason) return

      try {
        const res = await api.post('/admin/deposit/reject', {
          id: deposit.id,
          message: reason
        })
        if (res.data?.status === 'success') {
          if (window.notify) window.notify('success', 'Deposit rejected successfully')
          fetchDeposits()
        } else {
          if (window.notify) window.notify('error', res.data?.message || 'Failed to reject deposit')
        }
      } catch (e) {
        console.error('Error rejecting deposit:', e)
        if (window.notify) window.notify('error', 'Failed to reject deposit')
      }
    }

    const viewDeposit = (deposit) => {
      alert(`Deposit Details:\n\nID: ${deposit.id}\nTransaction ID: ${deposit.trx}\nAmount: ₹${formatAmount(deposit.amount)}\nCharge: ₹${formatAmount(deposit.charge)}\nAfter Charge: ₹${formatAmount(deposit.after_charge)}\nMethod: ${deposit.method_name}\nStatus: ${deposit.status_text}\nDate: ${formatDateTime(deposit.created_at)}`)
    }

    onMounted(() => {
      fetchDeposits(1)
    })

    return {
      deposits, loading, searchQuery, filterStatus,
      startDate, endDate,
      totalDeposits, currentPage, lastPage, paginationPages, summary,
      formatAmount, formatDateTime, getInitials, isPendingDeposit,
      fetchDeposits, debounceSearch, copyTrx,
      approveDeposit, rejectDeposit, viewDeposit
    }
  }
}
</script>

<style scoped>
.ma-deposits { animation: maFade 0.4s ease-out; }
@keyframes maFade { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }

/* ═══ Mini Stats ═══ */
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

/* ═══ Card ═══ */
.ma-card {
  background: rgba(30, 41, 59, 0.8); border: 1px solid rgba(255,255,255,0.08);
  border-radius: 18px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
}
.ma-card__body { padding: 1rem 1.25rem; }
.ma-card__header {
  padding: 1.25rem 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.06);
  display: flex; align-items: center; justify-content: space-between;
  background: rgba(15, 23, 42, 0.4);
}
.ma-card__header--gradient {
  background: linear-gradient(135deg, rgba(99,102,241,0.1) 0%, rgba(139,92,246,0.1) 100%);
  border-bottom: 1px solid rgba(99,102,241,0.2);
}
.ma-card__title { margin: 0 0 0.25rem 0; color: #f1f5f9; font-weight: 700; font-size: 1.15rem; }
.ma-card__title i { color: #818cf8; }
.ma-card__subtitle {
  margin: 0; color: #94a3b8; font-size: 0.8rem; font-weight: 500;
}
.ma-card__count {
  color: #64748b; font-size: 0.85rem; font-weight: 600;
  background: rgba(99,102,241,0.15); padding: 0.4rem 0.8rem;
  border-radius: 8px; border: 1px solid rgba(99,102,241,0.2);
}
.ma-table-card { box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05); }

/* ═══ Search ═══ */
.ma-search-box {
  display: flex; align-items: center; gap: 0.5rem;
  background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255,255,255,0.1);
  border-radius: 10px; padding: 0 1rem;
}
.ma-search-box i { color: #64748b; font-size: 0.9rem; }
.ma-search-input {
  flex: 1; background: none; border: none; outline: none;
  color: #f1f5f9; padding: 0.6rem 0; font-size: 0.9rem;
}
.ma-search-input::placeholder { color: #475569; }
.ma-select {
  background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255,255,255,0.1);
  border-radius: 10px; padding: 0.6rem 1rem; color: #f1f5f9;
  font-size: 0.88rem; outline: none; cursor: pointer;
}
.ma-select option { background: #1e293b; color: #f1f5f9; }
.ma-btn-refresh {
  width: 40px; height: 40px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.1);
  background: rgba(15, 23, 42, 0.6); color: #94a3b8; cursor: pointer;
  display: flex; align-items: center; justify-content: center; transition: all 0.2s;
}
.ma-btn-refresh:hover:not(:disabled) { color: #f1f5f9; background: rgba(99,102,241,0.2); }
.ma-btn-refresh:disabled { opacity: 0.5; cursor: not-allowed; }

/* ═══ Table ═══ */
.ma-table-wrapper {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}
.ma-table { width: 100%; border-collapse: collapse; font-size: 0.88rem; min-width: 1300px; }
.ma-table th {
  padding: 1rem 1.25rem; color: #94a3b8; font-weight: 700;
  text-transform: uppercase; font-size: 0.7rem; letter-spacing: 0.08em;
  border-bottom: 2px solid rgba(99,102,241,0.2); white-space: nowrap; text-align: left;
  background: rgba(15, 23, 42, 0.3);
}
.ma-table th i { color: #818cf8; font-size: 0.75rem; }
.ma-table td {
  padding: 1rem 1.25rem; color: #cbd5e1;
  border-bottom: 1px solid rgba(255,255,255,0.05); vertical-align: middle;
}
.ma-table-row { transition: all 0.2s ease; }
.ma-table-row:hover {
  background: rgba(99,102,241,0.05) !important;
  transform: scale(1.001);
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

/* ═══ ID Badge ═══ */
.ma-id-badge {
  display: inline-flex; align-items: center; gap: 0.5rem;
  background: rgba(99,102,241,0.15); padding: 0.4rem 0.75rem;
  border-radius: 8px; border: 1px solid rgba(99,102,241,0.2);
  color: #a5b4fc; font-weight: 700; font-size: 0.85rem;
}
.ma-id-badge i { font-size: 0.9rem; }

/* ═══ Transaction Code ═══ */
.ma-trx-cell {
  display: flex; align-items: center; gap: 0.5rem;
}
.ma-trx-code {
  background: rgba(15, 23, 42, 0.6); padding: 0.3rem 0.6rem;
  border-radius: 6px; border: 1px solid rgba(255,255,255,0.1);
  color: #f1f5f9; font-family: 'Courier New', monospace; font-size: 0.8rem;
  font-weight: 600;
}
.ma-btn-copy-small {
  width: 24px; height: 24px; border-radius: 6px; border: 1px solid rgba(255,255,255,0.1);
  background: rgba(59, 130, 246, 0.15); color: #60a5fa; cursor: pointer;
  display: flex; align-items: center; justify-content: center; font-size: 0.7rem;
  transition: all 0.2s; flex-shrink: 0;
}
.ma-btn-copy-small:hover {
  background: rgba(59, 130, 246, 0.25); color: #93c5fd;
  transform: translateY(-1px);
}

/* ═══ User Cell ═══ */
.ma-user-cell { display: flex; align-items: center; gap: 0.75rem; }
.ma-user-avatar {
  width: 36px; height: 36px; border-radius: 10px;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  color: #fff; font-weight: 700; font-size: 0.8rem;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.ma-user-name { display: block; color: #f1f5f9; font-weight: 600; font-size: 0.9rem; }
.ma-user-username { display: block; color: #64748b; font-size: 0.78rem; }

/* ═══ Amount Cell ═══ */
.ma-amount-cell {
  display: flex; align-items: center; gap: 0.5rem; font-weight: 700; font-size: 0.95rem;
}
.ma-amount-credit { color: #34d399 !important; }
.ma-amount-debit { color: #f87171 !important; }
.ma-amount-cell i { font-size: 0.85rem; }

/* ═══ Charge ═══ */
.ma-table__charge {
  color: #fbbf24 !important; font-weight: 600; font-size: 0.9rem;
}

/* ═══ Balance Cell ═══ */
.ma-balance-cell {
  display: flex; align-items: center; gap: 0.5rem;
}
.ma-balance-cell i {
  color: #60a5fa; font-size: 0.85rem;
}
.ma-table__balance {
  color: #60a5fa !important; font-weight: 700; font-size: 0.95rem;
  background: rgba(59, 130, 246, 0.1); padding: 0.3rem 0.6rem;
  border-radius: 6px; border: 1px solid rgba(59, 130, 246, 0.2);
}

/* ═══ Date Cell ═══ */
.ma-date-cell {
  display: flex; align-items: center; gap: 0.5rem;
}
.ma-date-cell i {
  color: #64748b; font-size: 0.85rem;
}
.ma-table__date {
  color: #64748b; font-size: 0.82rem; white-space: nowrap;
}

/* ═══ Badges ═══ */
.ma-badge {
  display: inline-block; padding: 0.2rem 0.6rem; border-radius: 6px;
  font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em;
  display: inline-flex; align-items: center; gap: 0.3rem;
}
.ma-badge i { font-size: 0.65rem; }
.ma-badge--success { background: rgba(16,185,129,0.15); color: #34d399; }
.ma-badge--warning { background: rgba(245,158,11,0.15); color: #fbbf24; }
.ma-badge--danger { background: rgba(239,68,68,0.15); color: #f87171; }
.ma-badge--secondary { background: rgba(100,116,139,0.15); color: #94a3b8; }

/* ═══ Action Buttons ═══ */
.ma-action-buttons {
  display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;
  position: relative;
}
.ma-action-btn {
  width: 36px; height: 36px; border-radius: 10px; border: none;
  background: rgba(15, 23, 42, 0.6); color: #94a3b8; cursor: pointer;
  display: flex; align-items: center; justify-content: center; font-size: 0.85rem;
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); flex-shrink: 0;
  position: relative; border: 1px solid rgba(255,255,255,0.08);
}
.ma-action-btn:hover {
  transform: translateY(-2px) scale(1.05);
  box-shadow: 0 6px 16px rgba(0,0,0,0.2);
}
.ma-action-btn:active { transform: translateY(0) scale(0.98); }
.ma-action-btn:disabled{
  opacity: 0.35;
  cursor: not-allowed;
  transform: none !important;
  box-shadow: none !important;
  filter: grayscale(0.4);
}
.ma-action-btn:disabled .ma-action-tooltip{ display: none; }
.ma-action-tooltip {
  position: absolute; bottom: -32px; left: 50%; transform: translateX(-50%);
  background: rgba(15, 23, 42, 0.95); color: #f1f5f9; padding: 0.3rem 0.6rem;
  border-radius: 6px; font-size: 0.7rem; white-space: nowrap;
  opacity: 0; pointer-events: none; transition: opacity 0.2s;
  border: 1px solid rgba(255,255,255,0.1); z-index: 10;
}
.ma-action-btn:hover .ma-action-tooltip {
  opacity: 1;
}
.ma-action-btn--view {
  background: rgba(59, 130, 246, 0.15); color: #60a5fa;
  border-color: rgba(59, 130, 246, 0.2);
}
.ma-action-btn--view:hover {
  background: rgba(59, 130, 246, 0.25); color: #93c5fd;
  border-color: rgba(59, 130, 246, 0.3);
  box-shadow: 0 6px 16px rgba(59, 130, 246, 0.3);
}
.ma-action-btn--approve {
  background: rgba(16, 185, 129, 0.15); color: #34d399;
  border-color: rgba(16, 185, 129, 0.2);
}
.ma-action-btn--approve:hover {
  background: rgba(16, 185, 129, 0.25); color: #6ee7b7;
  border-color: rgba(16, 185, 129, 0.3);
  box-shadow: 0 6px 16px rgba(16, 185, 129, 0.3);
}
.ma-action-btn--reject {
  background: rgba(239, 68, 68, 0.15); color: #f87171;
  border-color: rgba(239, 68, 68, 0.2);
}
.ma-action-btn--reject:hover {
  background: rgba(239, 68, 68, 0.25); color: #fca5a5;
  border-color: rgba(239, 68, 68, 0.3);
  box-shadow: 0 6px 16px rgba(239, 68, 68, 0.3);
}

/* ═══ Loading & Empty ═══ */
.ma-table__loading, .ma-table__empty {
  text-align: center; padding: 3rem 1rem !important; color: #64748b;
}
.ma-table__empty i { font-size: 2.5rem; margin-bottom: 0.75rem; display: block; color: #475569; }
.ma-table__empty p { margin: 0; }
.ma-table__loading { display: flex; align-items: center; justify-content: center; gap: 0.75rem; }

/* ═══ Pagination ═══ */
.ma-pagination {
  display: flex; align-items: center; justify-content: center; gap: 0.4rem;
  padding: 1rem 1.25rem; border-top: 1px solid rgba(255,255,255,0.06); flex-wrap: wrap;
}
.ma-page-btn {
  width: 36px; height: 36px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.08);
  background: transparent; color: #94a3b8; cursor: pointer; font-size: 0.85rem; font-weight: 600;
  display: flex; align-items: center; justify-content: center; transition: all 0.2s;
}
.ma-page-btn:hover:not(:disabled):not(.ma-page-dots) { background: rgba(99,102,241,0.15); color: #a5b4fc; border-color: rgba(99,102,241,0.3); }
.ma-page-btn--active { background: #6366f1 !important; color: #fff !important; border-color: #6366f1 !important; }
.ma-page-btn:disabled { opacity: 0.4; cursor: not-allowed; }
.ma-page-dots { border: none; }
.ma-page-info { color: #475569; font-size: 0.8rem; margin-left: 0.75rem; }

/* ═══ Spinner ═══ */
.ma-spinner {
  width: 24px; height: 24px; border: 3px solid rgba(255,255,255,0.1);
  border-top-color: #818cf8; border-radius: 50%; animation: maSpin 0.7s linear infinite;
}
@keyframes maSpin { to { transform: rotate(360deg); } }

@media (max-width: 768px) {
  .ma-card__header { flex-direction: column; align-items: flex-start; gap: 0.75rem; }
  .ma-card__subtitle { display: none; }
  .ma-table { font-size: 0.75rem; min-width: 1100px; }
  .ma-table th, .ma-table td { padding: 0.6rem 0.4rem; }
  .ma-table th { font-size: 0.65rem; }
  .ma-user-avatar { width: 28px; height: 28px; font-size: 0.7rem; }
  .ma-user-name { font-size: 0.8rem; }
  .ma-user-username { font-size: 0.68rem; }
  .ma-table__balance { font-size: 0.75rem; padding: 0.2rem 0.4rem; }
  .ma-badge { font-size: 0.65rem; padding: 0.2rem 0.4rem; }
  .ma-action-buttons { gap: 0.3rem; }
  .ma-action-btn { width: 30px; height: 30px; font-size: 0.7rem; }
}
</style>
