<template>
  <MasterAdminLayout page-title="Gateway Management">
    <div class="ma-gateways-view">
      
      <!-- Premium Container -->
      <div class="ma-glass-card">
        
        <!-- Primary Gateways Header -->
        <div class="ma-card-header">
          <div class="tw-flex tw-items-center tw-gap-3">
            <div class="ma-header-dot"></div>
            <h3 class="ma-card-title">Primary Payment Methods</h3>
          </div>
          <span class="ma-card-subtitle">Fast & Automated</span>
        </div>

        <div class="ma-gateways-list">
          <div v-for="g in mainGateways" :key="g.id" class="ma-gateway-item">
            <div class="ma-gateway-inner">
              <div class="ma-gateway-left">
                <div class="ma-gateway-icon" :class="`icon--${g.alias.toLowerCase()}`">
                  <i v-if="g.alias.toLowerCase() === 'simplypay'" class="fas fa-credit-card"></i>
                  <i v-else-if="g.alias.toLowerCase() === 'rupeerush'" class="fas fa-indian-rupee-sign"></i>
                  <i v-else-if="g.alias.toLowerCase() === 'watchpay'" class="fas fa-eye"></i>
                  <i v-else class="fas fa-wallet"></i>
                </div>
                <div class="ma-gateway-info">
                  <span class="ma-gateway-name">{{ g.name }}</span>
                  <span class="ma-gateway-status-text" :class="{ 'is-on': g.status == 1 }">
                    {{ g.status == 1 ? 'Live & Active' : 'Currently Offline' }}
                  </span>
                </div>
              </div>
              <div class="ma-gateway-right">
                <label class="ma-premium-switch">
                  <input type="checkbox" :checked="g.status == 1" @change="toggleStatus(g)">
                  <span class="ma-premium-slider"></span>
                </label>
              </div>
            </div>
          </div>

          <!-- If empty -->
          <div v-if="!mainGateways.length" class="ma-empty-state">
            <i class="fas fa-exclamation-circle tw-mb-2 tw-text-xl"></i>
            <p>No primary gateways detected</p>
          </div>
        </div>
      </div>

      <!-- Custom QR Management -->
      <div class="ma-glass-card tw-mt-8">
        <div class="ma-card-header">
          <div class="tw-flex tw-items-center tw-gap-3">
            <div class="ma-header-dot is-orange"></div>
            <h3 class="ma-card-title">Custom QR Terminals</h3>
            <label class="ma-premium-switch tw-scale-[0.8] tw-ml-2">
              <input type="checkbox" :checked="customQrGateway?.status == 1" @change="toggleStatus(customQrGateway)">
              <span class="ma-premium-slider"></span>
            </label>
          </div>
          <button v-if="canAddQr" class="ma-btn-upload-mini" @click="triggerFileInput" :disabled="uploading">
            <i v-if="uploading" class="fas fa-spinner fa-spin"></i>
            <i v-else class="fas fa-plus"></i>
            <span>Add Terminal</span>
          </button>
          <input type="file" ref="fileInput" @change="uploadQr" multiple accept="image/*" style="display: none;">
        </div>

        <div class="ma-qr-terminals-grid">
          <!-- Active Terminals -->
          <div v-for="(img, index) in customQrGateway?.qr_images" :key="index" class="ma-terminal-box is-active">
            <div class="ma-terminal-frame">
              <img :src="img.url" alt="QR" class="ma-terminal-img">
              <div class="ma-terminal-hover">
                <button @click="removeQr(index)" class="ma-terminal-delete" title="Delete">
                  <i class="fas fa-trash-alt"></i>
                </button>
              </div>
            </div>
            <span class="ma-terminal-label">Terminal #{{ index + 1 }}</span>
          </div>

          <!-- Empty Slots -->
          <div v-for="n in emptyQrSlots" :key="`empty-${n}`" class="ma-terminal-box is-empty" @click="triggerFileInput">
            <div class="ma-terminal-frame">
              <i class="fas fa-qrcode"></i>
            </div>
            <span class="ma-terminal-label">Ready</span>
          </div>
        </div>
      </div>

      <!-- Footer / Others -->
      <div v-if="otherGateways.length" class="ma-footer-actions tw-mt-6">
        <button @click="showOther = !showOther" class="ma-btn-ghost">
          <span>{{ showOther ? 'Hide' : 'Manage' }} Secondary Gateways ({{ otherGateways.length }})</span>
          <i :class="['fas', showOther ? 'fa-chevron-up' : 'fa-chevron-down']"></i>
        </button>
        
        <div v-if="showOther" class="ma-secondary-list tw-mt-4">
          <div v-for="gateway in otherGateways" :key="gateway.id" class="ma-secondary-row">
            <span class="ma-secondary-name">{{ gateway.name }}</span>
            <button @click="toggleStatus(gateway)" class="ma-btn-tiny" :class="{ 'is-active': gateway.status == 1 }">
              {{ gateway.status == 1 ? 'Disable' : 'Enable' }}
            </button>
          </div>
        </div>
      </div>

    </div>
  </MasterAdminLayout>
</template>

<script>
import { ref, onMounted, computed } from 'vue'
import MasterAdminLayout from '../../components/master_admin/MasterAdminLayout.vue'
import api from '../../services/api'

export default {
  name: 'Gateways',
  components: { MasterAdminLayout },
  setup() {
    const gateways = ref([])
    const fileInput = ref(null)
    const uploading = ref(false)
    const showOther = ref(false)

    const notify = (status, msg) => {
      if (window.notify) window.notify(status, msg)
    }

    const fetchGateways = async () => {
      try {
        const res = await api.get('/admin/gateways')
        if (res.data?.status === 'success') {
          gateways.value = res.data.data.gateways
        }
      } catch (e) {
        notify('error', 'Failed to fetch gateways')
      }
    }

    const toggleStatus = async (gateway) => {
      const originalStatus = gateway.status
      gateway.status = originalStatus == 1 ? 0 : 1
      
      try {
        const res = await api.post(`/admin/gateway/${gateway.id}/toggle`)
        if (res.data?.status !== 'success') {
          gateway.status = originalStatus
          notify('error', 'Update Failed')
        } else {
          notify('success', `${gateway.name} Updated`)
        }
      } catch (e) {
        gateway.status = originalStatus
        notify('error', 'Connection Error')
      }
    }

    const MAIN_ALIASES = ['SimplyPay', 'simplypay', 'Rupeerush', 'rupeerush', 'watchpay', 'WatchPay']

    const mainGateways = computed(() => {
      const filtered = gateways.value.filter(g => MAIN_ALIASES.includes(g.alias))
      return filtered.sort((a,b) => {
        const orderMap = { 
          'watchpay': 0, 'WatchPay': 0,
          'simplypay': 1, 'SimplyPay': 1, 
          'rupeerush': 2, 'Rupeerush': 2 
        }
        return (orderMap[a.alias] ?? 5) - (orderMap[b.alias] ?? 5)
      })
    })

    const otherGateways = computed(() => {
      return gateways.value.filter(g => 
        !MAIN_ALIASES.includes(g.alias) && g.alias !== 'custom_qr'
      )
    })

    const customQrGateway = computed(() => gateways.value.find(g => g.alias === 'custom_qr'))
    const canAddQr = computed(() => (customQrGateway.value?.qr_images?.length || 0) < 6)
    const emptyQrSlots = computed(() => Math.max(0, 6 - (customQrGateway.value?.qr_images?.length || 0)))

    const triggerFileInput = () => {
      if (!canAddQr.value) {
        notify('error', 'Max 6 QR codes allowed')
        return
      }
      fileInput.value.click()
    }

    const uploadQr = async (event) => {
      const files = event.target.files
      if (!files.length) return
      
      if ((customQrGateway.value?.qr_images?.length || 0) + files.length > 6) {
        notify('error', 'Maximum limit 6 reached')
        return
      }

      uploading.value = true
      const formData = new FormData()
      for (let i = 0; i < files.length; i++) {
        formData.append('qr_images[]', files[i])
      }

      try {
        const res = await api.post('/admin/gateway/upload-qr', formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        })
        if (res.data?.status === 'success') {
          notify('success', 'Terminal Updated')
          await fetchGateways()
        }
      } catch (e) {
        notify('error', 'Upload failed')
      } finally {
        uploading.value = false
        if (fileInput.value) fileInput.value.value = ''
      }
    }

    const removeQr = async (index) => {
      if (!confirm('Permanently remove this terminal QR?')) return
      try {
        const res = await api.post(`/admin/gateway/remove-qr/${index}`)
        if (res.data?.status === 'success') {
          notify('success', 'Terminal Removed')
          await fetchGateways()
        }
      } catch (e) {
        notify('error', 'Action failed')
      }
    }

    onMounted(fetchGateways)

    return { 
      mainGateways,
      otherGateways,
      toggleStatus, 
      customQrGateway, 
      triggerFileInput, 
      fileInput, 
      uploadQr, 
      removeQr,
      canAddQr,
      emptyQrSlots,
      uploading,
      showOther
    }
  }
}
</script>

<style scoped>
.ma-gateways-view {
  max-width: 600px;
  margin: 0 auto;
  padding: 20px 0 60px;
}

/* Glass Card Base */
.ma-glass-card {
  background: #0f172a;
  border-radius: 40px;
  border: 1px solid rgba(255, 255, 255, 0.05);
  box-shadow: 0 32px 64px -16px rgba(0, 0, 0, 0.6);
  overflow: hidden;
  padding: 32px;
  position: relative;
}

.ma-glass-card::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 100px;
  background: linear-gradient(to bottom, rgba(99, 102, 241, 0.05), transparent);
  pointer-events: none;
}

/* Header */
.ma-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 32px;
  padding: 0 8px;
}

.ma-header-dot {
  width: 10px;
  height: 10px;
  background: #6366f1;
  border-radius: 50%;
  box-shadow: 0 0 12px #6366f1;
}
.ma-header-dot.is-orange { background: #f97316; box-shadow: 0 0 12px #f97316; }

.ma-card-title {
  margin: 0;
  color: #fff;
  font-size: 1rem;
  font-weight: 700;
  letter-spacing: -0.2px;
}

.ma-card-subtitle {
  font-size: 0.75rem;
  font-weight: 700;
  color: rgba(255, 255, 255, 0.2);
  text-transform: uppercase;
  letter-spacing: 1px;
}

/* List Items */
.ma-gateways-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.ma-gateway-item {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.03);
  border-radius: 28px;
  transition: all 0.3s ease;
}

.ma-gateway-item:hover {
  background: rgba(255, 255, 255, 0.04);
  border-color: rgba(255, 255, 255, 0.08);
  transform: translateY(-2px);
}

.ma-gateway-inner {
  padding: 18px 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.ma-gateway-left {
  display: flex;
  align-items: center;
  gap: 18px;
}

.ma-gateway-icon {
  width: 52px;
  height: 52px;
  border-radius: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
}

.icon--watchpay { background: #4f46e5; color: #fff; }
.icon--simplypay { background: #10b981; color: #fff; }
.icon--rupeerush { background: #f97316; color: #fff; }

.ma-gateway-name {
  display: block;
  font-size: 1.15rem;
  font-weight: 800;
  color: #fff;
  margin-bottom: 2px;
}

.ma-gateway-status-text {
  font-size: 0.75rem;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.2);
}
.ma-gateway-status-text.is-on { color: #10b981; }

/* Switch Style */
.ma-premium-switch {
  position: relative;
  display: inline-block;
  width: 50px;
  height: 28px;
}

.ma-premium-switch input { opacity: 0; width: 0; height: 0; }

.ma-premium-slider {
  position: absolute;
  cursor: pointer;
  inset: 0;
  background-color: #1e293b;
  transition: .4s cubic-bezier(0.4, 0, 0.2, 1);
  border-radius: 34px;
  border: 1px solid rgba(255, 255, 255, 0.05);
}

.ma-premium-slider:before {
  position: absolute;
  content: "";
  height: 20px;
  width: 20px;
  left: 4px;
  bottom: 3.5px;
  background-color: #64748b;
  transition: .4s cubic-bezier(0.4, 0, 0.2, 1);
  border-radius: 50%;
  box-shadow: 0 2px 4px rgba(0,0,0,0.5);
}

input:checked + .ma-premium-slider { background-color: #6366f1; border-color: #818cf8; }
input:checked + .ma-premium-slider:before { transform: translateX(22px); background-color: #fff; }

/* QR Section */
.ma-qr-terminals-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
}

.ma-terminal-box {
  display: flex;
  flex-direction: column;
  gap: 10px;
  align-items: center;
}

.ma-terminal-frame {
  width: 100%;
  aspect-ratio: 1;
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
  transition: all 0.3s ease;
}

.is-active .ma-terminal-frame { background: #fff; }
.is-empty .ma-terminal-frame { cursor: pointer; color: rgba(255, 255, 255, 0.05); font-size: 1.5rem; }
.is-empty .ma-terminal-frame:hover { background: rgba(255, 255, 255, 0.04); color: #f97316; border-color: #f9731650; }

.ma-terminal-img { max-width: 85%; max-height: 85%; object-fit: contain; }

.ma-terminal-hover {
  position: absolute;
  inset: 0;
  background: rgba(15, 23, 42, 0.9);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.2s;
  backdrop-filter: blur(4px);
}

.is-active:hover .ma-terminal-hover { opacity: 1; }

.ma-terminal-delete {
  width: 44px; height: 44px; border-radius: 12px;
  background: #ef4444; border: none; color: #fff; cursor: pointer;
  transition: transform 0.2s;
}
.ma-terminal-delete:hover { transform: scale(1.1); }

.ma-terminal-label {
  font-size: 0.65rem;
  font-weight: 700;
  color: rgba(255, 255, 255, 0.2);
  text-transform: uppercase;
}

/* Secondary Actions */
.ma-btn-ghost {
  width: 100%;
  padding: 16px;
  background: transparent;
  border: 1px dashed rgba(255, 255, 255, 0.05);
  border-radius: 20px;
  color: rgba(255, 255, 255, 0.3);
  font-size: 0.8rem;
  font-weight: 600;
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
}

.ma-btn-ghost:hover { background: rgba(255, 255, 255, 0.02); color: #fff; }

.ma-btn-upload-mini {
  background: rgba(249, 115, 22, 0.1);
  color: #f97316;
  border: 1px solid rgba(249, 115, 22, 0.2);
  padding: 8px 16px;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  transition: all 0.2s;
}
.ma-btn-upload-mini:hover { background: #f97316; color: #fff; transform: translateY(-1px); }

.ma-secondary-list {
  background: rgba(15, 23, 42, 0.4);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 24px;
  overflow: hidden;
}

.ma-secondary-row {
  padding: 12px 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid rgba(255, 255, 255, 0.03);
}

.ma-secondary-row:last-child { border-bottom: none; }

.ma-secondary-name { font-size: 0.8rem; font-weight: 600; color: rgba(255, 255, 255, 0.4); }

.ma-btn-tiny {
  padding: 4px 10px; border-radius: 6px; font-size: 0.7rem; font-weight: 700;
  background: rgba(255, 255, 255, 0.05); border: none; color: rgba(255, 255, 255, 0.3); cursor: pointer;
}
.ma-btn-tiny.is-active { background: rgba(239, 68, 68, 0.1); color: #ef4444; }

.ma-empty-state { text-align: center; padding: 40px; color: rgba(255, 255, 255, 0.1); }
</style>
