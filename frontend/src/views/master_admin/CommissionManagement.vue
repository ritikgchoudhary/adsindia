<template>
  <MasterAdminLayout page-title="Commission Management">
    <div class="ma-wrap">
      <div class="ma-card mb-4">
        <div class="ma-card__body">
          <div class="ma-tabs">
            <button type="button" class="ma-tab" :class="{ active: activeTab === 'direct' }" @click="activeTab = 'direct'">
              <i class="fas fa-user-friends"></i>
              Direct Affiliate (All Users)
            </button>
            <button type="button" class="ma-tab" :class="{ active: activeTab === 'agents' }" @click="activeTab = 'agents'">
              <i class="fas fa-user-tie"></i>
              Manage Agents
            </button>
          </div>
        </div>
      </div>

      <!-- Manage Agents -->
      <div v-if="activeTab === 'agents'" class="ma-card">
        <div class="ma-card__header ma-card__header--gradient">
          <div>
            <h5 class="ma-card__title"><i class="fas fa-user-tie me-2"></i>Individual Agent Commissions</h5>
            <p class="ma-card__subtitle">Set custom commission rules for specific agents. These settings override global defaults for the selected agent.</p>
          </div>
          <button class="ma-btn" @click="fetchAgents">
            <i class="fas fa-sync-alt"></i>
            Refresh
          </button>
        </div>

        <div class="ma-card__body">
          <div v-if="loadingAgents" class="ma-loading"><div class="ma-spinner"></div> Loading...</div>
          <div v-else class="table-responsive">
            <table class="ma-table">
              <thead>
                <tr>
                  <th>Agent</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="agent in agents" :key="agent.id">
                  <td>
                    <div class="tw-font-bold tw-text-slate-100">{{ agent.firstname }} {{ agent.lastname }}</div>
                    <div class="tw-text-xs tw-text-indigo-400 tw-font-bold">ADS{{ agent.id }}</div>
                  </td>
                  <td>
                    <div v-if="agent.settings" class="tw-flex tw-flex-wrap tw-gap-1">
                       <span class="ma-mini-badge tw-bg-emerald-500/10 tw-text-emerald-400">ADS: {{ displayMiniSetting(agent.settings, 'adplan') }}</span>
                       <span class="ma-mini-badge tw-bg-indigo-500/10 tw-text-indigo-400">COURSE: {{ displayMiniSetting(agent.settings, 'course') }}</span>
                       <span class="ma-mini-badge tw-bg-purple-500/10 tw-text-purple-400">PARTNER: {{ displayMiniSetting(agent.settings, 'partner') }}</span>
                       <span class="ma-mini-badge tw-bg-sky-500/10 tw-text-sky-400">CERT: {{ displayMiniSetting(agent.settings, 'certificate') }}</span>
                       <span class="ma-mini-badge tw-bg-amber-500/10 tw-text-amber-400">KYC: {{ displayMiniSetting(agent.settings, 'kyc') }}</span>
                    </div>
                    <div v-else class="tw-text-xs tw-text-slate-500 italic">No custom settings</div>
                  </td>
                  <td>
                    <button class="ma-btn ma-btn--primary" @click="openAgentSettings(agent)">
                      <i class="fas fa-cog"></i>
                      Manage Settings
                    </button>
                  </td>
                </tr>
                <tr v-if="!agents.length">
                  <td colspan="3" class="tw-text-center tw-py-8 tw-text-slate-400">No agents found. Mark users as agents from User Management.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Agent Settings Modal -->
      <div v-if="showAgentModal" class="ma-modal-overlay">
        <div class="ma-modal ma-modal--lg">
          <div class="ma-modal__header">
            <div>
               <h5 class="ma-modal__title">Manage Agent: {{ selectedAgent?.firstname }} {{ selectedAgent?.lastname }}</h5>
               <p class="ma-card__subtitle">ADS{{ selectedAgent?.id }} • {{ selectedAgent?.email }}</p>
            </div>
            <button class="ma-modal__close" @click="showAgentModal = false">&times;</button>
          </div>
          <div class="ma-modal__body">
            <div v-if="loadingAgentSettings" class="ma-loading tw-py-10 tw-justify-center"><div class="ma-spinner"></div> Loading Settings...</div>
            <div v-else-if="agentForm" class="ma-agent-settings">
              
              <!-- Tab Navigation for settings categories -->
              <div class="ma-sub-tabs mb-4">
                 <button class="ma-sub-tab" :class="{ active: settingsTab === 'general' }" @click="settingsTab = 'general'">General Fees</button>
                 <button class="ma-sub-tab" :class="{ active: settingsTab === 'courses' }" @click="settingsTab = 'courses'">Courses (5)</button>
                 <button class="ma-sub-tab" :class="{ active: settingsTab === 'ads' }" @click="settingsTab = 'ads'">Ads Plans (4)</button>
                 <button class="ma-sub-tab" :class="{ active: settingsTab === 'partners' }" @click="settingsTab = 'partners'">Partners (4)</button>
              </div>

              <!-- General Fees -->
              <div v-if="settingsTab === 'general'" class="ma-grid">
                <div class="ma-setting" v-for="block in generalBlocks" :key="block.key">
                  <div class="ma-setting__head">
                    <div class="ma-setting__title">{{ block.title }}</div>
                    <label class="ma-switch">
                      <input type="checkbox" v-model="agentForm[block.enabledKey]" />
                      <span class="ma-switch__slider"></span>
                    </label>
                  </div>
                  <div class="ma-setting__row">
                    <label class="ma-label">Mode</label>
                    <select class="ma-select" v-model="agentForm[block.modeKey]" :disabled="!agentForm[block.enabledKey]">
                      <option value="percent">Percent (%)</option>
                      <option value="fixed">Fixed (₹)</option>
                    </select>
                  </div>
                  <div class="ma-setting__row">
                    <label class="ma-label">Value</label>
                    <input type="number" step="0.01" class="ma-input" v-model.number="agentForm[block.valueKey]" :disabled="!agentForm[block.enabledKey]" />
                  </div>
                </div>
              </div>

              <!-- Courses Overrides -->
              <div v-if="settingsTab === 'courses'">
                <p class="tw-text-xs tw-text-slate-400 tw-mb-4">Set specific commission for each course package. This overrides the "Course Package Commission" general setting above.</p>
                <div class="table-responsive">
                  <table class="ma-table ma-table--small">
                    <thead>
                      <tr>
                        <th>Package</th>
                        <th>Price</th>
                        <th>Override Mode</th>
                        <th>Override Value</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="p in staticCourses" :key="p.id">
                        <td>{{ p.name }}</td>
                        <td>₹{{ formatAmount(p.price) }}</td>
                        <td>
                          <select class="ma-select ma-select--sm" v-model="granular.course[p.id].mode">
                             <option value="percent">Percent (%)</option>
                             <option value="fixed">Fixed (₹)</option>
                          </select>
                        </td>
                        <td>
                          <input type="number" step="0.01" class="ma-input ma-input--sm" v-model.number="granular.course[p.id].value" placeholder="0.00" />
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Ads Plans Overrides -->
              <div v-if="settingsTab === 'ads'">
                <p class="tw-text-xs tw-text-slate-400 tw-mb-4">Set specific commission for each ads plan.</p>
                <div class="table-responsive">
                  <table class="ma-table ma-table--small">
                    <thead>
                      <tr>
                        <th>Plan</th>
                        <th>Price</th>
                        <th>Override Mode</th>
                        <th>Override Value</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="p in staticAds" :key="p.id">
                        <td>{{ p.name }}</td>
                        <td>₹{{ formatAmount(p.price) }}</td>
                        <td>
                          <select class="ma-select ma-select--sm" v-model="granular.adplan[p.id].mode">
                             <option value="percent">Percent (%)</option>
                             <option value="fixed">Fixed (₹)</option>
                          </select>
                        </td>
                        <td>
                          <input type="number" step="0.01" class="ma-input ma-input--sm" v-model.number="granular.adplan[p.id].value" placeholder="0.00" />
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Partner Overrides -->
              <div v-if="settingsTab === 'partners'">
                <p class="tw-text-xs tw-text-slate-400 tw-mb-4">Set specific commission for each partner program.</p>
                <div class="table-responsive">
                  <table class="ma-table ma-table--small">
                    <thead>
                      <tr>
                        <th>Program</th>
                        <th>Price</th>
                        <th>Override Mode</th>
                        <th>Override Value</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="p in staticPartners" :key="p.id">
                        <td>{{ p.name }}</td>
                        <td>₹{{ formatAmount(p.price) }}</td>
                        <td>
                          <select class="ma-select ma-select--sm" v-model="granular.partner[p.id].mode">
                             <option value="percent">Percent (%)</option>
                             <option value="fixed">Fixed (₹)</option>
                          </select>
                        </td>
                        <td>
                          <input type="number" step="0.01" class="ma-input ma-input--sm" v-model.number="granular.partner[p.id].value" placeholder="0.00" />
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

            </div>
          </div>
          <div class="ma-modal__footer tw-p-4 tw-border-t tw-border-slate-700 tw-flex tw-justify-end tw-gap-3">
             <button class="ma-btn" @click="showAgentModal = false">Cancel</button>
             <button class="ma-btn ma-btn--primary" :disabled="savingAgent" @click="saveAgentSettings">
                <i v-if="savingAgent" class="fas fa-spinner fa-spin"></i>
                <i v-else class="fas fa-save"></i>
                Save Settings
             </button>
          </div>
        </div>
      </div>
      <!-- Direct Affiliate Commission -->
      <div v-if="activeTab === 'direct'" class="ma-card">
        <div class="ma-card__header ma-card__header--gradient">
          <div>
            <h5 class="ma-card__title"><i class="fas fa-user-friends me-2"></i>Direct Affiliate Commission</h5>
            <p class="ma-card__subtitle">Fixed amount per package. Credited only after payment success.</p>
          </div>
          <button class="ma-btn" @click="fetchDirect">
            <i class="fas fa-sync-alt"></i>
            Refresh
          </button>
        </div>

        <div class="ma-card__body">
          <div v-if="loadingDirect" class="ma-loading"><div class="ma-spinner"></div> Loading...</div>
          <div v-else class="table-responsive">
            <table class="ma-table">
              <thead>
                <tr>
                  <th>Package</th>
                  <th>Price</th>
                  <th>Enabled</th>
                  <th>Commission Amount</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="r in directRows" :key="r.package_id">
                  <td>
                    <div class="tw-font-bold tw-text-slate-100">{{ r.package_name }}</div>
                    <div class="tw-text-xs tw-text-slate-400">ID: {{ r.package_id }}</div>
                  </td>
                  <td class="tw-text-slate-200">₹{{ formatAmount(r.package_price) }}</td>
                  <td>
                    <label class="ma-switch">
                      <input type="checkbox" v-model="r.enabled" />
                      <span class="ma-switch__slider"></span>
                    </label>
                  </td>
                  <td>
                    <input
                      type="number"
                      min="0"
                      step="0.01"
                      class="ma-input"
                      v-model.number="r.commission_amount"
                      :disabled="!r.enabled"
                    />
                  </td>
                  <td>
                    <button class="ma-btn ma-btn--primary" @click="saveDirect(r)">
                      <i class="fas fa-save"></i>
                      Save
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </MasterAdminLayout>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import MasterAdminLayout from '../../components/master_admin/MasterAdminLayout.vue'
import api from '../../services/api'

export default {
  name: 'CommissionManagement',
  components: { MasterAdminLayout },
  setup() {
    const route = useRoute()
    const router = useRouter()
    const activeTab = ref(route.query.tab || 'direct')
    const savingKey = ref('')

    // Direct affiliate
    const loadingDirect = ref(false)
    const directRows = ref([])

    // Agent defaults
    const loadingDefaults = ref(false)
    const agentDefaults = ref(null)

    // Agents List
    const loadingAgents = ref(false)
    const agents = ref([])

    // Individual Agent Settings
    const showAgentModal = ref(false)
    const loadingAgentSettings = ref(false)
    const selectedAgent = ref(null)
    const agentForm = ref(null)
    const settingsTab = ref('general')
    const savingAgent = ref(false)
    const granular = ref({ course: {}, adplan: {}, partner: {} })

    const staticCourses = [
      { id: 1, name: 'AdsLite', price: 1499 },
      { id: 2, name: 'AdsPro', price: 2999 },
      { id: 3, name: 'AdsSupreme', price: 5999 },
      { id: 4, name: 'AdsPremium', price: 9999 },
      { id: 5, name: 'AdsPremium+', price: 15999 }
    ]
    const staticAds = [
      { id: 1, name: 'Starter Plan', price: 2999 },
      { id: 2, name: 'Popular Plan', price: 4999 },
      { id: 3, name: 'Premium Plan', price: 7499 },
      { id: 4, name: 'Elite Plan', price: 9999 }
    ]
    const staticPartners = [
      { id: 1, name: 'Associate Partner', price: 1999 },
      { id: 2, name: 'Executive Partner', price: 3999 },
      { id: 3, name: 'Master Partner', price: 5999 },
      { id: 4, name: 'Elite Partner', price: 9999 }
    ]

    // Upgrade rules
    const rulePlanType = ref('ad_plan')
    const loadingRules = ref(false)
    const rulePlans = ref([])
    const ruleForm = ref({})

    const formatAmount = (n) => {
      if (n == null || n === '') return '0.00'
      return parseFloat(n).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }

    const defaultBlocks = computed(() => [
      { key: 'registration', title: 'Registration / Direct Commission', enabledKey: 'registration_enabled', modeKey: 'registration_mode', valueKey: 'registration_value' },
      { key: 'kyc', title: 'KYC Fees Commission', enabledKey: 'kyc_enabled', modeKey: 'kyc_mode', valueKey: 'kyc_value' },
      { key: 'withdraw', title: 'Withdrawal Fees Commission', enabledKey: 'withdraw_fee_enabled', modeKey: 'withdraw_fee_mode', valueKey: 'withdraw_fee_value' },
      { key: 'upgrade', title: 'Upgrade Commission', enabledKey: 'upgrade_enabled', modeKey: 'upgrade_mode', valueKey: 'upgrade_value' },
      { key: 'adplan', title: 'General Ads Plans Commission', enabledKey: 'adplan_enabled', modeKey: 'adplan_mode', valueKey: 'adplan_value' },
      { key: 'course', title: 'General Course Packages Commission', enabledKey: 'course_enabled', modeKey: 'course_mode', valueKey: 'course_value' },
      { key: 'partner', title: 'General Partner Program Commission', enabledKey: 'partner_enabled', modeKey: 'partner_mode', valueKey: 'partner_value' },
      { key: 'certificate', title: 'Ad Certificate Commission', enabledKey: 'certificate_enabled', modeKey: 'certificate_mode', valueKey: 'certificate_value' },
    ])

    const generalBlocks = computed(() => [
      { key: 'certificate', title: 'Ad Certificate Commission', enabledKey: 'certificate_enabled', modeKey: 'certificate_mode', valueKey: 'certificate_value' },
      { key: 'withdraw', title: 'Withdrawal Fees Commission', enabledKey: 'withdraw_fee_enabled', modeKey: 'withdraw_fee_mode', valueKey: 'withdraw_fee_value' },
      { key: 'kyc', title: 'KYC Fees Commission', enabledKey: 'kyc_enabled', modeKey: 'kyc_mode', valueKey: 'kyc_value' },
      { key: 'special_discount', title: 'Special Discount Link Commission', enabledKey: 'special_discount_enabled', modeKey: 'special_discount_mode', valueKey: 'special_discount_value' }
    ])

    const fetchDirect = async () => {
      loadingDirect.value = true
      try {
        const res = await api.get('/admin/commissions/direct-affiliate')
        if (res.data?.status === 'success') {
          directRows.value = (res.data.data?.rows || []).map((r) => ({
            package_id: r.package_id,
            package_name: r.package_name,
            package_price: r.package_price,
            enabled: !!r.enabled,
            commission_amount: Number(r.commission_amount || 0),
          }))
        }
      } catch (e) {
        if (window.notify) window.notify('error', 'Failed to load direct commission rules')
      } finally {
        loadingDirect.value = false
      }
    }

    const saveDirect = async (row) => {
      savingKey.value = `d:${row.package_id}`
      try {
        const payload = { enabled: !!row.enabled, commission_amount: Number(row.commission_amount || 0) }
        const res = await api.post(`/admin/commissions/direct-affiliate/${row.package_id}`, payload)
        if (res.data?.status === 'success') {
          if (window.notify) window.notify('success', 'Saved')
        } else if (window.notify) {
          window.notify('error', res.data?.message?.[0] || 'Failed to save')
        }
      } catch (e) {
        if (window.notify) window.notify('error', 'Failed to save')
      } finally {
        savingKey.value = ''
      }
    }

    const fetchAgents = async () => {
      loadingAgents.value = true
      try {
        const res = await api.get('/admin/agents')
        if (res.data?.status === 'success') {
          agents.value = res.data.data?.agents || []
        }
      } catch (e) {
        if (window.notify) window.notify('error', 'Failed to load agents')
      } finally {
        loadingAgents.value = false
      }
    }

    const openAgentSettings = async (agent) => {
      selectedAgent.value = agent
      showAgentModal.value = true
      loadingAgentSettings.value = true
      settingsTab.value = 'general'
      try {
        const res = await api.get(`/admin/user/${agent.id}/agent-commissions`)
        if (res.data?.status === 'success') {
          agentForm.value = res.data.data?.settings || {}
          
          // Prepare granular settings
          const rawGranular = agentForm.value.granular_settings || {}
          const newGranular = { course: {}, adplan: {}, partner: {} }
          
          staticCourses.forEach(p => {
            const saved = rawGranular.course?.[p.id] || {}
            newGranular.course[p.id] = { mode: saved.mode || 'percent', value: saved.value != null ? Number(saved.value) : 0 }
          })
          staticAds.forEach(p => {
            const saved = rawGranular.adplan?.[p.id] || {}
            newGranular.adplan[p.id] = { mode: saved.mode || 'percent', value: saved.value != null ? Number(saved.value) : 0 }
          })
          staticPartners.forEach(p => {
             const saved = rawGranular.partner?.[p.id] || {}
             newGranular.partner[p.id] = { mode: saved.mode || 'percent', value: saved.value != null ? Number(saved.value) : 0 }
          })
          granular.value = newGranular
        }
      } finally {
        loadingAgentSettings.value = false
      }
    }

    const saveAgentSettings = async () => {
      savingAgent.value = true
      try {
        const payload = {
          ...agentForm.value,
          granular_settings: granular.value
        }
        const res = await api.post(`/admin/user/${selectedAgent.value.id}/agent-commissions`, payload)
        if (res.data?.status === 'success') {
          if (window.notify) window.notify('success', 'Agent settings updated')
          showAgentModal.value = false
        }
      } catch (e) {
        if (window.notify) window.notify('error', 'Failed to update agent settings')
      } finally {
        savingAgent.value = false
      }
    }

    const fetchDefaults = async () => {
      loadingDefaults.value = true
      try {
        const res = await api.get('/admin/commissions/agent-defaults')
        if (res.data?.status === 'success') {
          agentDefaults.value = res.data.data?.defaults || null
        }
      } catch (e) {
        if (window.notify) window.notify('error', 'Failed to load agent defaults')
      } finally {
        loadingDefaults.value = false
      }
    }

    const saveDefaults = async () => {
      savingKey.value = 'defaults'
      try {
        const res = await api.post('/admin/commissions/agent-defaults', agentDefaults.value || {})
        if (res.data?.status === 'success') {
          agentDefaults.value = res.data.data?.defaults || agentDefaults.value
          if (window.notify) window.notify('success', 'Defaults saved')
        } else if (window.notify) {
          window.notify('error', res.data?.message?.[0] || 'Failed to save defaults')
        }
      } catch (e) {
        if (window.notify) window.notify('error', 'Failed to save defaults')
      } finally {
        savingKey.value = ''
      }
    }

    const fetchUpgradeRules = async () => {
      loadingRules.value = true
      try {
        const res = await api.get('/admin/commissions/agent-upgrade-rules', { params: { plan_type: rulePlanType.value } })
        if (res.data?.status === 'success') {
          rulePlans.value = res.data.data?.plans || []
          const rules = res.data.data?.rules || []
          const byId = {}
          for (const r of rules) byId[Number(r.plan_id)] = r

          const form = {}
          for (const p of rulePlans.value) {
            const r = byId[Number(p.id)]
            form[p.id] = {
              enabled: r ? !!r.enabled : false,
              mode: r?.mode || 'percent',
              value: Number(r?.value || 0),
            }
          }
          ruleForm.value = form
        }
      } catch (e) {
        if (window.notify) window.notify('error', 'Failed to load upgrade rules')
      } finally {
        loadingRules.value = false
      }
    }

    const saveRule = async (planId) => {
      savingKey.value = `r:${rulePlanType.value}:${planId}`
      try {
        const f = ruleForm.value?.[planId]
        const payload = {
          plan_type: rulePlanType.value,
          plan_id: Number(planId),
          enabled: !!f?.enabled,
          mode: f?.mode || 'percent',
          value: Number(f?.value || 0),
        }
        const res = await api.post('/admin/commissions/agent-upgrade-rules', payload)
        if (res.data?.status === 'success') {
          if (window.notify) window.notify('success', 'Saved')
        } else if (window.notify) {
          window.notify('error', res.data?.message?.[0] || 'Failed to save')
        }
      } catch (e) {
        if (window.notify) window.notify('error', 'Failed to save')
      } finally {
        savingKey.value = ''
      }
    }

    onMounted(async () => {
      await fetchDirect()
      await fetchAgents()
      await fetchDefaults()
      await fetchUpgradeRules()
    })

    return {
      activeTab,
      savingKey,
      loadingDirect,
      directRows,
      loadingDefaults,
      agentDefaults,
      defaultBlocks,
      rulePlanType,
      loadingRules,
      rulePlans,
      ruleForm,
      formatAmount,
      fetchDirect,
      saveDirect,
      fetchAgents,
      loadingAgents,
      agents,
      openAgentSettings,
      showAgentModal,
      loadingAgentSettings,
      selectedAgent,
      agentForm,
      settingsTab,
      savingAgent,
      saveAgentSettings,
      generalBlocks,
      granular,
      staticCourses,
      staticAds,
      staticPartners,
      fetchDefaults,
      saveDefaults,
      fetchUpgradeRules,
      saveRule,

      displayMiniSetting: (settings, type) => {
        if (!settings) return '0%';
        const mode = settings[type + '_mode'] || 'percent';
        const val = settings[type + '_value'] || 0;
        return mode === 'percent' ? val + '%' : '₹' + val;
      }
    }
  },
}
</script>

<style scoped>
.ma-wrap { animation: maFade 0.35s ease-out; }
@keyframes maFade { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

.ma-card{
  background: rgba(30, 41, 59, 0.8);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 18px;
  overflow: hidden;
  box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
}
.ma-card__header{
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid rgba(255,255,255,0.06);
  display:flex; align-items:center; justify-content:space-between; gap: 12px;
}
.ma-card__header--gradient{
  background: linear-gradient(135deg, rgba(99,102,241,0.12) 0%, rgba(139,92,246,0.10) 100%);
  border-bottom: 1px solid rgba(99,102,241,0.22);
}
.ma-card__body{ padding: 1.1rem 1.25rem; }
.ma-card__title{ margin:0 0 0.25rem 0; color:#f1f5f9; font-weight:800; font-size:1.05rem; }
.ma-card__subtitle{ margin:0; color:#94a3b8; font-size:0.82rem; }

.ma-tabs{ display:flex; gap:10px; flex-wrap:wrap; }
.ma-tab{
  border: 1px solid rgba(255,255,255,0.10);
  background: rgba(15,23,42,0.55);
  color: rgba(255,255,255,0.75);
  padding: 10px 14px;
  border-radius: 12px;
  font-weight: 800;
  cursor: pointer;
  display:flex; align-items:center; gap:10px;
  transition: all 0.18s ease;
}
.ma-tab:hover{ background: rgba(255,255,255,0.06); color:#fff; transform: translateY(-1px); }
.ma-tab.active{
  background: rgba(99,102,241,0.25);
  border-color: rgba(99,102,241,0.35);
  color:#fff;
}

.ma-btn{
  border: 1px solid rgba(255,255,255,0.10);
  background: rgba(15,23,42,0.55);
  color: rgba(255,255,255,0.8);
  padding: 10px 12px;
  border-radius: 12px;
  font-weight: 800;
  cursor: pointer;
  transition: all 0.18s ease;
  display:inline-flex; align-items:center; gap:10px;
}
.ma-btn:hover:not(:disabled){ background: rgba(255,255,255,0.08); color:#fff; }
.ma-btn:disabled{ opacity: 0.6; cursor: not-allowed; }
.ma-btn--primary{
  background: rgba(99,102,241,0.75);
  border-color: rgba(99,102,241,0.6);
  color: white;
}
.ma-btn--primary:hover:not(:disabled){ background: rgba(99,102,241,0.9); }

.ma-table{ width:100%; border-collapse:collapse; min-width: 880px; }
.ma-table th{
  padding: 0.85rem 1rem;
  color: #94a3b8;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-size: 0.72rem;
  border-bottom: 2px solid rgba(99,102,241,0.18);
  text-align:left;
  background: rgba(15,23,42,0.35);
}
.ma-table td{
  padding: 0.85rem 1rem;
  border-bottom: 1px solid rgba(255,255,255,0.06);
  color: #cbd5e1;
  vertical-align: middle;
}

.ma-input{
  width: 180px;
  max-width: 100%;
  height: 40px;
  border-radius: 12px;
  border: 1px solid rgba(255,255,255,0.10);
  background: rgba(15,23,42,0.55);
  color: #e2e8f0;
  padding: 0 12px;
  outline: none;
}
.ma-select{
  height: 40px;
  border-radius: 12px;
  border: 1px solid rgba(255,255,255,0.10);
  background: rgba(15,23,42,0.55);
  color: #e2e8f0;
  padding: 0 12px;
  outline: none;
}
.ma-select option{ background: #1e293b; }

.ma-loading{ display:flex; align-items:center; gap: 12px; color:#cbd5e1; font-weight:700; }
.ma-spinner{ width:22px; height:22px; border: 3px solid rgba(255,255,255,0.12); border-top-color:#818cf8; border-radius:50%; animation: maSpin 0.7s linear infinite; }
@keyframes maSpin { to { transform: rotate(360deg); } }

.ma-switch{ position: relative; display:inline-flex; align-items:center; }
.ma-switch input{ display:none; }
.ma-switch__slider{
  width: 46px; height: 26px; border-radius: 999px;
  background: rgba(148,163,184,0.22);
  border: 1px solid rgba(255,255,255,0.10);
  transition: all 0.2s ease;
  position: relative;
}
.ma-switch__slider::after{
  content:'';
  width: 20px; height: 20px;
  border-radius: 999px;
  background: rgba(255,255,255,0.9);
  position:absolute; top: 50%; left: 3px;
  transform: translateY(-50%);
  transition: all 0.2s ease;
}
.ma-switch input:checked + .ma-switch__slider{
  background: rgba(16,185,129,0.28);
  border-color: rgba(16,185,129,0.35);
}
.ma-switch input:checked + .ma-switch__slider::after{ left: 23px; background: #34d399; }

.ma-grid{
  display:grid;
  grid-template-columns: repeat(1, minmax(0, 1fr));
  gap: 14px;
}
@media (min-width: 900px){
  .ma-grid{ grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
.ma-setting{
  border: 1px solid rgba(255,255,255,0.08);
  background: rgba(15,23,42,0.35);
  border-radius: 16px;
  padding: 14px;
}
.ma-setting__head{ display:flex; align-items:center; justify-content:space-between; gap: 10px; margin-bottom: 10px; }
.ma-setting__title{ color:#f1f5f9; font-weight:900; }
.ma-setting__row{ display:flex; align-items:center; justify-content:space-between; gap: 10px; margin-top: 10px; }
.ma-label{ color:#94a3b8; font-weight:800; font-size: 0.8rem; }
.ma-setting__hint{ margin-top: 10px; color: rgba(255,255,255,0.55); font-size: 0.76rem; }

.ma-mini-badge { background: rgba(99,102,241,0.15); color: #a5b4fc; padding: 2px 6px; border-radius: 4px; font-size: 0.7rem; font-weight: 800; border: 1px solid rgba(99,102,241,0.2); }

.ma-modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 1.5rem; }
.ma-modal { background: #1e293b; border: 1px solid rgba(255,255,255,0.1); border-radius: 18px; width: 100%; max-width: 600px; max-height: 90vh; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); }
.ma-modal--lg { max-width: 850px; }
.ma-modal__header { padding: 1.25rem 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.06); display: flex; align-items: center; justify-content: space-between; }
.ma-modal__title { margin: 0; color: #f1f5f9; font-weight: 700; font-size: 1.1rem; }
.ma-modal__close { background: none; border: none; color: #94a3b8; font-size: 1.25rem; cursor: pointer; }
.ma-modal__body { padding: 1.5rem; overflow-y: auto; flex: 1; }

.ma-sub-tabs { display: flex; gap: 8px; border-bottom: 1px solid rgba(255,255,255,0.06); padding-bottom: 12px; }
.ma-sub-tab { background: none; border: none; color: #94a3b8; font-weight: 800; font-size: 0.85rem; padding: 6px 12px; border-radius: 8px; cursor: pointer; transition: all 0.2s; }
.ma-sub-tab:hover { color: #f1f5f9; background: rgba(255,255,255,0.05); }
.ma-sub-tab.active { color: #fff; background: rgba(99,102,241,0.6); }

.ma-table--small th, .ma-table--small td { padding: 0.5rem 0.75rem; font-size: 0.82rem; }
.ma-input--sm { height: 32px; width: 120px; font-size: 0.8rem; }
.ma-select--sm { height: 32px; font-size: 0.8rem; padding: 0 8px; }
</style>

