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
            <button type="button" class="ma-tab" :class="{ active: activeTab === 'agent_defaults' }" @click="activeTab = 'agent_defaults'">
              <i class="fas fa-user-shield"></i>
              Agent Defaults
            </button>
            <button type="button" class="ma-tab" :class="{ active: activeTab === 'upgrade_rules' }" @click="activeTab = 'upgrade_rules'">
              <i class="fas fa-sliders-h"></i>
              Upgrade Rules (Per Plan)
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

      <!-- Agent Defaults -->
      <div v-if="activeTab === 'agent_defaults'" class="ma-card">
        <div class="ma-card__header ma-card__header--gradient">
          <div>
            <h5 class="ma-card__title"><i class="fas fa-user-shield me-2"></i>Agent Commission Defaults</h5>
            <p class="ma-card__subtitle">These defaults are applied when you enable a user as Agent.</p>
          </div>
          <button class="ma-btn" @click="fetchDefaults">
            <i class="fas fa-sync-alt"></i>
            Refresh
          </button>
        </div>

        <div class="ma-card__body">
          <div v-if="loadingDefaults" class="ma-loading"><div class="ma-spinner"></div> Loading...</div>
          <div v-else-if="!agentDefaults" class="tw-text-slate-300">No defaults found.</div>

          <div v-else class="ma-grid">
            <div class="ma-setting" v-for="block in defaultBlocks" :key="block.key">
              <div class="ma-setting__head">
                <div class="ma-setting__title">{{ block.title }}</div>
                <label class="ma-switch">
                  <input type="checkbox" v-model="agentDefaults[block.enabledKey]" />
                  <span class="ma-switch__slider"></span>
                </label>
              </div>
              <div class="ma-setting__row">
                <label class="ma-label">Mode</label>
                <select class="ma-select" v-model="agentDefaults[block.modeKey]" :disabled="!agentDefaults[block.enabledKey]">
                  <option value="percent">Percent (%)</option>
                  <option value="fixed">Fixed (₹)</option>
                </select>
              </div>
              <div class="ma-setting__row">
                <label class="ma-label">Value</label>
                <input
                  type="number"
                  min="0"
                  step="0.01"
                  class="ma-input"
                  v-model.number="agentDefaults[block.valueKey]"
                  :disabled="!agentDefaults[block.enabledKey]"
                />
              </div>
              <div class="ma-setting__hint">
                Applied on gross amount before gateway charges.
              </div>
            </div>
          </div>

          <div class="tw-mt-5 tw-flex tw-justify-end">
            <button class="ma-btn ma-btn--primary" @click="saveDefaults">
              <i class="fas fa-save"></i>
              Save Defaults
            </button>
          </div>
        </div>
      </div>

      <!-- Upgrade Rules -->
      <div v-if="activeTab === 'upgrade_rules'" class="ma-card">
        <div class="ma-card__header ma-card__header--gradient">
          <div>
            <h5 class="ma-card__title"><i class="fas fa-sliders-h me-2"></i>Agent Upgrade Rules (Per Plan)</h5>
            <p class="ma-card__subtitle">Optional per-plan override. If a rule is enabled, it overrides the agent’s own registration/upgrade value for that plan.</p>
          </div>
          <div class="tw-flex tw-gap-2 tw-items-center">
            <select class="ma-select" v-model="rulePlanType" @change="fetchUpgradeRules">
              <option value="ad_plan">Ad Plans</option>
              <option value="package">Packages (AdsLite…)</option>
              <option value="course_plan">Course Plans</option>
            </select>
            <button class="ma-btn" @click="fetchUpgradeRules">
              <i class="fas fa-sync-alt"></i>
              Refresh
            </button>
          </div>
        </div>

        <div class="ma-card__body">
          <div v-if="loadingRules" class="ma-loading"><div class="ma-spinner"></div> Loading...</div>
          <div v-else class="table-responsive">
            <table class="ma-table">
              <thead>
                <tr>
                  <th>Plan</th>
                  <th>Price</th>
                  <th>Enabled</th>
                  <th>Mode</th>
                  <th>Value</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="p in rulePlans" :key="p.id">
                  <td>
                    <div class="tw-font-bold tw-text-slate-100">{{ p.name }}</div>
                    <div class="tw-text-xs tw-text-slate-400">ID: {{ p.id }}</div>
                  </td>
                  <td class="tw-text-slate-200">₹{{ formatAmount(p.price) }}</td>
                  <td>
                    <label class="ma-switch">
                      <input type="checkbox" v-model="ruleForm[p.id].enabled" />
                      <span class="ma-switch__slider"></span>
                    </label>
                  </td>
                  <td>
                    <select class="ma-select" v-model="ruleForm[p.id].mode" :disabled="!ruleForm[p.id].enabled">
                      <option value="percent">Percent (%)</option>
                      <option value="fixed">Fixed (₹)</option>
                    </select>
                  </td>
                  <td>
                    <input
                      type="number"
                      min="0"
                      step="0.01"
                      class="ma-input"
                      v-model.number="ruleForm[p.id].value"
                      :disabled="!ruleForm[p.id].enabled"
                    />
                  </td>
                  <td>
                    <button class="ma-btn ma-btn--primary" @click="saveRule(p.id)">
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
import MasterAdminLayout from '../../components/master_admin/MasterAdminLayout.vue'
import api from '../../services/api'

export default {
  name: 'CommissionManagement',
  components: { MasterAdminLayout },
  setup() {
    const route = useRoute()
    const activeTab = ref(route.query.tab || 'direct')
    const savingKey = ref('')

    // Direct affiliate
    const loadingDirect = ref(false)
    const directRows = ref([])

    // Agent defaults
    const loadingDefaults = ref(false)
    const agentDefaults = ref(null)

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
      { key: 'registration', title: 'Registration Commission', enabledKey: 'registration_enabled', modeKey: 'registration_mode', valueKey: 'registration_value' },
      { key: 'kyc', title: 'KYC Commission', enabledKey: 'kyc_enabled', modeKey: 'kyc_mode', valueKey: 'kyc_value' },
      { key: 'withdraw', title: 'Withdrawal Fee Commission', enabledKey: 'withdraw_fee_enabled', modeKey: 'withdraw_fee_mode', valueKey: 'withdraw_fee_value' },
      { key: 'upgrade', title: 'Upgrade Commission', enabledKey: 'upgrade_enabled', modeKey: 'upgrade_mode', valueKey: 'upgrade_value' },
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
      fetchDefaults,
      saveDefaults,
      fetchUpgradeRules,
      saveRule,
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
</style>

