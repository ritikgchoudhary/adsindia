<template>
  <MasterAdminLayout page-title="KYC Management">
    <div class="tw-animate-fade-in-up">
      <!-- Stats Row -->
      <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-6 tw-mb-8">
        <!-- Pending Stats -->
        <div class="tw-bg-slate-800 tw-rounded-xl tw-p-6 tw-border tw-border-slate-700 tw-shadow-lg tw-relative tw-overflow-hidden tw-group hover:tw-border-amber-500/50 tw-transition-all">
          <div class="tw-absolute tw-right-0 tw-top-0 tw-w-24 tw-h-24 tw-bg-amber-500/10 tw-rounded-bl-full tw-transition-all group-hover:tw-bg-amber-500/20"></div>
          <div class="tw-flex tw-justify-between tw-items-start tw-relative tw-z-10">
            <div>
              <p class="tw-text-slate-400 tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-mb-1">Pending Requests</p>
              <h3 class="tw-text-3xl tw-font-bold tw-text-white tw-mb-0">{{ kycPendingCount }}</h3>
            </div>
            <div class="tw-w-12 tw-h-12 tw-rounded-xl tw-bg-gradient-to-br tw-from-amber-500 tw-to-orange-600 tw-flex tw-items-center tw-justify-center tw-shadow-lg tw-shadow-amber-500/20">
              <i class="fas fa-clock tw-text-white tw-text-xl"></i>
            </div>
          </div>
        </div>

        <!-- Approved Stats -->
        <div class="tw-bg-slate-800 tw-rounded-xl tw-p-6 tw-border tw-border-slate-700 tw-shadow-lg tw-relative tw-overflow-hidden tw-group hover:tw-border-emerald-500/50 tw-transition-all">
          <div class="tw-absolute tw-right-0 tw-top-0 tw-w-24 tw-h-24 tw-bg-emerald-500/10 tw-rounded-bl-full tw-transition-all group-hover:tw-bg-emerald-500/20"></div>
          <div class="tw-flex tw-justify-between tw-items-start tw-relative tw-z-10">
            <div>
              <p class="tw-text-slate-400 tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-mb-1">Approved (Total)</p>
              <h3 class="tw-text-3xl tw-font-bold tw-text-white tw-mb-0">{{ kycApprovedCount }}</h3>
            </div>
            <div class="tw-w-12 tw-h-12 tw-rounded-xl tw-bg-gradient-to-br tw-from-emerald-500 tw-to-teal-600 tw-flex tw-items-center tw-justify-center tw-shadow-lg tw-shadow-emerald-500/20">
              <i class="fas fa-check-circle tw-text-white tw-text-xl"></i>
            </div>
          </div>
        </div>

        <!-- Rejected Stats -->
        <div class="tw-bg-slate-800 tw-rounded-xl tw-p-6 tw-border tw-border-slate-700 tw-shadow-lg tw-relative tw-overflow-hidden tw-group hover:tw-border-rose-500/50 tw-transition-all">
          <div class="tw-absolute tw-right-0 tw-top-0 tw-w-24 tw-h-24 tw-bg-rose-500/10 tw-rounded-bl-full tw-transition-all group-hover:tw-bg-rose-500/20"></div>
          <div class="tw-flex tw-justify-between tw-items-start tw-relative tw-z-10">
            <div>
              <p class="tw-text-slate-400 tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-mb-1">Rejected Today</p>
              <h3 class="tw-text-3xl tw-font-bold tw-text-white tw-mb-0">{{ kycRejectedCount }}</h3>
            </div>
            <div class="tw-w-12 tw-h-12 tw-rounded-xl tw-bg-gradient-to-br tw-from-rose-500 tw-to-red-600 tw-flex tw-items-center tw-justify-center tw-shadow-lg tw-shadow-rose-500/20">
              <i class="fas fa-times-circle tw-text-white tw-text-xl"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content Card -->
      <div class="tw-bg-slate-800 tw-rounded-2xl tw-border tw-border-slate-700 tw-shadow-xl tw-overflow-hidden">
        
        <!-- Header & Filters -->
        <div class="tw-p-6 tw-border-b tw-border-slate-700 tw-flex tw-flex-col md:tw-flex-row md:tw-items-center tw-justify-between tw-gap-4">
          <div>
            <h2 class="tw-text-xl tw-font-bold tw-text-white tw-flex tw-items-center tw-gap-2">
              <i class="fas" :class="filterStatus === 'kyc_verified' ? 'fa-check-circle tw-text-emerald-500' : 'fa-id-card tw-text-indigo-500'"></i>
              {{ filterStatus === 'kyc_verified' ? 'Approved KYC Applications' : 'Pending KYC Applications' }}
            </h2>
            <p class="tw-text-slate-400 tw-text-sm tw-mt-1">
              {{ filterStatus === 'kyc_verified' ? 'View and manage approved user documents' : 'Review and take action on pending KYC requests' }}
            </p>
          </div>

          <div class="tw-flex tw-items-center tw-gap-3">
             <div class="tw-bg-slate-900 tw-p-1 tw-rounded-lg tw-border tw-border-slate-700 tw-flex">
               <button 
                @click="setFilter('kyc_pending')"
                class="tw-px-4 tw-py-2 tw-rounded-md tw-text-sm tw-font-bold tw-transition-all"
                :class="filterStatus === 'kyc_pending' ? 'tw-bg-indigo-600 tw-text-white tw-shadow-lg' : 'tw-text-slate-400 hover:tw-text-white'"
               >
                 <i class="fas fa-clock tw-mr-1"></i> Pending
               </button>
               <button 
                @click="setFilter('kyc_verified')"
                class="tw-px-4 tw-py-2 tw-rounded-md tw-text-sm tw-font-bold tw-transition-all"
                :class="filterStatus === 'kyc_verified' ? 'tw-bg-emerald-600 tw-text-white tw-shadow-lg' : 'tw-text-slate-400 hover:tw-text-white'"
               >
                 <i class="fas fa-check-circle tw-mr-1"></i> Approved
               </button>
             </div>
             <button 
              @click="fetchKYCRequests(currentPage)" 
              class="tw-w-10 tw-h-10 tw-rounded-lg tw-bg-slate-700 hover:tw-bg-slate-600 tw-text-slate-300 hover:tw-text-white tw-flex tw-items-center tw-justify-center tw-transition-all tw-border tw-border-slate-600"
             >
               <i class="fas fa-sync-alt"></i>
             </button>
          </div>
        </div>

        <!-- Table -->
        <div class="tw-overflow-x-auto">
          <table class="tw-w-full">
            <thead class="tw-bg-slate-900/50">
              <tr>
                <th class="tw-px-6 tw-py-4 tw-text-left tw-text-xs tw-font-bold tw-text-slate-400 tw-uppercase tw-tracking-wider">User Details</th>
                <th class="tw-px-6 tw-py-4 tw-text-left tw-text-xs tw-font-bold tw-text-slate-400 tw-uppercase tw-tracking-wider">Contact Info</th>
                <th class="tw-px-6 tw-py-4 tw-text-left tw-text-xs tw-font-bold tw-text-slate-400 tw-uppercase tw-tracking-wider">Documents</th>
                <th class="tw-px-6 tw-py-4 tw-text-left tw-text-xs tw-font-bold tw-text-slate-400 tw-uppercase tw-tracking-wider">Status</th>
                <th class="tw-px-6 tw-py-4 tw-text-left tw-text-xs tw-font-bold tw-text-slate-400 tw-uppercase tw-tracking-wider">Action</th>
              </tr>
            </thead>
            <tbody class="tw-divide-y tw-divide-slate-700">
              <tr v-if="loading">
                <td colspan="5" class="tw-px-6 tw-py-16 tw-text-center">
                  <div class="tw-w-10 tw-h-10 tw-border-4 tw-border-indigo-500 tw-border-t-transparent tw-rounded-full tw-animate-spin tw-mx-auto"></div>
                  <p class="tw-text-slate-400 tw-mt-4">Loading requests...</p>
                </td>
              </tr>
              <tr v-else-if="requests.length === 0">
                <td colspan="5" class="tw-px-6 tw-py-16 tw-text-center">
                  <div class="tw-w-16 tw-h-16 tw-rounded-full tw-bg-slate-700/50 tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-4">
                    <i class="fas fa-clipboard-check tw-text-3xl tw-text-slate-500"></i>
                  </div>
                  <h3 class="tw-text-lg tw-font-bold tw-text-white">No requests found</h3>
                  <p class="tw-text-slate-400">There are no {{ filterStatus === 'kyc_verified' ? 'approved' : 'pending' }} KYC requests at the moment.</p>
                </td>
              </tr>
              <tr v-else v-for="user in requests" :key="user.id" class="hover:tw-bg-slate-700/30 tw-transition-colors">
                <td class="tw-px-6 tw-py-4">
                  <div class="tw-flex tw-items-center tw-gap-3">
                    <div class="tw-w-10 tw-h-10 tw-rounded-lg tw-bg-gradient-to-br tw-from-indigo-600 tw-to-violet-600 tw-text-white tw-flex tw-items-center tw-justify-center tw-font-bold tw-text-sm">
                      {{ getInitials(user) }}
                    </div>
                    <div>
                      <div class="tw-font-bold tw-text-white">{{ user.firstname }} {{ user.lastname }}</div>
                      <div class="tw-text-xs tw-text-indigo-400">@{{ user.username }}</div>
                    </div>
                  </div>
                </td>
                <td class="tw-px-6 tw-py-4">
                  <div class="tw-flex tw-flex-col tw-gap-1">
                    <div class="tw-text-sm tw-text-slate-300">
                      <i class="fas fa-envelope tw-w-4 tw-text-slate-500"></i> {{ user.email }}
                    </div>
                    <div class="tw-text-sm tw-text-slate-300">
                      <i class="fas fa-phone tw-w-4 tw-text-slate-500"></i> {{ user.mobile || 'N/A' }}
                    </div>
                  </div>
                </td>
                <td class="tw-px-6 tw-py-4">
                   <div class="tw-flex tw-gap-2">
                     <span v-if="user.kyc_data?.aadhaar_number" class="tw-px-2.5 tw-py-1 tw-rounded-md tw-text-xs tw-font-bold tw-bg-slate-700 tw-text-slate-300 tw-border tw-border-slate-600">
                       Aadhaar
                     </span>
                     <span v-if="user.kyc_data?.pan_number" class="tw-px-2.5 tw-py-1 tw-rounded-md tw-text-xs tw-font-bold tw-bg-slate-700 tw-text-slate-300 tw-border tw-border-slate-600">
                       PAN
                     </span>
                   </div>
                </td>
                <td class="tw-px-6 tw-py-4">
                  <span v-if="user.kv === 1" class="tw-inline-flex tw-items-center tw-gap-1.5 tw-px-3 tw-py-1 tw-rounded-full tw-text-xs tw-font-bold tw-bg-emerald-500/10 tw-text-emerald-400 tw-border tw-border-emerald-500/20">
                    <span class="tw-w-1.5 tw-h-1.5 tw-rounded-full tw-bg-emerald-400"></span> Approved
                  </span>
                  <span v-else-if="user.kv === 2" class="tw-inline-flex tw-items-center tw-gap-1.5 tw-px-3 tw-py-1 tw-rounded-full tw-text-xs tw-font-bold tw-bg-amber-500/10 tw-text-amber-400 tw-border tw-border-amber-500/20">
                    <span class="tw-w-1.5 tw-h-1.5 tw-rounded-full tw-bg-amber-400 tw-animate-pulse"></span> Pending
                  </span>
                  <div class="tw-text-xs tw-text-slate-500 tw-mt-1">
                    {{ formatDate(user.updated_at) }}
                  </div>
                </td>
                <td class="tw-px-6 tw-py-4">
                  <button 
                    @click="viewDetails(user)"
                    class="tw-px-4 tw-py-2 tw-rounded-lg tw-bg-indigo-600 hover:tw-bg-indigo-500 tw-text-white tw-text-sm tw-font-bold tw-shadow-lg tw-shadow-indigo-500/20 tw-transition-all tw-flex tw-items-center tw-gap-2"
                  >
                    <i class="fas fa-eye"></i> {{ user.kv === 1 ? 'Details' : 'Review' }}
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="lastPage > 1" class="tw-p-6 tw-border-t tw-border-slate-700 tw-flex tw-justify-center tw-items-center tw-gap-4">
          <button 
            @click="fetchKYCRequests(currentPage - 1)" 
            :disabled="currentPage === 1"
            class="tw-w-10 tw-h-10 tw-rounded-lg tw-bg-slate-700 hover:tw-bg-slate-600 tw-text-white disabled:tw-opacity-50 disabled:tw-cursor-not-allowed tw-flex tw-items-center tw-justify-center tw-transition-all"
          >
            <i class="fas fa-chevron-left"></i>
          </button>
          <span class="tw-text-slate-400 tw-font-medium">Page <span class="tw-text-white">{{ currentPage }}</span> of {{ lastPage }}</span>
          <button 
            @click="fetchKYCRequests(currentPage + 1)" 
            :disabled="currentPage === lastPage"
            class="tw-w-10 tw-h-10 tw-rounded-lg tw-bg-slate-700 hover:tw-bg-slate-600 tw-text-white disabled:tw-opacity-50 disabled:tw-cursor-not-allowed tw-flex tw-items-center tw-justify-center tw-transition-all"
          >
            <i class="fas fa-chevron-right"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Details Modal -->
    <div v-if="showModal" class="tw-fixed tw-inset-0 tw-z-50 tw-flex tw-items-center tw-justify-center tw-px-4">
      <!-- Backdrop -->
      <div class="tw-absolute tw-inset-0 tw-bg-slate-900/80 tw-backdrop-blur-sm" @click="closeModal"></div>
      
      <!-- Modal Content -->
      <div class="tw-bg-slate-800 tw-rounded-2xl tw-border tw-border-slate-700 tw-shadow-2xl tw-w-full tw-max-w-4xl tw-relative tw-z-10 tw-overflow-hidden tw-animate-fade-in-up tw-max-h-[90vh] tw-flex tw-flex-col">
        
        <!-- Modal Header -->
        <div class="tw-p-6 tw-border-b tw-border-slate-700 tw-flex tw-justify-between tw-items-center tw-bg-slate-800">
          <h3 class="tw-text-xl tw-font-bold tw-text-white tw-flex tw-items-center tw-gap-3">
            <span class="tw-w-10 tw-h-10 tw-rounded-lg tw-bg-indigo-600/20 tw-text-indigo-400 tw-flex tw-items-center tw-justify-center">
              <i class="fas fa-user-shield"></i>
            </span>
             KYC Review: {{ selectedUser?.firstname }} {{ selectedUser?.lastname }}
          </h3>
          <button @click="closeModal" class="tw-w-8 tw-h-8 tw-rounded-lg tw-bg-slate-700 hover:tw-bg-slate-600 tw-text-slate-400 hover:tw-text-white tw-flex tw-items-center tw-justify-center tw-transition-all">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <!-- Modal Body -->
        <div class="tw-p-6 tw-overflow-y-auto tw-flex-1">
          
          <!-- User Info Grid -->
          <div class="tw-bg-slate-900/50 tw-rounded-xl tw-p-5 tw-border tw-border-slate-700 tw-mb-6">
            <h4 class="tw-text-sm tw-font-bold tw-text-slate-400 tw-uppercase tw-tracking-wider tw-mb-4 tw-flex tw-items-center tw-gap-2">
              <i class="fas fa-info-circle"></i> User Information
            </h4>
            <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-4 tw-gap-6 disabled:tw-opacity-50">
               <div>
                  <label class="tw-block tw-text-xs tw-text-slate-500 tw-mb-1">Full Name</label>
                  <div class="tw-text-white tw-font-semibold">{{ selectedUser?.firstname }} {{ selectedUser?.lastname }}</div>
               </div>
               <div>
                  <label class="tw-block tw-text-xs tw-text-slate-500 tw-mb-1">Email Address</label>
                  <div class="tw-text-white tw-font-semibold tw-truncate" :title="selectedUser?.email">{{ selectedUser?.email }}</div>
               </div>
               <div>
                  <label class="tw-block tw-text-xs tw-text-slate-500 tw-mb-1">Mobile Number</label>
                  <div class="tw-text-white tw-font-semibold">{{ selectedUser?.mobile || 'N/A' }}</div>
               </div>
               
               <!-- Bank Details (Added if needed, based on typical KYC flow) -->
            </div>
          </div>

          <!-- Bank Information -->
          <div class="tw-bg-slate-900/50 tw-rounded-xl tw-p-5 tw-border tw-border-slate-700 tw-mb-6">
            <h4 class="tw-text-sm tw-font-bold tw-text-slate-400 tw-uppercase tw-tracking-wider tw-mb-4 tw-flex tw-items-center tw-gap-2">
              <i class="fas fa-university"></i> Bank Information
            </h4>
            <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-6">
              <div>
                <label class="tw-block tw-text-xs tw-text-slate-500 tw-mb-1">Account Holder Name</label>
                <div class="tw-text-white tw-font-semibold">{{ selectedUser?.bank_details?.account_holder_name || 'N/A' }}</div>
              </div>
              <div>
                <label class="tw-block tw-text-xs tw-text-slate-500 tw-mb-1">Account Number</label>
                <div class="tw-text-white tw-font-semibold">{{ selectedUser?.bank_details?.account_number || 'N/A' }}</div>
              </div>
              <div>
                <label class="tw-block tw-text-xs tw-text-slate-500 tw-mb-1">IFSC Code</label>
                <div class="tw-text-white tw-font-semibold">{{ selectedUser?.bank_details?.ifsc_code || 'N/A' }}</div>
              </div>
              <div>
                <label class="tw-block tw-text-xs tw-text-slate-500 tw-mb-1">Bank Name</label>
                <div class="tw-text-white tw-font-semibold">{{ selectedUser?.bank_details?.bank_name || 'N/A' }}</div>
              </div>
              <div>
                <label class="tw-block tw-text-xs tw-text-slate-500 tw-mb-1">UPI Id</label>
                <div class="tw-text-white tw-font-semibold">{{ selectedUser?.bank_details?.upi_id || 'N/A' }}</div>
              </div>
              <div>
                <label class="tw-block tw-text-xs tw-text-slate-500 tw-mb-1">Bank Registered Number</label>
                <div class="tw-text-white tw-font-semibold">{{ selectedUser?.bank_details?.bank_registered_no || 'N/A' }}</div>
              </div>
            </div>
          </div>

          <!-- Documents Grid -->
          <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-6 tw-mb-8">
            <!-- Aadhaar Card -->
            <div class="tw-bg-slate-900/50 tw-rounded-xl tw-border tw-border-slate-700 tw-overflow-hidden tw-flex tw-flex-col">
              <div class="tw-p-4 tw-border-b tw-border-slate-700 tw-flex tw-justify-between tw-items-center tw-bg-slate-800/50">
                <h5 class="tw-font-bold tw-text-white tw-text-sm tw-flex tw-items-center tw-gap-2">
                  <i class="fas fa-id-card tw-text-indigo-500"></i> Aadhaar Card
                </h5>
                <span class="tw-bg-slate-700 tw-text-slate-300 tw-px-2 tw-py-1 tw-rounded text-xs tw-font-mono">
                  {{ selectedUser?.kyc_data?.aadhaar_number || 'N/A' }}
                </span>
              </div>
              <div class="tw-p-4 tw-flex-1 tw-flex tw-items-center tw-justify-center tw-bg-slate-900 tw-min-h-[200px]">
                <a
                  v-if="selectedUser?.kyc_data?.aadhaar_image || selectedUser?.kyc_data?.aadhaar_file"
                  :href="selectedUser?.kyc_data?.aadhaar_image || selectedUser?.kyc_data?.aadhaar_file"
                  target="_blank"
                  class="tw-group tw-relative tw-block tw-w-full tw-h-full tw-rounded-lg tw-overflow-hidden"
                >
                  <img
                    v-if="selectedUser?.kyc_data?.aadhaar_image"
                    :src="selectedUser.kyc_data.aadhaar_image"
                    alt="Aadhaar"
                    class="tw-w-full tw-h-full tw-object-contain tw-bg-slate-800 tw-rounded-lg tw-transition-transform group-hover:tw-scale-105"
                  >
                  <div v-else class="tw-flex tw-flex-col tw-items-center tw-justify-center tw-gap-2 tw-h-full tw-w-full tw-bg-slate-800 tw-rounded-lg tw-text-slate-200">
                    <i class="fas fa-file-alt tw-text-4xl"></i>
                    <span class="tw-text-sm tw-font-semibold">Open Aadhaar Document</span>
                  </div>
                  <div class="tw-absolute tw-inset-0 tw-bg-black/50 tw-flex tw-items-center tw-justify-center tw-opacity-0 group-hover:tw-opacity-100 tw-transition-opacity">
                    <span class="tw-bg-white/20 tw-backdrop-blur-sm tw-text-white tw-px-4 tw-py-2 tw-rounded-full tw-font-bold tw-flex tw-items-center tw-gap-2">
                      <i class="fas fa-search-plus"></i> View Full Size
                    </span>
                  </div>
                </a>
                <div v-else class="tw-text-center tw-text-slate-600">
                  <i class="fas fa-image tw-text-4xl tw-mb-2 tw-opacity-50"></i>
                  <p class="tw-text-sm">No document uploaded</p>
                </div>
              </div>
            </div>

            <!-- PAN Card -->
            <div class="tw-bg-slate-900/50 tw-rounded-xl tw-border tw-border-slate-700 tw-overflow-hidden tw-flex tw-flex-col">
              <div class="tw-p-4 tw-border-b tw-border-slate-700 tw-flex tw-justify-between tw-items-center tw-bg-slate-800/50">
                <h5 class="tw-font-bold tw-text-white tw-text-sm tw-flex tw-items-center tw-gap-2">
                  <i class="fas fa-credit-card tw-text-indigo-500"></i> PAN Card
                </h5>
                <span class="tw-bg-slate-700 tw-text-slate-300 tw-px-2 tw-py-1 tw-rounded text-xs tw-font-mono">
                  {{ selectedUser?.kyc_data?.pan_number || 'N/A' }}
                </span>
              </div>
              <div class="tw-p-4 tw-flex-1 tw-flex tw-items-center tw-justify-center tw-bg-slate-900 tw-min-h-[200px]">
                <a
                  v-if="selectedUser?.kyc_data?.pan_image || selectedUser?.kyc_data?.pan_file"
                  :href="selectedUser?.kyc_data?.pan_image || selectedUser?.kyc_data?.pan_file"
                  target="_blank"
                  class="tw-group tw-relative tw-block tw-w-full tw-h-full tw-rounded-lg tw-overflow-hidden"
                >
                  <img
                    v-if="selectedUser?.kyc_data?.pan_image"
                    :src="selectedUser.kyc_data.pan_image"
                    alt="PAN Card"
                    class="tw-w-full tw-h-full tw-object-contain tw-bg-slate-800 tw-rounded-lg tw-transition-transform group-hover:tw-scale-105"
                  >
                  <div v-else class="tw-flex tw-flex-col tw-items-center tw-justify-center tw-gap-2 tw-h-full tw-w-full tw-bg-slate-800 tw-rounded-lg tw-text-slate-200">
                    <i class="fas fa-file-alt tw-text-4xl"></i>
                    <span class="tw-text-sm tw-font-semibold">Open PAN Document</span>
                  </div>
                  <div class="tw-absolute tw-inset-0 tw-bg-black/50 tw-flex tw-items-center tw-justify-center tw-opacity-0 group-hover:tw-opacity-100 tw-transition-opacity">
                    <span class="tw-bg-white/20 tw-backdrop-blur-sm tw-text-white tw-px-4 tw-py-2 tw-rounded-full tw-font-bold tw-flex tw-items-center tw-gap-2">
                      <i class="fas fa-search-plus"></i> View Full Size
                    </span>
                  </div>
                </a>
                <div v-else class="tw-text-center tw-text-slate-600">
                  <i class="fas fa-image tw-text-4xl tw-mb-2 tw-opacity-50"></i>
                  <p class="tw-text-sm">No document uploaded</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Actions Area -->
          <div class="tw-bg-slate-800 tw-border-t tw-border-slate-700 tw-pt-6 tw-mt-auto">
             
             <!-- Case 1: Already Approved -->
             <div v-if="selectedUser?.kv === 1 && !showRejectForm" class="tw-bg-emerald-500/10 tw-border tw-border-emerald-500/20 tw-rounded-xl tw-p-4">
               <div class="tw-flex tw-flex-col md:tw-flex-row md:tw-items-center tw-justify-between tw-gap-4">
                 <div class="tw-flex tw-items-center tw-gap-3">
                   <div class="tw-w-10 tw-h-10 tw-rounded-full tw-bg-emerald-500/20 tw-flex tw-items-center tw-justify-center tw-flex-shrink-0">
                     <i class="fas fa-check tw-text-emerald-500"></i>
                   </div>
                   <div>
                     <h4 class="tw-text-emerald-400 tw-font-bold tw-text-sm">KYC Approved</h4>
                     <p class="tw-text-emerald-500/70 tw-text-xs">This user has already been verified.</p>
                   </div>
                 </div>
                 <div class="tw-flex tw-gap-3">
                   <button @click="unapproveKYC" :disabled="processing" class="tw-px-4 tw-py-2 tw-bg-slate-700 hover:tw-bg-slate-600 tw-text-white tw-rounded-lg tw-text-sm tw-font-bold tw-transition-all hover:tw-shadow-lg disabled:tw-opacity-50">
                     <i class="fas fa-undo tw-mr-1"></i> Unapprove
                   </button>
                   <button @click="showRejectForm = true" :disabled="processing" class="tw-px-4 tw-py-2 tw-bg-rose-600 hover:tw-bg-rose-700 tw-text-white tw-rounded-lg tw-text-sm tw-font-bold tw-transition-all hover:tw-shadow-xl hover:tw-shadow-rose-500/20 disabled:tw-opacity-50">
                     <i class="fas fa-ban tw-mr-1"></i> Reject
                   </button>
                 </div>
               </div>
             </div>

             <!-- Case 2: Reject Form (common for both pending and approved) -->
             <div v-else-if="showRejectForm" class="tw-animate-fade-in">
               <div class="tw-bg-rose-500/5 tw-border tw-border-rose-500/20 tw-rounded-xl tw-p-5">
                 <h5 class="tw-text-rose-500 tw-font-bold tw-mb-3 tw-flex tw-items-center tw-gap-2">
                   <i class="fas fa-exclamation-triangle"></i> Reject KYC Application
                 </h5>
                 <div class="tw-mb-4">
                   <label class="tw-block tw-text-slate-400 tw-text-xs tw-font-bold tw-uppercase tw-mb-2">Reason for Rejection <span class="tw-text-rose-500">*</span></label>
                   <textarea 
                    v-model="rejectionReason" 
                    rows="3" 
                    placeholder="Enter the reason for rejection (this will be shown to the user)..."
                    class="tw-w-full tw-bg-slate-900 tw-border tw-border-slate-700 tw-rounded-xl tw-p-3 tw-text-white tw-text-sm focus:tw-outline-none focus:tw-border-rose-500 focus:tw-ring-1 focus:tw-ring-rose-500 tw-transition-all"
                   ></textarea>
                 </div>
                 <div class="tw-flex tw-justify-end tw-gap-3">
                   <button @click="showRejectForm = false" class="tw-px-4 tw-py-2 tw-bg-slate-700 hover:tw-bg-slate-600 tw-text-slate-300 tw-rounded-lg tw-text-sm tw-font-bold tw-transition-all">
                     Cancel
                   </button>
                   <button @click="rejectKYC" :disabled="!rejectionReason || processing" class="tw-px-6 tw-py-2 tw-bg-rose-600 hover:tw-bg-rose-700 tw-text-white tw-rounded-lg tw-text-sm tw-font-bold tw-shadow-lg tw-shadow-rose-500/20 tw-transition-all disabled:tw-opacity-50 disabled:tw-cursor-not-allowed">
                     <i class="fas fa-paper-plane tw-mr-1"></i> Confirm Rejection
                   </button>
                 </div>
               </div>
             </div>

             <!-- Case 3: Pending Action -->
             <div v-else class="tw-flex tw-justify-end tw-gap-4">
                <button @click="showRejectForm = true" class="tw-px-6 tw-py-3 tw-bg-slate-700 hover:tw-bg-rose-600 hover:tw-text-white tw-text-rose-400 tw-rounded-xl tw-text-sm tw-font-bold tw-transition-all tw-border tw-border-rose-500/20 hover:tw-border-rose-500">
                  <i class="fas fa-times-circle tw-mr-2"></i> Reject KYC
                </button>
                <div class="tw-relative">
                   <button @click="approveKYC" :disabled="processing" class="tw-px-8 tw-py-3 tw-bg-gradient-to-r tw-from-emerald-500 tw-to-teal-600 hover:tw-from-emerald-600 hover:tw-to-teal-700 tw-text-white tw-rounded-xl tw-text-sm tw-font-bold tw-shadow-lg tw-shadow-emerald-500/30 tw-transition-all tw-transform hover:tw-scale-[1.02] disabled:tw-opacity-50 disabled:tw-cursor-not-allowed disabled:tw-transform-none">
                     <i class="fas fa-check-circle tw-mr-2"></i> Approve KYC
                   </button>
                </div>
             </div>

          </div>
        </div>
      </div>
    </div>

  </MasterAdminLayout>
</template>

<script>
import { ref, onMounted } from 'vue'
import MasterAdminLayout from '../../components/master_admin/MasterAdminLayout.vue'
import api from '../../services/api'

export default {
  name: 'MasterAdminKYC',
  components: { MasterAdminLayout },
  setup() {
    const requests = ref([])
    const loading = ref(false)
    const processing = ref(false)
    const currentPage = ref(1)
    const lastPage = ref(1)
    const filterStatus = ref('kyc_pending') // 'kyc_pending' | 'kyc_verified'
    
    // Stats
    const kycPendingCount = ref(0)
    const kycApprovedCount = ref(0)
    const kycRejectedCount = ref(0)

    // Modal
    const showModal = ref(false)
    const selectedUser = ref(null)
    const showRejectForm = ref(false)
    const rejectionReason = ref('')

    const getInitials = (u) => ((u.firstname?.[0] || '') + (u.lastname?.[0] || '') || u.username?.[0] || 'U').toUpperCase()
    const formatDate = (d) => d ? new Date(d).toLocaleDateString() + ' ' + new Date(d).toLocaleTimeString() : '—'

    const setFilter = (status) => {
      filterStatus.value = status
      currentPage.value = 1
      fetchKYCRequests(1)
    }

    const fetchKYCRequests = async (page = 1) => {
      currentPage.value = page
      loading.value = true
      try {
        const res = await api.get('/admin/users', { 
          params: { 
            status: filterStatus.value,
            page: currentPage.value,
            per_page: 20
          } 
        })
        if (res.data?.status === 'success' && res.data.data) {
          requests.value = res.data.data.users || []
          lastPage.value = res.data.data.last_page || 1
          const total = res.data.data.total ?? requests.value.length
          if (filterStatus.value === 'kyc_pending') kycPendingCount.value = total
          else if (filterStatus.value === 'kyc_verified') kycApprovedCount.value = total
        }
      } catch (e) {
        console.error('Error fetching KYC requests:', e)
        if (window.notify) window.notify('error', 'Failed to load KYC requests')
      } finally {
        loading.value = false
      }
    }

    const viewDetails = (user) => {
      selectedUser.value = user
      showModal.value = true
      showRejectForm.value = false
      rejectionReason.value = ''
    }

    const closeModal = () => {
      showModal.value = false
      selectedUser.value = null
      showRejectForm.value = false
    }

    const approveKYC = async () => {
      if (!selectedUser.value) return
      if (!confirm(`Are you sure you want to approve KYC for ${selectedUser.value.firstname}?`)) return
      
      processing.value = true
      try {
        const res = await api.post(`/admin/user/${selectedUser.value.id}/approve-kyc`)
        if (res.data?.status === 'success') {
          if (window.notify) window.notify('success', 'KYC Approved Successfully')
          closeModal()
          fetchKYCRequests()
          kycApprovedCount.value++
        } else {
          throw new Error(res.data?.message || 'Failed to approve')
        }
      } catch (e) {
        console.error('Approve error:', e)
        if (window.notify) window.notify('error', e.response?.data?.message || e.message)
      } finally {
        processing.value = false
      }
    }

    const rejectKYC = async () => {
      if (!selectedUser.value || !rejectionReason.value) return
      
      processing.value = true
      try {
        const res = await api.post(`/admin/user/${selectedUser.value.id}/reject-kyc`, {
          reason: rejectionReason.value
        })
        if (res.data?.status === 'success') {
          if (window.notify) window.notify('success', 'KYC Rejected Successfully')
          closeModal()
          fetchKYCRequests()
          kycRejectedCount.value++
        } else {
          throw new Error(res.data?.message || 'Failed to reject')
        }
      } catch (e) {
        console.error('Reject error:', e)
        if (window.notify) window.notify('error', e.response?.data?.message || e.message)
      } finally {
        processing.value = false
      }
    }

    const unapproveKYC = async () => {
      if (!selectedUser.value) return
      if (!confirm(`Revoke KYC approval for ${selectedUser.value.firstname} ${selectedUser.value.lastname}? They will need to submit again if required.`)) return
      
      processing.value = true
      try {
        const res = await api.post(`/admin/user/${selectedUser.value.id}/unapprove-kyc`)
        if (res.data?.status === 'success') {
          if (window.notify) window.notify('success', 'KYC Unapproved Successfully')
          closeModal()
          fetchKYCRequests()
          kycApprovedCount.value = Math.max(0, kycApprovedCount.value - 1)
        } else {
          throw new Error(res.data?.message || 'Failed to unapprove')
        }
      } catch (e) {
        console.error('Unapprove error:', e)
        if (window.notify) window.notify('error', e.response?.data?.message || e.message)
      } finally {
        processing.value = false
      }
    }

    onMounted(() => {
      fetchKYCRequests()
      // Fetch approved count for stats (one quick request for count)
      api.get('/admin/users', { params: { status: 'kyc_verified', per_page: 1, page: 1 } })
        .then(r => {
          if (r.data?.status === 'success' && r.data.data?.total != null)
            kycApprovedCount.value = r.data.data.total
        })
        .catch(() => {})
    })

    return {
      requests, loading, processing, currentPage, lastPage, filterStatus,
      kycPendingCount, kycApprovedCount, kycRejectedCount,
      showModal, selectedUser, showRejectForm, rejectionReason,
      getInitials, formatDate, fetchKYCRequests, setFilter, viewDetails, closeModal,
      approveKYC, rejectKYC, unapproveKYC
    }
  }
}
</script>

<style scoped>
/* Scoped styles mainly for specific scrollbar or animation tweaks */
.tw-animate-fade-in-up {
  animation: fadeInUp 0.5s ease-out forwards;
}

.tw-animate-fade-in {
  animation: fadeIn 0.3s ease-out forwards;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

/* Custom scrollbar for table or modal content if needed */
::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}
::-webkit-scrollbar-track {
  background: rgba(30, 41, 59, 0.5); 
}
::-webkit-scrollbar-thumb {
  background: rgba(99, 102, 241, 0.3); 
  border-radius: 4px;
}
::-webkit-scrollbar-thumb:hover {
  background: rgba(99, 102, 241, 0.5); 
}
</style>
