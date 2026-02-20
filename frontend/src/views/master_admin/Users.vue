<template>
  <MasterAdminLayout page-title="All Users">
    <div class="ma-users">

      <!-- Stats Row -->
      <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
          <div class="ma-stat-mini" @click="filterStatus = ''">
            <div class="ma-stat-mini__icon ma-stat-mini__icon--blue"><i class="fas fa-users"></i></div>
            <div><span class="ma-stat-mini__val">{{ totalUsers }}</span><span class="ma-stat-mini__lbl">Total</span></div>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="ma-stat-mini" @click="filterStatus = 'active'">
            <div class="ma-stat-mini__icon ma-stat-mini__icon--green"><i class="fas fa-check-circle"></i></div>
            <div><span class="ma-stat-mini__val">{{ activeCount }}</span><span class="ma-stat-mini__lbl">Active</span></div>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="ma-stat-mini" @click="filterStatus = 'banned'">
            <div class="ma-stat-mini__icon ma-stat-mini__icon--red"><i class="fas fa-ban"></i></div>
            <div><span class="ma-stat-mini__val">{{ bannedCount }}</span><span class="ma-stat-mini__lbl">Banned</span></div>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="ma-stat-mini" @click="filterStatus = 'kyc_pending'">
            <div class="ma-stat-mini__icon ma-stat-mini__icon--amber"><i class="fas fa-clock"></i></div>
            <div><span class="ma-stat-mini__val">{{ kycPendingCount }}</span><span class="ma-stat-mini__lbl">KYC Pending</span></div>
          </div>
        </div>
      </div>

      <!-- Search & Filter -->
      <div class="ma-card mb-4">
        <div class="ma-card__body d-flex flex-wrap gap-3 align-items-center">
          <div class="flex-grow-1" style="min-width: 200px;">
            <div class="ma-search-box">
              <i class="fas fa-search"></i>
              <input type="text" v-model="searchQuery" placeholder="Search by name, email, username, mobile..." @input="debounceSearch" class="ma-search-input">
            </div>
          </div>
          <select v-model="filterStatus" @change="fetchUsers(1)" class="ma-select">
            <option value="">All Users</option>
            <option value="active">Active</option>
            <option value="banned">Banned</option>
            <option value="email_unverified">Email Unverified</option>
            <option value="kyc_pending">KYC Pending</option>
          </select>
          <button class="ma-btn ma-btn--primary" @click="openCreateUserModal">
            <i class="fas fa-user-plus me-1"></i> Create User
          </button>
          <button class="ma-btn-refresh" @click="fetchUsers()">
            <i class="fas fa-sync-alt"></i>
          </button>
        </div>
      </div>

      <!-- Create User Modal -->
      <div v-if="showCreateUserModal" class="ma-modal-overlay" @click="closeCreateUserModal">
        <div class="ma-modal" @click.stop>
          <div class="ma-modal__header">
            <h5 class="ma-modal__title">
              <i class="fas fa-user-plus me-2"></i>Create New User
            </h5>
            <button class="ma-modal__close" @click="closeCreateUserModal">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <div class="ma-modal__body">
            <form @submit.prevent="createUser" class="ma-form">
              <div class="row g-3">
                <div class="col-12">
                  <label class="ma-form-label">Name</label>
                  <input v-model="createForm.name" type="text" class="ma-form-input" placeholder="Full name" required>
                </div>
                <div class="col-md-6">
                  <label class="ma-form-label">Email</label>
                  <input v-model="createForm.email" type="email" class="ma-form-input" placeholder="user@example.com" required>
                  <small class="text-muted">Username will be auto-generated from email.</small>
                </div>
                <div class="col-md-6">
                  <label class="ma-form-label">Number</label>
                  <input v-model="createForm.mobile" type="text" class="ma-form-input" placeholder="9876543210" required>
                </div>
                <div class="col-md-6">
                  <label class="ma-form-label">State</label>
                  <input v-model="createForm.state" type="text" class="ma-form-input" placeholder="State" required>
                </div>
                <div class="col-md-6">
                  <label class="ma-form-label">Activate Course Package</label>
                  <select v-model.number="createForm.course_plan_id" class="ma-form-input" :disabled="coursePlansLoading">
                    <option :value="0">No package (user will buy later)</option>
                    <option v-for="p in activeCoursePlans" :key="p.id" :value="Number(p.id)">
                      {{ p.name }} — ₹{{ Number(p.price || 0).toFixed(0) }} / {{ p.validity_days }} days
                    </option>
                  </select>
                  <small class="text-muted">
                    Optional. If selected, this package will be activated immediately for the user.
                    <span v-if="coursePlansLoading">Loading packages...</span>
                    <span v-else-if="activeCoursePlans.length === 0">No active packages found.</span>
                  </small>
                </div>
                <div class="col-12">
                  <label class="ma-form-label">Sponsor ID</label>
                  <input v-model="createForm.sponsor_id" type="text" class="ma-form-input" placeholder="ADS15001 or sponsor_username">
                  <small class="text-muted">Optional. Sets user sponsor (ref_by).</small>
                </div>
                <div class="col-12">
                  <label class="ma-form-label">Password</label>
                  <input v-model="createForm.password" type="text" class="ma-form-input" required>
                  <small class="text-muted">User will be created with auto ID. Referral code will be `ADS{ID}`.</small>
                </div>
              </div>

              <div class="ma-form-actions mt-3">
                <button type="submit" class="ma-btn ma-btn--primary">
                  Create User
                </button>
                <button type="button" class="ma-btn ma-btn--outline" @click="closeCreateUserModal" :disabled="creatingUser">
                  Cancel
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Users Table -->
      <div class="ma-card ma-table-card">
        <div class="ma-card__header ma-card__header--gradient">
          <div>
            <h5 class="ma-card__title"><i class="fas fa-users me-2"></i>Users List</h5>
            <p class="ma-card__subtitle">Manage and monitor all registered users</p>
          </div>
          <span class="ma-card__count">{{ totalUsers }} users</span>
        </div>
        <div class="table-responsive ma-table-wrapper">
          <table class="ma-table">
            <thead>
              <tr>
                <th><i class="fas fa-hashtag me-1"></i>ID</th>
                <th><i class="fas fa-user me-1"></i>User</th>
                <th><i class="fas fa-envelope me-1"></i>Email</th>
                <th><i class="fas fa-phone me-1"></i>Mobile</th>
                <th><i class="fas fa-lock me-1"></i>Password</th>
                <th><i class="fas fa-wallet me-1"></i>Balance</th>
                <th><i class="fas fa-rupee-sign me-1"></i>Total Deposit</th>
                <th><i class="fas fa-info-circle me-1"></i>Status</th>
                <th><i class="fas fa-cog me-1"></i>Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading">
                <td colspan="9" class="ma-table__loading"><div class="ma-spinner"></div></td>
              </tr>
              <tr v-else-if="users.length === 0">
                <td colspan="9" class="ma-table__empty">
                  <i class="fas fa-users-slash"></i>
                  <p>No users found</p>
                </td>
              </tr>
              <tr v-else v-for="user in users" :key="user.id" class="ma-table-row">
                <td>
                  <div class="ma-id-badge">
                    <i class="fas fa-user-circle"></i>
                    <span>{{ user.id }}</span>
                  </div>
                </td>
                <td>
                  <div class="ma-user-cell">
                    <div class="ma-user-avatar">{{ getInitials(user) }}</div>
                    <div>
                      <span class="ma-user-name">{{ user.firstname }} {{ user.lastname }}</span>
                    </div>
                  </div>
                </td>
                <td>
                  <div class="ma-email-cell">
                    <i class="fas fa-envelope"></i>
                    <span class="ma-table__email" :title="user.email">{{ user.email || 'N/A' }}</span>
                    <span v-if="user.ev" class="ma-badge ma-badge--success" title="Email Verified">
                      <i class="fas fa-check-circle"></i>
                    </span>
                    <span v-else class="ma-badge ma-badge--warning" title="Email Unverified">
                      <i class="fas fa-times-circle"></i>
                    </span>
                  </div>
                </td>
                <td>
                  <div class="ma-mobile-cell">
                    <i class="fas fa-phone"></i>
                    <span>{{ user.mobile || 'N/A' }}</span>
                    <span v-if="user.sv" class="ma-badge ma-badge--success" title="Mobile Verified">
                      <i class="fas fa-check-circle"></i>
                    </span>
                    <span v-else-if="user.mobile" class="ma-badge ma-badge--warning" title="Mobile Unverified">
                      <i class="fas fa-times-circle"></i>
                    </span>
                  </div>
                </td>
                <td>
                  <div class="ma-password-cell">
                    <div class="ma-password-wrapper">
                      <span v-if="!showPassword[user.id]" class="ma-password-masked">••••••••</span>
                      <span v-else class="ma-password-text" :title="user.password">{{ user.password || 'N/A' }}</span>
                    </div>
                    <button class="ma-btn-toggle-password" @click="togglePassword(user.id)" :title="showPassword[user.id] ? 'Hide Password' : 'Show Password'">
                      <i :class="showPassword[user.id] ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                    </button>
                    <button v-if="showPassword[user.id]" class="ma-btn-copy" @click="copyPassword(user.password)" title="Copy Password">
                      <i class="fas fa-copy"></i>
                    </button>
                  </div>
                </td>
                <td>
                  <div class="ma-balance-cell">
                    <i class="fas fa-wallet"></i>
                    <span class="ma-table__balance">₹{{ formatAmount(user.balance || 0) }}</span>
                  </div>
                </td>
                <td>
                  <div class="ma-deposit-cell">
                    <i class="fas fa-rupee-sign"></i>
                    <span class="ma-table__deposit">₹{{ formatAmount(user.total_deposit || 0) }}</span>
                  </div>
                </td>
                <td>
                  <div class="ma-status-cell">
                    <span class="ma-badge" :class="{
                      'ma-badge--success': user.status === 'active',
                      'ma-badge--danger': user.status === 'banned'
                    }">
                      <i :class="user.status === 'active' ? 'fas fa-check-circle' : 'fas fa-ban'"></i>
                      {{ user.status === 'active' ? 'Active' : 'Banned' }}
                    </span>
                    <span v-if="user.kv === 2" class="ma-badge ma-badge--warning" title="KYC Pending">
                      <i class="fas fa-clock"></i> KYC
                    </span>
                    <span v-else-if="user.kv === 1" class="ma-badge ma-badge--success" title="KYC Verified">
                      <i class="fas fa-check-circle"></i> KYC
                    </span>
                  </div>
                </td>
                <td>
                    <div class="ma-action-buttons">
                    <button class="ma-action-btn ma-action-btn--view" @click="openManageUser(user)" title="Manage User">
                      <i class="fas fa-eye"></i>
                      <span class="ma-action-tooltip">View</span>
                    </button>
                    <!-- Verify KYC Button -->
                    <button 
                      v-if="user.kv === 2"
                      class="ma-action-btn ma-action-btn--kyc" 
                      @click="viewKYC(user)" 
                      title="Review KYC"
                    >
                      <i class="fas fa-user-shield text-warning"></i>
                      <span class="ma-action-tooltip">KYC</span>
                    </button>
                    <!-- View Approved KYC -->
                    <button 
                      v-else-if="user.kv === 1"
                      class="ma-action-btn ma-action-btn--kyc" 
                      @click="viewKYC(user)" 
                      title="View KYC Details"
                    >
                      <i class="fas fa-check-circle text-success"></i>
                      <span class="ma-action-tooltip">KYC</span>
                    </button>

                    <button class="ma-action-btn ma-action-btn--edit" @click="openManageUser(user)" title="Manage User">
                      <i class="fas fa-edit"></i>
                      <span class="ma-action-tooltip">Edit</span>
                    </button>
                    <button 
                      class="ma-action-btn ma-action-btn--bank" 
                      @click="editBankAccount(user)"
                      :title="`Edit Bank Account (KYC Status: ${user.kv})`"
                    >
                      <i class="fas fa-university"></i>
                      <span class="ma-action-tooltip">Bank</span>
                    </button>
                    <button 
                      class="ma-action-btn" 
                      :class="user.status === 'active' ? 'ma-action-btn--ban' : 'ma-action-btn--unban'"
                      @click="toggleUserStatus(user)"
                      :title="user.status === 'active' ? 'Ban User' : 'Unban User'"
                    >
                      <i :class="user.status === 'active' ? 'fas fa-ban' : 'fas fa-check-circle'"></i>
                      <span class="ma-action-tooltip">{{ user.status === 'active' ? 'Ban' : 'Unban' }}</span>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="lastPage > 1" class="ma-pagination">
          <button class="ma-page-btn" :disabled="currentPage === 1" @click="fetchUsers(currentPage - 1)">
            <i class="fas fa-chevron-left"></i>
          </button>
          <template v-for="p in paginationPages" :key="p">
            <button v-if="p === '...'" class="ma-page-btn ma-page-dots" disabled>...</button>
            <button v-else class="ma-page-btn" :class="{ 'ma-page-btn--active': p === currentPage }" @click="fetchUsers(p)">{{ p }}</button>
          </template>
          <button class="ma-page-btn" :disabled="currentPage === lastPage" @click="fetchUsers(currentPage + 1)">
            <i class="fas fa-chevron-right"></i>
          </button>
          <span class="ma-page-info">Page {{ currentPage }} of {{ lastPage }}</span>
        </div>
      </div>

      <!-- Manage User Modal -->
      <div v-if="showManageModal" class="ma-modal-overlay" @click="closeManageModal">
        <div class="ma-modal ma-modal--lg" @click.stop>
          <div class="ma-modal__header">
            <h5 class="ma-modal__title">
              <i class="fas fa-user-cog me-2"></i>Manage User - ADS{{ manageUser?.id }}
            </h5>
            <button class="ma-modal__close" @click="closeManageModal">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <div class="ma-modal__body">
            <div class="ma-tabs">
              <button class="ma-tab" :class="{ 'ma-tab--active': manageTab === 'basic' }" @click="manageTab = 'basic'">Basic</button>
              <button class="ma-tab" :class="{ 'ma-tab--active': manageTab === 'sponsor' }" @click="manageTab = 'sponsor'">Sponsor</button>
              <button class="ma-tab" :class="{ 'ma-tab--active': manageTab === 'agent' }" @click="manageTab = 'agent'; fetchAgentSettings()">Agent</button>
              <button class="ma-tab" :class="{ 'ma-tab--active': manageTab === 'password' }" @click="manageTab = 'password'">Password</button>
              <button class="ma-tab" :class="{ 'ma-tab--active': manageTab === 'trx' }" @click="manageTab = 'trx'; fetchUserTransactions(1)">Transactions</button>
            </div>

            <!-- Basic -->
            <div v-if="manageTab === 'basic'" class="ma-pane">
              <div class="row g-3">
                <div class="col-12">
                  <label class="ma-form-label">Name</label>
                  <input v-model="basicForm.name" type="text" class="ma-form-input" required>
                </div>
                <div class="col-md-6">
                  <label class="ma-form-label">Email</label>
                  <input v-model="basicForm.email" type="email" class="ma-form-input" required>
                </div>
                <div class="col-md-6">
                  <label class="ma-form-label">Number</label>
                  <input v-model="basicForm.mobile" type="text" class="ma-form-input" required>
                </div>
                <div class="col-md-6">
                  <label class="ma-form-label">State</label>
                  <input v-model="basicForm.state" type="text" class="ma-form-input" placeholder="State (optional)">
                </div>
                <div class="col-md-6">
                  <label class="ma-form-label">KYC Status</label>
                  <div class="ma-badge ma-badge--dark">
                    <span v-if="manageUser?.kv === 1"><i class="fas fa-check-circle me-1"></i>Verified</span>
                    <span v-else-if="manageUser?.kv === 2"><i class="fas fa-clock me-1"></i>Pending</span>
                    <span v-else><i class="fas fa-times-circle me-1"></i>Not Verified</span>
                  </div>
                  <div class="mt-2">
                    <button v-if="manageUser?.kv === 2 || manageUser?.kv === 1" class="ma-btn ma-btn--outline" @click="viewKYC(manageUser)">
                      <i class="fas fa-user-shield me-1"></i>View KYC
                    </button>
                  </div>
                </div>
              </div>

              <div class="ma-form-actions mt-3">
                <button class="ma-btn ma-btn--primary" :disabled="savingBasic" @click="saveBasic">
                  <i class="fas fa-spinner fa-spin me-1" v-if="savingBasic"></i>
                  {{ savingBasic ? 'Saving...' : 'Save Changes' }}
                </button>
              </div>
            </div>

            <!-- Sponsor -->
            <div v-else-if="manageTab === 'sponsor'" class="ma-pane">
              <div class="row g-3">
                <div class="col-12">
                  <div class="text-muted small mb-1">Current Sponsor</div>
                  <div class="ma-badge ma-badge--dark">
                    {{ manageUser?.referred_by ? ('ADS' + manageUser.referred_by) : 'None' }}
                  </div>
                </div>
                <div class="col-12">
                  <label class="ma-form-label">Sponsor ID (ADS ID / Username)</label>
                  <input v-model="sponsorId" type="text" class="ma-form-input" placeholder="ADS15001 or sponsor_username">
                  <small class="text-muted">Leave empty to clear sponsor.</small>
                </div>
              </div>
              <div class="ma-form-actions mt-3">
                <button class="ma-btn ma-btn--primary" :disabled="savingSponsor" @click="saveSponsor">
                  <i class="fas fa-spinner fa-spin me-1" v-if="savingSponsor"></i>
                  {{ savingSponsor ? 'Saving...' : 'Update Sponsor' }}
                </button>
              </div>
            </div>

            <!-- Password -->
            <div v-else-if="manageTab === 'password'" class="ma-pane">
              <div class="row g-3">
                <div class="col-12">
                  <label class="ma-form-label">New Password</label>
                  <input v-model="newPassword" type="text" class="ma-form-input" placeholder="New password" required>
                </div>
              </div>
              <div class="ma-form-actions mt-3">
                <button class="ma-btn ma-btn--primary" :disabled="savingPassword" @click="resetPassword">
                  <i class="fas fa-spinner fa-spin me-1" v-if="savingPassword"></i>
                  {{ savingPassword ? 'Saving...' : 'Reset Password' }}
                </button>
              </div>
            </div>

            <!-- Agent -->
            <div v-else-if="manageTab === 'agent'" class="ma-pane">
              <div v-if="agentLoading" class="ma-center py-4">
                <div class="ma-spinner"></div>
              </div>
              <div v-else class="row g-3">
                <div class="col-12">
                  <label class="ma-form-label">Mark User as Agent</label>
                  <div class="d-flex align-items-center gap-3">
                    <input id="is_agent" type="checkbox" v-model="agentForm.is_agent">
                    <label for="is_agent" class="mb-0 text-muted">Enable Agent dashboard + commissions</label>
                  </div>
                  <small class="text-muted">If disabled, affiliate dashboard pages are hidden for this user.</small>
                </div>

                <div v-if="agentForm.is_agent" class="col-12">
                  <div class="ma-soft-box">
                    <div class="fw-bold mb-2"><i class="fas fa-percentage me-1"></i>Commission Settings (per Agent)</div>

                    <div class="row g-3">
                      <div class="col-md-6">
                        <div class="ma-comm-row">
                          <div class="ma-comm-title">Registration</div>
                          <label class="ma-check"><input type="checkbox" v-model="agentForm.registration_enabled"> Enabled</label>
                          <select v-model="agentForm.registration_mode" class="ma-form-input ma-form-input--sm">
                            <option value="percent">Percent (%)</option>
                            <option value="fixed">Fixed (₹)</option>
                          </select>
                          <input v-model="agentForm.registration_value" type="number" min="0" step="0.0001" class="ma-form-input ma-form-input--sm" placeholder="Value">
                        </div>
                      </div>

                      <div class="col-md-6">
                        <div class="ma-comm-row">
                          <div class="ma-comm-title">KYC Fee</div>
                          <label class="ma-check"><input type="checkbox" v-model="agentForm.kyc_enabled"> Enabled</label>
                          <select v-model="agentForm.kyc_mode" class="ma-form-input ma-form-input--sm">
                            <option value="percent">Percent (%)</option>
                            <option value="fixed">Fixed (₹)</option>
                          </select>
                          <input v-model="agentForm.kyc_value" type="number" min="0" step="0.0001" class="ma-form-input ma-form-input--sm" placeholder="Value">
                        </div>
                      </div>

                      <div class="col-md-6">
                        <div class="ma-comm-row">
                          <div class="ma-comm-title">Withdrawal Fee (GST)</div>
                          <label class="ma-check"><input type="checkbox" v-model="agentForm.withdraw_fee_enabled"> Enabled</label>
                          <select v-model="agentForm.withdraw_fee_mode" class="ma-form-input ma-form-input--sm">
                            <option value="percent">Percent (%)</option>
                            <option value="fixed">Fixed (₹)</option>
                          </select>
                          <input v-model="agentForm.withdraw_fee_value" type="number" min="0" step="0.0001" class="ma-form-input ma-form-input--sm" placeholder="Value">
                        </div>
                      </div>

                      <div class="col-md-6">
                        <div class="ma-comm-row">
                          <div class="ma-comm-title">Upgrade</div>
                          <label class="ma-check"><input type="checkbox" v-model="agentForm.upgrade_enabled"> Enabled</label>
                          <select v-model="agentForm.upgrade_mode" class="ma-form-input ma-form-input--sm">
                            <option value="percent">Percent (%)</option>
                            <option value="fixed">Fixed (₹)</option>
                          </select>
                          <input v-model="agentForm.upgrade_value" type="number" min="0" step="0.0001" class="ma-form-input ma-form-input--sm" placeholder="Value">
                        </div>
                      </div>

                      <div class="col-12">
                        <div class="ma-comm-row">
                          <div class="ma-comm-title">Partner Override</div>
                          <label class="ma-check"><input type="checkbox" v-model="agentForm.partner_override_enabled"> Enabled</label>
                          <div class="text-muted small">Percent only</div>
                          <input v-model="agentForm.partner_override_percent" type="number" min="0" max="100" step="0.0001" class="ma-form-input ma-form-input--sm" placeholder="Percent">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-12">
                  <div class="ma-form-actions mt-2">
                    <button class="ma-btn ma-btn--primary" :disabled="savingAgent" @click="saveAgentSettings">
                      <i class="fas fa-spinner fa-spin me-1" v-if="savingAgent"></i>
                      {{ savingAgent ? 'Saving...' : 'Save Agent Settings' }}
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Transactions -->
            <div v-else class="ma-pane">
              <div class="ma-trx-header">
                <div class="text-muted">Latest transactions for this user</div>
                <button class="ma-btn-refresh" @click="fetchUserTransactions(trxPage)" :disabled="trxLoading">
                  <i class="fas fa-sync-alt" :class="{ 'fa-spin': trxLoading }"></i>
                </button>
              </div>
              <div class="table-responsive">
                <table class="ma-table ma-table--compact">
                  <thead>
                    <tr>
                      <th>TRX</th>
                      <th>Type</th>
                      <th>Amount</th>
                      <th>Details</th>
                      <th>Date</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-if="trxLoading">
                      <td colspan="5" class="ma-table__loading"><div class="ma-spinner"></div></td>
                    </tr>
                    <tr v-else-if="userTransactions.length === 0">
                      <td colspan="5" class="ma-table__empty">No transactions</td>
                    </tr>
                    <tr v-for="t in userTransactions" :key="t.id">
                      <td class="ma-mono">{{ t.trx }}</td>
                      <td><span class="ma-badge" :class="t.trx_type === '+' ? 'ma-badge--success' : 'ma-badge--danger'">{{ t.trx_type }}</span></td>
                      <td>₹{{ formatAmount(t.amount || 0) }}</td>
                      <td class="ma-truncate" :title="t.details">{{ t.details }}</td>
                      <td>{{ t.created_at }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <div v-if="trxLastPage > 1" class="ma-pagination mt-3">
                <button class="ma-page-btn" :disabled="trxPage === 1" @click="fetchUserTransactions(trxPage - 1)">
                  <i class="fas fa-chevron-left"></i>
                </button>
                <span class="ma-page-info">Page {{ trxPage }} of {{ trxLastPage }}</span>
                <button class="ma-page-btn" :disabled="trxPage === trxLastPage" @click="fetchUserTransactions(trxPage + 1)">
                  <i class="fas fa-chevron-right"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- KYC Review Modal -->
      <div v-if="showKYCModal" class="ma-modal-overlay" @click="closeKYCModal">
        <div class="ma-modal ma-modal--lg" @click.stop>
          <div class="ma-modal__header">
            <h5 class="ma-modal__title">
              <i class="fas fa-user-shield me-2"></i>Review KYC - {{ selectedUser?.firstname }} {{ selectedUser?.lastname }}
            </h5>
            <button class="ma-modal__close" @click="closeKYCModal">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <div class="ma-modal__body">
             <div class="row g-4">
              <!-- Bank Information -->
              <div class="col-12">
                <div class="ma-doc-card">
                  <div class="ma-doc-header">
                    <h6><i class="fas fa-university me-2"></i>Bank Information</h6>
                    <span class="ma-badge ma-badge--dark">User Bank Details</span>
                  </div>
                  <div class="p-3">
                    <div class="row g-3">
                      <div class="col-md-4">
                        <div class="text-muted small mb-1">Account Holder Name</div>
                        <div class="text-white fw-medium">{{ selectedUser?.bank_details?.account_holder_name || 'N/A' }}</div>
                      </div>
                      <div class="col-md-4">
                        <div class="text-muted small mb-1">Account Number</div>
                        <div class="text-white fw-medium">{{ selectedUser?.bank_details?.account_number || 'N/A' }}</div>
                      </div>
                      <div class="col-md-4">
                        <div class="text-muted small mb-1">IFSC Code</div>
                        <div class="text-white fw-medium">{{ selectedUser?.bank_details?.ifsc_code || 'N/A' }}</div>
                      </div>
                      <div class="col-md-4">
                        <div class="text-muted small mb-1">Bank Name</div>
                        <div class="text-white fw-medium">{{ selectedUser?.bank_details?.bank_name || 'N/A' }}</div>
                      </div>
                      <div class="col-md-4">
                        <div class="text-muted small mb-1">Branch Name</div>
                        <div class="text-white fw-medium">{{ selectedUser?.bank_details?.branch_name || 'N/A' }}</div>
                      </div>
                      <div class="col-md-4">
                        <div class="text-muted small mb-1">Bank Registered Number</div>
                        <div class="text-white fw-medium">{{ selectedUser?.bank_details?.bank_registered_no || 'N/A' }}</div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="ma-doc-card">
                  <div class="ma-doc-header">
                    <h6><i class="fas fa-id-card me-2"></i>Aadhaar Card</h6>
                    <span class="ma-badge ma-badge--dark">{{ selectedUser?.kyc_data?.aadhaar_number || 'N/A' }}</span>
                  </div>
                  <div class="ma-doc-preview">
                    <a
                      v-if="selectedUser?.kyc_data?.aadhaar_image || selectedUser?.kyc_data?.aadhaar_file"
                      :href="selectedUser?.kyc_data?.aadhaar_image || selectedUser?.kyc_data?.aadhaar_file"
                      target="_blank"
                      class="ma-doc-link"
                    >
                      <img v-if="selectedUser?.kyc_data?.aadhaar_image" :src="selectedUser.kyc_data.aadhaar_image" alt="Aadhaar" class="img-fluid rounded" />
                      <div v-else class="text-center py-5 text-muted w-100">
                        <i class="fa-regular fa-file fa-2x mb-2"></i>
                        <p class="mb-0">Open Aadhaar Document</p>
                      </div>
                    </a>
                    <div v-else class="text-center py-5 text-muted">
                      <i class="fas fa-image fa-2x mb-2"></i>
                      <p class="mb-0">No document uploaded</p>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="ma-doc-card">
                  <div class="ma-doc-header">
                    <h6><i class="fas fa-credit-card me-2"></i>PAN Card</h6>
                    <span class="ma-badge ma-badge--dark">{{ selectedUser?.kyc_data?.pan_number || 'N/A' }}</span>
                  </div>
                  <div class="ma-doc-preview">
                    <a
                      v-if="selectedUser?.kyc_data?.pan_image || selectedUser?.kyc_data?.pan_file"
                      :href="selectedUser?.kyc_data?.pan_image || selectedUser?.kyc_data?.pan_file"
                      target="_blank"
                      class="ma-doc-link"
                    >
                      <img v-if="selectedUser?.kyc_data?.pan_image" :src="selectedUser.kyc_data.pan_image" alt="PAN Card" class="img-fluid rounded" />
                      <div v-else class="text-center py-5 text-muted w-100">
                        <i class="fa-regular fa-file fa-2x mb-2"></i>
                        <p class="mb-0">Open PAN Document</p>
                      </div>
                    </a>
                    <div v-else class="text-center py-5 text-muted">
                      <i class="fas fa-image fa-2x mb-2"></i>
                      <p class="mb-0">No document uploaded</p>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Action Buttons -->
              <div class="col-12 mt-4" v-if="selectedUser?.kv === 2">
                <div v-if="!showRejectForm" class="d-flex gap-3 justify-content-end">
                   <button class="ma-btn ma-btn--danger" @click="showRejectForm = true">Reject</button>
                   <button class="ma-btn ma-btn--success" @click="approveKYC">Approve</button>
                </div>
                <div v-else class="ma-reject-form">
                  <textarea v-model="rejectionReason" class="ma-form-input mb-3" rows="2" placeholder="Rejection reason..."></textarea>
                  <div class="d-flex gap-3 justify-content-end">
                    <button class="ma-btn ma-btn--secondary" @click="showRejectForm = false">Cancel</button>
                    <button class="ma-btn ma-btn--danger" @click="rejectKYC">Confirm Reject</button>
                  </div>
                </div>
              </div>
              <div class="col-12 mt-4 text-center text-success fw-bold" v-else-if="selectedUser?.kv === 1">
                <i class="fas fa-check-circle me-2"></i> KYC Verified
              </div>
             </div>
          </div>
        </div>
      </div>

      <!-- Bank Account Edit Modal -->
      <div v-if="showBankModal" class="ma-modal-overlay" @click="closeBankModal">
        <div class="ma-modal" @click.stop>
          <div class="ma-modal__header">
            <h5 class="ma-modal__title">
              <i class="fas fa-university me-2"></i>Edit Bank Account - {{ selectedUser?.firstname }} {{ selectedUser?.lastname }}
            </h5>
            <button class="ma-modal__close" @click="closeBankModal">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <div class="ma-modal__body">
            <form @submit.prevent="saveBankDetails">
              <div class="ma-form-group">
                <label class="ma-form-label">
                  <i class="fas fa-user me-1"></i>Account Holder Name <span class="text-danger">*</span>
                </label>
                <input 
                  type="text" 
                  v-model="bankForm.account_holder_name" 
                  class="ma-form-input" 
                  placeholder="Enter account holder name"
                  required
                />
              </div>
              <div class="ma-form-group">
                <label class="ma-form-label">
                  <i class="fas fa-hashtag me-1"></i>Account Number <span class="text-danger">*</span>
                </label>
                <input 
                  type="text" 
                  v-model="bankForm.account_number" 
                  class="ma-form-input" 
                  placeholder="Enter account number"
                  required
                />
              </div>
              <div class="ma-form-group">
                <label class="ma-form-label">
                  <i class="fas fa-code me-1"></i>IFSC Code <span class="text-danger">*</span>
                </label>
                <input 
                  type="text" 
                  v-model="bankForm.ifsc_code" 
                  class="ma-form-input" 
                  placeholder="Enter IFSC code"
                  required
                  maxlength="20"
                />
              </div>
              <div class="ma-form-group">
                <label class="ma-form-label">
                  <i class="fas fa-university me-1"></i>Bank Name <span class="text-danger">*</span>
                </label>
                <input 
                  type="text" 
                  v-model="bankForm.bank_name" 
                  class="ma-form-input" 
                  placeholder="Enter bank name"
                  required
                />
              </div>
              <div class="ma-form-group">
                <label class="ma-form-label">
                  <i class="fas fa-id-card me-1"></i>Bank Registered Number
                </label>
                <input 
                  type="text" 
                  v-model="bankForm.bank_registered_no" 
                  class="ma-form-input" 
                  placeholder="Enter bank registered number (optional)"
                />
              </div>
              <div class="ma-form-group">
                <label class="ma-form-label">
                  <i class="fas fa-map-marker-alt me-1"></i>Branch Name
                </label>
                <input 
                  type="text" 
                  v-model="bankForm.branch_name" 
                  class="ma-form-input" 
                  placeholder="Enter branch name (optional)"
                />
              </div>
              <div class="ma-modal__footer">
                <button type="button" class="ma-btn ma-btn--secondary" @click="closeBankModal">
                  <i class="fas fa-times me-1"></i>Cancel
                </button>
                <button type="submit" class="ma-btn ma-btn--primary" :disabled="savingBank">
                  <i class="fas fa-save me-1"></i>{{ savingBank ? 'Saving...' : 'Save Changes' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </MasterAdminLayout>
</template>

<script>
import { ref, computed, onMounted, nextTick } from 'vue'
import MasterAdminLayout from '../../components/master_admin/MasterAdminLayout.vue'
import api from '../../services/api'

export default {
  name: 'MasterAdminUsers',
  components: { MasterAdminLayout },
  setup() {
    const users = ref([])
    const loading = ref(false)
    const searchQuery = ref('')
    const filterStatus = ref('')
    const totalUsers = ref(0)
    const currentPage = ref(1)
    const lastPage = ref(1)
    const activeCount = ref(0)
    const bannedCount = ref(0)
    const kycPendingCount = ref(0)
    const showPassword = ref({})
    const showCreateUserModal = ref(false)
    const creatingUser = ref(false)
    const createForm = ref({
      name: '',
      email: '',
      mobile: '',
      state: '',
      course_plan_id: 0,
      sponsor_id: '',
      password: ''
    })

    const coursePlans = ref([])
    const coursePlansLoading = ref(false)
    const activeCoursePlans = computed(() => {
      return (coursePlans.value || []).filter(p => Number(p?.status ?? 1) === 1)
    })
    const showBankModal = ref(false)
    const selectedUser = ref(null)
    // Manage User Modal (Phase 2)
    const showManageModal = ref(false)
    const manageUser = ref(null)
    const manageTab = ref('basic')
    const basicForm = ref({ name: '', email: '', mobile: '', state: '' })
    const sponsorId = ref('')
    const newPassword = ref('')
    const savingBasic = ref(false)
    const savingSponsor = ref(false)
    const savingPassword = ref(false)
    const agentLoading = ref(false)
    const savingAgent = ref(false)
    const agentForm = ref({
      is_agent: false,
      registration_enabled: true,
      registration_mode: 'percent',
      registration_value: 50,
      kyc_enabled: true,
      kyc_mode: 'percent',
      kyc_value: 50,
      withdraw_fee_enabled: true,
      withdraw_fee_mode: 'percent',
      withdraw_fee_value: 50,
      upgrade_enabled: true,
      upgrade_mode: 'percent',
      upgrade_value: 50,
      partner_override_enabled: false,
      partner_override_percent: 0
    })
    const userTransactions = ref([])
    const trxLoading = ref(false)
    const trxPage = ref(1)
    const trxLastPage = ref(1)

    const savingBank = ref(false)
    const bankForm = ref({
      account_holder_name: '',
      account_number: '',
      ifsc_code: '',
      bank_name: '',
      bank_registered_no: '',
      branch_name: ''
    })
    let searchTimeout = null

    const formatAmount = (n) => {
      if (!n && n !== 0) return '0.00'
      return parseFloat(n).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }
    const formatDate = (d) => d ? new Date(d).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' }) : '—'
    const getInitials = (u) => ((u.firstname?.[0] || '') + (u.lastname?.[0] || '') || u.username?.[0] || 'U').toUpperCase()

    const getApiErrorMessage = (payloadOrError) => {
      const d = payloadOrError?.response?.data ?? payloadOrError?.data ?? payloadOrError
      if (!d) return 'Request failed'
      if (typeof d === 'string') return d

      const msg = d.message
      if (Array.isArray(msg) && msg.length) return String(msg[0])
      if (typeof msg === 'string' && msg.trim()) return msg
      if (msg && typeof msg === 'object' && Array.isArray(msg.error) && msg.error.length) return String(msg.error[0])

      const errors = d.errors
      if (errors && typeof errors === 'object') {
        const firstKey = Object.keys(errors)[0]
        const firstVal = firstKey ? errors[firstKey] : null
        if (Array.isArray(firstVal) && firstVal.length) return String(firstVal[0])
      }

      return String(d.remark || 'Request failed')
    }

    const paginationPages = computed(() => {
      const pages = []
      const total = lastPage.value
      const cur = currentPage.value
      if (total <= 7) { for (let i = 1; i <= total; i++) pages.push(i); return pages }
      pages.push(1)
      if (cur > 3) pages.push('...')
      for (let i = Math.max(2, cur - 1); i <= Math.min(total - 1, cur + 1); i++) pages.push(i)
      if (cur < total - 2) pages.push('...')
      pages.push(total)
      return pages
    })

    const fetchUsers = async (page) => {
      if (page) currentPage.value = page
      loading.value = true
      try {
        const params = { page: currentPage.value, per_page: 20 }
        if (searchQuery.value) params.search = searchQuery.value
        if (filterStatus.value) params.status = filterStatus.value

        const res = await api.get('/admin/users', { params })
        if (res.data?.status === 'success' && res.data.data) {
          users.value = res.data.data.users || []
          totalUsers.value = res.data.data.total || 0
          currentPage.value = res.data.data.current_page || 1
          lastPage.value = res.data.data.last_page || 1
        }
      } catch (e) {
        console.error('Error fetching users:', e)
        if (window.notify) window.notify('error', 'Failed to load users')
      } finally {
        loading.value = false
      }
    }

    const fetchCounts = async () => {
      try {
        const res = await api.get('/admin/dashboard')
        if (res.data?.status === 'success' && res.data.data) {
          const d = res.data.data
          activeCount.value = d.verified_users || 0
          bannedCount.value = (d.total_users || 0) - (d.verified_users || 0)
          kycPendingCount.value = d.kyc_pending_users || 0
        }
      } catch (e) {}
    }

    const debounceSearch = () => {
      clearTimeout(searchTimeout)
      searchTimeout = setTimeout(() => fetchUsers(1), 400)
    }

    const togglePassword = (userId) => {
      showPassword.value[userId] = !showPassword.value[userId]
    }

    const copyPassword = async (password) => {
      if (!password) return
      try {
        await navigator.clipboard.writeText(password)
        if (window.notify) window.notify('success', 'Password copied to clipboard')
      } catch (e) {
        // Fallback for older browsers
        const textArea = document.createElement('textarea')
        textArea.value = password
        textArea.style.position = 'fixed'
        textArea.style.opacity = '0'
        document.body.appendChild(textArea)
        textArea.select()
        document.execCommand('copy')
        document.body.removeChild(textArea)
        if (window.notify) window.notify('success', 'Password copied to clipboard')
      }
    }

    const openManageUser = async (user) => {
      manageUser.value = user
      manageTab.value = 'basic'
      basicForm.value = {
        name: `${user.firstname || ''} ${user.lastname || ''}`.trim() || user.username || '',
        email: user.email || '',
        mobile: user.mobile || '',
        state: user.state || ''
      }
      sponsorId.value = user.referred_by ? `ADS${user.referred_by}` : ''
      newPassword.value = ''
      userTransactions.value = []
      trxPage.value = 1
      trxLastPage.value = 1
      agentLoading.value = false
      savingAgent.value = false
      agentForm.value = { ...agentForm.value, is_agent: !!(user.is_agent) }
      await nextTick()
      showManageModal.value = true
    }

    const closeManageModal = () => {
      showManageModal.value = false
      manageUser.value = null
      manageTab.value = 'basic'
      basicForm.value = { name: '', email: '', mobile: '', state: '' }
      sponsorId.value = ''
      newPassword.value = ''
      userTransactions.value = []
      trxPage.value = 1
      trxLastPage.value = 1
      agentLoading.value = false
      savingAgent.value = false
      agentForm.value = {
        is_agent: false,
        registration_enabled: true,
        registration_mode: 'percent',
        registration_value: 50,
        kyc_enabled: true,
        kyc_mode: 'percent',
        kyc_value: 50,
        withdraw_fee_enabled: true,
        withdraw_fee_mode: 'percent',
        withdraw_fee_value: 50,
        upgrade_enabled: true,
        upgrade_mode: 'percent',
        upgrade_value: 50,
        partner_override_enabled: false,
        partner_override_percent: 0
      }
    }

    const fetchAgentSettings = async () => {
      if (!manageUser.value) return
      agentLoading.value = true
      try {
        const res = await api.get(`/admin/user/${manageUser.value.id}/agent-commissions`, { __skipLoader: true })
        const d = res?.data?.data || res?.data
        if (d) {
          const s = d.settings || null
          agentForm.value.is_agent = !!d.is_agent
          if (s) {
            agentForm.value.registration_enabled = !!s.registration_enabled
            agentForm.value.registration_mode = s.registration_mode || 'percent'
            agentForm.value.registration_value = Number(s.registration_value ?? 0)
            agentForm.value.kyc_enabled = !!s.kyc_enabled
            agentForm.value.kyc_mode = s.kyc_mode || 'percent'
            agentForm.value.kyc_value = Number(s.kyc_value ?? 0)
            agentForm.value.withdraw_fee_enabled = !!s.withdraw_fee_enabled
            agentForm.value.withdraw_fee_mode = s.withdraw_fee_mode || 'percent'
            agentForm.value.withdraw_fee_value = Number(s.withdraw_fee_value ?? 0)
            agentForm.value.upgrade_enabled = !!s.upgrade_enabled
            agentForm.value.upgrade_mode = s.upgrade_mode || 'percent'
            agentForm.value.upgrade_value = Number(s.upgrade_value ?? 0)
            agentForm.value.partner_override_enabled = !!s.partner_override_enabled
            agentForm.value.partner_override_percent = Number(s.partner_override_percent ?? 0)
          }
        }
      } catch (e) {
        if (window.notify) window.notify('error', getApiErrorMessage(e) || 'Failed to load agent settings')
      } finally {
        agentLoading.value = false
      }
    }

    const saveAgentSettings = async () => {
      if (!manageUser.value) return
      savingAgent.value = true
      try {
        // 1) Update agent tag
        const tagRes = await api.post(`/admin/user/${manageUser.value.id}/agent`, { is_agent: !!agentForm.value.is_agent })
        if (tagRes.data?.status !== 'success') {
          if (window.notify) window.notify('error', getApiErrorMessage(tagRes) || 'Failed to update agent status')
          return
        }

        // 2) Update commission settings (only meaningful when agent enabled)
        if (agentForm.value.is_agent) {
          const payload = {
            registration_enabled: !!agentForm.value.registration_enabled,
            registration_mode: agentForm.value.registration_mode,
            registration_value: Number(agentForm.value.registration_value || 0),
            kyc_enabled: !!agentForm.value.kyc_enabled,
            kyc_mode: agentForm.value.kyc_mode,
            kyc_value: Number(agentForm.value.kyc_value || 0),
            withdraw_fee_enabled: !!agentForm.value.withdraw_fee_enabled,
            withdraw_fee_mode: agentForm.value.withdraw_fee_mode,
            withdraw_fee_value: Number(agentForm.value.withdraw_fee_value || 0),
            upgrade_enabled: !!agentForm.value.upgrade_enabled,
            upgrade_mode: agentForm.value.upgrade_mode,
            upgrade_value: Number(agentForm.value.upgrade_value || 0),
            partner_override_enabled: !!agentForm.value.partner_override_enabled,
            partner_override_percent: Number(agentForm.value.partner_override_percent || 0)
          }
          const res = await api.post(`/admin/user/${manageUser.value.id}/agent-commissions`, payload)
          if (res.data?.status !== 'success') {
            if (window.notify) window.notify('error', getApiErrorMessage(res) || 'Failed to update commission settings')
            return
          }
        }

        if (window.notify) window.notify('success', 'Agent settings saved')
        // Update local state + list
        manageUser.value = { ...manageUser.value, is_agent: !!agentForm.value.is_agent }
        fetchUsers(currentPage.value)
      } catch (e) {
        if (window.notify) window.notify('error', getApiErrorMessage(e) || 'Failed to save agent settings')
      } finally {
        savingAgent.value = false
      }
    }

    const saveBasic = async () => {
      if (!manageUser.value) return
      savingBasic.value = true
      try {
        const payload = {
          name: basicForm.value?.name || '',
          email: basicForm.value?.email || '',
          mobile: basicForm.value?.mobile || '',
          state: basicForm.value?.state || ''
        }
        const res = await api.post(`/admin/user/${manageUser.value.id}/basic-update`, payload)
        if (res.data?.status === 'success') {
          if (window.notify) window.notify('success', 'User updated successfully')
          fetchUsers(currentPage.value)
          // Update local user
          manageUser.value = { ...manageUser.value, ...(res.data.data?.user || {}) }
        } else {
          if (window.notify) window.notify('error', getApiErrorMessage(res) || 'Update failed')
        }
      } catch (e) {
        if (window.notify) window.notify('error', getApiErrorMessage(e) || 'Update failed')
      } finally {
        savingBasic.value = false
      }
    }

    const saveSponsor = async () => {
      if (!manageUser.value) return
      savingSponsor.value = true
      try {
        const res = await api.post(`/admin/user/${manageUser.value.id}/change-sponsor`, { sponsor_id: sponsorId.value || '' })
        if (res.data?.status === 'success') {
          if (window.notify) window.notify('success', 'Sponsor updated successfully')
          const referredBy = res.data.data?.referred_by ?? null
          manageUser.value = { ...manageUser.value, referred_by: referredBy }
          fetchUsers(currentPage.value)
        } else {
          if (window.notify) window.notify('error', getApiErrorMessage(res) || 'Sponsor update failed')
        }
      } catch (e) {
        if (window.notify) window.notify('error', getApiErrorMessage(e) || 'Sponsor update failed')
      } finally {
        savingSponsor.value = false
      }
    }

    const resetPassword = async () => {
      if (!manageUser.value || !newPassword.value) return
      savingPassword.value = true
      try {
        const res = await api.post(`/admin/user/${manageUser.value.id}/reset-password`, { password: newPassword.value })
        if (res.data?.status === 'success') {
          if (window.notify) window.notify('success', 'Password reset successfully')
          newPassword.value = ''
          fetchUsers(currentPage.value)
        } else {
          if (window.notify) window.notify('error', getApiErrorMessage(res) || 'Password reset failed')
        }
      } catch (e) {
        if (window.notify) window.notify('error', getApiErrorMessage(e) || 'Password reset failed')
      } finally {
        savingPassword.value = false
      }
    }

    const fetchUserTransactions = async (page = 1) => {
      if (!manageUser.value) return
      trxLoading.value = true
      trxPage.value = page
      try {
        const res = await api.get('/admin/transactions', { params: { user_id: manageUser.value.id, page, per_page: 10 } })
        if (res.data?.status === 'success' && res.data.data) {
          userTransactions.value = res.data.data.transactions || []
          trxLastPage.value = Number(res.data.data.last_page || 1)
        } else {
          userTransactions.value = []
          trxLastPage.value = 1
        }
      } catch (e) {
        userTransactions.value = []
        trxLastPage.value = 1
      } finally {
        trxLoading.value = false
      }
    }

    const toggleUserStatus = async (user) => {
      const action = user.status === 'active' ? 'ban' : 'unban'
      const actionText = user.status === 'active' ? 'ban' : 'unban'
      const confirmMsg = `Are you sure you want to ${actionText} user ${user.firstname} ${user.lastname} (ID: ${user.id})?`
      
      if (!confirm(confirmMsg)) return

      try {
        // Call API to ban/unban user
        const res = await api.post(`/admin/user/${user.id}/${action}`)
        if (res.data?.status === 'success') {
          if (window.notify) window.notify('success', `User ${actionText}ned successfully`)
          fetchUsers() // Refresh list
        } else {
          if (window.notify) window.notify('error', res.data?.message || `Failed to ${actionText} user`)
        }
      } catch (e) {
        console.error('Error toggling user status:', e)
        if (window.notify) window.notify('error', `Failed to ${actionText} user`)
      }
    }

    const editBankAccount = async (user) => {
      selectedUser.value = user
      
      // Handle bank_details - check if it's nested or direct
      let bankData = {}
      if (user.bank_details && typeof user.bank_details === 'object' && Object.keys(user.bank_details).length > 0) {
        bankData = user.bank_details
      } else if (user.account_holder_name || user.account_number || user.ifsc_code) {
        // Fallback: check if bank fields are directly on user object
        bankData = {
          account_holder_name: user.account_holder_name || '',
          account_number: user.account_number || '',
          ifsc_code: user.ifsc_code || '',
          bank_name: user.bank_name || '',
          bank_registered_no: user.bank_registered_no || '',
          branch_name: user.branch_name || ''
        }
      }
      
      // Set form values
      bankForm.value.account_holder_name = bankData.account_holder_name || ''
      bankForm.value.account_number = bankData.account_number || ''
      bankForm.value.ifsc_code = bankData.ifsc_code || ''
      bankForm.value.bank_name = bankData.bank_name || ''
      bankForm.value.bank_registered_no = bankData.bank_registered_no || ''
      bankForm.value.branch_name = bankData.branch_name || ''
      
      // Wait for Vue to update, then show modal
      await nextTick()
      showBankModal.value = true
    }

    const closeBankModal = () => {
      showBankModal.value = false
      selectedUser.value = null
      bankForm.value = {
        account_holder_name: '',
        account_number: '',
        ifsc_code: '',
        bank_name: '',
        bank_registered_no: '',
        branch_name: ''
      }
    }

    const saveBankDetails = async () => {
      if (!selectedUser.value) return
      
      savingBank.value = true
      try {
        const res = await api.post(`/admin/user/${selectedUser.value.id}/bank-details`, bankForm.value)
        if (res.data?.status === 'success') {
          if (window.notify) window.notify('success', 'Bank details updated successfully')
          closeBankModal()
          fetchUsers() // Refresh list to get updated data
        } else {
          if (window.notify) window.notify('error', res.data?.message || 'Failed to update bank details')
        }
      } catch (e) {
        console.error('Error updating bank details:', e)
        const errorMsg = e.response?.data?.message || e.response?.data?.data?.[0] || 'Failed to update bank details'
        if (window.notify) window.notify('error', errorMsg)
      } finally {
        savingBank.value = false
      }
    }

    // KYC Logic
    const showKYCModal = ref(false)
    const showRejectForm = ref(false)
    const rejectionReason = ref('')

    const viewKYC = (user) => {
      selectedUser.value = user
      showKYCModal.value = true
      showRejectForm.value = false
      rejectionReason.value = ''
    }

    const closeKYCModal = () => {
      showKYCModal.value = false
      selectedUser.value = null
      showRejectForm.value = false
    }

    const approveKYC = async () => {
      if (!selectedUser.value) return
      if (!confirm(`Verify KYC for ${selectedUser.value.firstname}?`)) return
      
      try {
        const res = await api.post(`/admin/user/${selectedUser.value.id}/approve-kyc`)
        if (res.data?.status === 'success') {
          if (window.notify) window.notify('success', 'KYC Approved')
          closeKYCModal()
          fetchUsers()
        }
      } catch (e) {
        if (window.notify) window.notify('error', e.response?.data?.message || 'Failed to approve')
      }
    }

    const rejectKYC = async () => {
      if (!selectedUser.value || !rejectionReason.value) return
      
      try {
        const res = await api.post(`/admin/user/${selectedUser.value.id}/reject-kyc`, { reason: rejectionReason.value })
        if (res.data?.status === 'success') {
          if (window.notify) window.notify('success', 'KYC Rejected')
          closeKYCModal()
          fetchUsers()
        }
      } catch (e) {
        if (window.notify) window.notify('error', e.response?.data?.message || 'Failed to reject')
      }
    }

    const openCreateUserModal = () => {
      showCreateUserModal.value = true
      createForm.value = {
        name: '',
        email: '',
        mobile: '',
        state: '',
        course_plan_id: 0,
        sponsor_id: '',
        password: ''
      }
      fetchCoursePlans()
    }

    const closeCreateUserModal = () => {
      showCreateUserModal.value = false
    }

    const createUser = async () => {
      creatingUser.value = true
      try {
        const payload = {
          name: createForm.value.name || '',
          email: createForm.value.email || '',
          mobile: createForm.value.mobile || '',
          state: createForm.value.state || '',
          password: createForm.value.password || '',
          sponsor_id: createForm.value.sponsor_id || '',
          course_plan_id: Number(createForm.value.course_plan_id || 0)
        }
        const res = await api.post('/admin/users/create', payload)
        if (res.data?.status === 'success') {
          const u = res.data.data?.user
          const plan = res.data.data?.activated_course_plan
          if (window.notify) {
            window.notify('success', plan?.name
              ? `User created: ADS${u?.id || ''} (Package activated: ${plan.name})`
              : `User created: ADS${u?.id || ''}`
            )
          }
          closeCreateUserModal()
          fetchUsers(1)
          fetchCounts()
        } else {
          if (window.notify) window.notify('error', res.data?.message?.[0] || 'Create user failed')
        }
      } catch (e) {
        if (window.notify) window.notify('error', e.response?.data?.message?.[0] || e.message || 'Create user failed')
      } finally {
        creatingUser.value = false
      }
    }

    async function fetchCoursePlans () {
      if (coursePlansLoading.value) return
      coursePlansLoading.value = true
      try {
        const res = await api.get('/admin/packages/all')
        if (res.data?.status === 'success' && res.data.data) {
          coursePlans.value = res.data.data.packages || []
        } else {
          coursePlans.value = []
        }
      } catch (e) {
        coursePlans.value = []
      } finally {
        coursePlansLoading.value = false
      }
    }

    onMounted(() => {
      fetchUsers(1)
      fetchCounts()
    })

    return {
      users, loading, searchQuery, filterStatus,
      totalUsers, currentPage, lastPage, paginationPages,
      activeCount, bannedCount, kycPendingCount,
      showPassword, togglePassword, copyPassword, openManageUser, closeManageModal,
      showManageModal, manageUser, manageTab, basicForm, sponsorId, newPassword,
      savingBasic, savingSponsor, savingPassword,
      agentLoading, savingAgent, agentForm, fetchAgentSettings, saveAgentSettings,
      userTransactions, trxLoading, trxPage, trxLastPage, fetchUserTransactions,
      saveBasic, saveSponsor, resetPassword,
      toggleUserStatus,
      formatAmount, formatDate, getInitials,
      fetchUsers, debounceSearch,
      showCreateUserModal, creatingUser, createForm, openCreateUserModal, closeCreateUserModal, createUser,
      coursePlansLoading, activeCoursePlans,
      showBankModal, selectedUser, bankForm, savingBank,
      editBankAccount, closeBankModal, saveBankDetails,
      showKYCModal, showRejectForm, rejectionReason, viewKYC, closeKYCModal, approveKYC, rejectKYC
    }
  }
}
</script>

<style scoped>
.ma-users { animation: maFade 0.4s ease-out; }
@keyframes maFade { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }

/* ═══ Mini Stats ═══ */
.ma-stat-mini {
  display: flex; align-items: center; gap: 0.75rem;
  background: rgba(30, 41, 59, 0.8); border: 1px solid rgba(255,255,255,0.08);
  border-radius: 14px; padding: 1rem 1.15rem; cursor: pointer;
  transition: all 0.25s ease;
}
.ma-stat-mini:hover { background: rgba(30, 41, 59, 1); transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.3); }
.ma-stat-mini__icon {
  width: 42px; height: 42px; border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.1rem; color: #fff; flex-shrink: 0;
}
.ma-stat-mini__icon--blue { background: linear-gradient(135deg, #3b82f6, #6366f1); }
.ma-stat-mini__icon--green { background: linear-gradient(135deg, #10b981, #059669); }
.ma-stat-mini__icon--red { background: linear-gradient(135deg, #ef4444, #dc2626); }
.ma-stat-mini__icon--amber { background: linear-gradient(135deg, #f59e0b, #d97706); }
.ma-stat-mini__val { display: block; color: #f1f5f9; font-size: 1.35rem; font-weight: 800; line-height: 1.2; }
.ma-stat-mini__lbl { display: block; color: #94a3b8; font-size: 0.78rem; font-weight: 600; }

.ma-tabs { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1rem; }
.ma-tab { border: 1px solid rgba(255,255,255,0.12); background: rgba(15,23,42,0.35); color: #e2e8f0; padding: 0.4rem 0.75rem; border-radius: 999px; font-weight: 700; font-size: 0.85rem; }
.ma-tab--active { background: rgba(79,70,229,0.9); border-color: rgba(99,102,241,0.7); }
.ma-pane { padding-top: 0.25rem; }
.ma-soft-box {
  background: rgba(15, 23, 42, 0.35);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 14px;
  padding: 1rem;
}
.ma-form-input--sm {
  padding: 0.45rem 0.65rem;
  font-size: 0.85rem;
}
.ma-comm-row {
  display: grid;
  grid-template-columns: 1fr auto 160px 1fr;
  gap: 0.6rem;
  align-items: center;
  padding: 0.65rem 0.75rem;
  border: 1px solid rgba(255,255,255,0.06);
  border-radius: 12px;
  background: rgba(2, 6, 23, 0.35);
}
.ma-comm-title { font-weight: 800; color: #e2e8f0; }
.ma-check { display: inline-flex; align-items: center; gap: 0.35rem; color: #cbd5e1; font-weight: 600; margin: 0; }
@media (max-width: 768px) {
  .ma-comm-row { grid-template-columns: 1fr; }
}
.ma-table--compact th, .ma-table--compact td { padding: 0.55rem 0.75rem; }
.ma-mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; font-size: 0.85rem; }
.ma-truncate { max-width: 380px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.ma-trx-header { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 0.75rem; }

/* ═══ Card ═══ */
.ma-card {
  background: rgba(30, 41, 59, 0.8); border: 1px solid rgba(255,255,255,0.08);
  border-radius: 18px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
}
.ma-card__body { padding: 1rem 1.25rem; }
.ma-card__header {
  padding: 1.25rem 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.06);
  display: flex; align-items: center; justify-content: space-between;
  background: rgba(15, 23, 42, 0.4);
}
.ma-card__header--gradient {
  background: linear-gradient(135deg, rgba(99,102,241,0.1) 0%, rgba(139,92,246,0.1) 100%);
  border-bottom: 1px solid rgba(99,102,241,0.2);
}
.ma-card__title { margin: 0 0 0.25rem 0; color: #f1f5f9; font-weight: 700; font-size: 1.15rem; }
.ma-card__title i { color: #818cf8; }
.ma-card__subtitle {
  margin: 0; color: #94a3b8; font-size: 0.8rem; font-weight: 500;
}
.ma-card__count {
  color: #64748b; font-size: 0.85rem; font-weight: 600;
  background: rgba(99,102,241,0.15); padding: 0.4rem 0.8rem;
  border-radius: 8px; border: 1px solid rgba(99,102,241,0.2);
}
.ma-table-card { box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05); }

/* ═══ Search ═══ */
.ma-search-box {
  display: flex; align-items: center; gap: 0.5rem;
  background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255,255,255,0.1);
  border-radius: 10px; padding: 0 1rem;
}
.ma-search-box i { color: #64748b; font-size: 0.9rem; }
.ma-search-input {
  flex: 1; background: none; border: none; outline: none;
  color: #f1f5f9; padding: 0.6rem 0; font-size: 0.9rem;
}
.ma-search-input::placeholder { color: #475569; }
.ma-select {
  background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255,255,255,0.1);
  border-radius: 10px; padding: 0.6rem 1rem; color: #f1f5f9;
  font-size: 0.88rem; outline: none; cursor: pointer;
}
.ma-select option { background: #1e293b; color: #f1f5f9; }
.ma-btn-refresh {
  width: 40px; height: 40px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.1);
  background: rgba(15, 23, 42, 0.6); color: #94a3b8; cursor: pointer;
  display: flex; align-items: center; justify-content: center; transition: all 0.2s;
}
.ma-btn-refresh:hover:not(:disabled) { color: #f1f5f9; background: rgba(99,102,241,0.2); }
.ma-btn-refresh:disabled { opacity: 0.5; cursor: not-allowed; }

/* ═══ Table ═══ */
.ma-table-wrapper {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}
.ma-table { width: 100%; border-collapse: collapse; font-size: 0.88rem; min-width: 1000px; }
.ma-table th {
  padding: 1rem 1.25rem; color: #94a3b8; font-weight: 700;
  text-transform: uppercase; font-size: 0.7rem; letter-spacing: 0.08em;
  border-bottom: 2px solid rgba(99,102,241,0.2); white-space: nowrap; text-align: left;
  background: rgba(15, 23, 42, 0.3);
}
.ma-table th i { color: #818cf8; font-size: 0.75rem; }
.ma-table td {
  padding: 1rem 1.25rem; color: #cbd5e1;
  border-bottom: 1px solid rgba(255,255,255,0.05); vertical-align: middle;
}
.ma-table-row { transition: all 0.2s ease; }
.ma-table-row:hover {
  background: rgba(99,102,241,0.05) !important;
  transform: scale(1.001);
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

/* ═══ ID Badge ═══ */
.ma-id-badge {
  display: inline-flex; align-items: center; gap: 0.5rem;
  background: rgba(99,102,241,0.15); padding: 0.4rem 0.75rem;
  border-radius: 8px; border: 1px solid rgba(99,102,241,0.2);
  color: #a5b4fc; font-weight: 700; font-size: 0.85rem;
}
.ma-id-badge i { font-size: 0.9rem; }

/* ═══ Email Cell ═══ */
.ma-email-cell {
  display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;
}
.ma-email-cell i {
  color: #818cf8; font-size: 0.85rem; flex-shrink: 0;
}
.ma-table__email {
  font-size: 0.85rem; max-width: 200px; overflow: hidden;
  text-overflow: ellipsis; white-space: nowrap; color: #cbd5e1;
}

/* ═══ Mobile Cell ═══ */
.ma-mobile-cell {
  display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;
}
.ma-mobile-cell i {
  color: #818cf8; font-size: 0.85rem; flex-shrink: 0;
}
.ma-mobile-cell > span:not(.ma-badge) {
  color: #cbd5e1; font-size: 0.85rem;
}

/* ═══ Balance Cell ═══ */
.ma-balance-cell {
  display: flex; align-items: center; gap: 0.5rem;
}
.ma-balance-cell i {
  color: #34d399; font-size: 0.85rem;
}
.ma-table__balance {
  color: #34d399 !important; font-weight: 700; font-size: 0.95rem;
  background: rgba(16, 185, 129, 0.1); padding: 0.3rem 0.6rem;
  border-radius: 6px; border: 1px solid rgba(16, 185, 129, 0.2);
}

/* ═══ Deposit Cell ═══ */
.ma-deposit-cell {
  display: flex; align-items: center; gap: 0.5rem;
}
.ma-deposit-cell i {
  color: #60a5fa; font-size: 0.85rem;
}
.ma-table__deposit {
  color: #60a5fa !important; font-weight: 700; font-size: 0.95rem;
  background: rgba(59, 130, 246, 0.1); padding: 0.3rem 0.6rem;
  border-radius: 6px; border: 1px solid rgba(59, 130, 246, 0.2);
}

/* ═══ Status Cell ═══ */
.ma-status-cell {
  display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap;
}
.ma-status-cell .ma-badge {
  display: inline-flex; align-items: center; gap: 0.3rem;
  padding: 0.25rem 0.5rem; font-size: 0.7rem;
}
.ma-status-cell .ma-badge i {
  font-size: 0.65rem;
}
.ma-table__date { color: #64748b; font-size: 0.82rem; white-space: nowrap; }
.ma-table__loading, .ma-table__empty {
  text-align: center; padding: 3rem 1rem !important; color: #64748b;
}
.ma-table__empty i { font-size: 2.5rem; margin-bottom: 0.75rem; display: block; color: #475569; }
.ma-table__empty p { margin: 0; }
.ma-table__loading { display: flex; align-items: center; justify-content: center; gap: 0.75rem; }

/* ═══ Password Cell ═══ */
.ma-password-cell {
  display: flex; align-items: center; gap: 0.5rem;
}
.ma-password-wrapper {
  flex: 1; min-width: 0;
}
.ma-password-masked {
  color: #64748b; font-family: 'Courier New', monospace; font-size: 0.9rem;
  letter-spacing: 0.15em; font-weight: 600;
}
.ma-password-text {
  color: #f1f5f9; font-family: 'Courier New', monospace; font-size: 0.8rem;
  word-break: break-all; max-width: 250px; display: inline-block;
  background: rgba(15, 23, 42, 0.5); padding: 0.3rem 0.6rem;
  border-radius: 6px; border: 1px solid rgba(255,255,255,0.1);
}
.ma-btn-toggle-password, .ma-btn-copy {
  width: 32px; height: 32px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1);
  background: rgba(15, 23, 42, 0.6); color: #94a3b8; cursor: pointer;
  display: flex; align-items: center; justify-content: center; font-size: 0.8rem;
  transition: all 0.2s; flex-shrink: 0;
}
.ma-btn-toggle-password:hover {
  background: rgba(99,102,241,0.2); color: #a5b4fc; border-color: rgba(99,102,241,0.3);
  transform: translateY(-1px);
}
.ma-btn-copy {
  background: rgba(59, 130, 246, 0.15); color: #60a5fa; border-color: rgba(59, 130, 246, 0.2);
}
.ma-btn-copy:hover {
  background: rgba(59, 130, 246, 0.25); color: #93c5fd; border-color: rgba(59, 130, 246, 0.3);
  transform: translateY(-1px);
}

/* ═══ Action Buttons ═══ */
.ma-action-buttons {
  display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;
  position: relative;
}
.ma-action-btn {
  width: 36px; height: 36px; border-radius: 10px; border: none;
  background: rgba(15, 23, 42, 0.6); color: #94a3b8; cursor: pointer;
  display: flex; align-items: center; justify-content: center; font-size: 0.85rem;
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); flex-shrink: 0;
  position: relative; border: 1px solid rgba(255,255,255,0.08);
}
.ma-action-btn:hover {
  transform: translateY(-2px) scale(1.05);
  box-shadow: 0 6px 16px rgba(0,0,0,0.2);
}
.ma-action-btn:active { transform: translateY(0) scale(0.98); }
.ma-action-tooltip {
  position: absolute; bottom: -32px; left: 50%; transform: translateX(-50%);
  background: rgba(15, 23, 42, 0.95); color: #f1f5f9; padding: 0.3rem 0.6rem;
  border-radius: 6px; font-size: 0.7rem; white-space: nowrap;
  opacity: 0; pointer-events: none; transition: opacity 0.2s;
  border: 1px solid rgba(255,255,255,0.1); z-index: 10;
}
.ma-action-btn:hover .ma-action-tooltip {
  opacity: 1;
}
.ma-action-btn--view {
  background: rgba(59, 130, 246, 0.15); color: #60a5fa;
}

.ma-doc-card {
  background: rgba(15, 23, 42, 0.5); border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 12px; overflow: hidden; height: 100%;
}
.ma-doc-header {
  padding: 1rem; border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  display: flex; justify-content: space-between; align-items: center;
}
.ma-doc-header h6 { margin: 0; color: #e2e8f0; font-size: 0.9rem; }
.ma-doc-preview {
  padding: 1rem; display: flex; align-items: center; justify-content: center;
  min-height: 200px; background: rgba(0, 0, 0, 0.2);
}
.ma-doc-link img { display: block; max-width: 100%; transition: transform 0.3s; }

.ma-modal--lg { max-width: 800px; }
.ma-reject-form textarea { width: 100%; border-radius: 8px; padding: 1rem; }

.ma-action-btn--view:hover {
  background: rgba(59, 130, 246, 0.25); color: #93c5fd;
  border-color: rgba(59, 130, 246, 0.3);
  box-shadow: 0 6px 16px rgba(59, 130, 246, 0.3);
}
.ma-action-btn--edit {
  background: rgba(245, 158, 11, 0.15); color: #fbbf24;
  border-color: rgba(245, 158, 11, 0.2);
}
.ma-action-btn--edit:hover {
  background: rgba(245, 158, 11, 0.25); color: #fcd34d;
  border-color: rgba(245, 158, 11, 0.3);
  box-shadow: 0 6px 16px rgba(245, 158, 11, 0.3);
}
.ma-action-btn--ban {
  background: rgba(239, 68, 68, 0.15); color: #f87171;
  border-color: rgba(239, 68, 68, 0.2);
}
.ma-action-btn--ban:hover {
  background: rgba(239, 68, 68, 0.25); color: #fca5a5;
  border-color: rgba(239, 68, 68, 0.3);
  box-shadow: 0 6px 16px rgba(239, 68, 68, 0.3);
}
.ma-action-btn--unban {
  background: rgba(16, 185, 129, 0.15); color: #34d399;
  border-color: rgba(16, 185, 129, 0.2);
}
.ma-action-btn--unban:hover {
  background: rgba(16, 185, 129, 0.25); color: #6ee7b7;
  border-color: rgba(16, 185, 129, 0.3);
  box-shadow: 0 6px 16px rgba(16, 185, 129, 0.3);
}
.ma-action-btn--bank {
  background: rgba(139, 92, 246, 0.15); color: #a78bfa;
  border-color: rgba(139, 92, 246, 0.2);
}
.ma-action-btn--bank:hover {
  background: rgba(139, 92, 246, 0.25); color: #c4b5fd;
  border-color: rgba(139, 92, 246, 0.3);
  box-shadow: 0 6px 16px rgba(139, 92, 246, 0.3);
}

/* ═══ User Cell ═══ */
.ma-user-cell { display: flex; align-items: center; gap: 0.75rem; }
.ma-user-avatar {
  width: 36px; height: 36px; border-radius: 10px;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  color: #fff; font-weight: 700; font-size: 0.8rem;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.ma-user-name { display: block; color: #f1f5f9; font-weight: 600; font-size: 0.9rem; }
.ma-user-username { display: block; color: #64748b; font-size: 0.78rem; }

/* ═══ Badges ═══ */
.ma-badge {
  display: inline-block; padding: 0.2rem 0.6rem; border-radius: 6px;
  font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em;
}
.ma-badge--success { background: rgba(16,185,129,0.15); color: #34d399; }
.ma-badge--danger { background: rgba(239,68,68,0.15); color: #f87171; }
.ma-badge--warning { background: rgba(245,158,11,0.15); color: #fbbf24; }
.ma-badge--secondary { background: rgba(100,116,139,0.15); color: #94a3b8; }

/* ═══ Pagination ═══ */
.ma-pagination {
  display: flex; align-items: center; justify-content: center; gap: 0.4rem;
  padding: 1rem 1.25rem; border-top: 1px solid rgba(255,255,255,0.06); flex-wrap: wrap;
}
.ma-page-btn {
  width: 36px; height: 36px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.08);
  background: transparent; color: #94a3b8; cursor: pointer; font-size: 0.85rem; font-weight: 600;
  display: flex; align-items: center; justify-content: center; transition: all 0.2s;
}
.ma-page-btn:hover:not(:disabled):not(.ma-page-dots) { background: rgba(99,102,241,0.15); color: #a5b4fc; border-color: rgba(99,102,241,0.3); }
.ma-page-btn--active { background: #6366f1 !important; color: #fff !important; border-color: #6366f1 !important; }
.ma-page-btn:disabled { opacity: 0.4; cursor: not-allowed; }
.ma-page-dots { border: none; }
.ma-page-info { color: #475569; font-size: 0.8rem; margin-left: 0.75rem; }

/* ═══ Spinner ═══ */
.ma-spinner {
  width: 24px; height: 24px; border: 3px solid rgba(255,255,255,0.1);
  border-top-color: #818cf8; border-radius: 50%; animation: maSpin 0.7s linear infinite;
}
@keyframes maSpin { to { transform: rotate(360deg); } }

/* ═══ Modal ═══ */
.ma-modal-overlay {
  position: fixed; top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0, 0, 0, 0.75); backdrop-filter: blur(4px);
  display: flex; align-items: center; justify-content: center;
  z-index: 9999; padding: 1rem; animation: maFade 0.2s ease-out;
}
.ma-modal {
  background: rgba(30, 41, 59, 0.95); border: 1px solid rgba(255,255,255,0.1);
  border-radius: 18px; width: 100%; max-width: 600px; max-height: 90vh;
  overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.5);
  display: flex; flex-direction: column; animation: maSlideUp 0.3s ease-out;
}
@keyframes maSlideUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
.ma-modal__header {
  padding: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.1);
  display: flex; align-items: center; justify-content: space-between;
  background: linear-gradient(135deg, rgba(139,92,246,0.1) 0%, rgba(99,102,241,0.1) 100%);
}
.ma-modal__title {
  margin: 0; color: #f1f5f9; font-weight: 700; font-size: 1.2rem;
  display: flex; align-items: center;
}
.ma-modal__title i { color: #a78bfa; }
.ma-modal__close {
  width: 36px; height: 36px; border-radius: 10px;
  border: 1px solid rgba(255,255,255,0.1); background: rgba(15, 23, 42, 0.6);
  color: #94a3b8; cursor: pointer; display: flex;
  align-items: center; justify-content: center; transition: all 0.2s;
}
.ma-modal__close:hover {
  background: rgba(239, 68, 68, 0.2); color: #f87171;
  border-color: rgba(239, 68, 68, 0.3);
}
.ma-modal__body {
  padding: 1.5rem; overflow-y: auto; flex: 1;
}
.ma-modal__footer {
  padding: 1.25rem 1.5rem; border-top: 1px solid rgba(255,255,255,0.1);
  display: flex; align-items: center; justify-content: flex-end; gap: 0.75rem;
  background: rgba(15, 23, 42, 0.4);
}

/* ═══ Form ═══ */
.ma-form-group {
  margin-bottom: 1.25rem;
}
.ma-form-label {
  display: block; color: #cbd5e1; font-size: 0.9rem; font-weight: 600;
  margin-bottom: 0.5rem; display: flex; align-items: center;
}
.ma-form-label i { color: #818cf8; margin-right: 0.4rem; }
.ma-form-input {
  width: 100%; padding: 0.75rem 1rem; background: rgba(15, 23, 42, 0.6);
  border: 1px solid rgba(255,255,255,0.1); border-radius: 10px;
  color: #f1f5f9; font-size: 0.9rem; outline: none; transition: all 0.2s;
}
.ma-form-input:focus {
  border-color: rgba(99,102,241,0.5); background: rgba(15, 23, 42, 0.8);
  box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
}
.ma-form-input::placeholder { color: #64748b; }

/* Fix Chrome autofill (white background on dark modals) */
.ma-form-input:-webkit-autofill,
.ma-form-input:-webkit-autofill:hover,
.ma-form-input:-webkit-autofill:focus,
.ma-form-input:-webkit-autofill:active {
  -webkit-text-fill-color: #f1f5f9 !important;
  caret-color: #f1f5f9 !important;
  transition: background-color 9999s ease-in-out 0s;
  box-shadow: 0 0 0px 1000px rgba(15, 23, 42, 0.85) inset !important;
  border: 1px solid rgba(255,255,255,0.12) !important;
}

/* Ensure selects match input theme */
select.ma-form-input {
  background-color: rgba(15, 23, 42, 0.6);
  color: #f1f5f9;
}

/* Make small helper text more readable on dark bg */
.ma-modal__body .text-muted {
  color: #94a3b8 !important;
}
.ma-btn {
  padding: 0.75rem 1.5rem; border-radius: 10px; border: none;
  font-size: 0.9rem; font-weight: 600; cursor: pointer;
  display: inline-flex; align-items: center; justify-content: center;
  transition: all 0.25s; outline: none;
}
.ma-btn:disabled {
  opacity: 0.5; cursor: not-allowed;
}
.ma-btn--primary {
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  color: #fff; border: 1px solid rgba(99,102,241,0.3);
}
.ma-btn--primary:hover:not(:disabled) {
  transform: translateY(-2px); box-shadow: 0 6px 16px rgba(99,102,241,0.4);
}
.ma-btn--secondary {
  background: rgba(15, 23, 42, 0.6); color: #94a3b8;
  border: 1px solid rgba(255,255,255,0.1);
}
.ma-btn--secondary:hover:not(:disabled) {
  background: rgba(15, 23, 42, 0.8); color: #cbd5e1;
}

@media (max-width: 1200px) {
  .ma-table { font-size: 0.8rem; }
  .ma-table th, .ma-table td { padding: 0.75rem 0.6rem; }
  .ma-user-name { font-size: 0.85rem; }
  .ma-user-username { font-size: 0.72rem; }
  .ma-table__email { max-width: 150px; }
}

@media (max-width: 768px) {
  .ma-card__header { flex-direction: column; align-items: flex-start; gap: 0.75rem; }
  .ma-card__subtitle { display: none; }
  .ma-table { font-size: 0.75rem; }
  .ma-table th, .ma-table td { padding: 0.6rem 0.4rem; }
  .ma-table th { font-size: 0.65rem; }
  .ma-user-avatar { width: 28px; height: 28px; font-size: 0.7rem; }
  .ma-user-name { font-size: 0.8rem; }
  .ma-user-username { font-size: 0.68rem; }
  .ma-table__email { max-width: 100px; font-size: 0.75rem; }
  .ma-password-text { max-width: 100px; font-size: 0.7rem; }
  .ma-action-buttons { gap: 0.3rem; }
  .ma-action-btn { width: 30px; height: 30px; font-size: 0.7rem; }
  .ma-id-badge { padding: 0.25rem 0.5rem; font-size: 0.7rem; }
  .ma-table__deposit, .ma-table__balance { font-size: 0.75rem; padding: 0.2rem 0.4rem; }
  .ma-badge { font-size: 0.65rem; padding: 0.2rem 0.4rem; }
  .ma-status-cell { gap: 0.3rem; }
  .ma-modal { max-width: 95%; margin: 1rem; }
  .ma-modal__header, .ma-modal__body, .ma-modal__footer { padding: 1rem; }
  .ma-form-group { margin-bottom: 1rem; }
}
</style>
