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

    onMounted(fetchAdsSettings)

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
</style>
