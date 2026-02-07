<template>
  <DashboardLayout page-title="Dashboard">
    <div class="notice"></div>
    
    <!-- KYC Alerts -->
    <div v-if="user.kv === 0 && user.kyc_rejection_reason" class="alert alert--danger mb-4 shadow-sm" role="alert" style="border-left: 4px solid #dc3545; border-radius: 8px;">
      <div class="alert__icon">
        <i class="fa-solid fa-circle-exclamation"></i>
      </div>
      <div class="alert__content">
        <h4 class="alert__title">KYC Documents Rejected</h4>
        <p class="alert__desc">
          <a class="alert__link" data-bs-toggle="modal" href="#kycRejectionReason">Show Reason</a>
          {{ kycContent?.reject || '' }}
          <router-link class="alert__link" to="/user/kyc-form">Click Here to Re-submit Documents</router-link>.
          <router-link class="alert__link" to="/user/kyc-data">See KYC Data</router-link>
        </p>
      </div>
    </div>
    <div v-else-if="user.kv === 0" class="alert alert--info mb-4 shadow-sm" role="alert" style="border-left: 4px solid #0dcaf0; border-radius: 8px;">
      <div class="alert__icon">
        <i class="fas fa-user-check"></i>
      </div>
      <div class="alert__content">
        <h4 class="alert__title">KYC Verification required</h4>
        <p class="alert__desc">
          {{ kycContent?.required || '' }}
          <router-link class="alert__link" to="/user/kyc-form">Click Here to Submit Documents</router-link>
        </p>
      </div>
    </div>
    <div v-else-if="user.kv === 2" class="alert alert--warning mb-4 shadow-sm" role="alert" style="border-left: 4px solid #ffc107; border-radius: 8px;">
      <div class="alert__icon">
        <i class="fa-solid fa-spinner"></i>
      </div>
      <div class="alert__content">
        <h4 class="alert__title">KYC Verification pending</h4>
        <p class="alert__desc">
          {{ kycContent?.pending || '' }}
          <router-link class="alert__link" to="/user/kyc-data">See KYC Data</router-link>
        </p>
      </div>
    </div>

    <!-- Welcome Section -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="card custom--card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 15px !important;">
          <div class="card-body p-4 text-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
              <div>
                <h3 class="mb-2" style="font-weight: 600;">
                  <i class="fas fa-hand-sparkles me-2"></i>Welcome to AdsSkill India!
                </h3>
                <p class="mb-0 opacity-90">Start earning by watching ads and completing tasks</p>
              </div>
              <div class="mt-3 mt-md-0">
                <router-link to="/user/ads-work" class="btn btn-light btn-lg px-4" style="border-radius: 10px; font-weight: 600;">
                  <i class="fas fa-play-circle me-2"></i>Start Earning Now
                </router-link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Ads Income / Earning Dashboard Section -->
    <div class="row gy-4 mb-4">
      <div class="col-12">
        <div class="card custom--card border-0 shadow-sm" style="border-radius: 15px !important;">
          <div class="card-header bg-transparent border-0 pb-0" style="border-radius: 15px 15px 0 0 !important;">
            <h5 class="mb-0" style="font-weight: 600; color: #2d3748;">
              <i class="fas fa-chart-line me-2 text-primary"></i>Ads Income / Earning Dashboard
            </h5>
          </div>
          <div class="card-body pt-3">
            <div class="row gy-4">
              <div class="col-xl-3 col-md-6">
                <div class="dashboard-widget earning-card earning-card-today border-0 shadow-sm" style="border-radius: 12px !important; transition: all 0.3s ease !important; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important; border: none !important;" @mouseenter="$event.currentTarget.style.transform = 'translateY(-5px)'; $event.currentTarget.style.boxShadow = '0 10px 30px rgba(102, 126, 234, 0.4)'" @mouseleave="$event.currentTarget.style.transform = 'translateY(0)'; $event.currentTarget.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)'">
                  <div class="dashboard-widget__icon flex-center" style="background: rgba(255,255,255,0.25) !important; border-radius: 12px !important; width: 60px !important; height: 60px !important; margin: 0 !important;">
                    <i class="fas fa-calendar-day text-white" style="font-size: 24px !important;"></i>
                  </div>
                  <div class="dashboard-widget__content" style="color: white !important;">
                    <h3 class="dashboard-widget__number text-white" style="font-weight: 700 !important; font-size: 28px !important; color: white !important; margin-bottom: 8px !important;">{{ currencySymbol }}{{ formatAmount(earnings.today) }}</h3>
                    <span class="dashboard-widget__text text-white" style="font-size: 14px !important; color: rgba(255,255,255,0.9) !important; font-weight: 500 !important;">Today Earning</span>
                  </div>
                </div>
              </div>
              <div class="col-xl-3 col-md-6">
                <div class="dashboard-widget earning-card earning-card-week border-0 shadow-sm" style="border-radius: 12px !important; transition: all 0.3s ease !important; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%) !important; border: none !important;" @mouseenter="$event.currentTarget.style.transform = 'translateY(-5px)'; $event.currentTarget.style.boxShadow = '0 10px 30px rgba(240, 147, 251, 0.4)'" @mouseleave="$event.currentTarget.style.transform = 'translateY(0)'; $event.currentTarget.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)'">
                  <div class="dashboard-widget__icon flex-center" style="background: rgba(255,255,255,0.25) !important; border-radius: 12px !important; width: 60px !important; height: 60px !important; margin: 0 !important;">
                    <i class="fas fa-calendar-week text-white" style="font-size: 24px !important;"></i>
                  </div>
                  <div class="dashboard-widget__content" style="color: white !important;">
                    <h3 class="dashboard-widget__number text-white" style="font-weight: 700 !important; font-size: 28px !important; color: white !important; margin-bottom: 8px !important;">{{ currencySymbol }}{{ formatAmount(earnings.last7Days) }}</h3>
                    <span class="dashboard-widget__text text-white" style="font-size: 14px !important; color: rgba(255,255,255,0.9) !important; font-weight: 500 !important;">Last 7 Days Earning</span>
                  </div>
                </div>
              </div>
              <div class="col-xl-3 col-md-6">
                <div class="dashboard-widget earning-card earning-card-month border-0 shadow-sm" style="border-radius: 12px !important; transition: all 0.3s ease !important; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%) !important; border: none !important;" @mouseenter="$event.currentTarget.style.transform = 'translateY(-5px)'; $event.currentTarget.style.boxShadow = '0 10px 30px rgba(79, 172, 254, 0.4)'" @mouseleave="$event.currentTarget.style.transform = 'translateY(0)'; $event.currentTarget.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)'">
                  <div class="dashboard-widget__icon flex-center" style="background: rgba(255,255,255,0.25) !important; border-radius: 12px !important; width: 60px !important; height: 60px !important; margin: 0 !important;">
                    <i class="fas fa-calendar-alt text-white" style="font-size: 24px !important;"></i>
                  </div>
                  <div class="dashboard-widget__content" style="color: white !important;">
                    <h3 class="dashboard-widget__number text-white" style="font-weight: 700 !important; font-size: 28px !important; color: white !important; margin-bottom: 8px !important;">{{ currencySymbol }}{{ formatAmount(earnings.last30Days) }}</h3>
                    <span class="dashboard-widget__text text-white" style="font-size: 14px !important; color: rgba(255,255,255,0.9) !important; font-weight: 500 !important;">Last 30 Days Earning</span>
                  </div>
                </div>
              </div>
              <div class="col-xl-3 col-md-6">
                <div class="dashboard-widget earning-card earning-card-total border-0 shadow-sm" style="border-radius: 12px !important; transition: all 0.3s ease !important; background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%) !important; border: none !important;" @mouseenter="$event.currentTarget.style.transform = 'translateY(-5px)'; $event.currentTarget.style.boxShadow = '0 10px 30px rgba(67, 233, 123, 0.4)'" @mouseleave="$event.currentTarget.style.transform = 'translateY(0)'; $event.currentTarget.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)'">
                  <div class="dashboard-widget__icon flex-center" style="background: rgba(255,255,255,0.25) !important; border-radius: 12px !important; width: 60px !important; height: 60px !important; margin: 0 !important;">
                    <i class="fas fa-coins text-white" style="font-size: 24px !important;"></i>
                  </div>
                  <div class="dashboard-widget__content" style="color: white !important;">
                    <h3 class="dashboard-widget__number text-white" style="font-weight: 700 !important; font-size: 28px !important; color: white !important; margin-bottom: 8px !important;">{{ currencySymbol }}{{ formatAmount(earnings.total) }}</h3>
                    <span class="dashboard-widget__text text-white" style="font-size: 14px !important; color: rgba(255,255,255,0.9) !important; font-weight: 500 !important;">Total Earning</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row gy-4 justify-content-center">
      <div class="col-xxl-9 col-lg-12">
        <div class="row gy-4 justify-content-center">
          <!-- Balance Widget -->
          <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="dashboard-widget border-0 shadow-sm" style="border-radius: 12px; transition: all 0.3s ease; background: #fff;" @mouseenter="$event.currentTarget.style.transform = 'translateY(-5px)'; $event.currentTarget.style.boxShadow = '0 10px 25px rgba(0,0,0,0.1)'" @mouseleave="$event.currentTarget.style.transform = 'translateY(0)'; $event.currentTarget.style.boxShadow = '0 2px 10px rgba(0,0,0,0.05)'">
              <div class="dashboard-widget__icon flex-center" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; width: 60px; height: 60px;">
                <i class="las la-coins text-white" style="font-size: 24px;"></i>
              </div>
              <div class="dashboard-widget__content">
                <h3 class="dashboard-widget__number" style="font-weight: 700; font-size: 24px; color: #2d3748;">
                  {{ currencySymbol }}{{ formatAmount(widget.balance) }}
                </h3>
                <span class="dashboard-widget__text" style="color: #718096; font-size: 14px;"> Balance </span>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="dashboard-widget border-0 shadow-sm" style="border-radius: 12px; transition: all 0.3s ease; background: #fff;" @mouseenter="$event.currentTarget.style.transform = 'translateY(-5px)'; $event.currentTarget.style.boxShadow = '0 10px 25px rgba(0,0,0,0.1)'" @mouseleave="$event.currentTarget.style.transform = 'translateY(0)'; $event.currentTarget.style.boxShadow = '0 2px 10px rgba(0,0,0,0.05)'">
              <div class="dashboard-widget__icon flex-center" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 12px; width: 60px; height: 60px;">
                <i class="fas fa-money-bill-wave-alt text-white" style="font-size: 24px;"></i>
              </div>
              <div class="dashboard-widget__content">
                <h3 class="dashboard-widget__number" style="font-weight: 700; font-size: 24px; color: #2d3748;"> {{ currencySymbol }}{{ formatAmount(widget.total_earning) }} </h3>
                <span class="dashboard-widget__text" style="color: #718096; font-size: 14px;"> Affiliate Income </span>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="dashboard-widget border-0 shadow-sm" style="border-radius: 12px; transition: all 0.3s ease; background: #fff;" @mouseenter="$event.currentTarget.style.transform = 'translateY(-5px)'; $event.currentTarget.style.boxShadow = '0 10px 25px rgba(0,0,0,0.1)'" @mouseleave="$event.currentTarget.style.transform = 'translateY(0)'; $event.currentTarget.style.boxShadow = '0 2px 10px rgba(0,0,0,0.05)'">
              <div class="dashboard-widget__icon flex-center" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); border-radius: 12px; width: 60px; height: 60px;">
                <i class="fas fa-video text-white" style="font-size: 24px;"></i>
              </div>
              <div class="dashboard-widget__content">
                <h3 class="dashboard-widget__number" style="font-weight: 700; font-size: 24px; color: #2d3748;"> {{ currencySymbol }}{{ formatAmount(widget.ads_income || 0) }} </h3>
                <span class="dashboard-widget__text" style="color: #718096; font-size: 14px;"> Ads Income </span>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="dashboard-widget border-0 shadow-sm" style="border-radius: 12px; transition: all 0.3s ease; background: #fff;" @mouseenter="$event.currentTarget.style.transform = 'translateY(-5px)'; $event.currentTarget.style.boxShadow = '0 10px 25px rgba(0,0,0,0.1)'" @mouseleave="$event.currentTarget.style.transform = 'translateY(0)'; $event.currentTarget.style.boxShadow = '0 2px 10px rgba(0,0,0,0.05)'">
              <div class="dashboard-widget__icon flex-center" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); border-radius: 12px; width: 60px; height: 60px;">
                <i class="fas fa-hand-holding-usd text-white" style="font-size: 24px;"></i>
              </div>
              <div class="dashboard-widget__content">
                <h3 class="dashboard-widget__number" style="font-weight: 700; font-size: 24px; color: #2d3748;"> {{ currencySymbol }}{{ formatAmount(widget.total_withdraw) }} </h3>
                <span class="dashboard-widget__text" style="color: #718096; font-size: 14px;"> Withdrawan </span>
              </div>
            </div>
          </div>

          <div class="col-12">
            <div class="card custom--card border-0 shadow-sm" style="border-radius: 15px !important;">
              <div class="card-header bg-transparent border-0 pb-0" style="border-radius: 15px 15px 0 0 !important;">
                <div class="d-flex justify-content-between align-items-center">
                  <h5 class="mb-0" style="font-weight: 600; color: #2d3748;">
                    <i class="fas fa-history me-2 text-primary"></i>Latest Transactions
                  </h5>
                  <router-link to="/user/transactions" class="btn btn-sm btn-outline-primary" style="border-radius: 8px;">
                    View All <i class="fas fa-arrow-right ms-1"></i>
                  </router-link>
                </div>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table--responsive--lg mb-0" style="border-collapse: separate; border-spacing: 0;">
                    <thead>
                      <tr style="background: #f7fafc; border-radius: 10px;">
                        <th style="padding: 15px; font-weight: 600; color: #4a5568; border: none;">Trx</th>
                        <th style="padding: 15px; font-weight: 600; color: #4a5568; border: none;">Transacted</th>
                        <th style="padding: 15px; font-weight: 600; color: #4a5568; border: none;">Amount</th>
                        <th style="padding: 15px; font-weight: 600; color: #4a5568; border: none;">Post Balance</th>
                        <th style="padding: 15px; font-weight: 600; color: #4a5568; border: none;">Detail</th>
                      </tr>
                    </thead>
                    <tbody>
                      <template v-for="trx in transactions" :key="trx?.id || Math.random()">
                        <tr v-if="trx && trx.id" style="border-bottom: 1px solid #e2e8f0; transition: all 0.2s ease;" @mouseenter="$event.currentTarget.style.backgroundColor = '#f7fafc'" @mouseleave="$event.currentTarget.style.backgroundColor = 'transparent'">
                          <td style="padding: 15px; border: none;">
                            <div><strong style="color: #2d3748; font-family: 'Courier New', monospace;">{{ trx.trx }}</strong></div>
                          </td>
                          <td style="padding: 15px; border: none;">
                            <div>
                              <span style="color: #2d3748; font-weight: 500;">{{ formatDateTime(trx.created_at) }}</span><br>
                              <small class="text-muted" style="font-size: 12px;">{{ trx.created_at_human }}</small>
                            </div>
                          </td>
                          <td style="padding: 15px; border: none;">
                            <div>
                              <span class="fw-bold" :class="trx.trx_type === '+' ? 'text-success' : 'text-danger'" style="font-size: 16px;">
                                {{ trx.trx_type }} {{ currencySymbol }}{{ formatAmount(trx.amount) }}
                              </span>
                            </div>
                          </td>
                          <td style="padding: 15px; border: none;">
                            <div style="color: #4a5568; font-weight: 500;">{{ currencySymbol }}{{ formatAmount(trx.post_balance) }}</div>
                          </td>
                          <td style="padding: 15px; border: none;">
                            <div style="color: #718096;">{{ trx.details }}</div>
                          </td>
                        </tr>
                      </template>
                      <tr v-if="transactions.length === 0">
                        <td colspan="5" class="text-center text-muted py-5" style="border: none;">
                          <i class="fas fa-inbox fa-3x mb-3 opacity-50"></i>
                          <p class="mb-0">No transactions found</p>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

        <div class="col-xxl-3 col-lg-12 ps-xxl-4">
          <div class="dashboard-sidebar">
            <div class="dashboard-sidebar__item">
              <div class="card custom--card border-0 shadow-sm" style="border-radius: 15px !important;">
                <div class="card-header bg-transparent border-0 pb-0" style="border-radius: 15px 15px 0 0 !important;">
                  <h5 class="mb-0" style="font-weight: 600; color: #2d3748;">
                    <i class="fas fa-lightbulb me-2 text-warning"></i>Suggest for you
                  </h5>
                </div>
                <div class="card-body pt-3">
                  <template v-for="campaign in campaigns" :key="campaign?.id || Math.random()">
                    <div v-if="campaign && campaign.id" class="latest-item mb-3 p-3 border rounded" style="border-radius: 10px !important; transition: all 0.3s ease; border-color: #e2e8f0 !important;" @mouseenter="$event.currentTarget.style.backgroundColor = '#f7fafc'; $event.currentTarget.style.transform = 'translateX(5px)'" @mouseleave="$event.currentTarget.style.backgroundColor = 'transparent'; $event.currentTarget.style.transform = 'translateX(0)'">
                      <div class="latest-item__thumb mb-2">
                        <router-link :to="`/campaign/${campaign.slug}`">
                          <img :src="campaign.image" class="fit-image rounded" alt="img" style="border-radius: 8px; width: 100%; height: 120px; object-fit: cover;">
                        </router-link>
                      </div>
                      <div class="latest-item__content">
                        <h6 class="latest-item__title mb-2" style="font-weight: 600; color: #2d3748;">
                          <router-link :to="`/campaign/${campaign.slug}`" style="color: #2d3748; text-decoration: none;">
                            {{ campaign.title }}
                          </router-link>
                        </h6>
                        <router-link :to="`/campaign/${campaign.slug}`" class="btn btn-sm btn-primary w-100" style="border-radius: 8px; font-weight: 600;">
                          <i class="fas fa-coins me-1"></i>{{ currencySymbol }}{{ formatAmount(campaign.payout_per_conversion) }}
                        </router-link>
                      </div>
                    </div>
                  </template>
                  <div v-if="campaigns.length === 0" class="text-center py-5">
                    <i class="fas fa-inbox fa-3x mb-3 opacity-50" style="color: #cbd5e0;"></i>
                    <p class="text-muted mb-0">No campaigns found</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
    </div>

    <div v-if="user.kv === 0 && user.kyc_rejection_reason" class="modal fade custom--modal" id="kycRejectionReason">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">KYC Document Rejection Reason</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>{{ user.kyc_rejection_reason }}</p>
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
    const campaigns = ref([])
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
        if (response.status === 'success' && response.data) {
          widget.value = response.data.widget || {}
          earnings.value = response.data.earnings || {
            today: 0,
            last7Days: 0,
            last30Days: 0,
            total: widget.value.total_earning || 0
          }
          transactions.value = response.data.transactions || []
          campaigns.value = response.data.campaigns || []
          user.value = response.data.user || {}
          kycContent.value = response.data.kyc_content
          currencySymbol.value = response.data.currency_symbol || '$'
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
    })

    return {
      widget,
      earnings,
      transactions,
      campaigns,
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
/* Dashboard UI Improvements */
.dashboard-widget {
  padding: 24px 20px !important;
  display: flex !important;
  align-items: center !important;
  gap: 20px !important;
  min-height: 120px;
}

.dashboard-widget__icon {
  flex-shrink: 0 !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
}

.dashboard-widget__content {
  flex: 1;
}

.dashboard-widget__number {
  margin-bottom: 8px !important;
  line-height: 1.2;
}

.dashboard-widget__text {
  font-size: 14px !important;
  opacity: 0.9;
}

/* Earning Cards - Gradient Backgrounds */
.earning-card-today {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
  color: white !important;
}

.earning-card-week {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%) !important;
  color: white !important;
}

.earning-card-month {
  background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%) !important;
  color: white !important;
}

.earning-card-total {
  background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%) !important;
  color: white !important;
}

/* Balance Widgets - White Cards */
.balance-widget {
  background: #ffffff !important;
  border: none !important;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05) !important;
}

.balance-widget:hover {
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1) !important;
  transform: translateY(-5px) !important;
}

/* Card Improvements */
.card.custom--card {
  border: none !important;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05) !important;
  transition: all 0.3s ease !important;
}

.card.custom--card:hover {
  box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1) !important;
}

/* Table Improvements */
.table thead tr {
  background: #f7fafc !important;
}

.table tbody tr {
  transition: all 0.2s ease !important;
}

.table tbody tr:hover {
  background-color: #f7fafc !important;
}

/* Campaign Cards */
.latest-item {
  transition: all 0.3s ease !important;
  border-radius: 10px !important;
}

.latest-item:hover {
  background-color: #f7fafc !important;
  transform: translateX(5px) !important;
}

/* Welcome Banner */
.welcome-banner {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
  border-radius: 15px !important;
  color: white !important;
}

/* Responsive */
@media (max-width: 768px) {
  .dashboard-widget {
    padding: 16px !important;
    min-height: 100px;
  }
  
  .dashboard-widget__number {
    font-size: 20px !important;
  }
}
</style>

