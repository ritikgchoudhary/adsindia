<template>
  <MasterAdminLayout page-title="Gateway Management">
    <div class="gateway-management">
      <!-- General Gateways List -->
      <div class="sections-grid">
        <div class="section-card">
          <div class="card-header">
            <h5 class="mb-0">All Payment Gateways</h5>
            <p class="text-muted small mb-0">Toggle status for automatic and manual gateways</p>
          </div>
          <div class="table-responsive mt-3">
            <table class="custom-table">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Alias</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="gateway in gateways" :key="gateway.id">
                  <td>{{ gateway.name }}</td>
                  <td><span class="badge badge--dark">{{ gateway.alias }}</span></td>
                  <td>
                    <span :class="['badge', gateway.status == 1 ? 'badge--success' : 'badge--warning']">
                      {{ gateway.status == 1 ? 'Enabled' : 'Disabled' }}
                    </span>
                  </td>
                  <td>
                    <button @click="toggleStatus(gateway)" class="btn-toggle" :class="{ 'btn-toggle--active': gateway.status == 1 }">
                      <i :class="['fas', gateway.status == 1 ? 'fa-toggle-on' : 'fa-toggle-off']"></i>
                      {{ gateway.status == 1 ? 'Disable' : 'Enable' }}
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Custom QR Management Section -->
        <div v-if="customQrGateway" class="section-card mt-4">
          <div class="card-header d-flex justify-content-between align-items-center">
            <div>
              <h5 class="mb-0">Custom QR System Management</h5>
              <p class="text-muted small mb-0">Upload and manage QR codes for manual payments</p>
            </div>
            <div class="upload-btn-wrapper">
              <button class="btn-primary" @click="triggerFileInput">
                <i class="fas fa-plus me-2"></i>Upload QR
              </button>
              <input type="file" ref="fileInput" @change="uploadQr" multiple accept="image/*" style="display: none;">
            </div>
          </div>

          <div class="qr-grid mt-4">
            <div v-for="(img, index) in customQrGateway.qr_images" :key="index" class="qr-card">
              <div class="qr-img-wrapper">
                <img :src="img.url" alt="QR Code" class="qr-image">
                <div class="qr-overlay">
                  <button @click="removeQr(index)" class="btn-delete" title="Remove QR">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </div>
              <p class="qr-name mt-2">{{ img.name }}</p>
            </div>
            <div v-if="!customQrGateway.qr_images?.length" class="empty-qr">
              <i class="fas fa-qrcode mb-3"></i>
              <p>No QR codes uploaded yet</p>
            </div>
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

    const notify = (status, msg) => {
      if (window.notify) window.notify(status, msg)
      else console.log(`[${status}] ${msg}`)
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
      try {
        const res = await api.post(`/admin/gateway/${gateway.id}/toggle`)
        if (res.data?.status === 'success') {
          gateway.status = gateway.status == 1 ? 0 : 1
          notify('success', res.data.message[0] || 'Status updated')
        }
      } catch (e) {
        notify('error', 'Failed to toggle status')
      }
    }

    const customQrGateway = computed(() => gateways.value.find(g => g.alias === 'custom_qr'))

    const triggerFileInput = () => fileInput.value.click()

    const uploadQr = async (event) => {
      const files = event.target.files
      if (!files.length) return

      const formData = new FormData()
      for (let i = 0; i < files.length; i++) {
        formData.append('qr_images[]', files[i])
      }

      try {
        const res = await api.post('/admin/gateway/upload-qr', formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        })
        if (res.data?.status === 'success') {
          notify('success', 'QR Code(s) uploaded successfully')
          fetchGateways()
        }
      } catch (e) {
        notify('error', 'Upload failed')
      }
    }

    const removeQr = async (index) => {
      if (!confirm('Are you sure you want to remove this QR code?')) return
      try {
        const res = await api.post(`/admin/gateway/remove-qr/${index}`)
        if (res.data?.status === 'success') {
          notify('success', 'QR Code removed')
          fetchGateways()
        }
      } catch (e) {
        notify('error', 'Failed to remove QR code')
      }
    }

    onMounted(fetchGateways)

    return { 
      gateways, 
      toggleStatus, 
      customQrGateway, 
      triggerFileInput, 
      fileInput, 
      uploadQr, 
      removeQr 
    }
  }
}
</script>

<style scoped>
.gateway-management {
  animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.section-card {
  background: rgba(0, 0, 0, 0.4);
  backdrop-filter: blur(20px) saturate(180%);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 20px;
  padding: 24px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
}

.card-header h5 {
  color: #f1f5f9;
  font-weight: 700;
  letter-spacing: -0.01em;
}

.card-header .text-muted {
  color: rgba(255, 255, 255, 0.5) !important;
}

.custom-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0 8px;
}

.custom-table th {
  padding: 12px 16px;
  color: rgba(255, 255, 255, 0.4);
  font-weight: 500;
  text-transform: uppercase;
  font-size: 0.75rem;
  letter-spacing: 0.05em;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.custom-table td {
  padding: 16px;
  color: #f1f5f9;
  background: rgba(255, 255, 255, 0.02);
  vertical-align: middle;
}

.custom-table tr td:first-child { border-radius: 12px 0 0 12px; }
.custom-table tr td:last-child { border-radius: 0 12px 12px 0; }

.badge {
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 0.75rem;
  font-weight: 600;
}

.badge--success { background: rgba(34, 197, 94, 0.2); color: #4ade80; border: 1px solid rgba(34, 197, 94, 0.2); }
.badge--warning { background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.2); }
.badge--dark { background: rgba(255, 255, 255, 0.05); color: rgba(255, 255, 255, 0.6); border: 1px solid rgba(255, 255, 255, 0.1); }

.btn-toggle {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #f1f5f9;
  padding: 8px 16px;
  border-radius: 10px;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-toggle:hover { background: rgba(255, 255, 255, 0.1); }
.btn-toggle--active { background: rgba(239, 68, 68, 0.1); color: #f87171; border-color: rgba(239, 68, 68, 0.2); }
.btn-toggle--active:hover { background: rgba(239, 68, 68, 0.2); }

.btn-primary {
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}

.btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(99, 102, 241, 0.4); }

.qr-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 20px;
}

.qr-card {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 16px;
  padding: 12px;
  text-align: center;
  transition: all 0.3s;
}

.qr-card:hover { transform: translateY(-4px); border-color: rgba(255, 255, 255, 0.15); }

.qr-img-wrapper {
  position: relative;
  aspect-ratio: 1;
  border-radius: 12px;
  overflow: hidden;
  background: white;
}

.qr-image { width: 100%; height: 100%; object-fit: contain; }

.qr-overlay {
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.3s;
}

.qr-img-wrapper:hover .qr-overlay { opacity: 1; }

.btn-delete {
  background: #ef4444;
  color: white;
  border: none;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  cursor: pointer;
  transition: transform 0.2s;
}

.btn-delete:hover { transform: scale(1.1); }

.qr-name {
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.4);
  word-break: break-all;
  margin: 0;
}

.empty-qr {
  grid-column: 1 / -1;
  padding: 60px;
  background: rgba(255, 255, 255, 0.02);
  border: 2px dashed rgba(255, 255, 255, 0.08);
  border-radius: 20px;
  text-align: center;
  color: rgba(255, 255, 255, 0.2);
}

.empty-qr i { font-size: 3rem; }
</style>
