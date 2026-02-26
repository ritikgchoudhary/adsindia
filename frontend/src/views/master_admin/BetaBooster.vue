<template>
  <MasterAdminLayout>
    <div class="beta-booster">
      <div class="tw-flex tw-items-center tw-justify-between tw-mb-8">
        <div>
          <router-link to="/master_admin/beta-features" class="tw-text-indigo-400 tw-text-sm tw-no-underline hover:tw-underline tw-mb-2 tw-inline-block">
            <i class="fas fa-arrow-left tw-mr-1"></i> Back to Hub
          </router-link>
          <h2 class="tw-text-2xl tw-font-bold tw-text-white tw-flex tw-items-center tw-gap-3">
             <i class="fas fa-rocket tw-text-orange-400"></i> Ad Booster Settings
          </h2>
        </div>
      </div>

      <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-3 tw-gap-8">
        <!-- Settings Form -->
        <div class="lg:tw-col-span-2 tw-bg-slate-900/40 tw-backdrop-blur-xl tw-border tw-border-white/10 tw-rounded-3xl tw-p-8">
          <h3 class="tw-text-white tw-font-bold tw-mb-6">Booster Plan Configuration</h3>
          
          <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-6 tw-mb-8">
            <div>
              <label class="tw-block tw-text-xs tw-font-bold tw-text-slate-500 tw-uppercase tw-mb-2">Daily Price (₹)</label>
              <input v-model="form.daily_price" type="number" class="tw-w-full tw-bg-white/5 tw-border tw-border-white/10 tw-rounded-xl tw-px-4 tw-py-3 tw-text-white focus:tw-border-orange-500 tw-outline-none" placeholder="29">
            </div>
            <div>
              <label class="tw-block tw-text-xs tw-font-bold tw-text-slate-500 tw-uppercase tw-mb-2">Weekly Price (₹)</label>
              <input v-model="form.weekly_price" type="number" class="tw-w-full tw-bg-white/5 tw-border tw-border-white/10 tw-rounded-xl tw-px-4 tw-py-3 tw-text-white focus:tw-border-orange-500 tw-outline-none" placeholder="149">
            </div>
          </div>

          <div class="tw-bg-orange-500/10 tw-border tw-border-orange-500/20 tw-rounded-2xl tw-p-6 tw-mb-8">
             <div class="tw-flex tw-items-center tw-gap-4 tw-mb-4">
                <div class="tw-w-10 tw-h-10 tw-bg-orange-500 tw-rounded-xl tw-flex tw-items-center tw-justify-center tw-text-white">
                   <i class="fas fa-bolt"></i>
                </div>
                <div>
                   <h4 class="tw-text-white tw-font-bold tw-text-sm">Booster Power</h4>
                   <p class="tw-text-[10px] tw-text-orange-200/60">How many extra ads does the booster give?</p>
                </div>
             </div>
             <input v-model="form.extra_ads" type="number" class="tw-w-full tw-bg-black/20 tw-border tw-border-white/10 tw-rounded-xl tw-px-4 tw-py-3 tw-text-white focus:tw-border-orange-500 tw-outline-none" placeholder="5">
          </div>

          <button @click="saveSettings" class="tw-w-full tw-bg-orange-600 hover:tw-bg-orange-700 tw-text-white tw-py-4 tw-rounded-2xl tw-font-bold tw-transition-all tw-shadow-xl tw-shadow-orange-500/10">
             Save Booster Configuration
          </button>
        </div>

        <!-- Sidebar Info -->
        <div class="tw-space-y-6">
           <div class="tw-bg-slate-900/40 tw-backdrop-blur-xl tw-border tw-border-white/10 tw-rounded-3xl tw-p-6">
              <h4 class="tw-text-white tw-font-bold tw-text-sm tw-mb-4">Stats Overvie</h4>
              <div class="tw-space-y-4">
                 <div class="tw-flex tw-justify-between">
                    <span class="tw-text-xs tw-text-slate-500">Active Boosters</span>
                    <span class="tw-text-xs tw-font-bold tw-text-white">142</span>
                 </div>
                 <div class="tw-flex tw-justify-between">
                    <span class="tw-text-xs tw-text-slate-500">Today's Revenue</span>
                    <span class="tw-text-xs tw-font-bold tw-text-emerald-400">₹4,118</span>
                 </div>
              </div>
           </div>

           <div class="tw-bg-gradient-to-br tw-from-orange-500 tw-to-red-600 tw-rounded-3xl tw-p-6 tw-text-white">
              <i class="fas fa-rocket tw-text-3xl tw-mb-4 tw-opacity-50"></i>
              <h4 class="tw-font-black tw-mb-2">Revenue Strategy</h4>
              <p class="tw-text-[10px] tw-text-white/80 tw-leading-relaxed">
                 The Ad Booster (Point #4) creates urgency. Users want to maximize daily earnings. Low-cost entries like ₹29/day encourage frequent impulsive purchases.
              </p>
           </div>
        </div>
      </div>
    </div>
  </MasterAdminLayout>
</template>

<script>
import MasterAdminLayout from '../../components/master_admin/MasterAdminLayout.vue'
import api from '../../services/api'
import { notify } from '@kyvg/vue3-notification'

export default {
  name: 'BetaBooster',
  components: { MasterAdminLayout },
  data() {
    return {
      form: {
        daily_price: 29,
        weekly_price: 149,
        extra_ads: 5
      }
    }
  },
  methods: {
    async fetchSettings() {
       try {
         const res = await api.get('/admin/beta/booster/settings')
         if (res.data.data.settings) {
           this.form = res.data.data.settings
         }
       } catch (e) {}
    },
    async saveSettings() {
       try {
         await api.post('/admin/beta/booster/settings', this.form)
         notify({ type: 'success', text: 'Booster settings saved' })
       } catch (e) {
         notify({ type: 'error', text: 'Failed to save' })
       }
    }
  },
  mounted() {
    this.fetchSettings()
  }
}
</script>
