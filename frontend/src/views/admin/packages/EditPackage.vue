<template>
  <AdminLayout page-title="Edit Package">
    <div class="ma-card">
      <div class="ma-card__header">
        <h5>Edit Package</h5>
        <router-link to="/admin/packages" class="btn btn-secondary btn-sm">
          <i class="fas fa-arrow-left"></i> Back to Packages
        </router-link>
      </div>
      <div class="ma-card__body">
        <form @submit.prevent="updatePackage">
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
              {{ loading ? 'Updating...' : 'Update Package' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import AdminLayout from '../../../components/admin/AdminLayout.vue'
import api from '../../../services/api'

export default {
  name: 'AdminPackageEdit',
  components: { AdminLayout },
  setup() {
    const router = useRouter()
    const route = useRoute()
    const loading = ref(false)

    const form = ref({
      id: null,
      name: '',
      price: '',
      description: '',
      level: 1,
      validity_days: 365,
      status: true
    })

    const fetchPackage = async () => {
      loading.value = true
      try {
        const id = route.params.id
        const response = await api.get(`/admin/packages/edit/${id}`)
        if (response.data.status === 'success') {
          const pkg = response.data.data.package
          form.value.id = pkg.id
          form.value.name = pkg.name
          form.value.price = pkg.price
          form.value.description = pkg.description
          form.value.level = pkg.level
          form.value.validity_days = pkg.validity_days
          form.value.status = pkg.status === 1
        }
      } catch (error) {
        console.error('Error fetching package:', error)
        if (window.notify) window.notify('error', 'Failed to load package data')
      } finally {
        loading.value = false
      }
    }

    const updatePackage = async () => {
      loading.value = true
      try {
        const payload = {
          name: form.value.name,
          price: form.value.price,
          description: form.value.description,
          level: form.value.level,
          validity_days: form.value.validity_days,
          status: form.value.status ? 1 : 0
        }
        
        const response = await api.post(`/admin/packages/update/${form.value.id}`, payload)

        if (response.data.status === 'success') {
          if (window.notify) window.notify('success', 'Package updated successfully')
          router.push('/admin/packages')
        } else {
             if (window.notify) window.notify('error', response.data.message || 'Failed to update')
        }
      } catch (error) {
        console.error('Error updating package:', error)
        const msg = error.response?.data?.message
        if (window.notify) window.notify('error', (typeof msg === 'object' ? Object.values(msg)[0] : msg) || 'Failed to update package')
      } finally {
        loading.value = false
      }
    }

    onMounted(() => {
      fetchPackage()
    })

    return { form, loading, updatePackage }
  }
}
</script>

<style scoped>
.ma-card { background: white; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); padding: 20px; }
.ma-card__header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 15px; }
.ma-card__header h5 { margin: 0; font-weight: bold; }
</style>
