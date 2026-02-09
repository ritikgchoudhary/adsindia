<template>
  <DashboardLayout page-title="Dashboard" :dark-theme="true">
    <div class="dashboard-dark-wrap">
    <!-- KYC Alerts -->
    <div v-if="user.kv === 0 && user.kyc_rejection_reason" class="mb-4">
      <div class="kyc-banner-wrapper kyc-banner-rejected shadow-sm">
        <div class="d-flex align-items-center gap-4">
          <div class="icon-box-high">
            <i class="fa-solid fa-circle-exclamation text-danger fa-2x"></i>
          </div>
          <div class="flex-grow-1" style="min-width: 0;">
            <h4 class="kyc-text-title" style="color: #0f172a; font-weight: 800; font-size: 1.5rem;">KYC Documents Rejected</h4>
            <p class="kyc-text-desc mb-3" style="color: #1e293b; font-weight: 600; line-height: 1.65;">
              {{ kycContent?.reject || 'Your KYC documents have been rejected. Please review the reason and resubmit.' }}
            </p>
            <div class="d-flex flex-wrap gap-2">
              <button class="btn-premium btn-primary-premium" data-bs-toggle="modal" data-bs-target="#kycRejectionReason">
                <i class="fas fa-info-circle"></i> Show Reason
              </button>
              <router-link class="btn-premium" style="background: white !important; border: 1px solid #e2e8f0 !important; color: #000000 !important;" to="/user/kyc-form">
                <i class="fas fa-redo"></i> Re-submit
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-else-if="user.kv === 2" class="mb-4">
      <div class="kyc-banner-wrapper shadow-sm">
        <div class="d-flex align-items-center gap-4">
          <div class="icon-box-high kyc-pending-icon">
            <i class="fas fa-spinner fa-spin fa-2x" style="color: #b45309;"></i>
          </div>
          <div class="flex-grow-1" style="min-width: 0;">
            <h4 class="kyc-text-title" style="color: #0f172a; font-weight: 800; font-size: 1.5rem;">KYC Verification Pending</h4>
            <p class="kyc-text-desc mb-3" style="color: #1e293b; font-weight: 600; line-height: 1.65; max-width: 100%;">
              {{ kycContent?.pending || 'Your KYC verification is being reviewed. We might need some additional information. You will get an email update soon. In the meantime, explore our platform with limited features.' }}
            </p>
            <div class="d-flex flex-wrap gap-2">
              <router-link to="/user/kyc-data" class="btn-premium btn-primary-premium">
                <i class="fas fa-eye"></i> See Data
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>


    <!-- Ads Income / Earning Section -->
    <div class="row g-4 mb-4">
      <div class="col-12">
        <div class="mb-4">
          <h5 class="dashboard-dark-title">
            <i class="fas fa-chart-line me-2"></i>Ads Income / Earning Dashboard
          </h5>
        </div>
        <div class="row g-4">
          <div v-for="(val, key) in { today: 'Today Earning', last7Days: 'Last 7 Days Earning', last30Days: 'Last 30 Days Earning', total: 'Total Earning' }" :key="key" class="col-xl-3 col-md-6">
            <div class="gradient-card p-4 h-100" :class="key === 'today' ? 'gradient-purple' : key === 'last7Days' ? 'gradient-pink' : key === 'last30Days' ? 'gradient-blue' : 'gradient-green'">
              <div class="gradient-card-icon mb-3">
                <i :class="key === 'total' ? 'fas fa-coins' : 'fas fa-calendar-alt'"></i>
              </div>
              <span class="gradient-card-label">{{ val }}</span>
              <h3 class="gradient-card-value m-0">{{ currencySymbol }}{{ formatAmount(earnings[key] ?? 0) }}</h3>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4 scroll-contain">
      <div class="col-xxl-9 col-lg-8">
        <div class="row g-4 mb-4">
          <!-- Balance Widgets - gradient cards -->
          <div v-for="(stat, index) in [
            { label: 'Balance', value: widget.balance, icon: 'fas fa-wallet', grad: 'gradient-purple' },
            { label: 'Affiliate Income', value: widget.total_earning, icon: 'fas fa-tags', grad: 'gradient-pink' },
            { label: 'Ads Income', value: widget.ads_income || 0, icon: 'fas fa-video', grad: 'gradient-blue' },
            { label: 'Withdrawal', value: widget.total_withdraw, icon: 'fas fa-hand-holding-usd', grad: 'gradient-green' }
          ]" :key="index" class="col-xl-3 col-md-6">
            <div class="gradient-card p-4 h-100" :class="stat.grad">
              <div class="gradient-card-icon mb-3">
                <i :class="stat.icon"></i>
              </div>
              <span class="gradient-card-label">{{ stat.label }}</span>
              <h3 class="gradient-card-value m-0">{{ currencySymbol }}{{ formatAmount(stat.value ?? 0) }}</h3>
            </div>
          </div>

          <!-- Transaction Table -->
          <div class="col-12 mt-4">
            <div class="dashboard-table-card p-0">
              <div class="p-4 border-bottom d-flex justify-content-between align-items-center dashboard-table-header">
                <h5 class="dashboard-dark-title mb-0">
                  <i class="fas fa-sync-alt me-2"></i>Latest Transactions
                </h5>
                <router-link to="/user/transactions" class="dashboard-view-all-link">
                  View All <i class="fas fa-arrow-right ms-1"></i>
                </router-link>
              </div>
              <div class="table-responsive">
                <table class="premium-table">
                  <thead>
                    <tr>
                      <th>Transaction ID</th>
                      <th>Date & Time</th>
                      <th>Amount</th>
                      <th>Balance</th>
                      <th>Details</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="trx in transactions" :key="trx?.id || Math.random()">
                      <td><span class="trx-id">{{ trx.trx }}</span></td>
                      <td>
                        <div class="fw-bold trx-date">{{ formatDateTime(trx.created_at) }}</div>
                        <div class="small trx-date-muted">{{ trx.created_at_human }}</div>
                      </td>
                      <td>
                        <span class="fw-bold" :class="trx.trx_type === '+' ? 'text-success' : 'text-danger'">
                          {{ trx.trx_type }}{{ currencySymbol }}{{ formatAmount(trx.amount) }}
                        </span>
                      </td>
                      <td><span class="fw-bold trx-balance">{{ currencySymbol }}{{ formatAmount(trx.post_balance) }}</span></td>
                      <td class="small trx-details">{{ trx.details }}</td>
                    </tr>
                    <tr v-if="transactions.length === 0">
                      <td colspan="5" class="text-center py-5">
                        <div class="opacity-25 mb-3"><i class="fas fa-inbox fa-3x"></i></div>
                        <p class="dashboard-empty-state" style="color: #475569; font-weight: 600;">No transactions found</p>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Sidebar -->
      <div class="col-xxl-3 col-lg-4">
        <div class="dashboard-sidebar">
          <!-- Available Balance Card -->
          <div class="gradient-card balance-card-right p-4 text-center mb-4 gradient-purple">
            <div class="gradient-card-icon mx-auto mb-3">
              <i class="fas fa-wallet"></i>
            </div>
            <p class="gradient-card-label mb-2">Available Balance</p>
            <h2 class="gradient-card-value balance-amount mb-4">{{ currencySymbol }}{{ formatAmount(widget.balance) }}</h2>
            <router-link to="/user/withdraw" class="btn-withdraw-money">
              <i class="fas fa-hand-holding-usd me-2"></i> Withdraw Money
            </router-link>
          </div>
          <!-- Suggest for you -->
          <div class="dashboard-table-card p-4">
            <h6 class="dashboard-dark-title mb-3">
              <i class="fas fa-lightbulb me-2"></i>Suggest for you
            </h6>
            <div class="text-center py-4">
              <i class="fas fa-folder-open fa-2x mb-2 suggest-icon"></i>
              <p class="suggest-empty mb-0">No campaigns found</p>
            </div>
          </div>
        </div>
      </div>
    </div>
    </div>

    <!-- KYC Modal -->
    <div class="modal fade" id="kycRejectionReason" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
          <div class="modal-header border-0 p-4 pb-0">
            <h5 class="modal-heading m-0"><i class="fas fa-info-circle me-2"></i>Rejection Reason</h5>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-4">
            <div class="p-3 bg-light rounded-3" style="font-size: 1.1rem; line-height: 1.6; color: #334155; font-weight: 500;">
              {{ user.kyc_rejection_reason }}
            </div>
          </div>
          <div class="modal-footer border-0 p-4 pt-0">
            <button type="button" class="btn-premium w-100 justify-content-center" style="background: #f1f5f9; color: #475569;" data-bs-dismiss="modal">Got it</button>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import DashboardLayout from '../components/DashboardLayout.vue'
import { userService } from '../services/userService'

export default {
  name: 'Dashboard',
  components: {
    DashboardLayout
  },
  setup() {
    const router = useRouter()
    const loading = ref(false)
    const widget = ref({
      balance: 0,
      total_earning: 0,
      ads_income: 0,
      total_withdraw: 0
    })
    const earnings = ref({
      today: 0,
      last7Days: 0,
      last30Days: 0,
      total: 0
    })
    const transactions = ref([])
    const user = ref({})
    const kycContent = ref(null)
    const currencySymbol = ref('$')
    const showKYCRejectionModal = ref(false)

    const formatAmount = (amount) => {
      if (!amount && amount !== 0) return '0.00'
      const num = parseFloat(amount) || 0
      return num.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }

    const formatDateTime = (dateString) => {
      if (!dateString) return '-'
      const date = new Date(dateString)
      const year = date.getFullYear()
      const month = String(date.getMonth() + 1).padStart(2, '0')
      const day = String(date.getDate()).padStart(2, '0')
      const hour12 = date.getHours() % 12 || 12
      const minutes = String(date.getMinutes()).padStart(2, '0')
      const ampm = date.getHours() >= 12 ? 'PM' : 'AM'
      return `${year}-${month}-${day} ${hour12}:${minutes} ${ampm}`
    }

    const fetchDashboardData = async () => {
      loading.value = true
      try {
        const response = await userService.getDashboard()
        const payload = response.data?.data || response.data
        if (response.status === 'success' && payload) {
          widget.value = payload.widget || {}
          const w = payload.widget || {}
          const defaultEarnings = {
            today: 0,
            last7Days: 0,
            last30Days: 0,
            total: Number(w.total_earning) + Number(w.ads_income || 0) || 0
          }
          earnings.value = payload.earnings || defaultEarnings
          transactions.value = payload.transactions || []
          campaigns.value = payload.campaigns || []
          user.value = payload.user || {}
          kycContent.value = payload.kyc_content
          currencySymbol.value = payload.currency_symbol || '₹'
        }
      } catch (error) {
        console.error('Error loading dashboard:', error)
        if (error.response?.status === 401) {
          router.push('/user/login')
        }
      } finally {
        loading.value = false
      }
    }

    onMounted(() => {
      fetchDashboardData()
      
      // Fix scrolling issue - ensure page can scroll (multiple attempts)
      const fixScrolling = () => {
        document.body.style.setProperty('overflow', 'auto', 'important')
        document.body.style.setProperty('height', 'auto', 'important')
        document.documentElement.style.setProperty('overflow', 'auto', 'important')
        document.documentElement.style.setProperty('height', 'auto', 'important')
        
        const elements = ['.dashboard-body', '.dashboard__right', '.dashboard', '.dashboard__inner', '.container-fluid']
        elements.forEach(selector => {
          document.querySelectorAll(selector).forEach(el => {
            el.style.setProperty('overflow-y', 'visible', 'important')
            el.style.setProperty('height', 'auto', 'important')
            el.style.setProperty('max-height', 'none', 'important')
          })
        })
      }
      
      // Fix multiple times
      fixScrolling()
      setTimeout(fixScrolling, 100)
      setTimeout(fixScrolling, 500)
      setTimeout(fixScrolling, 1000)
    })

    return {
      widget,
      earnings,
      transactions,
      user,
      kycContent,
      currencySymbol,
      formatAmount,
      formatDateTime
    }
  }
}
</script>
<style scoped>
/* Bulletproof Dashboard Layout Architecture */
.dashboard {
  font-family: 'Outfit', sans-serif;
  background-color: #f8fafc !important;
  min-height: 100vh !important;
  width: 100% !important;
  overflow: hidden !important; 
  display: flex !important;
}

.dashboard__inner {
  display: flex !important;
  flex-direction: row !important;
  width: 100% !important;
  min-height: 100vh !important;
  flex-wrap: nowrap !important;
}

/* Sidebar: Fixed & Push */
:deep(.sidebar-menu) {
  width: 280px !important;
  min-width: 280px !important;
  max-width: 280px !important;
  flex: 0 0 280px !important;
  height: 100vh !important;
  position: sticky !important;
  top: 0 !important;
  z-index: 100 !important;
  background-color: #1e293b !important;
}

/* Content Area: Flexible but Contained */
.dashboard__right {
  flex: 1 !important;
  min-width: 0 !important;
  background-color: #f8fafc !important;
  display: flex !important;
  flex-direction: column !important;
  height: 100vh !important;
  overflow-y: auto !important;
  overflow-x: hidden !important;
}

.dashboard-body {
  padding: 2.5rem !important;
  width: 100% !important;
  flex: 1 !important;
}

:deep(.page-header-title) {
  font-weight: 800 !important;
  font-size: 2.25rem !important;
  color: #0f172a !important;
  margin-bottom: 2.5rem !important;
  letter-spacing: -0.04em !important;
}

/* IMPORTANT: Fix for Icons (Font Awesome) */
:deep(.fas), :deep(.fab), :deep(.far), :deep(.fa), :deep(.la), :deep(.las) {
  font-family: "Font Awesome 6 Free", "Font Awesome 6 Brands", "Line Awesome Free", "Line Awesome Brands", "FontAwesome" !important;
  font-weight: 900 !important;
  display: inline-block !important;
  font-style: normal !important;
}

/* Sidebar Logo Styling */
:deep(.sidebar-logo) {
  background: white !important;
  padding: 1.5rem !important;
  border-bottom: 1px solid #e2e8f0 !important;
  margin-bottom: 1.5rem !important;
  display: flex !important;
  justify-content: center !important;
}

@media (max-width: 991px) {
  :deep(.sidebar-menu) {
    position: fixed !important;
    left: -280px !important;
    transition: left 0.3s ease !important;
  }
  :deep(.sidebar-menu.show-sidebar) {
    left: 0 !important;
  }
  .dashboard-body {
    padding: 1.5rem !important;
  }
}

/* Dashboard View - Dark theme wrap */
.dashboard-dark-wrap {
  width: 100%;
  min-height: 400px;
  padding-bottom: 2rem;
}
.dashboard-dark-title {
  color: #ffffff !important;
  font-weight: 700 !important;
  font-size: 1.25rem !important;
  font-family: 'Outfit', system-ui, -apple-system, sans-serif !important;
}
.dashboard-dark-title i {
  color: #60a5fa !important;
}

/* Gradient cards (earning + balance widgets) */
.gradient-card {
  border-radius: 16px !important;
  border: none !important;
  box-shadow: 0 4px 20px rgba(0,0,0,0.15) !important;
  color: #ffffff !important;
  min-height: 140px !important;
  display: flex !important;
  flex-direction: column !important;
  justify-content: flex-start !important;
  transition: transform 0.2s ease, box-shadow 0.2s ease !important;
}
.gradient-card:hover {
  transform: translateY(-2px) !important;
  box-shadow: 0 8px 28px rgba(0,0,0,0.2) !important;
}
.gradient-card-label {
  color: rgba(255,255,255,0.9) !important;
  font-size: 0.8rem !important;
  font-weight: 600 !important;
  text-transform: uppercase !important;
  letter-spacing: 0.03em !important;
  display: block !important;
  margin-bottom: 0.35rem !important;
  font-family: 'Outfit', system-ui, sans-serif !important;
}
.gradient-card-value {
  color: #ffffff !important;
  font-size: 1.6rem !important;
  font-weight: 800 !important;
  line-height: 1.2 !important;
  font-family: 'Outfit', system-ui, sans-serif !important;
  word-break: break-all !important;
}
.gradient-card-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: rgba(255,255,255,0.2) !important;
  color: #ffffff !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  font-size: 1.25rem !important;
  flex-shrink: 0 !important;
}
.gradient-purple {
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%) !important;
}
.gradient-pink {
  background: linear-gradient(135deg, #ec4899 0%, #f472b6 100%) !important;
}
.gradient-blue {
  background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%) !important;
}
.gradient-green {
  background: linear-gradient(135deg, #10b981 0%, #34d399 100%) !important;
}

/* Right side balance card */
.balance-card-right {
  min-height: 200px !important;
}
.balance-card-right .balance-amount {
  font-size: 1.75rem !important;
  word-break: break-all !important;
}
.btn-withdraw-money {
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  width: 100% !important;
  min-height: 48px !important;
  padding: 12px 1rem !important;
  background: rgba(30, 27, 75, 0.95) !important;
  color: #ffffff !important;
  font-weight: 700 !important;
  font-size: 1rem !important;
  border-radius: 12px !important;
  text-decoration: none !important;
  border: none !important;
  transition: all 0.2s !important;
  box-shadow: 0 2px 8px rgba(0,0,0,0.15) !important;
}
.btn-withdraw-money:hover {
  background: #1e1b4b !important;
  color: #ffffff !important;
  box-shadow: 0 4px 12px rgba(0,0,0,0.2) !important;
}

/* Suggest for you card */
.dashboard-table-card.p-4 {
  min-height: 120px !important;
}

/* Table card - light background */
.dashboard-table-card {
  background: #ffffff !important;
  border-radius: 16px !important;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08) !important;
  border: 1px solid #e2e8f0 !important;
  overflow: hidden !important;
}
.dashboard-table-header {
  background: #f8fafc !important;
  border-bottom: 1px solid #e2e8f0 !important;
  flex-wrap: wrap !important;
  gap: 0.5rem !important;
}
.dashboard-table-card .dashboard-dark-title {
  color: #0f172a !important;
}
.dashboard-table-card .dashboard-dark-title i {
  color: #4f46e5 !important;
}
.dashboard-view-all-link {
  color: #3b82f6 !important;
  font-weight: 600 !important;
  text-decoration: none !important;
}
.dashboard-view-all-link:hover {
  color: #2563eb !important;
}
.trx-date, .trx-balance {
  color: #1e293b !important;
}
.trx-date-muted, .trx-details {
  color: #64748b !important;
}

/* Suggest for you */
.suggest-icon {
  color: #94a3b8 !important;
}
.suggest-empty {
  color: #64748b !important;
  font-weight: 500 !important;
}

/* Dashboard View - Proper Spacing & Visibility */
.dashboard-view {
  font-family: 'Outfit', sans-serif;
  color: #0f172a !important;
}


/* Page Section Headers - high contrast */
.section-main-title {
  color: #0f172a !important;
  font-weight: 800 !important;
  font-size: 1.5rem !important;
  margin-bottom: 0.5rem !important;
  display: block !important;
}

.section-sub-title {
  color: #334155 !important;
  font-weight: 600 !important;
  font-size: 0.95rem !important;
  display: block !important;
  margin-bottom: 1.5rem !important;
}

.dashboard-empty-state {
  color: #475569 !important;
}

/* Stat Cards Refinement */
.premium-card {
  background: white !important;
  border: 1px solid #e2e8f0 !important;
  border-radius: 20px !important;
  box-shadow: 0 4px 15px rgba(0,0,0,0.03) !important;
  padding: 1.5rem !important;
}

.icon-box-high {
  width: 56px !important;
  height: 56px !important;
  border-radius: 14px !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  flex-shrink: 0 !important;
  margin-bottom: 1rem !important;
}

.icon-blue { background: #eef2ff !important; color: #4f46e5 !important; }
.icon-pink { background: #fdf2f8 !important; color: #db2777 !important; }
.icon-cyan { background: #ecfeff !important; color: #0891b2 !important; }
.icon-green { background: #ecfdf5 !important; color: #10b981 !important; }

.stat-label {
  color: #64748b !important;
  font-size: 0.85rem !important;
  font-weight: 800 !important;
  text-transform: uppercase !important;
  margin-bottom: 0.5rem !important;
  display: block !important;
}

.stat-value {
  color: #0f172a !important;
  font-size: 1.85rem !important;
  font-weight: 950 !important;
}

/* KYC Banner - readable text, no truncation */
.kyc-banner-wrapper {
  background: #fffbeb !important;
  border: 1px solid #fde68a !important;
  border-left: 10px solid #f59e0b !important;
  border-radius: 16px !important;
  padding: 1.5rem 1.5rem 1.5rem 1.25rem !important;
  overflow: visible !important;
  max-width: 100% !important;
}
@media (max-width: 768px) {
  .kyc-banner-wrapper {
    padding: 1.25rem !important;
  }
  .kyc-banner-wrapper .d-flex.gap-4 {
    flex-direction: column !important;
    align-items: flex-start !important;
    gap: 1rem !important;
  }
}

.kyc-banner-wrapper .kyc-text-title {
  color: #0f172a !important;
  font-weight: 800 !important;
}

.kyc-banner-wrapper .kyc-text-desc {
  color: #1e293b !important;
  font-weight: 600 !important;
  max-width: 100% !important;
}

.kyc-banner-rejected {
  background: #fef2f2 !important;
  border: 1px solid #fecaca !important;
  border-left: 10px solid #ef4444 !important;
}

.kyc-banner-wrapper .flex-grow-1 {
  overflow: visible !important;
  min-width: 0 !important;
}

.kyc-text-title {
  color: #0f172a !important;
  font-weight: 950 !important;
  font-size: 1.75rem !important;
  margin-bottom: 0.75rem !important;
}

.kyc-text-desc {
  color: #1e293b !important;
  font-weight: 600 !important;
  line-height: 1.65 !important;
  max-width: 100% !important;
  overflow: visible !important;
  word-wrap: break-word !important;
}

/* KYC Pending: icon clearly visible on yellow banner */
.kyc-pending-icon {
  background: rgba(180, 83, 9, 0.15) !important;
  color: #b45309 !important;
}
.kyc-pending-icon i {
  color: #b45309 !important;
}

/* Ads Earning Overview - strong readable colors */
.ads-overview-title {
  color: #0f172a !important;
  font-weight: 800 !important;
  font-size: 1.5rem !important;
}
.ads-overview-subtitle {
  color: #1e293b !important;
  font-weight: 600 !important;
  font-size: 0.95rem !important;
}
.ads-card-label {
  color: #475569 !important;
  font-weight: 700 !important;
  text-transform: uppercase !important;
  font-size: 0.85rem !important;
}
.ads-card-value {
  color: #0f172a !important;
  font-size: 1.85rem !important;
  font-weight: 900 !important;
}

/* Table - Clear Contrast */
.premium-table {
  width: 100% !important;
  border-collapse: collapse !important;
  font-family: 'Outfit', system-ui, sans-serif !important;
}

.premium-table th {
  background: #f8fafc !important;
  color: #334155 !important;
  font-weight: 700 !important;
  padding: 1rem 0.75rem !important;
  text-transform: uppercase !important;
  font-size: 0.75rem !important;
  letter-spacing: 0.03em !important;
  border-bottom: 2px solid #e2e8f0 !important;
  white-space: nowrap !important;
}

.premium-table td {
  color: #1e293b !important;
  font-weight: 600 !important;
  padding: 1rem 0.75rem !important;
  border-bottom: 1px solid #f1f5f9 !important;
  vertical-align: middle !important;
}

.premium-table tbody tr:hover {
  background: #f8fafc !important;
}

.table-responsive {
  overflow-x: auto !important;
  -webkit-overflow-scrolling: touch !important;
}

/* Balance Card (Right Side) */
.balance-card-inner {
  background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%) !important;
  color: white !important;
  border: none !important;
}

.balance-card-inner * {
  color: white !important;
}

.balance-card-inner .icon-box-high {
  background: rgba(255,255,255,0.2) !important;
}

.scroll-contain {
  max-width: 100% !important;
  margin: 0 !important;
}

/* Responsive grid */
@media (max-width: 576px) {
  .gradient-card {
    min-height: 120px !important;
  }
  .gradient-card-value {
    font-size: 1.35rem !important;
  }
  .dashboard-table-header {
    flex-direction: column !important;
    align-items: flex-start !important;
  }
}

/* ========== Headings – always visible ========== */
.dashboard-section-heading {
  color: #0f172a !important;
  font-weight: 800 !important;
  font-size: 1.25rem !important;
}
.dashboard-section-heading i {
  color: #4f46e5 !important;
}
.dashboard-table-header {
  background: #f8fafc !important;
  border-bottom: 1px solid #e2e8f0 !important;
}

/* ========== Table & body text – readable ========== */
.dashboard-cell-strong {
  color: #0f172a !important;
  font-weight: 700 !important;
}
.dashboard-cell-muted {
  color: #64748b !important;
  font-weight: 500 !important;
}
.dashboard-view p,
.dashboard-body p,
.dashboard-view .small {
  color: #334155 !important;
  font-weight: 500 !important;
}

/* ========== Buttons – clear color & text ========== */
.btn-premium {
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  padding: 0.6rem 1.25rem !important;
  font-weight: 700 !important;
  font-size: 0.95rem !important;
  border-radius: 12px !important;
  text-decoration: none !important;
  transition: all 0.2s !important;
  border: none !important;
  cursor: pointer !important;
}
.btn-primary-premium {
  background: #4f46e5 !important;
  color: #ffffff !important;
}
.btn-primary-premium:hover {
  background: #4338ca !important;
  color: #ffffff !important;
}
.btn-premium:not(.btn-primary-premium):not(.btn-withdraw) {
  background: #f1f5f9 !important;
  color: #0f172a !important;
  border: 1px solid #e2e8f0 !important;
}
.btn-premium:not(.btn-primary-premium):not(.btn-withdraw):hover {
  background: #e2e8f0 !important;
  color: #0f172a !important;
}
.btn-withdraw {
  background: #ffffff !important;
  color: #4f46e5 !important;
  font-weight: 700 !important;
  padding: 12px 1rem !important;
}
.btn-withdraw:hover {
  background: #eef2ff !important;
  color: #4338ca !important;
}
/* Modal heading */
.modal-heading {
  color: #0f172a !important;
  font-weight: 800 !important;
  font-size: 1.25rem !important;
}
.modal-heading i {
  color: #dc2626 !important;
}

/* Modal button */
button.btn-premium[data-bs-dismiss="modal"] {
  background: #334155 !important;
  color: #f1f5f9 !important;
}
button.btn-premium[data-bs-dismiss="modal"]:hover {
  background: #1e293b !important;
  color: #ffffff !important;
}

/* Balance card right sidebar */
.balance-card-label {
  color: rgba(255, 255, 255, 0.9) !important;
  font-size: 0.8rem !important;
  font-weight: 600 !important;
  text-transform: uppercase !important;
  letter-spacing: 0.05em !important;
}
.balance-card-amount {
  font-size: 1.75rem !important;
  font-weight: 800 !important;
  color: #ffffff !important;
}

:deep(*) {
  font-family: inherit; /* Don't force global font on everything to save icons */
}
</style>
