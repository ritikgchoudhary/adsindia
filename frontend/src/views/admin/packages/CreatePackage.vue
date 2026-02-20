<template>
  <AdminLayout page-title="Create Package">
    <div class="ma-card">
      <div class="ma-card__header">
        <h5>Create New Package</h5>
        <router-link to="/admin/packages" class="btn btn-secondary btn-sm">
          <i class="fas fa-arrow-left"></i> Back to Packages
        </router-link>
      </div>
      <div class="ma-card__body">
        <form @submit.prevent="createPackage">
          <div class="mb-3">
            <label for="name" class="form-label">Package Name *</label>
            <input type="text" v-model="form.name" class="form-control" id="name" required placeholder="e.g. LEARN Lite" />
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="price" class="form-label">Price (₹) *</label>
              <input type="number" v-model="form.price" class="form-control" id="price" required min="0" step="0.01" />
            </div>
            <div class="col-md-6 mb-3">
              <label for="validity" class="form-label">Validity (Days) *</label>
              <input type="number" v-model="form.validity_days" class="form-control" id="validity" required min="1" />
            </div>
          </div>

          <div class="mb-3">
            <label for="level" class="form-label">Level *</label>
            <select v-model="form.level" class="form-control" id="level" required>
              <option value="1">Level 1 (Basic)</option>
              <option value="2">Level 2 (Pro)</option>
              <option value="3">Level 3 (Supreme)</option>
              <option value="4">Level 4 (Premium)</option>
              <option value="5">Level 5 (Premium+)</option>
            </select>
            <small class="text-muted">Higher levels unlock more courses.</small>
          </div>

          <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea v-model="form.description" class="form-control" id="description" rows="3" placeholder="Package description..."></textarea>
          </div>

          <div class="mb-3 form-check">
            <input type="checkbox" v-model="form.status" class="form-check-input" id="status" />
            <label class="form-check-label" for="status">Active Status</label>
          </div>

          <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary" :disabled="loading">
              {{ loading ? 'Creating...' : 'Create Package' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import AdminLayout from '../../../components/admin/AdminLayout.vue'
import api from '../../../services/api'

export default {
  name: 'AdminPackageCreate',
  components: { AdminLayout },
  setup() {
    const router = useRouter()
    const loading = ref(false)
    const form = ref({
      name: '',
      price: '',
      description: '',
      level: 1,
      validity_days: 365,
      status: true
    })

    const createPackage = async () => {
      loading.value = true
      try {
        const payload = {
          ...form.value,
          status: form.value.status ? 1 : 0
        }
        
        const response = await api.post('/admin/packages/create', payload)

        if (response.data.status === 'success') {
          if (window.notify) window.notify('success', 'Package created successfully')
          router.push('/admin/packages')
        } else {
           if (window.notify) window.notify('error', response.data.message || 'Failed to create')
        }
      } catch (error) {
        console.error('Error creating package:', error)
        const msg = error.response?.data?.message
        if (window.notify) window.notify('error', (typeof msg === 'object' ? Object.values(msg)[0] : msg) || 'Failed to create package')
      } finally {
        loading.value = false
      }
    }

    return { form, loading, createPackage }
  }
}
</script>

<style scoped>
.ma-card { background: white; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); padding: 20px; }
.ma-card__header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 15px; }
.ma-card__header h5 { margin: 0; font-weight: bold; }
</style>
