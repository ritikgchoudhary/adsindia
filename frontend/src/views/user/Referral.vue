<template>
  <DashboardLayout page-title="My Team / Referral" :dark-theme="true">
    <!-- Partner Program Restriction Modal -->
    <div v-if="showPartnerModal" class="tw-fixed tw-inset-0 tw-z-[100] tw-flex tw-items-center tw-justify-center tw-px-4">
      <div class="tw-absolute tw-inset-0 tw-bg-slate-950/80 tw-backdrop-blur-md" @click="showPartnerModal = false"></div>
      <div class="tw-bg-[#151921] tw-rounded-[34px] tw-shadow-2xl tw-w-full tw-max-w-md tw-relative tw-z-10 tw-overflow-hidden tw-border tw-border-white/10 tw-animate-fade-in-up">
        <div class="tw-p-10 tw-text-center">
          <div class="tw-w-20 tw-h-20 tw-bg-indigo-500/10 tw-rounded-3xl tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-8 tw-border tw-border-indigo-500/20">
            <i class="fas fa-crown tw-text-4xl tw-text-indigo-400"></i>
          </div>
          <h3 class="tw-text-2xl tw-font-black tw-text-white tw-mb-4">Join Partner Program</h3>
          <p class="tw-text-slate-400 tw-leading-relaxed tw-mb-8 tw-text-sm">
            You need to join our <b>Partner Program</b> to access the Referral & Team Building tools. Unlock your earning potential today!
          </p>
          <div class="tw-flex tw-flex-col tw-gap-4">
            <router-link
              to="/user/partner-program"
              class="tw-w-full tw-py-4 tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-font-black tw-rounded-2xl tw-transition-all tw-no-underline tw-flex tw-items-center tw-justify-center tw-gap-2 tw-shadow-lg tw-shadow-indigo-500/20"
            >
              <i class="fas fa-rocket"></i> View Partner Plans
            </router-link>
            <button
              @click="showPartnerModal = false"
              class="tw-w-full tw-py-4 tw-bg-white/5 hover:tw-bg-white/10 tw-text-slate-300 tw-font-bold tw-rounded-2xl tw-transition-all tw-border-0 tw-cursor-pointer"
            >
              Maybe Later
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-12 tw-gap-8">
      
      <!-- Main Content -->
      <div class="lg:tw-col-span-8 tw-flex tw-flex-col tw-gap-8">
        
        <!-- General Referral Link (KEPT ORIGINAL) -->
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

        <!-- Package-Specific Links (KEPT ORIGINAL) -->
        <div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-slate-200 tw-overflow-hidden">
          <div class="tw-bg-slate-50 tw-p-5 tw-border-b tw-border-slate-200">
            <h5 class="tw-text-slate-900 tw-font-bold tw-text-lg tw-m-0 tw-flex tw-items-center">
              <i class="fas fa-gift tw-mr-2 tw-text-indigo-600"></i>Package-Specific Referral Links (All Plans)
            </h5>
          </div>
          <div class="tw-p-6">
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
            </div>
          </div>
        </div>

        <!-- Special Discount Links (KEPT ORIGINAL) -->
        <div v-if="globalSpecialLinks.length > 0" class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-slate-200 tw-overflow-hidden">
          <div class="tw-bg-slate-50 tw-p-5 tw-border-b tw-border-slate-200">
            <h5 class="tw-text-slate-900 tw-font-bold tw-text-lg tw-m-0 tw-flex tw-items-center">
              <i class="fas fa-bolt tw-mr-2 tw-text-indigo-600"></i>Special Discount Links
            </h5>
          </div>
          <div class="tw-p-6">
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
            </div>
          </div>
        </div>

        <!-- Premium My Downline Team Section (IMPROVED UI) -->
        <div class="tw-bg-[#151921] tw-rounded-[34px] tw-shadow-2xl tw-border tw-border-white/5 tw-overflow-hidden">
          <div class="tw-px-8 tw-py-8 tw-flex tw-flex-col sm:tw-flex-row tw-items-center tw-justify-between tw-gap-5">
            <h5 class="tw-text-white tw-font-black tw-text-2xl tw-m-0 tw-flex tw-items-center">
              <span class="tw-w-10 tw-h-10 tw-bg-indigo-500/20 tw-rounded-xl tw-flex tw-items-center tw-justify-center tw-mr-4">
                <i class="fas fa-users-crown tw-text-indigo-400"></i>
              </span>
              My Downline Team
            </h5>
            <div class="tw-relative tw-w-full sm:tw-w-80">
                <i class="fas fa-search tw-absolute tw-left-5 tw-top-1/2 -tw-translate-y-1/2 tw-text-slate-500"></i>
                <input 
                  v-model="searchQuery"
                  type="text" 
                  placeholder="Search by name or ADS ID..." 
                  class="tw-w-full tw-pl-12 tw-pr-5 tw-py-3.5 tw-bg-slate-800/40 tw-backdrop-blur-sm tw-border tw-border-white/10 tw-rounded-2xl tw-text-white tw-text-sm focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-indigo-500/30 tw-transition-all placeholder:tw-text-slate-600"
                >
            </div>
          </div>
          
          <div class="tw-px-8 tw-pb-10">
            <!-- Stats Grid - Premium Deep Gradients -->
            <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-6 tw-mb-12">
              <div class="tw-bg-gradient-to-br tw-from-indigo-600 tw-to-indigo-800 tw-rounded-[28px] tw-p-7 tw-text-white tw-shadow-xl tw-shadow-indigo-500/10 tw-relative tw-overflow-hidden tw-group hover:tw-scale-[1.02] tw-transition-transform">
                <div class="tw-absolute -tw-bottom-4 -tw-right-4 tw-text-8xl tw-opacity-10 tw-rotate-12 group-hover:tw-scale-110 tw-transition-transform">
                  <i class="fas fa-users"></i>
                </div>
                <h3 class="tw-text-4xl tw-font-black tw-mb-1">{{ Math.floor(totalMembersAnim) }}</h3>
                <p class="tw-text-indigo-200 tw-text-[11px] tw-font-black tw-uppercase tw-tracking-[2px] tw-m-0">Total Members</p>
              </div>
              
              <div class="tw-bg-gradient-to-br tw-from-emerald-500 tw-to-emerald-700 tw-rounded-[28px] tw-p-7 tw-text-white tw-shadow-xl tw-shadow-emerald-500/10 tw-relative tw-overflow-hidden tw-group hover:tw-scale-[1.02] tw-transition-transform">
                <div class="tw-absolute -tw-bottom-4 -tw-right-4 tw-text-8xl tw-opacity-10 tw-rotate-12 group-hover:tw-scale-110 tw-transition-transform">
                  <i class="fas fa-user-check"></i>
                </div>
                <h3 class="tw-text-4xl tw-font-black tw-mb-1">{{ Math.floor(activeMembersAnim) }}</h3>
                <p class="tw-text-emerald-100 tw-text-[11px] tw-font-black tw-uppercase tw-tracking-[2px] tw-m-0">Active Members</p>
              </div>
              
              <!-- Changed Amber/Yellow to Deep Violet Premium Gradient -->
              <div class="tw-bg-gradient-to-br tw-from-violet-600 tw-to-purple-800 tw-rounded-[28px] tw-p-7 tw-text-white tw-shadow-xl tw-shadow-violet-500/10 tw-relative tw-overflow-hidden tw-group hover:tw-scale-[1.02] tw-transition-transform">
                <div class="tw-absolute -tw-bottom-4 -tw-right-4 tw-text-8xl tw-opacity-10 tw-rotate-12 group-hover:tw-scale-110 tw-transition-transform">
                  <i class="fas fa-gem"></i>
                </div>
                <h3 class="tw-text-4xl tw-font-black tw-mb-1">{{ currencySymbol }}{{ formatAmount(teamEarningAnim) }}</h3>
                <p class="tw-text-violet-100 tw-text-[11px] tw-font-black tw-uppercase tw-tracking-[2px] tw-m-0">Team Earning</p>
              </div>
            </div>

            <!-- Sleek Table -->
            <div class="tw-overflow-x-auto">
              <table class="tw-w-full tw-text-sm tw-border-collapse">
                <thead>
                  <tr class="tw-border-b tw-border-white/5">
                    <th class="tw-px-6 tw-py-5 tw-text-left tw-text-[11px] tw-font-black tw-uppercase tw-text-slate-500 tw-tracking-[2px]">Member Details</th>
                    <th class="tw-px-6 tw-py-5 tw-text-left tw-text-[11px] tw-font-black tw-uppercase tw-text-slate-500 tw-tracking-[2px]">Join Date</th>
                    <th class="tw-px-6 tw-py-5 tw-text-left tw-text-[11px] tw-font-black tw-uppercase tw-text-slate-500 tw-tracking-[2px]">Status</th>
                    <th class="tw-px-6 tw-py-5 tw-text-right tw-text-[11px] tw-font-black tw-uppercase tw-text-slate-500 tw-tracking-[2px]">Income</th>
                  </tr>
                </thead>
                <tbody class="tw-divide-y tw-divide-white/5">
                  <tr v-for="member in filteredTeam" :key="member.id" class="hover:tw-bg-white/[0.02] tw-transition-colors group">
                    <td class="tw-px-6 tw-py-6">
                      <div class="tw-flex tw-items-center tw-gap-5">
                        <div class="tw-w-12 tw-h-12 tw-rounded-2xl tw-bg-indigo-500/10 tw-text-indigo-400 tw-flex tw-items-center tw-justify-center tw-font-black tw-text-base tw-border tw-border-indigo-500/20 group-hover:tw-scale-105 tw-transition-transform">
                          {{ (member.firstname?.[0] || member.username?.[0] || '?').toUpperCase() }}
                        </div>
                        <div class="tw-min-w-0">
                          <div class="tw-font-black tw-text-slate-100 tw-text-base tw-mb-0.5">
                             {{ (String((member.firstname || '') + ' ' + (member.lastname || '')).trim()) || member.username }}
                          </div>
                          <div class="tw-flex tw-items-center tw-gap-2.5">
                            <span class="tw-text-[10px] tw-font-black tw-bg-indigo-500/20 tw-text-indigo-300 tw-px-2 tw-py-0.5 tw-rounded-md tw-tracking-wider">ADS{{ member.id }}</span>
                            <span class="tw-text-xs tw-text-slate-500 tw-truncate">{{ member.email }}</span>
                          </div>
                        </div>
                      </div>
                    </td>
                    <td class="tw-px-6 tw-py-6 tw-text-slate-400 tw-text-sm tw-font-medium">{{ formatDate(member.joined_at) }}</td>
                    <td class="tw-px-6 tw-py-6">
                      <span 
                        class="tw-inline-flex tw-items-center tw-gap-2 tw-px-4 tw-py-1.5 tw-rounded-full tw-text-[10px] tw-font-black tw-uppercase tw-tracking-[1.5px] tw-shadow-sm"
                        :class="member.status === 'active' ? 'tw-bg-emerald-500/10 tw-text-emerald-400 tw-border tw-border-emerald-500/20' : 'tw-bg-rose-500/10 tw-text-rose-400 tw-border tw-border-rose-500/20'"
                      >
                        <span class="tw-w-1.5 tw-h-1.5 tw-rounded-full tw-bg-current tw-animate-pulse"></span>
                        {{ member.status }}
                      </span>
                    </td>
                    <td class="tw-px-6 tw-py-6 tw-font-black tw-text-white tw-text-right tw-text-lg">{{ currencySymbol }}{{ formatAmount(member.earning) }}</td>
                  </tr>
                  <tr v-if="filteredTeam.length === 0">
                    <td colspan="4" class="tw-px-6 tw-py-32 tw-text-center">
                      <div class="tw-w-24 tw-h-24 tw-bg-slate-800/50 tw-rounded-[30px] tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-6">
                        <i class="fas fa-users-slash tw-text-4xl tw-text-slate-600"></i>
                      </div>
                      <h4 class="tw-text-slate-400 tw-font-black tw-text-lg tw-mb-2">No team members found</h4>
                      <p class="tw-text-slate-600 tw-text-sm tw-max-w-xs tw-mx-auto">Use your referral link above to start building your network!</p>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>

      <!-- Right Sidebar (Sidebar kept exactly same) -->
      <div class="lg:tw-col-span-4 tw-flex tw-flex-col tw-gap-8">
        
        <!-- Affiliate Wallet -->
        <div 
          class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-slate-200 tw-overflow-hidden tw-cursor-pointer group"
          @click="handleAffiliateAction(null)"
        >
          <div class="tw-bg-slate-50 tw-p-5 tw-border-b tw-border-slate-200 group-hover:tw-bg-slate-100 tw-transition-colors">
            <h5 class="tw-text-slate-900 tw-font-bold tw-text-lg tw-m-0 tw-flex tw-items-center">
              <i class="fas fa-wallet tw-mr-2 tw-text-emerald-600"></i>Affiliate Wallet
            </h5>
          </div>
          <div class="tw-p-6 tw-flex tw-flex-col tw-gap-3">
            <button
              @click.stop="handleAffiliateAction('/user/affiliate-income')"
              class="tw-w-full tw-py-3 tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-font-bold tw-rounded-xl tw-text-center tw-no-underline tw-transition-all tw-text-sm tw-border-0 tw-cursor-pointer"
            >
              View Affiliate Dashboard
            </button>
            <button
              @click.stop="handleAffiliateAction('/user/affiliate-withdraw')"
              class="tw-w-full tw-py-3 tw-bg-emerald-600 hover:tw-bg-emerald-700 tw-text-white tw-font-bold tw-rounded-xl tw-text-center tw-no-underline tw-transition-all tw-text-sm tw-border-0 tw-cursor-pointer"
            >
              Withdraw Affiliate Income
            </button>
            <router-link
              to="/user/account-kyc"
              @click.stop
              class="tw-w-full tw-py-3 tw-bg-slate-900 hover:tw-bg-slate-800 tw-text-white tw-font-bold tw-rounded-xl tw-text-center tw-no-underline tw-transition-all tw-text-sm"
            >
              KYC (Required)
            </router-link>
          </div>
        </div>

        <!-- Referral Earning -->
        <div class="tw-bg-white tw-rounded-2xl tw-shadow-xl tw-border-2 tw-border-indigo-100 tw-overflow-hidden">
          <div class="tw-bg-indigo-600 tw-p-6 tw-text-white tw-flex tw-items-center tw-justify-between">
            <h5 class="tw-font-bold tw-text-lg tw-m-0">Referral Earning</h5>
            <span class="tw-text-indigo-200 tw-text-xl tw-font-bold">₹</span>
          </div>
          <div class="tw-p-6 tw-flex tw-flex-col tw-gap-0">
            
            <div class="tw-py-4 tw-border-b tw-border-slate-100">
              <p class="tw-text-[10px] tw-font-bold tw-uppercase tw-text-slate-400 tw-mb-1">Today Earning</p>
              <h4 class="tw-text-2xl tw-font-extrabold tw-text-emerald-500 tw-m-0">
                {{ currencySymbol }}{{ formatAmount(todayEarningAnim) }}
              </h4>
            </div>

            <div class="tw-py-4 tw-border-b tw-border-slate-100">
              <p class="tw-text-[10px] tw-font-bold tw-uppercase tw-text-slate-400 tw-mb-1">This Month</p>
              <h4 class="tw-text-2xl tw-font-extrabold tw-text-indigo-500 tw-m-0">
                {{ currencySymbol }}{{ formatAmount(thisMonthEarningAnim) }}
              </h4>
            </div>

            <div class="tw-py-4">
              <p class="tw-text-[10px] tw-font-bold tw-uppercase tw-text-slate-400 tw-mb-1">Total Earning</p>
              <h4 class="tw-text-3xl tw-font-extrabold tw-text-indigo-600 tw-m-0">
                {{ currencySymbol }}{{ formatAmount(totalEarningAnim) }}
              </h4>
            </div>

          </div>
        </div>
      </div>
      
    </div>
  </DashboardLayout>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import DashboardLayout from '../../components/DashboardLayout.vue'
import api from '../../services/api'

export default {
  name: 'Referral',
  components: {
    DashboardLayout
  },
  setup() {
    const router = useRouter()
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
    const searchQuery = ref('')
    const isPartner = ref(false)
    const isAgent = ref(false)
    const showPartnerModal = ref(false)

    // Count-up animated values
    const totalMembersAnim = ref(0)
    const activeMembersAnim = ref(0)
    const teamEarningAnim = ref(0)
    const todayEarningAnim = ref(0)
    const thisMonthEarningAnim = ref(0)
    const totalEarningAnim = ref(0)

    const animSeqMap = new WeakMap()
    const animateTo = (targetRef, to, duration = 1000) => {
      const seq = (animSeqMap.get(targetRef) || 0) + 1
      animSeqMap.set(targetRef, seq)
      const toNum = Number(to) || 0
      const start = performance.now()
      const step = (now) => {
        if (animSeqMap.get(targetRef) !== seq) return
        const p = Math.min(1, (now - start) / duration)
        const eased = 1 - Math.pow(1 - p, 3) // easeOutCubic
        targetRef.value = toNum * eased
        if (p < 1) requestAnimationFrame(step)
        else targetRef.value = toNum
      }
      requestAnimationFrame(step)
    }

    const filteredTeam = computed(() => {
      const q = searchQuery.value.toLowerCase().trim()
      if (!q) return downlineTeam.value
      return downlineTeam.value.filter(m => {
        const name = `${m.firstname} ${m.lastname}`.toLowerCase()
        const adsId = `ads${m.id}`
        const username = (m.username || '').toLowerCase()
        const email = (m.email || '').toLowerCase()
        return name.includes(q) || username.includes(q) || email.includes(q) || adsId.includes(q)
      })
    })

    const formatAmount = (amount) => {
      if (!amount && amount !== 0) return '0.00'
      return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }

    const formatDate = (dateString) => {
      if (!dateString) return '-'
      return new Date(dateString).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
    }

    const copyReferralLink = (inputId) => {
      const input = document.getElementById(inputId)
      if (input) {
        input.select()
        document.execCommand('copy')
        if (window.notify) {
          window.notify('success', 'Referral link copied successfully!')
        } else if (window.iziToast) {
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
          isPartner.value = response.data.data.is_partner || false
          isAgent.value = response.data.data.is_agent || false

          // Start count-up animations
          animateTo(totalMembersAnim, teamStats.value.total_members)
          animateTo(activeMembersAnim, teamStats.value.active_members)
          animateTo(teamEarningAnim, teamStats.value.total_earning)
          animateTo(todayEarningAnim, referralEarning.value.today)
          animateTo(thisMonthEarningAnim, referralEarning.value.this_month)
          animateTo(totalEarningAnim, referralEarning.value.total)
        }
      } catch (error) {
        console.error('Error loading referral data:', error)
      }
    }

    const handleAffiliateAction = (route) => {
      if (isAgent.value) {
        if (route) router.push(route)
      } else if (isPartner.value) {
        if (window.notify) {
          window.notify('info', 'You have joined the partner program. Please wait for admin to enable your affiliate dashboard.')
        } else {
          alert('You have joined the partner program. Please wait for admin to enable your affiliate dashboard.')
        }
      } else {
        showPartnerModal.value = true
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
      searchQuery,
      filteredTeam,
      formatAmount,
      formatDate,
      copyReferralLink,
      totalMembersAnim,
      activeMembersAnim,
      teamEarningAnim,
      todayEarningAnim,
      thisMonthEarningAnim,
      totalEarningAnim,
      isPartner,
      isAgent,
      showPartnerModal,
      handleAffiliateAction
    }
  }
}
</script>
