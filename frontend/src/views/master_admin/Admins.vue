<template>
  <MasterAdminLayout page-title="Admins Management">
    <div class="ma-admins">
      <!-- Header Section -->
      <div class="ma-header-glass mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-4">
          <div>
            <h1 class="ma-page-title">Team Management</h1>
            <p class="ma-page-subtitle">Control who has access to your mission control center</p>
          </div>
          <div class="d-flex gap-3">
            <button class="ma-btn-premium" @click="showCreate = true">
              <i class="fas fa-plus-circle"></i>
              <span>Assign New Admin</span>
            </button>
            <button class="ma-btn-icon-glass" @click="fetchAdmins" :disabled="loading">
              <i class="fas fa-sync-alt" :class="{ 'fa-spin': loading }"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Main Content Card -->
      <div class="ma-card-glass p-0 overflow-hidden">
        <div class="ma-card-banner">
          <div class="banner-content">
            <div class="banner-stat">
              <span class="stat-value text-white">{{ admins.length }}</span>
              <span class="stat-label">Active Controllers</span>
            </div>
          </div>
        </div>

        <div class="table-responsive">
          <table class="ma-premium-table">
            <thead>
              <tr>
                <th>Identity</th>
                <th>Network Alias</th>
                <th>Channel Status</th>
                <th>Onboarding</th>
                <th class="text-end">Command & Control</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading">
                <td colspan="5">
                  <div class="ma-table-loader">
                    <div class="ma-glow-spinner"></div>
                    <span>Fetching Personnel...</span>
                  </div>
                </td>
              </tr>
              <tr v-else-if="admins.length === 0">
                <td colspan="5">
                  <div class="ma-empty-state">
                    <div class="empty-icon-shield">
                      <i class="fas fa-shield-alt"></i>
                    </div>
                    <h4>No Personnel Registered</h4>
                    <p>Access is currently restricted to master account only.</p>
                  </div>
                </td>
              </tr>
              <tr v-else v-for="admin in admins" :key="admin.id" class="ma-admin-row">
                <td class="ma-identity-cell">
                  <div class="ma-user-avatar">
                    <span class="avatar-initials">{{ getInitials(admin.name) }}</span>
                    <div class="avatar-glow"></div>
                  </div>
                  <div class="ma-user-info">
                    <span class="ma-user-name">{{ admin.name || 'Anonymous Admin' }}</span>
                    <span class="ma-user-email">{{ admin.email }}</span>
                  </div>
                </td>
                <td>
                  <div class="ma-alias-badge">
                    <i class="fas fa-at"></i>
                    <span>{{ admin.username }}</span>
                  </div>
                </td>
                <td>
                  <div class="ma-status-indicator" :class="{ 'is-online': admin.status }">
                    <span class="status-dot"></span>
                    <span class="status-text">{{ admin.status ? 'Active' : 'Inactive' }}</span>
                  </div>
                </td>
                <td>
                  <span class="ma-onboard-date">{{ formatDate(admin.created_at) }}</span>
                </td>
                <td>
                  <div class="ma-command-actions justify-content-end">
                    <button 
                      class="cmd-btn cmd-btn--edit" 
                      @click="openEditAdmin(admin)" 
                      title="Adjust Clearances"
                      v-if="!admin.is_super_admin"
                    >
                      <i class="fas fa-user-shield"></i>
                    </button>
                    <button 
                      class="cmd-btn" 
                      :class="admin.status ? 'cmd-btn--restrict' : 'cmd-btn--activate'"
                      @click="toggleAdmin(admin)"
                      :title="admin.status ? 'Restrict Access' : 'Restore Access'"
                    >
                      <i :class="admin.status ? 'fas fa-lock' : 'fas fa-lock-open'"></i>
                    </button>
                    <button class="cmd-btn cmd-btn--key" @click="openResetPwd(admin)" title="Override Credentials">
                      <i class="fas fa-key"></i>
                    </button>
                    <button 
                      class="cmd-btn cmd-btn--danger" 
                      @click="confirmDelete(admin)" 
                      title="Decommission Account"
                      v-if="!admin.is_super_admin"
                    >
                      <i class="fas fa-trash-alt"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Modals (Improved UI) -->
      <Transition name="ma-slide-up">
        <div v-if="showCreate" class="ma-overlay-glass" @click.self="showCreate = false">
          <div class="ma-premium-sheet">
            <div class="sheet-header">
              <div class="header-deco"></div>
              <h3 class="sheet-title"><i class="fas fa-user-plus me-3"></i>Deploy Admin</h3>
              <button class="sheet-close" @click="showCreate = false"><i class="fas fa-times"></i></button>
            </div>
            <div class="sheet-body">
              <div class="ma-premium-form">
                <div class="form-row">
                  <label>Display Name</label>
                  <div class="input-wrapper">
                    <i class="far fa-user"></i>
                    <input v-model="createForm.name" type="text" placeholder="Full legal name">
                  </div>
                </div>
                <div class="grid-row">
                  <div class="form-row">
                    <label>Username</label>
                    <div class="input-wrapper">
                      <i class="fas fa-terminal"></i>
                      <input v-model="createForm.username" type="text" placeholder="system_handle">
                    </div>
                  </div>
                  <div class="form-row">
                    <label>Secure Email</label>
                    <div class="input-wrapper">
                      <i class="far fa-envelope"></i>
                      <input v-model="createForm.email" type="email" placeholder="admin@domain.com">
                    </div>
                  </div>
                </div>
                <div class="form-row">
                  <label>Access Credential (Password)</label>
                  <div class="input-wrapper">
                    <i class="fas fa-shield-alt"></i>
                    <input v-model="createForm.password" type="text" placeholder="Minimum 24-bit security level">
                  </div>
                </div>
                <div v-if="createError" class="ma-error-glow mt-3">{{ createError }}</div>
                <div class="form-footer mt-4">
                  <button class="ma-btn-premium w-100" :disabled="creating" @click="createAdmin">
                    <span v-if="!creating">Grant Global Access</span>
                    <span v-else class="d-flex align-items-center justify-content-center gap-2">
                       <i class="fas fa-circle-notch fa-spin"></i> Initializing...
                    </span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </Transition>

      <!-- Password Reset (Improved) -->
      <Transition name="ma-slide-up">
        <div v-if="showResetPwd" class="ma-overlay-glass" @click.self="showResetPwd = false">
          <div class="ma-premium-sheet ma-sheet--small">
            <div class="sheet-header">
              <div class="header-deco deco--warning"></div>
              <h3 class="sheet-title"><i class="fas fa-key me-3"></i>Credential Override</h3>
              <button class="sheet-close" @click="showResetPwd = false"><i class="fas fa-times"></i></button>
            </div>
            <div class="sheet-body text-center">
              <p class="text-muted mb-4 small">Force a security credential update for <strong>{{ selectedAdmin?.name }}</strong></p>
              <div class="ma-premium-form">
                <div class="input-wrapper mb-3">
                  <i class="fas fa-fingerprint"></i>
                  <input v-model="newPwd" type="text" placeholder="New high-entropy password">
                </div>
                <div v-if="pwdError" class="ma-error-glow mb-3">{{ pwdError }}</div>
                <button class="ma-btn-premium btn--warning w-100" :disabled="resettingPwd" @click="doResetPwd">
                   {{ resettingPwd ? 'Updating Cryptography...' : 'Apply Security Override' }}
                 </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>

      <!-- Edit Admin & Permissions -->
      <Transition name="ma-slide-up">
        <div v-if="showEdit" class="ma-overlay-glass" @click.self="showEdit = false">
          <div class="ma-premium-sheet">
            <div class="sheet-header">
              <div class="header-deco deco--primary"></div>
              <h3 class="sheet-title"><i class="fas fa-user-shield me-3"></i>Clearance Settings</h3>
              <button class="sheet-close" @click="showEdit = false"><i class="fas fa-times"></i></button>
            </div>
            <div class="sheet-body">
              <div class="ma-premium-form">
                <div class="grid-row mb-4">
                  <div class="form-row">
                    <label>Full Name</label>
                    <div class="input-wrapper">
                      <i class="far fa-user"></i>
                      <input v-model="editForm.name" type="text">
                    </div>
                  </div>
                  <div class="form-row">
                    <label>Personnel Email</label>
                    <div class="input-wrapper">
                      <i class="far fa-envelope"></i>
                      <input v-model="editForm.email" type="email">
                    </div>
                  </div>
                </div>

                <div class="security-divider mb-4">
                   <span>Menu Permissions (On / Off)</span>
                </div>

                <div class="permission-grid">
                  <div v-for="(label, key) in permissionLabels" :key="key" class="perm-item">
                    <span class="perm-label">{{ label }}</span>
                    <label class="premium-switch">
                      <input type="checkbox" v-model="editForm.permissions[key]">
                      <span class="switch-slider"></span>
                    </label>
                  </div>
                </div>

                <div v-if="editError" class="ma-error-glow mt-3">{{ editError }}</div>
                <div class="form-footer mt-4 gap-3 d-flex">
                   <button class="ma-btn-premium w-100" :disabled="updating" @click="updateAdmin">
                      <span v-if="!updating">Save Clearances</span>
                      <span v-else><i class="fas fa-circle-notch fa-spin"></i> Saving...</span>
                   </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </Transition>

      <!-- Delete Confirmation (Improved) -->
      <Transition name="ma-fade">
        <div v-if="showDeleteConfirm" class="ma-overlay-glass" @click.self="showDeleteConfirm = false">
          <div class="ma-danger-sheet">
            <div class="danger-icon">
              <i class="fas fa-skull-crossbones"></i>
            </div>
            <h3>Critical Command</h3>
            <p>You are about to remove all access for <strong>{{ selectedAdmin?.name }}</strong>. All access tokens will be revoked immediately.</p>
            <div class="danger-footer">
              <button class="danger-btn" :disabled="deleting" @click="doDelete">
                {{ deleting ? 'Removing Access...' : 'Confirm Removal' }}
              </button>
              <button class="cancel-btn" @click="showDeleteConfirm = false">Abort Command</button>
            </div>
          </div>
        </div>
      </Transition>

      <!-- Premium Toast -->
      <Transition name="ma-toast-slide">
        <div v-if="toast.show" class="ma-premium-toast" :class="'toast--' + toast.type">
          <div class="toast-indicator"></div>
          <i :class="toast.type === 'success' ? 'fas fa-check-shield' : 'fas fa-exclamation-triangle'"></i>
          <span>{{ toast.msg }}</span>
        </div>
      </Transition>
    </div>
  </MasterAdminLayout>
</template>

<script>
import { ref, onMounted } from 'vue'
import MasterAdminLayout from '../../components/master_admin/MasterAdminLayout.vue'
import api from '../../services/api'

export default {
  name: 'MasterAdminAdmins',
  components: { MasterAdminLayout },
  setup() {
    const admins = ref([])
    const loading = ref(false)
    const showCreate = ref(false)
    const creating = ref(false)
    const createError = ref('')
    const createForm = ref({ name: '', username: '', email: '', password: '' })

    const showResetPwd = ref(false)
    const resettingPwd = ref(false)
    const newPwd = ref('')
    const pwdError = ref('')
    const selectedAdmin = ref(null)

    const showDeleteConfirm = ref(false)
    const deleting = ref(false)

    const toast = ref({ show: false, type: 'success', msg: '' })

    const showToast = (msg, type = 'success') => {
      toast.value = { show: true, type, msg }
      setTimeout(() => { toast.value.show = false }, 4000)
    }

    const formatDate = (d) => {
      if (!d) return 'Inception'
      return new Date(d).toLocaleDateString('en-IN', { year: 'numeric', month: 'short', day: 'numeric' })
    }

    const getInitials = (name) => {
      if (!name) return 'A'
      return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2)
    }

    const fetchAdmins = async () => {
      loading.value = true
      try {
        const res = await api.get('/admin/admins')
        admins.value = res.data?.data?.admins || []
      } catch (e) {
        showToast('Failed to sync personnel database.', 'danger')
      } finally {
        loading.value = false
      }
    }

    const showEdit = ref(false)
    const updating = ref(false)
    const editError = ref('')
    const editForm = ref({ id: null, name: '', email: '', user_id: null, permissions: {} })
    
    const permissionLabels = {
      view_users: 'User Management (View)',
      edit_users: 'User Control (Edit/Ban)',
      view_orders: 'Orders Management',
      view_deposits: 'Deposits (View)',
      edit_deposits: 'Deposits (Approve/Reject)',
      view_withdrawals: 'Withdrawals (View)',
      edit_withdrawals: 'Withdrawals (Approve/Reject)',
      view_transactions: 'Transactions History',
      view_ledger: 'Account Ledger',
      view_reports: 'Home Reports',
      manage_commissions: 'Agent Commissions Control'
    }

    const openEditAdmin = (admin) => {
      selectedAdmin.value = admin
      editForm.value = {
        id: admin.id,
        name: admin.name,
        email: admin.email,
        user_id: admin.user_id,
        user_name: admin.user_name,
        permissions: admin.permissions || {
          view_users: true,
          edit_users: false,
          view_orders: false,
          view_deposits: false,
          edit_deposits: false,
          view_withdrawals: false,
          edit_withdrawals: true,
          view_transactions: false,
          view_ledger: false,
          view_reports: true,
          manage_commissions: false
        }
      }
      showEdit.value = true
    }

    const updateAdmin = async () => {
      editError.value = ''
      updating.value = true
      try {
        await api.post(`/admin/admins/${editForm.value.id}/update`, editForm.value)
        showEdit.value = false
        showToast('Clearances updated successfully.')
        fetchAdmins()
      } catch (e) {
        editError.value = e.response?.data?.message || 'Failed to update clearance.'
      } finally {
        updating.value = false
      }
    }

    const createAdmin = async () => {
      createError.value = ''
      if (!createForm.value.name || !createForm.value.username || !createForm.value.email || !createForm.value.password) {
        createError.value = 'All authentication parameters required.'; return
      }
      creating.value = true
      try {
        const res = await api.post('/admin/admins/create', createForm.value)
        const newAdmin = res.data?.data?.admin
        if (newAdmin) admins.value.unshift(newAdmin)
        showCreate.value = false
        createForm.value = { name: '', username: '', email: '', password: '' }
        showToast('Personnel onboarded successfully!')
      } catch (e) {
        const errs = e.response?.data?.errors
        createError.value = errs ? Object.values(errs).flat().join(' ') : (e.response?.data?.message || 'Failed to initialize personnel.')
      } finally {
        creating.value = false
      }
    }

    const toggleAdmin = async (admin) => {
      try {
        const res = await api.post(`/admin/admins/${admin.id}/toggle`)
        const newStatus = res.data?.data?.status
        admin.status = newStatus ?? (admin.status ? 0 : 1)
        showToast(`Admin access ${admin.status ? 'activated' : 'restricted'}.`)
      } catch (e) {
        showToast(e.response?.data?.message || 'Security toggle failed.', 'danger')
      }
    }

    const openResetPwd = (admin) => {
      selectedAdmin.value = admin
      newPwd.value = ''
      pwdError.value = ''
      showResetPwd.value = true
    }

    const doResetPwd = async () => {
      pwdError.value = ''
      if (!newPwd.value || newPwd.value.length < 6) { pwdError.value = 'Complexity must be at least 6 characters.'; return }
      resettingPwd.value = true
      try {
        await api.post(`/admin/admins/${selectedAdmin.value.id}/reset-password`, { password: newPwd.value })
        showResetPwd.value = false
        showToast('Credentials overridden successfully!')
      } catch (e) {
        pwdError.value = e.response?.data?.message || 'Override command rejected.'
      } finally {
        resettingPwd.value = false
      }
    }

    const confirmDelete = (admin) => {
      selectedAdmin.value = admin
      showDeleteConfirm.value = true
    }

    const doDelete = async () => {
      deleting.value = true
      try {
        await api.post(`/admin/admins/${selectedAdmin.value.id}/delete`)
        admins.value = admins.value.filter(a => a.id !== selectedAdmin.value.id)
        showDeleteConfirm.value = false
        showToast('Admin access removed.')
      } catch (e) {
        showToast(e.response?.data?.message || 'Decommission command failed.', 'danger')
      } finally {
        deleting.value = false
      }
    }

    onMounted(() => {
      const admin = JSON.parse(localStorage.getItem('admin_user') || '{}')
      if (!admin.is_super_admin) {
        if (window.notify) window.notify('error', 'Restricted access: Master Admin only.')
        router.push('/master_admin/dashboard')
        return
      }
      fetchAdmins()
    })

    return {
      admins, loading, showCreate, creating, createError, createForm,
      showResetPwd, resettingPwd, newPwd, pwdError, selectedAdmin,
      showDeleteConfirm, deleting,
      showEdit, updating, editError, editForm, permissionLabels,
      toast, formatDate, getInitials,
      fetchAdmins, createAdmin, toggleAdmin, openResetPwd, doResetPwd, confirmDelete, doDelete,
      openEditAdmin, updateAdmin
    }
  }
}
</script>

<style scoped>
.ma-admins {
  padding: 10px;
  animation: maFadeIn 0.6s cubic-bezier(0.22, 1, 0.36, 1);
}

@keyframes maFadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Glass Header */
.ma-header-glass {
  background: rgba(255, 255, 255, 0.03);
  backdrop-filter: blur(15px);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 24px;
  padding: 30px;
}

.ma-page-title {
  font-size: 2rem;
  font-weight: 800;
  letter-spacing: -0.02em;
  background: linear-gradient(135deg, #fff 0%, #a5b4fc 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  margin: 0;
}

.ma-page-subtitle {
  color: #94a3b8;
  font-size: 0.95rem;
  margin-top: 5px;
}

/* Premium Buttons */
.ma-btn-premium {
  background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 14px;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 10px;
  box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
  transition: all 0.3s ease;
  cursor: pointer;
}

.ma-btn-premium:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 25px rgba(79, 70, 229, 0.5);
  background: linear-gradient(135deg, #818cf8 0%, #6366f1 100%);
}

.ma-btn-premium.btn--warning {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
  box-shadow: 0 4px 15px rgba(217, 119, 6, 0.3);
}

.ma-btn-icon-glass {
  width: 48px;
  height: 48px;
  border-radius: 14px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #fff;
  display: grid;
  place-items: center;
  transition: all 0.2s ease;
  cursor: pointer;
}

.ma-btn-icon-glass:hover {
  background: rgba(255, 255, 255, 0.1);
  transform: rotate(30deg);
}

/* Main Card */
.ma-card-glass {
  background: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 28px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
}

.ma-card-banner {
  background: linear-gradient(90deg, #1e1b4b 0%, #312e81 100%);
  padding: 40px;
  position: relative;
  overflow: hidden;
}

.ma-card-banner::after {
  content: \'\';
  position: absolute;
  top: -50%;
  right: -10%;
  width: 300px;
  height: 300px;
  background: radial-gradient(circle, rgba(99, 102, 241, 0.15) 0%, transparent 70%);
  pointer-events: none;
}

.banner-stat {
  display: flex;
  flex-direction: column;
}

.stat-value {
  font-size: 3rem;
  font-weight: 800;
  line-height: 1;
}

.stat-label {
  color: #818cf8;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  font-size: 0.8rem;
  font-weight: 700;
  margin-top: 5px;
}

/* Premium Table */
.ma-premium-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
}

.ma-premium-table th {
  padding: 20px 30px;
  text-transform: uppercase;
  font-size: 0.72rem;
  letter-spacing: 0.08em;
  color: #64748b;
  font-weight: 800;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.ma-admin-row {
  transition: all 0.3s ease;
}

.ma-admin-row:hover {
  background: rgba(255, 255, 255, 0.02);
}

.ma-admin-row td {
  padding: 20px 30px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.03);
  vertical-align: middle;
}

/* Identity Cell */
.ma-identity-cell {
  display: flex;
  align-items: center;
  gap: 15px;
}

.ma-user-avatar {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: linear-gradient(135deg, #4f46e5 0%, #1e1b4b 100%);
  display: grid;
  place-items: center;
  position: relative;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.avatar-initials {
  font-weight: 800;
  font-size: 1rem;
  color: #fff;
  z-index: 2;
}

.avatar-glow {
  position: absolute;
  inset: -1px;
  border-radius: 13px;
  background: linear-gradient(135deg, #6366f1, #a855f7);
  opacity: 0.3;
  z-index: 1;
}

.ma-user-info {
  display: flex;
  flex-direction: column;
}

.ma-user-name {
  color: #f1f5f9;
  font-weight: 700;
  font-size: 1rem;
}

.ma-user-email {
  color: #64748b;
  font-size: 0.85rem;
}

/* Badges & Indicators */
.ma-alias-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 6px 14px;
  background: rgba(139, 92, 246, 0.1);
  border: 1px solid rgba(139, 92, 246, 0.2);
  border-radius: 10px;
  color: #a78bfa;
  font-family: inherit;
  font-weight: 600;
  font-size: 0.85rem;
}

.ma-status-indicator {
  display: flex;
  align-items: center;
  gap: 10px;
  color: #64748b;
  font-size: 0.85rem;
  font-weight: 600;
}

.ma-status-indicator.is-online {
  color: #10b981;
}

.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: currentColor;
  box-shadow: 0 0 10px currentColor;
}

.ma-onboard-date {
  color: #64748b;
  font-size: 0.85rem;
  font-weight: 500;
}

/* Command Buttons */
.ma-command-actions {
  display: flex;
  gap: 10px;
}

.cmd-btn {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  border: 1px solid rgba(255, 255, 255, 0.08);
  background: rgba(255, 255, 255, 0.03);
  color: #94a3b8;
  display: grid;
  place-items: center;
  transition: all 0.2s ease;
  cursor: pointer;
}

.cmd-btn:hover {
  background: rgba(255, 255, 255, 0.1);
  color: #fff;
  transform: scale(1.05);
}

.cmd-btn--activate:hover { color: #10b981; border-color: rgba(16, 185, 129, 0.3); }
.cmd-btn--restrict:hover { color: #f59e0b; border-color: rgba(245, 158, 11, 0.3); }
.cmd-btn--danger:hover { color: #ef4444; border-color: rgba(239, 68, 68, 0.3); }

/* Sheet (Modal) Design */
.ma-overlay-glass {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.7);
  backdrop-filter: blur(10px);
  z-index: 1000;
  display: grid;
  place-items: center;
  padding: 20px;
}

.ma-premium-sheet {
  background: #0f172a;
  width: 100%;
  max-width: 550px;
  max-height: 90vh; /* Add max height */
  overflow-y: auto; /* Enable vertical scroll */
  border-radius: 32px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 0 50px 100px -20px rgba(0, 0, 0, 0.8);
  animation: maSheetScale 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes maSheetScale {
  from { opacity: 0; transform: scale(0.9) translateY(20px); }
  to { opacity: 1; transform: scale(1) translateY(0); }
}

.sheet-header {
  padding: 30px;
  position: relative;
}

.header-deco {
  position: absolute;
  top: 0; left: 0; right: 0; height: 4px;
  background: linear-gradient(90deg, #6366f1, #a855f7);
}

.deco--warning { background: linear-gradient(90deg, #f59e0b, #ea580c); }

.sheet-title {
  color: #fff;
  font-weight: 800;
  font-size: 1.4rem;
  margin: 0;
}

.sheet-close {
  position: absolute;
  top: 25px; right: 25px;
  background: rgba(255, 255, 255, 0.05);
  border: none;
  color: #fff;
  width: 32px; height: 32px;
  border-radius: 50%;
  cursor: pointer;
}

.sheet-body { padding: 0 30px 40px 30px; }

/* Premium Forms */
.form-row {
  margin-bottom: 20px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-row label {
  color: #64748b;
  font-weight: 700;
  font-size: 0.8rem;
  text-transform: uppercase;
  padding-left: 5px;
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.input-wrapper i {
  position: absolute;
  left: 18px;
  color: #64748b;
  font-size: 0.9rem;
}

.input-wrapper input {
  width: 100%;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 16px;
  padding: 14px 14px 14px 48px;
  color: #fff;
  font-size: 0.95rem;
  transition: all 0.2s ease;
}

.input-wrapper input:focus {
  background: rgba(255, 255, 255, 0.05);
  border-color: #6366f1;
  box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
  outline: none;
}

.grid-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.ma-error-glow {
  padding: 12px 18px;
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.2);
  border-radius: 14px;
  color: #fca5a5;
  font-size: 0.85rem;
  text-align: center;
}

/* Danger Sheet */
.ma-danger-sheet {
  background: #0f172a;
  max-width: 400px;
  padding: 40px;
  border-radius: 32px;
  border: 1px solid rgba(239, 68, 68, 0.2);
  text-align: center;
}

.danger-icon {
  width: 64px; height: 64px;
  border-radius: 18px;
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
  display: grid;
  place-items: center;
  font-size: 1.8rem;
  margin: 0 auto 25px auto;
}

.ma-danger-sheet h3 { color: #f1f5f9; font-weight: 800; margin-bottom: 10px; }
.ma-danger-sheet p { color: #94a3b8; font-size: 0.95rem; margin-bottom: 30px; }

.danger-footer { display: flex; flex-direction: column; gap: 12px; }
.danger-btn {
  background: #ef4444; border: none; color: #fff; border-radius: 14px; padding: 14px;
  font-weight: 700; cursor: pointer; transition: all 0.2s ease;
}
.danger-btn:hover { background: #dc2626; transform: scale(1.02); }
.cancel-btn {
  background: transparent; border: 1px solid rgba(255, 255, 255, 0.1); color: #94a3b8;
  border-radius: 14px; padding: 12px; font-weight: 600; cursor: pointer;
}

/* Premium Toast */
.ma-premium-toast {
  position: fixed;
  bottom: 40px; right: 40px;
  background: rgba(15, 23, 42, 0.9);
  backdrop-filter: blur(15px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  padding: 16px 28px;
  border-radius: 18px;
  color: #fff;
  display: flex;
  align-items: center;
  gap: 15px;
  z-index: 10000;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
}

.toast-indicator {
  position: absolute;
  top: -1px; left: 18px; right: 18px; height: 2px;
  background: #6366f1;
}

.toast--danger .toast-indicator { background: #ef4444; }

/* Loaders */
.ma-glow-spinner {
  width: 40px; height: 40px;
  border: 3px solid rgba(255, 255, 255, 0.05);
  border-top-color: #6366f1;
  border-radius: 50%;
  animation: maSpin 0.8s linear infinite;
}

@keyframes maSpin { to { transform: rotate(360deg); } }

/* Permission Grid */
.security-divider {
  display: flex; align-items: center; gap: 15px; color: #6366f1;
  font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;
}
.security-divider::after { content: ''; flex: 1; height: 1px; background: rgba(99, 102, 241, 0.2); }

.permission-grid {
  display: grid; grid-template-columns: 1fr 1fr; gap: 15px;
}
.perm-item {
  display: flex; justify-content: space-between; align-items: center;
  padding: 12px 18px; background: rgba(255, 255, 255, 0.03);
  border-radius: 14px; border: 1px solid rgba(255, 255, 255, 0.05);
}
.perm-label { font-size: 0.9rem; color: #cbd5e1; }

.premium-switch {
  position: relative; width: 44px; height: 24px; cursor: pointer;
}
.premium-switch input { opacity: 0; width: 0; height: 0; }
.switch-slider {
  position: absolute; top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(255, 255, 255, 0.1); transition: .4s; border-radius: 34px;
}
.switch-slider:before {
  position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px;
  background: white; transition: .4s; border-radius: 50%;
}
input:checked + .switch-slider { background: #6366f1; box-shadow: 0 0 10px rgba(99, 102, 241, 0.5); }
input:checked + .switch-slider:before { transform: translateX(20px); }

.cmd-btn--edit { color: #6366f1; }
.cmd-btn--edit:hover { background: rgba(99, 102, 241, 0.15); box-shadow: 0 0 15px rgba(99, 102, 241, 0.3); }

/* Transitions */
.ma-slide-up-enter-active, .ma-slide-up-leave-active { transition: all 0.4s ease; }
.ma-slide-up-enter-from { opacity: 0; transform: translateY(40px); }
.ma-slide-up-leave-to { opacity: 0; transform: translateY(40px); }

.ma-toast-slide-enter-active { transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
.ma-toast-slide-leave-active { transition: all 0.2s ease; }
.ma-toast-slide-enter-from { opacity: 0; transform: translateX(50px); }
.ma-toast-slide-leave-to { opacity: 0; transform: scale(0.9); }
</style>
