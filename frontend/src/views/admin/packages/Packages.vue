<template>
  <AdminLayout page-title="Manage Packages">
    <div class="ma-card">
      <div class="ma-card__header d-flex justify-content-between align-items-center p-4 border-bottom">
        <h5 class="m-0 fw-bold">Packages Management</h5>
        <router-link to="/admin/packages/create" class="btn btn-primary btn-sm">
          <i class="fas fa-plus me-2"></i> Add New Package
        </router-link>
      </div>
      <div class="ma-card__body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="bg-light">
              <tr>
                <th class="px-4 py-3 border-bottom">ID</th>
                <th class="px-4 py-3 border-bottom">Name</th>
                <th class="px-4 py-3 border-bottom">Price</th>
                <th class="px-4 py-3 border-bottom">Validity</th>
                <th class="px-4 py-3 border-bottom">Level</th>
                <th class="px-4 py-3 border-bottom">Status</th>
                <th class="px-4 py-3 border-bottom text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading">
                <td colspan="7" class="text-center py-5">
                  <div class="spinner-border text-primary" role="status"></div>
                </td>
              </tr>
              <tr v-else-if="packages.length === 0">
                <td colspan="7" class="text-center text-muted py-5">No packages found</td>
              </tr>
              <tr v-else v-for="pkg in packages" :key="pkg.id">
                <td class="px-4 py-3">#{{ pkg.id }}</td>
                <td class="px-4 py-3 fw-bold">{{ pkg.name }}</td>
                <td class="px-4 py-3 text-primary fw-bold">₹{{ formatAmount(pkg.price) }}</td>
                <td class="px-4 py-3">{{ pkg.validity_days }} Days</td>
                <td class="px-4 py-3">
                  <span class="badge bg-info text-dark">Level {{ pkg.level }}</span>
                </td>
                <td class="px-4 py-3">
                  <span :class="pkg.status === 1 ? 'badge bg-success' : 'badge bg-danger'">
                    {{ pkg.status === 1 ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td class="px-4 py-3 text-end">
                  <div class="d-flex gap-2 justify-content-end">
                    <router-link :to="`/admin/packages/edit/${pkg.id}`" class="btn btn-sm btn-outline-primary" title="Edit">
                      <i class="fas fa-edit"></i>
                    </router-link>
                    <button @click="deletePackage(pkg.id)" class="btn btn-sm btn-outline-danger" title="Delete">
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script>
import { ref, onMounted } from 'vue'
import AdminLayout from '../../../components/admin/AdminLayout.vue'
import api from '../../../services/api'

export default {
  name: 'AdminPackages',
  components: { AdminLayout },
  setup() {
    const packages = ref([])
    const loading = ref(true)

    const formatAmount = (amount) => {
      return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }

    const fetchPackages = async () => {
      loading.value = true
      try {
        const response = await api.get('/admin/packages/all')
        if (response.data.status === 'success') {
          packages.value = response.data.data.packages || []
        }
      } catch (error) {
        console.error('Error fetching packages:', error)
        if (window.notify) window.notify('error', 'Failed to load packages')
      } finally {
        loading.value = false
      }
    }

    const deletePackage = async (id) => {
      if (!confirm('Are you sure you want to delete this package?')) return

      try {
        console.log('Sending delete request for ID:', id)
        const response = await api.post(`/admin/packages/delete/${id}`)
        console.log('Delete response:', response.data)
        
        if (response.data.status === 'success') {
          if (window.notify) window.notify('success', 'Package deleted successfully')
          fetchPackages()
        } else {
             if (window.notify) window.notify('error', response.data.message || 'Failed to delete')
        }
      } catch (error) {
        console.error('Error deleting package:', error)
        if (window.notify) window.notify('error', error.response?.data?.message || 'Failed to delete package')
      }
    }

    onMounted(() => {
      fetchPackages()
    })

    return { packages, loading, formatAmount, deletePackage }
  }
}
</script>

<style scoped>
.ma-card { background: white; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); overflow: hidden; margin-top: 20px; }
</style>
