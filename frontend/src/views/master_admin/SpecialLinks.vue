<template>
  <MasterAdminLayout page-title="Special Links">
    <div class="ma-special-links">
      <div class="ma-card mb-4">
        <div class="ma-card__header ma-card__header--gradient">
          <div>
            <h5 class="ma-card__title"><i class="fas fa-tag me-2"></i>Special Discount Link Generator</h5>
            <p class="ma-card__subtitle">Generate a global signed link (plan + discount locked) for everyone</p>
          </div>
        </div>

        <div class="ma-card__body">
          <div class="row g-3 align-items-end">
            <div class="col-md-5">
              <label class="form--label">Select Plan</label>
              <select v-model="packageId" class="form--control">
                <option v-for="p in packages" :key="p.id" :value="p.id">
                  {{ p.name }} - ₹{{ formatAmount(p.price) }}
                </option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form--label">Discount (₹)</label>
              <input v-model="discount" type="number" min="0" step="1" class="form--control" placeholder="0">
            </div>
            <div class="col-md-3 d-grid">
              <button class="ma-btn ma-btn--primary" @click="generate" title="Generate">
                <i class="fas fa-wand-magic-sparkles me-1"></i>
                Generate
              </button>
            </div>
          </div>

          <div v-if="specialLink" class="mt-4">
            <div class="ma-doc-card p-3">
              <div class="d-flex flex-column flex-md-row gap-2 align-items-md-center justify-content-between mb-2">
                <div>
                  <div class="fw-bold" style="color: rgba(255,255,255,0.92);">Generated Link</div>
                  <div class="small" style="color: rgba(255,255,255,0.60);">
                    Final amount: ₹{{ formatAmount(finalPrice) }} (Discount: ₹{{ formatAmount(discountNumber) }})
                  </div>
                </div>
              </div>
              <div class="d-flex gap-2">
                <input id="maSpecialLink" class="form--control" :value="specialLink" readonly>
                <button class="ma-btn" type="button" @click="copy('maSpecialLink')">
                  <i class="fas fa-copy me-1"></i>Copy
                </button>
              </div>
            </div>
          </div>

        </div>
      </div>

      <div class="ma-card">
        <div class="ma-card__header">
          <div class="d-flex align-items-center justify-content-between w-100 gap-2 flex-wrap">
            <div>
              <h5 class="ma-card__title mb-1"><i class="fas fa-list me-2"></i>Existing Special Links</h5>
              <p class="ma-card__subtitle mb-0">Edit or delete saved global links</p>
            </div>
            <div class="d-flex gap-2">
              <button class="ma-btn" type="button" @click="fetchExisting" title="Refresh">
                <i class="fas fa-rotate me-1"></i>Refresh
              </button>
            </div>
          </div>
        </div>
        <div class="ma-card__body">
          <div v-if="existingLinks.length === 0" class="text-center py-4" style="color: rgba(255,255,255,0.65);">
            No links yet. Generate one above.
          </div>

          <div v-else class="table-responsive">
            <table class="table table-dark table-striped align-middle mb-0">
              <thead>
                <tr>
                  <th style="width: 90px;">ID</th>
                  <th>Plan</th>
                  <th style="width: 130px;">Discount</th>
                  <th style="width: 140px;">Final</th>
                  <th>Link</th>
                  <th style="width: 110px;">Status</th>
                  <th style="width: 220px;">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="row in existingLinks" :key="row.id">
                  <td class="fw-bold">#{{ row.id }}</td>

                  <td>
                    <div v-if="editingId !== row.id" class="fw-bold">{{ row.package_name }}</div>
                    <div v-else>
                      <select v-model="editPackageId" class="form--control">
                        <option v-for="p in packages" :key="p.id" :value="p.id">
                          {{ p.name }} - ₹{{ formatAmount(p.price) }}
                        </option>
                      </select>
                    </div>
                  </td>

                  <td>
                    <div v-if="editingId !== row.id">₹{{ formatAmount(row.discount) }}</div>
                    <div v-else>
                      <input v-model="editDiscount" type="number" min="0" step="1" class="form--control" placeholder="0">
                    </div>
                  </td>

                  <td class="fw-bold text-success">
                    ₹{{ formatAmount(row.final_price) }}
                  </td>

                  <td>
                    <div class="d-flex gap-2">
                      <input :id="`existingLink${row.id}`" class="form--control" :value="row.link || makeLink(row)" readonly>
                      <button class="ma-btn" type="button" @click="copy(`existingLink${row.id}`)">
                        <i class="fas fa-copy"></i>
                      </button>
                    </div>
                  </td>

                  <td>
                    <span class="badge" :class="row.is_active ? 'bg-success' : 'bg-secondary'">
                      {{ row.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>

                  <td>
                    <div class="d-flex gap-2 flex-wrap">
                      <button v-if="editingId !== row.id" class="ma-btn" type="button" @click="startEdit(row)">
                        <i class="fas fa-pen me-1"></i>Edit
                      </button>
                      <template v-else>
                        <button class="ma-btn ma-btn--primary" type="button" @click="saveEdit(row)">
                          <i class="fas fa-check me-1"></i>Save
                        </button>
                        <button class="ma-btn" type="button" @click="cancelEdit" :disabled="saveLoading">
                          Cancel
                        </button>
                      </template>

                      <button class="ma-btn" type="button" @click="toggleActive(row)" :disabled="saveLoading">
                        {{ row.is_active ? 'Disable' : 'Enable' }}
                      </button>

                      <button class="ma-btn ma-btn--danger" type="button" @click="remove(row)">
                        <i class="fas fa-trash me-1"></i>Delete
                      </button>
                    </div>
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
  name: 'MasterAdminSpecialLinks',
  components: { MasterAdminLayout },
  setup() {
    const packageId = ref(2)
    const discount = ref(0)
    const loading = ref(false)
    const specialLink = ref('')
    const finalPrice = ref(0)

    const existingLinks = ref([])
    const listLoading = ref(false)
    const editingId = ref(null)
    const editPackageId = ref(2)
    const editDiscount = ref(0)
    const saveLoading = ref(false)
    const deleteLoadingId = ref(null)

    const packages = ref([
      { id: 1, name: 'AdsLite', price: 1499 },
      { id: 2, name: 'AdsPro', price: 2999 },
      { id: 3, name: 'AdsSupreme', price: 5999 },
      { id: 4, name: 'AdsPremium', price: 9999 },
      { id: 5, name: 'AdsPremium+', price: 15999 },
    ])

    const discountNumber = computed(() => {
      const n = parseInt(discount.value, 10)
      return Number.isFinite(n) && n > 0 ? n : 0
    })

    const formatAmount = (n) => {
      if (!n && n !== 0) return '0.00'
      return parseFloat(n).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }

    const makeLink = (row) => {
      if (!row) return ''
      const pkgId = row.package_id
      const disc = parseInt(row.discount, 10) || 0
      const sig = row.sig || ''
      if (!pkgId || !sig) return ''
      if (disc > 0) return `${window.location.origin}/register?pkg=${pkgId}&disc=${disc}&sig=${sig}`
      return `${window.location.origin}/register?pkg=${pkgId}&sig=${sig}`
    }

    const fetchExisting = async () => {
      listLoading.value = true
      try {
        const res = await api.get('/admin/referral/special-links')
        const links = res.data?.data?.links || []
        existingLinks.value = Array.isArray(links)
          ? links.map((x) => ({ ...x, link: x.link || makeLink(x) }))
          : []
      } catch (e) {
        existingLinks.value = []
      } finally {
        listLoading.value = false
      }
    }

    const generate = async () => {
      loading.value = true
      try {
        const res = await api.post('/admin/referral/special-link', {
          package_id: packageId.value,
          discount: discountNumber.value
        })
        if (res.data?.status === 'success' && res.data.data) {
          specialLink.value = res.data.data.link || ''
          finalPrice.value = res.data.data.final_price || 0
          if (window.notify) window.notify('success', 'Special link generated')
          fetchExisting()
        } else {
          if (window.notify) window.notify('error', res.data?.message?.[0] || 'Failed to generate link')
        }
      } catch (e) {
        if (window.notify) window.notify('error', e?.response?.data?.message?.[0] || 'Failed to generate link')
      } finally {
        loading.value = false
      }
    }

    const copy = (id) => {
      const el = document.getElementById(id)
      if (!el) return
      el.select()
      document.execCommand('copy')
      if (window.notify) window.notify('success', 'Link copied')
    }

    const startEdit = (row) => {
      editingId.value = row.id
      editPackageId.value = row.package_id
      editDiscount.value = row.discount
    }

    const cancelEdit = () => {
      editingId.value = null
    }

    const saveEdit = async (row) => {
      if (!row?.id) return
      saveLoading.value = true
      try {
        const res = await api.put(`/admin/referral/special-links/${row.id}`, {
          package_id: editPackageId.value,
          discount: parseInt(editDiscount.value, 10) || 0,
          is_active: row.is_active
        })
        if (res.data?.status === 'success' && res.data.data) {
          const updated = res.data.data
          existingLinks.value = existingLinks.value.map((x) =>
            x.id === row.id ? { ...x, ...updated, link: updated.link || makeLink(updated) } : x
          )
          editingId.value = null
          if (window.notify) window.notify('success', 'Link updated')
        } else {
          if (window.notify) window.notify('error', res.data?.message?.[0] || 'Failed to update')
        }
      } catch (e) {
        if (window.notify) window.notify('error', e?.response?.data?.message?.[0] || 'Failed to update')
      } finally {
        saveLoading.value = false
      }
    }

    const toggleActive = async (row) => {
      if (!row?.id) return
      saveLoading.value = true
      try {
        const res = await api.put(`/admin/referral/special-links/${row.id}`, {
          is_active: !row.is_active
        })
        if (res.data?.status === 'success' && res.data.data) {
          const updated = res.data.data
          existingLinks.value = existingLinks.value.map((x) =>
            x.id === row.id ? { ...x, ...updated, link: updated.link || makeLink(updated) } : x
          )
          if (window.notify) window.notify('success', 'Status updated')
        } else {
          if (window.notify) window.notify('error', res.data?.message?.[0] || 'Failed to update status')
        }
      } catch (e) {
        if (window.notify) window.notify('error', e?.response?.data?.message?.[0] || 'Failed to update status')
      } finally {
        saveLoading.value = false
      }
    }

    const remove = async (row) => {
      if (!row?.id) return
      if (!window.confirm('Delete this link?')) return
      deleteLoadingId.value = row.id
      try {
        const res = await api.delete(`/admin/referral/special-links/${row.id}`)
        if (res.data?.status === 'success') {
          existingLinks.value = existingLinks.value.filter((x) => x.id !== row.id)
          if (window.notify) window.notify('success', 'Link deleted')
        } else {
          if (window.notify) window.notify('error', res.data?.message?.[0] || 'Failed to delete')
        }
      } catch (e) {
        if (window.notify) window.notify('error', e?.response?.data?.message?.[0] || 'Failed to delete')
      } finally {
        deleteLoadingId.value = null
      }
    }

    onMounted(() => {
      fetchExisting()
    })

    return {
      packageId,
      discount,
      loading,
      packages,
      specialLink,
      finalPrice,
      discountNumber,
      formatAmount,
      generate,
      copy,
      existingLinks,
      listLoading,
      fetchExisting,
      editingId,
      editPackageId,
      editDiscount,
      startEdit,
      cancelEdit,
      saveEdit,
      saveLoading,
      deleteLoadingId,
      remove,
      toggleActive,
      makeLink,
    }
  }
}
</script>

<style scoped>
.ma-special-links { animation: maFade 0.35s ease-out; }
@keyframes maFade { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

.ma-btn--danger {
  background: rgba(239, 68, 68, 0.14);
  border: 1px solid rgba(239, 68, 68, 0.35);
  color: rgba(255,255,255,0.92);
}
.ma-btn--danger:hover { background: rgba(239, 68, 68, 0.22); }
</style>

