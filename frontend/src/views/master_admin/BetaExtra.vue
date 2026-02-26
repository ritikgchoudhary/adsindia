<template>
  <MasterAdminLayout>
    <div class="beta-extra">
      <div class="tw-flex tw-items-center tw-justify-between tw-mb-8">
        <div>
          <router-link to="/master_admin/beta-features" class="tw-text-indigo-400 tw-text-sm tw-no-underline hover:tw-underline tw-mb-2 tw-inline-block">
            <i class="fas fa-arrow-left tw-mr-1"></i> Back to Hub
          </router-link>
          <h2 class="tw-text-2xl tw-font-bold tw-text-white tw-flex tw-items-center tw-gap-3">
             <i class="fas fa-sliders-h tw-text-indigo-400"></i> Manual Price Configuration
          </h2>
          <p class="tw-text-slate-400 tw-text-sm tw-mt-1">Set manual pricing for all remaining upgrade points (2-17).</p>
        </div>
      </div>

      <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-3 tw-gap-6">
        <!-- Loop through points from your list that aren't already separate pages -->
        
        <!-- P2: Agent License -->
        <div class="config-card">
          <label class="config-label">#2 Agent License Fee (₹)</label>
          <input v-model="settings.p2_agent_license.price" type="number" class="config-input">
        </div>

        <!-- P4: Limit Upgrade -->
        <div class="config-card">
          <label class="config-label">#4 Withdrawal Limit Pass (₹)</label>
          <input v-model="settings.p4_limit_upgrade.price" type="number" class="config-input">
        </div>

        <!-- P5: 6-Month Pass -->
        <div class="config-card">
          <label class="config-label">#5 Withdrawal Fee-Free Pass (₹)</label>
          <input v-model="settings.p5_withdraw_pass.price" type="number" class="config-input">
        </div>

        <!-- P7: KYC Fast Track -->
        <div class="config-card">
          <label class="config-label">#7 KYC Fast Track Fee (₹)</label>
          <input v-model="settings.p7_kyc_fast.price" type="number" class="config-input">
        </div>

        <!-- P8: Account Reactivation -->
        <div class="config-card">
          <label class="config-label">#8 Reactivation/Recovery Fee (₹)</label>
          <input v-model="settings.p8_reactivation.price" type="number" class="config-input">
        </div>

        <!-- P10: Profit Booster -->
        <div class="config-card">
          <label class="config-label">#10 Profit Booster Pass (₹)</label>
          <input v-model="settings.p10_profit_boost.price" type="number" class="config-input">
        </div>

        <!-- P11: Comm Booster -->
        <div class="config-card">
          <label class="config-label">#11 Referral Comm. Booster (₹)</label>
          <input v-model="settings.p11_comm_booster.price" type="number" class="config-input">
        </div>

        <!-- P12: Level Unlock -->
        <div class="config-card">
          <label class="config-label">#12 Level Unlock (Lvl 2/3) (₹)</label>
          <input v-model="settings.p12_level_unlock.price" type="number" class="config-input">
        </div>

        <!-- P13: Senior Agent -->
        <div class="config-card">
          <label class="config-label">#13 Senior Agent Plan (₹)</label>
          <input v-model="settings.p13_senior_agent.price" type="number" class="config-input">
        </div>

        <!-- P14: Toolkit Store -->
        <div class="config-card">
          <label class="config-label">#14 Marketing Toolkit Store (₹)</label>
          <input v-model="settings.p14_toolkit_base.price" type="number" class="config-input">
        </div>

        <!-- P15: Premium Courses -->
        <div class="config-card">
          <label class="config-label">#15 Premium Masterclass (₹)</label>
          <input v-model="settings.p15_masterclass.price" type="number" class="config-input">
        </div>

        <!-- P16: Elite Content -->
        <div class="config-card">
          <label class="config-label">#16 Elite Training Pass (₹)</label>
          <input v-model="settings.p16_elite_training.price" type="number" class="config-input">
        </div>

        <!-- P17: Featured Partner -->
        <div class="config-card">
          <label class="config-label">#17 Featured Profile Fee (₹)</label>
          <input v-model="settings.p17_featured_partner.price" type="number" class="config-input">
        </div>
      </div>

      <div class="tw-mt-10">
        <button @click="saveSettings" :disabled="isSaving" class="tw-w-full tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-py-4 tw-rounded-2xl tw-font-bold tw-transition-all tw-flex tw-items-center tw-justify-center tw-gap-2">
           <i v-if="isSaving" class="fas fa-spinner fa-spin"></i>
           {{ isSaving ? 'Saving Changes...' : 'Save All Manual Prices' }}
        </button>
      </div>
    </div>
  </MasterAdminLayout>
</template>

<style scoped>
.config-card {
  @apply tw-bg-slate-900/40 tw-backdrop-blur-xl tw-border tw-border-white/10 tw-rounded-2xl tw-p-5;
}
.config-label {
  @apply tw-block tw-text-[10px] tw-font-bold tw-text-slate-500 tw-uppercase tw-mb-2;
}
.config-input {
  @apply tw-w-full tw-bg-white/5 tw-border tw-border-white/10 tw-rounded-xl tw-px-4 tw-py-3 tw-text-white tw-text-sm focus:tw-border-indigo-500 focus:tw-outline-none tw-transition-all;
}
</style>

<script>
import MasterAdminLayout from '../../components/master_admin/MasterAdminLayout.vue'
import api from '../../services/api'
import { notify } from '@kyvg/vue3-notification'

export default {
  name: 'BetaExtra',
  components: { MasterAdminLayout },
  data() {
    return {
      isSaving: false,
      settings: {
        p2_agent_license: { price: 1000 },
        p4_limit_upgrade: { price: 499 },
        p5_withdraw_pass: { price: 499 },
        p7_kyc_fast: { price: 249 },
        p8_reactivation: { price: 199 },
        p10_profit_boost: { price: 499 },
        p11_comm_booster: { price: 299 },
        p12_level_unlock: { price: 499 },
        p13_senior_agent: { price: 2000 },
        p14_toolkit_base: { price: 500 },
        p15_masterclass: { price: 999 },
        p16_elite_training: { price: 199 },
        p17_featured_partner: { price: 999 }
      }
    }
  },
  methods: {
    async fetchSettings() {
       try {
         const res = await api.get('/admin/beta/extra/settings')
         if (res.data.data.settings) {
           this.settings = { ...this.settings, ...res.data.data.settings }
         }
       } catch (e) {}
    },
    async saveSettings() {
       this.isSaving = true
       try {
         await api.post('/admin/beta/extra/settings', this.settings)
         notify({ type: 'success', text: 'All manual prices updated successfully' })
       } catch (e) {
          notify({ type: 'error', text: 'Failed to update settings' })
       } finally {
         this.isSaving = false
       }
    }
  },
  mounted() {
    this.fetchSettings()
  }
}
</script>
