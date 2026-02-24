<template>
  <MasterAdminLayout page-title="All Orders">
    <div class="ma-deposits">
      <!-- Stats Row -->
      <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
          <div class="ma-stat-mini" @click="filterStatus = ''">
            <div class="ma-stat-mini__icon ma-stat-mini__icon--blue"><i class="fas fa-receipt"></i></div>
            <div><span class="ma-stat-mini__val">{{ summary.total || 0 }}</span><span class="ma-stat-mini__lbl">Total Orders</span></div>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="ma-stat-mini" @click="filterStatus = 'approved'">
            <div class="ma-stat-mini__icon ma-stat-mini__icon--green"><i class="fas fa-check-circle"></i></div>
            <div><span class="ma-stat-mini__val">₹{{ formatAmount(summary.successful || 0) }}</span><span class="ma-stat-mini__lbl">Approved</span></div>
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
          <select v-model="filterStatus" @change="fetchOrders(1)" class="ma-select">
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
            <option value="initiated">Initiated</option>
          </select>
          <select v-model="filterType" @change="fetchOrders(1)" class="ma-select">
            <option value="">All Type</option>
            <option value="ad_plan_purchase">Ad Plan</option>
            <option value="ad_certificate_fee">Certificate</option>
            <option value="kyc_fee">KYC</option>
            <option value="package_upgrade_gateway">Package</option>
            <option value="partner_program_gateway">Partner Program</option>
            <option value="registration_fee">Registration</option>
          </select>
          <div class="d-flex gap-2 align-items-center">
            <input type="date" v-model="startDate" class="ma-date">
            <span class="ma-date-sep">to</span>
            <input type="date" v-model="endDate" class="ma-date">
          </div>
          <button class="ma-btn-refresh" @click="fetchOrders()">
            <i class="fas fa-sync-alt"></i>
          </button>
        </div>
      </div>

      <!-- Orders Table (Gateway Deposits + Gateway Payments) -->
      <div class="ma-card ma-table-card">
        <div class="ma-card__header ma-card__header--gradient">
          <div>
            <h5 class="ma-card__title"><i class="fas fa-file-invoice-dollar me-2"></i>Unified Orders History</h5>
            <p class="ma-card__subtitle">Manage all gateway deposits and withdrawals in one place</p>
          </div>
          <span class="ma-card__count">{{ totalOrders }} orders</span>
        </div>
        <!-- Mobile View (Card List) -->
        <div class="tw-block md:tw-hidden">
          <div v-if="loading" class="tw-py-20 tw-text-center">
            <div class="ma-spinner tw-mx-auto"></div>
          </div>
          <div v-else-if="orders.length === 0" class="tw-py-24 tw-text-center tw-text-white/40">
            <i class="fas fa-receipt fa-3x tw-mb-3"></i>
            <p>No orders found</p>
          </div>
          <div v-else class="tw-p-4 tw-space-y-4">
            <div v-for="o in orders" :key="o.id" class="tw-bg-white/5 tw-border tw-border-white/10 tw-rounded-2xl tw-p-5">
              <div class="tw-flex tw-justify-between tw-items-start tw-mb-4">
                <div class="ma-user-cell">
                  <div class="ma-user-avatar">{{ getInitials(o.user) }}</div>
                  <div>
                    <span class="ma-user-name">{{ o.user?.firstname }} {{ o.user?.lastname }}</span>
                    <span class="ma-user-username">@{{ o.user?.username }}</span>
                  </div>
                </div>
                <span class="ma-badge" :class="{
                  'ma-badge--success': o.status_class === 'success' && !o.is_manual,
                  'ma-badge--danger': o.status_class === 'success' && o.is_manual,
                  'ma-badge--warning': o.status_class === 'warning',
                  'ma-badge--red': o.status_class === 'danger',
                  'ma-badge--secondary': o.status_class === 'secondary'
                }">
                  {{ o.is_manual ? 'Manual' : o.status_text }}
                </span>
              </div>

              <div class="tw-grid tw-grid-cols-2 tw-gap-4 tw-mb-4 tw-border-y tw-border-white/5 tw-py-4">
                <div>
                  <div class="tw-text-slate-500 tw-text-[9px] tw-font-bold tw-uppercase tw-tracking-tight tw-mb-1">Amount</div>
                  <div class="tw-text-emerald-400 tw-text-xs tw-font-bold">₹{{ formatAmount(o.amount) }}</div>
                  <div class="tw-text-white/30 tw-text-[9px]">A: ₹{{ formatAmount(o.after_charge) }}</div>
                </div>
                <div>
                  <div class="tw-text-slate-500 tw-text-[9px] tw-font-bold tw-uppercase tw-tracking-tight tw-mb-1">Type/Trx</div>
                  <div class="tw-text-indigo-400 tw-text-[10px] tw-font-bold tw-truncate">{{ o.order_type || 'Payment' }}</div>
                  <code class="tw-text-white/30 tw-text-[9px] tw-font-mono">{{ o.trx }}</code>
                </div>
              </div>

              <div class="tw-flex tw-justify-between tw-items-center">
                <div class="tw-text-white/30 tw-text-[10px]">
                  {{ formatDateTime(o.created_at) }}
                </div>
                <div class="ma-action-buttons">
                  <button class="ma-action-btn ma-action-btn--view" @click="viewOrder(o)">
                    <i class="fas fa-eye"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="table-responsive ma-table-wrapper tw-hidden md:tw-block">
          <table class="ma-table">
            <thead>
              <tr>
                <th><i class="fas fa-hashtag me-1"></i>ID</th>
                <th><i class="fas fa-barcode me-1"></i>Transaction ID</th>
                <th><i class="fas fa-user me-1"></i>User</th>
                <th><i class="fas fa-rupee-sign me-1"></i>Amount</th>
                <th><i class="fas fa-percentage me-1"></i>Charge</th>
                <th><i class="fas fa-wallet me-1"></i>After Charge</th>
                <th><i class="fas fa-credit-card me-1"></i>Gateway</th>
                <th><i class="fas fa-layer-group me-1"></i>Type</th>
                <th><i class="fas fa-info-circle me-1"></i>Status</th>
                <th><i class="fas fa-calendar me-1"></i>Date</th>
                <th><i class="fas fa-cog me-1"></i>Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading">
                <td colspan="11" class="ma-table__loading"><div class="ma-spinner"></div></td>
              </tr>
              <tr v-else-if="orders.length === 0">
                <td colspan="11" class="ma-table__empty">
                  <i class="fas fa-receipt"></i>
                  <p>No orders found</p>
                </td>
              </tr>
              <tr v-else v-for="o in orders" :key="o.id" class="ma-table-row">
                <td>
                  <div class="ma-id-badge">
                    <i class="fas fa-hashtag"></i>
                    <span>{{ o.id }}</span>
                  </div>
                </td>
                <td>
                  <div class="ma-trx-cell">
                    <code class="ma-trx-code">{{ o.trx }}</code>
                    <button class="ma-btn-copy-small" @click="copyTrx(o.trx)" title="Copy">
                      <i class="fas fa-copy"></i>
                    </button>
                  </div>
                </td>
                <td>
                  <div v-if="o.user" class="ma-user-cell">
                    <div class="ma-user-avatar">{{ getInitials(o.user) }}</div>
                    <div>
                      <span class="ma-user-name">{{ o.user.firstname }} {{ o.user.lastname }}</span>
                      <span class="ma-user-username">@{{ o.user.username }}</span>
                    </div>
                  </div>
                  <span v-else class="text-muted">N/A</span>
                </td>
                <td>
                  <div class="ma-amount-cell ma-amount-credit">
                    <i class="fas fa-arrow-down"></i>
                    <span>₹{{ formatAmount(o.amount) }}</span>
                  </div>
                </td>
                <td><span class="ma-table__charge">₹{{ formatAmount(o.charge) }}</span></td>
                <td>
                  <div class="ma-balance-cell">
                    <i class="fas fa-wallet"></i>
                    <span class="ma-table__balance">₹{{ formatAmount(o.after_charge) }}</span>
                  </div>
                </td>
                <td>
                  <span class="ma-badge ma-badge--secondary">{{ o.method_name || 'Gateway' }}</span>
                </td>
                <td>
                  <span class="ma-badge" :class="o.source === 'deposit' ? 'ma-badge--warning' : 'ma-badge--success'">
                    <i :class="o.source === 'deposit' ? 'fas fa-wallet' : 'fas fa-bolt'"></i>
                    {{ o.order_type || (o.source === 'deposit' ? 'Deposit' : 'Payment') }}
                  </span>
                </td>
                <td>
                  <span class="ma-badge" :class="{
                    'ma-badge--success': o.status_class === 'success' && !o.is_manual,
                    'ma-badge--danger': o.status_class === 'success' && o.is_manual,
                    'ma-badge--warning': o.status_class === 'warning',
                    'ma-badge--red': o.status_class === 'danger',
                    'ma-badge--secondary': o.status_class === 'secondary'
                  }">
                    <i :class="{
                      'fas fa-check-circle': o.status_class === 'success',
                      'fas fa-clock': o.status_class === 'warning',
                      'fas fa-times-circle': o.status_class === 'danger',
                      'fas fa-circle': o.status_class === 'secondary'
                    }"></i>
                    {{ o.is_manual ? 'Manual Approved' : o.status_text }}
                  </span>
                </td>
                <td>
                  <div class="ma-date-cell">
                    <i class="fas fa-clock"></i>
                    <span class="ma-table__date">{{ formatDateTime(o.created_at) }}</span>
                  </div>
                </td>
                <td>
                  <div class="ma-action-buttons">
                    <button
                      type="button"
                      class="ma-action-btn ma-action-btn--view"
                      @click="viewOrder(o)"
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

        <div v-if="lastPage > 1" class="ma-pagination">
          <button class="ma-page-btn" :disabled="currentPage === 1" @click="fetchOrders(currentPage - 1)">
            <i class="fas fa-chevron-left"></i>
          </button>
          <template v-for="p in paginationPages" :key="p">
            <button v-if="p === '...'" class="ma-page-btn ma-page-dots" disabled>...</button>
            <button v-else class="ma-page-btn" :class="{ 'ma-page-btn--active': p === currentPage }" @click="fetchOrders(p)">{{ p }}</button>
          </template>
          <button class="ma-page-btn" :disabled="currentPage === lastPage" @click="fetchOrders(currentPage + 1)">
            <i class="fas fa-chevron-right"></i>
          </button>
          <span class="ma-page-info">Page {{ currentPage }} of {{ lastPage }}</span>
        </div>
      </div>
    </div>

    <!-- View Details Modal -->
    <div v-if="selectedOrder" class="ma-modal-overlay" @click.self="selectedOrder = null">
      <div class="ma-modal">
        <div class="ma-modal__header">
          <div class="d-flex align-items-center gap-2">
             <i class="fas fa-file-invoice-dollar text-primary"></i>
             <h5 class="m-0 text-white">{{ selectedOrder.source === 'withdrawal' ? 'Withdrawal' : 'Order' }} #{{ selectedOrder.id }}</h5>
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
                    <div class="ma-modal-info__row"><span><i class="fas fa-check-double me-2"></i>Status:</span><span :class="'badge-solid badge-solid--' + selectedOrder.status_class">{{ selectedOrder.status_text }}</span></div>
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
        <div class="ma-modal__footer d-flex justify-content-between align-items-center gap-2 p-3 border-top border-white-5">
           <div>
             <button class="ma-btn ma-btn--delete" @click="deleteOrder(selectedOrder); selectedOrder = null">
               <i class="fas fa-trash-alt me-1"></i>Delete
             </button>
           </div>
           <div class="d-flex gap-2">
             <button class="ma-btn ma-btn--secondary" @click="selectedOrder = null">Close</button>
             <button v-if="isPending(selectedOrder)" class="ma-btn ma-btn--reject" @click="rejectOrder(selectedOrder); selectedOrder = null">
               <i class="fas fa-times me-1"></i>Reject
             </button>
             <button v-if="isPending(selectedOrder)" class="ma-btn ma-btn--approve" @click="approveOrder(selectedOrder); selectedOrder = null">
               <i class="fas fa-check me-1"></i>Approve
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
  name: 'MasterAdminOrders',
  components: { MasterAdminLayout },
  setup() {
    const orders = ref([])
    const loading = ref(false)
    const searchQuery = ref('')
    const filterStatus = ref('')
    const filterType = ref('')
    const startDate = ref('')
    const endDate = ref('')
    const totalOrders = ref(0)
    const currentPage = ref(1)
    const lastPage = ref(1)
    const selectedOrder = ref(null)
    const summary = ref({ total: 0, successful: 0, pending: 0, rejected: 0, initiated: 0 })
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

    const isPending = (d) => {
      if (!d) return false
      return d.approvable === true || d.approvable === 1 || Number(d.status) === 0 || Number(d.status) === 2
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

    const fetchOrders = async (page) => {
      if (page) currentPage.value = page
      loading.value = true
      try {
        const params = { page: currentPage.value, per_page: 20 }
        if (searchQuery.value) params.search = searchQuery.value
        if (filterStatus.value) params.status = filterStatus.value
        if (filterType.value) params.type = filterType.value
        if (startDate.value) params.start_date = startDate.value
        if (endDate.value) params.end_date = endDate.value

        const res = await api.get('/admin/all-gateway-orders', { params })
        if (res.data?.status === 'success' && res.data.data) {
          orders.value = res.data.data.orders || []
          totalOrders.value = res.data.data.total || 0
          currentPage.value = res.data.data.current_page || 1
          lastPage.value = res.data.data.last_page || 1
          if (res.data.data.summary) summary.value = res.data.data.summary
        }
      } catch (e) {
        console.error('Error fetching orders:', e)
        if (window.notify) window.notify('error', 'Failed to load orders')
      } finally {
        loading.value = false
      }
    }

    const debounceSearch = () => {
      clearTimeout(searchTimeout)
      searchTimeout = setTimeout(() => fetchOrders(1), 400)
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

    const approveOrder = async (o) => {
      if (!isPending(o)) {
        if (window.notify) window.notify('warning', 'Only pending orders can be approved')
        return
      }
      if (!confirm(`Approve order #${o.id} of ₹${formatAmount(o.amount)}?`)) return
      try {
        const id = o.source_id || o.id
        const res = await api.post(`/admin/order/approve/${id}?source=${o.source}`)
        if (res.data?.status === 'success') {
          if (window.notify) window.notify('success', res.data?.message || 'Order approved successfully')
          fetchOrders()
        } else {
          if (window.notify) window.notify('error', res.data?.message || 'Failed to approve')
        }
      } catch (e) {
        if (window.notify) window.notify('error', 'Failed to approve')
      }
    }

    const rejectOrder = async (o) => {
      if (!isPending(o)) {
        if (window.notify) window.notify('warning', 'Only pending orders can be rejected')
        return
      }
      const reason = prompt(`Enter rejection reason for order #${o.id}:`)
      if (!reason) return
      try {
        const id = o.source_id || o.id
        const res = await api.post(`/admin/order/reject/${id}?source=${o.source}`, { reason })
        if (res.data?.status === 'success') {
          if (window.notify) window.notify('success', res.data?.message || 'Order rejected successfully')
          fetchOrders()
        } else {
          if (window.notify) window.notify('error', res.data?.message || 'Failed to reject')
        }
      } catch (e) {
        if (window.notify) window.notify('error', 'Failed to reject')
      }
    }

    const deleteOrder = async (o) => {
      if (!confirm(`Are you sure you want to PERMANENTLY delete order #${o.id}? This cannot be undone.`)) return
      try {
        const id = o.source_id || o.id
        const res = await api.post(`/admin/order/delete/${id}?source=${o.source}`)
        if (res.data?.status === 'success') {
          if (window.notify) window.notify('success', res.data?.message || 'Order deleted successfully')
          fetchOrders()
        } else {
          if (window.notify) window.notify('error', res.data?.message || 'Failed to delete')
        }
      } catch (e) {
        if (window.notify) window.notify('error', 'Failed to delete')
      }
    }

    const viewOrder = (o) => {
      selectedOrder.value = o
    }

    onMounted(() => fetchOrders(1))

    return {
      orders, loading, searchQuery, filterStatus, filterType, startDate, endDate,
      totalOrders, currentPage, lastPage, paginationPages, summary, selectedOrder,
      formatAmount, formatDateTime, getInitials, isPending,
      fetchOrders, debounceSearch, copyTrx,
      approveOrder, rejectOrder, deleteOrder, viewOrder
    }
  }
}
</script>

<!-- Reuse same styles as Deposits page by copying classnames -->
<style scoped>
.ma-deposits { animation: maFade 0.4s ease-out; }
@keyframes maFade { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }

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
.ma-card__subtitle { margin: 0; color: #94a3b8; font-size: 0.8rem; font-weight: 500; }
.ma-card__count { color: #64748b; font-size: 0.85rem; font-weight: 600; background: rgba(99,102,241,0.15); padding: 0.4rem 0.8rem; border-radius: 8px; border: 1px solid rgba(99,102,241,0.2); }
.ma-table-card { box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05); }

.ma-search-box { display: flex; align-items: center; gap: 0.5rem; background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 0 1rem; }
.ma-search-box i { color: #64748b; font-size: 0.9rem; }
.ma-search-input { flex: 1; background: none; border: none; outline: none; color: #f1f5f9; padding: 0.6rem 0; font-size: 0.9rem; }
.ma-search-input::placeholder { color: #475569; }
.ma-select { background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 0.6rem 1rem; color: #f1f5f9; font-size: 0.88rem; outline: none; cursor: pointer; }
.ma-select option { background: #1e293b; color: #f1f5f9; }
.ma-btn-refresh { width: 40px; height: 40px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.1); background: rgba(15, 23, 42, 0.6); color: #94a3b8; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; }
.ma-btn-refresh:hover:not(:disabled) { color: #f1f5f9; background: rgba(99,102,241,0.2); }
.ma-btn-refresh:disabled { opacity: 0.5; cursor: not-allowed; }

.ma-table-wrapper { overflow-x: auto; -webkit-overflow-scrolling: touch; }
.ma-table { width: 100%; border-collapse: collapse; font-size: 0.88rem; min-width: 1400px; }
.ma-table th { padding: 1rem 1.25rem; color: #f1f5f9; font-weight: 800; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.08em; border-bottom: 2px solid rgba(99,102,241,0.3); white-space: nowrap; text-align: left; background: rgba(15, 23, 42, 0.5); }
.ma-table th i { color: #818cf8; font-size: 0.75rem; }
.ma-table td { padding: 1rem 1.25rem; color: #cbd5e1; border-bottom: 1px solid rgba(255,255,255,0.05); vertical-align: middle; }
.ma-table-row { transition: all 0.2s ease; }
.ma-table-row:hover { background: rgba(99,102,241,0.05) !important; transform: scale(1.001); box-shadow: 0 2px 8px rgba(0,0,0,0.1); }

.ma-id-badge { display: inline-flex; align-items: center; gap: 0.5rem; background: rgba(99,102,241,0.15); padding: 0.4rem 0.75rem; border-radius: 8px; border: 1px solid rgba(99,102,241,0.2); color: #a5b4fc; font-weight: 700; font-size: 0.85rem; }
.ma-trx-cell { display: flex; align-items: center; gap: 0.5rem; }
.ma-trx-code { background: rgba(15, 23, 42, 0.6); padding: 0.3rem 0.6rem; border-radius: 6px; border: 1px solid rgba(255,255,255,0.1); color: #f1f5f9; font-family: 'Courier New', monospace; font-size: 0.8rem; font-weight: 600; }
.ma-btn-copy-small { width: 24px; height: 24px; border-radius: 6px; border: 1px solid rgba(255,255,255,0.1); background: rgba(59, 130, 246, 0.15); color: #60a5fa; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; transition: all 0.2s; flex-shrink: 0; }
.ma-btn-copy-small:hover { background: rgba(59, 130, 246, 0.25); color: #93c5fd; transform: translateY(-1px); }

.ma-user-cell { display: flex; align-items: center; gap: 0.75rem; }
.ma-user-avatar { width: 36px; height: 36px; border-radius: 10px; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; font-weight: 700; font-size: 0.8rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.ma-user-name { display: block; color: #f1f5f9; font-weight: 600; font-size: 0.9rem; }
.ma-user-username { display: block; color: #64748b; font-size: 0.78rem; }

.ma-amount-cell { display: flex; align-items: center; gap: 0.5rem; font-weight: 700; font-size: 0.95rem; }
.ma-amount-credit { color: #34d399 !important; }
.ma-table__charge { color: #fbbf24 !important; font-weight: 600; font-size: 0.9rem; }
.ma-balance-cell { display: flex; align-items: center; gap: 0.5rem; }
.ma-balance-cell i { color: #60a5fa; font-size: 0.85rem; }
.ma-table__balance { color: #60a5fa !important; font-weight: 700; font-size: 0.95rem; background: rgba(59, 130, 246, 0.1); padding: 0.3rem 0.6rem; border-radius: 6px; border: 1px solid rgba(59, 130, 246, 0.2); }

.ma-date-cell { display: flex; align-items: center; gap: 0.5rem; }
.ma-date-cell i { color: #64748b; font-size: 0.85rem; }
.ma-table__date { color: #64748b; font-size: 0.82rem; white-space: nowrap; }

.ma-badge { display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.2rem 0.6rem; border-radius: 6px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em; }
.ma-badge i { font-size: 0.65rem; }
.ma-badge--success { background: rgba(16,185,129,0.15); color: #34d399; }
.ma-badge--warning { background: rgba(245,158,11,0.15); color: #fbbf24; }
.ma-badge--danger { background: rgba(239,68,68,0.15); color: #f87171; }
.ma-badge--secondary { background: rgba(100,116,139,0.15); color: #94a3b8; }

.ma-action-buttons { display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; position: relative; }
.ma-action-btn { width: 36px; height: 36px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.08); background: rgba(15, 23, 42, 0.6); color: #94a3b8; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); position: relative; }
.ma-action-btn:hover { transform: translateY(-2px) scale(1.05); box-shadow: 0 6px 16px rgba(0,0,0,0.2); }
.ma-action-btn:disabled{ opacity: 0.35; cursor: not-allowed; transform: none !important; box-shadow: none !important; }
.ma-action-tooltip { position: absolute; bottom: -32px; left: 50%; transform: translateX(-50%); background: rgba(15, 23, 42, 0.95); color: #f1f5f9; padding: 0.3rem 0.6rem; border-radius: 6px; font-size: 0.7rem; white-space: nowrap; opacity: 0; pointer-events: none; transition: opacity 0.2s; border: 1px solid rgba(255,255,255,0.1); z-index: 10; }
.ma-action-btn:hover .ma-action-tooltip { opacity: 1; }
.ma-action-btn--view { background: rgba(59, 130, 246, 0.15); color: #60a5fa; border-color: rgba(59, 130, 246, 0.2); }
.ma-action-btn--delete { background: rgba(239, 68, 68, 0.15); color: #f87171; border-color: rgba(239, 68, 68, 0.2); }

.ma-action-pill{
  height: 36px;
  padding: 0 12px;
  border-radius: 10px;
  border: 1px solid rgba(255,255,255,0.08);
  background: rgba(15, 23, 42, 0.6);
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-weight: 800;
  font-size: 0.82rem;
  letter-spacing: 0.02em;
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}
.ma-action-pill:hover{ transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,0.2); }
.ma-action-pill:disabled{ opacity: 0.5; cursor: not-allowed; transform: none !important; box-shadow: none !important; }
.ma-action-pill--approve{ background: rgba(16, 185, 129, 0.15); color: #34d399; border-color: rgba(16, 185, 129, 0.2); }
.ma-action-pill--reject{ background: rgba(239, 68, 68, 0.15); color: #f87171; border-color: rgba(239, 68, 68, 0.2); }

.ma-table__loading, .ma-table__empty { text-align: center; padding: 3rem 1rem !important; color: #64748b; }
.ma-table__empty i { font-size: 2.5rem; margin-bottom: 0.75rem; display: block; color: #475569; }
.ma-table__loading { display: flex; align-items: center; justify-content: center; gap: 0.75rem; }

.ma-pagination { display: flex; align-items: center; justify-content: center; gap: 0.4rem; padding: 1rem 1.25rem; border-top: 1px solid rgba(255,255,255,0.06); flex-wrap: wrap; }
.ma-page-btn { width: 36px; height: 36px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.08); background: transparent; color: #94a3b8; cursor: pointer; font-size: 0.85rem; font-weight: 600; display: flex; align-items: center; justify-content: center; transition: all 0.2s; }
.ma-page-btn--active { background: #6366f1 !important; color: #fff !important; border-color: #6366f1 !important; }
.ma-page-btn:disabled { opacity: 0.4; cursor: not-allowed; }
.ma-page-dots { border: none; }
.ma-page-info { color: #475569; font-size: 0.8rem; margin-left: 0.75rem; }

.ma-spinner { width: 24px; height: 24px; border: 3px solid rgba(255,255,255,0.1); border-top-color: #818cf8; border-radius: 50%; animation: maSpin 0.7s linear infinite; }
@keyframes maSpin { to { transform: rotate(360deg); } }
/* ═══ Modal ═══ */
.ma-modal-overlay {
  position: fixed; top: 0; left: 0; width: 100%; height: 100%;
  background: rgba(0,0,0,0.85); backdrop-filter: blur(8px);
  display: flex; align-items: center; justify-content: center; z-index: 9999;
  padding: 1.5rem; animation: maFade 0.3s ease-out;
}
.ma-modal {
  background: #1e293b; border: 1px solid rgba(255,255,255,0.08);
  border-radius: 20px; width: 100%; max-width: 650px; overflow: hidden;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
}
.ma-modal__header {
  padding: 1.25rem 1.5rem; background: rgba(15,23,42,0.4);
  display: flex; align-items: center; justify-content: space-between;
  border-bottom: 1px solid rgba(255,255,255,0.05);
}
.ma-modal__close {
  background: none; border: none; color: #64748b; cursor: pointer;
  width: 32px; height: 32px; border-radius: 50%; transition: all 0.2s;
}
.ma-modal__close:hover { background: rgba(255,255,255,0.05); color: #f1f5f9; }
.ma-modal-card {
  background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 14px; padding: 1.25rem; height: 100%;
}
.ma-modal-card__title {
  color: #818cf8; font-size: 0.8rem; font-weight: 800; text-transform: uppercase;
  letter-spacing: 0.05em; margin-bottom: 1.25rem; display: flex; align-items: center;
}
.ma-modal-info { display: flex; flex-direction: column; gap: 0.85rem; }
.ma-modal-info__row { display: flex; align-items: center; justify-content: space-between; font-size: 0.95rem; }
.ma-modal-info__row span:first-child { color: #e2e8f0; font-weight: 700; opacity: 0.9; }
.ma-modal-info__row span:last-child { color: #ffffff; }
.ma-modal-info__line { height: 1px; background: rgba(255,255,255,0.06); margin: 0.5rem 0; border-style: dashed; }
.ma-modal-info--horizontal { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem 2rem; }

.ma-btn {
  height: 40px; padding: 0 1.5rem; border-radius: 10px; font-weight: 700;
  font-size: 0.88rem; transition: all 0.2s; border: none; cursor: pointer;
}
.ma-btn--approve { background: #10b981; color: #fff; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3); }
.ma-btn--approve:hover { background: #059669; transform: translateY(-1px); }
.ma-btn--reject { background: #ef4444; color: #fff; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3); }
.ma-btn--reject:hover { background: #dc2626; transform: translateY(-1px); }
.ma-btn--delete { background: rgba(220,38,38,0.12); color: #f87171; border: 1px solid rgba(239,68,68,0.3); }
.ma-btn--delete:hover { background: rgba(220,38,38,0.25); transform: translateY(-1px); }
.ma-btn--secondary { background: rgba(255,255,255,0.06); color: #cbd5e1; border: 1px solid rgba(255,255,255,0.1); }
.ma-btn--secondary:hover { background: rgba(255,255,255,0.1); transform: translateY(-1px); }

.border-white-5 { border-color: rgba(255,255,255,0.1) !important; }
.text-white-50 { color: rgba(255,255,255,0.7) !important; }
.text-light { color: #f8fafc !important; }
.text-faded { color: #e2e8f0 !important; }
.text-amber-bright { color: #fbbf24 !important; }
.text-success-bright { color: #10b981 !important; }
.text-danger-bright { color: #f87171 !important; }
.fw-extrabold { font-weight: 900; }

.badge-solid {
  padding: 0.35rem 0.75rem;
  border-radius: 6px;
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  display: inline-block;
}
.badge-solid--primary { background: rgba(96, 165, 250, 0.15); color: #60a5fa; border: 1px solid rgba(96, 165, 250, 0.3); }
.badge-solid--success { background: rgba(52, 211, 153, 0.15); color: #34d399; border: 1px solid rgba(52, 211, 153, 0.3); }
.badge-solid--danger { background: rgba(248, 113, 113, 0.15); color: #f87171; border: 1px solid rgba(248, 113, 113, 0.3); }
.badge-solid--warning { background: rgba(251, 191, 36, 0.15); color: #fbbf24; border: 1px solid rgba(251, 191, 36, 0.3); }
.badge-solid--info { background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3); }
.badge-solid--dark { background: rgba(148, 163, 184, 0.15); color: #94a3b8; border: 1px solid rgba(148, 163, 184, 0.3); }

@media (max-width: 576px) {
  .ma-modal-info--horizontal { grid-template-columns: 1fr; }
}
</style>

