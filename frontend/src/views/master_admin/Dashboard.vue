<template>
  <MasterAdminLayout page-title="Dashboard">
    <div class="ma-reports">

      <!-- Range Selector -->
      <div class="tw-bg-slate-900/50 tw-backdrop-blur-xl tw-border tw-border-white/5 tw-rounded-2xl tw-p-4 tw-mb-6 tw-flex tw-flex-wrap tw-gap-4 tw-items-center tw-justify-between tw-shadow-sm">
        <div class="tw-flex tw-items-center tw-gap-4">
          <span class="tw-text-slate-400 tw-font-bold tw-text-sm tw-uppercase tw-tracking-widest">
            <i class="fas fa-calendar-alt tw-mr-2"></i>Date Range
          </span>
          <div class="tw-flex tw-gap-2 tw-bg-black/20 tw-p-1.5 tw-rounded-xl tw-border tw-border-white/5">
            <button v-for="r in ranges" :key="r.val"
              class="tw-px-4 tw-py-2 tw-rounded-lg tw-text-xs tw-font-bold tw-transition-all tw-duration-300 tw-border tw-border-transparent"
              :class="range === r.val ? 'tw-bg-indigo-500/20 tw-text-indigo-400 tw-border-indigo-500/30 tw-shadow-inner' : 'tw-text-slate-400 hover:tw-text-white hover:tw-bg-white/5'"
              @click="range = r.val; fetchReports()"
            >{{ r.label }}</button>
          </div>
        </div>
        <button class="tw-w-10 tw-h-10 tw-rounded-xl tw-bg-indigo-600 hover:tw-bg-indigo-500 tw-text-white tw-border-0 tw-flex tw-items-center tw-justify-center tw-transition-all tw-duration-300 tw-shadow-[0_4px_14px_0_rgba(79,70,229,0.39)] hover:tw-shadow-[0_6px_20px_rgba(79,70,229,0.23)] hover:-tw-translate-y-0.5" @click="fetchReports" title="Refresh">
          <i class="fas fa-sync-alt" :class="{ 'fa-spin': loading }"></i>
        </button>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="tw-text-center tw-py-20">
        <div class="tw-w-16 tw-h-16 tw-border-4 tw-border-indigo-500/30 tw-border-t-indigo-500 tw-rounded-full tw-animate-spin tw-mx-auto tw-mb-4"></div>
        <div class="tw-text-slate-400 tw-font-medium">Fetching rich analytics...</div>
      </div>

      <template v-else>
        <!-- Summary Stats Row -->
        <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 lg:tw-grid-cols-4 tw-gap-4 tw-mb-6">
          <!-- Total Deposits -->
          <div class="tw-bg-gradient-to-br tw-from-emerald-900/40 tw-to-slate-900/80 tw-backdrop-blur-xl tw-rounded-[24px] tw-p-6 tw-border tw-border-emerald-500/20 tw-relative tw-overflow-hidden tw-group hover:-tw-translate-y-1 tw-transition-all tw-duration-300 hover:tw-shadow-[0_8px_30px_-10px_rgba(16,185,129,0.3)]">
            <div class="tw-absolute -tw-right-6 -tw-top-6 tw-w-24 tw-h-24 tw-bg-emerald-500/10 tw-rounded-full tw-blur-2xl transition-all group-hover:tw-bg-emerald-500/20"></div>
            <div class="tw-flex tw-items-center tw-justify-between tw-mb-4 relative">
              <div class="tw-w-12 tw-h-12 tw-rounded-xl tw-bg-emerald-500/10 tw-text-emerald-400 tw-flex tw-items-center tw-justify-center tw-text-xl tw-border tw-border-emerald-500/20 tw-shadow-inner"><i class="fas fa-arrow-down"></i></div>
              <span class="tw-text-emerald-400/50 tw-text-xs tw-font-bold tw-uppercase">All Time</span>
            </div>
            <h3 class="tw-text-white tw-text-3xl tw-font-black tw-mb-1 tw-tracking-tight">₹{{ fmt(summary.total_deposits) }}</h3>
            <p class="tw-text-slate-400 tw-text-xs tw-font-bold tw-uppercase tw-tracking-widest">Total Deposits</p>
          </div>

          <!-- Total Withdrawals -->
          <div class="tw-bg-gradient-to-br tw-from-rose-900/40 tw-to-slate-900/80 tw-backdrop-blur-xl tw-rounded-[24px] tw-p-6 tw-border tw-border-rose-500/20 tw-relative tw-overflow-hidden tw-group hover:-tw-translate-y-1 tw-transition-all tw-duration-300 hover:tw-shadow-[0_8px_30px_-10px_rgba(244,63,94,0.3)]">
            <div class="tw-absolute -tw-right-6 -tw-top-6 tw-w-24 tw-h-24 tw-bg-rose-500/10 tw-rounded-full tw-blur-2xl transition-all group-hover:tw-bg-rose-500/20"></div>
            <div class="tw-flex tw-items-center tw-justify-between tw-mb-4 relative">
              <div class="tw-w-12 tw-h-12 tw-rounded-xl tw-bg-rose-500/10 tw-text-rose-400 tw-flex tw-items-center tw-justify-center tw-text-xl tw-border tw-border-rose-500/20 tw-shadow-inner"><i class="fas fa-arrow-up"></i></div>
              <span class="tw-text-rose-400/50 tw-text-xs tw-font-bold tw-uppercase">All Time</span>
            </div>
            <h3 class="tw-text-white tw-text-3xl tw-font-black tw-mb-1 tw-tracking-tight">₹{{ fmt(summary.total_withdrawals) }}</h3>
            <p class="tw-text-slate-400 tw-text-xs tw-font-bold tw-uppercase tw-tracking-widest">Total Withdrawals</p>
          </div>

          <!-- Net Revenue -->
          <div class="tw-bg-gradient-to-br tw-from-purple-900/40 tw-to-slate-900/80 tw-backdrop-blur-xl tw-rounded-[24px] tw-p-6 tw-border tw-border-purple-500/20 tw-relative tw-overflow-hidden tw-group hover:-tw-translate-y-1 tw-transition-all tw-duration-300 hover:tw-shadow-[0_8px_30px_-10px_rgba(168,85,247,0.3)]">
            <div class="tw-absolute -tw-right-6 -tw-top-6 tw-w-24 tw-h-24 tw-bg-purple-500/10 tw-rounded-full tw-blur-2xl transition-all group-hover:tw-bg-purple-500/20"></div>
            <div class="tw-flex tw-items-center tw-justify-between tw-mb-4 relative">
              <div class="tw-w-12 tw-h-12 tw-rounded-xl tw-bg-purple-500/10 tw-text-purple-400 tw-flex tw-items-center tw-justify-center tw-text-xl tw-border tw-border-purple-500/20 tw-shadow-inner"><i class="fas fa-chart-line"></i></div>
              <span class="tw-text-purple-400/50 tw-text-xs tw-font-bold tw-uppercase">Net Profit</span>
            </div>
            <h3 class="tw-text-white tw-text-3xl tw-font-black tw-mb-1 tw-tracking-tight">₹{{ fmt(summary.net_revenue) }}</h3>
            <p class="tw-text-slate-400 tw-text-xs tw-font-bold tw-uppercase tw-tracking-widest">System Revenue</p>
          </div>

          <!-- Total Users -->
          <div class="tw-bg-gradient-to-br tw-from-blue-900/40 tw-to-slate-900/80 tw-backdrop-blur-xl tw-rounded-[24px] tw-p-6 tw-border tw-border-blue-500/20 tw-relative tw-overflow-hidden tw-group hover:-tw-translate-y-1 tw-transition-all tw-duration-300 hover:tw-shadow-[0_8px_30px_-10px_rgba(59,130,246,0.3)]">
            <div class="tw-absolute -tw-right-6 -tw-top-6 tw-w-24 tw-h-24 tw-bg-blue-500/10 tw-rounded-full tw-blur-2xl transition-all group-hover:tw-bg-blue-500/20"></div>
            <div class="tw-flex tw-items-center tw-justify-between tw-mb-4 relative">
              <div class="tw-w-12 tw-h-12 tw-rounded-xl tw-bg-blue-500/10 tw-text-blue-400 tw-flex tw-items-center tw-justify-center tw-text-xl tw-border tw-border-blue-500/20 tw-shadow-inner"><i class="fas fa-users"></i></div>
              <span class="tw-text-blue-400/50 tw-text-xs tw-font-bold tw-uppercase">Community</span>
            </div>
            <h3 class="tw-text-white tw-text-3xl tw-font-black tw-mb-1 tw-tracking-tight">{{ fmt(summary.total_users) }}</h3>
            <p class="tw-text-slate-400 tw-text-xs tw-font-bold tw-uppercase tw-tracking-widest">Total Users</p>
          </div>
        </div>

        <!-- Quick Mini Stats Row -->
        <div class="tw-grid tw-grid-cols-2 lg:tw-grid-cols-6 tw-gap-4 tw-mb-6">
          <div class="tw-bg-slate-900/60 tw-backdrop-blur-xl tw-border tw-border-white/5 tw-rounded-2xl tw-p-4 tw-text-center hover:tw-bg-slate-800/80 tw-transition-colors">
              <div class="tw-text-emerald-400 tw-font-black tw-text-2xl tw-mb-1">{{ summary.active_users }}</div>
              <div class="tw-text-slate-400 tw-text-[10px] tw-font-bold tw-uppercase tw-tracking-wide">Active Users</div>
          </div>
          <div class="tw-bg-slate-900/60 tw-backdrop-blur-xl tw-border tw-border-white/5 tw-rounded-2xl tw-p-4 tw-text-center hover:tw-bg-slate-800/80 tw-transition-colors">
              <div class="tw-text-rose-400 tw-font-black tw-text-2xl tw-mb-1">{{ summary.banned_users }}</div>
              <div class="tw-text-slate-400 tw-text-[10px] tw-font-bold tw-uppercase tw-tracking-wide">Banned</div>
          </div>
          <div class="tw-bg-slate-900/60 tw-backdrop-blur-xl tw-border tw-border-white/5 tw-rounded-2xl tw-p-4 tw-text-center hover:tw-bg-slate-800/80 tw-transition-colors">
              <div class="tw-text-amber-400 tw-font-black tw-text-2xl tw-mb-1">{{ summary.kyc_pending }}</div>
              <div class="tw-text-slate-400 tw-text-[9px] tw-font-bold tw-uppercase tw-tracking-wide">KYC Pending</div>
          </div>
          <div class="tw-bg-slate-900/60 tw-backdrop-blur-xl tw-border tw-border-white/5 tw-rounded-2xl tw-p-4 tw-text-center hover:tw-bg-slate-800/80 tw-transition-colors">
              <div class="tw-text-blue-400 tw-font-black tw-text-2xl tw-mb-1">{{ summary.kyc_verified }}</div>
              <div class="tw-text-slate-400 tw-text-[9px] tw-font-bold tw-uppercase tw-tracking-wide">KYC Verified</div>
          </div>
          <div class="tw-bg-slate-900/60 tw-backdrop-blur-xl tw-border tw-border-white/5 tw-rounded-2xl tw-p-4 tw-text-center hover:tw-bg-slate-800/80 tw-transition-colors">
              <div class="tw-text-amber-400 tw-font-black tw-text-2xl tw-mb-1">{{ summary.pending_deposits }}</div>
              <div class="tw-text-slate-400 tw-text-[9px] tw-font-bold tw-uppercase tw-tracking-wide">Deposit Pending</div>
          </div>
          <div class="tw-bg-slate-900/60 tw-backdrop-blur-xl tw-border tw-border-white/5 tw-rounded-2xl tw-p-4 tw-text-center hover:tw-bg-slate-800/80 tw-transition-colors">
              <div class="tw-text-amber-400 tw-font-black tw-text-2xl tw-mb-1">{{ summary.pending_withdrawals }}</div>
              <div class="tw-text-slate-400 tw-text-[9px] tw-font-bold tw-uppercase tw-tracking-wide">Withdraw Pending</div>
          </div>
        </div>



        <!-- Today's Quick Highlights -->
        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-4 tw-mb-6">
          <div class="tw-bg-indigo-900/20 tw-backdrop-blur-xl tw-border tw-border-indigo-500/20 tw-rounded-2xl tw-p-5 tw-flex tw-items-center tw-gap-5">
            <div class="tw-w-14 tw-h-14 tw-rounded-xl tw-bg-indigo-500/20 tw-text-indigo-400 tw-flex tw-items-center tw-justify-center tw-text-2xl">
              <i class="fas fa-user-plus"></i>
            </div>
            <div>
              <h4 class="tw-text-white tw-font-black tw-text-2xl tw-mb-0">{{ summary.new_users_today }}</h4>
              <p class="tw-text-indigo-300/70 tw-text-xs tw-font-bold tw-uppercase tw-tracking-widest tw-mb-0">New Users Today</p>
            </div>
          </div>
          <div class="tw-bg-emerald-900/20 tw-backdrop-blur-xl tw-border tw-border-emerald-500/20 tw-rounded-2xl tw-p-5 tw-flex tw-items-center tw-gap-5">
            <div class="tw-w-14 tw-h-14 tw-rounded-xl tw-bg-emerald-500/20 tw-text-emerald-400 tw-flex tw-items-center tw-justify-center tw-text-2xl">
              <i class="fas fa-rupee-sign"></i>
            </div>
            <div>
              <h4 class="tw-text-white tw-font-black tw-text-2xl tw-mb-0">₹{{ fmt(summary.deposits_today) }}</h4>
              <p class="tw-text-emerald-300/70 tw-text-xs tw-font-bold tw-uppercase tw-tracking-widest tw-mb-0">Deposits Today</p>
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


      </template>
    </div>
  </MasterAdminLayout>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import MasterAdminLayout from '../../components/master_admin/MasterAdminLayout.vue'
import api from '../../services/api'

export default {
  name: 'MasterAdminDashboard',
  components: { MasterAdminLayout },
  setup() {
    const loading = ref(false)
    const range = ref(30)
    const ranges = [
      { val: 7,  label: '7 Days'  },
      { val: 14, label: '14 Days' },
      { val: 30, label: '30 Days' },
      { val: 60, label: '60 Days' },
      { val: 90, label: '90 Days' },
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


    onMounted(() => {
      fetchReports()
    })

    return {
      loading, range, ranges,
      summary, userGrowth, revenueChart, withdrawChart,
      fmt, fetchReports,
      userGrowthBars, revBars,
      userActivePct, kycVerifiedPct
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

</style>
