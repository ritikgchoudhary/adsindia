<template>
  <DashboardLayout page-title="My Team / Referral">
    <div class="row">
      <div class="col-lg-8">
        <div class="card custom--card mb-4">
          <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-link me-2"></i>General Referral Link</h5>
          </div>
          <div class="card-body">
            <div class="input-group">
              <input type="text" :value="referralLink" class="form-control form--control" readonly :id="'referralLink'">
              <button class="btn btn--base" @click="copyReferralLink('referralLink')">
                <i class="fas fa-copy me-2"></i>Copy Link
              </button>
            </div>
            <p class="text-muted mt-3 mb-0">
              <i class="fas fa-info-circle me-2"></i>
              Share this link with your friends and earn commission on their activities.
            </p>
          </div>
        </div>

        <div class="card custom--card mb-4">
          <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-gift me-2"></i>Package-Specific Referral Links</h5>
          </div>
          <div class="card-body">
            <p class="text-muted mb-3">
              <i class="fas fa-info-circle me-2"></i>
              Use package-specific links to offer discounts. When someone registers using these links, they get discounted prices.
            </p>
            <template v-for="pkgLink in packageLinks" :key="pkgLink?.package_id || Math.random()">
              <div v-if="pkgLink && pkgLink.package_id" class="mb-3 p-3 border rounded">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                  <h6 class="mb-1">{{ pkgLink.package_name }}</h6>
                  <p class="mb-0 text-muted">
                    Original: {{ currencySymbol }}{{ formatAmount(pkgLink.original_price) }}
                    <span v-if="pkgLink.discount > 0" class="text-success">
                      → Discounted: {{ currencySymbol }}{{ formatAmount(pkgLink.discounted_price) }}
                      <span class="badge badge--success">Save {{ currencySymbol }}{{ formatAmount(pkgLink.discount) }}</span>
                    </span>
                    <span v-else class="text-muted">(No discount)</span>
                  </p>
                </div>
              </div>
              <div class="input-group">
                <input type="text" :value="pkgLink.link" class="form-control form--control" readonly :id="`pkgLink${pkgLink.package_id}`">
                <button class="btn btn--base" @click="copyReferralLink(`pkgLink${pkgLink.package_id}`)">
                  <i class="fas fa-copy me-2"></i>Copy
                </button>
              </div>
              </div>
            </template>
          </div>
        </div>

        <div class="card custom--card mb-4">
          <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-users me-2"></i>My Downline Team</h5>
          </div>
          <div class="card-body">
            <div class="row mb-3">
              <div class="col-md-4">
                <div class="stat-box text-center p-3 bg-primary text-white rounded">
                  <h3>{{ teamStats.total_members }}</h3>
                  <p class="mb-0">Total Members</p>
                </div>
              </div>
              <div class="col-md-4">
                <div class="stat-box text-center p-3 bg-success text-white rounded">
                  <h3>{{ teamStats.active_members }}</h3>
                  <p class="mb-0">Active Members</p>
                </div>
              </div>
              <div class="col-md-4">
                <div class="stat-box text-center p-3 bg-warning text-white rounded">
                  <h3>{{ currencySymbol }}{{ formatAmount(teamStats.total_earning) }}</h3>
                  <p class="mb-0">Team Earning</p>
                </div>
              </div>
            </div>

            <table class="table table--responsive--lg">
              <thead>
                <tr>
                  <th>Username</th>
                  <th>Email</th>
                  <th>Joined Date</th>
                  <th>Status</th>
                  <th>Earning</th>
                </tr>
              </thead>
              <tbody>
                <template v-for="member in downlineTeam" :key="member?.id || Math.random()">
                  <tr v-if="member && member.id">
                  <td>{{ member.username }}</td>
                  <td>{{ member.email }}</td>
                  <td>{{ formatDate(member.joined_at) }}</td>
                  <td>
                    <span :class="member.status === 'active' ? 'badge badge--success' : 'badge badge--warning'">
                      {{ member.status }}
                    </span>
                  </td>
                  <td>{{ currencySymbol }}{{ formatAmount(member.earning) }}</td>
                  </tr>
                </template>
                <tr v-if="downlineTeam.length === 0">
                  <td colspan="5" class="text-center text-muted">No team members yet</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="card custom--card">
          <div class="card-header">
            <h5 class="mb-0">Referral Earning</h5>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <p class="mb-1">Today Earning</p>
              <h4 class="text-success">{{ currencySymbol }}{{ formatAmount(referralEarning.today) }}</h4>
            </div>
            <div class="mb-3">
              <p class="mb-1">This Month</p>
              <h4 class="text-primary">{{ currencySymbol }}{{ formatAmount(referralEarning.this_month) }}</h4>
            </div>
            <div>
              <p class="mb-1">Total Earning</p>
              <h4 class="text-warning">{{ currencySymbol }}{{ formatAmount(referralEarning.total) }}</h4>
            </div>
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
  name: 'Referral',
  components: {
    DashboardLayout
  },
  setup() {
    const referralLink = ref('')
    const packageLinks = ref([])
    const downlineTeam = ref([])
    const teamStats = ref({
      total_members: 0,
      active_members: 0,
      total_earning: 0
    })
    const referralEarning = ref({
      today: 0,
      this_month: 0,
      total: 0
    })
    const currencySymbol = ref('₹')

    const formatAmount = (amount) => {
      if (!amount && amount !== 0) return '0.00'
      return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }

    const formatDate = (dateString) => {
      if (!dateString) return '-'
      return new Date(dateString).toLocaleDateString('en-IN')
    }

    const copyReferralLink = (inputId) => {
      const input = document.getElementById(inputId)
      input.select()
      document.execCommand('copy')
      if (window.iziToast) {
        window.iziToast.success({
          title: 'Success',
          message: 'Referral link copied to clipboard!',
          position: 'topRight'
        })
      }
    }

    const fetchReferralData = async () => {
      try {
        const response = await api.get('/referral')
        if (response.data.status === 'success' && response.data.data) {
          referralLink.value = response.data.data.referral_link || ''
          packageLinks.value = response.data.data.package_links || []
          downlineTeam.value = response.data.data.downline_team || []
          teamStats.value = response.data.data.team_stats || teamStats.value
          referralEarning.value = response.data.data.referral_earning || referralEarning.value
          currencySymbol.value = response.data.currency_symbol || '₹'
        }
      } catch (error) {
        console.error('Error loading referral data:', error)
      }
    }

    onMounted(() => {
      fetchReferralData()
    })

    return {
      referralLink,
      packageLinks,
      downlineTeam,
      teamStats,
      referralEarning,
      currencySymbol,
      formatAmount,
      formatDate,
      copyReferralLink
    }
  }
}
</script>
