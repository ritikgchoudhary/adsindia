<template>
  <DashboardLayout page-title="Account & KYC" :dark-theme="true">
    <div class="tw-grid tw-grid-cols-1 tw-gap-8">
      
      <!-- Step Progress Indicator -->
      <div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-slate-200 tw-p-6">
        <div class="tw-flex tw-justify-between tw-items-center tw-relative">
          <!-- Connecting Line -->
          <div class="tw-absolute tw-top-5 tw-left-0 tw-w-full tw-h-1 tw-bg-slate-100 -tw-z-0"></div>
          
          <!-- Steps -->
          <div class="tw-relative tw-z-10 tw-text-center tw-flex-1 tw-cursor-pointer" @click="navigateToStep(1)">
            <div 
              class="tw-w-12 tw-h-12 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mx-auto tw-border-4 tw-transition-all tw-duration-300"
              :class="currentStep >= 1 ? 'tw-bg-indigo-600 tw-border-indigo-100 tw-text-white tw-shadow-lg tw-shadow-indigo-500/30' : 'tw-bg-slate-100 tw-border-slate-50 tw-text-slate-400'"
            >
              <i class="fas fa-university tw-text-lg"></i>
            </div>
            <div class="tw-mt-3 tw-font-bold tw-text-sm" :class="currentStep >= 1 ? 'tw-text-indigo-600' : 'tw-text-slate-400'">Bank Details</div>
          </div>

          <div class="tw-relative tw-z-10 tw-text-center tw-flex-1 tw-cursor-pointer" @click="navigateToStep(2)">
            <div 
              class="tw-w-12 tw-h-12 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mx-auto tw-border-4 tw-transition-all tw-duration-300"
              :class="currentStep >= 2 ? 'tw-bg-indigo-600 tw-border-indigo-100 tw-text-white tw-shadow-lg tw-shadow-indigo-500/30' : 'tw-bg-slate-100 tw-border-slate-50 tw-text-slate-400'"
            >
              <i class="fas fa-id-card tw-text-lg"></i>
            </div>
            <div class="tw-mt-3 tw-font-bold tw-text-sm" :class="currentStep >= 2 ? 'tw-text-indigo-600' : 'tw-text-slate-400'">KYC Documents</div>
          </div>

          <div class="tw-relative tw-z-10 tw-text-center tw-flex-1 tw-cursor-pointer" @click="navigateToStep(3)">
            <div 
              class="tw-w-12 tw-h-12 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mx-auto tw-border-4 tw-transition-all tw-duration-300"
              :class="kycStatus === 1 ? 'tw-bg-emerald-500 tw-border-emerald-100 tw-text-white tw-shadow-lg tw-shadow-emerald-500/30' : (currentStep >= 3 ? 'tw-bg-indigo-600 tw-border-indigo-100 tw-text-white' : 'tw-bg-slate-100 tw-border-slate-50 tw-text-slate-400')"
            >
              <i class="fas" :class="kycStatus === 1 ? 'fa-check' : 'fa-shield-alt'"></i>
            </div>
            <div class="tw-mt-3 tw-font-bold tw-text-sm" :class="kycStatus === 1 ? 'tw-text-emerald-600' : (currentStep >= 3 ? 'tw-text-indigo-600' : 'tw-text-slate-400')">Verification</div>
          </div>
        </div>
      </div>

      <!-- Step 1: Bank Details -->
      <div v-if="currentStep === 1" class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-slate-200 tw-overflow-hidden">
        <div class="tw-bg-slate-50 tw-p-5 tw-border-b tw-border-slate-200 tw-flex tw-justify-between tw-items-center">
          <h5 class="tw-text-slate-900 tw-font-bold tw-text-lg tw-m-0 tw-flex tw-items-center">
            <i class="fas fa-university tw-mr-2 tw-text-indigo-600"></i>Step 1: Bank Details
          </h5>
          <button 
            v-if="hasSavedBankDetails && !showBankForm && !isKycLocked" 
            @click="showBankForm = true"
            class="tw-px-4 tw-py-2 tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-text-sm tw-font-bold tw-rounded-lg tw-transition-all tw-border-0 tw-cursor-pointer"
          >
            <i class="fas fa-plus tw-mr-1"></i> Add / Edit Details
          </button>

          <button
            v-if="isKycVerified"
            type="button"
            @click="resetVerifiedKyc"
            class="tw-px-4 tw-py-2 tw-bg-rose-600 hover:tw-bg-rose-700 tw-text-white tw-text-sm tw-font-bold tw-rounded-lg tw-transition-all tw-border-0 tw-cursor-pointer"
          >
            <i class="fas fa-trash tw-mr-1"></i> Delete Old & Add New
          </button>
        </div>
        <div class="tw-p-6">
          
          <!-- Saved Bank Details Card -->
          <div v-if="hasSavedBankDetails && !showBankForm" class="tw-bg-gradient-to-br tw-from-slate-800 tw-to-slate-900 tw-rounded-xl tw-p-6 tw-text-white tw-shadow-lg tw-max-w-md tw-mx-auto tw-mb-6">
            <div class="tw-flex tw-justify-between tw-items-start tw-mb-6">
              <div>
                <p class="tw-text-slate-400 tw-text-xs tw-uppercase tw-tracking-wider tw-font-bold tw-mb-1">Bank Name</p>
                <h3 class="tw-text-xl tw-font-bold tw-tracking-wide">{{ bankDetails.bank_name }}</h3>
              </div>
              <i class="fas fa-university tw-text-3xl tw-opacity-20"></i>
            </div>
            
            <div class="tw-mb-6">
              <p class="tw-text-slate-400 tw-text-xs tw-uppercase tw-tracking-wider tw-font-bold tw-mb-1">Account Number</p>
              <div class="tw-text-lg tw-font-mono tw-tracking-widest">{{ maskAccountNumber(bankDetails.account_number) }}</div>
            </div>
            

            <div class="tw-flex tw-justify-between tw-mt-6">
              <div>
                <p class="tw-text-slate-400 tw-text-xs tw-uppercase tw-tracking-wider tw-font-bold tw-mb-1">Holder Name</p>
                <div class="tw-font-medium">{{ bankDetails.account_holder_name }}</div>
              </div>
              <div class="tw-text-right">
                <p class="tw-text-slate-400 tw-text-xs tw-uppercase tw-tracking-wider tw-font-bold tw-mb-1">IFSC Code</p>
                <div class="tw-font-mono">{{ maskIfscOrUpi(bankDetails.ifsc_code) }}</div>
              </div>
            </div>

            <div v-if="bankDetails.upi_id" class="tw-mt-6 tw-pt-6 tw-border-t tw-border-white/10">
              <p class="tw-text-slate-400 tw-text-xs tw-uppercase tw-tracking-wider tw-font-bold tw-mb-1">UPI ID</p>
              <div class="tw-font-medium">{{ maskUpi(bankDetails.upi_id) }}</div>
            </div>

            <!-- Next Step Button (Visible when viewing card) -->
            <div class="tw-mt-8 tw-border-t tw-border-white/10 tw-pt-6 tw-text-center">
               <button 
                 @click="navigateToStep(2)"
                 class="tw-w-full tw-py-3 tw-bg-white/10 hover:tw-bg-white/20 tw-text-white tw-font-bold tw-rounded-lg tw-transition-all tw-border-0 tw-cursor-pointer"
               >
                 Proceed to KYC Documents <i class="fas fa-arrow-right tw-ml-2"></i>
               </button>
            </div>
          </div>

          <!-- Add/Edit Form -->
          <form v-if="showBankForm" @submit.prevent="submitBankDetails" :class="{ 'tw-animate-fade-in-up': showBankForm }">
            <div v-if="isKycLocked" class="tw-mb-5 tw-rounded-xl tw-border tw-border-amber-200 tw-bg-amber-50 tw-p-4 tw-text-amber-800 tw-text-sm tw-font-medium">
              <i class="fas fa-lock tw-mr-2"></i>
              Bank details are locked while KYC is verified or under review. If you need to change it, use <b>Delete Old & Add New</b>.
            </div>
            <div class="tw-flex tw-justify-between tw-items-center tw-mb-6" v-if="hasSavedBankDetails">
               <h6 class="tw-text-slate-700 tw-font-bold tw-m-0">Update Bank Account</h6>
               <button 
                 type="button" 
                 @click="showBankForm = false"
                 class="tw-text-slate-500 hover:tw-text-slate-700 tw-text-sm tw-font-medium tw-bg-transparent tw-border-0 tw-cursor-pointer"
               >
                 Cancel
               </button>
            </div>
            
            <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-6">
              <div>
                <label class="tw-block tw-text-sm tw-font-bold tw-text-slate-700 tw-mb-2">Account Holder Name <span class="tw-text-red-500">*</span></label>
                <input type="text" v-model="bankDetails.account_holder_name" class="tw-w-full tw-px-4 tw-py-3 tw-bg-white tw-border tw-border-slate-300 tw-rounded-xl focus:tw-outline-none focus:tw-border-indigo-500 focus:tw-ring-4 focus:tw-ring-indigo-500/10 tw-transition-all" required placeholder="Enter account holder name">
              </div>
              <div>
                <label class="tw-block tw-text-sm tw-font-bold tw-text-slate-700 tw-mb-2">Bank Name <span class="tw-text-red-500">*</span></label>
                <input type="text" v-model="bankDetails.bank_name" class="tw-w-full tw-px-4 tw-py-3 tw-bg-white tw-border tw-border-slate-300 tw-rounded-xl focus:tw-outline-none focus:tw-border-indigo-500 focus:tw-ring-4 focus:tw-ring-indigo-500/10 tw-transition-all" required placeholder="Ex. SBI, HDFC, ICICI">
              </div>
              <div>
                <label class="tw-block tw-text-sm tw-font-bold tw-text-slate-700 tw-mb-2">Account Number <span class="tw-text-red-500">*</span></label>
                <input type="text" v-model="bankDetails.account_number" class="tw-w-full tw-px-4 tw-py-3 tw-bg-white tw-border tw-border-slate-300 tw-rounded-xl focus:tw-outline-none focus:tw-border-indigo-500 focus:tw-ring-4 focus:tw-ring-indigo-500/10 tw-transition-all" required placeholder="Enter account number">
              </div>
              <div>
                <label class="tw-block tw-text-sm tw-font-bold tw-text-slate-700 tw-mb-2">IFSC Code <span class="tw-text-red-500">*</span></label>
                <input type="text" v-model="bankDetails.ifsc_code" class="tw-w-full tw-px-4 tw-py-3 tw-bg-white tw-border tw-border-slate-300 tw-rounded-xl focus:tw-outline-none focus:tw-border-indigo-500 focus:tw-ring-4 focus:tw-ring-indigo-500/10 tw-transition-all tw-uppercase" maxlength="11" required placeholder="Enter IFSC code">
              </div>
              <div>
                <label class="tw-block tw-text-sm tw-font-bold tw-text-slate-700 tw-mb-2">Bank Registered No. <span class="tw-text-red-500">*</span></label>
                <input type="text" v-model="bankDetails.bank_registered_no" class="tw-w-full tw-px-4 tw-py-3 tw-bg-white tw-border tw-border-slate-300 tw-rounded-xl focus:tw-outline-none focus:tw-border-indigo-500 focus:tw-ring-4 focus:tw-ring-indigo-500/10 tw-transition-all" required placeholder="Mobile number registered with bank">
              </div>
              <div>
                <label class="tw-block tw-text-sm tw-font-bold tw-text-slate-700 tw-mb-2">UPI ID <span class="tw-text-slate-400 tw-font-normal">(Optional)</span></label>
                <input type="text" v-model="bankDetails.upi_id" class="tw-w-full tw-px-4 tw-py-3 tw-bg-white tw-border tw-border-slate-300 tw-rounded-xl focus:tw-outline-none focus:tw-border-indigo-500 focus:tw-ring-4 focus:tw-ring-indigo-500/10 tw-transition-all" placeholder="Enter UPI ID (e.g. name@bank)">
              </div>
            </div>
            <div class="tw-flex tw-justify-end tw-mt-8">
              <button type="submit" class="tw-px-8 tw-py-3.5 tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-font-bold tw-rounded-xl tw-shadow-lg tw-shadow-indigo-500/30 tw-transition-all tw-flex tw-items-center tw-justify-center tw-gap-2 tw-border-0 tw-cursor-pointer">
                <i class="fas fa-save"></i> Save Details
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Step 2: KYC Documents -->
      <div v-if="currentStep === 2" class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-slate-200 tw-overflow-hidden">
        <div class="tw-bg-slate-50 tw-p-5 tw-border-b tw-border-slate-200">
          <h5 class="tw-text-slate-900 tw-font-bold tw-text-lg tw-m-0 tw-flex tw-items-center">
            <i class="fas fa-id-card tw-mr-2 tw-text-indigo-600"></i>Step 2: KYC Documents
          </h5>
        </div>
        <div class="tw-p-6">
          <!-- Payment gate (₹990) -->
          <div v-if="!hasPaidKYCFee && Number(kycFee) > 0" class="tw-mb-6 tw-rounded-xl tw-border tw-border-amber-200 tw-bg-amber-50 tw-p-5">
            <div class="tw-text-amber-900 tw-font-bold tw-mb-2">
              <i class="fas fa-credit-card tw-mr-2"></i>KYC Fee Payment Required
            </div>
            <div class="tw-text-amber-800 tw-text-sm tw-leading-relaxed tw-mb-4">
              Please pay ₹{{ formatAmount(kycFee) }} first. After payment, you can submit your KYC documents for admin review.
            </div>
            <div class="tw-bg-white/60 tw-rounded-lg tw-p-3 tw-mb-4 tw-text-sm tw-text-slate-700">
              <label class="tw-flex tw-items-start tw-gap-2 tw-cursor-pointer tw-select-none">
                <input type="checkbox" v-model="termsAccepted" class="tw-mt-1">
                <span>
                  I agree with
                  <router-link to="/policy/terms-of-service" class="tw-font-bold tw-text-indigo-700 tw-no-underline hover:tw-underline">Terms of Service</router-link>
                  and
                  <router-link to="/policy/privacy-policy" class="tw-font-bold tw-text-indigo-700 tw-no-underline hover:tw-underline">Privacy Policy</router-link>
                  before payment.
                </span>
              </label>
            </div>
            <button
              type="button"
              @click="handleKYCPayment"
              :disabled="processingPayment || !termsAccepted"
              class="tw-w-full tw-py-3.5 tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-font-bold tw-rounded-xl tw-shadow-lg tw-shadow-indigo-500/30 tw-transition-all tw-flex tw-items-center tw-justify-center tw-gap-2 disabled:tw-opacity-60 disabled:tw-cursor-not-allowed tw-border-0 tw-cursor-pointer"
            >
              <i class="fas fa-arrow-right"></i>
              Pay ₹{{ formatAmount(kycFee) }} & Continue
            </button>
          </div>

          <div v-if="isKycLocked" class="tw-mb-6 tw-rounded-xl tw-border tw-border-amber-200 tw-bg-amber-50 tw-p-4 tw-text-amber-800 tw-text-sm tw-font-medium">
            <i class="fas fa-lock tw-mr-2"></i>
            Your KYC is <b>{{ isKycVerified ? 'verified' : 'under review' }}</b>. Editing is disabled.
            <span v-if="isKycVerified"> If you need to update it, click <b>Delete Old & Add New</b> (this will remove your old KYC + bank details).</span>
          </div>
          <form @submit.prevent="submitKYC">
            <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-6">
              <div>
                <label class="tw-block tw-text-sm tw-font-bold tw-text-slate-700 tw-mb-2">Aadhaar Number <span class="tw-text-red-500">*</span></label>
                <input type="text" v-model="kycData.aadhaar_number" :disabled="isKycLocked" class="tw-w-full tw-px-4 tw-py-3 tw-bg-white tw-border tw-border-slate-300 tw-rounded-xl focus:tw-outline-none focus:tw-border-indigo-500 focus:tw-ring-4 focus:tw-ring-indigo-500/10 tw-transition-all disabled:tw-opacity-70 disabled:tw-cursor-not-allowed" maxlength="12" pattern="[0-9]{12}" :required="!isKycLocked">
                <small class="tw-block tw-text-slate-400 tw-text-xs tw-mt-1">Enter 12-digit Aadhaar number</small>
              </div>
              <div>
                <label class="tw-block tw-text-sm tw-font-bold tw-text-slate-700 tw-mb-2">Aadhaar Document <span class="tw-text-red-500">*</span></label>
                <div class="tw-flex tw-items-center tw-gap-3 tw-flex-wrap">
                  <input :key="aadhaarInputKey" type="file" :disabled="isKycLocked" @change="handleFileChange('aadhaar', $event)" class="tw-flex-1 tw-min-w-0 tw-px-4 tw-py-3 tw-bg-white tw-border tw-border-slate-300 tw-rounded-xl focus:tw-outline-none focus:tw-border-indigo-500 tw-transition-all file:tw-mr-4 file:tw-py-1.5 file:tw-px-3 file:tw-rounded-lg file:tw-border-0 file:tw-text-xs file:tw-font-bold file:tw-bg-indigo-50 file:tw-text-indigo-700 hover:file:tw-bg-indigo-100 disabled:tw-opacity-70 disabled:tw-cursor-not-allowed" accept="image/*,.pdf" :required="!isKycLocked">
                  <span v-if="aadhaarFileName" class="tw-inline-flex tw-items-center tw-gap-2 tw-px-3 tw-py-1.5 tw-bg-emerald-50 tw-text-emerald-700 tw-rounded-lg tw-text-sm tw-font-medium tw-border tw-border-emerald-200 tw-shrink-0">
                    <i class="fas fa-check-circle tw-text-emerald-500"></i>
                    <span class="tw-truncate tw-max-w-[180px]" :title="aadhaarFileName">{{ aadhaarFileName }}</span>
                  </span>
                </div>
                <small class="tw-block tw-text-slate-400 tw-text-xs tw-mt-1">Upload Aadhaar card (Image or PDF). Selected file will appear here.</small>
              </div>
              <div>
                <label class="tw-block tw-text-sm tw-font-bold tw-text-slate-700 tw-mb-2">PAN Card Number <span class="tw-text-red-500">*</span></label>
                <input type="text" v-model="kycData.pan_number" :disabled="isKycLocked" class="tw-w-full tw-px-4 tw-py-3 tw-bg-white tw-border tw-border-slate-300 tw-rounded-xl focus:tw-outline-none focus:tw-border-indigo-500 focus:tw-ring-4 focus:tw-ring-indigo-500/10 tw-transition-all tw-uppercase disabled:tw-opacity-70 disabled:tw-cursor-not-allowed" maxlength="10" pattern="[A-Z]{5}[0-9]{4}[A-Z]{1}" :required="!isKycLocked">
                <small class="tw-block tw-text-slate-400 tw-text-xs tw-mt-1">Enter 10-character PAN number</small>
              </div>
              <div>
                <label class="tw-block tw-text-sm tw-font-bold tw-text-slate-700 tw-mb-2">PAN Card Document <span class="tw-text-red-500">*</span></label>
                <div class="tw-flex tw-items-center tw-gap-3 tw-flex-wrap">
                  <input :key="panInputKey" type="file" :disabled="isKycLocked" @change="handleFileChange('pan', $event)" class="tw-flex-1 tw-min-w-0 tw-px-4 tw-py-3 tw-bg-white tw-border tw-border-slate-300 tw-rounded-xl focus:tw-outline-none focus:tw-border-indigo-500 tw-transition-all file:tw-mr-4 file:tw-py-1.5 file:tw-px-3 file:tw-rounded-lg file:tw-border-0 file:tw-text-xs file:tw-font-bold file:tw-bg-indigo-50 file:tw-text-indigo-700 hover:file:tw-bg-indigo-100 disabled:tw-opacity-70 disabled:tw-cursor-not-allowed" accept="image/*,.pdf" :required="!isKycLocked">
                  <span v-if="panFileName" class="tw-inline-flex tw-items-center tw-gap-2 tw-px-3 tw-py-1.5 tw-bg-emerald-50 tw-text-emerald-700 tw-rounded-lg tw-text-sm tw-font-medium tw-border tw-border-emerald-200 tw-shrink-0">
                    <i class="fas fa-check-circle tw-text-emerald-500"></i>
                    <span class="tw-truncate tw-max-w-[180px]" :title="panFileName">{{ panFileName }}</span>
                  </span>
                </div>
                <small class="tw-block tw-text-slate-400 tw-text-xs tw-mt-1">Upload PAN card (Image or PDF). Selected file will appear here.</small>
              </div>
            </div>
            <div class="tw-flex tw-justify-between tw-mt-8">
              <button type="button" @click="currentStep = 1" class="tw-px-6 tw-py-3.5 tw-bg-slate-100 hover:tw-bg-slate-200 tw-text-slate-700 tw-font-bold tw-rounded-xl tw-transition-all tw-flex tw-items-center tw-justify-center tw-gap-2 tw-border-0 tw-cursor-pointer">
                <i class="fas fa-arrow-left"></i> Previous
              </button>
              <button type="submit" :disabled="isKycLocked || !canSubmitKYC || processing" class="tw-px-8 tw-py-3.5 tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-font-bold tw-rounded-xl tw-shadow-lg tw-shadow-indigo-500/30 tw-transition-all tw-flex tw-items-center tw-justify-center tw-gap-2 disabled:tw-opacity-60 disabled:tw-cursor-not-allowed tw-border-0 tw-cursor-pointer">
                <i class="fas fa-paper-plane"></i>
                Submit KYC
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Step 3: Verification Status -->
      <div v-if="currentStep === 3" class="tw-bg-slate-900 tw-rounded-2xl tw-shadow-sm tw-border tw-border-white/10 tw-overflow-hidden">
        <div class="tw-p-10 tw-text-center tw-text-white">
          
          <!-- Under Review -->
          <div v-if="kycStatus === 2" class="tw-max-w-md tw-mx-auto">
            <div class="tw-w-24 tw-h-24 tw-rounded-full tw-bg-sky-500/15 tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-6 tw-border tw-border-sky-400/20">
              <i class="fas fa-clock tw-text-5xl tw-text-sky-300"></i>
            </div>
            <h3 class="tw-text-2xl tw-font-bold tw-text-white tw-mb-2">KYC Under Review</h3>
            <p class="tw-text-slate-300 tw-mb-8">Your KYC application has been submitted successfully and is pending admin review.</p>
            
            <!-- Payment Status -->
            <div v-if="hasPaidKYCFee" class="tw-bg-emerald-500/10 tw-border tw-border-emerald-400/20 tw-rounded-xl tw-p-4 tw-mb-6 tw-text-emerald-200 tw-text-sm tw-text-left">
              <i class="fas fa-check-circle tw-mr-2"></i>
              <strong>Payment Status:</strong> ₹{{ formatAmount(kycFee) }} verification fee has been paid successfully (via payment gateway).
              <span v-if="kycFeeTrx" class="tw-font-semibold"> TRX: {{ kycFeeTrx }}</span>
              <span v-if="kycFeePaidAt" class="tw-text-emerald-300"> ({{ kycFeePaidAt }})</span>
              Waiting for admin approval.
            </div>
            
            <!-- Payment Required -->
            <div v-else class="tw-bg-amber-500/10 tw-border tw-border-amber-400/20 tw-rounded-xl tw-p-5 tw-mb-6">
              <div class="tw-text-amber-200 tw-text-sm tw-font-medium tw-mb-3">
                <i class="fas fa-exclamation-triangle tw-mr-2"></i>
                <strong>Payment Required:</strong> Please pay ₹{{ formatAmount(kycFee) }} verification fee to proceed with KYC approval.
              </div>
              <div class="tw-text-slate-200 tw-text-sm tw-bg-slate-950/30 tw-border tw-border-white/10 tw-rounded-lg tw-p-3 tw-mb-4">
                You will be redirected to the payment gateway. After payment, we’ll confirm automatically.
              </div>

              <div class="tw-bg-slate-950/30 tw-border tw-border-white/10 tw-rounded-lg tw-p-3 tw-mb-4 tw-text-sm tw-text-slate-200">
                <label class="tw-flex tw-items-start tw-gap-2 tw-cursor-pointer tw-select-none">
                  <input type="checkbox" v-model="termsAccepted" class="tw-mt-1">
                  <span>
                    I agree with
                    <router-link to="/policy/terms-of-service" class="tw-font-bold tw-text-indigo-300 tw-no-underline hover:tw-underline hover:tw-text-indigo-200">Terms of Service</router-link>
                    and
                    <router-link to="/policy/privacy-policy" class="tw-font-bold tw-text-indigo-300 tw-no-underline hover:tw-underline hover:tw-text-indigo-200">Privacy Policy</router-link>
                    before payment.
                  </span>
                </label>
              </div>

              <button 
                @click="handleKYCPayment" 
                :disabled="processingPayment || !termsAccepted"
                class="tw-w-full tw-py-3.5 tw-bg-indigo-600 hover:tw-bg-indigo-500 tw-text-white tw-font-bold tw-rounded-xl tw-shadow-lg tw-shadow-indigo-500/20 tw-transition-all tw-flex tw-items-center tw-justify-center tw-gap-2 disabled:tw-opacity-60 disabled:tw-cursor-not-allowed tw-border-0 tw-cursor-pointer"
              >
                <i class="fas fa-credit-card"></i>
                Pay ₹{{ formatAmount(kycFee) }}
              </button>
            </div>
          </div>

          <!-- Verified -->
          <div v-else-if="kycStatus === 1" class="tw-max-w-md tw-mx-auto">
            <div class="tw-w-24 tw-h-24 tw-rounded-full tw-bg-emerald-500/15 tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-6 tw-border tw-border-emerald-400/20">
              <i class="fas fa-check-circle tw-text-5xl tw-text-emerald-300"></i>
            </div>
            <h3 class="tw-text-2xl tw-font-bold tw-text-white tw-mb-2">KYC Verified!</h3>
            <p class="tw-text-slate-300">Congratulations! Your KYC has been verified by admin.</p>

            <button
              type="button"
              class="tw-mt-6 tw-w-full tw-py-3.5 tw-bg-rose-600 hover:tw-bg-rose-700 tw-text-white tw-font-bold tw-rounded-xl tw-shadow-lg tw-shadow-rose-500/20 tw-transition-all tw-flex tw-items-center tw-justify-center tw-gap-2 tw-border-0 tw-cursor-pointer"
              @click="resetVerifiedKyc"
            >
              <i class="fas fa-trash"></i> Delete Old & Add New
            </button>
            <p class="tw-mt-3 tw-text-xs tw-text-slate-500">
              This will delete your old bank details & KYC documents. Then you can add new details and submit KYC again.
            </p>
          </div>

          <!-- Rejected -->
          <div v-else-if="kycStatus === 3" class="tw-max-w-md tw-mx-auto">
            <div class="tw-w-24 tw-h-24 tw-rounded-full tw-bg-rose-500/15 tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-6 tw-border tw-border-rose-400/20">
              <i class="fas fa-times-circle tw-text-5xl tw-text-rose-300"></i>
            </div>
            <h3 class="tw-text-2xl tw-font-bold tw-text-white tw-mb-2">KYC Rejected</h3>
            <p class="tw-text-rose-200 tw-bg-rose-500/10 tw-border tw-border-rose-400/20 tw-p-4 tw-rounded-xl tw-mb-6 tw-text-sm" v-if="kycRejectionReason">
              Reason: {{ kycRejectionReason }}
            </p>
            <button @click="currentStep = 1" class="tw-px-8 tw-py-3.5 tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-font-bold tw-rounded-xl tw-shadow-lg tw-shadow-indigo-500/30 tw-transition-all tw-flex tw-items-center tw-justify-center tw-gap-2 tw-border-0 tw-cursor-pointer">
              <i class="fas fa-redo"></i> Re-submit KYC
            </button>
          </div>

        </div>
      </div>

    </div>

    <!-- Payment Modal -->
    <div v-if="showPaymentModal" class="tw-fixed tw-inset-0 tw-z-[100] tw-flex tw-items-center tw-justify-center tw-px-4">
      <div class="tw-absolute tw-inset-0 tw-bg-black/60 tw-backdrop-blur-sm" @click="showPaymentModal = false"></div>
      <div class="tw-bg-white tw-rounded-2xl tw-shadow-2xl tw-w-full tw-max-w-md tw-relative tw-z-10 tw-overflow-hidden tw-animate-fade-in-up">
        <div class="tw-bg-indigo-600 tw-p-5 tw-flex tw-justify-between tw-items-center">
          <h5 class="tw-text-white tw-font-bold tw-text-lg tw-m-0 tw-flex tw-items-center">
            <i class="fas fa-credit-card tw-mr-2"></i>KYC Payment Required
          </h5>
          <button @click="showPaymentModal = false" class="tw-text-white/80 hover:tw-text-white tw-bg-transparent tw-border-0 tw-cursor-pointer">
            <i class="fas fa-times tw-text-xl"></i>
          </button>
        </div>
        
        <div class="tw-p-6">
          <div class="tw-text-center tw-mb-6">
            <div class="tw-w-16 tw-h-16 tw-bg-indigo-100 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-4">
              <span class="tw-text-2xl tw-text-indigo-600 tw-font-bold">₹</span>
            </div>
            <h4 class="tw-text-xl tw-font-bold tw-text-slate-900 tw-mb-1">Pay KYC Fee</h4>
            <p class="tw-text-slate-500 tw-text-sm tw-mb-4">Complete your KYC verification by paying the required fee</p>
            
            <div class="tw-bg-indigo-50 tw-border tw-border-indigo-100 tw-rounded-xl tw-p-4">
              <h3 class="tw-text-2xl tw-font-extrabold tw-text-indigo-700 tw-m-0">
                {{ currencySymbol }}{{ formatAmount(kycFee) }}
              </h3>
              <small class="tw-text-indigo-400 tw-font-medium">Amount to be paid</small>
            </div>
          </div>

          <div class="tw-mb-6 tw-text-slate-600 tw-text-sm tw-bg-slate-50 tw-border tw-border-slate-200 tw-rounded-xl tw-p-4">
            You will be redirected to the payment gateway. After payment, please return to this page.
          </div>

          <div class="tw-flex tw-gap-3">
             <button class="tw-flex-1 tw-py-3 tw-bg-slate-100 hover:tw-bg-slate-200 tw-text-slate-700 tw-font-bold tw-rounded-xl tw-transition-all tw-border-0 tw-cursor-pointer" @click="showPaymentModal = false">
               Cancel
             </button>
             <button 
               class="tw-flex-1 tw-py-3 tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-font-bold tw-rounded-xl tw-shadow-lg tw-shadow-indigo-500/30 tw-transition-all tw-flex tw-items-center tw-justify-center tw-gap-2 disabled:tw-opacity-60 disabled:tw-cursor-not-allowed tw-border-0 tw-cursor-pointer" 
               @click="processPayment" 
               :disabled="processingPayment"
             >
               <i class="fas fa-check"></i>
               Pay & Continue
             </button>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import DashboardLayout from '../../components/DashboardLayout.vue'
import api from '../../services/api'
import { openPaymentInNewTab } from '../../services/paymentWindow'

export default {
  name: 'AccountKYC',
  components: {
    DashboardLayout
  },
  setup() {
    const router = useRouter()
    const currentStep = ref(1)
    const bankDetails = ref({
      account_holder_name: '',
      bank_name: '',
      account_number: '',
      ifsc_code: '',
      bank_registered_no: '',
      upi_id: ''
    })
    const kycData = ref({
      aadhaar_number: '',
      pan_number: ''
    })
    const kycFiles = ref({})
    const aadhaarFileName = ref('')
    const panFileName = ref('')
    const kycStatus = ref(0)
    const kycRejectionReason = ref('')
    const kycFee = ref(990) // Fixed ₹990 fee
    const currencySymbol = ref('₹')
    const userBalance = ref(0)
    const kycFeeTrx = ref(null)
    const kycFeePaidAt = ref(null)
    const showPaymentModal = ref(false)
    const processing = ref(false)
    const processingPayment = ref(false)
    const termsAccepted = ref(false)
    const hasPaidKYCFee = ref(false)
    const hasSavedBankDetails = ref(false)
    const showBankForm = ref(false)
    const aadhaarInputKey = ref(1)
    const panInputKey = ref(1)

    const isKycVerified = computed(() => kycStatus.value === 1)
    const isKycPending = computed(() => kycStatus.value === 2)
    const isKycLocked = computed(() => isKycVerified.value || isKycPending.value)

    const canSubmitKYC = computed(() => {
      if (Number(kycFee.value) > 0 && !hasPaidKYCFee.value) return false
      return !isKycLocked.value &&
        kycData.value.aadhaar_number && 
        kycData.value.pan_number && 
        kycFiles.value.aadhaar && 
        kycFiles.value.pan
    })

    const formatAmount = (amount) => {
      if (!amount && amount !== 0) return '0.00'
      return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }

    const maskKeepLast4 = (val) => {
      if (val === null || val === undefined) return ''
      const str = String(val).trim().replace(/\s+/g, '')
      if (!str) return ''
      if (str.length <= 4) return str
      return '*'.repeat(str.length - 4) + str.slice(-4)
    }

    const maskAccountNumber = (accountNo) => {
      return maskKeepLast4(accountNo)
    }

    const maskIfscOrUpi = (value) => {
      return maskKeepLast4(value)
    }

    const maskUpi = (upi) => {
      if (!upi) return ''
      const str = String(upi).trim()
      if (!str) return ''
      const atIdx = str.indexOf('@')
      if (atIdx > 0) {
        const local = str.slice(0, atIdx)
        const domain = str.slice(atIdx + 1)
        return `${maskKeepLast4(local)}@${domain}`
      }
      return maskKeepLast4(str)
    }

    const handleFileChange = (type, event) => {
      const file = event.target.files[0]
      kycFiles.value[type] = file
      if (type === 'aadhaar') {
        aadhaarFileName.value = file ? file.name : ''
      } else {
        panFileName.value = file ? file.name : ''
      }
    }

    const submitBankDetails = async () => {
      if (isKycLocked.value) {
        if (window.notify) {
          window.notify('error', 'KYC is verified/under review. Please delete old KYC first to edit details.')
        }
        return
      }

      // Validate bank details
      if (!bankDetails.value.account_holder_name || 
          !bankDetails.value.bank_name || 
          !bankDetails.value.account_number || 
          !bankDetails.value.ifsc_code || 
          !bankDetails.value.bank_registered_no) {
        if (window.notify) {
          window.notify('error', 'Please fill all required bank details')
        }
        return
      }

      // Save bank details first
      try {
        const response = await api.post('/bank-details', bankDetails.value)
        if (response.data.status === 'success') {
          hasSavedBankDetails.value = true
          showBankForm.value = false
          if (window.notify) {
            window.notify('success', 'Bank details saved successfully.')
          }
        }
      } catch (error) {
        console.error('Error saving bank details:', error)
        if (window.notify) {
          window.notify('error', error.response?.data?.message || 'Failed to save bank details')
        }
      }
    }

    const processPayment = async () => {
      processingPayment.value = true
      try {
        const redirectUrl = `/user/payment-redirect?flow=kyc_fee&amount=990&plan_name=${encodeURIComponent('KYC Verification Fee')}&back=${encodeURIComponent('/user/account-kyc')}`
        const w = window.open(redirectUrl, '_blank')
        if (!w) {
          window.location.href = redirectUrl
        }
      } catch (e) {
        console.error(e)
      } finally {
        processingPayment.value = false
      }
    }

    const submitKYC = async () => {
      if (isKycLocked.value) {
        if (window.notify) {
          window.notify('error', 'KYC is verified/under review. You cannot edit KYC unless you delete old KYC and resubmit.')
        }
        return
      }

      if (!canSubmitKYC.value) {
        if (window.notify) {
          if (Number(kycFee.value) > 0 && !hasPaidKYCFee.value) {
            window.notify('error', 'Please pay the KYC fee (₹990) first. After payment, submit your documents.')
          } else {
            window.notify('error', 'Please fill all required fields and upload documents')
          }
        }
        return
      }

      processing.value = true
      try {
        const formData = new FormData()
        formData.append('aadhaar_number', kycData.value.aadhaar_number)
        formData.append('pan_number', kycData.value.pan_number)
        formData.append('aadhaar_document', kycFiles.value.aadhaar)
        formData.append('pan_document', kycFiles.value.pan)

        const response = await api.post('/kyc-submit', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })

        if (response.data.status === 'success') {
          currentStep.value = 3
          kycStatus.value = 2 // Pending review
          
          if (window.notify) {
            window.notify('success', 'KYC application submitted successfully! Waiting for admin review.')
          }
          
          // Check payment status when reaching Step 3
          await checkPaymentStatus()
        }
      } catch (error) {
        console.error('Error submitting KYC:', error)
        const errorMsg = error.response?.data?.message?.[0] || error.response?.data?.message || 'Failed to submit KYC'
        if (window.notify) {
          window.notify('error', errorMsg)
        }
      } finally {
        processing.value = false
      }
    }

    const checkPaymentStatus = async () => {
      try {
        const response = await api.get('/account-kyc')
        if (response.data.status === 'success' && response.data.data) {
          // Check if payment has been made by checking has_paid_kyc_fee flag
          hasPaidKYCFee.value = response.data.data.has_paid_kyc_fee || false
        }
      } catch (error) {
        console.error('Error checking payment status:', error)
      }
    }

    const handleKYCPayment = async () => {
      processingPayment.value = true
      try {
        if (!termsAccepted.value) {
          if (window.notify) window.notify('error', 'Please accept Terms & Privacy Policy before payment.')
          return
        }
        const redirectUrl = `/user/payment-redirect?flow=kyc_fee&back=${encodeURIComponent('/user/account-kyc')}`
        const w = window.open(redirectUrl, '_blank')
        if (!w) {
          router.push(redirectUrl)
        } else if (window.notify) {
          window.notify('info', 'Payment tab opened. Complete payment to proceed with KYC.')
        }
      } catch (error) {
        console.error('Error processing payment:', error)
        const errorMsg = error.response?.data?.message?.[0] || error.response?.data?.message || 'Payment failed. Please try again.'
        if (window.notify) {
          window.notify('error', errorMsg)
        }
      } finally {
        processingPayment.value = false
      }
    }

    const fetchAccountData = async () => {
      try {
        const response = await api.get('/account-kyc')
        if (response.data.status === 'success' && response.data.data) {
          const data = response.data.data
          bankDetails.value = data.bank_details || bankDetails.value
          // Ensure upi_id is reactive even if backend returns bank_details without it initially
          if (data.bank_details && !data.bank_details.upi_id) {
             bankDetails.value.upi_id = ''
          }
          kycStatus.value = data.kyc_status || 0
          kycRejectionReason.value = data.kyc_rejection_reason || ''
          userBalance.value = data.balance || 0
          currencySymbol.value = data.currency_symbol || '₹'
          if (typeof data.kyc_fee === 'number') {
            kycFee.value = data.kyc_fee
          } else if (data.kyc_fee !== undefined && data.kyc_fee !== null) {
            const feeNum = Number(data.kyc_fee)
            if (!Number.isNaN(feeNum)) kycFee.value = feeNum
          }
          hasPaidKYCFee.value = data.has_paid_kyc_fee || false
          kycFeeTrx.value = data.kyc_fee_trx || null
          kycFeePaidAt.value = data.kyc_fee_paid_at || null
          
          // Set current step based on status
          if (kycStatus.value === 0) {
            currentStep.value = 1
          } else if (kycStatus.value === 2 || kycStatus.value === 1 || kycStatus.value === 3) {
            // Allow navigating back to step 1 even if KYC is done/pending
            // But default to Step 3 (Status) for visibility
            currentStep.value = 3
          } else {
            currentStep.value = 2
          }
          
          // Check if we have saved bank details
          if (data.bank_details && data.bank_details.account_number) {
            hasSavedBankDetails.value = true
            showBankForm.value = false
          } else {
            hasSavedBankDetails.value = false
            showBankForm.value = true
          }
        }
      } catch (error) {
        console.error('Error loading account data:', error)
      }
    }

    const resetVerifiedKyc = async () => {
      if (!isKycVerified.value) return
      const ok = window.confirm('This will delete your old Bank Details + KYC documents. After that you can add new details and submit KYC again. Continue?')
      if (!ok) return

      processing.value = true
      try {
        const response = await api.post('/account-kyc/reset')
        if (response.data.status === 'success') {
          // Reset local state (and then re-fetch)
          bankDetails.value = {
            account_holder_name: '',
            bank_name: '',
            account_number: '',
            ifsc_code: '',
            bank_registered_no: '',
            branch_name: '',
            upi_id: ''
          }
          kycData.value = { aadhaar_number: '', pan_number: '' }
          kycFiles.value = {}
          aadhaarFileName.value = ''
          panFileName.value = ''
          aadhaarInputKey.value += 1
          panInputKey.value += 1
          hasSavedBankDetails.value = false
          showBankForm.value = true
          kycStatus.value = 0
          kycRejectionReason.value = ''
          currentStep.value = 1

          if (window.notify) {
            window.notify('success', response.data.message?.[0] || 'Old KYC deleted. Please add new details.')
          }
          await fetchAccountData()
        } else if (window.notify) {
          window.notify('error', response.data.message?.[0] || 'Failed to delete old KYC')
        }
      } catch (error) {
        console.error('Error resetting KYC:', error)
        if (window.notify) {
          window.notify('error', error.response?.data?.message?.[0] || error.response?.data?.message || 'Failed to delete old KYC')
        }
      } finally {
        processing.value = false
      }
    }

    // Watch for step changes to check payment status at Step 3
    watch(currentStep, async (newStep) => {
      if (newStep === 3 && kycStatus.value === 2) {
        // User reached verification step, check payment status
        await checkPaymentStatus()
      }
    })

    const navigateToStep = (step) => {
      // Allow navigation logic
      if (step === 1) {
        currentStep.value = 1
      } else if (step === 2) {
        // Only allow step 2 if step 1 is done or we have saved details
        if (hasSavedBankDetails.value || canSubmitKYC.value) {
           currentStep.value = 2
        }
      } else if (step === 3) {
        // Only allow step 3 if KYC is submitted
        if (kycStatus.value !== 0) {
          currentStep.value = 3
        }
      }
    }

    onMounted(() => {
      ;(async () => {
        // If returned from Gateway, confirm payment
        const urlParams = new URLSearchParams(window.location.search)
        const trx = urlParams.get('watchpay_trx') || urlParams.get('simplypay_trx')
        if (trx) {
          try {
            const gateway = urlParams.get('simplypay_trx') ? 'simplypay' : 'watchpay'
            const confirmRes = await api.post('/kyc-payment/confirm', { trx, gateway })
            if (confirmRes.data.status === 'success') {
              hasPaidKYCFee.value = true
              if (window.notify) window.notify('success', 'KYC fee payment verified successfully.')
              // Stay on KYC page and move user to document step
              currentStep.value = 2
              // remove trx param from URL (optional)
              try {
                const clean = window.location.pathname
                window.history.replaceState({}, document.title, clean)
              } catch (_) {}
            }
          } catch (e) {
            // ignore
          }
        }

        await fetchAccountData()
      })()
      
      // If already on Step 3 and KYC is pending, check payment status
      if (currentStep.value === 3 && kycStatus.value === 2) {
        checkPaymentStatus()
      }
    })

    return {
      currentStep,
      bankDetails,
      kycData,
      kycStatus,
      kycRejectionReason,
      kycFee,
      currencySymbol,
      userBalance,
      kycFeeTrx,
      kycFeePaidAt,
      showPaymentModal,
      processing,
      processingPayment,
      termsAccepted,
      canSubmitKYC,
      isKycVerified,
      isKycPending,
      isKycLocked,
      formatAmount,
      maskAccountNumber,
      maskIfscOrUpi,
      maskUpi,
      handleFileChange,
      submitBankDetails,
      processPayment,
      submitKYC,
      handleKYCPayment,
      hasPaidKYCFee,
      checkPaymentStatus,
      hasSavedBankDetails,
      showBankForm,
      navigateToStep,
      aadhaarFileName,
      panFileName,
      aadhaarInputKey,
      panInputKey,
      resetVerifiedKyc,
    }
  }
}
</script>
