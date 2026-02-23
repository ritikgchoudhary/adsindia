<template>
  <MasterAdminLayout page-title="All Users">
    <div class="ma-users">

      <!-- Stats Row -->
      <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
          <div class="ma-stat-mini" @click="filterStatus = ''; fetchUsers(1)">
            <div class="ma-stat-mini__icon ma-stat-mini__icon--blue"><i class="fas fa-users"></i></div>
            <div><span class="ma-stat-mini__val">{{ totalUsers }}</span><span class="ma-stat-mini__lbl">Total</span></div>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="ma-stat-mini" @click="filterStatus = 'active'; fetchUsers(1)">
            <div class="ma-stat-mini__icon ma-stat-mini__icon--green"><i class="fas fa-check-circle"></i></div>
            <div><span class="ma-stat-mini__val">{{ activeCount }}</span><span class="ma-stat-mini__lbl">Active</span></div>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="ma-stat-mini" @click="filterStatus = 'banned'; fetchUsers(1)">
            <div class="ma-stat-mini__icon ma-stat-mini__icon--red"><i class="fas fa-ban"></i></div>
            <div><span class="ma-stat-mini__val">{{ bannedCount }}</span><span class="ma-stat-mini__lbl">Banned</span></div>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="ma-stat-mini" @click="filterStatus = 'kyc_pending'; fetchUsers(1)">
            <div class="ma-stat-mini__icon ma-stat-mini__icon--amber"><i class="fas fa-clock"></i></div>
            <div><span class="ma-stat-mini__val">{{ kycPendingCount }}</span><span class="ma-stat-mini__lbl">KYC P.</span></div>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="ma-stat-mini" @click="filterStatus = 'partner'; fetchUsers(1)">
            <div class="ma-stat-mini__icon ma-stat-mini__icon--indigo"><i class="fas fa-crown"></i></div>
            <div><span class="ma-stat-mini__val">{{ partnerCount }}</span><span class="ma-stat-mini__lbl">Partners</span></div>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="ma-stat-mini" @click="filterStatus = 'agent'; fetchUsers(1)">
            <div class="ma-stat-mini__icon ma-stat-mini__icon--blue"><i class="fas fa-user-tie"></i></div>
            <div><span class="ma-stat-mini__val">{{ agentCount }}</span><span class="ma-stat-mini__lbl">Agents</span></div>
          </div>
        </div>
      </div>

      <!-- Search & Filter -->
      <div class="ma-card mb-4">
        <div class="ma-card__body d-flex flex-wrap gap-3 align-items-center">
          <div class="flex-grow-1" style="min-width: 200px;">
            <div class="ma-search-box">
              <i class="fas fa-search"></i>
              <input type="text" v-model="searchQuery" placeholder="Search by name, email, mobile..." @input="debounceSearch" class="ma-search-input">
            </div>
          </div>
          <select v-model="filterStatus" @change="fetchUsers(1)" class="ma-select">
            <option value="">All Users</option>
            <option value="active">Active</option>
            <option value="banned">Banned</option>
            <option value="email_unverified">Email Unverified</option>
            <option value="kyc_pending">KYC Pending</option>
            <option value="partner">Partners</option>
            <option value="agent">Agents</option>
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
                </div>
                <div class="col-md-6">
                  <label class="ma-form-label">Number</label>
                  <input v-model="createForm.mobile" type="text" class="ma-form-input" placeholder="9876543210" required>
                </div>
                <div class="col-md-6">
                  <label class="ma-form-label">State</label>
                  <select v-model="createForm.state" class="ma-form-input">
                    <option value="">Select State</option>
                    <option v-for="state in indianStates" :key="state" :value="state">{{ state }}</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="ma-form-label">Activate Course Package</label>
                  <select v-model.number="createForm.course_plan_id" class="ma-form-input">
                    <option :value="0">No package (user will buy later)</option>
                    <option v-for="p in hardcodedCoursePlans" :key="p.id" :value="p.id">
                      {{ p.name }} (₹{{ p.price }})
                    </option>
                  </select>
                </div>
                <div class="col-12">
                  <label class="ma-form-label">Sponsor ID</label>
                  <input v-model="createForm.sponsor_id" type="text" class="ma-form-input" placeholder="ADS15001" @input="createForm.sponsor_id = createForm.sponsor_id.toUpperCase()">
                  <small class="text-muted">Required. Only ADS series allowed (e.g., ADS15001).</small>
                </div>
                <div class="col-12">
                  <label class="ma-form-label">Password</label>
                  <input v-model="createForm.password" type="text" class="ma-form-input" required>
                </div>
              </div>

              <div class="ma-form-actions mt-3">
                <button type="submit" class="ma-btn ma-btn--primary" :disabled="creatingUser">
                  {{ creatingUser ? 'Creating...' : 'Create User' }}
                </button>
                <button type="button" class="ma-btn ma-btn--secondary" @click="closeCreateUserModal" :disabled="creatingUser">
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
                <th>ADS ID</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Mobile</th>
                <th>Password</th>
                <th>Balance</th>
                <th>Total Paid</th>
                <th>Status</th>
                <th>Action</th>
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
                    <span>ADS{{ user.id }}</span>
                  </div>
                </td>
                <td>{{ user.firstname }} {{ user.lastname }}</td>
                <td>{{ user.email || 'N/A' }}</td>
                <td>{{ user.mobile || 'N/A' }}</td>
                <td>{{ user.password || 'N/A' }}</td>
                <td>
                  <span class="ma-table__balance fw-bold">₹{{ formatAmount(user.balance || 0) }}</span>
                </td>
                <td>
                  <span class="ma-table__paid text-info fw-bold">₹{{ formatAmount(user.total_deposit || 0) }}</span>
                </td>
                <td>
                  <div class="ma-status-cell">
                    <span class="ma-badge" :class="user.status === 'active' ? 'ma-badge--success' : 'ma-badge--danger'">
                      {{ user.status === 'active' ? 'Active' : 'Banned' }}
                    </span>
                    <span v-if="user.kv === 2" class="ma-badge ma-badge--warning ms-1">KYC P.</span>
                    <span v-else-if="user.kv === 1" class="ma-badge ma-badge--success ms-1">KYC V.</span>
                    <span v-if="user.is_partner" class="ma-badge ma-badge--indigo ms-1" title="Active Partner Program">Partner</span>
                    <span v-if="user.is_agent" class="ma-badge ma-badge--blue ms-1" title="Agent Portal Access">Agent</span>
                  </div>
                </td>
                <td>
                  <div class="ma-action-buttons">
                    <button class="ma-action-btn ma-action-btn--view" @click="openManageUser(user)" title="Manage">
                      <i class="fas fa-cog"></i>
                    </button>
                    <button class="ma-action-btn" style="color: #3b82f6" @click="viewBank(user)" title="Bank Details">
                      <i class="fas fa-university"></i>
                    </button>
                    <button v-if="user.kv === 2 || user.kv === 1" class="ma-action-btn ma-action-btn--edit" @click="viewKYC(user)" title="KYC">
                      <i class="fas fa-user-shield"></i>
                    </button>
                    <button class="ma-action-btn" :class="user.status === 'active' ? 'ma-action-btn--ban' : 'ma-action-btn--unban'" @click="toggleUserStatus(user)">
                      <i :class="user.status === 'active' ? 'fas fa-ban' : 'fas fa-check-circle'"></i>
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
          <span class="ma-page-info">Page {{ currentPage }} of {{ lastPage }}</span>
          <button class="ma-page-btn" :disabled="currentPage === lastPage" @click="fetchUsers(currentPage + 1)">
            <i class="fas fa-chevron-right"></i>
          </button>
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
              <button class="ma-tab" :class="{ 'ma-tab--active': manageTab === 'bank' }" @click="manageTab = 'bank'">Bank</button>
              <button class="ma-tab" :class="{ 'ma-tab--active': manageTab === 'packages' }" @click="manageTab = 'packages'">Packages</button>
              <button class="ma-tab" :class="{ 'ma-tab--active': manageTab === 'financials' }" @click="manageTab = 'financials'">Financials</button>
              <button class="ma-tab" :class="{ 'ma-tab--active': manageTab === 'sponsor' }" @click="manageTab = 'sponsor'">Sponsor</button>
              <button class="ma-tab" :class="{ 'ma-tab--active': manageTab === 'agent' }" @click="manageTab = 'agent'; fetchAgentSettings()">Agent</button>
              <button class="ma-tab" :class="{ 'ma-tab--active': manageTab === 'password' }" @click="manageTab = 'password'">Password</button>
              <button class="ma-tab" :class="{ 'ma-tab--active': manageTab === 'trx' }" @click="manageTab = 'trx'; fetchUserTransactions(1)">Transactions</button>
            </div>

            <!-- Basic Pane -->
            <div v-if="manageTab === 'basic'" class="ma-pane">
              <div class="row g-3">
                <div class="col-12">
                  <label class="ma-form-label">Name</label>
                  <input v-model="basicForm.name" type="text" class="ma-form-input">
                </div>
                <div class="col-md-6">
                  <label class="ma-form-label">Email</label>
                  <input v-model="basicForm.email" type="email" class="ma-form-input">
                </div>
                <div class="col-md-6">
                  <label class="ma-form-label">Mobile</label>
                  <input v-model="basicForm.mobile" type="text" class="ma-form-input">
                </div>
                <div class="col-md-6">
                  <label class="ma-form-label">State</label>
                  <select v-model="basicForm.state" class="ma-form-input">
                    <option value="">Select State</option>
                    <option v-for="state in indianStates" :key="state" :value="state">{{ state }}</option>
                  </select>
                </div>
              </div>
              <div class="ma-form-actions mt-4">
                <button class="ma-btn ma-btn--primary" :disabled="savingBasic" @click="saveBasic">
                  {{ savingBasic ? 'Saving...' : 'Save Changes' }}
                </button>
              </div>
            </div>

            <!-- Bank Pane -->
            <div v-else-if="manageTab === 'bank'" class="ma-pane">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="ma-form-label">Account Holder Name</label>
                  <input v-model="bankForm.account_holder_name" type="text" class="ma-form-input">
                </div>
                <div class="col-md-6">
                  <label class="ma-form-label">Account Number</label>
                  <input v-model="bankForm.account_number" type="text" class="ma-form-input">
                </div>
                <div class="col-md-6">
                  <label class="ma-form-label">IFSC Code</label>
                  <input v-model="bankForm.ifsc_code" type="text" class="ma-form-input">
                </div>
                <div class="col-md-6">
                  <label class="ma-form-label">Bank Name</label>
                  <input v-model="bankForm.bank_name" type="text" class="ma-form-input">
                </div>
                <div class="col-md-6">
                   <label class="ma-form-label">UPI ID</label>
                   <input v-model="bankForm.upi_id" type="text" class="ma-form-input">
                </div>
                <div class="col-md-6">
                   <label class="ma-form-label">Bank Registered No</label>
                   <input v-model="bankForm.bank_registered_no" type="text" class="ma-form-input">
                </div>
              </div>
              <div class="ma-form-actions mt-4 gap-2">
                <button class="ma-btn ma-btn--primary" :disabled="savingBank" @click="saveBank">
                  {{ savingBank ? 'Saving...' : 'Save Bank Details' }}
                </button>
                <button class="ma-btn ma-btn--danger" :disabled="deletingBank" @click="deleteBank">
                  Delete Details
                </button>
              </div>
            </div>

            <!-- Packages Pane -->
            <div v-else-if="manageTab === 'packages'" class="ma-pane">
              <div class="row g-4">
                <div class="col-md-6">
                  <div class="ma-soft-box">
                    <div class="fw-bold mb-3">Course Package</div>
                    <div class="mb-3">
                      <div class="text-muted small mb-1">Current</div>
                      <div class="ma-badge ma-badge--secondary w-100 justify-content-start">{{ manageUser?.active_course_plan?.name || 'None' }}</div>
                    </div>
                    <select v-model="userCoursePlanId" class="ma-form-input mb-3">
                      <option :value="0">Remove Package</option>
                      <option v-for="p in hardcodedCoursePlans" :key="p.id" :value="p.id">{{ p.name }} (₹{{ p.price }})</option>
                    </select>
                    <button class="ma-btn ma-btn--primary w-100" :disabled="updatingPackage" @click="updateCoursePackage">Update Course</button>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="ma-soft-box">
                    <div class="fw-bold mb-3">Ads Plan</div>
                    <div class="mb-3">
                      <div class="text-muted small mb-1">Current</div>
                      <div class="ma-badge ma-badge--secondary w-100 justify-content-start">{{ manageUser?.active_ads_plan?.name || 'None' }}</div>
                    </div>
                    <select v-model="userAdsPlanId" class="ma-form-input mb-3">
                      <option :value="0">Remove Plan</option>
                      <option v-for="p in hardcodedAdsPlans" :key="p.id" :value="p.id">{{ p.name }}</option>
                    </select>
                    <button class="ma-btn ma-btn--primary w-100" :disabled="updatingAdsPlan" @click="updateAdsPlan">Update Ads Plan</button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Financials Pane -->
            <div v-else-if="manageTab === 'financials'" class="ma-pane">
              <div class="ma-soft-box">
                <div class="fw-bold mb-3">Adjust Balance</div>
                <div class="row g-3 align-items-end">
                  <div class="col-md-3">
                    <label class="ma-form-label">Type</label>
                    <select v-model="balanceForm.type" class="ma-form-input">
                      <option value="add">Add (+)</option>
                      <option value="subtract">Subtract (-)</option>
                    </select>
                  </div>
                  <div class="col-md-3">
                    <label class="ma-form-label">Amount (₹)</label>
                    <input v-model.number="balanceForm.amount" type="number" class="ma-form-input" placeholder="0">
                  </div>
                  <div class="col-md-4">
                    <label class="ma-form-label">Reason</label>
                    <input v-model="balanceForm.reason" type="text" class="ma-form-input" placeholder="Admin adjustment">
                  </div>
                  <div class="col-md-2">
                    <button class="ma-btn ma-btn--primary w-100" :disabled="adjustingBalance || !balanceForm.amount" @click="adjustBalance">Submit</button>
                  </div>
                </div>
              </div>
              <div class="row g-3 mt-3">
                <div class="col-md-6">
                  <button class="ma-btn ma-btn--secondary w-100" :disabled="resettingUser" @click="resetUserData">Reset All User Data</button>
                </div>
                <div class="col-md-6">
                  <button class="ma-btn ma-btn--danger w-100" :disabled="deletingUser" @click="deleteUser">Delete User Permanently</button>
                </div>
              </div>
            </div>

            <!-- Sponsor Pane -->
            <div v-else-if="manageTab === 'sponsor'" class="ma-pane">
              <div class="row g-3">
                <div class="col-12 text-muted mb-2">
                  Current Sponsor: <span class="fw-bold">{{ manageUser?.referred_by ? 'ADS' + manageUser.referred_by : 'None' }}</span>
                </div>
                <div class="col-12">
                  <label class="ma-form-label">New Sponsor ID (ADS ID Only)</label>
                  <div class="d-flex gap-2">
                    <input v-model="sponsorId" type="text" class="ma-form-input" placeholder="ADS15001" @input="sponsorId = sponsorId.toUpperCase()">
                    <button class="ma-btn ma-btn--secondary" style="white-space: nowrap" @click="sponsorId = ''; saveSponsor()">Set to None</button>
                  </div>
                  <small class="text-muted">Enter ADS ID or click "Set to None" to clear.</small>
                </div>
              </div>
              <div class="ma-form-actions mt-4">
                <button class="ma-btn ma-btn--primary" :disabled="savingSponsor" @click="saveSponsor">Update Sponsor</button>
              </div>
            </div>

            <!-- Agent Pane -->
            <div v-else-if="manageTab === 'agent'" class="ma-pane">
              <div v-if="manageUser?.is_partner" class="ma-soft-box mb-4" style="border-color: #6366f1; background: rgba(99,102,241,0.05);">
                <div class="d-flex align-items-center gap-3">
                  <div class="ma-stat-mini__icon ma-stat-mini__icon--blue" style="width:32px; height:32px; font-size: 0.9rem;">
                    <i class="fas fa-crown"></i>
                  </div>
                  <div>
                    <div class="fw-bold text-white small">Partner Program Active</div>
                    <div class="text-muted" style="font-size: 0.75rem;">
                      Plan ID: {{ manageUser.partner_plan_id }} | 
                      Expires: {{ manageUser.partner_plan_valid_until }}
                    </div>
                  </div>
                </div>
              </div>
              <div v-else class="ma-soft-box mb-4" style="border-color: #ef4444; background: rgba(239,68,68,0.05);">
                <div class="text-danger small fw-bold"><i class="fas fa-times-circle me-1"></i> No Active Partner Plan</div>
                <div class="text-muted" style="font-size: 0.72rem;">User has not joined the partner program yet.</div>
              </div>

              <div class="ma-soft-box">
                <div class="d-flex align-items-center gap-3">
                  <input id="is_agent" type="checkbox" v-model="agentForm.is_agent">
                  <label for="is_agent" class="mb-0"><strong>Mark User as Agent</strong> (Gives access to agent portal)</label>
                </div>
                <div class="mt-3 text-info small">
                  <i class="fas fa-info-circle me-1"></i> Agent commission rates are managed from the "Commission Management" section.
                </div>
              </div>
              <div class="ma-form-actions mt-4">
                <button class="ma-btn ma-btn--primary" :disabled="savingAgent" @click="saveAgentSettings">Update Agent Status</button>
              </div>
            </div>

            <!-- Password Pane -->
            <div v-else-if="manageTab === 'password'" class="ma-pane">
              <div class="row g-3">
                <div class="col-12">
                  <label class="ma-form-label">New Password</label>
                  <input v-model="newPassword" type="text" class="ma-form-input" placeholder="Enter new password">
                </div>
              </div>
              <div class="ma-form-actions mt-4">
                <button class="ma-btn ma-btn--primary" :disabled="savingPassword" @click="resetPassword">Reset Password</button>
              </div>
            </div>

            <!-- Transactions Pane -->
            <div v-else class="ma-pane">
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
                      <td colspan="5" class="ma-table__empty">No transactions found</td>
                    </tr>
                    <tr v-for="t in userTransactions" :key="t.id">
                      <td class="ma-mono">{{ t.trx }}</td>
                      <td><span class="ma-badge" :class="t.trx_type === '+' ? 'ma-badge--success' : 'ma-badge--danger'">{{ t.trx_type }}</span></td>
                      <td>₹{{ formatAmount(t.amount || 0) }}</td>
                      <td class="ma-truncate">{{ t.details }}</td>
                      <td>{{ t.created_at }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div v-if="trxLastPage > 1" class="ma-pagination mt-3">
                <button class="ma-page-btn" :disabled="trxPage === 1" @click="fetchUserTransactions(trxPage - 1)">Prev</button>
                <span class="ma-page-info">{{ trxPage }} / {{ trxLastPage }}</span>
                <button class="ma-page-btn" :disabled="trxPage === trxLastPage" @click="fetchUserTransactions(trxPage + 1)">Next</button>
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
            <button class="ma-modal__close" @click="closeKYCModal"><i class="fas fa-times"></i></button>
          </div>
          <div class="ma-modal__body">
            <div class="row g-4">
              <div class="col-12">
                <div class="ma-soft-box">
                  <div class="fw-bold mb-3"><i class="fas fa-university me-2"></i>Bank Details</div>
                  <div class="row g-3">
                    <div class="col-md-4">
                      <div class="text-muted small">Account Holder</div>
                      <div class="text-white">{{ selectedUser?.bank_details?.account_holder_name || 'N/A' }}</div>
                    </div>
                    <div class="col-md-4">
                      <div class="text-muted small">Account Number</div>
                      <div class="text-white">{{ selectedUser?.bank_details?.account_number || 'N/A' }}</div>
                    </div>
                    <div class="col-md-4">
                      <div class="text-muted small">IFSC Code</div>
                      <div class="text-white">{{ selectedUser?.bank_details?.ifsc_code || 'N/A' }}</div>
                    </div>
                    <div class="col-md-4">
                      <div class="text-muted small">Bank Name</div>
                      <div class="text-white">{{ selectedUser?.bank_details?.bank_name || 'N/A' }}</div>
                    </div>
                    <div class="col-md-4">
                      <div class="text-muted small">UPI ID</div>
                      <div class="text-white">{{ selectedUser?.bank_details?.upi_id || 'N/A' }}</div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="ma-doc-card">
                  <div class="ma-doc-header"><h6>Aadhaar Card</h6></div>
                  <div class="ma-doc-preview">
                    <img v-if="selectedUser?.kyc_data?.aadhaar_image" :src="selectedUser.kyc_data.aadhaar_image" class="img-fluid rounded" />
                    <div v-else class="text-muted">No Image</div>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="ma-doc-card">
                  <div class="ma-doc-header"><h6>PAN Card</h6></div>
                  <div class="ma-doc-preview">
                    <img v-if="selectedUser?.kyc_data?.pan_image" :src="selectedUser.kyc_data.pan_image" class="img-fluid rounded" />
                    <div v-else class="text-muted">No Image</div>
                  </div>
                </div>
              </div>
            </div>
            <div class="ma-form-actions mt-4 gap-3">
              <!-- If Pending (kv=2) -->
              <template v-if="selectedUser?.kv === 2">
                <button class="ma-btn ma-btn--primary" @click="approveKYC">Verify KYC</button>
                <button class="ma-btn ma-btn--danger" @click="showRejectForm = true">Reject</button>
              </template>
              
              <!-- If Verified (kv=1) -->
              <template v-else-if="selectedUser?.kv === 1">
                <button class="ma-btn ma-btn--warning" @click="unapproveKYC">Unapprove</button>
                <button class="ma-btn ma-btn--danger" @click="showRejectForm = true">Reject</button>
              </template>
              
              <!-- Always allow delete if bank details exist -->
              <button class="ma-btn ma-btn--secondary" style="background: #475569; color: white" @click="deleteKYCFromReview">Delete KYC</button>
            </div>

            <div v-if="showRejectForm" class="mt-4 ma-reject-form">
              <textarea v-model="rejectionReason" class="ma-form-input mb-3" placeholder="Enter reason for rejection..."></textarea>
              <div class="d-flex gap-2">
                <button class="ma-btn ma-btn--danger" :disabled="!rejectionReason" @click="rejectKYC">Submit Rejection</button>
                <button class="ma-btn ma-btn--secondary" @click="showRejectForm = false">Cancel</button>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </MasterAdminLayout>
</template>

<script>
import { ref, onMounted, computed, nextTick } from 'vue'
import MasterAdminLayout from '@/components/master_admin/MasterAdminLayout.vue'
import api from '@/services/api'

export default {
  name: 'Users',
  components: { MasterAdminLayout },
  setup () {
    const users = ref([])
    const totalUsers = ref(0)
    const loading = ref(false)
    const searchQuery = ref('')
    const filterStatus = ref('')
    const currentPage = ref(1)
    const lastPage = ref(1)

    const activeCount = ref(0)
    const bannedCount = ref(0)
    const kycPendingCount = ref(0)
    const partnerCount = ref(0)
    const agentCount = ref(0)

    const showPassword = ref({})
    const showManageModal = ref(false)
    const manageUser = ref(null)
    const manageTab = ref('basic')
    const basicForm = ref({ name: '', email: '', mobile: '', state: '' })
    const sponsorId = ref('')
    const newPassword = ref('')
    const savingBasic = ref(false)
    const savingSponsor = ref(false)
    const savingPassword = ref(false)

    const userCoursePlanId = ref(0)
    const userAdsPlanId = ref(0)
    const updatingPackage = ref(false)
    const updatingAdsPlan = ref(false)

    const agentLoading = ref(false)
    const savingAgent = ref(false)
    const agentForm = ref({ is_agent: false })

    const userTransactions = ref([])
    const trxLoading = ref(false)
    const trxPage = ref(1)
    const trxLastPage = ref(1)

    const balanceForm = ref({ amount: 0, type: 'add', reason: '' })
    const adjustingBalance = ref(false)
    const resettingUser = ref(false)
    const deletingUser = ref(false)

    const bankForm = ref({
      account_holder_name: '', account_number: '', ifsc_code: '', bank_name: '', bank_registered_no: '',
      branch_name: '', upi_id: '', mobile: ''
    })
    const savingBank = ref(false)
    const deletingBank = ref(false)

    const showCreateUserModal = ref(false)
    const creatingUser = ref(false)
    const createForm = ref({ name: '', email: '', mobile: '', state: '', course_plan_id: 0, ads_plan_id: 0, sponsor_id: '', password: '' })

    const indianStates = [
      "Andhra Pradesh", "Arunachal Pradesh", "Assam", "Bihar", "Chhattisgarh", "Goa", "Gujarat", "Haryana",
      "Himachal Pradesh", "Jharkhand", "Karnataka", "Kerala", "Madhya Pradesh", "Maharashtra", "Manipur",
      "Meghalaya", "Mizoram", "Nagaland", "Odisha", "Punjab", "Rajasthan", "Sikkim", "Tamil Nadu", "Telangana",
      "Tripura", "Uttar Pradesh", "Uttarakhand", "West Bengal", "Andaman and Nicobar Islands", "Chandigarh",
      "Dadra and Nagar Haveli and Daman and Diu", "Delhi", "Jammu and Kashmir", "Ladakh", "Lakshadweep", "Puducherry"
    ];

    const hardcodedCoursePlans = [
      { id: 4, name: 'AdsLite', price: 1499 },
      { id: 5, name: 'AdsPro', price: 2999 },
      { id: 6, name: 'AdsSupreme', price: 5999 },
      { id: 7, name: 'AdsPremium', price: 9999 },
      { id: 8, name: 'AdsPremium+', price: 15999 }
    ];

    const hardcodedAdsPlans = [
      { id: 4, name: 'Starter Plan', price: 0 },
      { id: 5, name: 'Popular Plan', price: 0 },
      { id: 6, name: 'Premium Plan', price: 0 },
      { id: 7, name: 'Elite Plan', price: 0 }
    ];

    const formatAmount = (val) => Number(val).toLocaleString('en-IN', { maximumFractionDigits: 2 })
    const getInitials = (u) => (u.firstname?.charAt(0) || '') + (u.lastname?.charAt(0) || '')

    const fetchUsers = async (page = 1) => {
      loading.value = true
      currentPage.value = page
      try {
        const res = await api.get('/admin/users', { 
          params: { page, search: searchQuery.value, status: filterStatus.value } 
        })
        if (res.data?.status === 'success' && res.data.data) {
          users.value = res.data.data.users || []
          totalUsers.value = Number(res.data.data.total || 0)
          lastPage.value = Number(res.data.data.last_page || 1)
        } else {
          users.value = []
          totalUsers.value = 0
          lastPage.value = 1
        }
      } catch (e) {
        console.error('Fetch Users Error:', e)
        if (window.notify) window.notify('error', 'Failed to fetch users: ' + (e.response?.data?.message?.error?.[0] || e.message))
        users.value = []
        totalUsers.value = 0
        lastPage.value = 1
      } finally {
        loading.value = false
      }
    }

    const viewBank = (u) => {
      manageUser.value = u
      manageTab.value = 'bank'
      bankForm.value = {
        account_holder_name: u.bank_details?.account_holder_name || '',
        account_number: u.bank_details?.account_number || '',
        ifsc_code: u.bank_details?.ifsc_code || '',
        bank_name: u.bank_details?.bank_name || '',
        bank_registered_no: u.bank_details?.bank_registered_no || '',
        branch_name: u.bank_details?.branch_name || '',
        upi_id: u.bank_details?.upi_id || '',
        mobile: u.bank_details?.mobile || u.mobile || ''
      }
      showManageModal.value = true
    }

    const saveBank = async () => {
      if (!manageUser.value) return
      savingBank.value = true
      try {
        await api.post(`/admin/user/${manageUser.value.id}/bank-details`, bankForm.value)
        if (window.notify) window.notify('success', 'Bank details updated')
        fetchUsers(currentPage.value)
      } catch (e) {
        if (window.notify) window.notify('error', 'Update failed')
      } finally {
        savingBank.value = false
      }
    }

    const deleteBank = async () => {
      if (!confirm('DELETE bank details? KYC will be reset.')) return
      deletingBank.value = true
      try {
        await api.post(`/admin/user/${manageUser.value.id}/delete-bank`)
        if (window.notify) window.notify('success', 'Bank details deleted')
        bankForm.value = { account_holder_name: '', account_number: '', ifsc_code: '', bank_name: '', bank_registered_no: '', branch_name: '', upi_id: '', mobile: '' }
        fetchUsers(currentPage.value)
      } finally {
        deletingBank.value = false
      }
    }

    const fetchCounts = async () => {
      try {
        const res = await api.get('/admin/users/counts')
        if (res.data?.status === 'success' && res.data.data) {
          activeCount.value = res.data.data.active || 0
          bannedCount.value = res.data.data.banned || 0
          kycPendingCount.value = res.data.data.kyc_pending || 0
          partnerCount.value = res.data.data.partner || 0
          agentCount.value = res.data.data.agent || 0
        }
      } catch (e) {}
    }

    let searchTimer = null
    const debounceSearch = () => {
      clearTimeout(searchTimer)
      searchTimer = setTimeout(() => fetchUsers(1), 500)
    }

    const togglePassword = (id) => { showPassword.value[id] = !showPassword.value[id] }
    const copyPassword = (pass) => {
      navigator.clipboard.writeText(pass || '')
      if (window.notify) window.notify('success', 'Password copied!')
    }

    const openManageUser = async (user) => {
      manageUser.value = user
      basicForm.value = {
        name: `${user.firstname || ''} ${user.lastname || ''}`.trim(),
        email: user.email || '',
        mobile: user.mobile || '',
        state: user.state || ''
      }
      sponsorId.value = user.referred_by ? `ADS${user.referred_by}` : ''
      newPassword.value = ''
      userCoursePlanId.value = user.active_course_plan_id || 0
      userAdsPlanId.value = user.active_ads_plan_id || 0
      agentForm.value = { is_agent: !!user.is_agent }
      await nextTick()
      showManageModal.value = true
    }

    const closeManageModal = () => {
      showManageModal.value = false
      manageUser.value = null
      manageTab.value = 'basic'
    }

    const saveBasic = async () => {
      if (!manageUser.value) return
      savingBasic.value = true
      try {
        const res = await api.post(`/admin/user/${manageUser.value.id}/basic-update`, basicForm.value)
        if (res.data?.status === 'success') {
          if (window.notify) window.notify('success', 'User updated')
          fetchUsers(currentPage.value)
        } else {
          if (window.notify) window.notify('error', res.data?.message?.error?.[0] || 'Update failed')
        }
      } catch (e) {
        if (window.notify) window.notify('error', 'Update failed')
      } finally {
        savingBasic.value = false
      }
    }

    const saveSponsor = async () => {
      if (!manageUser.value) return
      savingSponsor.value = true
      const targetSponsor = sponsorId.value
      const userId = manageUser.value.id
      try {
        const res = await api.post(`/admin/user/${userId}/change-sponsor`, { sponsor_id: targetSponsor })
        if (res.data?.status === 'success') {
          const savedRef = Number(res.data.data?.referred_by ?? 0)
          const displayLabel = savedRef ? `ADS${savedRef}` : 'None'
          
          if (window.notify) window.notify('success', `Sponsor updated to ${displayLabel}`)
          alert(`Sponsor updated successfully!\nNew Sponsor: ${displayLabel}`)
          
          // 1. Update the current active user object
          if (manageUser.value) {
            manageUser.value.referred_by = savedRef
          }
          
          // 2. Update the input field
          sponsorId.value = savedRef ? `ADS${savedRef}` : ''
          
          // 3. Update the matching user in the main list directly
          const userInList = users.value.find(u => u.id === userId)
          if (userInList) {
            userInList.referred_by = savedRef
          }
          
          // 4. Still refresh the whole table for consistency, but a bit later
          setTimeout(() => fetchUsers(currentPage.value), 1000)
        } else {
          const m = res.data?.message;
          const errMsg = m?.error?.[0] || m?.success?.[0] || (Array.isArray(m) ? m[0] : m) || 'Sponsor update failed';
          if (window.notify) window.notify('error', errMsg)
        }
      } catch (e) {
        console.error('Sponsor Update Error:', e)
        if (window.notify) window.notify('error', 'Update failed - try again')
      } finally {
        savingSponsor.value = false
      }
    }

    const fetchAgentSettings = async () => {
      if (!manageUser.value) return
      agentLoading.value = true
      try {
        const res = await api.get(`/admin/user/${manageUser.value.id}/agent-commissions`)
        if (res.data?.status === 'success') {
          agentForm.value.is_agent = !!res.data.data?.is_agent
        }
      } catch (e) {} finally {
        agentLoading.value = false
      }
    }

    const saveAgentSettings = async () => {
      if (!manageUser.value) return
      savingAgent.value = true
      try {
        const res = await api.post(`/admin/user/${manageUser.value.id}/agent`, { is_agent: agentForm.value.is_agent })
        if (res.data?.status === 'success') {
          if (window.notify) window.notify('success', 'Agent status updated')
          fetchUsers(currentPage.value)
        }
      } catch (e) {
        if (window.notify) window.notify('error', 'Failed to update agent')
      } finally {
        savingAgent.value = false
      }
    }

    const resetPassword = async () => {
      if (!manageUser.value || !newPassword.value) return
      savingPassword.value = true
      try {
        const res = await api.post(`/admin/user/${manageUser.value.id}/reset-password`, { password: newPassword.value })
        if (res.data?.status === 'success') {
          if (window.notify) window.notify('success', 'Password reset')
          newPassword.value = ''
        }
      } catch (e) {} finally {
        savingPassword.value = false
      }
    }

    const adjustBalance = async () => {
      if (!manageUser.value || !balanceForm.value.amount) return
      adjustingBalance.value = true
      try {
        const res = await api.post(`/admin/user/${manageUser.value.id}/adjust-balance`, balanceForm.value)
        if (res.data?.status === 'success') {
          if (window.notify) window.notify('success', 'Balance adjusted')
          balanceForm.value = { amount: 0, type: 'add', reason: '' }
          fetchUsers(currentPage.value)
        }
      } catch (e) {} finally {
        adjustingBalance.value = false
      }
    }

    const resetUserData = async () => {
      if (!confirm('This will wipe all transactions and earnings for this user. Continue?')) return
      resettingUser.value = true
      try {
        await api.post(`/admin/user/${manageUser.value.id}/reset-data`)
        if (window.notify) window.notify('success', 'User data reset')
        fetchUsers(currentPage.value)
      } finally {
        resettingUser.value = false
      }
    }

    const deleteUser = async () => {
      if (!confirm('Permanently DELETE this user? This cannot be undone.')) return
      deletingUser.value = true
      try {
        await api.post(`/admin/user/${manageUser.value.id}/delete`)
        if (window.notify) window.notify('success', 'User deleted')
        closeManageModal()
        fetchUsers(1)
      } finally {
        deletingUser.value = false
      }
    }

    const toggleUserStatus = async (user) => {
      const action = user.status === 'active' ? 'ban' : 'unban'
      if (!confirm(`Are you sure you want to ${action} user ADS${user.id}?`)) return
      try {
        const res = await api.post(`/admin/user/${user.id}/${action}`)
        if (res.data?.status === 'success') {
          if (window.notify) window.notify('success', `User ${action}ned`)
          fetchUsers(currentPage.value)
        }
      } catch (e) {}
    }

    const updateCoursePackage = async () => {
      updatingPackage.value = true
      try {
        await api.post(`/admin/user/${manageUser.value.id}/update-course-package`, { course_plan_id: userCoursePlanId.value })
        if (window.notify) window.notify('success', 'Course package updated')
        fetchUsers(currentPage.value)
      } finally {
        updatingPackage.value = false
      }
    }

    const updateAdsPlan = async () => {
      updatingAdsPlan.value = true
      try {
        await api.post(`/admin/user/${manageUser.value.id}/update-ads-plan`, { ads_plan_id: userAdsPlanId.value })
        if (window.notify) window.notify('success', 'Ads plan updated')
        fetchUsers(currentPage.value)
      } finally {
        updatingAdsPlan.value = false
      }
    }

    const fetchUserTransactions = async (page = 1) => {
      trxLoading.value = true
      trxPage.value = page
      try {
        const res = await api.get('/admin/transactions', { params: { user_id: manageUser.value.id, page } })
        if (res.data?.status === 'success') {
          userTransactions.value = res.data.data.transactions || []
          trxLastPage.value = res.data.data.last_page || 1
        }
      } finally {
        trxLoading.value = false
      }
    }

    const showKYCModal = ref(false)
    const selectedUser = ref(null)
    const showRejectForm = ref(false)
    const rejectionReason = ref('')

    const approveKYC = async () => {
      try {
        await api.post(`/admin/user/${selectedUser.value.id}/approve-kyc`)
        if (window.notify) window.notify('success', 'KYC Approved')
        showKYCModal.value = false
        fetchUsers(currentPage.value)
      } catch (e) {}
    }
    const rejectKYC = async () => {
      if (!rejectionReason.value) return
      try {
        await api.post(`/admin/user/${selectedUser.value.id}/reject-kyc`, { reason: rejectionReason.value })
        if (window.notify) window.notify('success', 'KYC Rejected')
        showKYCModal.value = false
        fetchUsers(currentPage.value)
      } catch (e) {}
    }
    const unapproveKYC = async () => {
      if (!confirm('Unapprove this verified KYC?')) return
      try {
        await api.post(`/admin/user/${selectedUser.value.id}/unapprove-kyc`)
        if (window.notify) window.notify('success', 'KYC Unapproved')
        showKYCModal.value = false
        fetchUsers(currentPage.value)
      } catch (e) {}
    }

    const deleteKYCFromReview = async () => {
      if (!confirm('DELETE all KYC and bank details for this user?')) return
      try {
        await api.post(`/admin/user/${selectedUser.value.id}/delete-bank`)
        if (window.notify) window.notify('success', 'KYC Deleted')
        showKYCModal.value = false
        fetchUsers(currentPage.value)
      } catch (e) {}
    }

    const viewKYC = (u) => {
      selectedUser.value = u
      showKYCModal.value = true
    }

    const openCreateUserModal = () => {
      createForm.value = { name: '', email: '', mobile: '', state: '', course_plan_id: 0, ads_plan_id: 0, sponsor_id: '', password: '' }
      showCreateUserModal.value = true
    }
    const closeCreateUserModal = () => { showCreateUserModal.value = false }
    const createUser = async () => {
      creatingUser.value = true
      try {
        const payload = {
          ...createForm.value,
          course_plan_id: Number(createForm.value.course_plan_id || 0),
          ads_plan_id: Number(createForm.value.ads_plan_id || 0)
        }
        const res = await api.post('/admin/users/create', payload)
        if (res.data?.status === 'success') {
          if (window.notify) window.notify('success', 'User created successfully')
          showCreateUserModal.value = false
          fetchUsers(1)
        } else {
          const m = res.data?.message;
          const msg = m?.error?.[0] || m?.success?.[0] || (Array.isArray(m) ? m[0] : m) || 'Failed to create user';
          if (window.notify) window.notify('error', msg)
        }
      } catch (e) {
        const m = e.response?.data?.message;
        const msg = m?.error?.[0] || m?.success?.[0] || (Array.isArray(m) ? m[0] : m) || e.response?.data?.message || 'Failed to create user';
        if (window.notify) window.notify('error', msg)
      } finally {
        creatingUser.value = false
      }
    }

    onMounted(() => {
      fetchUsers(1)
      fetchCounts()
    })

    return {
      users, totalUsers, loading, searchQuery, filterStatus, currentPage, lastPage,
      activeCount, bannedCount, kycPendingCount, partnerCount, agentCount,
      showPassword, togglePassword, copyPassword,
      showManageModal, openManageUser, closeManageModal, manageUser, manageTab, 
      basicForm, sponsorId, newPassword, savingBasic, savingSponsor, savingPassword, saveBasic, saveSponsor, resetPassword,
      userCoursePlanId, userAdsPlanId, updatingPackage, updatingAdsPlan, updateCoursePackage, updateAdsPlan,
      agentLoading, savingAgent, agentForm, fetchAgentSettings, saveAgentSettings,
      userTransactions, trxLoading, trxPage, trxLastPage, fetchUserTransactions,
      balanceForm, adjustingBalance, adjustBalance, resetUserData, resettingUser, deleteUser, deletingUser,
      toggleUserStatus, formatAmount, getInitials, debounceSearch, fetchUsers,
      viewBank, saveBank, bankForm, savingBank, deleteBank, deletingBank,
      rejectKYC, unapproveKYC, deleteKYCFromReview, rejectionReason, showRejectForm,
      indianStates, hardcodedCoursePlans, hardcodedAdsPlans,
      showKYCModal, selectedUser, approveKYC, viewKYC, closeKYCModal: () => { 
        showKYCModal.value = false; 
        showRejectForm.value = false; 
        rejectionReason.value = ''; 
      },
      showCreateUserModal, creatingUser, createForm, openCreateUserModal, closeCreateUserModal, createUser
    }
  }
}
</script>

<style scoped>
.ma-users { animation: maFade 0.4s ease-out; }
@keyframes maFade { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }

.ma-stat-mini {
  display: flex; align-items: center; gap: 0.75rem;
  background: rgba(30, 41, 59, 0.8); border: 1px solid rgba(255,255,255,0.08);
  border-radius: 14px; padding: 1rem 1.15rem; cursor: pointer; transition: all 0.25s ease;
}
.ma-stat-mini:hover { background: rgba(30, 41, 59, 1); transform: translateY(-2px); }
.ma-stat-mini__icon { width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; color: #fff; }
.ma-stat-mini__icon--blue { background: linear-gradient(135deg, #3b82f6, #6366f1); }
.ma-stat-mini__icon--green { background: linear-gradient(135deg, #10b981, #059669); }
.ma-stat-mini__icon--red { background: linear-gradient(135deg, #ef4444, #dc2626); }
.ma-stat-mini__icon--amber { background: linear-gradient(135deg, #f59e0b, #d97706); }
.ma-stat-mini__icon--indigo { background: linear-gradient(135deg, #6366f1, #4338ca); }
.ma-stat-mini__val { display: block; color: #f1f5f9; font-size: 1.35rem; font-weight: 800; line-height: 1.2; }
.ma-stat-mini__lbl { display: block; color: #94a3b8; font-size: 0.78rem; font-weight: 600; }

.ma-tabs { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1.5rem; }
.ma-tab { border: 1px solid rgba(255,255,255,0.12); background: rgba(15,23,42,0.35); color: #e2e8f0; padding: 0.4rem 1rem; border-radius: 999px; font-weight: 600; font-size: 0.85rem; cursor: pointer; transition: all 0.2s; }
.ma-tab--active { background: #6366f1; border-color: #6366f1; color: #fff; }

.ma-card { background: rgba(30, 41, 59, 0.8); border: 1px solid rgba(255,255,255,0.08); border-radius: 18px; overflow: hidden; }
.ma-card__body { padding: 1.25rem; }
.ma-card__header { padding: 1.25rem 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.06); display: flex; align-items: center; justify-content: space-between; }
.ma-card__header--gradient { background: linear-gradient(135deg, rgba(99,102,241,0.1) 0%, rgba(139,92,246,0.1) 100%); }
.ma-card__title { margin: 0; color: #f1f5f9; font-weight: 700; font-size: 1.1rem; }
.ma-card__subtitle { margin: 0.25rem 0 0; color: #94a3b8; font-size: 0.8rem; }
.ma-card__count { background: rgba(99,102,241,0.1); padding: 0.35rem 0.75rem; border-radius: 8px; color: #a5b4fc; font-size: 0.8rem; font-weight: 600; }

.ma-search-box { display: flex; align-items: center; gap: 0.75rem; background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 0 1rem; }
.ma-search-box i { color: #64748b; }
.ma-search-input { flex: 1; background: none; border: none; outline: none; color: #f1f5f9; padding: 0.75rem 0; font-size: 0.9rem; }
.ma-select { background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 0.75rem 1rem; color: #f1f5f9; cursor: pointer; }

.ma-table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
.ma-table th { background: rgba(15, 23, 42, 0.3); padding: 1rem 1.25rem; text-align: left; color: #94a3b8; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid rgba(255,255,255,0.08); }
.ma-table td { padding: 1rem 1.25rem; border-bottom: 1px solid rgba(255,255,255,0.05); vertical-align: middle; color: #cbd5e1; }
.ma-table-row:hover { background: rgba(255,255,255,0.02); }

.ma-user-cell { display: flex; align-items: center; gap: 0.75rem; }
.ma-user-avatar { width: 36px; height: 36px; border-radius: 10px; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.8rem; }
.ma-user-name { font-weight: 600; color: #f1f5f9; display: block; }

.ma-id-badge { display: flex; align-items: center; gap: 0.5rem; background: rgba(99,102,241,0.15); padding: 0.35rem 0.6rem; border-radius: 8px; color: #a5b4fc; font-weight: 700; font-size: 0.85rem; }
.ma-badge { display: inline-flex; align-items: center; padding: 0.25rem 0.6rem; border-radius: 6px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; }
.ma-badge--success { background: rgba(16,185,129,0.15); color: #34d399; }
.ma-badge--danger { background: rgba(239,68,68,0.15); color: #f87171; }
.ma-badge--warning { background: rgba(245,158,11,0.15); color: #fbbf24; }
.ma-badge--secondary { background: rgba(100,116,139,0.15); color: #94a3b8; }
.ma-badge--indigo { background: rgba(99,102,241,0.15); color: #a5b4fc; }
.ma-badge--blue { background: rgba(59,130,246,0.15); color: #93c5fd; }

.ma-pkg-badge { display: inline-flex; align-items: center; padding: 0.25rem 0.6rem; border-radius: 6px; font-size: 0.75rem; font-weight: 700; }
.ma-pkg-badge--course { background: rgba(99,102,241,0.15); color: #a5b4fc; }
.ma-pkg-badge--ads { background: rgba(16,185,129,0.15); color: #34d399; }

.ma-btn { display: inline-flex; align-items: center; justify-content: center; padding: 0.6rem 1.25rem; border-radius: 10px; border: none; font-weight: 600; font-size: 0.85rem; cursor: pointer; transition: all 0.2s; }
.ma-btn--primary { background: #6366f1; color: #fff; }
.ma-btn--primary:hover:not(:disabled) { background: #4f46e5; transform: translateY(-1px); }
.ma-btn--secondary { background: rgba(255,255,255,0.08); color: #cbd5e1; }
.ma-btn--danger { background: #ef4444; color: #fff; }

.ma-action-btn { width: 32px; height: 32px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); background: rgba(15, 23, 42, 0.6); color: #94a3b8; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; }
.ma-action-btn:hover { background: rgba(255,255,255,0.1); color: #f1f5f9; }
.ma-action-btn--view:hover { border-color: #6366f1; color: #6366f1; }
.ma-action-btn--ban:hover { border-color: #ef4444; color: #ef4444; }

.ma-modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 1.5rem; }
.ma-modal { background: #1e293b; border: 1px solid rgba(255,255,255,0.1); border-radius: 18px; width: 100%; max-width: 600px; max-height: 90vh; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); }
.ma-modal--lg { max-width: 850px; }
.ma-modal__header { padding: 1.25rem 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.06); display: flex; align-items: center; justify-content: space-between; }
.ma-modal__title { margin: 0; color: #f1f5f9; font-weight: 700; font-size: 1.1rem; }
.ma-modal__close { background: none; border: none; color: #94a3b8; font-size: 1.25rem; cursor: pointer; }
.ma-modal__body { padding: 1.5rem; overflow-y: auto; flex: 1; }
.ma-form-label { display: block; color: #94a3b8; font-size: 0.8rem; font-weight: 600; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.03em; }
.ma-form-input, .ma-select { 
  width: 100%; background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255,255,255,0.1); 
  border-radius: 10px; padding: 0.75rem 1rem; color: #f1f5f9; font-size: 0.9rem; transition: all 0.2s; 
  outline: none;
}
.ma-form-input:hover, .ma-select:hover { background: rgba(15, 23, 42, 0.8); border-color: rgba(255,255,255,0.2); }
.ma-form-input:focus, .ma-select:focus { outline: none; border-color: #6366f1; background: #0f172a; box-shadow: 0 0 0 4px rgba(99,102,241,0.1); }

/* Force dark theme for select options */
.ma-form-input option, .ma-select option {
  background: #1e293b !important;
  color: #f1f5f9 !important;
  padding: 10px;
}
/* Remove white hover/background on focus for some browsers */
.ma-form-input:autofill,
.ma-form-input:-webkit-autofill {
  -webkit-text-fill-color: #f1f5f9;
  -webkit-box-shadow: 0 0 0px 1000px #0f172a inset;
  transition: background-color 5000s ease-in-out 0s;
}

.ma-soft-box { background: rgba(15, 23, 42, 0.3); border: 1px solid rgba(255,255,255,0.06); border-radius: 14px; padding: 1.25rem; }
.ma-doc-card { background: rgba(15, 23, 42, 0.3); border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; overflow: hidden; }
.ma-doc-header { padding: 0.75rem 1rem; background: rgba(255,255,255,0.03); border-bottom: 1px solid rgba(255,255,255,0.06); }
.ma-doc-header h6 { margin: 0; font-size: 0.85rem; color: #f1f5f9; }
.ma-doc-preview { height: 200px; display: flex; align-items: center; justify-content: center; background: #000; padding: 1rem; }
.ma-doc-preview img { max-height: 100%; max-width: 100%; object-fit: contain; }

.ma-spinner { width: 24px; height: 24px; border: 3px solid rgba(99,102,241,0.2); border-top-color: #6366f1; border-radius: 50%; animation: spin 0.8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

@media (max-width: 768px) {
  .ma-modal--lg { max-width: 100%; }
  .ma-tabs { gap: 0.35rem; }
  .ma-tab { padding: 0.35rem 0.75rem; font-size: 0.75rem; }
}
</style>
