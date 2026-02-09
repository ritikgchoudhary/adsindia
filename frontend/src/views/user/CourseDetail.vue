<template>
  <DashboardLayout page-title="Course Details">
    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-3">Loading course...</p>
    </div>

    <div v-else-if="course" class="row">
      <!-- Video Player Section -->
      <div class="col-lg-8 mb-4">
        <div class="card custom--card" style="border-radius: 12px; overflow: hidden;">
          <div class="card-body p-0">
            <div class="video-container" style="position: relative; width: 100%; padding-top: 56.25%; background: #000;">
              <video
                ref="videoPlayer"
                :src="course.video_url"
                controls
                class="w-100 h-100"
                style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"
                @error="handleVideoError"
                @loadeddata="handleVideoLoaded"
              >
                Your browser does not support the video tag.
              </video>
            </div>
          </div>
        </div>

        <!-- Course Info -->
        <div class="card custom--card mt-4" style="border-radius: 12px;">
          <div class="card-body">
            <div class="d-flex align-items-start justify-content-between mb-3">
              <div>
                <h3 class="mb-2 fw-bold">{{ course.title }}</h3>
                <span class="badge" :class="getCategoryBadgeClass(course.category)" style="font-size: 0.9rem; padding: 8px 16px;">
                  {{ course.category }}
                </span>
              </div>
              <div class="text-end">
                <span class="badge badge--success" v-if="course.is_free" style="font-size: 1rem; padding: 8px 16px;">
                  <i class="fas fa-check-circle me-1"></i>Free Access
                </span>
              </div>
            </div>
            <p class="text-muted mb-4" style="font-size: 1rem; line-height: 1.6;">{{ course.description }}</p>
            
            <div class="row">
              <div class="col-md-6 mb-3">
                <div class="d-flex align-items-center">
                  <i class="fas fa-clock text-primary me-2" style="font-size: 1.2rem;"></i>
                  <div>
                    <strong>Duration</strong>
                    <p class="mb-0 text-muted">{{ course.duration }}</p>
                  </div>
                </div>
              </div>
              <div class="col-md-6 mb-3">
                <div class="d-flex align-items-center">
                  <i class="fas fa-users text-primary me-2" style="font-size: 1.2rem;"></i>
                  <div>
                    <strong>Students</strong>
                    <p class="mb-0 text-muted">{{ course.students_count }} enrolled</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="col-lg-4">
        <!-- Course Actions -->
        <div class="card custom--card mb-4" style="border-radius: 12px;">
          <div class="card-body">
            <h5 class="mb-3">Course Information</h5>
            <div class="mb-3">
              <strong>Category:</strong>
              <p class="mb-0 text-muted">{{ course.category }}</p>
            </div>
            <div class="mb-3">
              <strong>Required Package:</strong>
              <p class="mb-0">
                <span class="badge bg-info">{{ getPackageName(course.required_package) }}</span>
              </p>
            </div>
            <div v-if="!course.is_free" class="mb-3">
              <strong>Price:</strong>
              <p class="mb-0 text-primary fw-bold">{{ currencySymbol }}{{ formatAmount(course.price) }}</p>
            </div>
            <router-link to="/user/courses" class="btn btn-outline-primary w-100">
              <i class="fas fa-arrow-left me-2"></i>Back to Courses
            </router-link>
          </div>
        </div>

        <!-- Related Courses -->
        <div class="card custom--card" style="border-radius: 12px;">
          <div class="card-body">
            <h5 class="mb-3">All Courses</h5>
            <router-link to="/user/courses" class="btn btn--base w-100">
              <i class="fas fa-list me-2"></i>View All Courses
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="text-center py-5">
      <i class="fas fa-exclamation-triangle fa-4x text-warning mb-3"></i>
      <h4>Course Not Found</h4>
      <p class="text-muted">The course you're looking for doesn't exist or you don't have access to it.</p>
      <router-link to="/user/courses" class="btn btn--base mt-3">
        <i class="fas fa-arrow-left me-2"></i>Back to Courses
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

    const formatAmount = (amount) => {
      if (!amount && amount !== 0) return '0.00'
      return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }

    const getCategoryBadgeClass = (category) => {
      const categoryMap = {
        'Video Editing': 'bg-danger',
        'Graphic Design': 'bg-primary',
        'Digital Marketing': 'bg-success',
        'Content Writing': 'bg-info',
        'UI/UX Design': 'bg-warning'
      }
      return categoryMap[category] || 'bg-secondary'
    }

    const getPackageName = (packageId) => {
      const packageNames = {
        1: 'AdsLite',
        2: 'AdsPro',
        3: 'AdsSupreme',
        4: 'AdsPremium',
        5: 'AdsPremium+'
      }
      return packageNames[packageId] || 'Unknown'
    }

    const handleVideoError = (event) => {
      console.error('Video error:', event)
      if (window.notify) {
        window.notify('error', 'Failed to load video. Please try again later.')
      }
    }

    const handleVideoLoaded = () => {
      console.log('Video loaded successfully')
    }

    const fetchCourse = async () => {
      loading.value = true
      try {
        const courseId = route.params.id
        const response = await api.get('/courses')
        
        if (response.data.status === 'success') {
          const responseData = response.data.data
          const courses = Array.isArray(responseData) ? responseData : (responseData?.data || [])
          
          // Find the specific course
          const foundCourse = courses.find(c => c.id == courseId)
          
          if (foundCourse) {
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
      course,
      loading,
      currencySymbol,
      videoPlayer,
      formatAmount,
      getCategoryBadgeClass,
      getPackageName,
      handleVideoError,
      handleVideoLoaded
    }
  }
}
</script>

<style scoped>
.video-container {
  position: relative;
  width: 100%;
  padding-top: 56.25%; /* 16:9 aspect ratio */
  background: #000;
}

.video-container video {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}
</style>
