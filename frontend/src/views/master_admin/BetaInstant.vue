<template>
  <MasterAdminLayout>
    <div class="beta-instant">
      <div class="tw-flex tw-items-center tw-justify-between tw-mb-8">
        <div>
          <router-link to="/master_admin/beta-features" class="tw-text-emerald-400 tw-text-sm tw-no-underline hover:tw-underline tw-mb-2 tw-inline-block">
            <i class="fas fa-arrow-left tw-mr-1"></i> Back to Hub
          </router-link>
          <h2 class="tw-text-2xl tw-font-bold tw-text-white tw-flex tw-items-center tw-gap-3">
             <i class="fas fa-bolt tw-text-emerald-400"></i> Instant Payout Settings
          </h2>
        </div>
      </div>

      <div class="tw-max-w-xl">
        <div class="tw-bg-slate-900/40 tw-backdrop-blur-xl tw-border tw-border-white/10 tw-rounded-3xl tw-p-8">
          <h3 class="tw-text-white tw-font-bold tw-mb-6">Payout Policy (Point #2)</h3>
          
          <div class="tw-space-y-6">
            <div>
              <label class="tw-block tw-text-xs tw-font-bold tw-text-slate-500 tw-uppercase tw-mb-2">Instant Fee (Static ₹)</label>
              <input v-model="settings.fee" type="number" class="tw-w-full tw-bg-white/5 tw-border tw-border-white/10 tw-rounded-xl tw-px-4 tw-py-3 tw-text-white focus:tw-border-emerald-500 tw-outline-none" placeholder="50">
              <p class="tw-text-[10px] tw-text-slate-500 tw-mt-2">This amount is added to the gateway processing fee when user chooses Instant.</p>
            </div>

            <div class="tw-flex tw-items-center tw-justify-between tw-p-4 tw-bg-white/5 tw-rounded-2xl tw-border tw-border-white/5">
              <div>
                <div class="tw-text-white tw-font-bold tw-text-sm">Highlight Priority TRX</div>
                <div class="tw-text-[10px] tw-text-slate-500">Show a pulse icon on admin withdrawal list for instant requests.</div>
              </div>
              <button @click="settings.highlight = !settings.highlight" class="tw-relative tw-inline-flex tw-h-6 tw-w-11 tw-flex-shrink-0 tw-cursor-pointer tw-rounded-full tw-border-2 tw-border-transparent tw-transition-colors tw-duration-200" :class="settings.highlight ? 'tw-bg-emerald-600' : 'tw-bg-slate-700'">
                <span class="tw-inline-block tw-h-5 tw-w-5 tw-transform tw-rounded-full tw-bg-white tw-transition tw-duration-200" :class="settings.highlight ? 'tw-translate-x-5' : 'tw-translate-x-0'"></span>
              </button>
            </div>

            <button @click="saveSettings" class="tw-w-full tw-bg-emerald-600 hover:tw-bg-emerald-700 tw-text-white tw-py-4 tw-rounded-2xl tw-font-bold tw-transition-all">
               Save Payout Policy
            </button>
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
  name: 'BetaInstant',
  components: { MasterAdminLayout },
  data() {
    return {
      settings: {
        fee: 50,
        highlight: true
      }
    }
  },
  methods: {
    async fetchSettings() {
       try {
         const res = await api.get('/admin/beta/instant/settings')
         if (res.data.data.settings) {
           this.settings = res.data.data.settings
         }
       } catch (e) {}
    },
    async saveSettings() {
       try {
         await api.post('/admin/beta/instant/settings', this.settings)
         notify({ type: 'success', text: 'Instant payout policy updated' })
       } catch (e) {
          notify({ type: 'error', text: 'Failed to update' })
       }
    }
  },
  mounted() {
    this.fetchSettings()
  }
}
</script>
