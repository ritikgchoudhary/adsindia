<template>
  <DashboardLayout page-title="My Team / Referral" :dark-theme="true">
    <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-12 tw-gap-8">
      
      <!-- Main Content -->
      <div class="lg:tw-col-span-8 tw-flex tw-flex-col tw-gap-8">
        
        <!-- General Referral Link -->
        <div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-slate-200 tw-overflow-hidden">
          <div class="tw-bg-slate-50 tw-p-5 tw-border-b tw-border-slate-200">
            <h5 class="tw-text-slate-900 tw-font-bold tw-text-lg tw-m-0 tw-flex tw-items-center">
              <i class="fas fa-link tw-mr-2 tw-text-indigo-600"></i>General Referral Link
            </h5>
          </div>
          <div class="tw-p-6">
            <div class="tw-flex tw-flex-col md:tw-flex-row tw-gap-3 tw-mb-4">
              <input 
                type="text" 
                :value="referralLink" 
                class="tw-flex-1 tw-px-4 tw-py-3 tw-bg-slate-50 tw-border tw-border-slate-300 tw-rounded-xl tw-text-slate-600 tw-font-mono tw-text-sm focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-indigo-500/20"
                readonly 
                :id="'referralLink'"
              >
              <button 
                type="button" 
                class="tw-px-6 tw-py-3 tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-font-bold tw-rounded-xl tw-transition-all tw-flex tw-items-center tw-justify-center tw-gap-2 tw-shadow-lg tw-shadow-indigo-500/20 tw-border-0 tw-cursor-pointer"
                @click="copyReferralLink('referralLink')"
              >
                <i class="fas fa-copy"></i> Copy Link
              </button>
            </div>
            <p v-if="referralCode" class="tw-text-slate-600 tw-text-sm tw-mb-2 tw-flex tw-items-center tw-gap-2">
              <span class="tw-font-medium">Your referral code:</span>
              <code class="tw-bg-slate-100 tw-px-2 tw-py-1 tw-rounded tw-font-mono tw-font-bold tw-text-indigo-600">{{ referralCode }}</code>
            </p>
            <p class="tw-text-slate-500 tw-text-sm tw-m-0 tw-flex tw-items-start tw-gap-2">
              <i class="fas fa-info-circle tw-mt-0.5 tw-text-indigo-500"></i>
              Share this link with your friends and earn commission on their activities.
            </p>
          </div>
        </div>

        <!-- Package-Specific Links (Full List) -->
        <div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-slate-200 tw-overflow-hidden">
          <div class="tw-bg-slate-50 tw-p-5 tw-border-b tw-border-slate-200">
            <h5 class="tw-text-slate-900 tw-font-bold tw-text-lg tw-m-0 tw-flex tw-items-center">
              <i class="fas fa-gift tw-mr-2 tw-text-indigo-600"></i>Package-Specific Referral Links (All Plans)
            </h5>
          </div>
          <div class="tw-p-6">
            <p class="tw-text-slate-500 tw-text-sm tw-mb-6 tw-flex tw-items-start tw-gap-2">
              <i class="fas fa-info-circle tw-mt-0.5 tw-text-indigo-500"></i>
              These are your default package links. Copy and share any link.
            </p>

            <div class="tw-flex tw-flex-col tw-gap-4">
              <template v-for="pkgLink in packageLinks" :key="pkgLink?.package_id || Math.random()">
                <div v-if="pkgLink && pkgLink.package_id" class="tw-p-4 tw-rounded-xl tw-bg-slate-50 tw-border tw-border-slate-200 hover:tw-border-indigo-200 hover:tw-bg-white hover:tw-shadow-md tw-transition-all">
                  <div class="tw-flex tw-flex-col md:tw-flex-row md:tw-items-center tw-justify-between tw-gap-2 tw-mb-3">
                    <h6 class="tw-text-slate-900 tw-font-bold tw-text-base tw-m-0">{{ pkgLink.package_name }}</h6>
                    <div class="tw-text-sm tw-text-slate-600">
                      Package Price: <span class="tw-font-medium">{{ currencySymbol }}{{ formatAmount(pkgLink.original_price) }}</span>
                    </div>
                  </div>
                  <div class="tw-flex tw-gap-2">
                    <input
                      type="text"
                      :value="pkgLink.link"
                      class="tw-flex-1 tw-px-3 tw-py-2 tw-bg-white tw-border tw-border-slate-300 tw-rounded-lg tw-text-slate-600 tw-font-mono tw-text-xs focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-indigo-500/20"
                      readonly
                      :id="`pkgLink${pkgLink.package_id}`"
                    >
                    <button
                      type="button"
                      class="tw-px-4 tw-py-2 tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-font-bold tw-text-xs tw-rounded-lg tw-transition-all tw-border-0 tw-cursor-pointer"
                      @click="copyReferralLink(`pkgLink${pkgLink.package_id}`)"
                    >
                      Copy
                    </button>
                  </div>
                </div>
              </template>

              <div v-if="packageLinks.length === 0" class="tw-text-center tw-py-8 tw-text-slate-400">
                <i class="fas fa-gift tw-text-3xl tw-mb-2"></i>
                <p>No package links available</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Special Discount Links (Global / Admin) -->
        <div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-slate-200 tw-overflow-hidden">
          <div class="tw-bg-slate-50 tw-p-5 tw-border-b tw-border-slate-200">
            <h5 class="tw-text-slate-900 tw-font-bold tw-text-lg tw-m-0 tw-flex tw-items-center">
              <i class="fas fa-bolt tw-mr-2 tw-text-indigo-600"></i>Special Discount Links
            </h5>
          </div>
          <div class="tw-p-6">
            <p class="tw-text-slate-500 tw-text-sm tw-mb-6 tw-flex tw-items-start tw-gap-2">
              <i class="fas fa-info-circle tw-mt-0.5 tw-text-indigo-500"></i>
              These links include an admin-set discount and locked plan. Copy and share any link.
            </p>

            <div class="tw-flex tw-flex-col tw-gap-4">
              <template v-for="sLink in globalSpecialLinks" :key="sLink?.id || Math.random()">
                <div v-if="sLink && sLink.id" class="tw-p-4 tw-rounded-xl tw-bg-slate-50 tw-border tw-border-slate-200 hover:tw-border-indigo-200 hover:tw-bg-white hover:tw-shadow-md tw-transition-all">
                  <div class="tw-flex tw-flex-col md:tw-flex-row md:tw-items-center tw-justify-between tw-gap-2 tw-mb-3">
                    <h6 class="tw-text-slate-900 tw-font-bold tw-text-base tw-m-0">{{ sLink.package_name }}</h6>
                    <div class="tw-text-sm tw-text-slate-600 tw-flex tw-flex-wrap tw-gap-x-4 tw-gap-y-1">
                      <span>Price: <span class="tw-font-medium">{{ currencySymbol }}{{ formatAmount(sLink.original_price) }}</span></span>
                      <span>Discount: <span class="tw-font-medium">{{ currencySymbol }}{{ formatAmount(sLink.discount) }}</span></span>
                      <span>Final: <span class="tw-font-bold tw-text-emerald-600">{{ currencySymbol }}{{ formatAmount(sLink.final_price) }}</span></span>
                    </div>
                  </div>
                  <div class="tw-flex tw-gap-2">
                    <input
                      type="text"
                      :value="sLink.link"
                      class="tw-flex-1 tw-px-3 tw-py-2 tw-bg-white tw-border tw-border-slate-300 tw-rounded-lg tw-text-slate-600 tw-font-mono tw-text-xs focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-indigo-500/20"
                      readonly
                      :id="`specialLink${sLink.id}`"
                    >
                    <button
                      type="button"
                      class="tw-px-4 tw-py-2 tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-font-bold tw-text-xs tw-rounded-lg tw-transition-all tw-border-0 tw-cursor-pointer"
                      @click="copyReferralLink(`specialLink${sLink.id}`)"
                    >
                      Copy
                    </button>
                  </div>
                </div>
              </template>

              <div v-if="globalSpecialLinks.length === 0" class="tw-text-center tw-py-8 tw-text-slate-400">
                <i class="fas fa-bolt tw-text-3xl tw-mb-2"></i>
                <p>No special links available right now</p>
              </div>
            </div>
          </div>
        </div>

        <!-- My Downline Team -->
        <div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-slate-200 tw-overflow-hidden">
          <div class="tw-bg-slate-50 tw-p-5 tw-border-b tw-border-slate-200">
            <h5 class="tw-text-slate-900 tw-font-bold tw-text-lg tw-m-0 tw-flex tw-items-center">
              <i class="fas fa-users tw-mr-2 tw-text-indigo-600"></i>My Downline Team
            </h5>
          </div>
          <div class="tw-p-6">
            <!-- Stats Grid -->
            <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-4 tw-mb-8">
              <div class="tw-bg-gradient-to-br tw-from-blue-500 tw-to-blue-600 tw-rounded-2xl tw-p-5 tw-text-white tw-text-center tw-shadow-lg tw-shadow-blue-500/20">
                <h3 class="tw-text-3xl tw-font-extrabold tw-mb-1">{{ teamStats.total_members }}</h3>
                <p class="tw-text-blue-100 tw-text-xs tw-font-bold tw-uppercase tw-tracking-wide tw-m-0">Total Members</p>
              </div>
              <div class="tw-bg-gradient-to-br tw-from-emerald-500 tw-to-emerald-600 tw-rounded-2xl tw-p-5 tw-text-white tw-text-center tw-shadow-lg tw-shadow-emerald-500/20">
                <h3 class="tw-text-3xl tw-font-extrabold tw-mb-1">{{ teamStats.active_members }}</h3>
                <p class="tw-text-emerald-100 tw-text-xs tw-font-bold tw-uppercase tw-tracking-wide tw-m-0">Active Members</p>
              </div>
              <div class="tw-bg-gradient-to-br tw-from-amber-500 tw-to-amber-600 tw-rounded-2xl tw-p-5 tw-text-white tw-text-center tw-shadow-lg tw-shadow-amber-500/20">
                <h3 class="tw-text-3xl tw-font-extrabold tw-mb-1">{{ currencySymbol }}{{ formatAmount(teamStats.total_earning) }}</h3>
                <p class="tw-text-amber-100 tw-text-xs tw-font-bold tw-uppercase tw-tracking-wide tw-m-0">Team Earning</p>
              </div>
            </div>

            <!-- Table -->
            <div class="tw-overflow-x-auto">
              <table class="tw-w-full tw-text-sm tw-border-collapse">
                <thead>
                  <tr class="tw-bg-slate-50 tw-border-b tw-border-slate-200">
                    <th class="tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-bold tw-uppercase tw-text-slate-500 tw-tracking-wider">Name</th>
                    <th class="tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-bold tw-uppercase tw-text-slate-500 tw-tracking-wider">Email</th>
                    <th class="tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-bold tw-uppercase tw-text-slate-500 tw-tracking-wider">Joined Date</th>
                    <th class="tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-bold tw-uppercase tw-text-slate-500 tw-tracking-wider">Status</th>
                    <th class="tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-bold tw-uppercase tw-text-slate-500 tw-tracking-wider">Earning</th>
                  </tr>
                </thead>
                <tbody class="tw-divide-y tw-divide-slate-100">
                  <tr v-for="member in downlineTeam" :key="member?.id || Math.random()" v-show="member && member.id" class="hover:tw-bg-slate-50/50">
                    <td class="tw-px-4 tw-py-3 tw-font-bold tw-text-slate-700">
                      {{ (String((member.firstname || '') + ' ' + (member.lastname || '')).trim()) || member.username }}
                    </td>
                    <td class="tw-px-4 tw-py-3 tw-text-slate-600">{{ member.email }}</td>
                    <td class="tw-px-4 tw-py-3 tw-text-slate-600">{{ formatDate(member.joined_at) }}</td>
                    <td class="tw-px-4 tw-py-3">
                      <span 
                        class="tw-px-2 tw-py-1 tw-rounded-lg tw-text-xs tw-font-bold tw-uppercase"
                        :class="member.status === 'active' ? 'tw-bg-emerald-100 tw-text-emerald-700' : 'tw-bg-amber-100 tw-text-amber-700'"
                      >
                        {{ member.status }}
                      </span>
                    </td>
                    <td class="tw-px-4 tw-py-3 tw-font-bold tw-text-slate-800">{{ currencySymbol }}{{ formatAmount(member.earning) }}</td>
                  </tr>
                  <tr v-if="downlineTeam.length === 0">
                    <td colspan="5" class="tw-px-4 tw-py-10 tw-text-center">
                      <div class="tw-w-16 tw-h-16 tw-bg-slate-50 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-4">
                        <i class="fas fa-users-slash tw-text-3xl tw-text-slate-300"></i>
                      </div>
                      <p class="tw-text-slate-500 tw-font-medium">No team members yet. Share your referral link to grow your team!</p>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>

      <!-- Right Sidebar: Referral Earning -->
      <div class="lg:tw-col-span-4">
        <!-- Affiliate Actions -->
        <div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-slate-200 tw-overflow-hidden tw-mb-6">
          <div class="tw-bg-slate-50 tw-p-5 tw-border-b tw-border-slate-200">
            <h5 class="tw-text-slate-900 tw-font-bold tw-text-lg tw-m-0 tw-flex tw-items-center">
              <i class="fas fa-wallet tw-mr-2 tw-text-emerald-600"></i>Affiliate Wallet
            </h5>
          </div>
          <div class="tw-p-6 tw-flex tw-flex-col tw-gap-3">
            <router-link
              to="/user/affiliate-income"
              class="tw-w-full tw-py-3 tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-font-bold tw-rounded-xl tw-text-center tw-no-underline tw-transition-colors"
            >
              View Affiliate Dashboard
            </router-link>
            <router-link
              to="/user/affiliate-withdraw"
              class="tw-w-full tw-py-3 tw-bg-emerald-600 hover:tw-bg-emerald-700 tw-text-white tw-font-bold tw-rounded-xl tw-text-center tw-no-underline tw-transition-colors"
            >
              Withdraw Affiliate Income
            </router-link>
            <router-link
              to="/user/account-kyc"
              class="tw-w-full tw-py-3 tw-bg-slate-900 hover:tw-bg-slate-800 tw-text-white tw-font-bold tw-rounded-xl tw-text-center tw-no-underline tw-transition-colors"
            >
              KYC (Required)
            </router-link>
          </div>
        </div>

        <div class="tw-bg-white tw-rounded-2xl tw-shadow-xl tw-border-2 tw-border-indigo-100 tw-overflow-hidden tw-sticky tw-top-4">
          <div class="tw-bg-gradient-to-br tw-from-indigo-600 tw-to-violet-600 tw-p-6 tw-text-white tw-flex tw-items-center tw-justify-between">
            <h5 class="tw-font-bold tw-text-lg tw-m-0">Referral Earning</h5>
            <span class="tw-text-indigo-300 tw-text-xl tw-font-bold">₹</span>
          </div>
          <div class="tw-p-6 tw-flex tw-flex-col tw-gap-0">
            
            <div class="tw-py-4 tw-border-b tw-border-slate-100 tw-group hover:tw-bg-slate-50/50 tw-transition-colors">
              <p class="tw-text-xs tw-font-bold tw-uppercase tw-text-slate-400 tw-mb-1">Today Earning</p>
              <h4 class="tw-text-2xl tw-font-extrabold tw-text-emerald-500 tw-m-0 tw-transition-transform group-hover:tw-scale-105 group-hover:tw-origin-left">
                {{ currencySymbol }}{{ formatAmount(referralEarning.today) }}
              </h4>
            </div>

            <div class="tw-py-4 tw-border-b tw-border-slate-100 tw-group hover:tw-bg-slate-50/50 tw-transition-colors">
              <p class="tw-text-xs tw-font-bold tw-uppercase tw-text-slate-400 tw-mb-1">This Month</p>
              <h4 class="tw-text-2xl tw-font-extrabold tw-text-indigo-500 tw-m-0 tw-transition-transform group-hover:tw-scale-105 group-hover:tw-origin-left">
                {{ currencySymbol }}{{ formatAmount(referralEarning.this_month) }}
              </h4>
            </div>

            <div class="tw-py-4 tw-group hover:tw-bg-slate-50/50 tw-transition-colors">
              <p class="tw-text-xs tw-font-bold tw-uppercase tw-text-slate-400 tw-mb-1">Total Earning</p>
              <h4 class="tw-text-3xl tw-font-extrabold tw-text-transparent tw-bg-clip-text tw-bg-gradient-to-r tw-from-indigo-600 tw-to-purple-600 tw-m-0 tw-transition-transform group-hover:tw-scale-105 group-hover:tw-origin-left">
                {{ currencySymbol }}{{ formatAmount(referralEarning.total) }}
              </h4>
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
    const referralCode = ref('')
    const referralLink = ref('')
    const packageLinks = ref([])
    const globalSpecialLinks = ref([])
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
      if (input) {
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
    }

    const fetchReferralData = async () => {
      try {
        const response = await api.get('/referral')
        if (response.data.status === 'success' && response.data.data) {
          referralCode.value = response.data.data.referral_code || ''
          referralLink.value = response.data.data.referral_link || ''
          packageLinks.value = response.data.data.package_links || []
          globalSpecialLinks.value = response.data.data.global_special_links || []
          downlineTeam.value = response.data.data.downline_team || []
          teamStats.value = response.data.data.team_stats || teamStats.value
          referralEarning.value = response.data.data.referral_earning || referralEarning.value
          currencySymbol.value = response.data.data?.currency_symbol || response.data.currency_symbol || '₹'
        }
      } catch (error) {
        console.error('Error loading referral data:', error)
      }
    }

    onMounted(() => {
      fetchReferralData()
    })

    return {
      referralCode,
      referralLink,
      packageLinks,
      globalSpecialLinks,
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
