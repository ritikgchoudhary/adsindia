<template>
  <MasterAdminLayout page-title="Transactions">
    <div class="ma-transactions">
      <!-- Search & Filter -->
      <div class="ma-card mb-4">
        <div class="ma-card__body d-flex flex-wrap gap-3 align-items-center">
          <div class="flex-grow-1" style="min-width: 200px;">
            <div class="ma-search-box">
              <i class="fas fa-search"></i>
              <input type="text" v-model="searchQuery" placeholder="Search by transaction ID, username, email..." @input="debounceSearch" class="ma-search-input">
            </div>
          </div>
          <select v-model="filterTrxType" @change="fetchTransactions(1)" class="ma-select">
            <option value="">All Types</option>
            <option value="+">Credit (+)</option>
            <option value="-">Debit (-)</option>
          </select>
          <select v-model="filterRemark" @change="fetchTransactions(1)" class="ma-select">
            <option value="">All Remarks</option>
            <option v-for="remark in remarks" :key="remark" :value="remark">{{ formatRemark(remark) }}</option>
          </select>
          <button class="ma-btn-refresh" @click="fetchTransactions()">
            <i class="fas fa-sync-alt"></i>
          </button>
        </div>
      </div>

      <!-- Transactions Table -->
      <div class="ma-card ma-table-card">
        <div class="ma-card__header ma-card__header--gradient">
          <div>
            <h5 class="ma-card__title"><i class="fas fa-exchange-alt me-2"></i>All Transactions</h5>
            <p class="ma-card__subtitle">View and manage all user transactions</p>
          </div>
          <span class="ma-card__count">{{ totalTransactions }} transactions</span>
        </div>
        <div class="table-responsive ma-table-wrapper">
          <table class="ma-table">
            <thead>
              <tr>
                <th><i class="fas fa-hashtag me-1"></i>ID</th>
                <th><i class="fas fa-barcode me-1"></i>Transaction ID</th>
                <th><i class="fas fa-user me-1"></i>User</th>
                <th><i class="fas fa-exchange-alt me-1"></i>Type</th>
                <th><i class="fas fa-rupee-sign me-1"></i>Amount</th>
                <th><i class="fas fa-wallet me-1"></i>Balance</th>
                <th><i class="fas fa-info-circle me-1"></i>Details</th>
                <th><i class="fas fa-tag me-1"></i>Remark</th>
                <th><i class="fas fa-calendar me-1"></i>Date</th>
              </tr>
            </thead>
            <tbody>

              <tr v-if="loading">
                <td colspan="9" class="ma-table__loading"><div class="ma-spinner"></div></td>
              </tr>
              <tr v-else-if="transactions.length === 0">
                <td colspan="9" class="ma-table__empty">
                  <i class="fas fa-exchange-alt"></i>
                  <p>No transactions found</p>
                </td>
              </tr>
              <tr v-else v-for="trx in transactions" :key="trx.id" class="ma-table-row">
                <td>
                  <div class="ma-id-badge">
                    <i class="fas fa-hashtag"></i>
                    <span>{{ trx.id }}</span>
                  </div>
                </td>
                <td>
                  <div class="ma-trx-cell">
                    <code class="ma-trx-code">{{ trx.trx }}</code>
                    <button class="ma-btn-copy-small" @click="copyTrx(trx.trx)" title="Copy">
                      <i class="fas fa-copy"></i>
                    </button>
                  </div>
                </td>
                <td>
                  <div v-if="trx.user" class="ma-user-cell">
                    <div class="ma-user-avatar">{{ getInitials(trx.user) }}</div>
                    <div>
                      <span class="ma-user-name">{{ trx.user.firstname }} {{ trx.user.lastname }}</span>
                      <span class="ma-user-username">@{{ trx.user.username }}</span>
                    </div>
                  </div>
                  <span v-else class="text-muted">N/A</span>
                </td>
                <td>
                  <span class="ma-badge" :class="trx.trx_type === '+' ? 'ma-badge--success' : 'ma-badge--danger'">
                    <i :class="trx.trx_type === '+' ? 'fas fa-arrow-up' : 'fas fa-arrow-down'"></i>
                    {{ trx.trx_type }}
                  </span>
                </td>
                <td>
                  <div class="ma-amount-cell" :class="trx.trx_type === '+' ? 'ma-amount-credit' : 'ma-amount-debit'">
                    <i :class="trx.trx_type === '+' ? 'fas fa-plus-circle' : 'fas fa-minus-circle'"></i>
                    <span>₹{{ formatAmount(trx.amount) }}</span>
                  </div>
                </td>
                <td>
                  <div class="ma-balance-cell">
                    <i class="fas fa-wallet"></i>
                    <span class="ma-table__balance">₹{{ formatAmount(trx.post_balance) }}</span>
                  </div>
                </td>
                <td>
                  <span class="ma-table__details" :title="trx.details">{{ trx.details || '—' }}</span>
                </td>
                <td>
                  <span class="ma-badge ma-badge--secondary">{{ formatRemark(trx.remark) }}</span>
                </td>
                <td>
                  <div class="ma-date-cell">
                    <i class="fas fa-clock"></i>
                    <span class="ma-table__date">{{ formatDateTime(trx.created_at) }}</span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="lastPage > 1" class="ma-pagination">
          <button class="ma-page-btn" :disabled="currentPage === 1" @click="fetchTransactions(currentPage - 1)">
            <i class="fas fa-chevron-left"></i>
          </button>
          <template v-for="p in paginationPages" :key="p">
            <button v-if="p === '...'" class="ma-page-btn ma-page-dots" disabled>...</button>
            <button v-else class="ma-page-btn" :class="{ 'ma-page-btn--active': p === currentPage }" @click="fetchTransactions(p)">{{ p }}</button>
          </template>
          <button class="ma-page-btn" :disabled="currentPage === lastPage" @click="fetchTransactions(currentPage + 1)">
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
      formatAmount, formatDateTime, formatRemark, getInitials,
      fetchTransactions, debounceSearch, copyTrx
    }
  }
}
</script>

