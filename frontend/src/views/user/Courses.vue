<template>
  <DashboardLayout page-title="Video Courses" :dark-theme="true">
    <!-- Page header (visible change) -->
    <div class="tw-mb-4 sm:tw-mb-8 tw-rounded-xl sm:tw-rounded-2xl tw-overflow-hidden tw-border tw-border-indigo-500/30 tw-bg-gradient-to-r tw-from-indigo-600/20 tw-to-purple-600/20 tw-backdrop-blur-md">
      <div class="tw-px-4 sm:tw-px-6 tw-py-4 sm:tw-py-5 tw-flex tw-flex-wrap tw-items-center tw-justify-between tw-gap-3 sm:tw-gap-4">
        <div class="tw-flex tw-items-center tw-gap-3 sm:tw-gap-4">
          <div class="tw-w-10 tw-h-10 sm:tw-w-14 sm:tw-h-14 tw-rounded-lg sm:tw-rounded-xl tw-bg-indigo-500/30 tw-flex tw-items-center tw-justify-center tw-text-xl sm:tw-text-2xl tw-text-indigo-300">
            <i class="fas fa-play-circle"></i>
          </div>
          <div>
            <h1 class="tw-text-white tw-font-bold tw-text-lg sm:tw-text-2xl tw-m-0 tw-tracking-tight">Video Courses</h1>
            <p class="tw-text-white/70 tw-text-[10px] sm:tw-text-sm tw-mt-0.5 sm:tw-mt-1 tw-mb-0">Access courses based on your package</p>
          </div>
        </div>
        <router-link to="/user/packages" class="tw-px-4 tw-py-2 sm:tw-px-5 sm:tw-py-2.5 tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-font-bold tw-rounded-lg sm:tw-rounded-xl tw-shadow-lg tw-transition-all tw-no-underline tw-inline-flex tw-items-center tw-text-xs sm:tw-text-sm">
          <i class="fas fa-box-open tw-mr-1.5 sm:tw-mr-2"></i>Packages
        </router-link>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="tw-flex tw-flex-col tw-items-center tw-justify-center tw-py-20">
      <div class="tw-w-16 tw-h-16 tw-border-4 tw-border-indigo-600/20 tw-border-t-indigo-600 tw-rounded-full tw-animate-spin tw-mb-4"></div>
      <p class="tw-text-white/60 tw-animate-pulse">Fetching your courses catalog...</p>
    </div>

    <!-- API Error State -->
    <div v-else-if="loadError" class="tw-flex tw-justify-center tw-p-8">
      <div class="tw-bg-red-500/10 tw-border-2 tw-border-red-500/30 tw-rounded-2xl tw-p-12 tw-max-w-xl tw-text-center tw-backdrop-blur-md">
        <div class="tw-w-20 tw-h-20 tw-bg-red-500/20 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-6">
          <i class="fas fa-exclamation-triangle tw-text-4xl tw-text-red-500"></i>
        </div>
        <h4 class="tw-text-white tw-font-bold tw-text-2xl tw-mb-4">Could Not Load Courses</h4>
        <p class="tw-text-white/90 tw-mb-6">{{ loadError }}</p>
        <button type="button" class="tw-px-8 tw-py-3 tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-font-bold tw-rounded-xl tw-shadow-lg tw-transition-all tw-border-0 tw-cursor-pointer" @click="fetchCourses">
          <i class="fas fa-redo tw-mr-2"></i> Try Again
        </button>
      </div>
    </div>

    <template v-else>
      <!-- Ad Certificate Gate -->
      <div v-if="hasActiveCoursePlan && requiresAdCertificate && !hasAdCertificate" class="tw-mb-6 tw-rounded-xl sm:tw-rounded-2xl tw-p-4 sm:tw-p-6 tw-backdrop-blur-md tw-border tw-border-amber-500/30 tw-bg-amber-500/10">
        <div class="tw-flex tw-items-start tw-gap-4 sm:tw-gap-6 tw-flex-wrap">
          <div class="tw-w-10 tw-h-10 sm:tw-w-14 sm:tw-h-14 tw-rounded-lg sm:tw-rounded-xl tw-bg-amber-500/20 tw-text-amber-400 tw-flex tw-items-center tw-justify-center tw-text-xl sm:tw-text-2xl tw-shrink-0">
            <i class="fas fa-lock"></i>
          </div>
          <div class="tw-flex-1">
            <h5 class="tw-text-white tw-font-bold tw-text-base sm:tw-text-xl tw-mb-1 sm:tw-mb-2">Courses Locked</h5>
            <p class="tw-text-white/90 tw-m-0 tw-text-xs sm:tw-text-base tw-leading-relaxed">
              Unlock courses by purchasing <strong>Ad Certificate</strong>.
            </p>
            <div class="tw-mt-3 tw-flex tw-flex-col sm:tw-flex-row tw-gap-2">
              <button
                type="button"
                @click="purchaseAdCertificate"
                :disabled="purchasingAdCert"
                class="tw-px-4 tw-py-2 sm:tw-px-6 sm:tw-py-3 tw-bg-amber-500 hover:tw-bg-amber-600 tw-text-white tw-font-bold tw-rounded-lg sm:tw-rounded-xl tw-shadow-lg tw-transition-all tw-border-0 tw-cursor-pointer disabled:tw-opacity-60 disabled:tw-cursor-not-allowed tw-text-xs sm:tw-text-base"
              >
                <i class="fas fa-credit-card tw-mr-1.5 sm:tw-mr-2"></i>
                Buy Certificate – {{ currencySymbol }}{{ formatAmount(adCertificatePrice) }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Package Info Banner -->
      <div v-if="!planIsActive" class="tw-mb-6 tw-rounded-xl sm:tw-rounded-2xl tw-p-4 sm:tw-p-6 tw-backdrop-blur-md tw-border tw-border-white/10 tw-bg-amber-500/10 tw-border-amber-500/30">
        <div class="tw-flex tw-items-center tw-gap-4 sm:tw-gap-6">
          <div class="tw-w-10 tw-h-10 sm:tw-w-14 sm:tw-h-14 tw-rounded-lg sm:tw-rounded-xl tw-bg-amber-500/20 tw-text-amber-500 tw-flex tw-items-center tw-justify-center tw-text-xl sm:tw-text-2xl tw-shrink-0">
            <i class="fas fa-info-circle"></i>
          </div>
          <div>
            <h5 class="tw-text-white tw-font-bold tw-text-base sm:tw-text-xl tw-mb-1 sm:tw-mb-2">No Active Package</h5>
            <p class="tw-text-white/90 tw-m-0 tw-text-xs sm:tw-text-base">
              Unlock courses with a package. 
              <router-link to="/user/packages" class="tw-text-indigo-300 hover:tw-text-white tw-font-bold tw-no-underline">View Plans →</router-link>
            </p>
          </div>
        </div>
      </div>

      <div v-else class="tw-mb-6 tw-rounded-xl sm:tw-rounded-2xl tw-p-4 sm:tw-p-6 tw-backdrop-blur-md tw-border-l-4 tw-border-l-emerald-400 tw-border tw-border-white/10 tw-bg-emerald-500/10 tw-shadow-lg">
        <div class="tw-flex tw-items-center tw-gap-4 sm:tw-gap-6">
          <div class="tw-w-10 tw-h-10 sm:tw-w-14 sm:tw-h-14 tw-rounded-lg sm:tw-rounded-xl tw-bg-emerald-500/30 tw-text-emerald-400 tw-flex tw-items-center tw-justify-center tw-text-xl sm:tw-text-2xl tw-shrink-0">
            <i class="fas fa-crown"></i>
          </div>
          <div class="tw-flex-1">
            <h5 class="tw-text-white tw-font-bold tw-text-base sm:tw-text-xl tw-mb-1 sm:tw-mb-2">{{ currentPackageName }}</h5>
            <p class="tw-text-white/90 tw-m-0 tw-text-xs sm:tw-text-base">
              <strong>{{ unlockedCount }}</strong> / {{ totalCourses }} courses unlocked.
            </p>
          </div>
        </div>
      </div>

      <!-- Hide course list until Ad Certificate purchased -->
      <template v-if="!requiresAdCertificate || hasAdCertificate">
      <!-- Debug Info (temporary) -->
      <!-- <div v-if="!loading && !loadError" class="tw-mb-4 tw-p-4 tw-rounded-lg tw-bg-white/10 tw-text-white/80 tw-text-sm">
        <strong>Debug:</strong> courses.length = {{ courses.length }}, totalCourses = {{ totalCourses }}, hasActiveCoursePlan = {{ hasActiveCoursePlan }}<br>
        <span v-if="courses.length > 0">First course ID: {{ courses[0]?.id }}, Title: {{ courses[0]?.title }}</span>
      </div> -->

      <!-- Courses Grid (package-based: unlocked first, then locked) -->
      <div v-if="courses && courses.length > 0" class="tw-w-full tw-relative tw-z-10">
        <!-- Section: Courses in your package (unlocked) -->
        <div v-if="unlockedCourses.length > 0" class="tw-mb-10">
          <div class="tw-mb-4 sm:tw-mb-6 tw-flex tw-items-center tw-justify-between tw-flex-wrap tw-gap-2 tw-pb-2 sm:tw-pb-4 tw-border-b-2 tw-border-emerald-500/40">
            <h3 class="tw-text-white tw-font-bold tw-text-lg sm:tw-text-2xl tw-flex tw-items-center tw-m-0">
              <span class="tw-w-8 tw-h-8 sm:tw-w-10 sm:tw-h-10 tw-rounded-lg tw-bg-emerald-500/30 tw-flex tw-items-center tw-justify-center tw-mr-2 sm:tw-mr-3 tw-text-emerald-400 tw-text-xs sm:tw-text-base"><i class="fas fa-unlock"></i></span>
              Unlocked Courses
              <span class="tw-ml-2 sm:tw-ml-3 tw-bg-emerald-600 tw-text-white tw-px-2 tw-py-0.5 sm:tw-px-3 sm:tw-py-1 tw-rounded-full tw-text-[10px] sm:tw-text-sm tw-font-bold">{{ unlockedCourses.length }}</span>
            </h3>
          </div>
          <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 lg:tw-grid-cols-3 tw-gap-4 sm:tw-gap-8">
            <template v-for="(course, idx) in unlockedCourses" :key="`unlocked-${course?.id ?? course?.course_id ?? idx}`">
              <div
                class="tw-bg-white/95 tw-backdrop-blur-xl tw-rounded-xl sm:tw-rounded-2xl tw-overflow-hidden tw-shadow-xl hover:tw-shadow-2xl hover:-tw-translate-y-1 tw-transition-all tw-duration-300 tw-flex tw-flex-col tw-border tw-border-white/20 tw-cursor-pointer"
                role="button"
                tabindex="0"
                @click="openCourse(course)"
              >
                <div class="tw-relative tw-h-40 sm:tw-h-56 tw-bg-gradient-to-br tw-from-indigo-400 tw-to-purple-500 tw-overflow-hidden group">
                  <img :src="course.thumbnail || 'https://picsum.photos/400/250?random=' + (course.id || idx)" :alt="course.title || course.name" class="tw-w-full tw-h-full tw-object-cover tw-transition-transform tw-duration-500 group-hover:tw-scale-110" @error="handleImageError">
                  <div class="tw-absolute tw-inset-0 tw-bg-black/60 tw-flex tw-items-center tw-justify-center tw-opacity-0 group-hover:tw-opacity-100 tw-transition-opacity tw-duration-300">
                    <span class="tw-px-4 tw-py-2 tw-bg-indigo-600 tw-text-white tw-font-bold tw-rounded-lg tw-text-xs">Watch Now</span>
                  </div>
                  <div class="tw-absolute tw-top-2 tw-right-2 tw-z-10"><span class="tw-px-2 tw-py-1 tw-rounded-md tw-text-[8px] sm:tw-text-[10px] tw-font-bold tw-text-white tw-shadow-md tw-backdrop-blur-sm" :class="getCategoryBadgeClass(course.category)">{{ course.category || 'General' }}</span></div>
                </div>
                <div class="tw-p-3 sm:tw-p-5 tw-flex-1 tw-flex tw-flex-col">
                  <h4 class="tw-text-slate-900 tw-font-bold tw-text-sm sm:tw-text-lg tw-mb-1.5 tw-leading-tight tw-line-clamp-2 tw-min-h-[2.5rem]">{{ course.title || course.name || 'Course' }}</h4>
                  <div class="tw-flex tw-justify-between tw-items-center tw-pt-3 tw-border-t tw-border-slate-100">
                    <div class="tw-flex tw-items-center tw-gap-1.5 tw-text-slate-500 tw-text-[10px] sm:tw-text-xs"><i class="fas fa-clock tw-text-indigo-600"></i>{{ course.duration || '0h' }}</div>
                    <span v-if="course.is_completed" class="tw-bg-emerald-100 tw-text-emerald-700 tw-px-2 tw-py-0.5 tw-rounded-md tw-text-[9px] sm:tw-text-xs tw-font-bold">Done</span>
                    <span v-else class="tw-text-indigo-600 tw-font-extrabold tw-text-sm sm:tw-text-base">{{ currencySymbol }}{{ formatAmount(course.price) }}</span>
                  </div>
                </div>
              </div>
            </template>
          </div>
        </div>

        <!-- Section: Upgrade to unlock more (locked) -->
        <div v-if="lockedCourses.length > 0" class="tw-mb-10">
          <div class="tw-mb-6 tw-flex tw-items-center tw-justify-between tw-flex-wrap tw-gap-3 tw-pb-4 tw-border-b-2 tw-border-amber-500/40">
            <h3 class="tw-text-white tw-font-bold tw-text-2xl tw-flex tw-items-center tw-m-0">
              <span class="tw-w-10 tw-h-10 tw-rounded-lg tw-bg-amber-500/30 tw-flex tw-items-center tw-justify-center tw-mr-3 tw-text-amber-400"><i class="fas fa-lock"></i></span>
              Unlock more courses
              <span class="tw-ml-3 tw-bg-amber-600 tw-text-white tw-px-3 tw-py-1 tw-rounded-full tw-text-sm tw-font-bold">{{ lockedCourses.length }}</span>
            </h3>
            <router-link to="/user/packages" class="tw-px-5 tw-py-2.5 tw-bg-amber-500 hover:tw-bg-amber-600 tw-text-white tw-font-bold tw-rounded-xl tw-shadow-lg tw-transition-all tw-no-underline tw-inline-flex tw-items-center">
              <i class="fas fa-arrow-up tw-mr-2"></i>Upgrade Package
            </router-link>
          </div>
          <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-3 tw-gap-8">
            <template v-for="(course, idx) in lockedCourses" :key="`locked-${course?.id ?? course?.course_id ?? idx}`">
              <div
                class="tw-bg-white/95 tw-backdrop-blur-xl tw-rounded-2xl tw-overflow-hidden tw-shadow-xl hover:tw-shadow-2xl hover:-tw-translate-y-2 tw-transition-all tw-duration-300 tw-flex tw-flex-col tw-border tw-border-amber-200 tw-cursor-pointer"
                role="button"
                tabindex="0"
                @click="handleLockedClick(course)"
                @keydown.enter.prevent="handleLockedClick(course)"
                @keydown.space.prevent="handleLockedClick(course)"
              >
                <div class="tw-relative tw-h-64 tw-bg-gradient-to-br tw-from-slate-400 tw-to-slate-500 tw-overflow-hidden group">
                  <img :src="course.thumbnail || 'https://picsum.photos/400/250?random=' + (course.id || idx)" :alt="course.title || course.name" class="tw-w-full tw-h-full tw-object-cover tw-transition-transform tw-duration-500 group-hover:tw-scale-110" @error="handleImageError">
                  <div class="tw-absolute tw-inset-0 tw-bg-black/50 tw-flex tw-items-center tw-justify-center tw-transition-opacity group-hover:tw-bg-black/60">
                    <div class="tw-px-6 tw-py-3 tw-bg-amber-500 hover:tw-bg-amber-600 tw-text-white tw-font-bold tw-rounded-xl tw-shadow-lg tw-transition-all hover:tw-scale-105 tw-no-underline tw-flex tw-items-center">
                      <i class="fas fa-lock tw-mr-2"></i>{{ (planIsActive && !hasAdCertificate) ? 'Unlock Courses' : 'Upgrade to Unlock' }}
                    </div>
                  </div>
                  <div class="tw-absolute tw-top-4 tw-right-4 tw-z-10"><span class="tw-px-4 tw-py-2 tw-rounded-full tw-text-xs tw-font-bold tw-text-white tw-shadow-md tw-backdrop-blur-sm" :class="getCategoryBadgeClass(course.category)">{{ course.category || 'General' }}</span></div>
                  <div class="tw-absolute tw-top-4 tw-left-4 tw-bg-amber-500/90 tw-text-white tw-px-3 tw-py-1.5 tw-rounded-full tw-text-xs tw-font-bold tw-shadow-md tw-backdrop-blur-sm tw-z-10"><i class="fas fa-lock tw-mr-1"></i>Locked</div>
                </div>
                <div class="tw-p-6 tw-flex-1 tw-flex tw-flex-col">
                  <h4 class="tw-text-slate-900 tw-font-bold tw-text-xl tw-mb-3 tw-leading-tight tw-line-clamp-2 tw-min-h-[3.5rem]">{{ course.title || course.name || 'Course' }}</h4>
                  <p class="tw-text-slate-500 tw-text-sm tw-leading-relaxed tw-mb-4 tw-flex-1 tw-line-clamp-3 tw-min-h-[4rem]">{{ course.description || 'No description available.' }}</p>
                  <div class="tw-flex tw-gap-4 tw-p-3 tw-bg-slate-50 tw-rounded-xl tw-mb-4">
                    <div class="tw-flex tw-items-center tw-gap-2 tw-text-slate-600 tw-text-sm tw-font-bold"><i class="fas fa-clock tw-text-indigo-600"></i><span>{{ course.duration || '0 hours' }}</span></div>
                    <div class="tw-flex tw-items-center tw-gap-2 tw-text-slate-600 tw-text-sm tw-font-bold"><i class="fas fa-users tw-text-indigo-600"></i><span>{{ course.students_count ?? 0 }} Students</span></div>
                  </div>
                  <div class="tw-flex tw-justify-between tw-items-center tw-pt-4 tw-border-t tw-border-slate-100">
                    <span v-if="course.is_free" class="tw-bg-emerald-100 tw-text-emerald-700 tw-px-4 tw-py-2 tw-rounded-full tw-font-bold tw-text-sm">Free</span>
                    <span v-else class="tw-text-indigo-600 tw-font-extrabold tw-text-xl">{{ currencySymbol }}{{ formatAmount(course.price) }}</span>
                    <span class="tw-bg-amber-100 tw-text-amber-700 tw-px-3 tw-py-1.5 tw-rounded-full tw-text-xs tw-font-bold"><i class="fas fa-lock tw-mr-1"></i>Upgrade package</span>
                  </div>
                </div>
              </div>
            </template>
          </div>
        </div>
      </div>

      <!-- No courses but have plan: show retry + debug -->

      <!-- No Courses Message -->
      <div v-else class="tw-flex tw-justify-center tw-py-12">
        <div class="tw-bg-amber-500/10 tw-border tw-border-amber-500/30 tw-rounded-2xl tw-p-10 tw-text-center tw-backdrop-blur-md tw-max-w-lg">
          <div class="tw-w-16 tw-h-16 tw-bg-amber-500/20 tw-text-amber-500 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-6">
            <i class="fas fa-graduation-cap tw-text-3xl"></i>
          </div>
          <h3 class="tw-text-white tw-font-bold tw-text-2xl tw-mb-4">No Courses Available</h3>
          <p class="tw-text-white/80 tw-mb-6">No courses found in the catalog. This could mean:</p>
          <ul class="tw-text-left tw-text-white/80 tw-space-y-3 tw-mb-8 tw-pl-4">
            <li class="tw-flex tw-items-center"><i class="fas fa-circle tw-text-[6px] tw-mr-3 tw-text-amber-500"></i>No courses have been added yet</li>
            <li class="tw-flex tw-items-center"><i class="fas fa-circle tw-text-[6px] tw-mr-3 tw-text-amber-500"></i>You need to purchase a course package first</li>
            <li class="tw-flex tw-items-center"><i class="fas fa-circle tw-text-[6px] tw-mr-3 tw-text-amber-500"></i>Courses are being updated</li>
          </ul>
          <div class="tw-flex tw-flex-col sm:tw-flex-row tw-gap-4 tw-justify-center">
            <router-link to="/user/packages" class="tw-px-6 tw-py-2.5 tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-font-bold tw-rounded-xl tw-shadow-lg tw-transition-all tw-no-underline tw-inline-flex tw-items-center tw-justify-center">
              <i class="fas fa-box tw-mr-2"></i>View Packages
            </router-link>
            <button type="button" class="tw-px-6 tw-py-2.5 tw-bg-white/10 hover:tw-bg-white/20 tw-text-white tw-font-bold tw-rounded-xl tw-backdrop-blur-sm tw-transition-all tw-border-0 tw-cursor-pointer tw-inline-flex tw-items-center tw-justify-center" @click="fetchCourses">
              <i class="fas fa-redo tw-mr-2"></i>Refresh
            </button>
          </div>
        </div>
      </div>

      <div class="tw-text-center tw-py-6 tw-text-white/30 tw-text-xs">
        Courses loaded from your plan • Build {{ buildId }}
      </div>
      </template>
    </template>
  </DashboardLayout>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import DashboardLayout from '../../components/DashboardLayout.vue'
import api from '../../services/api'
import { openPaymentInNewTab } from '../../services/paymentWindow'

export default {
  name: 'Courses',
  components: {
    DashboardLayout
  },
  setup() {
    const route = useRoute()
    const router = useRouter()
    const courses = ref([])
    const currencySymbol = ref('₹')
    const hasActiveCoursePlan = ref(false)
    const planIsActive = ref(false)
    const requiresAdCertificate = ref(true)
    const hasAdCertificate = ref(false)
    const adCertificatePrice = ref(1250)
    const purchasingAdCert = ref(false)
    const totalCourses = ref(0)
    const unlockedCount = ref(0)
    const currentPackageName = ref('')
    const debugPayloadKeys = ref('')
    const loading = ref(true)
    const loadError = ref('')
    const termsAccepted = ref(true) // Default to true since it seems to be expected but UI is missing

    // Package-based: split into unlocked (in your package) vs locked (upgrade to unlock)
    const unlockedCourses = computed(() => (courses.value || []).filter(c => c && c.unlocked !== false && c.unlocked !== 0))
    const lockedCourses = computed(() => (courses.value || []).filter(c => c && (c.unlocked === false || c.unlocked === 0)))

    const formatAmount = (amount) => {
      if (!amount && amount !== 0) return '0.00'
      return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }

    const getCategoryBadgeClass = (category) => {
      const categoryMap = {
        'Video Editing': 'tw-bg-red-500',
        'Graphic Design': 'tw-bg-indigo-500',
        'Digital Marketing': 'tw-bg-emerald-500',
        'Content Writing': 'tw-bg-sky-500',
        'UI/UX Design': 'tw-bg-amber-500',
        'Web Development': 'tw-bg-slate-800'
      }
      return categoryMap[category] || 'tw-bg-slate-500'
    }

    const getCourseId = (course) => course?.id || course?.course_id

    const openCourse = (course) => {
      const id = getCourseId(course)
      if (!id) return
      router.push(`/user/courses/${id}`)
    }

    const handleLockedClick = (course) => {
      if (planIsActive.value && requiresAdCertificate.value && !hasAdCertificate.value) {
        if (window.iziToast) {
          window.iziToast.info({
            title: 'Access Required',
            message: 'Please purchase Ad Certificate to watch courses.',
            position: 'topRight'
          })
        } else if (window.notify) {
          window.notify('info', 'Please purchase Ad Certificate to watch courses.')
        }
        return
      }
      router.push('/user/packages')
    }

    const openPackages = () => {
      router.push('/user/packages')
    }

    const handleImageError = (event) => {
      event.target.src = 'https://picsum.photos/400/250?random=' + Math.random()
    }

    // Find first non-empty array in response (any array of objects = courses list)
    function findCoursesArray(obj, depth = 0) {
      if (depth > 4) return null
      if (!obj || typeof obj !== 'object') return null
      if (Array.isArray(obj) && obj.length > 0) {
        const first = obj[0]
        if (first && typeof first === 'object') return obj
        return null
      }
      const keys = Object.keys(obj)
      for (let i = 0; i < keys.length; i++) {
        const found = findCoursesArray(obj[keys[i]], depth + 1)
        if (found) return found
      }
      return null
    }

    const fetchCourses = async () => {
      loading.value = true
      loadError.value = ''
      try {
        const response = await api.get('/courses')
        const res = response.data

        if (res.status !== 'success') {
          console.warn('API returned non-success status:', res.status)
          courses.value = []
          loadError.value = res.message?.[0] || 'Failed to load courses'
          return
        }

        // Payload: API returns { data: { courses_list, data, list, courses, ... }, courses_list } (from database)
        const payload = res.data && typeof res.data === 'object' ? res.data : {}
        let list = []

        // Prefer any array from API (dynamic from DB) – use first found, even if empty
        if (Array.isArray(res.courses_list)) {
          list = res.courses_list
        } else if (Array.isArray(res.data) && res.data.length > 0 && res.data[0] && typeof res.data[0] === 'object') {
          list = res.data
        } else if (Array.isArray(payload.courses_list)) {
          list = payload.courses_list
        } else if (Array.isArray(payload.data)) {
          list = payload.data
        } else if (Array.isArray(payload.list)) {
          list = payload.list
        } else if (Array.isArray(payload.courses)) {
          list = payload.courses
        }
        if (list.length === 0) {
          const found = findCoursesArray(res)
          if (found && Array.isArray(found)) {
            list = found
          }
        }

        // Process courses - ensure all courses are included
        if (!Array.isArray(list)) {
          courses.value = []
        } else {
          courses.value = list.map((c, index) => {
            if (!c || (typeof c !== 'object')) return null
            
            // Use index as fallback ID if both id and course_id are missing
            const courseId = c.id || c.course_id || `temp-${index + 1}`
            
            // Build course object with all available fields
            const processedCourse = {
              ...c,
              id: courseId,
              course_id: courseId,
              unlocked: c.unlocked !== false && c.unlocked !== 0 && c.unlocked !== '0' && c.unlocked !== false,
              title: c.title || c.name || `Course ${courseId}`,
              name: c.name || c.title || `Course ${courseId}`,
              description: c.description || 'No description available for this course.',
              thumbnail: c.thumbnail || c.image || `https://picsum.photos/400/250?random=${courseId}`,
              duration: c.duration || '0 hours',
              students_count: c.students_count || 0,
              price: c.price || 0,
              is_free: (c.is_free === true || c.is_free === 1 || (c.price === 0 || c.price === '0')),
              category: c.category || 'General',
              is_completed: c.is_completed === true || c.is_completed === 1
            }
            
            return processedCourse
          }).filter(c => c !== null && c !== undefined)
        }
        
        debugPayloadKeys.value = Object.keys(payload).join(', ')

        // Access flags
        requiresAdCertificate.value = payload.requires_ad_certificate !== false && payload.requires_ad_certificate !== 0
        hasAdCertificate.value = payload.has_ad_certificate === true || payload.has_ad_certificate === 1
        adCertificatePrice.value = payload.ad_certificate_price != null ? Number(payload.ad_certificate_price) : 1250
        planIsActive.value = payload.current_plan_is_active === true || payload.current_plan_is_active === 1
        const accessEnabled = payload.course_access_enabled === true || payload.course_access_enabled === 1

        // Set package info (plan active = true even if locked by Ad Certificate)
        hasActiveCoursePlan.value = !!planIsActive.value
        totalCourses.value = payload.total_courses != null ? Number(payload.total_courses) : courses.value.length
        unlockedCount.value = payload.unlocked_count != null ? Number(payload.unlocked_count) : courses.value.filter(c => c.unlocked !== false).length
        currencySymbol.value = payload.currency_symbol || '₹'
        currentPackageName.value = payload.current_plan_name || (payload.user_course_plan_level != null && payload.user_course_plan_level > 0 ? 'Level ' + payload.user_course_plan_level : 'No Package')

      } catch (error) {
        console.error('Error loading courses:', error)
        courses.value = []
        if (error.response?.status === 401) {
          loadError.value = 'Please log in to view courses.'
        } else if (error.response?.data?.message) {
          const msg = error.response.data.message
          loadError.value = Array.isArray(msg) ? msg[0] : (typeof msg === 'object' ? JSON.stringify(msg) : msg)
        } else {
          loadError.value = error.message || 'Failed to load courses. Please try again.'
        }
      } finally {
        loading.value = false
      }
    }

    const purchaseAdCertificate = async () => {
      purchasingAdCert.value = true
      try {
        if (!termsAccepted.value) {
          if (window.notify) window.notify('error', 'Please accept Terms & Privacy Policy before payment.')
          return
        }
        const amount = adCertificatePrice.value
        const redirectUrl = `/user/payment-redirect?flow=ad_certificate_course&amount=${amount}&plan_name=Ad%20Certificate&back=${encodeURIComponent('/user/courses')}`
        const w = window.open(redirectUrl, '_blank')
        if (!w) {
          router.push(redirectUrl)
        } else if (window.notify) {
          window.notify('info', 'Payment tab opened. Complete payment to unlock courses.')
        }
      } finally {
        purchasingAdCert.value = false
      }
    }

    onMounted(() => {
      ;(async () => {
        const trx = route.query.watchpay_trx
        const isAdCert = route.query.ad_certificate === '1' || route.query.ad_certificate === 1
        if (trx && isAdCert) {
          try {
            const confirmRes = await api.post('/ad-certificate/payment/confirm', { trx })
            if (confirmRes.data?.status === 'success') {
              if (window.notify) window.notify('success', 'Ad Certificate purchased successfully. Courses unlocked!')
              setTimeout(() => router.replace('/dashboard'), 700)
            }
          } catch (_) {
            // ignore
          }
        }
        fetchCourses()
      })()
    })

    const buildId = typeof __BUILD_ID__ !== 'undefined' ? __BUILD_ID__ : 'dev'
    return {
      buildId,
      courses,
      unlockedCourses,
      lockedCourses,
      currencySymbol,
      hasActiveCoursePlan,
      planIsActive,
      requiresAdCertificate,
      hasAdCertificate,
      adCertificatePrice,
      purchasingAdCert,
      totalCourses,
      unlockedCount,
      currentPackageName,
      debugPayloadKeys,
      loading,
      loadError,
      formatAmount,
      getCategoryBadgeClass,
      openCourse,
      handleLockedClick,
      openPackages,
      handleImageError,
      fetchCourses,
      purchaseAdCertificate,
      termsAccepted
    }
  }
}
</script>

<style scoped>
@media (max-width: 640px) {
  /* Header adjustments */
  .tw-px-4.sm\:tw-px-6 { padding: 0.85rem 1rem !important; }
  .tw-py-4.sm\:tw-py-5 { padding-top: 1rem !important; padding-bottom: 1rem !important; }
  .tw-w-10.tw-h-10 { width: 2.5rem !important; height: 2.5rem !important; border-radius: 0.75rem !important; font-size: 1.15rem !important; }
  h1.tw-text-lg { font-size: 1.15rem !important; }
  p.tw-text-\[10px\] { font-size: 8px !important; }
  .tw-px-4.tw-py-2 { padding: 0.5rem 0.85rem !important; font-size: 10px !important; border-radius: 0.65rem !important; }
  .tw-mb-4.sm\:tw-mb-8 { margin-bottom: 1rem !important; border-radius: 1rem !important; }

  /* Alert Banners */
  .tw-p-4.sm\:tw-p-6 { padding: 1rem !important; border-radius: 1rem !important; margin-bottom: 1.25rem !important; }
  .tw-w-10.tw-h-10.sm\:tw-w-14 { width: 2.25rem !important; height: 2.25rem !important; font-size: 1rem !important; border-radius: 0.65rem !important; }
  h5.tw-text-base { font-size: 0.95rem !important; margin-bottom: 0.25rem !important; }
  p.tw-text-xs { font-size: 0.75rem !important; line-height: 1.4 !important; }
  .tw-mt-3.tw-flex { margin-top: 0.75rem !important; gap: 0.5rem !important; }
  .tw-px-4.tw-py-2.sm\:tw-px-6 { padding: 0.65rem 0.85rem !important; font-size: 10px !important; border-radius: 0.65rem !important; }

  /* Section Titles */
  .tw-mb-10 { margin-bottom: 1.5rem !important; }
  .tw-mb-4.sm\:tw-mb-6 { margin-bottom: 1rem !important; padding-bottom: 0.65rem !important; }
  h3.tw-text-lg { font-size: 1.1rem !important; }
  .tw-w-8.tw-h-8 { width: 1.75rem !important; height: 1.75rem !important; font-size: 0.75rem !important; border-radius: 0.5rem !important; }
  .tw-text-\[10px\] { font-size: 8px !important; padding: 0.15rem 0.5rem !important; }

  /* Course Card Mobile (Compact) */
  .tw-h-40 { height: 7.5rem !important; }
  .tw-p-3 { padding: 0.75rem !important; }
  h4.tw-text-sm { font-size: 0.85rem !important; margin-bottom: 0.5rem !important; min-height: auto !important; }
  .tw-pt-3 { padding-top: 0.5rem !important; }
  .tw-text-\[10px\] { font-size: 9px !important; }
  .tw-text-sm { font-size: 0.85rem !important; }
  .tw-rounded-xl { border-radius: 0.85rem !important; }
  .tw-gap-8 { gap: 1rem !important; }

  /* Locked Course Adjustments */
  .tw-h-64 { height: 10rem !important; }
  .tw-px-6.tw-py-3 { padding: 0.5rem 1rem !important; font-size: 10px !important; border-radius: 0.75rem !important; }
  .tw-text-xl { font-size: 1rem !important; min-height: auto !important; margin-bottom: 0.35rem !important; }
  .tw-text-sm.tw-leading-relaxed { font-size: 0.75rem !important; line-height: 1.3 !important; min-height: auto !important; margin-bottom: 0.75rem !important; }
  .tw-p-3.tw-bg-slate-50 { padding: 0.5rem !important; margin-bottom: 0.75rem !important; border-radius: 0.65rem !important; gap: 0.5rem !important; }
  .tw-gap-2.tw-text-sm { gap: 0.35rem !important; font-size: 10px !important; }
  .tw-pt-4 { padding-top: 0.65rem !important; }
  .tw-px-4.tw-py-2 { padding: 0.35rem 0.65rem !important; font-size: 10px !important; }
}
</style>
