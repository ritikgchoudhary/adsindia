<template>
  <DashboardLayout page-title="Course Details" :dark-theme="true">
    <!-- Loading -->
    <div v-if="loading" class="tw-flex tw-justify-center tw-py-20">
      <div class="tw-w-10 tw-h-10 tw-border-4 tw-border-indigo-500 tw-border-t-transparent tw-rounded-full tw-animate-spin"></div>
    </div>

    <!-- Course Found -->
    <div v-else-if="course" class="tw-animate-fade-in-up">
      <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-3 tw-gap-8">

        <!-- Left: Video + Info -->
        <div class="lg:tw-col-span-2">
          <!-- Video Player -->
          <div class="tw-bg-white/95 tw-backdrop-blur-xl tw-rounded-2xl tw-shadow-xl tw-overflow-hidden tw-border-0">
            <div class="tw-relative tw-w-full tw-pt-[56.25%] tw-bg-black">
              <iframe
                v-if="isYoutubeOrVimeoUrl(course.video_url)"
                :src="embedVideoUrl(course.video_url)"
                class="tw-absolute tw-top-0 tw-left-0 tw-w-full tw-h-full tw-border-0"
                allowfullscreen
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
              ></iframe>
              <video
                v-else-if="course.video_url"
                ref="videoPlayer"
                :src="course.video_url"
                controls
                class="tw-absolute tw-top-0 tw-left-0 tw-w-full tw-h-full tw-border-0"
                @error="handleVideoError"
                @loadeddata="handleVideoLoaded"
                @ended="onVideoEnded"
              >
                Your browser does not support the video tag.
              </video>
              <div v-if="course && course.video_url && !isYoutubeOrVimeoUrl(course.video_url) && course.is_completed" class="tw-absolute tw-bottom-0 tw-left-0 tw-right-0 tw-py-2 tw-px-3 tw-bg-emerald-500/90 tw-text-white tw-text-sm tw-font-bold tw-flex tw-items-center tw-justify-center tw-gap-2">
                <i class="fas fa-check-circle"></i> Video watched – Course completed
              </div>
              <div v-else class="tw-absolute tw-inset-0 tw-flex tw-flex-col tw-items-center tw-justify-center tw-text-white/50 tw-gap-3">
                <i class="fas fa-play-circle tw-text-5xl tw-opacity-40"></i>
                <p class="tw-m-0 tw-font-medium tw-text-base">Video not available yet</p>
              </div>
            </div>
            <!-- Mark as watched for YouTube/Vimeo (embed) – we can't detect end inside iframe -->
            <div v-if="course && isYoutubeOrVimeoUrl(course.video_url) && !course.is_completed" class="tw-mt-4 tw-p-4 tw-bg-indigo-50 tw-rounded-xl tw-border tw-border-indigo-100">
              <p class="tw-text-slate-600 tw-text-sm tw-mb-3 tw-m-0">Video embed (YouTube/Vimeo) – after watching, mark as complete to get your certificate.</p>
              <button
                type="button"
                :disabled="markingComplete"
                class="tw-px-5 tw-py-2.5 tw-bg-indigo-600 hover:tw-bg-indigo-700 disabled:tw-opacity-60 tw-text-white tw-font-bold tw-rounded-xl tw-border-0 tw-cursor-pointer tw-transition-all"
                @click="markCourseComplete"
              >
                <span><i class="fas fa-check-circle tw-mr-2"></i>I've watched the video – Mark as complete</span>
              </button>
            </div>
            <div v-else-if="course && course.is_completed" class="tw-mt-4 tw-p-4 tw-bg-emerald-50 tw-rounded-xl tw-border tw-border-emerald-200 tw-flex tw-items-center tw-gap-2">
              <i class="fas fa-check-circle tw-text-emerald-600 tw-text-xl"></i>
              <span class="tw-text-emerald-800 tw-font-bold">Video watched – Course completed</span>
            </div>
          </div>

          <!-- Course Info -->
          <div class="tw-mt-6 tw-bg-white/95 tw-backdrop-blur-xl tw-rounded-2xl tw-shadow-lg tw-border tw-border-slate-200/70 tw-overflow-hidden">
            <div class="tw-p-6">
              <div class="tw-mb-4">
                <div class="tw-flex tw-flex-col md:tw-flex-row md:tw-items-start md:tw-justify-between tw-gap-4">
                  <div>
                    <h3 class="tw-text-slate-900 tw-font-extrabold tw-text-2xl tw-mb-2 tw-leading-tight">{{ course.title }}</h3>
                    <div class="tw-flex tw-flex-wrap tw-gap-2">
                      <span class="tw-inline-flex tw-items-center tw-px-3 tw-py-1 tw-rounded-lg tw-text-xs tw-font-bold tw-text-white" :class="getCategoryBadgeClass(course.category)">
                        {{ course.category }}
                      </span>
                      <span v-if="course.is_free" class="tw-inline-flex tw-items-center tw-px-3 tw-py-1 tw-rounded-lg tw-text-xs tw-font-bold tw-text-white tw-bg-gradient-to-r tw-from-emerald-500 tw-to-emerald-400">
                        <i class="fas fa-check-circle tw-mr-1"></i>Free Access
                      </span>
                    </div>
                  </div>
                </div>
              </div>

              <p class="tw-text-slate-600 tw-text-base tw-leading-relaxed tw-mb-6">{{ course.description }}</p>

              <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 tw-gap-4">
                <div class="tw-flex tw-items-center tw-gap-3 tw-p-3 tw-bg-slate-50 tw-rounded-xl tw-border tw-border-slate-100 hover:tw-bg-indigo-50/50 hover:tw-border-indigo-100 tw-transition-colors">
                  <div class="tw-w-10 tw-h-10 tw-rounded-lg tw-bg-blue-100 tw-text-blue-600 tw-flex tw-items-center tw-justify-center tw-shrink-0">
                    <i class="fas fa-clock"></i>
                  </div>
                  <div>
                    <span class="tw-block tw-text-xs tw-font-bold tw-text-slate-400 tw-uppercase tw-tracking-wide">Duration</span>
                    <span class="tw-block tw-text-slate-900 tw-font-bold tw-text-sm">{{ course.duration || 'N/A' }}</span>
                  </div>
                </div>
                
                <div class="tw-flex tw-items-center tw-gap-3 tw-p-3 tw-bg-slate-50 tw-rounded-xl tw-border tw-border-slate-100 hover:tw-bg-emerald-50/50 hover:tw-border-emerald-100 tw-transition-colors">
                  <div class="tw-w-10 tw-h-10 tw-rounded-lg tw-bg-emerald-100 tw-text-emerald-600 tw-flex tw-items-center tw-justify-center tw-shrink-0">
                    <i class="fas fa-users"></i>
                  </div>
                  <div>
                    <span class="tw-block tw-text-xs tw-font-bold tw-text-slate-400 tw-uppercase tw-tracking-wide">Students</span>
                    <span class="tw-block tw-text-slate-900 tw-font-bold tw-text-sm">{{ course.students_count || 0 }} enrolled</span>
                  </div>
                </div>

                <div class="tw-flex tw-items-center tw-gap-3 tw-p-3 tw-bg-slate-50 tw-rounded-xl tw-border tw-border-slate-100 hover:tw-bg-purple-50/50 hover:tw-border-purple-100 tw-transition-colors" v-if="!course.is_free">
                  <div class="tw-w-10 tw-h-10 tw-rounded-lg tw-bg-purple-100 tw-text-purple-600 tw-flex tw-items-center tw-justify-center tw-shrink-0">
                    <i class="fas fa-tag"></i>
                  </div>
                  <div>
                    <span class="tw-block tw-text-xs tw-font-bold tw-text-slate-400 tw-uppercase tw-tracking-wide">Price</span>
                    <span class="tw-block tw-text-indigo-600 tw-font-extrabold tw-text-lg">{{ currencySymbol }}{{ formatAmount(course.price) }}</span>
                  </div>
                </div>

                  <div class="tw-flex tw-items-center tw-gap-3 tw-p-3 tw-bg-slate-50 tw-rounded-xl tw-border tw-border-slate-100 hover:tw-bg-amber-50/50 hover:tw-border-amber-100 tw-transition-colors">
                  <div class="tw-w-10 tw-h-10 tw-rounded-lg tw-bg-amber-100 tw-text-amber-600 tw-flex tw-items-center tw-justify-center tw-shrink-0">
                    <i class="fas fa-box"></i>
                  </div>
                  <div>
                    <span class="tw-block tw-text-xs tw-font-bold tw-text-slate-400 tw-uppercase tw-tracking-wide">Course Plan</span>
                    <span class="tw-block tw-text-slate-900 tw-font-bold tw-text-sm">
                      <template v-if="course.is_free">Free</template>
                      <template v-else>{{ getRequiredPlanLabel(course.required_course_plan_id) }}</template>
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Sidebar -->
        <div class="lg:tw-col-span-1">
          <!-- Course Info Card -->
          <div class="tw-bg-white/95 tw-backdrop-blur-xl tw-rounded-2xl tw-shadow-lg tw-border tw-border-slate-200/70 tw-overflow-hidden tw-mb-6">
            <div class="tw-bg-slate-50 tw-p-4 tw-border-b tw-border-slate-200 tw-flex tw-items-center tw-gap-2">
              <i class="fas fa-info-circle tw-text-indigo-600"></i>
              <h5 class="tw-text-slate-900 tw-font-bold tw-text-base tw-m-0">Course Information</h5>
            </div>
            <div class="tw-p-5">
              <div class="tw-flex tw-justify-between tw-items-center tw-py-3 tw-border-b tw-border-slate-100 first:tw-pt-0 last:tw-border-0 last:tw-pb-0">
                <span class="tw-text-slate-500 tw-text-sm tw-font-bold">Category</span>
                <span class="tw-inline-flex tw-items-center tw-px-2 tw-py-1 tw-rounded-md tw-text-xs tw-font-bold tw-text-white" :class="getCategoryBadgeClass(course.category)">
                  {{ course.category }}
                </span>
              </div>
              <div class="tw-flex tw-justify-between tw-items-center tw-py-3 tw-border-b tw-border-slate-100 last:tw-border-0 last:tw-pb-0">
                <span class="tw-text-slate-500 tw-text-sm tw-font-bold">Course Plan Required</span>
                <span class="tw-bg-blue-100 tw-text-blue-700 tw-px-2.5 tw-py-1 tw-rounded-md tw-text-xs tw-font-bold">
                  <template v-if="course.is_free">Free</template>
                  <template v-else>{{ getRequiredPlanLabel(course.required_course_plan_id) }}</template>
                </span>
              </div>
              <div class="tw-flex tw-justify-between tw-items-center tw-py-3 tw-border-b tw-border-slate-100 last:tw-border-0 last:tw-pb-0" v-if="!course.is_free">
                <span class="tw-text-slate-500 tw-text-sm tw-font-bold">Price</span>
                <span class="tw-text-indigo-600 tw-font-extrabold tw-text-base">{{ currencySymbol }}{{ formatAmount(course.price) }}</span>
              </div>
              <div class="tw-flex tw-justify-between tw-items-center tw-py-3 tw-border-b tw-border-slate-100 last:tw-border-0 last:tw-pb-0" v-if="course.duration">
                <span class="tw-text-slate-500 tw-text-sm tw-font-bold">Duration</span>
                <span class="tw-text-slate-900 tw-font-bold tw-text-sm">{{ course.duration }}</span>
              </div>
              <div class="tw-flex tw-justify-between tw-items-center tw-py-3 tw-border-b tw-border-slate-100 last:tw-border-0 last:tw-pb-0" v-if="course.students_count">
                <span class="tw-text-slate-500 tw-text-sm tw-font-bold">Students</span>
                <span class="tw-text-slate-900 tw-font-bold tw-text-sm">{{ course.students_count }}</span>
              </div>
            </div>
          </div>

          <!-- Navigation -->
          <div class="tw-bg-white/95 tw-backdrop-blur-xl tw-rounded-2xl tw-shadow-lg tw-border tw-border-slate-200/70 tw-overflow-hidden">
            <div class="tw-p-5 tw-flex tw-flex-col tw-gap-3">
              <router-link to="/user/courses" class="tw-flex tw-items-center tw-justify-center tw-w-full tw-py-3 tw-px-4 tw-rounded-xl tw-font-bold tw-text-sm tw-bg-slate-50 hover:tw-bg-indigo-50 tw-text-slate-700 hover:tw-text-indigo-600 tw-border tw-border-slate-200 hover:tw-border-indigo-200 tw-transition-all tw-no-underline">
                <i class="fas fa-arrow-left tw-mr-2"></i> Back to Courses
              </router-link>
              <router-link to="/user/packages" class="tw-flex tw-items-center tw-justify-center tw-w-full tw-py-3 tw-px-4 tw-rounded-xl tw-font-bold tw-text-sm tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-shadow-lg tw-shadow-indigo-500/20 tw-transition-all tw-no-underline hover:tw-translate-y-px">
                <i class="fas fa-graduation-cap tw-mr-2"></i> View Packages
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Not Found -->
    <div v-else class="tw-text-center tw-py-16">
      <div class="tw-text-amber-500 tw-text-6xl tw-mb-4">
        <i class="fas fa-exclamation-triangle"></i>
      </div>
      <h4 class="tw-text-white tw-font-bold tw-text-2xl tw-mb-2">Course Not Found</h4>
      <p class="tw-text-white/60 tw-text-base tw-mb-6">The course you're looking for doesn't exist or you don't have access.</p>
      <router-link to="/user/courses" class="tw-inline-flex tw-items-center tw-px-6 tw-py-3 tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-font-bold tw-rounded-xl tw-shadow-lg tw-transition-all tw-no-underline">
        <i class="fas fa-arrow-left tw-mr-2"></i> Back to Courses
      </router-link>
    </div>
  </DashboardLayout>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import DashboardLayout from '../../components/DashboardLayout.vue'
import api from '../../services/api'

export default {
  name: 'CourseDetail',
  components: {
    DashboardLayout
  },
  setup() {
    const route = useRoute()
    const router = useRouter()
    const course = ref(null)
    const loading = ref(true)
    const currencySymbol = ref('₹')
    const videoPlayer = ref(null)
    const markingComplete = ref(false)

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
        'Web Development': 'tw-bg-slate-700'
      }
      return categoryMap[category] || 'tw-bg-slate-500'
    }

    const getRequiredPlanLabel = (level) => {
      const lv = Number(level || 0)
      if (!lv || Number.isNaN(lv)) return 'N/A'
      return `Course Plan Level ${lv}`
    }

    const isYoutubeOrVimeoUrl = (url) => {
      if (!url || typeof url !== 'string') return false
      return /youtube\.com|youtu\.be|vimeo\.com/i.test(url)
    }

    const embedVideoUrl = (url) => {
      if (!url) return ''
      const u = url.trim()
      let vid = ''
      if (u.includes('youtube.com/watch?v=')) {
        vid = u.split('v=')[1]?.split('&')[0] || ''
        return vid ? `https://www.youtube.com/embed/${vid}` : u
      }
      if (u.includes('youtu.be/')) {
        vid = u.split('youtu.be/')[1]?.split('?')[0] || ''
        return vid ? `https://www.youtube.com/embed/${vid}` : u
      }
      if (u.includes('vimeo.com/')) {
        const m = u.match(/vimeo\.com\/(?:video\/)?(\d+)/)
        return m ? `https://player.vimeo.com/video/${m[1]}` : u
      }
      return u
    }

    const handleVideoError = (event) => {
      console.error('Video error:', event)
      if (window.notify) window.notify('error', 'Failed to load video.')
    }

    const handleVideoLoaded = () => {
      console.log('Video loaded successfully')
    }

    /** Call API to mark course as complete (tracks video watched + issues certificate) */
    const markCourseComplete = async () => {
      if (!course.value?.id || markingComplete.value) return
      markingComplete.value = true
      try {
        const res = await api.post('/courses/complete', { course_id: course.value.id })
        if (res.data?.status === 'success') {
          course.value = { ...course.value, is_completed: true }
          if (window.iziToast) {
            window.iziToast.success({
              title: 'Done',
              message: res.data?.message?.[0] || 'Course marked as complete. Certificate issued.',
              position: 'topRight'
            })
          }
        }
      } catch (err) {
        const msg = err.response?.data?.message?.[0] || err.message || 'Failed to mark complete'
        if (window.iziToast) {
          window.iziToast.error({ title: 'Error', message: msg, position: 'topRight' })
        }
      } finally {
        markingComplete.value = false
      }
    }

    /** When native video ends, auto-track watch and mark course complete */
    const onVideoEnded = () => {
      if (course.value?.id && !course.value?.is_completed) {
        markCourseComplete()
      }
    }

    const fetchCourse = async () => {
      loading.value = true
      try {
        const courseId = route.params.id
        const response = await api.get('/courses')
        if (response.data.status === 'success') {
          const responseData = response.data.data
          const courses = Array.isArray(responseData) ? responseData : (responseData?.data || [])
          const foundCourse = courses.find(c => c.id == courseId)
          if (foundCourse) {
            // Prevent direct access to locked courses
            if (foundCourse.unlocked === false || foundCourse.unlocked === 0 || foundCourse.unlocked === '0') {
              if (window.iziToast) {
                window.iziToast.error({
                  title: 'Locked',
                  message: 'This course is locked. Please ensure you have an active package and Ad Certificate to watch courses.',
                  position: 'topRight'
                })
              }
              router.push('/user/courses')
              course.value = null
              return
            }

            course.value = foundCourse
            currencySymbol.value = responseData?.currency_symbol || response.data.data?.currency_symbol || '₹'
          } else {
            course.value = null
          }
        }
      } catch (error) {
        console.error('Error loading course:', error)
        course.value = null
      } finally {
        loading.value = false
      }
    }

    onMounted(() => {
      fetchCourse()
    })

    return {
      course, loading, currencySymbol, videoPlayer, markingComplete,
      formatAmount, getCategoryBadgeClass, getRequiredPlanLabel,
      isYoutubeOrVimeoUrl, embedVideoUrl,
      handleVideoError, handleVideoLoaded,
      onVideoEnded, markCourseComplete
    }
  }
}
</script>
