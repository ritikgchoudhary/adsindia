<template>
  <DashboardLayout page-title="Account & KYC" :dark-theme="true">
    <div class="tw-grid tw-grid-cols-1 tw-gap-8">
      
      <!-- Step Progress Indicator -->
      <div class="tw-bg-slate-900/80 tw-backdrop-blur-sm tw-rounded-xl sm:tw-rounded-2xl tw-shadow-xl tw-border tw-border-slate-800 tw-p-4 sm:tw-p-8">
        <div class="tw-flex tw-justify-between tw-items-center tw-relative">
          <!-- Connecting Line -->
          <div class="tw-absolute tw-top-4 sm:tw-top-6 tw-left-0 tw-w-full tw-h-0.5 tw-bg-slate-800 -tw-z-0"></div>
          
          <!-- Steps -->
          <div class="tw-relative tw-z-10 tw-text-center tw-flex-1 tw-cursor-pointer" @click="navigateToStep(1)">
            <div 
              class="tw-w-8 tw-h-8 sm:tw-w-12 sm:tw-h-12 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mx-auto tw-border-2 tw-transition-all tw-duration-300"
              :class="currentStep >= 1 ? 'tw-bg-indigo-600 tw-border-indigo-400 tw-text-white tw-shadow-lg tw-shadow-indigo-500/30' : 'tw-bg-slate-800 tw-border-slate-700 tw-text-slate-500'"
            >
              <i class="fas fa-university tw-text-sm sm:tw-text-lg"></i>
            </div>
            <div class="tw-mt-2 sm:tw-mt-3 tw-font-bold tw-text-[8px] sm:tw-text-xs tw-uppercase tw-tracking-wider" :class="currentStep >= 1 ? 'tw-text-white' : 'tw-text-slate-500'">Bank</div>
          </div>

          <div class="tw-relative tw-z-10 tw-text-center tw-flex-1 tw-cursor-pointer" @click="navigateToStep(2)">
            <div 
              class="tw-w-8 tw-h-8 sm:tw-w-12 sm:tw-h-12 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mx-auto tw-border-2 tw-transition-all tw-duration-300"
              :class="currentStep >= 2 ? 'tw-bg-indigo-600 tw-border-indigo-400 tw-text-white tw-shadow-lg tw-shadow-indigo-500/30' : 'tw-bg-slate-800 tw-border-slate-700 tw-text-slate-500'"
            >
              <i class="fas fa-id-card tw-text-sm sm:tw-text-lg"></i>
            </div>
            <div class="tw-mt-2 sm:tw-mt-3 tw-font-bold tw-text-[8px] sm:tw-text-xs tw-uppercase tw-tracking-wider" :class="currentStep >= 2 ? 'tw-text-white' : 'tw-text-slate-500'">Documents</div>
          </div>

          <div class="tw-relative tw-z-10 tw-text-center tw-flex-1 tw-cursor-pointer" @click="navigateToStep(3)">
            <div 
              class="tw-w-8 tw-h-8 sm:tw-w-12 sm:tw-h-12 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mx-auto tw-border-2 tw-transition-all tw-duration-300"
              :class="kycStatus === 1 ? 'tw-bg-emerald-500 tw-border-emerald-400 tw-text-white tw-shadow-lg tw-shadow-emerald-500/30' : (currentStep >= 3 ? 'tw-bg-indigo-600 tw-border-indigo-400 tw-text-white' : 'tw-bg-slate-800 tw-border-slate-700 tw-text-slate-500')"
            >
              <i class="fas tw-text-sm sm:tw-text-lg" :class="kycStatus === 1 ? 'fa-check' : 'fa-shield-alt'"></i>
            </div>
            <div class="tw-mt-2 sm:tw-mt-3 tw-font-bold tw-text-[8px] sm:tw-text-xs tw-uppercase tw-tracking-wider" :class="kycStatus === 1 ? 'tw-text-emerald-400' : (currentStep >= 3 ? 'tw-text-white' : 'tw-text-slate-500')">Review</div>
          </div>
        </div>
      </div>

      <!-- Step 1: Bank Details -->
      <div v-if="currentStep === 1" class="tw-bg-slate-900/80 tw-backdrop-blur-sm tw-rounded-xl sm:tw-rounded-2xl tw-shadow-xl tw-border tw-border-slate-800 tw-overflow-hidden">
        <div class="tw-bg-slate-800/50 tw-p-3 sm:tw-p-5 tw-border-b tw-border-slate-800 tw-flex tw-items-center tw-justify-between tw-gap-2">
          <h5 class="tw-text-white tw-font-bold tw-text-sm sm:tw-text-lg tw-m-0 tw-flex tw-items-center">
            <i class="fas fa-university tw-mr-2 sm:tw-mr-3 tw-text-indigo-400"></i><span class="tw-hidden sm:tw-inline">Step 1: </span>Bank Details
          </h5>
          <div class="tw-flex tw-gap-1 sm:tw-gap-2">
            <button 
              v-if="hasSavedBankDetails && !showBankForm && !isKycLocked" 
              @click="showBankForm = true"
              class="tw-px-2 sm:tw-px-4 tw-py-1.5 sm:tw-py-2 tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-text-[10px] sm:tw-text-xs tw-font-bold tw-rounded-lg tw-transition-all tw-border-0 tw-cursor-pointer tw-whitespace-nowrap"
            >
              <i class="fas fa-pen tw-mr-1"></i> Edit
            </button>
  
            <button
              v-if="isKycVerified"
              type="button"
              @click="resetVerifiedKyc"
              class="tw-px-2 sm:tw-px-4 tw-py-1.5 sm:tw-py-2 tw-bg-rose-600 hover:tw-bg-rose-700 tw-text-white tw-text-[10px] sm:tw-text-xs tw-font-bold tw-rounded-lg tw-transition-all tw-border-0 tw-cursor-pointer tw-whitespace-nowrap"
            >
              <i class="fas fa-trash tw-mr-1"></i> Reset
            </button>
          </div>
        </div>
        <div class="tw-p-4 sm:tw-p-8">
          
          <!-- Saved Bank Details Card (Matches Screenshot) -->
          <div v-if="hasSavedBankDetails && !showBankForm" class="tw-bg-slate-950 tw-border tw-border-white/5 tw-rounded-2xl tw-p-5 sm:tw-p-8 tw-text-white tw-shadow-2xl tw-max-w-lg tw-mx-auto tw-relative">
            <div class="tw-flex tw-justify-between tw-items-start tw-mb-5 sm:tw-mb-8">
              <div>
                <p class="tw-text-slate-400 tw-text-[8px] sm:tw-text-[10px] tw-uppercase tw-tracking-[0.2em] tw-font-bold tw-mb-1 sm:tw-mb-2">Bank Name</p>
                <h3 class="tw-text-lg sm:tw-text-2xl tw-font-extrabold tw-tracking-wide tw-m-0">{{ bankDetails.bank_name }}</h3>
              </div>
              <i class="fas fa-university tw-text-2xl sm:tw-text-4xl tw-text-white/10"></i>
            </div>
            
            <div class="tw-mb-5 sm:tw-mb-8">
              <p class="tw-text-slate-400 tw-text-[8px] sm:tw-text-[10px] tw-uppercase tw-tracking-[0.2em] tw-font-bold tw-mb-1 sm:tw-mb-2">Account Number</p>
              <div class="tw-text-sm sm:tw-text-xl tw-font-mono tw-tracking-[0.1em] sm:tw-tracking-[0.3em] tw-font-bold">{{ maskAccountNumber(bankDetails.account_number) }}</div>
            </div>
            
            <div class="tw-grid tw-grid-cols-2 tw-gap-4 sm:tw-gap-8">
              <div>
                <p class="tw-text-slate-400 tw-text-[8px] sm:tw-text-[10px] tw-uppercase tw-tracking-[0.2em] tw-font-bold tw-mb-1 sm:tw-mb-2">Holder Name</p>
                <div class="tw-text-xs sm:tw-text-base tw-font-bold tw-truncate">{{ bankDetails.account_holder_name }}</div>
              </div>
              <div>
                <p class="tw-text-slate-400 tw-text-[8px] sm:tw-text-[10px] tw-uppercase tw-tracking-[0.2em] tw-font-bold tw-mb-1 sm:tw-mb-2">IFSC Code</p>
                <div class="tw-text-xs sm:tw-text-base tw-font-mono tw-font-bold">{{ maskIfscOrUpi(bankDetails.ifsc_code) }}</div>
              </div>
              <div v-if="bankDetails.bank_registered_no">
                <p class="tw-text-slate-400 tw-text-[8px] sm:tw-text-[10px] tw-uppercase tw-tracking-[0.2em] tw-font-bold tw-mb-1 sm:tw-mb-2">Mobile Number</p>
                <div class="tw-text-xs sm:tw-text-base tw-font-bold">{{ maskKeepLast4(bankDetails.bank_registered_no) }}</div>
              </div>
              <div v-if="bankDetails.upi_id">
                <p class="tw-text-slate-400 tw-text-[8px] sm:tw-text-[10px] tw-uppercase tw-tracking-[0.2em] tw-font-bold tw-mb-1 sm:tw-mb-2">UPI ID</p>
                <div class="tw-text-xs sm:tw-text-base tw-font-bold tw-truncate">{{ maskUpi(bankDetails.upi_id) }}</div>
              </div>
            </div>

            <!-- Proceed Button (Inside Card) -->
            <button 
              @click="navigateToStep(2)"
              class="tw-w-full tw-mt-6 sm:tw-mt-10 tw-py-3 sm:tw-py-4 tw-bg-white/5 hover:tw-bg-white/10 tw-text-white tw-font-bold tw-rounded-xl tw-transition-all tw-border tw-border-white/10 tw-cursor-pointer tw-flex tw-items-center tw-justify-center tw-gap-2 tw-text-sm"
            >
              Proceed to Documents <i class="fas fa-arrow-right tw-text-xs"></i>
            </button>
          </div>

          <!-- Add/Edit Form -->
          <form v-if="showBankForm" @submit.prevent="submitBankDetails" class="tw-max-w-3xl tw-mx-auto">
            <div v-if="isKycLocked" class="tw-mb-6 tw-rounded-xl tw-border tw-border-amber-500/30 tw-bg-amber-500/10 tw-p-5 tw-text-amber-200 tw-text-sm">
              <i class="fas fa-lock tw-mr-2"></i>
              Bank details are locked while KYC is verified or under review. If you need to change it, use <b>Delete Old & Add New</b>.
            </div>
            <div class="tw-flex tw-justify-between tw-items-center tw-mb-6" v-if="hasSavedBankDetails">
               <h6 class="tw-text-slate-300 tw-font-bold tw-m-0">Update Bank Account</h6>
               <button 
                 type="button" 
                 @click="showBankForm = false"
                 class="tw-text-slate-500 hover:tw-text-slate-300 tw-text-sm tw-font-medium tw-bg-transparent tw-border-0 tw-cursor-pointer"
               >
                 Cancel
               </button>
            </div>
            
            <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-6">
              <div>
                <label class="tw-block tw-text-sm tw-font-bold tw-text-slate-400 tw-mb-2">Account Holder Name <span class="tw-text-red-500">*</span></label>
                <input type="text" v-model="bankDetails.account_holder_name" class="tw-w-full tw-px-4 tw-py-3 tw-bg-slate-950 tw-border tw-border-slate-800 tw-rounded-xl tw-text-white focus:tw-outline-none focus:tw-border-indigo-500 focus:tw-ring-4 focus:tw-ring-indigo-500/10 tw-transition-all" required placeholder="Enter account holder name">
              </div>
              <div>
                <label class="tw-block tw-text-sm tw-font-bold tw-text-slate-400 tw-mb-2">Bank Name <span class="tw-text-red-500">*</span></label>
                <input type="text" v-model="bankDetails.bank_name" class="tw-w-full tw-px-4 tw-py-3 tw-bg-slate-950 tw-border tw-border-slate-800 tw-rounded-xl tw-text-white focus:tw-outline-none focus:tw-border-indigo-500 focus:tw-ring-4 focus:tw-ring-indigo-500/10 tw-transition-all" required placeholder="Ex. SBI, HDFC, ICICI">
              </div>
              <div>
                <label class="tw-block tw-text-sm tw-font-bold tw-text-slate-400 tw-mb-2">Account Number <span class="tw-text-red-500">*</span></label>
                <input type="text" v-model="bankDetails.account_number" class="tw-w-full tw-px-4 tw-py-3 tw-bg-slate-950 tw-border tw-border-slate-800 tw-rounded-xl tw-text-white focus:tw-outline-none focus:tw-border-indigo-500 focus:tw-ring-4 focus:tw-ring-indigo-500/10 tw-transition-all" required placeholder="Enter account number">
              </div>
              <div>
                <label class="tw-block tw-text-sm tw-font-bold tw-text-slate-400 tw-mb-2">IFSC Code <span class="tw-text-red-500">*</span></label>
                <input type="text" v-model="bankDetails.ifsc_code" class="tw-w-full tw-px-4 tw-py-3 tw-bg-slate-950 tw-border tw-border-slate-800 tw-rounded-xl tw-text-white focus:tw-outline-none focus:tw-border-indigo-500 focus:tw-ring-4 focus:tw-ring-indigo-500/10 tw-transition-all tw-uppercase" maxlength="11" required placeholder="Enter IFSC code">
              </div>
              <div>
                <label class="tw-block tw-text-sm tw-font-bold tw-text-slate-400 tw-mb-2">Bank Registered No. <span class="tw-text-red-500">*</span></label>
                <input type="text" v-model="bankDetails.bank_registered_no" class="tw-w-full tw-px-4 tw-py-3 tw-bg-slate-950 tw-border tw-border-slate-800 tw-rounded-xl tw-text-white focus:tw-outline-none focus:tw-border-indigo-500 focus:tw-ring-4 focus:tw-ring-indigo-500/10 tw-transition-all" required placeholder="Mobile number registered with bank">
              </div>
              <div>
                <label class="tw-block tw-text-sm tw-font-bold tw-text-slate-400 tw-mb-2">UPI ID <span class="tw-text-slate-400 tw-font-normal">(Optional)</span></label>
                <input type="text" v-model="bankDetails.upi_id" class="tw-w-full tw-px-4 tw-py-3 tw-bg-slate-950 tw-border tw-border-slate-800 tw-rounded-xl tw-text-white focus:tw-outline-none focus:tw-border-indigo-500 focus:tw-ring-4 focus:tw-ring-indigo-500/10 tw-transition-all" placeholder="Enter UPI ID (e.g. name@bank)">
              </div>
            </div>
            <div class="tw-flex tw-justify-end tw-mt-6 sm:tw-mt-8">
              <button 
                type="button" 
                @click="submitBankDetails"
                class="tw-w-full sm:tw-w-auto tw-px-8 tw-py-3 sm:tw-py-3.5 tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-font-bold tw-rounded-xl tw-shadow-lg tw-transition-all tw-flex tw-items-center tw-justify-center tw-gap-2 tw-border-0 tw-cursor-pointer tw-text-sm"
              >
                <i class="fas fa-save"></i> Save Bank Details
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Step 2: KYC Documents -->
      <div v-if="currentStep === 2" class="tw-bg-slate-900/80 tw-backdrop-blur-sm tw-rounded-xl sm:tw-rounded-2xl tw-shadow-xl tw-border tw-border-slate-800 tw-overflow-hidden">
        <div class="tw-bg-slate-800/50 tw-p-3 sm:tw-p-5 tw-border-b tw-border-slate-800">
          <h5 class="tw-text-white tw-font-bold tw-text-sm sm:tw-text-lg tw-m-0 tw-flex tw-items-center">
            <i class="fas fa-id-card tw-mr-2 sm:tw-mr-3 tw-text-indigo-400"></i><span class="tw-hidden sm:tw-inline">Step 2: </span>Documents
          </h5>
        </div>
        <div class="tw-p-4 sm:tw-p-8">
          
          <!-- Integrated Payment & Terms (Required for Step 2) -->
          <div v-if="!hasPaidKYCFee && Number(kycFee) > 0" class="tw-mb-10 tw-rounded-2xl tw-border tw-border-indigo-500/20 tw-bg-indigo-500/5 tw-p-6">
            <div class="tw-flex tw-items-start tw-gap-4">
              <div class="tw-bg-indigo-500/20 tw-text-indigo-500 tw-p-3 tw-rounded-full">
                <i class="fas fa-credit-card tw-text-xl"></i>
              </div>
              <div class="tw-flex-1">
                <div class="tw-text-indigo-400 tw-font-bold tw-text-lg tw-mb-1">Verification Fee Required</div>
                <div class="tw-text-slate-400 tw-text-sm tw-leading-relaxed tw-mb-6">
                  A processing fee of <strong class="tw-text-indigo-400">₹{{ formatAmount(kycFee) }}</strong> is required for KYC verification and document processing.
                </div>
                
                <div class="tw-bg-slate-950/50 tw-rounded-xl tw-p-4 tw-mb-6 tw-border tw-border-white/5">
                  <label class="tw-flex tw-items-start tw-gap-3 tw-cursor-pointer tw-select-none">
                    <input type="checkbox" v-model="termsAccepted" class="tw-mt-1.5 focus:tw-ring-indigo-500">
                    <span class="tw-text-slate-300 tw-text-xs tw-leading-relaxed">
                      I agree with the
                      <router-link to="/policy/terms-of-service" class="tw-font-bold tw-text-indigo-400 tw-no-underline hover:tw-underline">Terms of Service</router-link>
                      and
                      <router-link to="/policy/privacy-policy" class="tw-font-bold tw-text-indigo-400 tw-no-underline hover:tw-underline">Privacy Policy</router-link>.
                    </span>
                  </label>
                </div>

                <button
                  type="button"
                  @click="handleKYCPayment"
                  :disabled="processingPayment || !termsAccepted"
                  class="tw-w-full md:tw-w-auto tw-px-8 tw-py-4 tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-font-extrabold tw-rounded-xl tw-shadow-lg tw-shadow-indigo-500/20 tw-transition-all tw-flex tw-items-center tw-justify-center tw-gap-2 disabled:tw-opacity-50 disabled:tw-cursor-not-allowed tw-border-0 tw-cursor-pointer tw-uppercase tw-tracking-widest tw-text-xs"
                >
                  <i class="fas fa-shield-alt"></i> Pay ₹{{ formatAmount(kycFee) }} & Proceed
                </button>
              </div>
            </div>
          </div>

          <div v-if="isKycLocked" class="tw-mb-8 tw-rounded-xl tw-border tw-border-amber-500/30 tw-bg-amber-500/10 tw-p-5 tw-text-amber-200 tw-text-sm">
            <i class="fas fa-lock tw-mr-2"></i>
            Your KYC is <b>{{ isKycVerified ? 'verified' : 'under review' }}</b>.
            <span v-if="isKycVerified" class="tw-block tw-mt-2">If you need to update it, use <b>Delete Old & Add New</b> above.</span>
          </div>

          <form @submit.prevent="submitKYC" class="tw-max-w-4xl tw-mx-auto" :class="{ 'tw-opacity-50 tw-pointer-events-none': !hasPaidKYCFee && Number(kycFee) > 0 }">
            <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-x-8 tw-gap-y-8">
              <div class="tw-col-span-full mb-2">
                <h6 class="tw-text-white tw-font-bold tw-text-sm tw-m-0">Government Issued Documents</h6>
                <p class="tw-text-slate-500 tw-text-xs tw-mt-1">Provide clear scans or photos of your Aadhaar and PAN documents.</p>
              </div>

              <div>
                <label class="tw-block tw-text-xs tw-font-bold tw-text-slate-400 tw-uppercase tw-tracking-widest tw-mb-3">Aadhaar Number</label>
                <input type="text" v-model="kycData.aadhaar_number" :disabled="isKycLocked" class="tw-w-full tw-px-5 tw-py-4 tw-bg-slate-950 tw-border tw-border-slate-800 tw-rounded-xl tw-text-white focus:tw-outline-none focus:tw-border-indigo-500 focus:tw-ring-4 focus:tw-ring-indigo-500/10 tw-transition-all" maxlength="12" placeholder="12-digit UID" :required="!isKycLocked">
              </div>

              <div>
                <label class="tw-block tw-text-xs tw-font-bold tw-text-slate-400 tw-uppercase tw-tracking-widest tw-mb-3">Aadhaar Document</label>
                <div class="tw-relative group">
                  <input :key="aadhaarInputKey" type="file" :disabled="isKycLocked" @change="handleFileChange('aadhaar', $event)" class="tw-w-full tw-px-5 tw-py-3.5 tw-bg-slate-950 tw-border tw-border-slate-800 tw-rounded-xl tw-text-white focus:tw-outline-none focus:tw-border-indigo-500 tw-transition-all file:tw-mr-4 file:tw-py-1.5 file:tw-px-3 file:tw-rounded-lg file:tw-border-0 file:tw-text-[10px] file:tw-font-black file:tw-bg-indigo-500/10 file:tw-text-indigo-400 hover:file:tw-bg-indigo-500/20" accept="image/*,.pdf" :required="!isKycLocked">
                  <div v-if="aadhaarFileName" class="tw-mt-3 tw-bg-emerald-500/10 tw-border tw-border-emerald-500/30 tw-rounded-xl tw-p-3 tw-flex tw-items-center tw-gap-3">
                    <i class="fas fa-check-circle tw-text-emerald-400"></i>
                    <span class="tw-text-emerald-300 tw-text-xs tw-truncate tw-font-bold">{{ aadhaarFileName }}</span>
                  </div>
                </div>
              </div>

              <div>
                <label class="tw-block tw-text-xs tw-font-bold tw-text-slate-400 tw-uppercase tw-tracking-widest tw-mb-3">PAN Card Number</label>
                <input type="text" v-model="kycData.pan_number" :disabled="isKycLocked" class="tw-w-full tw-px-5 tw-py-4 tw-bg-slate-950 tw-border tw-border-slate-800 tw-rounded-xl tw-text-white focus:tw-outline-none focus:tw-border-indigo-500 focus:tw-ring-4 focus:tw-ring-indigo-500/10 tw-transition-all tw-uppercase" maxlength="10" placeholder="10-character PAN" :required="!isKycLocked">
              </div>

              <div>
                <label class="tw-block tw-text-xs tw-font-bold tw-text-slate-400 tw-uppercase tw-tracking-widest tw-mb-3">PAN Card Document</label>
                <div class="tw-relative group">
                  <input :key="panInputKey" type="file" :disabled="isKycLocked" @change="handleFileChange('pan', $event)" class="tw-w-full tw-px-5 tw-py-3.5 tw-bg-slate-950 tw-border tw-border-slate-800 tw-rounded-xl tw-text-white focus:tw-outline-none focus:tw-border-indigo-500 tw-transition-all file:tw-mr-4 file:tw-py-1.5 file:tw-px-3 file:tw-rounded-lg file:tw-border-0 file:tw-text-[10px] file:tw-font-black file:tw-bg-indigo-500/10 file:tw-text-indigo-400 hover:file:tw-bg-indigo-500/20" accept="image/*,.pdf" :required="!isKycLocked">
                  <div v-if="panFileName" class="tw-mt-3 tw-bg-emerald-500/10 tw-border tw-border-emerald-500/30 tw-rounded-xl tw-p-3 tw-flex tw-items-center tw-gap-3">
                    <i class="fas fa-check-circle tw-text-emerald-400"></i>
                    <span class="tw-text-emerald-300 tw-text-xs tw-truncate tw-font-bold">{{ panFileName }}</span>
                  </div>
                </div>
              </div>
            </div>

            <div class="tw-flex tw-justify-between tw-mt-12 tw-pt-8 tw-border-t tw-border-white/5">
              <button type="button" @click="currentStep = 1" class="tw-px-8 tw-py-4 tw-bg-slate-800/80 hover:tw-bg-slate-800 tw-text-white tw-font-bold tw-rounded-xl tw-transition-all tw-flex tw-items-center tw-justify-center tw-gap-2 tw-border-0 tw-cursor-pointer tw-text-xs tw-uppercase tw-tracking-widest">
                <i class="fas fa-arrow-left"></i> Previous
              </button>
              <button 
                type="submit" 
                :disabled="isKycLocked || !canSubmitKYC || processing" 
                class="tw-px-10 tw-py-4 tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-font-extrabold tw-rounded-xl tw-shadow-xl tw-shadow-indigo-500/20 tw-transition-all tw-flex tw-items-center tw-justify-center tw-gap-2 disabled:tw-opacity-40 disabled:tw-cursor-not-allowed tw-border-0 tw-cursor-pointer tw-uppercase tw-tracking-widest tw-text-xs"
              >
                <i class="fas fa-paper-plane"></i>
                Submit for Verification
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Step 3: Verification Status -->
      <div v-if="currentStep === 3" class="tw-bg-slate-900/80 tw-backdrop-blur-sm tw-rounded-2xl tw-shadow-xl tw-border tw-border-slate-800 tw-overflow-hidden">
        <div class="tw-p-12 tw-text-center">
          
          <!-- Under Review -->
          <div v-if="kycStatus === 2" class="tw-max-w-md tw-mx-auto">
            <div class="tw-w-28 tw-h-28 tw-rounded-full tw-bg-sky-500/10 tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-8 tw-border-2 tw-border-sky-500/30">
              <i class="fas fa-clock tw-text-5xl tw-text-sky-400"></i>
            </div>
            <h3 class="tw-text-3xl tw-font-extrabold tw-text-white tw-mb-3">KYC Under Review</h3>
            <p class="tw-text-slate-400 tw-leading-relaxed tw-mb-10">Your KYC has been submitted successfully and is currently under review. Please wait for approval, it will be verified within 1 hour.</p>
            
          </div>

          <!-- Verified -->
          <div v-else-if="kycStatus === 1" class="tw-max-w-md tw-mx-auto">
            <div class="tw-w-28 tw-h-28 tw-rounded-full tw-bg-emerald-500/10 tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-8 tw-border-2 tw-border-emerald-500/30">
              <i class="fas fa-check-circle tw-text-5xl tw-text-emerald-400"></i>
            </div>
            <h3 class="tw-text-3xl tw-font-extrabold tw-text-white tw-mb-3">KYC Verified</h3>
            <p class="tw-text-slate-400 tw-mb-10">Congratulations! Your identity has been successfully verified. You can now access all features.</p>

            <button
              type="button"
              class="tw-w-full tw-py-4 tw-bg-slate-800 hover:tw-bg-rose-600 tw-text-white tw-font-bold tw-rounded-xl tw-transition-all tw-flex tw-items-center tw-justify-center tw-gap-2 tw-border-0 tw-cursor-pointer tw-uppercase tw-tracking-widest tw-text-xs"
              @click="resetVerifiedKyc"
            >
              <i class="fas fa-trash"></i> Reset & Edit Details
            </button>
          </div>

          <!-- Rejected -->
          <div v-else-if="kycStatus === 3" class="tw-max-w-md tw-mx-auto">
            <div class="tw-w-28 tw-h-28 tw-rounded-full tw-bg-rose-500/10 tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-8 tw-border-2 tw-border-rose-500/30">
              <i class="fas fa-times-circle tw-text-5xl tw-text-rose-400"></i>
            </div>
            <h3 class="tw-text-3xl tw-font-extrabold tw-text-white tw-mb-3">KYC Rejected</h3>
            <div class="tw-bg-rose-500/10 tw-border tw-border-rose-500/20 tw-p-5 tw-rounded-2xl tw-mb-10" v-if="kycRejectionReason">
              <div class="tw-text-rose-400 tw-font-bold tw-text-xs tw-uppercase tw-tracking-widest tw-mb-2">Reason for rejection</div>
              <p class="tw-text-rose-200/80 tw-text-sm tw-m-0">{{ kycRejectionReason }}</p>
            </div>
            <button @click="currentStep = 1" class="tw-px-10 tw-py-4 tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-font-extrabold tw-rounded-xl tw-shadow-xl tw-shadow-indigo-500/20 tw-transition-all tw-flex tw-items-center tw-justify-center tw-gap-2 tw-border-0 tw-cursor-pointer tw-uppercase tw-tracking-widest tw-text-xs">
              <i class="fas fa-redo"></i> Update & Re-submit
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
    const showBankForm = ref(true)
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

      const d = bankDetails.value
      // Validation
      if (!d.account_holder_name) {
         if (window.notify) window.notify('error', 'Please enter Account Holder Name');
         return
      }
      if (!d.bank_name) {
         if (window.notify) window.notify('error', 'Please enter Bank Name');
         return
      }
      if (!d.account_number) {
         if (window.notify) window.notify('error', 'Please enter Account Number');
         return
      }
      if (!d.ifsc_code) {
         if (window.notify) window.notify('error', 'Please enter IFSC Code');
         return
      }
      if (!d.bank_registered_no) {
         if (window.notify) window.notify('error', 'Please enter Bank Registered Mobile No.');
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
        } else {
           if (window.notify) {
              const m = response.data.message;
              const msg = (m?.success?.[0] || m?.error?.[0] || (Array.isArray(m) ? m[0] : m) || 'Unknown error');
              window.notify('error', msg);
           }
        }
      } catch (error) {
        console.error('Error saving bank details:', error)
        if (window.notify) {
          const m = error.response?.data?.message;
          const msg = (m?.error?.[0] || m?.success?.[0] || (Array.isArray(m) ? m[0] : m) || 'Failed to save bank details');
          window.notify('error', msg)
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
        if (window.notify) {
          const m = error.response?.data?.message;
          const msg = (m?.error?.[0] || m?.success?.[0] || (Array.isArray(m) ? m[0] : m) || error.response?.data?.message || 'Failed to submit KYC');
          window.notify('error', msg)
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
        const amount = kycFee.value || 990
        const redirectUrl = `/user/payment-redirect?flow=kyc_fee&amount=${amount}&plan_name=${encodeURIComponent('KYC Verification Fee')}&back=${encodeURIComponent('/user/account-kyc')}`
        const w = window.open(redirectUrl, '_blank')
        if (!w) {
          router.push(redirectUrl)
        } else if (window.notify) {
          window.notify('info', 'Payment tab opened. Complete payment to proceed with KYC.')
        }
      } catch (error) {
        console.error('Error processing payment:', error)
        if (window.notify) {
          const m = error.response?.data?.message;
          const msg = (m?.error?.[0] || m?.success?.[0] || (Array.isArray(m) ? m[0] : m) || error.response?.data?.message || 'Payment failed. Please try again.');
          window.notify('error', msg)
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
          
          // Set current step based on status and bank details
          const bankSaved = data.bank_details && data.bank_details.account_number && String(data.bank_details.account_number).trim() !== '';

          if (kycStatus.value === 1 || kycStatus.value === 2 || kycStatus.value === 3) {
            // Already submitted or verified -> Show status (Step 3)
            currentStep.value = 3
          } else if (bankSaved) {
            // Bank details exist but KYC not submitted (Status 0) -> Go to Documents (Step 2)
            currentStep.value = 2
          } else {
            // New user -> Start at Step 1
            currentStep.value = 1
          }
          
          // Check if we have saved bank details
          if (bankSaved) {
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
            const m = response.data.message;
            const msg = (m?.success?.[0] || m?.error?.[0] || (Array.isArray(m) ? m[0] : m) || 'Old KYC deleted. Please add new details.');
            window.notify('success', msg)
          }
          await fetchAccountData()
        } else if (window.notify) {
          const m = response.data.message;
          const msg = (m?.error?.[0] || m?.success?.[0] || (Array.isArray(m) ? m[0] : m) || 'Failed to delete old KYC');
          window.notify('error', msg)
        }
      } catch (error) {
        console.error('Error resetting KYC:', error)
        if (window.notify) {
          const m = error.response?.data?.message;
          const msg = (m?.error?.[0] || m?.success?.[0] || (Array.isArray(m) ? m[0] : m) || error.response?.data?.message || 'Failed to delete old KYC');
          window.notify('error', msg)
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
        const trx = urlParams.get('watchpay_trx') || urlParams.get('simplypay_trx') || urlParams.get('rupeerush_trx')
        if (trx) {
          try {
            const gateway = urlParams.get('simplypay_trx') ? 'simplypay' : (urlParams.get('rupeerush_trx') ? 'rupeerush' : 'watchpay')
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
      maskKeepLast4,
    }
  }
}
</script>

<style scoped>
@media (max-width: 640px) {
  /* Progress Indicator */
  .tw-p-4.sm\:tw-p-8 { padding: 0.65rem !important; }
  .tw-w-8.tw-h-8 { width: 1.85rem !important; height: 1.85rem !important; }
  .tw-w-8.tw-h-8 i { font-size: 0.8rem !important; }
  .tw-font-bold.tw-text-\[8px\] { font-size: 6px !important; margin-top: 0.2rem !important; }
  
  /* Bank Details Header */
  .tw-p-3.sm\:tw-p-5 { padding: 0.5rem 0.75rem !important; }
  h5.tw-text-sm { font-size: 0.8rem !important; }
  .tw-px-2.sm\:tw-px-4 { padding: 0.35rem 0.65rem !important; font-size: 8px !important; }
  
  /* Bank Details Card (Step 1) */
  .tw-p-4.sm\:tw-p-8 { padding: 0.85rem 0.65rem !important; }
  .tw-bg-slate-950.tw-p-5 { padding: 0.85rem !important; border-radius: 1rem !important; }
  
  /* Bank Card Labels */
  .tw-text-\[8px\].sm\:tw-text-\[10px\].tw-uppercase { font-size: 6px !important; margin-bottom: 0.25rem !important; letter-spacing: 0.1em !important; }
  h3.tw-text-lg { font-size: 0.95rem !important; margin-top: 0 !important; }
  .tw-text-sm.sm\:tw-text-xl.tw-font-mono { font-size: 0.8rem !important; }
  .tw-text-xs.sm\:tw-text-base { font-size: 0.7rem !important; }
  .tw-w-full.tw-mt-6 { margin-top: 0.75rem !important; padding: 0.65rem !important; border-radius: 0.75rem !important; font-size: 0.8rem !important; }
  
  /* Bank Form */
  .tw-grid.tw-grid-cols-1.md\:tw-grid-cols-2 { gap: 0.75rem !important; }
  input.tw-py-3 { padding: 0.5rem 0.85rem !important; font-size: 0.85rem !important; border-radius: 0.75rem !important; }
  label.tw-text-sm { font-size: 0.7rem !important; margin-bottom: 0.25rem !important; }
  .tw-px-8.tw-py-3 { width: 100% !important; padding: 0.65rem !important; font-size: 0.85rem !important; border-radius: 0.75rem !important; }
  
  /* Documents (Step 2) */
  .tw-mb-10.tw-p-6 { padding: 0.85rem !important; border-radius: 1rem !important; margin-bottom: 1rem !important; }
  .tw-text-lg.tw-mb-1 { font-size: 0.9rem !important; }
  .tw-text-sm.tw-leading-relaxed { font-size: 0.7rem !important; margin-bottom: 0.85rem !important; }
  .tw-px-8.tw-py-4 { padding: 0.65rem 1rem !important; font-size: 9px !important; border-radius: 0.75rem !important; }
  
  /* File inputs */
  input.tw-py-3\.5 { padding: 0.5rem 0.85rem !important; font-size: 0.8rem !important; }
  .tw-bg-emerald-500\/10.tw-p-3 { padding: 0.4rem 0.65rem !important; border-radius: 0.65rem !important; margin-top: 0.4rem !important; }
  
  /* Document Form Labels */
  .tw-text-xs.tw-font-bold.tw-text-slate-400.tw-uppercase { font-size: 9px !important; margin-bottom: 0.5rem !important; }
  
  /* Document buttons */
  .tw-px-10.tw-py-4 { padding: 0.65rem 1rem !important; font-size: 9px !important; border-radius: 0.75rem !important; }
  .tw-mt-12.tw-pt-8 { margin-top: 1rem !important; padding-top: 0.75rem !important; }
  
  /* Review Status (Step 3) */
  .tw-p-12 { padding: 1.5rem 0.85rem !important; }
  .tw-w-28.tw-h-28 { width: 4rem !important; height: 4rem !important; margin-bottom: 1rem !important; }
  .tw-w-28.tw-h-28 i { font-size: 1.75rem !important; }
  h3.tw-text-3xl { font-size: 1.25rem !important; }
  p.tw-text-slate-400 { font-size: 0.75rem !important; margin-bottom: 1rem !important; }
  .tw-bg-rose-500\/10 { padding: 0.75rem !important; border-radius: 0.85rem !important; margin-bottom: 1rem !important; }
  
  /* Modals */
  .tw-rounded-\[2\.5rem\] { border-radius: 1.25rem !important; }
  .tw-p-10 { padding: 1rem !important; }
  .tw-text-2xl { font-size: 1.15rem !important; }
}
</style>
