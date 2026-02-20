<template>
  <MasterAdminLayout page-title="Settings">
    <div class="ma-settings">
      <div class="ma-card mb-4">
        <div class="ma-card__header ma-card__header--gradient">
          <div>
            <h5 class="ma-card__title"><i class="fas fa-address-book me-2"></i>Homepage Contact Section</h5>
            <p class="ma-card__subtitle">Edit the Email / Phone / Address shown on Home → Contact section (`/#contact`).</p>
          </div>
        </div>
        <div class="ma-card__body">
          <div v-if="loadError" class="alert alert-danger mb-0">
            <i class="fas fa-exclamation-circle me-2"></i>{{ loadError }}
            <button type="button" class="btn btn-sm btn-outline-danger ms-2" @click="fetchContactInfo">Retry</button>
          </div>

          <form v-else @submit.prevent="saveContactInfo" class="ma-form">
            <div class="ma-form-group">
              <label class="ma-form-label">Title</label>
              <input
                v-model="form.title"
                type="text"
                class="ma-form-input"
                placeholder="Contact Us"
              >
            </div>

            <div class="ma-form-group">
              <label class="ma-form-label">Office Address</label>
              <textarea
                v-model="form.address"
                class="ma-form-input"
                rows="3"
                placeholder="Enter office address"
              ></textarea>
            </div>

            <div class="ma-form-group">
              <label class="ma-form-label">Email Address</label>
              <input
                v-model="form.email_address"
                type="email"
                class="ma-form-input"
                placeholder="support@adsskillindia.in"
              >
            </div>

            <div class="ma-form-group">
              <label class="ma-form-label">Phone Number</label>
              <input
                v-model="form.contact_number"
                type="text"
                class="ma-form-input"
                placeholder="+91XXXXXXXXXX"
              >
              <small class="text-muted">Tip: include country code if needed (e.g. +91...)</small>
            </div>

            <div class="ma-form-actions">
              <button type="submit" class="ma-btn ma-btn--primary">
                Save Contact Info
              </button>
              <span v-if="saveMessage" class="ms-3" :class="saveSuccess ? 'text-success' : 'text-danger'">{{ saveMessage }}</span>
            </div>
          </form>
        </div>
      </div>

      <!-- Policy Pages -->
      <div class="ma-card mb-4">
        <div class="ma-card__header ma-card__header--gradient">
          <div>
            <h5 class="ma-card__title"><i class="fas fa-file-contract me-2"></i>Policy Pages</h5>
            <p class="ma-card__subtitle">Edit content for Refund Policy, Terms &amp; Condition, Disclaimer, and Privacy Policy (shown in footer → Useful Links).</p>
          </div>
        </div>
        <div class="ma-card__body">
          <div v-if="policiesLoadError" class="alert alert-danger mb-0">
            <i class="fas fa-exclamation-circle me-2"></i>{{ policiesLoadError }}
            <button type="button" class="btn btn-sm btn-outline-danger ms-2" @click="fetchPolicyPages">Retry</button>
          </div>

          <div v-else class="ma-policy-editor">
            <div class="ma-policy-sidebar">
              <button
                v-for="p in policyPages"
                :key="p.slug"
                type="button"
                class="ma-policy-tab"
                :class="{ 'is-active': selectedPolicySlug === p.slug }"
                @click="selectPolicy(p.slug)"
              >
                <div class="ma-policy-tab__title">{{ p.title }}</div>
                <div class="ma-policy-tab__slug">/policy/{{ p.slug }}</div>
              </button>
            </div>

            <div class="ma-policy-panel" v-if="selectedPolicy">
              <form @submit.prevent="savePolicyPage" class="ma-form">
                <div class="ma-form-group">
                  <label class="ma-form-label">Page Title</label>
                  <input v-model="policyForm.title" type="text" class="ma-form-input" placeholder="Enter title">
                </div>

                <div class="ma-form-group">
                  <label class="ma-form-label">Content (HTML allowed)</label>
                  <textarea
                    v-model="policyForm.html"
                    class="ma-form-input"
                    rows="14"
                    placeholder="<h3>...</h3><p>...</p>"
                  ></textarea>
                  <small class="text-muted">Tip: You can paste HTML. This content is rendered on the public page.</small>
                </div>

                <div class="ma-form-actions">
                  <button type="submit" class="ma-btn ma-btn--primary">
                    Save Policy Page
                  </button>
                  <a
                    v-if="selectedPolicySlug"
                    class="btn btn-sm btn-outline-light"
                    :href="`/policy/${selectedPolicySlug}`"
                    target="_blank"
                    rel="noopener noreferrer"
                  >Preview</a>
                  <span v-if="policySaveMessage" class="ms-2" :class="policySaveSuccess ? 'text-success' : 'text-danger'">{{ policySaveMessage }}</span>
                </div>
              </form>
            </div>

            <div v-else class="text-muted">No policy selected.</div>
          </div>
        </div>
      </div>

      <!-- Gateway Test (WatchPay) -->
      <div class="ma-card mb-4">
        <div class="ma-card__header ma-card__header--gradient">
          <div>
            <h5 class="ma-card__title"><i class="fas fa-credit-card me-2"></i>Gateway Test (WatchPay)</h5>
            <p class="ma-card__subtitle">Enter an amount and open WatchPay checkout to test gateway connectivity.</p>
          </div>
        </div>
        <div class="ma-card__body">
          <form class="ma-form" @submit.prevent="initiateGatewayTest">
            <div class="ma-form-group">
              <label class="ma-form-label">Amount (min 100)</label>
              <input v-model.number="gatewayTest.amount" type="number" min="100" step="1" class="ma-form-input" placeholder="990" required>
            </div>

            <div class="ma-form-actions">
              <button type="submit" class="ma-btn ma-btn--primary">
                Open Gateway
              </button>
              <button
                v-if="gatewayTest.trx"
                type="button"
                class="ma-btn ma-btn--outline"
                @click="checkGatewayTestStatus"
              >
                <i class="fas fa-sync-alt me-1"></i>
                Check Status
              </button>
              <span v-if="gatewayTest.message" class="ms-2" :class="gatewayTest.success ? 'text-success' : 'text-danger'">{{ gatewayTest.message }}</span>
            </div>

            <div v-if="gatewayTest.trx" class="mt-3">
              <div class="text-muted">TRX: <strong>{{ gatewayTest.trx }}</strong></div>
              <div v-if="gatewayTest.status" class="text-muted">Status: <strong>{{ gatewayTest.status }}</strong></div>
            </div>
          </form>
        </div>
      </div>

      <!-- Ads Settings (Ad Plans text + per-ad income) -->
      <div class="ma-card mb-4">
        <div class="ma-card__header ma-card__header--gradient">
          <div>
            <h5 class="ma-card__title"><i class="fas fa-bullhorn me-2"></i>Ads Settings</h5>
            <p class="ma-card__subtitle">Control per-ad income, enable/disable ads, new-user offer, and Ad Plans page text.</p>
          </div>
        </div>
        <div class="ma-card__body">
          <div v-if="adsLoadError" class="alert alert-danger mb-3">
            <i class="fas fa-exclamation-circle me-2"></i>{{ adsLoadError }}
            <button type="button" class="btn btn-sm btn-outline-danger ms-2" @click="fetchAdsSettings">Retry</button>
          </div>

          <form v-else class="ma-form" @submit.prevent="saveAdsSettings">
            <div class="row g-3">
              <div class="col-md-3">
                <label class="ma-form-label">Ads Enabled</label>
                <select v-model="adsForm.ads_enabled" class="ma-form-input">
                  <option :value="1">Enabled</option>
                  <option :value="0">Disabled</option>
                </select>
              </div>
              <div class="col-md-3">
                <label class="ma-form-label">Reward Mode</label>
                <select v-model="adsForm.ads_reward_mode" class="ma-form-input">
                  <option value="random">Random (Min/Max)</option>
                  <option value="fixed">Fixed</option>
                </select>
              </div>
              <div class="col-md-3" v-if="adsForm.ads_reward_mode === 'fixed'">
                <label class="ma-form-label">Fixed Reward / Ad</label>
                <input v-model.number="adsForm.ads_reward_fixed" type="number" step="0.01" class="ma-form-input" placeholder="5000">
              </div>
              <div class="col-md-3" v-else>
                <label class="ma-form-label">Min Reward / Ad</label>
                <input v-model.number="adsForm.ads_reward_min" type="number" step="0.01" class="ma-form-input" placeholder="1000">
              </div>
              <div class="col-md-3" v-if="adsForm.ads_reward_mode !== 'fixed'">
                <label class="ma-form-label">Max Reward / Ad</label>
                <input v-model.number="adsForm.ads_reward_max" type="number" step="0.01" class="ma-form-input" placeholder="5000">
              </div>
              <div class="col-md-3" v-if="adsForm.ads_reward_mode !== 'fixed'">
                <label class="ma-form-label">Step</label>
                <input v-model.number="adsForm.ads_reward_step" type="number" step="0.01" class="ma-form-input" placeholder="100">
              </div>
              <div class="col-md-3">
                <label class="ma-form-label">Multiplier</label>
                <input v-model.number="adsForm.ads_reward_multiplier" type="number" step="0.01" class="ma-form-input" placeholder="1">
                <small class="text-muted">Example: 1.2 = +20% reward</small>
              </div>

              <div class="col-12"><hr class="my-2"></div>

              <div class="col-md-3">
                <label class="ma-form-label">New User Offer</label>
                <select v-model="adsForm.new_user_offer_enabled" class="ma-form-input">
                  <option :value="1">Enabled</option>
                  <option :value="0">Disabled</option>
                </select>
              </div>
              <div class="col-md-3">
                <label class="ma-form-label">New User Ads Count</label>
                <input v-model.number="adsForm.new_user_offer_ads" type="number" min="0" step="1" class="ma-form-input" placeholder="2">
              </div>
              <div class="col-md-3">
                <label class="ma-form-label">New User Reward / Ad</label>
                <input v-model.number="adsForm.new_user_offer_reward" type="number" step="0.01" class="ma-form-input" placeholder="5000">
              </div>

              <div class="col-12"><hr class="my-2"></div>

              <div class="col-md-4">
                <label class="ma-form-label">Ad Plans Info Title</label>
                <input v-model="adsForm.ad_plans_info_title" type="text" class="ma-form-input" placeholder="How Ad Plans Work">
              </div>
              <div class="col-12">
                <label class="ma-form-label">Ad Plans Info Description (HTML allowed)</label>
                <textarea v-model="adsForm.ad_plans_info_description" class="ma-form-input" rows="3" placeholder="Purchase an ad plan to unlock..."></textarea>
              </div>
              <div class="col-12">
                <label class="ma-form-label">Ad Plans Bullets (one per line)</label>
                <textarea v-model="adsBulletsText" class="ma-form-input" rows="5" placeholder="Each ad takes 1 minute&#10;Earn {currency}{reward_min} - {currency}{reward_max} per ad"></textarea>
                <small class="text-muted">Placeholders: <code>{currency}</code>, <code>{reward_min}</code>, <code>{reward_max}</code>.</small>
              </div>
            </div>

            <div class="ma-form-actions mt-3">
              <button type="submit" class="ma-btn ma-btn--primary">
                Save Ads Settings
              </button>
              <button type="button" class="ma-btn ma-btn--outline" @click="fetchAdsLiability">
                <i class="fas fa-sync-alt me-1"></i>
                Refresh Liability
              </button>
              <span v-if="adsSaveMessage" class="ms-3" :class="adsSaveSuccess ? 'text-success' : 'text-danger'">{{ adsSaveMessage }}</span>
            </div>

            <div v-if="adsLiability.date" class="mt-3">
              <div class="text-muted">Liability date: <strong>{{ adsLiability.date }}</strong></div>
              <div class="text-muted">Active orders: <strong>{{ adsLiability.active_orders }}</strong> • Remaining ads today: <strong>{{ adsLiability.total_remaining_ads }}</strong></div>
              <div class="text-muted">Max per ad: <strong>₹{{ formatAmount(adsLiability.max_reward_per_ad || 0) }}</strong> • Expected per ad: <strong>₹{{ formatAmount(adsLiability.avg_reward_per_ad || 0) }}</strong></div>
              <div class="text-muted">Max liability: <strong>₹{{ formatAmount(adsLiability.max_liability || 0) }}</strong> • Expected liability: <strong>₹{{ formatAmount(adsLiability.expected_liability || 0) }}</strong></div>
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
    const form = reactive({
      title: '',
      address: '',
      email_address: '',
      contact_number: ''
    })

    const saving = ref(false)
    const loadError = ref('')
    const saveMessage = ref('')
    const saveSuccess = ref(false)

    // Policy pages
    const policyPages = ref([])
    const policiesLoadError = ref('')
    const selectedPolicySlug = ref('')
    const policySaving = ref(false)
    const policySaveMessage = ref('')
    const policySaveSuccess = ref(false)
    const policyForm = reactive({ title: '', html: '' })

    const selectedPolicy = ref(null)

    // WatchPay test
    const gatewayTest = reactive({
      amount: 990,
      loading: false,
      statusLoading: false,
      trx: '',
      status: '',
      message: '',
      success: false
    })

    // Ads settings (income + ad plans text)
    const adsLoadError = ref('')
    const adsSaving = ref(false)
    const adsSaveMessage = ref('')
    const adsSaveSuccess = ref(false)
    const adsLiabilityLoading = ref(false)
    const adsLiability = reactive({
      date: '',
      active_orders: 0,
      total_remaining_ads: 0,
      max_reward_per_ad: 0,
      avg_reward_per_ad: 0,
      max_liability: 0,
      expected_liability: 0
    })
    const adsForm = reactive({
      ads_enabled: 1,
      ads_reward_mode: 'random',
      ads_reward_fixed: 0,
      ads_reward_min: 1000,
      ads_reward_max: 5000,
      ads_reward_step: 100,
      ads_reward_multiplier: 1,
      new_user_offer_enabled: 1,
      new_user_offer_ads: 2,
      new_user_offer_reward: 5000,
      ad_plans_info_title: '',
      ad_plans_info_description: '',
      ad_plans_info_bullets: []
    })
    const adsBulletsText = ref('')

    const formatAmount = (n) => {
      if (n === null || n === undefined) return '0.00'
      return parseFloat(n).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }

    const fetchAdsSettings = async () => {
      adsLoadError.value = ''
      try {
        const res = await api.get('/admin/ads-income/settings')
        if (res.data?.status === 'success' && res.data.data) {
          Object.assign(adsForm, res.data.data)
          const bullets = Array.isArray(res.data.data.ad_plans_info_bullets) ? res.data.data.ad_plans_info_bullets : []
          adsBulletsText.value = bullets.join('\n')
        }
      } catch (e) {
        adsLoadError.value = e.response?.data?.message?.[0] || e.message || 'Failed to load ads settings'
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
          adsSaveMessage.value = 'Saved successfully.'
          setTimeout(() => { adsSaveMessage.value = '' }, 4000)
          fetchAdsSettings()
          fetchAdsLiability()
        } else {
          adsSaveSuccess.value = false
          adsSaveMessage.value = res.data?.message?.[0] || 'Save failed'
        }
      } catch (e) {
        adsSaveSuccess.value = false
        const msg = e.response?.data?.message?.[0] || e.response?.data?.message || e.message || 'Failed to save'
        adsSaveMessage.value = Array.isArray(msg) ? msg[0] : msg
      } finally {
        adsSaving.value = false
      }
    }

    const fetchAdsLiability = async () => {
      adsLiabilityLoading.value = true
      try {
        const res = await api.get('/admin/ads-income/liability')
        if (res.data?.status === 'success' && res.data.data) {
          Object.assign(adsLiability, res.data.data)
        }
      } catch (_) {
        // ignore
      } finally {
        adsLiabilityLoading.value = false
      }
    }

    const fetchContactInfo = async () => {
      loadError.value = ''
      try {
        const res = await api.get('/admin/contact-info')
        if (res.data?.status === 'success' && res.data.data) {
          form.title = res.data.data.title || ''
          form.address = res.data.data.address || ''
          form.email_address = res.data.data.email_address || ''
          form.contact_number = res.data.data.contact_number || ''
        }
      } catch (e) {
        loadError.value = e.response?.data?.message?.[0] || e.message || 'Failed to load contact info'
      }
    }

    const saveContactInfo = async () => {
      saving.value = true
      saveMessage.value = ''
      try {
        const res = await api.post('/admin/contact-info', {
          title: form.title || '',
          address: form.address || '',
          email_address: form.email_address || '',
          contact_number: form.contact_number || ''
        })
        if (res.data?.status === 'success') {
          saveSuccess.value = true
          saveMessage.value = 'Saved successfully. Homepage Contact section will show these values.'
          setTimeout(() => { saveMessage.value = '' }, 4000)
        } else {
          saveSuccess.value = false
          saveMessage.value = res.data?.message?.[0] || 'Save failed'
        }
      } catch (e) {
        saveSuccess.value = false
        saveMessage.value = e.response?.data?.message?.[0] || e.message || 'Failed to save'
      } finally {
        saving.value = false
      }
    }

    onMounted(() => { fetchContactInfo() })
    onMounted(() => { fetchAdsSettings(); fetchAdsLiability() })

    const fetchPolicyPages = async () => {
      policiesLoadError.value = ''
      try {
        const res = await api.get('/admin/policy-pages')
        if (res.data?.status === 'success' && Array.isArray(res.data.data)) {
          policyPages.value = res.data.data
          if (!selectedPolicySlug.value && policyPages.value.length) {
            selectPolicy(policyPages.value[0].slug)
          } else {
            // refresh selected object
            const cur = policyPages.value.find(p => p.slug === selectedPolicySlug.value)
            if (cur) {
              selectedPolicy.value = cur
              policyForm.title = cur.title || ''
              policyForm.html = cur.html || ''
            }
          }
        }
      } catch (e) {
        policiesLoadError.value = e.response?.data?.message?.[0] || e.message || 'Failed to load policy pages'
      }
    }

    const selectPolicy = (slug) => {
      selectedPolicySlug.value = slug
      const cur = policyPages.value.find(p => p.slug === slug) || null
      selectedPolicy.value = cur
      policyForm.title = cur?.title || ''
      policyForm.html = cur?.html || ''
      policySaveMessage.value = ''
    }

    const savePolicyPage = async () => {
      if (!selectedPolicySlug.value) return
      policySaving.value = true
      policySaveMessage.value = ''
      try {
        const res = await api.post(`/admin/policy-pages/${encodeURIComponent(selectedPolicySlug.value)}`, {
          title: policyForm.title || '',
          html: policyForm.html || ''
        })
        if (res.data?.status === 'success' && res.data.data) {
          policySaveSuccess.value = true
          policySaveMessage.value = 'Saved successfully.'
          // update local list
          const idx = policyPages.value.findIndex(p => p.slug === selectedPolicySlug.value)
          if (idx !== -1) {
            policyPages.value[idx] = { ...policyPages.value[idx], title: res.data.data.title, html: res.data.data.html }
          }
          selectedPolicy.value = policyPages.value[idx] || selectedPolicy.value
          setTimeout(() => { policySaveMessage.value = '' }, 4000)
        } else {
          policySaveSuccess.value = false
          policySaveMessage.value = res.data?.message?.[0] || 'Save failed'
        }
      } catch (e) {
        policySaveSuccess.value = false
        policySaveMessage.value = e.response?.data?.message?.[0] || e.message || 'Failed to save'
      } finally {
        policySaving.value = false
      }
    }

    onMounted(() => { fetchPolicyPages() })

    const initiateGatewayTest = async () => {
      gatewayTest.loading = true
      gatewayTest.message = ''
      gatewayTest.success = false
      gatewayTest.status = ''
      try {
        const res = await api.post('/admin/watchpay/test-payment', { amount: gatewayTest.amount })
        if (res.data?.status === 'success' && res.data.data?.payment_url) {
          gatewayTest.success = true
          gatewayTest.trx = res.data.data.trx || ''
          gatewayTest.message = 'Gateway initialized. Opening checkout...'
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
        const res = await api.get('/admin/watchpay/test-payment/status', { params: { trx: gatewayTest.trx } })
        if (res.data?.status === 'success' && res.data.data) {
          gatewayTest.status = res.data.data.status || ''
          gatewayTest.success = true
          gatewayTest.message = `Status: ${gatewayTest.status}`
        } else {
          gatewayTest.success = false
          gatewayTest.message = res.data?.message?.[0] || 'Failed to fetch status'
        }
      } catch (e) {
        gatewayTest.success = false
        gatewayTest.message = e.response?.data?.message?.[0] || e.message || 'Failed to fetch status'
      } finally {
        gatewayTest.statusLoading = false
      }
    }

    return {
      form,
      saving,
      loadError,
      saveMessage,
      saveSuccess,
      fetchContactInfo,
      saveContactInfo,

      policyPages,
      policiesLoadError,
      selectedPolicySlug,
      selectedPolicy,
      policyForm,
      policySaving,
      policySaveMessage,
      policySaveSuccess,
      fetchPolicyPages,
      selectPolicy,
      savePolicyPage,

      gatewayTest,
      initiateGatewayTest,
      checkGatewayTestStatus,

      // Ads settings
      adsLoadError,
      adsSaving,
      adsSaveMessage,
      adsSaveSuccess,
      adsForm,
      adsBulletsText,
      fetchAdsSettings,
      saveAdsSettings,
      adsLiability,
      adsLiabilityLoading,
      fetchAdsLiability,
      formatAmount,
    }
  }
}
</script>

<style scoped>
.ma-form { max-width: 700px; }
.ma-form-group { margin-bottom: 1.25rem; }
.ma-form-label { display: block; font-weight: 600; color: #e2e8f0; margin-bottom: 0.35rem; }
.ma-form-input {
  width: 100%;
  padding: 0.6rem 0.75rem;
  border-radius: 8px;
  border: 1px solid rgba(255,255,255,0.15);
  background: rgba(30, 41, 59, 0.5);
  color: #f1f5f9;
}
.ma-form-input::placeholder { color: #64748b; }
.ma-form-input:focus {
  outline: none;
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
}
.ma-form-actions { margin-top: 1.5rem; display: flex; align-items: center; flex-wrap: wrap; gap: 0.5rem; }

.ma-policy-editor {
  display: grid;
  grid-template-columns: 260px 1fr;
  gap: 1rem;
}
@media (max-width: 992px) {
  .ma-policy-editor { grid-template-columns: 1fr; }
}
.ma-policy-sidebar {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}
.ma-policy-tab {
  text-align: left;
  border: 1px solid rgba(255,255,255,0.12);
  background: rgba(15, 23, 42, 0.4);
  border-radius: 10px;
  padding: 0.7rem 0.8rem;
  color: #e2e8f0;
  transition: 0.15s ease;
}
.ma-policy-tab:hover { border-color: rgba(99, 102, 241, 0.55); }
.ma-policy-tab.is-active {
  border-color: rgba(99, 102, 241, 0.9);
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
}
.ma-policy-tab__title { font-weight: 700; font-size: 0.95rem; }
.ma-policy-tab__slug { font-size: 0.78rem; color: rgba(226, 232, 240, 0.55); margin-top: 0.15rem; }
.ma-policy-panel { min-width: 0; }
</style>
