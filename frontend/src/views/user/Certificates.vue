<template>
  <DashboardLayout page-title="Certificates" :dark-theme="true">
    <div class="tw-max-w-7xl tw-mx-auto tw-pb-10">
      
      <!-- Premium Hero Header -->
      <div class="tw-relative tw-mb-10 tw-overflow-hidden tw-rounded-3xl tw-bg-gradient-to-br tw-from-slate-900 tw-via-indigo-950 tw-to-slate-900 tw-p-8 tw-border tw-border-white/10 tw-shadow-2xl">
        <!-- Abstract Decorations -->
        <div class="tw-absolute -tw-top-24 -tw-right-24 tw-w-64 tw-h-64 tw-bg-indigo-500/20 tw-rounded-full tw-blur-3xl"></div>
        <div class="tw-absolute -tw-bottom-24 -tw-left-24 tw-w-64 tw-h-64 tw-bg-amber-500/10 tw-rounded-full tw-blur-3xl"></div>
        
        <div class="tw-relative tw-z-10 tw-flex tw-flex-col md:tw-flex-row tw-items-center tw-justify-between tw-gap-8">
          <div class="tw-max-w-2xl">
            <div class="tw-flex tw-items-center tw-gap-3 tw-mb-4">
              <span class="tw-px-3 tw-py-1 tw-bg-amber-500/20 tw-text-amber-400 tw-text-xs tw-font-bold tw-uppercase tw-tracking-widest tw-rounded-full tw-border tw-border-amber-500/30">
                Official Credentials
              </span>
            </div>
            <h1 class="tw-text-3xl md:tw-text-4xl tw-font-extrabold tw-text-white tw-mb-4">Course Certificates</h1>
            <p class="tw-text-slate-300 tw-text-lg tw-leading-relaxed">
              Celebrate your achievements. Complete courses to unlock professional certificates that validate your skills and boost your career profile.
            </p>
          </div>
          
          <!-- Quick Stats -->
          <div class="tw-flex tw-gap-4 tw-shrink-0">
            <div class="tw-bg-white/5 tw-backdrop-blur-md tw-border tw-border-white/10 tw-rounded-2xl tw-p-5 tw-text-center tw-min-w-[120px]">
              <div class="tw-text-3xl tw-font-bold tw-text-amber-400">{{ earnedCount }}</div>
              <div class="tw-text-slate-400 tw-text-xs tw-uppercase tw-font-bold tw-mt-1">Earned</div>
            </div>
            <div class="tw-bg-white/5 tw-backdrop-blur-md tw-border tw-border-white/10 tw-rounded-2xl tw-p-5 tw-text-center tw-min-w-[120px]">
              <div class="tw-text-3xl tw-font-bold tw-text-slate-300">{{ certificates.length }}</div>
              <div class="tw-text-slate-400 tw-text-xs tw-uppercase tw-font-bold tw-mt-1">Available</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Ad Certificate Gate (Premium Version) -->
      <div v-if="requiresAdCertificate && !hasAdCertificate" 
        class="tw-mb-10 tw-bg-gradient-to-r tw-from-amber-900/40 tw-to-orange-900/40 tw-backdrop-blur-xl tw-border tw-border-amber-500/30 tw-rounded-3xl tw-p-8 tw-shadow-xl"
      >
        <div class="tw-flex tw-flex-col lg:tw-flex-row tw-items-center tw-gap-8">
          <div class="tw-w-24 tw-h-24 tw-rounded-full tw-bg-amber-500/20 tw-flex tw-items-center tw-justify-center tw-text-4xl tw-text-amber-400 tw-animate-pulse tw-shrink-0 tw-border tw-border-amber-500/20">
            <i class="fas fa-lock"></i>
          </div>
          <div class="tw-flex-1 tw-text-center lg:tw-text-left">
            <h2 class="tw-text-2xl tw-font-bold tw-text-amber-200 tw-mb-2">Unlock Your Professional Journey</h2>
            <p class="tw-text-amber-100/70 tw-text-lg tw-max-w-2xl">
              To view and download your course certificates, you need to active your <b>Ad Certificate</b> subscription. 
              This also grants you access to premium learning materials.
            </p>
          </div>
          <div class="tw-flex tw-flex-col sm:tw-flex-row tw-gap-4 tw-shrink-0">
            <button
              @click="purchaseAdCertificate"
              :disabled="purchasingAdCert"
              class="tw-group tw-px-8 tw-py-4 tw-bg-amber-500 hover:tw-bg-amber-400 tw-text-slate-900 tw-font-black tw-text-lg tw-rounded-2xl tw-shadow-2xl tw-shadow-amber-500/40 tw-transition-all tw-duration-300 tw-border-0 tw-cursor-pointer disabled:tw-opacity-60 tw-flex tw-items-center tw-justify-center"
            >
              <i v-if="purchasingAdCert" class="fas fa-spinner fa-spin tw-mr-3"></i>
              <i v-else class="fas fa-crown tw-mr-3 tw-transition-transform group-hover:tw-scale-125"></i>
              Unlock Now – ₹{{ formatAmount(adCertificatePrice) }}
            </button>
            <router-link to="/user/courses" class="tw-px-8 tw-py-4 tw-bg-white/10 hover:tw-bg-white/20 tw-text-white tw-font-bold tw-rounded-2xl tw-border tw-border-white/10 tw-backdrop-blur-md tw-no-underline tw-inline-flex tw-items-center tw-justify-center tw-transition-all">
              <i class="fas fa-play tw-mr-3"></i>Continue Learning
            </router-link>
          </div>
        </div>
      </div>

      <!-- Main Content Grid -->
      <div v-if="loading" class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-3 tw-gap-8">
        <div v-for="i in 3" :key="i" class="tw-bg-white/5 tw-border tw-border-white/10 tw-rounded-3xl tw-h-80 tw-animate-pulse"></div>
      </div>

      <div v-else-if="certificates.length > 0" class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-3 tw-gap-8">
        <div v-for="item in certificates" :key="item.course_id + (item.certificate_id || '')" 
          class="tw-group tw-relative tw-flex tw-flex-col tw-h-full tw-bg-slate-900/50 tw-backdrop-blur-md tw-rounded-[2.5rem] tw-border-2 tw-transition-all tw-duration-500 hover:-tw-translate-y-2"
          :class="item.locked ? 'tw-border-slate-800 tw-opacity-70' : 'tw-border-white/5 hover:tw-border-indigo-500/50 hover:tw-bg-indigo-950/20 hover:tw-shadow-2xl hover:tw-shadow-indigo-500/10'"
        >
          <!-- Card Background Pattern (visible only on hover unlocked) -->
          <div v-if="!item.locked" class="tw-absolute tw-inset-0 tw-bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] tw-opacity-[0.03] tw-rounded-[2.5rem]"></div>
          
          <div class="tw-relative tw-p-8 tw-flex-1 tw-flex tw-flex-col">
            <!-- Top Badge -->
            <div class="tw-flex tw-justify-between tw-items-start tw-mb-8">
              <div class="tw-w-16 tw-h-16 tw-rounded-2xl tw-flex tw-items-center tw-justify-center tw-text-2xl tw-shadow-inner"
                :class="item.locked ? 'tw-bg-slate-800 tw-text-slate-600' : 'tw-bg-gradient-to-br tw-from-amber-400 tw-to-orange-600 tw-text-white tw-shadow-amber-500/20'"
              >
                <i class="fas" :class="item.locked ? 'fa-lock' : 'fa-certificate'"></i>
              </div>
              <span v-if="!item.locked" class="tw-px-3 tw-py-1 tw-bg-emerald-500/20 tw-text-emerald-400 tw-text-[10px] tw-font-black tw-uppercase tw-tracking-widest tw-rounded-lg tw-border tw-border-emerald-500/30">
                Earned
              </span>
              <span v-else class="tw-px-3 tw-py-1 tw-bg-slate-800 tw-text-slate-500 tw-text-[10px] tw-font-black tw-uppercase tw-tracking-widest tw-rounded-lg tw-border tw-border-slate-700">
                Pending
              </span>
            </div>
            
            <h3 class="tw-text-xl tw-font-bold tw-text-white tw-mb-4 tw-line-clamp-2 tw-min-h-[3.5rem] tw-leading-tight">
              {{ item.course_name }}
            </h3>
            
            <div class="tw-mt-auto">
              <template v-if="item.locked">
                <div class="tw-bg-white/5 tw-rounded-2xl tw-p-4 tw-mb-6 tw-border tw-border-white/5">
                  <p class="tw-text-slate-400 tw-text-xs tw-mb-2 tw-flex tw-justify-between">
                    <span>Progress to unlock</span>
                    <span>0%</span>
                  </p>
                  <div class="tw-h-1.5 tw-w-full tw-bg-slate-800 tw-rounded-full tw-overflow-hidden">
                    <div class="tw-h-full tw-w-0 tw-bg-indigo-500"></div>
                  </div>
                </div>
                <router-link :to="`/user/courses/${item.course_id}`" class="tw-w-full tw-py-4 tw-bg-indigo-600/10 hover:tw-bg-indigo-600 tw-text-indigo-400 hover:tw-text-white tw-font-bold tw-rounded-2xl tw-transition-all tw-duration-300 tw-no-underline tw-flex tw-items-center tw-justify-center tw-border tw-border-indigo-500/20 hover:tw-border-indigo-500">
                  <i class="fas fa-play tw-mr-2"></i>Resume Course
                </router-link>
              </template>
              
              <template v-else>
                <div class="tw-flex tw-items-center tw-gap-4 tw-mb-6">
                  <div class="tw-flex-1">
                    <p class="tw-text-slate-500 tw-text-[10px] tw-uppercase tw-font-bold tw-tracking-wider tw-mb-1">Credential ID</p>
                    <p class="tw-text-slate-300 tw-text-sm tw-font-mono tw-truncate">{{ item.certificate_number }}</p>
                  </div>
                  <div class="tw-text-right">
                    <p class="tw-text-slate-500 tw-text-[10px] tw-uppercase tw-font-bold tw-tracking-wider tw-mb-1">Issued</p>
                    <p class="tw-text-slate-300 tw-text-sm">{{ formatDate(item.issued_at) }}</p>
                  </div>
                </div>
                
                <div class="tw-grid tw-grid-cols-2 tw-gap-4">
                  <button type="button" class="tw-py-3 tw-bg-indigo-500/10 hover:tw-bg-indigo-500/20 tw-text-indigo-400 tw-font-bold tw-rounded-2xl tw-transition-all tw-border tw-border-indigo-500/20 tw-cursor-pointer tw-flex tw-items-center tw-justify-center tw-gap-2" @click="openViewModal(item)">
                    <i class="fas fa-eye"></i> View
                  </button>
                  <button type="button" class="tw-py-3 tw-bg-emerald-500 hover:tw-bg-emerald-400 tw-text-slate-900 tw-font-black tw-rounded-2xl tw-shadow-xl tw-shadow-emerald-500/20 tw-transition-all tw-border-0 tw-cursor-pointer tw-flex tw-items-center tw-justify-center tw-gap-2" @click="downloadCertificate(item)">
                    <i class="fas fa-download"></i> Save
                  </button>
                </div>
              </template>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else-if="!loading" class="tw-bg-slate-900/50 tw-backdrop-blur-md tw-border tw-border-white/5 tw-rounded-[3rem] tw-p-16 tw-text-center tw-max-w-3xl tw-mx-auto">
        <div class="tw-w-32 tw-h-32 tw-mx-auto tw-bg-indigo-500/10 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-text-indigo-500 tw-mb-8 tw-border tw-border-indigo-500/10">
          <i class="fas fa-graduation-cap tw-text-5xl"></i>
        </div>
        <h2 class="tw-text-white tw-font-bold tw-text-3xl tw-mb-4">Your achievements await</h2>
        <p class="tw-text-slate-400 tw-text-lg tw-mb-10 tw-max-w-md tw-mx-auto">Unlock professional courses and start earning certificates to level up your career profile.</p>
        <router-link to="/user/packages" class="tw-inline-flex tw-items-center tw-px-10 tw-py-4 tw-bg-indigo-600 hover:tw-bg-indigo-500 tw-text-white tw-font-black tw-text-lg tw-rounded-2xl tw-shadow-2xl tw-shadow-indigo-500/40 tw-transition-all tw-duration-300 tw-no-underline">
          <i class="fas fa-rocket tw-mr-3"></i>Browse Packages
        </router-link>
      </div>
    </div>

    <!-- Enhanced Certificate View Modal -->
    <div v-if="viewingCert" class="tw-fixed tw-inset-0 tw-z-[100] tw-flex tw-items-center tw-justify-center tw-p-2 sm:tw-p-6">
      <div class="tw-absolute tw-inset-0 tw-bg-slate-950/80 tw-backdrop-blur-md" @click="closeViewModal"></div>
      
      <div class="tw-relative tw-bg-white tw-w-full tw-max-w-3xl tw-rounded-[2.5rem] tw-shadow-[0_0_100px_-15px_rgba(99,102,241,0.3)] tw-overflow-hidden tw-animate-modal-in">
        <!-- Close Button -->
        <button type="button" class="tw-absolute tw-top-6 tw-right-6 tw-z-50 tw-w-10 tw-h-10 tw-bg-slate-100 hover:tw-bg-slate-200 tw-text-slate-500 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-border-0 tw-cursor-pointer tw-transition-all" @click="closeViewModal">
          <i class="fas fa-times"></i>
        </button>

        <!-- Certificate Container -->
        <div class="tw-p-4 sm:tw-p-10 tw-bg-slate-50 tw-relative">
          <div class="tw-p-1 tw-bg-gradient-to-br tw-from-amber-200 tw-via-amber-400 tw-to-amber-200 tw-rounded-[1.5rem] tw-shadow-xl" id="certPrintArea">
            <div class="tw-bg-white tw-rounded-[1.3rem] tw-p-6 sm:tw-p-12 tw-text-center tw-relative tw-overflow-hidden">
              <!-- Decorative BG -->
              <div class="tw-absolute tw-inset-0 tw-bg-[url('https://www.transparenttextures.com/patterns/cream-paper.png')] tw-opacity-50"></div>
              <div class="tw-absolute tw-top-0 tw-right-0 tw-w-40 tw-h-40 tw-bg-gradient-to-bl tw-from-amber-100/40 tw-to-transparent tw-rounded-bl-full"></div>
              
              <div class="tw-relative tw-z-10">
                <!-- Header -->
                <div class="tw-mb-8">
                  <div class="tw-inline-block tw-p-4 tw-rounded-full tw-bg-slate-50 tw-mb-4 tw-border tw-border-slate-100">
                    <i class="fas fa-certificate tw-text-6xl tw-text-amber-500 tw-drop-shadow-sm"></i>
                  </div>
                  <h4 class="tw-text-slate-400 tw-uppercase tw-tracking-[0.3em] tw-font-bold tw-text-[10px] tw-mb-2">Official Certification</h4>
                  <h2 class="tw-text-3xl sm:tw-text-5xl tw-font-serif tw-text-slate-900 tw-mb-4">Certificate<br><span class="tw-text-2xl sm:tw-text-3xl tw-font-light tw-italic tw-text-slate-500">of Achievement</span></h2>
                </div>

                <div class="tw-w-24 tw-h-[2px] tw-bg-amber-300 tw-mx-auto tw-mb-8"></div>

                <p class="tw-text-slate-500 tw-italic tw-mb-2">This acknowledges that</p>
                <h3 class="tw-text-2xl sm:tw-text-3xl tw-font-bold tw-text-slate-900 tw-mb-6">ADS Global Student</h3>
                
                <p class="tw-text-slate-500 tw-mb-4">has successfully met all requirements and demonstrated proficiency in</p>
                <div class="tw-px-6 tw-py-4 tw-bg-slate-900 tw-rounded-2xl tw-inline-block tw-mb-8 tw-shadow-xl">
                  <h3 class="tw-text-xl sm:tw-text-2xl tw-font-black tw-text-amber-400 tw-uppercase tw-tracking-wide">{{ viewingCert.course_name }}</h3>
                </div>

                <!-- Footer Details -->
                <div class="tw-grid tw-grid-cols-2 tw-gap-8 tw-max-w-lg tw-mx-auto tw-mb-10">
                  <div class="tw-text-left">
                    <p class="tw-text-[10px] tw-uppercase tw-font-bold tw-text-slate-400 tw-tracking-widest tw-mb-1">Instructor</p>
                    <p class="tw-text-slate-800 tw-font-serif tw-italic tw-text-lg">ADS Learning Academy</p>
                    <div class="tw-h-[1px] tw-w-full tw-bg-slate-200 tw-my-2"></div>
                  </div>
                  <div class="tw-text-right">
                    <p class="tw-text-[10px] tw-uppercase tw-font-bold tw-text-slate-400 tw-tracking-widest tw-mb-1">Date Awarded</p>
                    <p class="tw-text-slate-800 tw-font-mono tw-text-lg">{{ formatDate(viewingCert.issued_at) }}</p>
                    <div class="tw-h-[1px] tw-w-full tw-bg-slate-200 tw-my-2"></div>
                  </div>
                </div>

                <!-- Seal & ID -->
                <div class="tw-flex tw-flex-col sm:tw-flex-row tw-items-center tw-justify-between tw-gap-6 tw-bg-slate-50 tw-p-6 tw-rounded-2xl tw-border tw-border-slate-100">
                  <div class="tw-text-left">
                    <p class="tw-text-[10px] tw-uppercase tw-font-bold tw-text-slate-400 tw-tracking-widest tw-mb-1">Credential ID</p>
                    <p class="tw-text-slate-700 tw-font-mono tw-text-xs">{{ viewingCert.certificate_number }}</p>
                  </div>
                  <div class="tw-flex tw-items-center tw-gap-3">
                    <div class="tw-text-right">
                      <p class="tw-text-[10px] tw-uppercase tw-font-black tw-text-slate-800 tw-tracking-tighter">VERIFIED by</p>
                      <p class="tw-text-[10px] tw-font-bold tw-text-indigo-600">ADSINDIA OFFICIAL</p>
                    </div>
                    <div class="tw-w-12 tw-h-12 tw-rounded-full tw-bg-amber-400 tw-flex tw-items-center tw-justify-center tw-text-white tw-shadow-lg tw-shadow-amber-400/30">
                      <i class="fas fa-star tw-text-xl"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="tw-bg-white tw-p-8 tw-border-t tw-border-slate-100 tw-flex tw-flex-col sm:tw-flex-row tw-justify-center tw-gap-4">
          <button type="button" class="tw-px-8 tw-py-4 tw-bg-slate-100 tw-text-slate-600 tw-font-bold tw-rounded-2xl hover:tw-bg-slate-200 tw-transition-all tw-border-0 tw-cursor-pointer" @click="closeViewModal">
            Dismiss
          </button>
          <button type="button" class="tw-group tw-px-10 tw-py-4 tw-bg-indigo-600 tw-text-white tw-font-black tw-rounded-2xl hover:tw-bg-indigo-500 tw-shadow-2xl tw-shadow-indigo-500/30 tw-transition-all tw-border-0 tw-cursor-pointer tw-flex tw-items-center tw-justify-center tw-gap-3" @click="printCertificate">
            <i class="fas fa-print tw-transition-transform group-hover:tw-scale-110"></i> Print or Save as PDF
          </button>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import DashboardLayout from '../../components/DashboardLayout.vue'
import api from '../../services/api'

export default {
  name: 'Certificates',
  components: {
    DashboardLayout
  },
  setup() {
    const certificates = ref([])
    const viewingCert = ref(null)
    const loading = ref(true)
    const requiresAdCertificate = ref(true)
    const hasAdCertificate = ref(false)
    const adCertificatePrice = ref(1250)
    const purchasingAdCert = ref(false)

    // Computed
    const earnedCount = computed(() => certificates.value.filter(c => !c.locked).length)

    const formatDate = (dateString) => {
      if (!dateString) return '–'
      return new Date(dateString).toLocaleDateString('en-IN', { year: 'numeric', month: 'long', day: 'numeric' })
    }

    const formatAmount = (amount) => {
      if (!amount && amount !== 0) return '0.00'
      return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }

    const fetchCertificates = async () => {
      loading.value = true
      try {
        const response = await api.get('/certificates')
        
        if (response.data.status === 'success') {
          const payload = response.data.data && typeof response.data.data === 'object' ? response.data.data : {}
          requiresAdCertificate.value = payload.requires_ad_certificate !== false && payload.requires_ad_certificate !== 0
          hasAdCertificate.value = payload.has_ad_certificate === true || payload.has_ad_certificate === 1
          adCertificatePrice.value = payload.ad_certificate_price != null ? Number(payload.ad_certificate_price) : 1250

          // Handle different response structures
          let data = response.data.data
          
          if (data && typeof data === 'object' && data.data && Array.isArray(data.data)) {
            data = data.data
          } else if (Array.isArray(data)) {
            // Already correct
          } else if (data && typeof data === 'object') {
            data = data.list || data.items || data.certificates || []
          }
          
          certificates.value = Array.isArray(data) ? data : []
        } else {
          certificates.value = []
        }
      } catch (error) {
        console.error('Error loading certificates:', error)
        certificates.value = []
      } finally {
        loading.value = false
      }
    }

    const purchaseAdCertificate = async () => {
      purchasingAdCert.value = true
      try {
        const redirectUrl = `/user/payment-redirect?flow=ad_certificate&back=${encodeURIComponent('/user/certificates')}`
        const w = window.open(redirectUrl, '_blank')
        if (!w) {
          window.location.href = redirectUrl
        }
      } catch (e) {
        console.error(e)
      } finally {
        purchasingAdCert.value = false
      }
    }

    const openViewModal = (item) => {
      if (!item || item.locked) return
      viewingCert.value = item
    }

    const closeViewModal = () => {
      viewingCert.value = null
    }

    const printCertificate = () => {
      const el = document.getElementById('certPrintArea')
      if (!el) return
      
      const printContent = el.innerHTML
      const win = window.open('', '_blank')
      win.document.write(`
        <html><head><title>Certificate - \${viewingCert.value?.course_name || ''}</title>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        <script src="https://cdn.tailwindcss.com"><\/script>
        <style>
          @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Inter:wght@400;700;900&display=swap');
          body { font-family: 'Inter', sans-serif; padding: 0; margin: 0; background: white; -webkit-print-color-adjust: exact; }
          .font-serif { font-family: 'Playfair Display', serif; }
          @page { size: landscape; margin: 0; }
          .print-container { width: 100%; max-width: 1000px; margin: 0 auto; padding: 20px; }
          @media print {
            .print-container { padding: 0; }
          }
        </style>
        </head><body>
          <div class="print-container">
            \${printContent}
          </div>
          <script>
            window.onload = function() {
              setTimeout(function(){ window.print(); window.close(); }, 800);
            }
          <\/script>
        </body></html>`)
      win.document.close()
    }

    const downloadCertificate = (item) => {
      if (!item || item.locked) return
      viewingCert.value = item
      setTimeout(() => printCertificate(), 100)
    }

    onMounted(() => {
      ;(async () => {
        const trx = route.query.watchpay_trx || route.query.simplypay_trx
        if (trx) {
          try {
            const gateway = route.query.simplypay_trx ? 'simplypay' : 'watchpay'
            const confirmRes = await api.post('/ad-certificate/payment/confirm', { trx, gateway })
            if (confirmRes.data.status === 'success') {
              if (window.notify) window.notify('success', 'Certificate unlocked successfully!')
            }
          } catch (e) {
            // ignore
          }
        }
        fetchCertificates()
      })()
    })

    return {
      certificates,
      viewingCert,
      loading,
      earnedCount,
      formatDate,
      formatAmount,
      fetchCertificates,
      openViewModal,
      closeViewModal,
      printCertificate,
      downloadCertificate,
      requiresAdCertificate,
      hasAdCertificate,
      adCertificatePrice,
      purchasingAdCert,
      purchaseAdCertificate
    }
  }
}
</script>

<style scoped>
.tw-animate-modal-in {
  animation: modalIn 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes modalIn {
  from { opacity: 0; transform: scale(0.9) translateY(20px); }
  to { opacity: 1; transform: scale(1) translateY(0); }
}

.font-serif {
  font-family: 'Playfair Display', serif;
}

/* Custom line clamp if not supported */
.tw-line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
