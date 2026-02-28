<template>
  <DashboardLayout page-title="Team Members" :dark-theme="true">
    <div class="tw-min-h-screen tw-pb-10">
      
      <!-- Clean Header -->
      <div class="tw-mb-6 sm:tw-mb-8">
        <div class="tw-flex tw-flex-col sm:tw-flex-row tw-items-center tw-justify-between tw-gap-4">
          <div class="tw-flex tw-items-center tw-gap-4">
            <div class="tw-w-10 tw-h-10 sm:tw-w-12 sm:tw-h-12 tw-bg-indigo-500/10 tw-rounded-xl tw-flex tw-items-center tw-justify-center tw-border tw-border-indigo-500/20">
              <i class="fas fa-users tw-text-indigo-400 tw-text-lg"></i>
            </div>
            <div>
              <h2 class="tw-text-xl sm:tw-text-2xl tw-font-bold tw-text-white tw-m-0">Team Members</h2>
              <p class="tw-text-slate-500 tw-text-xs tw-font-medium tw-m-0">Overview of your network and earnings</p>
            </div>
          </div>
          <div class="tw-relative tw-w-full sm:tw-w-72">
            <i class="fas fa-search tw-absolute tw-left-4 tw-top-1/2 -tw-translate-y-1/2 tw-text-slate-500 tw-text-xs"></i>
            <input 
              v-model="searchQuery"
              type="text" 
              placeholder="Search by name, ID or email..." 
              class="tw-w-full tw-pl-10 tw-pr-4 tw-py-2.5 tw-bg-slate-800/50 tw-border tw-border-white/10 tw-rounded-xl tw-text-white tw-text-xs focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-indigo-500/30 tw-transition-all tw-placeholder-slate-500"
            >
          </div>
        </div>
      </div>

      <!-- Stats Grid - Simple & Clean -->
      <div class="tw-grid tw-grid-cols-2 md:tw-grid-cols-3 tw-gap-4 sm:tw-gap-6 tw-mb-8">
        <div class="tw-bg-[#151921] tw-p-5 sm:tw-p-6 tw-rounded-2xl tw-border tw-border-white/5 tw-shadow-sm">
          <div class="tw-flex tw-items-center tw-gap-3 tw-mb-3">
            <div class="tw-w-8 tw-h-8 tw-bg-indigo-500/10 tw-rounded-lg tw-flex tw-items-center tw-justify-center">
              <i class="fas fa-user-group tw-text-indigo-400 tw-text-xs"></i>
            </div>
            <p class="tw-text-slate-400 tw-text-[10px] sm:tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-m-0">Total Members</p>
          </div>
          <h3 class="tw-text-2xl sm:tw-text-3xl tw-font-black tw-text-white tw-m-0">{{ Math.floor(totalMembersAnim) }}</h3>
        </div>

        <div class="tw-bg-[#151921] tw-p-5 sm:tw-p-6 tw-rounded-2xl tw-border tw-border-white/5 tw-shadow-sm">
          <div class="tw-flex tw-items-center tw-gap-3 tw-mb-3">
            <div class="tw-w-8 tw-h-8 tw-bg-emerald-500/10 tw-rounded-lg tw-flex tw-items-center tw-justify-center">
              <i class="fas fa-check-circle tw-text-emerald-400 tw-text-xs"></i>
            </div>
            <p class="tw-text-slate-400 tw-text-[10px] sm:tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-m-0">Active Members</p>
          </div>
          <h3 class="tw-text-2xl sm:tw-text-3xl tw-font-black tw-text-white tw-m-0">{{ Math.floor(activeMembersAnim) }}</h3>
        </div>

        <div class="tw-bg-[#151921] tw-p-5 sm:tw-p-6 tw-rounded-2xl tw-border tw-border-white/5 tw-shadow-sm tw-col-span-2 md:tw-col-span-1">
          <div class="tw-flex tw-items-center tw-gap-3 tw-mb-3">
            <div class="tw-w-8 tw-h-8 tw-bg-violet-500/10 tw-rounded-lg tw-flex tw-items-center tw-justify-center">
              <i class="fas fa-wallet tw-text-violet-400 tw-text-xs"></i>
            </div>
            <p class="tw-text-slate-400 tw-text-[10px] sm:tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-m-0">Team Earning</p>
          </div>
          <h3 class="tw-text-2xl sm:tw-text-3xl tw-font-black tw-text-white tw-m-0">{{ currencySymbol }}{{ formatAmount(teamEarningAnim) }}</h3>
        </div>
      </div>

      <!-- Member Table Card -->
      <div class="tw-bg-[#151921] tw-rounded-2xl sm:tw-rounded-[30px] tw-border tw-border-white/5 tw-overflow-hidden">
        <div class="tw-px-6 tw-py-5 tw-border-b tw-border-white/5 tw-flex tw-items-center tw-justify-between">
          <h5 class="tw-text-white tw-font-bold tw-text-sm sm:tw-text-base tw-m-0">Downline Members</h5>
          <span class="tw-text-[10px] tw-font-bold tw-text-slate-500 tw-bg-white/5 tw-px-3 tw-py-1 tw-rounded-full">{{ filteredTeam.length }} Members Found</span>
        </div>

        <div class="tw-overflow-x-auto">
          <!-- Desktop Table -->
          <table class="tw-w-full tw-text-sm tw-border-collapse tw-hidden lg:tw-table">
            <thead>
              <tr class="tw-border-b tw-border-white/5">
                <th class="tw-px-6 tw-py-4 tw-text-left tw-text-[10px] tw-font-black tw-uppercase tw-text-slate-500 tw-tracking-widest">User Details</th>
                <th class="tw-px-6 tw-py-4 tw-text-left tw-text-[10px] tw-font-black tw-uppercase tw-text-slate-500 tw-tracking-widest">Contact Info</th>
                <th class="tw-px-6 tw-py-4 tw-text-left tw-text-[10px] tw-font-black tw-uppercase tw-text-slate-500 tw-tracking-widest">Join Date</th>
                <th class="tw-px-6 tw-py-4 tw-text-center tw-text-[10px] tw-font-black tw-uppercase tw-text-slate-500 tw-tracking-widest">Status</th>
                <th class="tw-px-6 tw-py-4 tw-text-right tw-text-[10px] tw-font-black tw-uppercase tw-text-slate-500 tw-tracking-widest">Earning</th>
              </tr>
            </thead>
            <tbody class="tw-divide-y tw-divide-white/5">
              <tr v-for="member in filteredTeam" :key="member.id" class="hover:tw-bg-white/[0.02] tw-transition-colors">
                <td class="tw-px-6 tw-py-5">
                  <div class="tw-flex tw-items-center tw-gap-4">
                    <div class="tw-w-10 tw-h-10 tw-rounded-xl tw-bg-indigo-500/10 tw-text-indigo-400 tw-flex tw-items-center tw-justify-center tw-font-black tw-text-sm tw-border tw-border-indigo-500/10">
                      {{ (member.firstname?.[0] || member.username?.[0] || '?').toUpperCase() }}
                    </div>
                    <div>
                      <div class="tw-font-bold tw-text-slate-100 tw-text-sm">{{ (String((member.firstname || '') + ' ' + (member.lastname || '')).trim()) || member.username }}</div>
                      <div class="tw-text-[10px] tw-font-black tw-bg-indigo-500/20 tw-text-indigo-300 tw-px-1.5 tw-py-0.5 tw-rounded tw-mt-1 tw-inline-block">{{ member.display_id || ('ADS' + member.id) }}</div>
                    </div>
                  </div>
                </td>
                <td class="tw-px-6 tw-py-5">
                  <div class="tw-text-slate-400 tw-text-xs">{{ member.email }}</div>
                </td>
                <td class="tw-px-6 tw-py-5">
                  <div class="tw-text-slate-400 tw-text-xs">{{ formatDate(member.joined_at) }}</div>
                </td>
                <td class="tw-px-6 tw-py-5 tw-text-center">
                  <span 
                    class="tw-inline-flex tw-items-center tw-gap-1.5 tw-px-3 tw-py-1 tw-rounded-full tw-text-[10px] tw-font-bold tw-uppercase tw-tracking-wider"
                    :class="member.status === 'active' ? 'tw-bg-emerald-500/10 tw-text-emerald-400' : 'tw-bg-rose-500/10 tw-text-rose-400'"
                  >
                    <span class="tw-w-1.5 tw-h-1.5 tw-rounded-full tw-bg-current"></span>
                    {{ member.status }}
                  </span>
                </td>
                <td class="tw-px-6 tw-py-5 tw-text-right">
                  <div class="tw-font-black tw-text-white tw-text-sm">{{ currencySymbol }}{{ formatAmount(member.earning) }}</div>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Mobile List View -->
          <div class="lg:tw-hidden tw-p-2 tw-space-y-4">
            <div v-for="member in filteredTeam" :key="member.id" class="tw-bg-white/[0.03] tw-rounded-xl tw-p-4 tw-border tw-border-white/5">
              <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                <div class="tw-flex tw-items-center tw-gap-3">
                  <div class="tw-w-10 tw-h-10 tw-rounded-xl tw-bg-indigo-500/10 tw-text-indigo-400 tw-flex tw-items-center tw-justify-center tw-font-black tw-text-sm">
                    {{ (member.firstname?.[0] || member.username?.[0] || '?').toUpperCase() }}
                  </div>
                  <div>
                    <div class="tw-font-bold tw-text-white tw-text-sm">{{ (String((member.firstname || '') + ' ' + (member.lastname || '')).trim()) || member.username }}</div>
                    <div class="tw-text-[9px] tw-font-bold tw-text-slate-500">Joined: {{ formatDate(member.joined_at) }}</div>
                  </div>
                </div>
                <span 
                  class="tw-px-2 tw-py-0.5 tw-rounded-md tw-text-[8px] tw-font-bold tw-uppercase tw-tracking-wider"
                  :class="member.status === 'active' ? 'tw-bg-emerald-500/10 tw-text-emerald-400' : 'tw-bg-rose-500/10 tw-text-rose-400'"
                >
                  {{ member.status }}
                </span>
              </div>
              <div class="tw-flex tw-items-center tw-justify-between tw-bg-black/20 tw-p-3 tw-rounded-lg">
                <div>
                  <div class="tw-text-[9px] tw-text-slate-500 tw-uppercase tw-font-bold">Member ID</div>
                  <div class="tw-text-indigo-300 tw-text-xs tw-font-bold uppercase">{{ member.display_id || ('ADS' + member.id) }}</div>
                </div>
                <div class="tw-text-right">
                  <div class="tw-text-[9px] tw-text-slate-500 tw-uppercase tw-font-bold">Earning</div>
                  <div class="tw-text-white tw-text-sm tw-font-black">{{ currencySymbol }}{{ formatAmount(member.earning) }}</div>
                </div>
              </div>
            </div>

            <div v-if="filteredTeam.length === 0" class="tw-py-12 tw-text-center">
              <i class="fas fa-users-slash tw-text-3xl tw-text-slate-700 tw-mb-3"></i>
              <p class="tw-text-slate-500 tw-text-sm tw-m-0">No matching members found</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import DashboardLayout from '../../components/DashboardLayout.vue'
import api from '../../services/api'

export default {
  name: 'MyTeam',
  components: {
    DashboardLayout
  },
  setup() {
    const downlineTeam = ref([])
    const teamStats = ref({
      total_members: 0,
      active_members: 0,
      total_earning: 0
    })
    const currencySymbol = ref('₹')
    const searchQuery = ref('')

    const totalMembersAnim = ref(0)
    const activeMembersAnim = ref(0)
    const teamEarningAnim = ref(0)

    const animateTo = (targetRef, to, duration = 800) => {
      const toNum = Number(to) || 0
      const start = performance.now()
      const step = (now) => {
        const p = Math.min(1, (now - start) / duration)
        targetRef.value = toNum * p
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
        const adsId = (m.display_id || `ads${m.id}`).toLowerCase()
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

    const fetchTeamData = async () => {
      try {
        const response = await api.get('/referral')
        if (response.data.status === 'success' && response.data.data) {
          downlineTeam.value = response.data.data.downline_team || []
          teamStats.value = response.data.data.team_stats || teamStats.value
          currencySymbol.value = response.data.data?.currency_symbol || response.data.currency_symbol || '₹'

          animateTo(totalMembersAnim, teamStats.value.total_members)
          animateTo(activeMembersAnim, teamStats.value.active_members)
          animateTo(teamEarningAnim, teamStats.value.total_earning)
        }
      } catch (error) {
        console.error('Error loading team data:', error)
      }
    }

    onMounted(() => {
      fetchTeamData()
    })

    return {
      downlineTeam,
      teamStats,
      currencySymbol,
      searchQuery,
      filteredTeam,
      formatAmount,
      formatDate,
      totalMembersAnim,
      activeMembersAnim,
      teamEarningAnim
    }
  }
}
</script>

<style scoped>
/* Simple transitions */
.tw-bg-\[\#151921\] {
  background-color: #151921 !important;
}

@media (max-width: 640px) {
  .tw-rounded-2xl { border-radius: 1rem !important; }
}
</style>
