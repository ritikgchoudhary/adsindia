<template>
  <MasterAdminLayout>
    <div class="beta-verified">
      <div class="tw-flex tw-items-center tw-justify-between tw-mb-8">
        <div>
          <router-link to="/master_admin/beta-features" class="tw-text-indigo-400 tw-text-sm tw-no-underline hover:tw-underline tw-mb-2 tw-inline-block">
            <i class="fas fa-arrow-left tw-mr-1"></i> Back to Hub
          </router-link>
          <h2 class="tw-text-2xl tw-font-bold tw-text-white tw-flex tw-items-center tw-gap-3">
             <i class="fas fa-check-circle tw-text-blue-400"></i> Verified Badge Settings
          </h2>
        </div>
      </div>

      <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-8">
        <div class="tw-bg-slate-900/40 tw-backdrop-blur-xl tw-border tw-border-white/10 tw-rounded-3xl tw-p-8">
          <h3 class="tw-text-white tw-font-bold tw-mb-6">Global Badge Settings</h3>
          
          <div class="tw-space-y-6">
            <div>
              <label class="tw-block tw-text-xs tw-font-bold tw-text-slate-500 tw-uppercase tw-mb-2">Badge Activation Fee (₹)</label>
              <input v-model="settings.price" type="number" class="tw-w-full tw-bg-white/5 tw-border tw-border-white/10 tw-rounded-xl tw-px-4 tw-py-3 tw-text-white focus:tw-border-blue-500 tw-outline-none" placeholder="99">
              <p class="tw-text-[10px] tw-text-slate-500 tw-mt-2">Standard suggested fee for Point #3 is ₹99.</p>
            </div>

            <div class="tw-flex tw-items-center tw-justify-between tw-p-4 tw-bg-white/5 tw-rounded-2xl tw-border tw-border-white/5">
              <div>
                <div class="tw-text-white tw-font-bold tw-text-sm">Auto-Verify on Payment</div>
                <div class="tw-text-[10px] tw-text-slate-500">Enable badge instantly after successful purchase.</div>
              </div>
              <button @click="settings.auto_enable = !settings.auto_enable" class="tw-relative tw-inline-flex tw-h-6 tw-w-11 tw-flex-shrink-0 tw-cursor-pointer tw-rounded-full tw-border-2 tw-border-transparent tw-transition-colors tw-duration-200" :class="settings.auto_enable ? 'tw-bg-blue-600' : 'tw-bg-slate-700'">
                <span class="tw-inline-block tw-h-5 tw-w-5 tw-transform tw-rounded-full tw-bg-white tw-transition tw-duration-200" :class="settings.auto_enable ? 'tw-translate-x-5' : 'tw-translate-x-0'"></span>
              </button>
            </div>

            <button @click="saveSettings" class="tw-w-full tw-bg-blue-600 hover:tw-bg-blue-700 tw-text-white tw-py-4 tw-rounded-2xl tw-font-bold tw-transition-all">
               Update Badge Policy
            </button>
          </div>
        </div>

        <div class="tw-bg-slate-900/40 tw-backdrop-blur-xl tw-border tw-border-white/10 tw-rounded-3xl tw-p-8">
           <h3 class="tw-text-white tw-font-bold tw-mb-6">Preview Badge</h3>
           <div class="tw-bg-white/5 tw-rounded-2xl tw-p-8 tw-text-center">
              <div class="tw-relative tw-inline-block tw-mb-4">
                 <div class="tw-w-20 tw-h-20 tw-bg-slate-800 tw-rounded-full tw-mx-auto tw-flex tw-items-center tw-justify-center tw-text-3xl tw-font-bold tw-text-white">
                    JD
                 </div>
                 <div class="tw-absolute -tw-bottom-1 -tw-right-1 tw-w-8 tw-h-8 tw-bg-blue-600 tw-rounded-full tw-border-4 tw-border-slate-900 tw-flex tw-items-center tw-justify-center">
                    <i class="fas fa-check tw-text-white tw-text-[10px]"></i>
                 </div>
              </div>
              <h4 class="tw-text-white tw-font-black tw-text-xl tw-flex tw-items-center tw-justify-center tw-gap-2">
                 John Doe <i class="fas fa-check-circle tw-text-blue-500 tw-text-sm"></i>
              </h4>
              <p class="tw-text-slate-500 tw-text-xs">Verified Ad Partner</p>
           </div>
           
           <div class="tw-mt-8 tw-space-y-3">
              <div class="tw-flex tw-items-center tw-gap-3 tw-text-xs tw-text-slate-400">
                 <i class="fas fa-info-circle tw-text-blue-400"></i>
                 <span>Badge increases referral trust by 45%.</span>
              </div>
              <div class="tw-flex tw-items-center tw-gap-3 tw-text-xs tw-text-slate-400">
                 <i class="fas fa-info-circle tw-text-blue-400"></i>
                 <span>Requires KYC to be verified before purchase.</span>
              </div>
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
  name: 'BetaVerified',
  components: { MasterAdminLayout },
  data() {
    return {
      settings: {
        price: 99,
        auto_enable: true
      }
    }
  },
  methods: {
    async fetchSettings() {
      // Mock for now or use GS
       try {
         const res = await api.get('/admin/beta/verified/settings')
         if (res.data.data.settings) {
           this.settings = res.data.data.settings
         }
       } catch (e) {}
    },
    async saveSettings() {
       try {
         await api.post('/admin/beta/verified/settings', this.settings)
         notify({ type: 'success', text: 'Verified Badge policy updated' })
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
