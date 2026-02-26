<template>
  <MasterAdminLayout page-title="Withdrawals">
    <div class="ma-withdrawals">
      
      <!-- Top Summary Overlay -->
      <div class="ma-summary-overlay animate__animated animate__fadeInDown">
        <div class="row g-4">
          <div class="col-md-3">
            <div class="ma-stat-card ma-stat-card--primary" @click="filterStatus = ''">
              <div class="ma-stat-card__icon"><i class="fas fa-list-ul"></i></div>
              <div class="ma-stat-card__content">
                <span class="ma-stat-card__lbl">Total Requests</span>
                <span class="ma-stat-card__val">{{ summary.total || 0 }}</span>
              </div>
              <div class="ma-stat-card__glow"></div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="ma-stat-card ma-stat-card--warning" @click="filterStatus = 'processing'">
              <div class="ma-stat-card__icon"><i class="fas fa-clock"></i></div>
              <div class="ma-stat-card__content">
                <span class="ma-stat-card__lbl">Pending/Processing</span>
                <span class="ma-stat-card__val">
                  ₹{{ formatAmount(summary.processing || 0) }}
                  <span class="ma-stat-card__count" v-if="summary.processing_count">({{ summary.processing_count }})</span>
                </span>
              </div>
              <div class="ma-stat-card__glow"></div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="ma-stat-card ma-stat-card--success" @click="filterStatus = 'success'">
              <div class="ma-stat-card__icon"><i class="fas fa-check-double"></i></div>
              <div class="ma-stat-card__content">
                <span class="ma-stat-card__lbl">Approved Total</span>
                <span class="ma-stat-card__val">
                  ₹{{ formatAmount(summary.success || 0) }}
                  <span class="ma-stat-card__count" v-if="summary.success_count">({{ summary.success_count }})</span>
                </span>
              </div>
              <div class="ma-stat-card__glow"></div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="ma-stat-card ma-stat-card--danger" @click="filterStatus = 'rejected'">
              <div class="ma-stat-card__icon"><i class="fas fa-times-circle"></i></div>
              <div class="ma-stat-card__content">
                <span class="ma-stat-card__lbl">Rejected/Refunded</span>
                <span class="ma-stat-card__val">
                  ₹{{ formatAmount(summary.rejected || 0) }}
                  <span class="ma-stat-card__count" v-if="summary.rejected_count">({{ summary.rejected_count }})</span>
                </span>
              </div>
              <div class="ma-stat-card__glow"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters & Search Bar -->
      <div class="ma-filter-bar animate__animated animate__fadeInUp">
        <div class="ma-search-wrapper">
          <i class="fas fa-search"></i>
          <input 
            type="text" 
            v-model="searchQuery" 
            placeholder="Search TRX, Username, Email..."
            @input="debounceSearch"
          >
        </div>
        
        <div class="ma-filter-group">
          <div class="ma-select-wrapper">
            <i class="fas fa-filter"></i>
            <select v-model="filterStatus" @change="fetchWithdrawals(1)">
              <option value="">All Statuses</option>
              <option value="processing">Processing</option>
              <option value="success">Approved</option>
              <option value="rejected">Rejected</option>
            </select>
          </div>

          <div class="ma-select-wrapper">
            <i class="fas fa-wallet"></i>
            <select v-model="filterWallet" @change="fetchWithdrawals(1)">
              <option value="">All Wallets</option>
              <option value="main">Main Wallet</option>
              <option value="affiliate">Affiliate Wallet</option>
            </select>
          </div>

          <div class="ma-date-picker">
            <i class="fas fa-calendar-alt"></i>
            <input type="date" v-model="startDate" @change="fetchWithdrawals(1)">
            <span class="mx-2 text-white/30">to</span>
            <input type="date" v-model="endDate" @change="fetchWithdrawals(1)">
          </div>

          <button class="ma-refresh-btn" @click="fetchWithdrawals()" :disabled="loading">
            <i class="fas fa-sync-alt" :class="{ 'fa-spin': loading }"></i>
          </button>
        </div>
      </div>

      <!-- Main Data Table -->
      <div class="ma-main-card animate__animated animate__fadeIn">
        <div class="ma-card-header">
          <div class="d-flex align-items-center gap-3">
            <div class="ma-card-icon"><i class="fas fa-arrow-up"></i></div>
            <div>
              <h4 class="m-0 font-weight-bold">Withdrawal Requests</h4>
              <p class="m-0 text-white/50 small">Review and manage payouts to users</p>
            </div>
          </div>
          <div class="ma-record-count"> Showing {{ withdrawals.length }} of {{ total }} records </div>
        </div>

        <!-- Mobile View (Card List) -->
        <div class="tw-block md:tw-hidden">
          <div v-if="loading" class="tw-py-20 tw-text-center">
            <div class="ma-loader"></div>
            <p class="tw-mt-3 tw-text-white/40">Fetching records...</p>
          </div>
          <div v-else-if="withdrawals.length === 0" class="tw-py-24 tw-text-center tw-text-white/40">
            <i class="fas fa-inbox fa-3x tw-mb-3"></i>
            <p>No withdrawal requests found.</p>
          </div>
          <div v-else class="tw-p-4 tw-space-y-4">
            <div v-for="w in withdrawals" :key="w.id" class="tw-bg-white/5 tw-border tw-border-white/10 tw-rounded-2xl tw-p-5">
              <div class="tw-flex tw-justify-between tw-items-start tw-mb-4">
                <div class="ma-user-profile">
                  <div class="ma-avatar">{{ getInitials(w.user) }}</div>
                  <div class="ma-user-info">
                    <div class="ma-name">{{ w.user?.firstname }} {{ w.user?.lastname }}</div>
                    <div class="ma-username">@{{ w.user?.username }}</div>
                  </div>
                </div>
                <div class="ma-status-chip" :class="statusInfo(w).badgeClass">
                  {{ statusInfo(w).text }}
                </div>
              </div>

              <div class="tw-grid tw-grid-cols-2 tw-gap-4 tw-mb-4 tw-border-y tw-border-white/5 tw-py-4">
                <div>
                  <div class="tw-text-slate-500 tw-text-[9px] tw-font-bold tw-uppercase tw-tracking-tight tw-mb-1">Amount</div>
                  <div class="tw-text-emerald-400 tw-text-xs tw-font-bold">₹{{ formatAmount(w.after_charge || 0) }}</div>
                  <div class="tw-text-white/30 tw-text-[9px]">G: ₹{{ formatAmount(w.amount) }}</div>
                </div>
                <div>
                  <div class="tw-text-slate-500 tw-text-[9px] tw-font-bold tw-uppercase tw-tracking-tight tw-mb-1">Wallet/Method</div>
                  <div class="tw-text-slate-300 tw-text-xs tw-font-bold">{{ (w.wallet || 'main').toUpperCase() }}</div>
                  <div class="tw-text-white/30 tw-text-[9px]">{{ w.method?.name || 'Manual' }}</div>
                </div>
              </div>

              <div class="tw-flex tw-justify-between tw-items-center">
                <div class="tw-text-white/30 tw-text-[10px]">
                  {{ formatDateTime(w.created_at) }}
                </div>
                <div class="ma-table-actions">
                  <button class="btn-action btn-view" @click="viewDetails(w)">
                    <i class="fas fa-receipt"></i>
                  </button>
                  <template v-if="isPending(w)">

                    <button class="btn-action btn-approve" @click="approve(w)" title="Manual Approve">
                      <i class="fas fa-check"></i>
                    </button>
                    <button class="btn-action btn-reject" @click="reject(w)" title="Reject & Refund">
                      <i class="fas fa-times"></i>
                    </button>
                  </template>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="table-responsive tw-hidden md:tw-block">
          <table class="ma-table">
            <thead>
              <tr>
                <th> TRANSACTION </th>
                <th> USER DETAILS </th>
                <th> WALLET </th>
                <th> METHOD </th>
                <th> FINANCIALS </th>
                <th> STATUS </th>
                <th> REQUEST DATE </th>
                <th class="text-end"> ACTIONS </th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading">
                <td colspan="8" class="text-center py-5">
                  <div class="ma-loader"></div>
                  <p class="mt-3 text-white/40">Fetching records...</p>
                </td>
              </tr>
              <tr v-else-if="withdrawals.length === 0" class="ma-empty-row">
                <td colspan="8" class="text-center py-5 text-white/40">
                  <i class="fas fa-inbox fa-3x mb-3"></i>
                  <p>No withdrawal requests found matches your filter.</p>
                </td>
              </tr>
              <tr v-else v-for="w in withdrawals" :key="w.id" class="ma-table-row">
                <td>
                  <div class="ma-trx-group">
                    <span class="ma-id-label">#{{ w.id }}</span>
                    <div class="ma-trx-pill" @click="copyTrx(w.trx)">
                      <code>{{ w.trx }}</code>
                      <i class="fas fa-copy ms-2"></i>
                    </div>
                  </div>
                </td>
                <td>
                  <div v-if="w.user" class="ma-user-profile">
                    <div class="ma-avatar">{{ getInitials(w.user) }}</div>
                    <div class="ma-user-info">
                      <div class="ma-name">{{ w.user.firstname }} {{ w.user.lastname }}</div>
                      <div class="ma-username">@{{ w.user.username }}</div>
                    </div>
                  </div>
                  <span v-else class="text-white/30">Unknown User</span>
                </td>
                <td>
                  <span class="ma-wallet-badge" :class="'ma-wallet--' + (w.wallet || 'main')">
                    <i :class="walletIcon(w.wallet)"></i>
                    {{ (w.wallet || 'main').toUpperCase() }}
                  </span>
                </td>
                <td>
                  <div class="ma-method-pill">
                    {{ w.method?.name || 'Manual' }}
                  </div>
                </td>
                <td>
                  <div class="ma-financials">
                    <div class="ma-fin-row">
                      <span class="lbl">Gross:</span>
                      <span class="val">₹{{ formatAmount(w.amount) }}</span>
                    </div>
                    <div class="ma-fin-row text-danger/80">
                      <span class="lbl">Charge:</span>
                      <span class="val">-₹{{ formatAmount(w.charge || 0) }}</span>
                    </div>
                    <div class="ma-fin-row ma-fin-net">
                      <span class="lbl">NET PAY:</span>
                      <span class="val">₹{{ formatAmount(w.after_charge || 0) }}</span>
                    </div>
                  </div>
                </td>
                <td>
                  <div class="ma-status-chip" :class="statusInfo(w).badgeClass">
                    <div class="status-dot"></div>
                    {{ statusInfo(w).text }}
                  </div>
                </td>
                <td class="ma-date-col">
                  <div class="ma-date">{{ formatDateTime(w.created_at) }}</div>
                  <div class="ma-time text-white/40 small">{{ w.created_at_human }}</div>
                </td>
                <td>
                  <div class="ma-table-actions">
                    <button class="btn-action btn-view" @click="viewDetails(w)" title="View Documents & Logs">
                      <i class="fas fa-receipt"></i>
                    </button>
                    <template v-if="isPending(w)">

                      <button class="btn-action btn-approve" @click="approve(w)" title="Manual Approve">
                        <i class="fas fa-check"></i>
                      </button>
                      <button class="btn-action btn-reject" @click="reject(w)" title="Reject & Refund">
                        <i class="fas fa-times"></i>
                      </button>
                    </template>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Premium Pagination -->
        <div class="ma-pagination-footer" v-if="lastPage > 1">
          <div class="pagination-controls">
            <button class="pg-btn pg-btn--arrow" :disabled="currentPage === 1" @click="fetchWithdrawals(currentPage - 1)">
              <i class="fas fa-chevron-left"></i>
            </button>
            <div class="pg-numbers">
              <button 
                v-for="p in paginationPages" 
                :key="String(p)" 
                class="pg-btn"
                :class="{ 'active': p === currentPage, 'dots': p === '...' }"
                :disabled="p === '...' || loading"
                @click="p !== '...' && fetchWithdrawals(p)"
              >
                {{ p }}
              </button>
            </div>
            <button class="pg-btn pg-btn--arrow" :disabled="currentPage === lastPage" @click="fetchWithdrawals(currentPage + 1)">
              <i class="fas fa-chevron-right"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Premium Detail Modal -->
      <div v-if="selectedWithdrawal" class="ma-modal-overlay" @click.self="selectedWithdrawal = null">
        <div class="ma-modal ma-modal--wide animate__animated animate__zoomIn">
          <div class="ma-modal__header">
            <div class="d-flex align-items-center gap-3">
              <div class="ma-modal-header-icon"><i class="fas fa-file-invoice-dollar"></i></div>
              <div>
                <h5 class="m-0 font-weight-bold">Withdrawal #{{ selectedWithdrawal.id }}</h5>
                <p class="m-0 text-white/40 small">TRX: {{ selectedWithdrawal.trx }}</p>
              </div>
            </div>
            <button class="ma-modal-close" @click="selectedWithdrawal = null"><i class="fas fa-times"></i></button>
          </div>

          <div class="ma-modal__body">
            <div class="row g-3">

              <!-- LEFT COLUMN -->
              <div class="col-md-6 d-flex flex-column gap-3">

                <!-- User Identity -->
                <div class="ma-modal-card">
                  <div class="ma-modal-card__label"><i class="fas fa-user-circle me-2"></i>USER IDENTITY</div>
                  <div class="d-flex align-items-center gap-3 mt-2">
                    <div class="ma-modal-avatar">{{ getInitials(selectedWithdrawal.user) }}</div>
                    <div>
                      <div class="fw-bold text-white" style="font-size:1.1rem">{{ selectedWithdrawal.user?.firstname }} {{ selectedWithdrawal.user?.lastname }}</div>
                      <div class="text-white/50 small">@{{ selectedWithdrawal.user?.username }}</div>
                      <div class="mt-1">
                        <span class="ma-detail-badge ma-detail-badge--indigo">ADS ID: {{ selectedWithdrawal.user?.ads_id }}</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Bank / UPI Details -->
                <div class="ma-modal-card ma-modal-card--bank flex-grow-1">
                  <div class="ma-modal-card__label"><i class="fas fa-university me-2"></i>BANK / UPI DETAILS</div>
                  <div class="ma-detail-table mt-2">
                    <template v-if="getWithdrawalDetail(selectedWithdrawal, 'payout_type') === 'upi' || (!getWithdrawalDetail(selectedWithdrawal, 'account_number') && getWithdrawalDetail(selectedWithdrawal, 'upi_id'))">
                      <!-- Account Holder Name - UPI -->
                      <div class="ma-detail-row ma-detail-row--highlight">
                        <span class="ma-detail-key"><i class="fas fa-user"></i> Account Holder</span>
                        <span class="ma-detail-val fw-bold" style="color:#fbbf24">
                          {{ getWithdrawalDetail(selectedWithdrawal, 'account_holder_name') || '—' }}
                        </span>
                      </div>
                      <div class="ma-detail-row">
                        <span class="ma-detail-key"><i class="fas fa-mobile-alt"></i> Payout Type</span>
                        <span class="ma-detail-val"><span class="ma-detail-badge ma-detail-badge--purple">UPI Transfer</span></span>
                      </div>
                      <div class="ma-detail-row">
                        <span class="ma-detail-key"><i class="fas fa-at"></i> UPI ID</span>
                        <span class="ma-detail-val ma-detail-val--mono">{{ getWithdrawalDetail(selectedWithdrawal, 'upi_id') || '—' }}</span>
                      </div>
                    </template>
                    <template v-else>
                      <!-- Account Holder Name - Bank -->
                      <div class="ma-detail-row ma-detail-row--highlight">
                        <span class="ma-detail-key"><i class="fas fa-user"></i> Account Holder</span>
                        <span class="ma-detail-val fw-bold" style="color:#fbbf24">
                          {{ getWithdrawalDetail(selectedWithdrawal, 'account_holder_name') || '—' }}
                        </span>
                      </div>
                      <div class="ma-detail-row">
                        <span class="ma-detail-key"><i class="fas fa-credit-card"></i> Payout Type</span>
                        <span class="ma-detail-val"><span class="ma-detail-badge ma-detail-badge--blue">Bank Transfer</span></span>
                      </div>
                      <div class="ma-detail-row">
                        <span class="ma-detail-key"><i class="fas fa-building"></i> Bank Name</span>
                        <span class="ma-detail-val fw-bold">{{ getWithdrawalDetail(selectedWithdrawal, 'bank_name') || '—' }}</span>
                      </div>
                      <div class="ma-detail-row">
                        <span class="ma-detail-key"><i class="fas fa-hashtag"></i> Account No.</span>
                        <span class="ma-detail-val ma-detail-val--mono">{{ getWithdrawalDetail(selectedWithdrawal, 'account_number') || '—' }}</span>
                      </div>
                      <div class="ma-detail-row">
                        <span class="ma-detail-key"><i class="fas fa-code"></i> IFSC Code</span>
                        <span class="ma-detail-val ma-detail-val--mono">{{ getWithdrawalDetail(selectedWithdrawal, 'ifsc_code') || '—' }}</span>
                      </div>
                      <div class="ma-detail-row" v-if="getWithdrawalDetail(selectedWithdrawal, 'bank_registered_no')">
                        <span class="ma-detail-key"><i class="fas fa-phone"></i> Reg. Mobile</span>
                        <span class="ma-detail-val ma-detail-val--mono">{{ getWithdrawalDetail(selectedWithdrawal, 'bank_registered_no') }}</span>
                      </div>
                      <div class="ma-detail-row" v-if="getWithdrawalDetail(selectedWithdrawal, 'upi_id')">
                        <span class="ma-detail-key"><i class="fas fa-at"></i> UPI ID</span>
                        <span class="ma-detail-val">{{ getWithdrawalDetail(selectedWithdrawal, 'upi_id') }}</span>
                      </div>
                    </template>
                  </div>
                </div>

              </div>

              <!-- RIGHT COLUMN -->
              <div class="col-md-6 d-flex flex-column gap-3">

                <!-- Financial Audit -->
                <div class="ma-modal-card ma-modal-card--highlight">
                  <div class="ma-modal-card__label"><i class="fas fa-chart-pie me-2"></i>FINANCIAL AUDIT</div>
                  <div class="ma-modal-fin-grid mt-2">
                    <div class="fin-item">
                      <span class="fin-lbl">Gross Amount</span>
                      <span class="fin-val">₹{{ formatAmount(selectedWithdrawal.amount) }}</span>
                    </div>
                    <div class="fin-item">
                      <span class="fin-lbl">Service Charge</span>
                      <span class="fin-val" style="color:#f87171">-₹{{ formatAmount(selectedWithdrawal.charge || 0) }}</span>
                    </div>
                    <div class="fin-item fin-item--payout">
                      <span class="fin-lbl">Final Payout</span>
                      <span class="fin-val" style="color:#4ade80">₹{{ formatAmount(selectedWithdrawal.after_charge || 0) }}</span>
                    </div>
                  </div>
                </div>

                <!-- GST Payment Info -->
                <div class="ma-modal-card ma-modal-card--gst flex-grow-1">
                  <div class="ma-modal-card__label"><i class="fas fa-receipt me-2"></i>GST PAYMENT INFO</div>
                  <div class="ma-detail-table mt-2">
                    <div class="ma-detail-row">
                      <span class="ma-detail-key"><i class="fas fa-percent"></i> GST Rate</span>
                      <span class="ma-detail-val fw-bold">{{ getWithdrawInfo(selectedWithdrawal.withdraw_information, 'gst_percent') || '18' }}%</span>
                    </div>
                    <div class="ma-detail-row">
                      <span class="ma-detail-key"><i class="fas fa-rupee-sign"></i> GST Paid</span>
                      <span class="ma-detail-val fw-bold" style="color:#4ade80">₹{{ formatAmount(getWithdrawInfo(selectedWithdrawal.withdraw_information, 'gst_amount') || 0) }}</span>
                    </div>
                    <div class="ma-detail-row">
                      <span class="ma-detail-key"><i class="fas fa-fingerprint"></i> GST TRX ID</span>
                      <span class="ma-detail-val ma-detail-val--mono" style="font-size:0.78rem">{{ getWithdrawInfo(selectedWithdrawal.withdraw_information, 'gst_paid_trx') || '—' }}</span>
                    </div>
                    <div class="ma-detail-row">
                      <span class="ma-detail-key"><i class="fas fa-clock"></i> Paid At</span>
                      <span class="ma-detail-val" style="font-size:0.82rem">{{ getWithdrawInfo(selectedWithdrawal.withdraw_information, 'gst_paid_at') || '—' }}</span>
                    </div>
                    <div class="ma-detail-row">
                      <span class="ma-detail-key"><i class="fas fa-shield-alt"></i> Approval</span>
                      <span class="ma-detail-val">
                        <span :class="getWithdrawInfo(selectedWithdrawal.withdraw_information, 'approval_mode')?.includes('Manual') ? 'ma-detail-badge ma-detail-badge--orange' : 'ma-detail-badge ma-detail-badge--green'">
                          {{ getWithdrawInfo(selectedWithdrawal.withdraw_information, 'approval_mode') || 'Auto Gateway' }}
                        </span>
                      </span>
                    </div>
                  </div>
                </div>

                <!-- Rejection Reason -->
                <div v-if="selectedWithdrawal.admin_feedback" class="ma-modal-card" style="border-color: rgba(248,113,113,0.3); background: rgba(220,38,38,0.06)">
                  <div class="ma-modal-card__label" style="color:#f87171"><i class="fas fa-ban me-2"></i>REJECTION REASON</div>
                  <p class="mb-0 mt-2 text-white/80 small">{{ selectedWithdrawal.admin_feedback }}</p>
                </div>

              </div>
            </div>
          </div>

          <div class="ma-modal__footer">
            <div class="d-flex justify-content-between align-items-center w-100">
              <div class="ma-modal-status-pill" :class="statusInfo(selectedWithdrawal).badgeClass">
                <i :class="statusInfo(selectedWithdrawal).icon" class="me-2"></i>
                {{ statusInfo(selectedWithdrawal).text }}
              </div>
              <div class="d-flex gap-2">
                <button class="ma-modal-btn ma-modal-btn--secondary" @click="selectedWithdrawal = null">Close</button>
                <template v-if="isPending(selectedWithdrawal)">
                  <button class="ma-modal-btn ma-modal-btn--success" @click="approve(selectedWithdrawal); selectedWithdrawal = null">Manual</button>
                  <button class="ma-modal-btn ma-modal-btn--danger" @click="reject(selectedWithdrawal); selectedWithdrawal = null">Reject</button>
                </template>
              </div>
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
    const selectedWithdrawal = ref(null)
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
      const num = parseFloat(n)
      return num.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
    }

    const formatDateTime = (d) => {
      if (!d) return '\u2014'
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
      return Number(w?.status) === 2 || String(w?.status_badge || '').toLowerCase().includes('pending')
    }

    const statusInfo = (w) => {
      const s = Number(w?.status)
      if (s === 1) return { text: 'Approved', badgeClass: 'st-approved', icon: 'fas fa-check-circle' }
      if (s === 2) return { text: 'Processing', badgeClass: 'st-processing', icon: 'fas fa-clock' }
      if (s === 3) return { text: 'Rejected', badgeClass: 'st-rejected', icon: 'fas fa-times-circle' }
      
      const t = String(w?.status_text || '').toLowerCase()
      if (t.includes('pending') || t.includes('processing')) return { text: w?.status_text || 'Processing', badgeClass: 'st-processing', icon: 'fas fa-clock' }
      if (t.includes('success') || t.includes('approved')) return { text: w?.status_text || 'Approved', badgeClass: 'st-approved', icon: 'fas fa-check-circle' }
      if (t.includes('reject')) return { text: w?.status_text || 'Rejected', badgeClass: 'st-rejected', icon: 'fas fa-times-circle' }
      return { text: w?.status_text || 'Unknown', badgeClass: 'st-unknown', icon: 'fas fa-circle' }
    }

    const walletIcon = (wallet) => {
      const w = String(wallet || 'main').toLowerCase()
      return w === 'affiliate' ? 'fas fa-users' : 'fas fa-wallet'
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
        if (window.notify) window.notify('success', 'Trx ID Copied');
      } catch (_) {}
    }

    const viewDetails = (w) => {
      selectedWithdrawal.value = w
    }

    const approve = async (w) => {
      if (!isPending(w)) return
      if (!confirm(`Finalize payout MANUALLY for #${w.id}? (Funds will be marked as paid, but you must transfer them yourself.)`)) return
      try {
        const res = await api.post('/admin/withdraw/approve', { id: w.id })
        if (res.data?.status === 'success') {
          if (window.notify) window.notify('success', 'Request Approved');
          fetchWithdrawals()
        }
      } catch (e) {
        if (window.notify) window.notify('error', e.response?.data?.message || 'Approval Failed');
      }
    }



    const reject = async (w) => {
      if (!isPending(w)) return
      const reason = prompt(`Reason for rejection (Visible to User):`)
      if (!reason) return
      try {
        const res = await api.post('/admin/withdraw/reject', { id: w.id, details: reason })
        if (res.data?.status === 'success') {
          if (window.notify) window.notify('success', 'Rejected & Refunded');
          fetchWithdrawals()
        }
      } catch (e) {
        if (window.notify) window.notify('error', e.response?.data?.message || 'Rejection Failed');
      }
    }

    const getWithdrawInfo = (info, key) => {
      if (!info || !Array.isArray(info)) return null
      const found = info.find(i => i.name === key)
      return found ? found.value : null
    }

    const getWithdrawalDetail = (w, key) => {
      // 1. Check withdraw_information (Dynamic form data)
      let val = getWithdrawInfo(w.withdraw_information, key);
      if (val) return val;

      // 2. Fallback to User object fields
      if (w.user && w.user[key]) return w.user[key];

      return null;
    }

    onMounted(() => fetchWithdrawals(1))

    return {
      withdrawals, summary, searchQuery, filterStatus, filterWallet, startDate, endDate,
      loading, total, currentPage, lastPage, paginationPages,
      formatAmount, formatDateTime, getInitials, isPending, statusInfo, walletIcon,
      fetchWithdrawals, debounceSearch, copyTrx, viewDetails, approve, reject,
      selectedWithdrawal, getWithdrawInfo, getWithdrawalDetail
    }
  }
}
</script>

<style scoped>
.ma-withdrawals {
  padding: 10px;
}

/* Glassy Stat Cards */
.ma-summary-overlay {
  margin-bottom: 30px;
}
.ma-stat-card {
  position: relative;
  overflow: hidden;
  background: rgba(30, 41, 59, 0.5);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 20px;
  padding: 24px;
  display: flex;
  align-items: center;
  gap: 20px;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.ma-stat-card:hover {
  transform: translateY(-5px);
  background: rgba(30, 41, 59, 0.8);
  border-color: rgba(255, 255, 255, 0.15);
}
.ma-stat-card__icon {
  width: 56px; height: 56px;
  border-radius: 16px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.5rem; color: #fff;
  z-index: 1;
}
.ma-stat-card--primary .ma-stat-card__icon { background: linear-gradient(135deg, #6366f1, #4f46e5); }
.ma-stat-card--warning .ma-stat-card__icon { background: linear-gradient(135deg, #f59e0b, #d97706); }
.ma-stat-card--success .ma-stat-card__icon { background: linear-gradient(135deg, #10b981, #059669); }
.ma-stat-card--danger  .ma-stat-card__icon { background: linear-gradient(135deg, #ef4444, #dc2626); }

.ma-stat-card__content { z-index: 1; }
.ma-stat-card__lbl { display: block; font-size: 0.8rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; }
.ma-stat-card__val { display: block; font-size: 1.5rem; font-weight: 900; color: #f1f5f9; margin-top: 4px; }
.ma-stat-card__count { font-size: 0.8rem; color: #94a3b8; font-weight: 600; vertical-align: middle; margin-left: 6px; opacity: 0.7; }

/* Filter Bar Visuals */
.ma-filter-bar {
  background: rgba(15, 23, 42, 0.6);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 18px;
  padding: 16px 20px;
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  align-items: center;
  margin-bottom: 25px;
}
.ma-search-wrapper {
  position: relative; flex: 1; min-width: 250px;
}
.ma-search-wrapper i { position: absolute; left: 14px; top: 12px; color: #64748b; }
.ma-search-wrapper input {
  width: 100%; height: 42px; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.05);
  border-radius: 12px; padding: 0 15px 0 40px; color: #fff; outline: none; transition: 0.2s;
}
.ma-search-wrapper input:focus { border-color: #6366f1; background: rgba(0,0,0,0.4); }

.ma-filter-group { display: flex; flex-wrap: wrap; gap: 12px; align-items: center; }
.ma-select-wrapper { position: relative; }
.ma-select-wrapper i { position: absolute; left: 12px; top: 12px; font-size: 0.85rem; color: #64748b; }
.ma-select-wrapper select {
  height: 42px; padding: 0 15px 0 35px; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.05);
  border-radius: 12px; color: #cbd5e1; outline: none; cursor: pointer; min-width: 140px;
}
.ma-date-picker {
  display: flex; align-items: center; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.05);
  border-radius: 12px; padding: 0 15px; height: 42px;
}
.ma-date-picker i { color: #64748b; margin-right: 12px; }
.ma-date-picker input { background: transparent; border: none; color: #fff; outline: none; font-size: 0.9rem; }

.ma-refresh-btn {
  width: 42px; height: 42px; border-radius: 12px; background: rgba(99, 102, 241, 0.15);
  border: 1px solid rgba(99, 102, 241, 0.3); color: #818cf8; cursor: pointer; transition: 0.3s;
}
.ma-refresh-btn:hover { background: rgba(99, 102, 241, 0.3); transform: rotate(180deg); }

/* Main Table Container */
.ma-main-card {
  background: rgba(30, 41, 59, 0.4);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 24px;
  overflow: hidden;
}
.ma-card-header {
  padding: 24px; border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  display: flex; justify-content: space-between; align-items: center;
}
.ma-card-icon {
  width: 44px; height: 44px; border-radius: 12px; background: rgba(99, 102, 241, 0.1);
  color: #818cf8; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;
}
.ma-record-count {
  background: rgba(255,255,255,0.05); padding: 6px 14px; border-radius: 100px; font-size: 0.75rem; color: #94a3b8;
}

/* Elegant Table Stylings */
.ma-table { width: 100%; border-collapse: separate; border-spacing: 0; }
.ma-table thead th {
  padding: 16px 24px; font-size: 0.75rem; color: #64748b; text-transform: uppercase;
  letter-spacing: 1px; font-weight: 800; border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}
.ma-table-row:hover { background: rgba(255, 255, 255, 0.02); }
.ma-table td { padding: 18px 24px; vertical-align: middle; border-bottom: 1px solid rgba(255, 255, 255, 0.02); }

.ma-trx-group { display: flex; flex-direction: column; gap: 6px; }
.ma-id-label { font-size: 0.7rem; font-weight: 800; color: #6366f1; opacity: 0.8; }
.ma-trx-pill {
  display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 8px;
  background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.05); font-size: 0.8rem;
  color: #cbd5e1; cursor: pointer; transition: 0.2s;
}
.ma-trx-pill:hover { border-color: #6366f1; color: #fff; }

.ma-user-profile { display: flex; align-items: center; gap: 12px; }
.ma-avatar {
  width: 40px; height: 40px; border-radius: 12px; background: linear-gradient(135deg, #1e293b, #334155);
  display: flex; align-items: center; justify-content: center; font-weight: 900; color: #818cf8;
  border: 1px solid rgba(129, 140, 248, 0.2); font-size: 0.9rem;
}
.ma-name { font-weight: 700; color: #f1f5f9; font-size: 0.95rem; }
.ma-username { font-size: 0.78rem; color: #64748b; }

.ma-wallet-badge {
  display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 8px;
  font-size: 0.75rem; font-weight: 800;
}
.ma-wallet--main { background: rgba(148, 163, 184, 0.15); color: #94a3b8; }
.ma-wallet--affiliate { background: rgba(16, 185, 129, 0.15); color: #34d399; }

.ma-method-pill {
  padding: 4px 12px; border-radius: 100px; border: 1px solid rgba(255,255,255,0.1);
  font-size: 0.75rem; font-weight: 700; color: #cbd5e1;
}

.ma-financials { display: flex; flex-direction: column; gap: 2px; }
.ma-fin-row { display: flex; justify-content: space-between; gap: 15px; font-size: 0.8rem; }
.ma-fin-row .lbl { color: #64748b; font-weight: 600; }
.ma-fin-row .val { color: #cbd5e1; font-weight: 700; }
.ma-fin-net { margin-top: 4px; padding-top: 4px; border-top: 1px dashed rgba(255,255,255,0.1); }
.ma-fin-net .val { color: #34d399; font-size: 0.95rem; font-weight: 900; }

.ma-status-chip {
  display: inline-flex; align-items: center; gap: 8px; padding: 6px 14px; border-radius: 100px;
  font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;
}
.status-dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }

.st-approved   { background: rgba(16, 185, 129, 0.15); color: #10b981; }
.st-processing { background: rgba(245, 158, 11, 0.15); color: #f59e0b; box-shadow: 0 0 15px rgba(245,158,11,0.1); }
.st-rejected   { background: rgba(239, 68, 68, 0.15); color: #f87171; }
.st-unknown    { background: rgba(148, 163, 184, 0.15); color: #94a3b8; }

.ma-date-col { font-size: 0.85rem; font-weight: 600; color: #f1f5f9; }

/* Action Buttons */
.ma-table-actions { display: flex; gap: 8px; justify-content: flex-end; }
.btn-action {
  width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center;
  border: 1px solid rgba(255,255,255,0.05); background: rgba(30, 41, 59, 0.6); color: #cbd5e1; transition: 0.3s;
}
.btn-action:hover { transform: translateY(-3px); color: #fff; }
.btn-view:hover { background: #6366f1; border-color: #818cf8; box-shadow: 0 4px 12px rgba(99,102,241,0.3); }
.btn-approve:hover { background: #10b981; border-color: #34d399; box-shadow: 0 4px 12px rgba(16,185,129,0.3); }
.btn-pay:hover { background: #6366f1; border-color: #818cf8; box-shadow: 0 4px 12px rgba(99,102,241,0.3); color: #fff; }
.btn-reject:hover { background: #ef4444; border-color: #f87171; box-shadow: 0 4px 12px rgba(239,68,68,0.3); }

/* Premium Pagination */
.ma-pagination-footer { padding: 25px; border-top: 1px solid rgba(255,255,255,0.05); display: flex; justify-content: center; }
.pagination-controls { display: flex; align-items: center; gap: 8px; }
.pg-btn {
  height: 40px; min-width: 40px; padding: 0 14px; border-radius: 12px;
  background: rgba(30, 41, 59, 0.4); border: 1px solid rgba(255,255,255,0.05);
  color: #94a3b8; font-weight: 700; font-size: 0.9rem; transition: 0.2s;
}
.pg-btn:hover:not(:disabled) { background: rgba(99, 102, 241, 0.1); border-color: rgba(99,102,241,0.3); color: #fff; }
.pg-btn.active { background: #6366f1; border-color: #818cf8; color: #fff; box-shadow: 0 4px 12px rgba(99,102,241,0.25); }
.pg-btn--arrow { background: rgba(15, 23, 42, 0.5); }
.pg-btn.dots { border: none; background: transparent; cursor: default; }

/* Loader */
.ma-loader {
  width: 40px; height: 40px; border: 3px solid rgba(99, 102, 241, 0.1);
  border-top-color: #6366f1; border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* PREMIUM MODAL STYLES */
.ma-modal-overlay {
  position: fixed; top: 0; left: 0; width: 100%; height: 100%;
  background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(12px);
  display: flex; align-items: center; justify-content: center; z-index: 1000;
  padding: 20px;
}
.ma-modal {
  background: #1e293b; border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 28px; width: 100%; max-width: 750px; overflow: hidden;
  box-shadow: 0 40px 80px -20px rgba(0, 0, 0, 0.5);
}
.ma-modal__header {
  padding: 24px 30px; display: flex; align-items: center; justify-content: space-between;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05); background: rgba(0,0,0,0.2);
}
.ma-modal-header-icon {
  width: 48px; height: 48px; border-radius: 14px; background: rgba(99, 102, 241, 0.15);
  display: flex; align-items: center; justify-content: center; color: #818cf8; font-size: 1.2rem;
}
.ma-modal-close {
  background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
  color: #94a3b8; width: 36px; height: 36px; border-radius: 50%; cursor: pointer; transition: 0.2s;
}
.ma-modal-close:hover { background: rgba(239, 68, 68, 0.2); color: #f87171; }

.ma-modal__body { padding: 30px; }
.ma-modal-card {
  background: rgba(15, 23, 42, 0.4); border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 20px; padding: 20px; height: 100%;
}
.ma-modal-card--highlight { background: rgba(99, 102, 241, 0.05); border-color: rgba(99, 102, 241, 0.2); }
.ma-modal-card__label {
  font-size: 0.72rem; font-weight: 800; color: #64748b; letter-spacing: 1px; margin-bottom: 18px;
}

.ma-modal-avatar {
  width: 64px; height: 64px; border-radius: 18px; 
  background: linear-gradient(135deg, #3b82f6, #6366f1);
  display: flex; align-items: center; justify-content: center;
  font-size: 1.5rem; font-weight: 900; color: #fff;
}

.ma-modal-fin-grid { display: flex; flex-direction: column; gap: 8px; }
.fin-item { display: flex; justify-content: space-between; font-size: 0.9rem; }
.fin-lbl { color: #94a3b8; font-weight: 600; }
.fin-val { color: #f1f5f9; font-weight: 800; }
.fin-item--payout {
  margin-top: 8px; padding-top: 10px; border-top: 1px dashed rgba(255,255,255,0.1);
  font-size: 1.1rem;
}

.ma-modal-payout-list { display: flex; flex-direction: column; gap: 12px; }
.payout-row { display: flex; justify-content: space-between; align-items: center; padding-bottom: 8px; border-bottom: 1px solid rgba(255,255,255,0.03); }
.payout-lbl { font-size: 0.85rem; font-weight: 600; }
.payout-val { font-size: 0.95rem; }

.ma-modal__footer {
  padding: 20px 30px; background: rgba(0,0,0,0.1); display: flex;
  border-top: 1px solid rgba(255, 255, 255, 0.05);
}
.ma-modal-status-pill {
  padding: 8px 16px; border-radius: 100px; font-size: 0.8rem; font-weight: 800; border: 1px solid rgba(255,255,255,0.1);
}
.ma-modal-btn {
  padding: 10px 20px; border-radius: 14px; font-weight: 700; font-size: 0.9rem; transition: 0.2s; border: 1px solid transparent;
}
.ma-modal-btn:hover { transform: translateY(-2px); }
.ma-modal-btn--secondary { background: rgba(255,255,255,0.05); color: #fff; border-color: rgba(255,255,255,0.1); }
.ma-modal-btn--success { background: #10b981; color: #fff; box-shadow: 0 4px 15px rgba(16,185,129,0.3); }
.ma-modal-btn--danger { background: #ef4444; color: #fff; box-shadow: 0 4px 15px rgba(239,68,68,0.3); }

.ma-modal--wide { max-width: 800px !important; }

/* Bank / GST Card Variants */
.ma-modal-card--bank {
  background: rgba(59, 130, 246, 0.05);
  border-color: rgba(59, 130, 246, 0.2);
}
.ma-modal-card--gst {
  background: rgba(16, 185, 129, 0.05);
  border-color: rgba(16, 185, 129, 0.18);
}

/* Label without bottom margin when using new layout */
.ma-modal-card .ma-modal-card__label {
  margin-bottom: 0;
  display: flex;
  align-items: center;
}

/* Detail Table Rows */
.ma-detail-table {
  display: flex;
  flex-direction: column;
  gap: 0;
}
.ma-detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 9px 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  gap: 12px;
}
.ma-detail-row:last-child { border-bottom: none; }
.ma-detail-row--highlight {
  background: rgba(251, 191, 36, 0.06);
  border-radius: 8px;
  padding: 10px 10px;
  border-bottom: 1px solid rgba(251, 191, 36, 0.12) !important;
  margin-bottom: 2px;
}

.ma-detail-key {
  font-size: 0.8rem;
  font-weight: 600;
  color: #64748b;
  display: flex;
  align-items: center;
  gap: 6px;
  white-space: nowrap;
  flex-shrink: 0;
}
.ma-detail-key i { width: 14px; text-align: center; opacity: 0.7; }

.ma-detail-val {
  font-size: 0.88rem;
  font-weight: 700;
  color: #e2e8f0;
  text-align: right;
  word-break: break-all;
}
.ma-detail-val--mono {
  font-family: 'Courier New', monospace;
  font-size: 0.82rem;
  letter-spacing: 0.5px;
  color: #cbd5e1;
}

/* Detail Badges */
.ma-detail-badge {
  display: inline-flex;
  align-items: center;
  padding: 3px 10px;
  border-radius: 100px;
  font-size: 0.72rem;
  font-weight: 800;
  letter-spacing: 0.3px;
  text-transform: uppercase;
}
.ma-detail-badge--blue   { background: rgba(59,130,246,0.15); color: #60a5fa; border: 1px solid rgba(59,130,246,0.3); }
.ma-detail-badge--purple { background: rgba(139,92,246,0.15); color: #a78bfa; border: 1px solid rgba(139,92,246,0.3); }
.ma-detail-badge--green  { background: rgba(16,185,129,0.15); color: #34d399; border: 1px solid rgba(16,185,129,0.3); }
.ma-detail-badge--orange { background: rgba(245,158,11,0.15); color: #fbbf24; border: 1px solid rgba(245,158,11,0.3); }
.ma-detail-badge--indigo { background: rgba(99,102,241,0.15); color: #818cf8; border: 1px solid rgba(99,102,241,0.3); }

.fs-7 { font-size: 0.75rem !important; }
</style>
