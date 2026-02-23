<template>
  <MasterAdminLayout page-title="Reports & Analytics">
    <div class="ma-reports">

      <!-- Range Selector -->
      <div class="ma-card mb-4">
        <div class="ma-card__body d-flex flex-wrap gap-3 align-items-center">
          <span class="text-muted" style="font-size:0.9rem;"><i class="fas fa-calendar-alt me-2"></i>Date Range</span>
          <div class="ma-range-tabs">
            <button v-for="r in ranges" :key="r.val"
              class="ma-range-tab"
              :class="{ 'ma-range-tab--active': range === r.val }"
              @click="range = r.val; fetchReports()"
            >{{ r.label }}</button>
          </div>
          <button class="ma-btn-refresh ms-auto" @click="fetchReports" :title="'Refresh'">
            <i class="fas fa-sync-alt" :class="{ 'fa-spin': loading }"></i>
          </button>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="ma-center py-5">
        <div class="ma-spinner"></div>
        <p class="text-muted mt-3">Loading analytics...</p>
      </div>

      <template v-else>
        <!-- Summary Stats Row -->
        <div class="row g-3 mb-4">
          <div class="col-sm-6 col-md-3">
            <div class="ma-stat-card ma-stat-card--green">
              <div class="ma-stat-card__icon"><i class="fas fa-arrow-down"></i></div>
              <div class="ma-stat-card__body">
                <div class="ma-stat-card__val">₹{{ fmt(summary.total_deposits) }}</div>
                <div class="ma-stat-card__lbl">Total Deposits</div>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-md-3">
            <div class="ma-stat-card ma-stat-card--red">
              <div class="ma-stat-card__icon"><i class="fas fa-arrow-up"></i></div>
              <div class="ma-stat-card__body">
                <div class="ma-stat-card__val">₹{{ fmt(summary.total_withdrawals) }}</div>
                <div class="ma-stat-card__lbl">Total Withdrawals</div>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-md-3">
            <div class="ma-stat-card ma-stat-card--purple">
              <div class="ma-stat-card__icon"><i class="fas fa-chart-line"></i></div>
              <div class="ma-stat-card__body">
                <div class="ma-stat-card__val">₹{{ fmt(summary.net_revenue) }}</div>
                <div class="ma-stat-card__lbl">Net Revenue</div>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-md-3">
            <div class="ma-stat-card ma-stat-card--blue">
              <div class="ma-stat-card__icon"><i class="fas fa-users"></i></div>
              <div class="ma-stat-card__body">
                <div class="ma-stat-card__val">{{ summary.total_users }}</div>
                <div class="ma-stat-card__lbl">Total Users</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Second stat row  -->
        <div class="row g-3 mb-4">
          <div class="col-6 col-md-2">
            <div class="ma-mini-stat">
              <div class="ma-mini-stat__val text-success">{{ summary.active_users }}</div>
              <div class="ma-mini-stat__lbl">Active Users</div>
            </div>
          </div>
          <div class="col-6 col-md-2">
            <div class="ma-mini-stat">
              <div class="ma-mini-stat__val text-danger">{{ summary.banned_users }}</div>
              <div class="ma-mini-stat__lbl">Banned Users</div>
            </div>
          </div>
          <div class="col-6 col-md-2">
            <div class="ma-mini-stat">
              <div class="ma-mini-stat__val text-warning">{{ summary.kyc_pending }}</div>
              <div class="ma-mini-stat__lbl">KYC Pending</div>
            </div>
          </div>
          <div class="col-6 col-md-2">
            <div class="ma-mini-stat">
              <div class="ma-mini-stat__val text-info">{{ summary.kyc_verified }}</div>
              <div class="ma-mini-stat__lbl">KYC Verified</div>
            </div>
          </div>
          <div class="col-6 col-md-2">
            <div class="ma-mini-stat">
              <div class="ma-mini-stat__val text-warning">{{ summary.pending_deposits }}</div>
              <div class="ma-mini-stat__lbl">Pending Deposits</div>
            </div>
          </div>
          <div class="col-6 col-md-2">
            <div class="ma-mini-stat">
              <div class="ma-mini-stat__val text-warning">{{ summary.pending_withdrawals }}</div>
              <div class="ma-mini-stat__lbl">Pending Withdrawals</div>
            </div>
          </div>
        </div>

        <!-- Today's Quick Stats -->
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <div class="ma-today-card">
              <i class="fas fa-user-plus ma-today-card__icon"></i>
              <div>
                <div class="ma-today-card__val">{{ summary.new_users_today }}</div>
                <div class="ma-today-card__lbl">New Users Today</div>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="ma-today-card ma-today-card--green">
              <i class="fas fa-rupee-sign ma-today-card__icon"></i>
              <div>
                <div class="ma-today-card__val">₹{{ fmt(summary.deposits_today) }}</div>
                <div class="ma-today-card__lbl">Deposits Today</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Charts Row -->
        <div class="row g-4 mb-4">
          <!-- User Growth Chart -->
          <div class="col-lg-6">
            <div class="ma-card h-100">
              <div class="ma-card__header ma-card__header--gradient">
                <h5 class="ma-card__title"><i class="fas fa-user-plus me-2"></i>User Growth (Last {{ range }} days)</h5>
              </div>
              <div class="ma-card__body">
                <div v-if="userGrowth.length === 0" class="text-center text-muted py-4">No data for this period.</div>
                <div v-else class="ma-bar-chart">
                  <div class="ma-bar-chart__labels">
                    <span v-for="(b, i) in userGrowthBars" :key="i" class="ma-bar-chart__label">{{ b.label }}</span>
                  </div>
                  <div class="ma-bar-chart__bars">
                    <div
                      v-for="(b, i) in userGrowthBars"
                      :key="i"
                      class="ma-bar ma-bar--blue"
                      :style="{ height: b.pct + '%' }"
                      :title="b.label + ': ' + b.val + ' users'"
                    >
                      <span class="ma-bar__val">{{ b.val }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Revenue Chart -->
          <div class="col-lg-6">
            <div class="ma-card h-100">
              <div class="ma-card__header ma-card__header--gradient">
                <h5 class="ma-card__title"><i class="fas fa-chart-bar me-2"></i>Deposits vs Withdrawals (Last {{ range }} days)</h5>
              </div>
              <div class="ma-card__body">
                <div v-if="revenueChart.length === 0 && withdrawChart.length === 0" class="text-center text-muted py-4">No data for this period.</div>
                <div v-else>
                  <div class="ma-legend mb-3">
                    <span class="ma-legend__item"><span class="ma-legend__dot ma-legend__dot--green"></span>Deposits</span>
                    <span class="ma-legend__item"><span class="ma-legend__dot ma-legend__dot--red"></span>Withdrawals</span>
                  </div>
                  <div class="ma-bar-chart">
                    <div class="ma-bar-chart__labels">
                      <span v-for="(b, i) in revBars" :key="i" class="ma-bar-chart__label">{{ b.label }}</span>
                    </div>
                    <div class="ma-bar-chart__bars">
                      <div v-for="(b, i) in revBars" :key="i" class="ma-bar-group">
                        <div class="ma-bar ma-bar--green" :style="{ height: b.dPct + '%' }" :title="'Deposit: ₹' + fmt(b.dep)"></div>
                        <div class="ma-bar ma-bar--red" :style="{ height: b.wPct + '%' }" :title="'Withdrawal: ₹' + fmt(b.wd)"></div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Transaction Summary Table -->
        <div class="ma-card mb-4">
          <div class="ma-card__header ma-card__header--gradient">
            <h5 class="ma-card__title"><i class="fas fa-exchange-alt me-2"></i>Financial Summary</h5>
          </div>
          <div class="ma-card__body">
            <div class="row g-3">
              <div class="col-md-4">
                <div class="ma-fin-block ma-fin-block--green">
                  <div class="ma-fin-block__label">Total Deposits</div>
                  <div class="ma-fin-block__val">₹{{ fmt(summary.total_deposits) }}</div>
                  <div class="ma-fin-block__sub">All time</div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="ma-fin-block ma-fin-block--red">
                  <div class="ma-fin-block__label">Total Withdrawals</div>
                  <div class="ma-fin-block__val">₹{{ fmt(summary.total_withdrawals) }}</div>
                  <div class="ma-fin-block__sub">All time</div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="ma-fin-block ma-fin-block--purple">
                  <div class="ma-fin-block__label">Net Revenue</div>
                  <div class="ma-fin-block__val" :class="summary.net_revenue >= 0 ? 'text-success' : 'text-danger'">₹{{ fmt(summary.net_revenue) }}</div>
                  <div class="ma-fin-block__sub">Deposits - Withdrawals</div>
                </div>
              </div>
            </div>

            <div class="row g-3 mt-2">
              <div class="col-md-6">
                <div class="ma-progress-row">
                  <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted small">Active Users</span>
                    <span class="text-white small">{{ summary.active_users }} / {{ summary.total_users }}</span>
                  </div>
                  <div class="ma-progress">
                    <div class="ma-progress__bar ma-progress__bar--green" :style="{ width: userActivePct + '%' }"></div>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="ma-progress-row">
                  <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted small">KYC Verified</span>
                    <span class="text-white small">{{ summary.kyc_verified }} / {{ summary.total_users }}</span>
                  </div>
                  <div class="ma-progress">
                    <div class="ma-progress__bar ma-progress__bar--blue" :style="{ width: kycVerifiedPct + '%' }"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- History Management (Clear Logs) -->
        <div class="ma-card">
          <div class="ma-card__header ma-card__header--danger">
            <h5 class="ma-card__title"><i class="fas fa-trash-alt me-2"></i>History Management (Reset Admin View)</h5>
            <p class="ma-card__subtitle text-white/60">Hide old logs from Admin dashboard without deleting database records. Reversible by clearing timestamps.</p>
          </div>
          <div class="ma-card__body">
            <div class="ma-maintenance-grid">
              <div v-for="action in clearActions" :key="action.key" class="ma-maintenance-item">
                <div class="ma-maintenance-item__content">
                  <div class="ma-maintenance-item__title">{{ action.label }}</div>
                  <div class="ma-maintenance-item__desc">{{ action.desc }}</div>
                </div>
                <button
                  class="ma-btn-clear"
                  :class="{ 'ma-btn-clear--loading': cleaning === action.key }"
                  :disabled="cleaning"
                  @click="confirmClear(action)"
                >
                  <i v-if="cleaning === action.key" class="fas fa-spinner fa-spin"></i>
                  <i v-else class="fas fa-trash"></i>
                  <span>Clear</span>
                </button>
              </div>
            </div>
          </div>
        </div>

      </template>
    </div>
  </MasterAdminLayout>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import MasterAdminLayout from '../../components/master_admin/MasterAdminLayout.vue'
import api from '../../services/api'

export default {
  name: 'MasterAdminReports',
  components: { MasterAdminLayout },
  setup() {
    const loading = ref(false)
    const cleaning = ref(null)
    const range = ref(30)
    const ranges = [
      { val: 7,  label: '7 Days'  },
      { val: 14, label: '14 Days' },
      { val: 30, label: '30 Days' },
      { val: 60, label: '60 Days' },
      { val: 90, label: '90 Days' },
    ]

    const clearActions = [
      { key: 'orders', label: 'Clear All Orders', desc: 'Hides gateway attempts & plan purchases from Admin view. User data is safe.', path: '/admin/clear-history/orders' },
      { key: 'deposits', label: 'Clear Deposits', desc: 'Hides deposit history from Admin view. Does NOT delete user balance.', path: '/admin/clear-history/deposits' },
      { key: 'withdrawals', label: 'Clear Withdrawals', desc: 'Hides withdrawal logs from Admin view. Does NOT affect user history.', path: '/admin/clear-history/withdrawals' },
      { key: 'transactions', label: 'Clear Transactions', desc: 'Hides all transaction logs from Admin view. Safe for users.', path: '/admin/clear-history/transactions' },
    ]

    const summary = ref({
      total_deposits: 0,
      total_withdrawals: 0,
      net_revenue: 0,
      pending_deposits: 0,
      pending_withdrawals: 0,
      total_users: 0,
      active_users: 0,
      banned_users: 0,
      kyc_pending: 0,
      kyc_verified: 0,
      total_transactions: 0,
      new_users_today: 0,
      deposits_today: 0,
    })
    const userGrowth   = ref([])
    const revenueChart = ref([])
    const withdrawChart= ref([])

    const fmt = (n) => {
      const num = parseFloat(n) || 0
      return num.toLocaleString('en-IN', { minimumFractionDigits: 0, maximumFractionDigits: 2 })
    }

    const fetchReports = async () => {
      loading.value = true
      try {
        const res = await api.get('/admin/reports', { params: { range: range.value } })
        const d = res.data?.data || {}
        summary.value = d.summary || summary.value
        userGrowth.value   = d.user_growth || []
        revenueChart.value = d.revenue_chart || []
        withdrawChart.value= d.withdraw_chart || []
      } catch (e) {
        console.error('Reports error', e)
      } finally {
        loading.value = false
      }
    }

    // Bar chart helpers
    const userGrowthBars = computed(() => {
      const data = userGrowth.value
      const max = Math.max(1, ...data.map(d => d.count))
      return data.map(d => ({
        label: d.date ? d.date.slice(5) : '', // MM-DD
        val: d.count,
        pct: Math.max(4, Math.round((d.count / max) * 100))
      }))
    })

    const revBars = computed(() => {
      // Merge deposit and withdrawal by date
      const depMap = {}
      revenueChart.value.forEach(r => { depMap[r.date] = r.total })
      const wdMap = {}
      withdrawChart.value.forEach(r => { wdMap[r.date] = r.total })

      const allDates = [...new Set([
        ...revenueChart.value.map(r => r.date),
        ...withdrawChart.value.map(r => r.date)
      ])].sort()

      const maxVal = Math.max(1, ...allDates.map(d => Math.max(depMap[d] || 0, wdMap[d] || 0)))
      return allDates.map(d => ({
        label: d ? d.slice(5) : '',
        dep: depMap[d] || 0,
        wd:  wdMap[d] || 0,
        dPct: Math.max(4, Math.round(((depMap[d] || 0) / maxVal) * 100)),
        wPct: Math.max(4, Math.round(((wdMap[d] || 0) / maxVal) * 100)),
      }))
    })

    const userActivePct = computed(() => {
      if (!summary.value.total_users) return 0
      return Math.round((summary.value.active_users / summary.value.total_users) * 100)
    })

    const kycVerifiedPct = computed(() => {
      if (!summary.value.total_users) return 0
      return Math.round((summary.value.kyc_verified / summary.value.total_users) * 100)
    })

    const confirmClear = async (action) => {
      if (!confirm(`Are you sure you want to ${action.label.toLowerCase()}? This will hide older logs from your Admin view for a cleaner slate. No actual data will be deleted from the database.`)) return
      const secondCheck = prompt(`Type "CLEAR" to confirm:`)
      if (secondCheck !== 'CLEAR') return

      cleaning.value = action.key
      try {
        const res = await api.post(action.path)
        if (res.data?.status === 'success') {
          if (window.notify) window.notify('success', res.data.message || 'Cleared successfully')
          fetchReports()
        } else {
          if (window.notify) window.notify('error', res.data?.message || 'Failed to clear')
        }
      } catch (e) {
        console.error('Clear error', e)
        if (window.notify) window.notify('error', 'Operation failed')
      } finally {
        cleaning.value = null
      }
    }

    onMounted(fetchReports)

    return {
      loading, range, ranges,
      summary, userGrowth, revenueChart, withdrawChart,
      fmt, fetchReports,
      userGrowthBars, revBars,
      userActivePct, kycVerifiedPct,
      cleaning, clearActions, confirmClear
    }
  }
}
</script>

<style scoped>
.ma-reports { animation: maFade 0.4s ease-out; }
@keyframes maFade { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }

/* Range tabs */
.ma-range-tabs { display: flex; gap: 6px; flex-wrap: wrap; }
.ma-range-tab {
  padding: 6px 14px;
  border-radius: 8px;
  border: 1px solid rgba(255,255,255,0.12);
  background: transparent;
  color: #94a3b8;
  font-size: 0.83rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}
.ma-range-tab:hover { background: rgba(255,255,255,0.06); color: #f1f5f9; }
.ma-range-tab--active { background: rgba(99,102,241,0.2); border-color: rgba(99,102,241,0.5); color: #a5b4fc; }

/* Stat cards */
.ma-stat-card {
  background: rgba(30,41,59,0.6);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 16px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  transition: all 0.3s;
}
.ma-stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,0.2); }
.ma-stat-card__icon {
  width: 52px; height: 52px;
  border-radius: 14px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.3rem; flex-shrink: 0;
}
.ma-stat-card--green .ma-stat-card__icon { background: rgba(16,185,129,0.2); color: #34d399; border: 1px solid rgba(16,185,129,0.3); }
.ma-stat-card--red   .ma-stat-card__icon { background: rgba(239,68,68,0.2);  color: #f87171; border: 1px solid rgba(239,68,68,0.3); }
.ma-stat-card--purple.ma-stat-card__icon { background: rgba(139,92,246,0.2); color: #a78bfa; border: 1px solid rgba(139,92,246,0.3); }
.ma-stat-card--blue  .ma-stat-card__icon { background: rgba(59,130,246,0.2); color: #60a5fa; border: 1px solid rgba(59,130,246,0.3); }
.ma-stat-card--purple .ma-stat-card__icon { background: rgba(139,92,246,0.2); color: #a78bfa; border: 1px solid rgba(139,92,246,0.3); }
.ma-stat-card--blue   .ma-stat-card__icon { background: rgba(59,130,246,0.2); color: #60a5fa; border: 1px solid rgba(59,130,246,0.3); }
.ma-stat-card__val { font-size: 1.4rem; font-weight: 800; color: #f1f5f9; line-height: 1.2; }
.ma-stat-card__lbl { font-size: 0.8rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 3px; }

/* Mini stat */
.ma-mini-stat {
  background: rgba(30,41,59,0.5);
  border: 1px solid rgba(255,255,255,0.07);
  border-radius: 12px;
  padding: 16px;
  text-align: center;
}
.ma-mini-stat__val { font-size: 1.6rem; font-weight: 800; line-height: 1.2; }
.ma-mini-stat__lbl { font-size: 0.77rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 4px; }

/* Today cards */
.ma-today-card {
  background: rgba(30,41,59,0.5);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 14px;
  padding: 20px 24px;
  display: flex;
  align-items: center;
  gap: 18px;
}
.ma-today-card--green { border-color: rgba(16,185,129,0.2); }
.ma-today-card__icon { font-size: 2rem; color: #a78bfa; }
.ma-today-card--green .ma-today-card__icon { color: #34d399; }
.ma-today-card__val { font-size: 1.8rem; font-weight: 800; color: #f1f5f9; }
.ma-today-card__lbl { font-size: 0.82rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 3px; }

/* Bar charts */
.ma-bar-chart {
  display: flex;
  flex-direction: column;
  gap: 8px;
  height: 180px;
}
.ma-bar-chart__bars {
  display: flex;
  align-items: flex-end;
  gap: 4px;
  flex: 1;
  border-bottom: 1px solid rgba(255,255,255,0.08);
  padding-bottom: 4px;
  overflow-x: auto;
}
.ma-bar-chart__labels {
  display: flex;
  gap: 4px;
  overflow-x: auto;
}
.ma-bar-chart__label {
  font-size: 0.65rem;
  color: #64748b;
  text-align: center;
  flex: 1;
  min-width: 24px;
  white-space: nowrap;
}

.ma-bar {
  flex: 1;
  min-width: 12px;
  border-radius: 4px 4px 0 0;
  position: relative;
  cursor: default;
  transition: opacity 0.2s;
}
.ma-bar:hover { opacity: 0.85; }
.ma-bar__val {
  position: absolute;
  top: -16px;
  left: 50%;
  transform: translateX(-50%);
  font-size: 0.6rem;
  color: #94a3b8;
  white-space: nowrap;
}
.ma-bar--blue   { background: linear-gradient(180deg, #6366f1, #4f46e5); }
.ma-bar--green  { background: linear-gradient(180deg, #10b981, #059669); }
.ma-bar--red    { background: linear-gradient(180deg, #ef4444, #dc2626); }

.ma-bar-group { display: flex; gap: 2px; align-items: flex-end; flex: 1; min-width: 28px; }

/* Legend */
.ma-legend { display: flex; gap: 16px; }
.ma-legend__item { display: flex; align-items: center; gap: 6px; font-size: 0.82rem; color: #94a3b8; }
.ma-legend__dot { width: 10px; height: 10px; border-radius: 50%; }
.ma-legend__dot--green { background: #10b981; }
.ma-legend__dot--red   { background: #ef4444; }

/* Financial blocks */
.ma-fin-block {
  background: rgba(30,41,59,0.4);
  border: 1px solid rgba(255,255,255,0.07);
  border-radius: 14px;
  padding: 20px;
  text-align: center;
}
.ma-fin-block--green { border-color: rgba(16,185,129,0.2); }
.ma-fin-block--red   { border-color: rgba(239,68,68,0.2); }
.ma-fin-block--purple{ border-color: rgba(139,92,246,0.2); }
.ma-fin-block__label { font-size: 0.78rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px; }
.ma-fin-block__val { font-size: 1.5rem; font-weight: 800; color: #f1f5f9; }
.ma-fin-block__sub { font-size: 0.75rem; color: #64748b; margin-top: 4px; }

/* Progress bars */
.ma-progress-row { margin-bottom: 8px; }
.ma-progress {
  background: rgba(255,255,255,0.06);
  border-radius: 100px;
  height: 8px;
  overflow: hidden;
}
.ma-progress__bar {
  height: 100%;
  border-radius: 100px;
  transition: width 0.8s ease;
}
.ma-progress__bar--green { background: linear-gradient(90deg, #10b981, #34d399); }
.ma-progress__bar--blue  { background: linear-gradient(90deg, #3b82f6, #60a5fa); }

/* Util */
.text-info { color: #38bdf8 !important; }
.ma-center { text-align: center; }
/* Maintenance */
.ma-card__header--danger {
  background: linear-gradient(135deg, rgba(239,68,68,0.15) 0%, rgba(185,28,28,0.15) 100%);
  border-bottom: 1px solid rgba(239,68,68,0.3);
}

.ma-maintenance-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1rem;
}

.ma-maintenance-item {
  background: rgba(15, 23, 42, 0.4);
  border: 1px solid rgba(255,255,255,0.06);
  border-radius: 12px;
  padding: 1rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
}

.ma-maintenance-item__content { flex: 1; }
.ma-maintenance-item__title { font-size: 0.9rem; font-weight: 700; color: #f1f5f9; margin-bottom: 2px; }
.ma-maintenance-item__desc { font-size: 0.75rem; color: #64748b; }

.ma-btn-clear {
  padding: 0.5rem 1rem;
  border-radius: 8px;
  border: 1px solid rgba(239,68,68,0.4);
  background: rgba(239,68,68,0.1);
  color: #f87171;
  font-size: 0.8rem;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.2s;
}

.ma-btn-clear:hover:not(:disabled) {
  background: rgba(239,68,68,0.25);
  color: #fff;
  transform: translateY(-2px);
}

.ma-btn-clear:disabled { opacity: 0.5; cursor: not-allowed; }
.ma-btn-clear--loading { background: rgba(255,255,255,0.05); color: #94a3b8; border-color: rgba(255,255,255,0.1); }
</style>
