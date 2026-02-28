<template>
  <MasterAdminLayout page-title="Leaderboard & Dashboard Control">
    <div class="ma-leaderboard-control">
      
      <!-- Global Visibility Settings -->
      <div class="tw-bg-slate-900/60 tw-backdrop-blur-xl tw-border tw-border-white/10 tw-rounded-[2.5rem] tw-p-8 tw-mb-8 tw-shadow-[0_20px_50px_rgba(0,0,0,0.3)]">
        <div class="tw-flex tw-items-center tw-justify-between tw-mb-8">
          <div class="tw-flex tw-items-center tw-gap-4">
            <div class="tw-w-14 tw-h-14 tw-bg-gradient-to-br tw-from-indigo-500/20 tw-to-purple-500/20 tw-rounded-2xl tw-flex tw-items-center tw-justify-center tw-text-indigo-400 tw-border tw-border-indigo-500/20">
              <i class="fas fa-globe-americas tw-text-2xl"></i>
            </div>
            <div>
              <h5 class="tw-text-2xl tw-font-black tw-text-white tw-m-0 tw-tracking-tight">Global Visibility</h5>
              <p class="tw-text-slate-500 tw-text-xs tw-font-bold tw-mt-1 tw-uppercase tw-tracking-widest">Public Leaderboard Tab Control</p>
            </div>
          </div>
          <button @click="fetchSettings" class="tw-p-4 tw-bg-white/5 hover:tw-bg-white/10 tw-border tw-border-white/10 tw-rounded-2xl tw-text-slate-400 tw-transition-all active:tw-scale-90">
            <i class="fas fa-sync-alt" :class="{ 'tw-animate-spin': loading }"></i>
          </button>
        </div>

        <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 lg:tw-grid-cols-4 tw-gap-6">
          <div v-for="period in visibilityPeriods" :key="period.key" 
               class="tw-group tw-relative tw-bg-white/[0.03] tw-border tw-border-white/10 tw-rounded-3xl tw-p-6 tw-transition-all hover:tw-bg-white/[0.06] hover:tw-border-white/20">
            <div class="tw-flex tw-flex-col tw-items-center tw-text-center tw-relative tw-z-10">
              <div class="tw-text-slate-500 tw-text-[10px] tw-font-black tw-uppercase tw-tracking-[0.2em] tw-mb-6">{{ period.label }}</div>
              
              <label class="tw-relative tw-inline-flex tw-items-center tw-cursor-pointer">
                <input type="checkbox" v-model="settings[period.key]" class="tw-sr-only tw-peer">
                <div class="tw-w-16 tw-h-8 tw-bg-slate-800 tw-rounded-full tw-peer peer-checked:after:tw-translate-x-full peer-checked:after:tw-border-white after:tw-content-[''] after:tw-absolute after:tw-top-1 after:tw-left-[4px] after:tw-bg-white after:tw-rounded-full after:tw-h-6 after:tw-w-6 after:tw-transition-all tw-border-2 tw-border-white/5 peer-checked:tw-bg-gradient-to-r peer-checked:tw-from-emerald-500 peer-checked:tw-to-teal-400 peer-checked:tw-border-emerald-500/30"></div>
              </label>

              <div class="tw-mt-5 tw-flex tw-items-center tw-gap-2">
                <div class="tw-w-1.5 tw-h-1.5 tw-rounded-full" :class="settings[period.key] ? 'tw-bg-emerald-500 tw-shadow-[0_0_8px_rgba(16,185,129,0.8)]' : 'tw-bg-rose-500 tw-shadow-[0_0_8px_rgba(244,63,94,0.8)]'"></div>
                <span class="tw-text-[11px] tw-font-black tw-uppercase tw-tracking-widest" :class="settings[period.key] ? 'tw-text-emerald-400' : 'tw-text-rose-400'">
                  {{ settings[period.key] ? 'Enabled' : 'Disabled' }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <div class="tw-mt-10 tw-flex tw-justify-center">
          <button @click="updateGlobalSettings" 
                  class="tw-group tw-relative tw-px-12 tw-py-4 tw-bg-indigo-500 tw-text-white tw-font-black tw-rounded-2xl tw-text-sm tw-overflow-hidden tw-transition-all hover:tw-bg-indigo-600 hover:tw-shadow-[0_10px_30px_rgba(79,70,229,0.4)] active:tw-scale-95 disabled:tw-opacity-50" 
                  :disabled="updatingGlobal">
            <span class="tw-relative tw-z-10 tw-flex tw-items-center tw-gap-3">
              <i class="fas fa-save tw-text-lg"></i>
              {{ updatingGlobal ? 'PROPAGATING...' : 'PUBLISH CHANGES' }}
            </span>
            <div class="tw-absolute tw-inset-0 tw-bg-gradient-to-r tw-from-transparent tw-via-white/10 tw-to-transparent tw--translate-x-full group-hover:tw-animate-[shimmer_1.5s_infinite]"></div>
          </button>
        </div>
      </div>

      <!-- User Overrides Center -->
      <div class="tw-bg-slate-900/60 tw-backdrop-blur-xl tw-border tw-border-white/10 tw-rounded-[2.5rem] tw-overflow-visible tw-relative tw-shadow-2xl">
        <div class="tw-p-8 tw-border-b tw-border-white/5 tw-flex tw-flex-col md:tw-flex-row tw-justify-between tw-items-center tw-gap-8">
          <div class="tw-flex tw-items-center tw-gap-4">
            <div class="tw-w-14 tw-h-14 tw-bg-gradient-to-br tw-from-amber-500/20 tw-to-orange-500/20 tw-rounded-2xl tw-flex tw-items-center tw-justify-center tw-text-amber-400 tw-border tw-border-amber-500/20">
              <i class="fas fa-fire tw-text-2xl"></i>
            </div>
            <div>
              <h5 class="tw-text-2xl tw-font-black tw-text-white tw-m-0 tw-tracking-tight">Personalized Hype Center</h5>
              <p class="tw-text-slate-500 tw-text-xs tw-font-bold tw-mt-1 tw-uppercase tw-tracking-widest">Manual Income Injection Panel</p>
            </div>
          </div>
          
          <!-- Search Bench -->
          <div class="tw-relative tw-w-full md:tw-w-96">
            <div class="tw-absolute tw-left-5 tw-top-1/2 tw--translate-y-1/2 tw-text-slate-500">
              <i class="fas fa-search tw-text-sm"></i>
            </div>
            <input 
              type="text" 
              v-model="searchQuery" 
              placeholder="Search by User ID, Name or Email..." 
              @input="debounceSearch"
              class="tw-w-full tw-bg-white/5 tw-border tw-border-white/10 tw-rounded-[20px] tw-pl-12 tw-pr-6 tw-py-4 tw-text-white tw-text-sm tw-font-bold focus:tw-border-indigo-500 focus:tw-bg-white/[0.08] tw-outline-none tw-transition-all tw-placeholder:tw-text-slate-600 shadow-inner"
            >
          </div>
        </div>

        <!-- Table Body -->
        <div class="tw-overflow-x-auto">
          <table class="tw-w-full tw-border-collapse">
            <thead>
              <tr class="tw-bg-white/[0.02]">
                <th class="tw-px-8 tw-py-6 tw-text-left tw-text-[10px] tw-font-black tw-text-slate-500 tw-uppercase tw-tracking-[0.2em]">Subscriber</th>
                <th class="tw-px-6 tw-py-6 tw-text-left tw-text-[10px] tw-font-black tw-text-slate-500 tw-uppercase tw-tracking-[0.2em]">LB Identity</th>
                <th class="tw-px-6 tw-py-6 tw-text-left tw-text-[10px] tw-font-black tw-text-slate-500 tw-uppercase tw-tracking-[0.2em]">Ads Hype (T / W / M / A)</th>
                <th class="tw-px-6 tw-py-6 tw-text-left tw-text-[10px] tw-font-black tw-text-slate-500 tw-uppercase tw-tracking-[0.2em]">Affiliate Hype (T / W / M / A)</th>
                <th class="tw-px-8 tw-py-6 tw-text-right tw-text-[10px] tw-font-black tw-text-slate-500 tw-uppercase tw-tracking-[0.2em]">Configuration</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading" class="tw-border-b tw-border-white/5">
                <td colspan="5" class="tw-py-32 tw-text-center">
                  <div class="ma-spinner tw-mx-auto tw-border-indigo-500"></div>
                  <div class="tw-mt-4 tw-text-slate-500 tw-text-xs tw-font-black tw-uppercase tw-tracking-widest tw-animate-pulse">Accessing Data Vault...</div>
                </td>
              </tr>
              <tr v-else-if="users.length === 0" class="tw-border-b tw-border-white/5">
                <td colspan="5" class="tw-py-32 tw-text-center">
                  <div class="tw-w-20 tw-h-20 tw-bg-white/5 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-4 tw-border tw-border-white/10">
                    <i class="fas fa-user-slash tw-text-3xl tw-text-slate-700"></i>
                  </div>
                  <h4 class="tw-text-slate-400 tw-font-black tw-text-lg">No Results Found</h4>
                  <p class="tw-text-slate-600 tw-text-xs tw-mt-2">Try adjusting your search filters</p>
                </td>
              </tr>
              <tr v-else v-for="user in users" :key="user.id" class="tw-group hover:tw-bg-white/[0.03] tw-transition-all tw-border-b tw-border-white/5 last:tw-border-0">
                <td class="tw-px-8 tw-py-6">
                  <div class="tw-flex tw-items-center tw-gap-4">
                    <div class="tw-w-10 tw-h-10 tw-bg-slate-800 tw-border tw-border-white/10 tw-rounded-xl tw-flex tw-items-center tw-justify-center tw-text-indigo-400 tw-font-black tw-text-xs tw-shadow-lg">
                      {{ (user.firstname || 'U').charAt(0) }}{{ (user.lastname || 'P').charAt(0) }}
                    </div>
                    <div>
                      <div class="tw-text-white tw-font-black tw-text-sm">{{ user.firstname }} {{ user.lastname }}</div>
                      <div class="tw-text-slate-600 tw-text-[10px] tw-font-bold tw-uppercase tw-tracking-widest tw-mt-0.5">{{ user.email }}</div>
                    </div>
                  </div>
                </td>
                <td class="tw-px-6 tw-py-6">
                  <div class="tw-flex tw-flex-col tw-gap-2">
                    <code class="tw-text-[11px] tw-text-indigo-400 tw-font-black tw-bg-indigo-500/10 tw-px-2.5 tw-py-1 tw-rounded-lg tw-border tw-border-indigo-500/20 tw-w-max">
                      ADS{{ user.id }}
                    </code>
                    <div class="tw-flex tw-items-center tw-gap-1.5">
                      <div class="tw-w-1.5 tw-h-1.5 tw-rounded-full" :class="user.is_lb_hidden ? 'tw-bg-rose-500' : 'tw-bg-emerald-500'"></div>
                      <span class="tw-text-[9px] tw-font-black tw-uppercase tw-tracking-widest" :class="user.is_lb_hidden ? 'tw-text-rose-400' : 'tw-text-emerald-400'">
                        {{ user.is_lb_hidden ? 'Ghost Mode' : 'Listed' }}
                      </span>
                    </div>
                  </div>
                </td>
                <td class="tw-px-6 tw-py-6">
                  <div class="tw-flex tw-items-center tw-gap-2 tw-text-xs tw-font-mono tw-font-bold tw-text-slate-400">
                    <span class="tw-text-indigo-400">₹{{ user.lead_ads_today }}</span>
                    <span class="tw-text-slate-700">/</span>
                    <span>₹{{ user.lead_ads_weekly }}</span>
                    <span class="tw-text-slate-700">/</span>
                    <span>₹{{ user.lead_ads_monthly }}</span>
                    <span class="tw-text-slate-700">/</span>
                    <span class="tw-text-slate-500">₹{{ user.lead_ads_all_time }}</span>
                  </div>
                </td>
                <td class="tw-px-6 tw-py-6">
                   <div class="tw-flex tw-items-center tw-gap-2 tw-text-xs tw-font-mono tw-font-bold tw-text-slate-400">
                    <span class="tw-text-emerald-400">₹{{ user.lead_aff_today }}</span>
                    <span class="tw-text-slate-700">/</span>
                    <span>₹{{ user.lead_aff_weekly }}</span>
                    <span class="tw-text-slate-700">/</span>
                    <span>₹{{ user.lead_aff_monthly }}</span>
                    <span class="tw-text-slate-700">/</span>
                    <span class="tw-text-slate-500">₹{{ user.lead_aff_all_time }}</span>
                  </div>
                </td>
                <td class="tw-px-8 tw-py-6 tw-text-right">
                  <button @click="openEditModal(user)" class="tw-h-11 tw-px-5 tw-bg-white/5 tw-text-indigo-400 tw-rounded-2xl hover:tw-bg-indigo-500 hover:tw-text-white tw-border tw-border-white/10 hover:tw-border-indigo-400 tw-transition-all active:tw-scale-90 tw-group/btn">
                    <i class="fas fa-sliders-h tw-mr-2 group-hover/btn:tw-rotate-90 tw-transition-transform"></i>
                    <span class="tw-text-[10px] tw-font-black tw-uppercase tw-tracking-widest">Configure</span>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="lastPage > 1" class="tw-p-8 tw-bg-white/[0.02] tw-border-t tw-border-white/5 tw-flex tw-items-center tw-justify-center tw-gap-6">
          <button @click="fetchUsers(currentPage - 1)" :disabled="currentPage === 1" 
                  class="tw-w-12 tw-h-12 tw-bg-white/5 tw-border tw-border-white/10 tw-rounded-2xl tw-text-slate-400 hover:tw-bg-white/10 hover:tw-text-white disabled:tw-opacity-20 tw-transition-all">
            <i class="fas fa-arrow-left"></i>
          </button>
          <div class="tw-px-6 tw-py-2 tw-bg-white/5 tw-border tw-border-white/10 tw-rounded-full tw-text-white tw-font-black tw-text-xs tw-uppercase tw-tracking-widest">
            Level {{ currentPage }} <span class="tw-text-slate-500 tw-mx-2">OF</span> {{ lastPage }}
          </div>
          <button @click="fetchUsers(currentPage + 1)" :disabled="currentPage === lastPage" 
                  class="tw-w-12 tw-h-12 tw-bg-white/5 tw-border tw-border-white/10 tw-rounded-2xl tw-text-slate-400 hover:tw-bg-white/10 hover:tw-text-white disabled:tw-opacity-20 tw-transition-all">
            <i class="fas fa-arrow-right"></i>
          </button>
        </div>
      </div>

      <!-- Edit Hype Modal -->
      <div v-if="showEditModal" class="ma-modal-overlay" @click="closeEditModal">
        <div class="ma-modal ma-modal--lg tw-rounded-[40px] tw-overflow-hidden tw-border tw-border-white/10 tw-shadow-[0_0_100px_rgba(0,0,0,0.5)]" @click.stop>
          <div class="ma-modal__header tw-bg-slate-900 tw-py-8 tw-px-10 tw-border-b tw-border-white/5">
            <div class="tw-flex tw-items-center tw-gap-5">
              <div class="tw-w-16 tw-h-16 tw-bg-indigo-500/10 tw-rounded-[24px] tw-flex tw-items-center tw-justify-center tw-text-indigo-400 tw-border tw-border-indigo-500/20">
                <i class="fas fa-bolt tw-text-2xl"></i>
              </div>
              <div>
                <h5 class="tw-text-2xl tw-font-black tw-text-white tw-m-0">Hype Configuration</h5>
                <p class="tw-text-indigo-400 tw-text-xs tw-font-black tw-uppercase tw-tracking-widest tw-mt-1">Tuning ID: ADS{{ editingUser?.id }}</p>
              </div>
            </div>
            <button class="ma-modal__close tw-bg-white/5 hover:tw-bg-rose-500/20 hover:tw-text-rose-400 tw-w-12 tw-h-12 tw-rounded-2xl tw-transition-all" @click="closeEditModal">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <div class="ma-modal__body tw-bg-slate-950 tw-p-10">
            <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-10">
              <!-- Ads Center -->
              <div class="tw-relative tw-bg-indigo-500/[0.03] tw-border tw-border-indigo-500/10 tw-rounded-[35px] tw-p-8">
                <div class="tw-absolute tw-top-0 tw-right-0 tw-p-6 tw-opacity-5">
                  <i class="fas fa-chart-line tw-text-8xl"></i>
                </div>
                <h6 class="tw-text-indigo-400 tw-font-black tw-text-xs tw-uppercase tw-tracking-[0.2em] tw-mb-8 tw-flex tw-items-center tw-gap-3">
                  <div class="tw-w-2 tw-h-2 tw-bg-indigo-500 tw-rounded-full tw-animate-pulse"></div>
                  Ads Dashboard Overrides
                </h6>
                <div class="tw-space-y-6">
                  <div v-for="f in ['today', 'weekly', 'monthly', 'all_time']" :key="'ads_'+f">
                    <label class="tw-text-[10px] tw-font-black tw-text-slate-500 tw-uppercase tw-tracking-widest tw-mb-2 tw-block">Add to {{ f.replace('_', ' ') }}</label>
                    <div class="tw-relative">
                      <span class="tw-absolute tw-left-5 tw-top-1/2 tw--translate-y-1/2 tw-text-indigo-400 tw-font-bold">₹</span>
                      <input 
                        type="number" 
                        step="any" 
                        v-model.number="editForm['lead_ads_'+f]" 
                        @input="propagateAds(f)"
                        class="tw-w-full tw-bg-black/40 tw-border tw-border-white/10 tw-rounded-2xl tw-py-4 tw-pl-10 tw-pr-6 tw-text-white tw-font-mono tw-text-lg focus:tw-border-indigo-500 tw-outline-none tw-transition-all shadow-inner"
                      >
                    </div>
                  </div>
                </div>
              </div>

              <!-- Affiliate Center -->
              <div class="tw-relative tw-bg-emerald-500/[0.03] tw-border tw-border-emerald-500/10 tw-rounded-[35px] tw-p-8">
                <div class="tw-absolute tw-top-0 tw-right-0 tw-p-6 tw-opacity-5">
                  <i class="fas fa-users tw-text-8xl"></i>
                </div>
                <h6 class="tw-text-emerald-400 tw-font-black tw-text-xs tw-uppercase tw-tracking-[0.2em] tw-mb-8 tw-flex tw-items-center tw-gap-3">
                  <div class="tw-w-2 tw-h-2 tw-bg-emerald-500 tw-rounded-full tw-animate-pulse"></div>
                  Affiliate Dashboard Overrides
                </h6>
                <div class="tw-space-y-6">
                  <div v-for="f in ['today', 'weekly', 'monthly', 'all_time']" :key="'aff_'+f">
                    <label class="tw-text-[10px] tw-font-black tw-text-slate-500 tw-uppercase tw-tracking-widest tw-mb-2 tw-block">Add to {{ f.replace('_', ' ') }}</label>
                    <div class="tw-relative">
                      <span class="tw-absolute tw-left-5 tw-top-1/2 tw--translate-y-1/2 tw-text-emerald-400 tw-font-bold">₹</span>
                      <input 
                        type="number" 
                        step="any" 
                        v-model.number="editForm['lead_aff_'+f]" 
                        @input="propagateAff(f)"
                        class="tw-w-full tw-bg-black/40 tw-border tw-border-white/10 tw-rounded-2xl tw-py-4 tw-pl-10 tw-pr-6 tw-text-white tw-font-mono tw-text-lg focus:tw-border-emerald-500 tw-outline-none tw-transition-all shadow-inner"
                      >
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Ghost Mode Toggle -->
            <div class="tw-mt-10 tw-p-8 tw-bg-gradient-to-r tw-from-white/[0.03] tw-to-transparent tw-rounded-[30px] tw-border tw-border-white/10 tw-flex tw-items-center tw-justify-between tw-shadow-lg">
              <div class="tw-flex tw-items-center tw-gap-5">
                <div class="tw-w-12 tw-h-12 tw-bg-rose-500/10 tw-rounded-2xl tw-flex tw-items-center tw-justify-center tw-text-rose-400 tw-border tw-border-rose-500/20">
                  <i class="fas fa-ghost tw-text-xl"></i>
                </div>
                <div>
                  <h6 class="tw-text-white tw-font-black tw-text-sm tw-m-0">Ghost Identification Mode</h6>
                  <p class="tw-text-slate-500 tw-text-[10px] tw-font-bold tw-uppercase tw-tracking-widest tw-mt-1 tw-m-0">Remove user from public rankings</p>
                </div>
              </div>
              <label class="tw-relative tw-inline-flex tw-items-center tw-cursor-pointer">
                <input type="checkbox" v-model="editForm.is_lb_hidden" class="tw-sr-only tw-peer">
                <div class="tw-w-16 tw-h-8 tw-bg-slate-800 tw-rounded-full tw-peer peer-checked:after:tw-translate-x-full peer-checked:after:tw-border-white after:tw-content-[''] after:tw-absolute after:tw-top-1 after:tw-left-[4px] after:tw-bg-white after:tw-rounded-full after:tw-h-6 after:tw-w-6 after:tw-transition-all tw-border-2 tw-border-white/5 peer-checked:tw-bg-rose-600"></div>
              </label>
            </div>

            <div class="tw-mt-10">
              <button @click="updateUserHype" 
                      class="tw-group tw-relative tw-w-full tw-py-6 tw-bg-white tw-text-slate-950 tw-font-black tw-rounded-3xl tw-text-sm tw-overflow-hidden tw-transition-all hover:tw-bg-indigo-50 hover:tw-shadow-[0_20px_40px_rgba(0,0,0,0.3)] active:tw-scale-95 disabled:tw-opacity-50" 
                      :disabled="updatingUser">
                <span class="tw-relative tw-z-10 tw-flex tw-items-center tw-justify-center tw-gap-3">
                  <i class="fas fa-check-circle tw-text-xl"></i>
                  {{ updatingUser ? 'UPDATING ARCHIVES...' : 'COMMIT CHANGES' }}
                </span>
              </button>
            </div>
          </div>
        </div>
      </div>

    </div>
  </MasterAdminLayout>
</template>

<script>
import { ref, onMounted } from 'vue'
import api from '../../services/api'
import MasterAdminLayout from '../../components/master_admin/MasterAdminLayout.vue'

export default {
  name: 'LeaderboardControl',
  components: { MasterAdminLayout },
  setup() {
    const loading = ref(true)
    const updatingGlobal = ref(false)
    const updatingUser = ref(false)
    const users = ref([])
    const currentPage = ref(1)
    const lastPage = ref(1)
    const searchQuery = ref('')
    let searchTimeout = null

    const settings = ref({
      lb_show_today: false,
      lb_show_weekly: false,
      lb_show_monthly: false,
      lb_show_all_time: false
    })

    const visibilityPeriods = [
      { key: 'lb_show_today', label: 'TODAY' },
      { key: 'lb_show_weekly', label: '7 DAYS' },
      { key: 'lb_show_monthly', label: '30 DAYS' },
      { key: 'lb_show_all_time', label: 'ALL TIME' }
    ]

    const showEditModal = ref(false)
    const editingUser = ref(null)
    const editForm = ref({
      lead_ads_today: 0,
      lead_ads_weekly: 0,
      lead_ads_monthly: 0,
      lead_ads_all_time: 0,
      lead_aff_today: 0,
      lead_aff_weekly: 0,
      lead_aff_monthly: 0,
      lead_aff_all_time: 0,
      is_lb_hidden: false
    })

    const fetchSettings = async () => {
      loading.value = true
      try {
        const response = await api.get('/admin/leaderboard-control/settings')
        if (response.data.status === 'success') {
          settings.value = {
            lb_show_today: !!response.data.settings.lb_show_today,
            lb_show_weekly: !!response.data.settings.lb_show_weekly,
            lb_show_monthly: !!response.data.settings.lb_show_monthly,
            lb_show_all_time: !!response.data.settings.lb_show_all_time
          }
        }
      } catch (error) {
        if (window.notify) window.notify('error', 'Failed to synchronize settings')
      } finally {
        loading.value = false
      }
    }

    const fetchUsers = async (page = 1) => {
      loading.value = true
      try {
        const response = await api.get('/admin/leaderboard-control/users', {
          params: { page, search: searchQuery.value }
        })
        if (response.data.status === 'success') {
          users.value = response.data.users.data
          currentPage.value = response.data.users.current_page
          lastPage.value = response.data.users.last_page
        }
      } catch (error) {
        if (window.notify) window.notify('error', 'Failed to traverse user database')
      } finally {
        loading.value = false
      }
    }

    const debounceSearch = () => {
      if (searchTimeout) clearTimeout(searchTimeout)
      searchTimeout = setTimeout(() => {
        fetchUsers(1)
      }, 500)
    }

    const updateGlobalSettings = async () => {
      updatingGlobal.value = true
      try {
        const payload = {
          lb_show_today: settings.value.lb_show_today ? 1 : 0,
          lb_show_weekly: settings.value.lb_show_weekly ? 1 : 0,
          lb_show_monthly: settings.value.lb_show_monthly ? 1 : 0,
          lb_show_all_time: settings.value.lb_show_all_time ? 1 : 0
        }
        const response = await api.post('/admin/leaderboard-control/settings', payload)
        if (response.data.status === 'success') {
          if (window.notify) window.notify('success', 'Global configuration broadcasted')
          if (response.data.settings) {
            settings.value = {
              lb_show_today: !!response.data.settings.lb_show_today,
              lb_show_weekly: !!response.data.settings.lb_show_weekly,
              lb_show_monthly: !!response.data.settings.lb_show_monthly,
              lb_show_all_time: !!response.data.settings.lb_show_all_time
            }
          }
        }
      } catch (error) {
        if (window.notify) window.notify('error', 'Sync failure: Broadcasting interrupted')
      } finally {
        updatingGlobal.value = false
      }
    }

    const openEditModal = (user) => {
      editingUser.value = user
      editForm.value = {
        lead_ads_today: Number(user.lead_ads_today || 0),
        lead_ads_weekly: Number(user.lead_ads_weekly || 0),
        lead_ads_monthly: Number(user.lead_ads_monthly || 0),
        lead_ads_all_time: Number(user.lead_ads_all_time || 0),
        lead_aff_today: Number(user.lead_aff_today || 0),
        lead_aff_weekly: Number(user.lead_aff_weekly || 0),
        lead_aff_monthly: Number(user.lead_aff_monthly || 0),
        lead_aff_all_time: Number(user.lead_aff_all_time || 0),
        is_lb_hidden: !!user.is_lb_hidden
      }
      showEditModal.value = true
    }

    const closeEditModal = () => {
      showEditModal.value = false
      editingUser.value = null
    }

    const updateUserHype = async () => {
      updatingUser.value = true
      try {
        const response = await api.post(`/admin/leaderboard-control/user/${editingUser.value.id}`, editForm.value)
        if (response.data.status === 'success') {
          if (window.notify) window.notify('success', 'Identity hype updated successfully')
          fetchUsers(currentPage.value)
          closeEditModal()
        }
      } catch (error) {
        if (window.notify) window.notify('error', 'Hype injection failed')
      } finally {
        updatingUser.value = false
      }
    }

    const leadFields = ['today', 'weekly', 'monthly', 'all_time']
    
    const propagateAds = (changedField) => {
      const val = editForm.value['lead_ads_' + changedField]
      const index = leadFields.indexOf(changedField)
      for (let i = index + 1; i < leadFields.length; i++) {
        const nextField = 'lead_ads_' + leadFields[i]
        if (editForm.value[nextField] < val) {
          editForm.value[nextField] = val
        }
      }
    }

    const propagateAff = (changedField) => {
      const val = editForm.value['lead_aff_' + changedField]
      const index = leadFields.indexOf(changedField)
      for (let i = index + 1; i < leadFields.length; i++) {
        const nextField = 'lead_aff_' + leadFields[i]
        if (editForm.value[nextField] < val) {
          editForm.value[nextField] = val
        }
      }
    }

    onMounted(() => {
      fetchSettings()
      fetchUsers()
    })

    return {
      loading, updatingGlobal, updatingUser, users, currentPage, lastPage,
      searchQuery, settings, visibilityPeriods, debounceSearch, fetchUsers,
      updateGlobalSettings, showEditModal, editingUser, editForm, openEditModal,
      closeEditModal, updateUserHype, fetchSettings, propagateAds, propagateAff
    }
  }
}
</script>

<style scoped>
@keyframes shimmer {
  100% { transform: translateX(100%); }
}

.ma-leaderboard-control {
  padding-bottom: 4rem;
}

.ma-spinner {
  width: 48px;
  height: 48px;
  border: 4px solid rgba(255, 255, 255, 0.1);
  border-top: 4px solid #4f46e5;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.ma-page-btn {
  @apply tw-flex tw-items-center tw-justify-center;
}

/* Custom modal styling for deeper glassmorphism */
.ma-modal-overlay {
  background: rgba(0, 0, 0, 0.8) !important;
  backdrop-filter: blur(15px) !important;
}

/* Hide arrows on number input for clean look */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
</style>

