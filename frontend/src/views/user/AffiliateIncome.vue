<template>
  <DashboardLayout page-title="Affiliate Income">
    <div class="row">
      <div class="col-12">
        <div class="card custom--card mb-4">
          <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Affiliate Income Dashboard</h5>
          </div>
          <div class="card-body">
            <div class="row gy-4">
              <div class="col-xl-3 col-md-6">
                <div class="dashboard-widget">
                  <div class="dashboard-widget__icon flex-center bg-primary">
                    <i class="fas fa-calendar-day"></i>
                  </div>
                  <div class="dashboard-widget__content">
                    <h3 class="dashboard-widget__number">{{ currencySymbol }}{{ formatAmount(income.today) }}</h3>
                    <span class="dashboard-widget__text">Today Income</span>
                  </div>
                </div>
              </div>
              <div class="col-xl-3 col-md-6">
                <div class="dashboard-widget">
                  <div class="dashboard-widget__icon flex-center bg-info">
                    <i class="fas fa-calendar-week"></i>
                  </div>
                  <div class="dashboard-widget__content">
                    <h3 class="dashboard-widget__number">{{ currencySymbol }}{{ formatAmount(income.this_week) }}</h3>
                    <span class="dashboard-widget__text">This Week</span>
                  </div>
                </div>
              </div>
              <div class="col-xl-3 col-md-6">
                <div class="dashboard-widget">
                  <div class="dashboard-widget__icon flex-center bg-warning">
                    <i class="fas fa-calendar-alt"></i>
                  </div>
                  <div class="dashboard-widget__content">
                    <h3 class="dashboard-widget__number">{{ currencySymbol }}{{ formatAmount(income.this_month) }}</h3>
                    <span class="dashboard-widget__text">This Month</span>
                  </div>
                </div>
              </div>
              <div class="col-xl-3 col-md-6">
                <div class="dashboard-widget">
                  <div class="dashboard-widget__icon flex-center bg-success">
                    <i class="fas fa-coins"></i>
                  </div>
                  <div class="dashboard-widget__content">
                    <h3 class="dashboard-widget__number">{{ currencySymbol }}{{ formatAmount(income.total) }}</h3>
                    <span class="dashboard-widget__text">Total Income</span>
                  </div>
                </div>
              </div>
              <div class="col-xl-3 col-md-6" v-if="affiliateEarning > 0">
                <div class="dashboard-widget">
                  <div class="dashboard-widget__icon flex-center bg-warning">
                    <i class="fas fa-percentage"></i>
                  </div>
                  <div class="dashboard-widget__content">
                    <h3 class="dashboard-widget__number">{{ currencySymbol }}{{ formatAmount(affiliateEarning) }}</h3>
                    <span class="dashboard-widget__text">50% Affiliate Earning</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="card custom--card">
          <div class="card-header">
            <h5 class="mb-0">Income History</h5>
          </div>
          <div class="card-body">
            <table class="table table--responsive--lg">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Source</th>
                  <th>Description</th>
                  <th>Amount</th>
                </tr>
              </thead>
              <tbody>
                <template v-for="item in incomeHistory" :key="item?.id || Math.random()">
                  <tr v-if="item && item.id">
                  <td>{{ formatDateTime(item.created_at) }}</td>
                  <td>{{ item.source }}</td>
                  <td>{{ item.description }}</td>
                  <td class="text-success">{{ currencySymbol }}{{ formatAmount(item.amount) }}</td>
                  </tr>
                </template>
                <tr v-if="incomeHistory.length === 0">
                  <td colspan="4" class="text-center text-muted">No income history found</td>
                </tr>
              </tbody>
            </table>
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
  name: 'AffiliateIncome',
  components: {
    DashboardLayout
  },
  setup() {
    const income = ref({
      today: 0,
      this_week: 0,
      this_month: 0,
      total: 0
    })
    const incomeHistory = ref([])
    const affiliateEarning = ref(0)
    const currencySymbol = ref('₹')

    const formatAmount = (amount) => {
      if (!amount && amount !== 0) return '0.00'
      return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }

    const formatDateTime = (dateString) => {
      if (!dateString) return '-'
      return new Date(dateString).toLocaleString('en-IN')
    }

    const fetchAffiliateIncome = async () => {
      try {
        const response = await api.get('/affiliate-income')
        if (response.data.status === 'success' && response.data.data) {
          income.value = response.data.data.income || income.value
          affiliateEarning.value = response.data.data.affiliate_earning || 0
          incomeHistory.value = response.data.data.history || []
          currencySymbol.value = response.data.currency_symbol || '₹'
        }
      } catch (error) {
        console.error('Error loading affiliate income:', error)
      }
    }

    onMounted(() => {
      fetchAffiliateIncome()
    })

    return {
      income,
      affiliateEarning,
      incomeHistory,
      currencySymbol,
      formatAmount,
      formatDateTime
    }
  }
}
</script>
