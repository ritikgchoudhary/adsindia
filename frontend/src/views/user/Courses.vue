<template>
  <DashboardLayout page-title="Video Courses">
    <!-- Package Info Banner -->
    <div v-if="userPackageId === 0" class="alert alert-warning mb-4" style="border-radius: 10px;">
      <div class="d-flex align-items-center">
        <i class="fas fa-info-circle fa-2x me-3"></i>
        <div>
          <h5 class="mb-1">No Active Package</h5>
          <p class="mb-0">Purchase a package to unlock more courses. <router-link to="/user/packages" class="alert-link">View Packages</router-link></p>
        </div>
      </div>
    </div>

    <div v-else class="alert alert-success mb-4" style="border-radius: 10px;">
      <div class="d-flex align-items-center">
        <i class="fas fa-check-circle fa-2x me-3"></i>
        <div>
          <h5 class="mb-1">Active Package: {{ currentPackageName }}</h5>
          <p class="mb-0">You have access to <strong>{{ totalCourses }}</strong> courses. Upgrade package to unlock more!</p>
        </div>
      </div>
    </div>

    <!-- Courses Grid -->
    <div v-if="courses.length > 0" class="row">
      <div v-for="course in courses" :key="course?.id || Math.random()" class="col-lg-4 col-md-6 mb-4" v-if="course && course.id">
        <div class="card custom--card course-card" style="border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: transform 0.3s;">
          <div class="course-thumb" style="position: relative; overflow: hidden; height: 220px;">
            <img 
              :src="course.thumbnail || 'https://picsum.photos/400/250?random=' + course.id" 
              :alt="course.title" 
              class="img-fluid"
              style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;"
              @error="handleImageError"
            >
            <div class="course-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s;">
              <router-link :to="`/user/courses/${course.id}`" class="btn btn--base btn-lg" style="border-radius: 8px; font-weight: 600;">
                <i class="fas fa-play me-2"></i>Watch Course
              </router-link>
            </div>
            <div class="course-badge" style="position: absolute; top: 10px; right: 10px;">
              <span class="badge" :class="getCategoryBadgeClass(course.category)" style="font-size: 0.75rem; padding: 6px 12px;">
                {{ course.category }}
              </span>
            </div>
          </div>
          <div class="card-body" style="padding: 20px;">
            <h5 class="mb-2 fw-bold" style="color: #212529; font-size: 1.1rem; min-height: 50px;">{{ course.title }}</h5>
            <p class="text-muted mb-3" style="font-size: 0.9rem; min-height: 60px; line-height: 1.5;">{{ course.description }}</p>
            <div class="d-flex justify-content-between align-items-center mb-3" style="font-size: 0.85rem; color: #6c757d;">
              <span><i class="fas fa-clock me-1"></i>{{ course.duration }}</span>
              <span><i class="fas fa-users me-1"></i>{{ course.students_count }} Students</span>
            </div>
            <div class="mt-3">
              <span class="badge badge--success" v-if="course.is_free" style="font-size: 0.85rem; padding: 6px 12px;">
                <i class="fas fa-check-circle me-1"></i>Free Access
              </span>
              <span v-else class="text-primary fw-bold">
                {{ currencySymbol }}{{ formatAmount(course.price) }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- No Courses Message -->
    <div v-else class="text-center py-5">
      <i class="fas fa-graduation-cap fa-4x text-muted mb-3"></i>
      <h4>No Courses Available</h4>
      <p class="text-muted">Please purchase a package to access courses.</p>
      <router-link to="/user/packages" class="btn btn--base mt-3">
        <i class="fas fa-box me-2"></i>View Packages
      </router-link>
    </div>
  </DashboardLayout>
</template>

<script>
import { ref, onMounted } from 'vue'
import DashboardLayout from '../../components/DashboardLayout.vue'
import api from '../../services/api'

export default {
  name: 'Courses',
  components: {
    DashboardLayout
  },
  setup() {
    const courses = ref([])
    const currencySymbol = ref('₹')
    const userPackageId = ref(0)
    const totalCourses = ref(0)
    const currentPackageName = ref('')

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

    const handleImageError = (event) => {
      event.target.src = 'https://picsum.photos/400/250?random=' + Math.random()
    }

    const fetchCourses = async () => {
      try {
        const response = await api.get('/courses')
        console.log('Courses API Response:', response.data)
        
        if (response.data.status === 'success') {
          const responseData = response.data.data
          
          // Extract courses array
          if (Array.isArray(responseData)) {
            courses.value = responseData
          } else if (responseData?.data && Array.isArray(responseData.data)) {
            courses.value = responseData.data
          } else {
            courses.value = []
          }
          
          // Extract other data
          userPackageId.value = responseData?.user_package_id || response.data.data?.user_package_id || 0
          totalCourses.value = responseData?.total_courses || response.data.data?.total_courses || courses.value.length
          currencySymbol.value = responseData?.currency_symbol || response.data.data?.currency_symbol || '₹'
          
          // Get package name
          const packageNames = {
            1: 'AdsLite',
            2: 'AdsPro',
            3: 'AdsSupreme',
            4: 'AdsPremium',
            5: 'AdsPremium+'
          }
          currentPackageName.value = packageNames[userPackageId.value] || 'No Package'
          
          console.log('Loaded courses:', courses.value.length)
          console.log('User package ID:', userPackageId.value)
        }
      } catch (error) {
        console.error('Error loading courses:', error)
        courses.value = []
      }
    }

    onMounted(() => {
      fetchCourses()
    })

    return {
      courses,
      currencySymbol,
      userPackageId,
      totalCourses,
      currentPackageName,
      formatAmount,
      getCategoryBadgeClass,
      handleImageError
    }
  }
}
</script>

<style scoped>
.course-card {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.course-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 8px 16px rgba(0,0,0,0.15) !important;
}

.course-thumb:hover .course-overlay {
  opacity: 1;
}

.course-thumb:hover img {
  transform: scale(1.1);
}

.course-badge {
  z-index: 10;
}
</style>
