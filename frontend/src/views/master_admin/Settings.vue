<template>
  <MasterAdminLayout page-title="System Settings">
    <div class="ma-settings">
      
      <!-- Ads Rewards Settings -->
      <div class="ma-card mb-4 animate__animated animate__fadeIn">
        <div class="ma-card__header ma-card__header--gradient">
          <div class="d-flex align-items-center">
            <div class="ma-icon-box me-3">
              <i class="fas fa-bullhorn"></i>
            </div>
            <div>
              <h5 class="ma-card__title">Ads Rewards Configuration</h5>
              <p class="ma-card__subtitle">Control earnings for new user offer and regular ad plans.</p>
            </div>
          </div>
        </div>
        <div class="ma-card__body">
          <div v-if="adsLoadError" class="alert alert-danger mb-4">
            <i class="fas fa-exclamation-circle me-2"></i>{{ adsLoadError }}
            <button type="button" class="btn btn-sm btn-outline-danger ms-2" @click="fetchAdsSettings">Retry</button>
          </div>

          <form v-else class="ma-form" @submit.prevent="saveAdsSettings">
            <!-- Global Switch -->
            <div class="row g-4 mb-4">
              <div class="col-md-6">
                <div class="ma-form-group">
                  <label class="ma-form-label">Ads System Status</label>
                  <select v-model="adsForm.ads_enabled" class="ma-form-input">
                    <option :value="1">✅ Enabled (System wide)</option>
                    <option :value="0">❌ Disabled</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="ma-form-group">
                  <label class="ma-form-label">Global Multiplier</label>
                  <div class="input-group">
                    <input v-model.number="adsForm.ads_reward_multiplier" type="number" step="0.01" class="ma-form-input" placeholder="1.0">
                    <span class="input-group-text bg-dark border-0 text-muted">x</span>
                  </div>
                  <small class="text-muted">Multiplies all rewards. Set 1.0 for no change.</small>
                </div>
              </div>
            </div>

            <!-- New User Offer Section -->
            <div class="section-divider mb-4">
              <span class="section-title">induction phase: New User Offer (2 Ads)</span>
            </div>

            <div class="row g-4 mb-4">
              <div class="col-md-4">
                <div class="ma-form-group">
                  <label class="ma-form-label">Offer Status</label>
                  <select v-model="adsForm.new_user_offer_enabled" class="ma-form-input">
                    <option :value="1">Enabled</option>
                    <option :value="0">Disabled</option>
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <div class="ma-form-group">
                  <label class="ma-form-label">Ad 1 Reward (₹)</label>
                  <input v-model.number="adsForm.new_user_offer_rewards[0]" type="number" step="1" class="ma-form-input" placeholder="5000">
                  <small class="text-muted">First 30-min ad.</small>
                </div>
              </div>
              <div class="col-md-4">
                <div class="ma-form-group">
                  <label class="ma-form-label">Ad 2 Reward (₹)</label>
                  <input v-model.number="adsForm.new_user_offer_rewards[1]" type="number" step="1" class="ma-form-input" placeholder="5000">
                  <small class="text-muted">Second 30-min ad.</small>
                </div>
              </div>
            </div>

            <!-- Regular Plans Section -->
            <div class="section-divider mb-4">
              <span class="section-title">Active Plans: Regular Ad Rewards (1 Min Ads)</span>
            </div>

            <div class="row g-4 mb-4">
              <div class="col-md-4">
                <div class="ma-form-group">
                  <label class="ma-form-label">Reward Mode</label>
                  <select v-model="adsForm.ads_reward_mode" class="ma-form-input">
                    <option value="random">Dynamic (Min/Max Range)</option>
                    <option value="fixed">Fixed Amount</option>
                  </select>
                </div>
              </div>

              <template v-if="adsForm.ads_reward_mode === 'random'">
                <div class="col-md-4">
                  <div class="ma-form-group">
                    <label class="ma-form-label">Min Reward / Ad (₹)</label>
                    <input v-model.number="adsForm.ads_reward_min" type="number" step="1" class="ma-form-input" placeholder="25">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="ma-form-group">
                    <label class="ma-form-label">Max Reward / Ad (₹)</label>
                    <input v-model.number="adsForm.ads_reward_max" type="number" step="1" class="ma-form-input" placeholder="70">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="ma-form-group">
                    <label class="ma-form-label">Random Step (₹)</label>
                    <input v-model.number="adsForm.ads_reward_step" type="number" step="1" class="ma-form-input" placeholder="1">
                    <small class="text-muted">Increments for randomization.</small>
                  </div>
                </div>
              </template>

              <div class="col-md-4" v-else>
                <div class="ma-form-group">
                  <label class="ma-form-label">Fixed Reward / Ad (₹)</label>
                  <input v-model.number="adsForm.ads_reward_fixed" type="number" step="1" class="ma-form-input" placeholder="50">
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="ma-form-actions mt-4 bg-dark p-3 rounded-4 border border-1 border-secondary">
              <button type="submit" class="ma-btn ma-btn--primary px-5" :disabled="adsSaving">
                <i v-if="adsSaving" class="fas fa-spinner fa-spin me-2"></i>
                <i v-else class="fas fa-save me-2"></i>
                Save All Ads Settings
              </button>
              <span v-if="adsSaveMessage" class="ms-3" :class="adsSaveSuccess ? 'text-success' : 'text-danger'">
                {{ adsSaveMessage }}
              </span>
            </div>
          </form>
        </div>
      </div>

      <!-- Withdrawal Settings -->
      <div class="ma-card mb-4 animate__animated animate__fadeIn">
        <div class="ma-card__header ma-card__header--gradient">
          <div class="d-flex align-items-center">
            <div class="ma-icon-box me-3 icon-orange">
              <i class="fas fa-money-bill-wave"></i>
            </div>
            <div>
              <h5 class="ma-card__title">Withdrawal Configuration</h5>
              <p class="ma-card__subtitle">Manage limits and service charges for different payout destinations.</p>
            </div>
          </div>
        </div>
        <div class="ma-card__body">
          <div v-if="withdrawLoadError" class="alert alert-danger mb-4">
            <i class="fas fa-exclamation-circle me-2"></i>{{ withdrawLoadError }}
            <button type="button" class="btn btn-sm btn-outline-danger ms-2" @click="fetchWithdrawalSettings">Retry</button>
          </div>

          <form v-else class="ma-form" @submit.prevent="saveWithdrawalSettings">
            
            <!-- User Bank Settings -->
            <div class="section-divider mb-4">
              <span class="section-title">User Withdrawal: Bank Transfer</span>
            </div>
            <div class="row g-4 mb-4">
              <div class="col-md-3">
                <div class="ma-form-group">
                  <label class="ma-form-label">Min Limit (₹)</label>
                  <input v-model.number="withdrawForm.user_bank_min_limit" type="number" class="ma-form-input" required>
                </div>
              </div>
              <div class="col-md-3">
                <div class="ma-form-group">
                  <label class="ma-form-label">Max Limit (₹)</label>
                  <input v-model.number="withdrawForm.user_bank_max_limit" type="number" class="ma-form-input" required>
                </div>
              </div>
              <div class="col-md-3">
                <div class="ma-form-group">
                  <label class="ma-form-label">Fixed Charge (₹)</label>
                  <input v-model.number="withdrawForm.user_bank_fixed_charge" type="number" class="ma-form-input" required>
                </div>
              </div>
              <div class="col-md-3">
                <div class="ma-form-group">
                  <label class="ma-form-label">Percent Charge (%)</label>
                  <input v-model.number="withdrawForm.user_bank_percent_charge" type="number" step="0.01" class="ma-form-input" required>
                </div>
              </div>
            </div>

            <!-- User UPI Settings -->
            <div class="section-divider mb-4">
              <span class="section-title">User Withdrawal: UPI Transfer</span>
            </div>
            <div class="row g-4 mb-4">
              <div class="col-md-3">
                <div class="ma-form-group">
                  <label class="ma-form-label">Min Limit (₹)</label>
                  <input v-model.number="withdrawForm.user_upi_min_limit" type="number" class="ma-form-input" required>
                </div>
              </div>
              <div class="col-md-3">
                <div class="ma-form-group">
                  <label class="ma-form-label">Max Limit (₹)</label>
                  <input v-model.number="withdrawForm.user_upi_max_limit" type="number" class="ma-form-input" required>
                </div>
              </div>
              <div class="col-md-3">
                <div class="ma-form-group">
                  <label class="ma-form-label">Fixed Charge (₹)</label>
                  <input v-model.number="withdrawForm.user_upi_fixed_charge" type="number" class="ma-form-input" required>
                </div>
              </div>
              <div class="col-md-3">
                <div class="ma-form-group">
                  <label class="ma-form-label">Percent Charge (%)</label>
                  <input v-model.number="withdrawForm.user_upi_percent_charge" type="number" step="0.01" class="ma-form-input" required>
                </div>
              </div>
            </div>

            <!-- Affiliate Settings -->
            <div class="section-divider mb-4">
              <span class="section-title">Affiliate Wallet Withdrawals</span>
            </div>
            <div class="row g-4 mb-4">
              <div class="col-md-3">
                <div class="ma-form-group">
                  <label class="ma-form-label">Min Limit (₹)</label>
                  <input v-model.number="withdrawForm.affiliate_min_limit" type="number" class="ma-form-input" required>
                </div>
              </div>
              <div class="col-md-3">
                <div class="ma-form-group">
                  <label class="ma-form-label">Max Limit (₹)</label>
                  <input v-model.number="withdrawForm.affiliate_max_limit" type="number" class="ma-form-input" required>
                </div>
              </div>
              <div class="col-md-3">
                <div class="ma-form-group">
                  <label class="ma-form-label">Fixed Charge (₹)</label>
                  <input v-model.number="withdrawForm.affiliate_fixed_charge" type="number" class="ma-form-input" required>
                </div>
              </div>
              <div class="col-md-3">
                <div class="ma-form-group">
                  <label class="ma-form-label">Percent Charge (%)</label>
                  <input v-model.number="withdrawForm.affiliate_percent_charge" type="number" step="0.01" class="ma-form-input" required>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="ma-form-actions mt-4 bg-dark p-3 rounded-4 border border-1 border-secondary">
              <button type="submit" class="ma-btn ma-btn--primary px-5" :disabled="withdrawSaving">
                <i v-if="withdrawSaving" class="fas fa-spinner fa-spin me-2"></i>
                <i v-else class="fas fa-save me-2"></i>
                Save Withdrawal Settings
              </button>
              <span v-if="withdrawSaveMessage" class="ms-3" :class="withdrawSaveSuccess ? 'text-success' : 'text-danger'">
                {{ withdrawSaveMessage }}
              </span>
            </div>
          </form>
        </div>
      </div>

      <!-- Gateway Test -->
      <div class="ma-card mb-4 animate__animated animate__fadeIn">
        <div class="ma-card__header ma-card__header--gradient">
          <div class="d-flex align-items-center">
            <div class="ma-icon-box me-3 icon-purple">
              <i class="fas fa-vial"></i>
            </div>
            <div>
              <h5 class="ma-card__title">Gateway Connectivity Test</h5>
              <p class="ma-card__subtitle">Verify if your payment gateways are correctly integrated.</p>
            </div>
          </div>
        </div>
        <div class="ma-card__body">
          <form class="ma-form" @submit.prevent="initiateGatewayTest">
            <div class="row g-4 align-items-end">
              <div class="col-md-4">
                <div class="ma-form-group">
                  <label class="ma-form-label">Select Gateway</label>
                  <select v-model="gatewayTest.gateway" class="ma-form-input" required>
                    <option value="watchpay">WatchPay</option>
                    <option value="simplypay">SimplyPay</option>
                    <option value="rupeerush">RupeeRush</option>
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <div class="ma-form-group">
                  <label class="ma-form-label">Amount (min 100)</label>
                  <div class="input-group">
                    <span class="input-group-text bg-dark border-0 text-muted">₹</span>
                    <input v-model.number="gatewayTest.amount" type="number" min="100" step="1" class="ma-form-input" placeholder="100" required>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="ma-form-group">
                  <button type="submit" class="ma-btn ma-btn--outline w-100 py-2" :disabled="gatewayTest.loading">
                    <i v-if="gatewayTest.loading" class="fas fa-spinner fa-spin me-2"></i>
                    <i v-else class="fas fa-external-link-alt me-2"></i>
                    Init Test Payment
                  </button>
                </div>
              </div>
            </div>

            <div v-if="gatewayTest.trx" class="mt-4 p-3 bg-dark rounded-4 border border-1 border-secondary animate__animated animate__slideInDown">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small">TEST SESSION</span>
                <span :class="gatewayTest.success ? 'text-success' : 'text-warning'" class="small fw-bold">{{ gatewayTest.status || 'Pending Payment' }}</span>
              </div>
              <h4 class="mb-3 text-white">TRX: {{ gatewayTest.trx }}</h4>
              
              <div class="d-flex gap-2">
                <button type="button" class="ma-btn ma-btn--primary py-1 px-3 fs-7" @click="checkGatewayTestStatus" :disabled="gatewayTest.statusLoading">
                  <i v-if="gatewayTest.statusLoading" class="fas fa-spinner fa-spin me-1"></i>
                  Sync Status
                </button>
                <div v-if="gatewayTest.message" class="ms-2 align-self-center fst-italic text-info small">
                  {{ gatewayTest.message }}
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>

      <!-- History Management (Clear Logs) -->
      <div class="ma-card mb-4 animate__animated animate__fadeIn">
        <div class="ma-card__header ma-card__header--danger">
          <div class="d-flex align-items-center">
            <div class="ma-icon-box me-3 icon-danger">
              <i class="fas fa-trash-alt"></i>
            </div>
            <div>
              <h5 class="ma-card__title">History Management (Reset Admin View)</h5>
              <p class="ma-card__subtitle">Hide old logs from Admin dashboard without deleting database records.</p>
            </div>
          </div>
        </div>
        <div class="ma-card__body">
          <div class="ma-maintenance-grid">
            <div v-for="action in clearActions" :key="action.key" class="ma-maintenance-item">
              <div class="ma-maintenance-item__content">
                <div class="ma-maintenance-item__title">{{ action.label }}</div>
                <div class="ma-maintenance-item__desc">{{ action.desc }}</div>
              </div>
              <button
                class="ma-btn-clear"
                :class="{ 'ma-btn-clear--loading': cleaning === action.key }"
                :disabled="cleaning"
                @click="confirmClear(action)"
              >
                <i v-if="cleaning === action.key" class="fas fa-spinner fa-spin"></i>
                <i v-else class="fas fa-trash"></i>
                <span>Clear</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </MasterAdminLayout>
</template>

<script>
import MasterAdminLayout from '../../components/master_admin/MasterAdminLayout.vue'
import { ref, reactive, onMounted } from 'vue'
import api from '../../services/api'

export default {
  name: 'MasterAdminSettings',
  components: { MasterAdminLayout },
  setup() {
    // Gateway test state
    const gatewayTest = reactive({
      gateway: 'watchpay',
      amount: 100,
      loading: false,
      statusLoading: false,
      trx: '',
      status: '',
      message: '',
      success: false
    })

    // Ads settings state
    const adsLoadError = ref('')
    const adsSaving = ref(false)
    const adsSaveMessage = ref('')
    const adsSaveSuccess = ref(false)
    const adsBulletsText = ref('')
    
    const adsForm = reactive({
      ads_enabled: 1,
      ads_reward_mode: 'random',
      ads_reward_fixed: 0,
      ads_reward_min: 25,
      ads_reward_max: 70,
      ads_reward_step: 1,
      ads_reward_multiplier: 1,
      new_user_offer_enabled: 1,
      new_user_offer_ads: 2,
      new_user_offer_reward: 5000,
      new_user_offer_rewards: [5000, 5000],
      ad_plans_info_title: '',
      ad_plans_info_bullets: []
    })

    const fetchAdsSettings = async () => {
      adsLoadError.value = ''
      try {
        const res = await api.get('/admin/ads-income/settings')
        if (res.data?.status === 'success' && res.data.data) {
          const data = res.data.data
          Object.assign(adsForm, data)
          
          // Ensure new user specific rewards array exists
          if (!Array.isArray(adsForm.new_user_offer_rewards) || adsForm.new_user_offer_rewards.length < 2) {
            adsForm.new_user_offer_rewards = [
              data.new_user_offer_reward || 5000,
              data.new_user_offer_reward || 5000
            ]
          }

          const bullets = Array.isArray(data.ad_plans_info_bullets) ? data.ad_plans_info_bullets : []
          adsBulletsText.value = bullets.join('\n')
        }
      } catch (e) {
        adsLoadError.value = 'Failed to load ads settings: ' + (e.response?.data?.message?.[0] || e.message)
      }
    }

    const saveAdsSettings = async () => {
      adsSaving.value = true
      adsSaveMessage.value = ''
      adsSaveSuccess.value = false
      try {
        const bullets = String(adsBulletsText.value || '')
          .split('\n')
          .map(s => s.trim())
          .filter(Boolean)

        const payload = {
          ...adsForm,
          ads_enabled: Number(adsForm.ads_enabled) ? 1 : 0,
          new_user_offer_enabled: Number(adsForm.new_user_offer_enabled) ? 1 : 0,
          ad_plans_info_bullets: bullets
        }

        const res = await api.post('/admin/ads-income/settings', payload)
        if (res.data?.status === 'success') {
          adsSaveSuccess.value = true
          adsSaveMessage.value = 'Settings updated successfully.'
          setTimeout(() => { adsSaveMessage.value = '' }, 4000)
        } else {
          adsSaveMessage.value = res.data?.message?.[0] || 'Save failed'
        }
      } catch (e) {
        const msg = e.response?.data?.message?.[0] || e.message || 'Failed to save'
        adsSaveMessage.value = msg
      } finally {
        adsSaving.value = false
      }
    }

    const initiateGatewayTest = async () => {
      gatewayTest.loading = true
      gatewayTest.message = ''
      gatewayTest.success = false
      gatewayTest.status = ''
      try {
        const res = await api.post('/admin/gateway-test/initiate', { 
          gateway: gatewayTest.gateway,
          amount: gatewayTest.amount 
        })
        if (res.data?.status === 'success' && res.data.data?.payment_url) {
          gatewayTest.success = true
          gatewayTest.trx = res.data.data.trx || ''
          gatewayTest.message = 'Gateway initialized. Launching checkout...'
          window.open(res.data.data.payment_url, '_blank', 'noopener,noreferrer')
        } else {
          gatewayTest.message = res.data?.message?.[0] || 'Failed to initiate gateway test'
        }
      } catch (e) {
        gatewayTest.message = e.response?.data?.message?.[0] || e.message || 'Failed to initiate gateway test'
      } finally {
        gatewayTest.loading = false
      }
    }

    const checkGatewayTestStatus = async () => {
      if (!gatewayTest.trx) return
      gatewayTest.statusLoading = true
      gatewayTest.message = ''
      try {
        const res = await api.get('/admin/gateway-test/status', { 
          params: { 
            trx: gatewayTest.trx,
            gateway: gatewayTest.gateway
          } 
        })
        if (res.data?.status === 'success' && res.data.data) {
          gatewayTest.status = res.data.data.status || ''
          gatewayTest.success = gatewayTest.status === 'success'
          gatewayTest.message = `Sync Complete: ${gatewayTest.status.toUpperCase()}`
        } else {
          gatewayTest.success = false
          gatewayTest.message = res.data?.message?.[0] || 'Failed to sync status'
        }
      } catch (e) {
        gatewayTest.success = false
        gatewayTest.message = e.response?.data?.message?.[0] || e.message || 'Failed to fetch status'
      } finally {
        gatewayTest.statusLoading = false
      }
    }

    const cleaning = ref(null)
    const clearActions = [
      { key: 'orders', label: 'Clear All Orders', desc: 'Hides gateway attempts & plan purchases from Admin view. User data is safe.', path: '/admin/clear-history/orders' },
      { key: 'deposits', label: 'Clear Deposits', desc: 'Hides deposit history from Admin view. Does NOT delete user balance.', path: '/admin/clear-history/deposits' },
      { key: 'withdrawals', label: 'Clear Withdrawals', desc: 'Hides withdrawal logs from Admin view. Does NOT affect user history.', path: '/admin/clear-history/withdrawals' },
      { key: 'transactions', label: 'Clear Transactions', desc: 'Hides all transaction logs from Admin view. Safe for users.', path: '/admin/clear-history/transactions' },
      { key: 'ledger', label: 'Clear Account Ledger', desc: 'Hides all previous ledger summaries from Admin view. Reversible.', path: '/admin/clear-history/ledger' },
    ]

    const confirmClear = async (action) => {
      if (!confirm(`Are you sure you want to ${action.label.toLowerCase()}? This will hide older logs from your Admin view for a cleaner slate. No actual data will be deleted from the database.`)) return
      const secondCheck = prompt(`Type "CLEAR" to confirm:`)
      if (secondCheck !== 'CLEAR') return

      cleaning.value = action.key
      try {
        const res = await api.post(action.path)
        if (res.data?.status === 'success') {
          if (window.notify) window.notify('success', res.data.message || 'Cleared successfully')
        } else {
          if (window.notify) window.notify('error', res.data?.message || 'Failed to clear')
        }
      } catch (e) {
        console.error('Clear error', e)
        if (window.notify) window.notify('error', 'Operation failed')
      } finally {
        cleaning.value = null
      }
    }

    // Withdrawal settings state
    const withdrawLoadError = ref('')
    const withdrawSaving = ref(false)
    const withdrawSaveMessage = ref('')
    const withdrawSaveSuccess = ref(false)

    const withdrawForm = reactive({
      user_bank_min_limit: 10000,
      user_bank_max_limit: 1000000,
      user_bank_fixed_charge: 0,
      user_bank_percent_charge: 0,
      user_upi_min_limit: 10000,
      user_upi_max_limit: 1000000,
      user_upi_fixed_charge: 0,
      user_upi_percent_charge: 0,
      affiliate_min_limit: 10000,
      affiliate_max_limit: 1000000,
      affiliate_fixed_charge: 0,
      affiliate_percent_charge: 0
    })

    const fetchWithdrawalSettings = async () => {
      withdrawLoadError.value = ''
      try {
        const res = await api.get('/admin/withdrawal-settings')
        if (res.data?.status === 'success' && res.data.data) {
          const d = res.data.data
          withdrawForm.user_bank_min_limit = d.user_bank.min_limit
          withdrawForm.user_bank_max_limit = d.user_bank.max_limit
          withdrawForm.user_bank_fixed_charge = d.user_bank.fixed_charge
          withdrawForm.user_bank_percent_charge = d.user_bank.percent_charge

          withdrawForm.user_upi_min_limit = d.user_upi.min_limit
          withdrawForm.user_upi_max_limit = d.user_upi.max_limit
          withdrawForm.user_upi_fixed_charge = d.user_upi.fixed_charge
          withdrawForm.user_upi_percent_charge = d.user_upi.percent_charge

          withdrawForm.affiliate_min_limit = d.affiliate.min_limit
          withdrawForm.affiliate_max_limit = d.affiliate.max_limit
          withdrawForm.affiliate_fixed_charge = d.affiliate.fixed_charge
          withdrawForm.affiliate_percent_charge = d.affiliate.percent_charge
        }
      } catch (e) {
        withdrawLoadError.value = 'Failed to load withdrawal settings'
      }
    }

    const saveWithdrawalSettings = async () => {
      withdrawSaving.value = true
      withdrawSaveMessage.value = ''
      withdrawSaveSuccess.value = false
      try {
        const res = await api.post('/admin/withdrawal-settings', withdrawForm)
        if (res.data?.status === 'success') {
          withdrawSaveSuccess.value = true
          withdrawSaveMessage.value = 'Withdrawal settings updated.'
          setTimeout(() => { withdrawSaveMessage.value = '' }, 4000)
        }
      } catch (e) {
        withdrawSaveMessage.value = e.response?.data?.message?.[0] || 'Save failed'
      } finally {
        withdrawSaving.value = false
      }
    }

    onMounted(() => {
      fetchAdsSettings()
      fetchWithdrawalSettings()
    })

    return {
      gatewayTest,
      initiateGatewayTest,
      checkGatewayTestStatus,
      adsLoadError,
      adsSaving,
      adsSaveMessage,
      adsSaveSuccess,
      adsForm,
      adsBulletsText,
      fetchAdsSettings,
      saveAdsSettings,
      withdrawLoadError,
      withdrawSaving,
      withdrawSaveMessage,
      withdrawSaveSuccess,
      withdrawForm,
      fetchWithdrawalSettings,
      saveWithdrawalSettings,
      cleaning, 
      clearActions, 
      confirmClear
    }
  }
}
</script>

<style scoped>
.ma-settings {
  max-width: 900px;
  margin: 0 auto;
}

.ma-card {
  background: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 24px;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.ma-card__header--gradient {
  padding: 1.75rem;
  background: linear-gradient(to right, rgba(99, 102, 241, 0.15), rgba(79, 70, 229, 0.05));
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.ma-icon-box {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.25rem;
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}

.icon-purple {
  background: linear-gradient(135deg, #a855f7 0%, #8b5cf6 100%);
  box-shadow: 0 4px 12px rgba(168, 85, 247, 0.3);
}

.icon-orange {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
  box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
}

.ma-card__title { font-size: 1.25rem; font-weight: 800; color: white; margin: 0; letter-spacing: -0.5px; }
.ma-card__subtitle { font-size: 0.9rem; color: rgba(255, 255, 255, 0.45); margin: 0.25rem 0 0 0; }
.ma-card__body { padding: 2rem; }

.section-divider {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.section-divider::after {
  content: "";
  flex: 1;
  height: 1px;
  background: rgba(255,255,255,0.06);
}

.section-title {
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  color: #6366f1;
  letter-spacing: 1px;
}

.ma-form-group { margin-bottom: 0; }
.ma-form-label { display: block; font-weight: 700; color: #ccd6f6; margin-bottom: 0.5rem; font-size: 0.85rem; }

.ma-form-input {
  width: 100%;
  padding: 0.8rem 1rem;
  border-radius: 12px;
  border: 1px solid rgba(255,255,255,0.1);
  background: rgba(30, 41, 59, 0.4);
  color: #f1f5f9;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  font-size: 0.95rem;
}

.ma-form-input:focus {
  outline: none;
  border-color: #6366f1;
  background: rgba(30, 41, 59, 0.7);
  box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
}

.input-group-text {
  border-radius: 0 12px 12px 0;
  padding-left: 1rem;
  padding-right: 1rem;
}

.ma-btn {
  padding: 0.8rem 1.5rem;
  border-radius: 14px;
  font-weight: 700;
  transition: all 0.2s;
  cursor: pointer;
}

.ma-btn--primary {
  background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
  color: white;
  border: none;
}

.ma-btn--primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4);
}

.ma-btn--outline {
  background: transparent;
  border: 2px solid rgba(99, 102, 241, 0.4);
  color: #e2e8f0;
}

.ma-btn--outline:hover {
  border-color: #6366f1;
  background: rgba(99, 102, 241, 0.1);
  color: white;
}

.fs-7 { font-size: 0.85rem; }

/* Maintenance */
.ma-card__header--danger {
  padding: 1.75rem;
  background: linear-gradient(to right, rgba(239, 68, 68, 0.15), rgba(185, 28, 28, 0.05));
  border-bottom: 1px solid rgba(239, 68, 68, 0.05);
}

.icon-danger {
  background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.ma-maintenance-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1rem;
}

.ma-maintenance-item {
  background: rgba(15, 23, 42, 0.4);
  border: 1px solid rgba(255,255,255,0.06);
  border-radius: 12px;
  padding: 1rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
}

.ma-maintenance-item__content { flex: 1; }
.ma-maintenance-item__title { font-size: 0.9rem; font-weight: 700; color: #f1f5f9; margin-bottom: 2px; }
.ma-maintenance-item__desc { font-size: 0.75rem; color: #64748b; }

.ma-btn-clear {
  padding: 0.5rem 1rem;
  border-radius: 8px;
  border: 1px solid rgba(239,68,68,0.4);
  background: rgba(239,68,68,0.1);
  color: #f87171;
  font-size: 0.8rem;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.2s;
}

.ma-btn-clear:hover:not(:disabled) {
  background: rgba(239,68,68,0.25);
  color: #fff;
  transform: translateY(-2px);
}

.ma-btn-clear:disabled { opacity: 0.5; cursor: not-allowed; }
.ma-btn-clear--loading { background: rgba(255,255,255,0.05); color: #94a3b8; border-color: rgba(255,255,255,0.1); }
</style>
