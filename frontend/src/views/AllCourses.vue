<template>
  <div class="all-courses-page">
    <!-- Header Section -->
    <section class="courses-header">
      <div class="container">
        <div class="row">
          <div class="col-12">
            <nav aria-label="breadcrumb" class="mb-3">
              <ol class="breadcrumb">
                <li class="breadcrumb-item">
                  <router-link to="/">Home</router-link>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Courses</li>
              </ol>
            </nav>
            <h1 class="page-title">All Courses</h1>
          </div>
        </div>
      </div>
    </section>

    <!-- Courses Grid Section -->
    <section class="courses-section py-5">
      <div class="container">
        <!-- Loading State -->
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem; border-color: #4f46e5; border-right-color: transparent;">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-3" style="color: rgba(255, 255, 255, 0.7);">Loading courses...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="loadError" class="alert text-center" style="background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.4); color: #fca5a5; backdrop-filter: blur(10px);">
          <i class="fas fa-exclamation-circle me-2"></i>
          {{ loadError }}
        </div>

        <!-- Courses Grid -->
        <div v-else-if="courses.length > 0" class="row g-4">
          <div 
            v-for="course in courses" 
            :key="course.id || course.course_id" 
            class="col-lg-3 col-md-4 col-sm-6"
          >
            <div class="course-card">
              <div class="course-thumb">
                <img 
                  :src="course.thumbnail || 'https://picsum.photos/400/250?random=' + (course.id || course.course_id)" 
                  :alt="course.title || course.name" 
                  class="img-fluid"
                  @error="handleImageError"
                >
                <div class="course-overlay">
                  <template v-if="isAuthenticated">
                    <router-link 
                      v-if="course.unlocked !== false" 
                      :to="`/user/courses/${course.id || course.course_id}`" 
                      class="btn btn-primary btn-sm"
                    >
                      <i class="fas fa-play me-2"></i>Watch Course
                    </router-link>
                    <router-link 
                      v-else 
                      to="/user/packages" 
                      class="btn btn-warning btn-sm"
                    >
                      <i class="fas fa-lock me-2"></i>Unlock with Package
                    </router-link>
                  </template>
                  <router-link 
                    v-else 
                    to="/register" 
                    class="btn btn-primary btn-sm"
                  >
                    <i class="fas fa-user-plus me-2"></i>Sign Up to Access
                  </router-link>
                </div>
                <div class="course-badge">
                  <span class="badge" :class="getCategoryBadgeClass(course.category)">
                    {{ course.category || 'General' }}
                  </span>
                </div>
                <div v-if="course.is_completed && isAuthenticated" class="course-completed-badge">
                  <span class="badge bg-success">
                    <i class="fas fa-check-circle me-1"></i>Completed
                  </span>
                </div>
              </div>
              <div class="course-body">
                <h5 class="course-title">{{ course.title || course.name || 'Course' }}</h5>
                <p class="course-description">{{ course.description || 'No description available.' }}</p>
                <div class="course-meta">
                  <span class="meta-item">
                    <i class="fas fa-clock text-primary"></i>
                    <strong>{{ course.duration || '0 hours' }}</strong>
                  </span>
                  <span class="meta-item">
                    <i class="fas fa-users text-info"></i>
                    <strong>{{ course.students_count ?? 0 }}</strong> Students
                  </span>
                </div>
                <div class="course-footer">
                  <span v-if="course.is_free" class="badge bg-success">
                    <i class="fas fa-check-circle me-1"></i>Free Access
                  </span>
                  <span v-else class="course-price">
                    {{ currencySymbol }}{{ formatAmount(course.price) }}
                  </span>
                  <span v-if="course.unlocked === false && isAuthenticated" class="badge bg-warning">
                    <i class="fas fa-lock me-1"></i>Locked
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-5">
          <i class="fas fa-book-open fa-4x mb-3" style="color: rgba(255, 255, 255, 0.3);"></i>
          <h4 style="color: rgba(255, 255, 255, 0.7);">No Courses Available</h4>
          <p style="color: rgba(255, 255, 255, 0.5);">Check back later for new courses.</p>
        </div>

        <!-- Pagination (if needed) -->
        <div v-if="courses.length > 0 && totalCourses > courses.length" class="pagination-wrapper mt-5">
          <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
              <li class="page-item" :class="{ disabled: currentPage === 1 }">
                <a class="page-link" href="#" @click.prevent="changePage(currentPage - 1)">Previous</a>
              </li>
              <li 
                v-for="page in totalPages" 
                :key="page" 
                class="page-item" 
                :class="{ active: page === currentPage }"
              >
                <a class="page-link" href="#" @click.prevent="changePage(page)">{{ page }}</a>
              </li>
              <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                <a class="page-link" href="#" @click.prevent="changePage(currentPage + 1)">Next</a>
              </li>
            </ul>
          </nav>
          <p class="text-center text-muted mt-3">
            Showing {{ ((currentPage - 1) * perPage) + 1 }} to {{ Math.min(currentPage * perPage, totalCourses) }} of {{ totalCourses }} results
          </p>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'

export default {
  name: 'AllCourses',
  setup() {
    const router = useRouter()
    const courses = ref([])
    const currencySymbol = ref('₹')
    const loading = ref(true)
    const loadError = ref('')
    const isAuthenticated = ref(false)
    const currentPage = ref(1)
    const perPage = ref(12)
    const totalCourses = ref(0)

    const totalPages = computed(() => {
      return Math.ceil(totalCourses.value / perPage.value)
    })

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
        'UI/UX Design': 'bg-warning',
        'Web Development': 'bg-dark',
        'Affiliate Marketing': 'bg-primary',
        'Freelancing': 'bg-success',
        'Excel': 'bg-success',
        'Google Ads': 'bg-primary',
        'Stock Market': 'bg-danger',
        'Social Media': 'bg-info',
        'Personality Development': 'bg-warning'
      }
      return categoryMap[category] || 'bg-secondary'
    }

    const handleImageError = (event) => {
      event.target.src = 'https://picsum.photos/400/250?random=' + Math.random()
    }

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
          courses.value = []
          loadError.value = res.message?.[0] || 'Failed to load courses'
          return
        }

        const payload = res.data && typeof res.data === 'object' ? res.data : {}
        let list = []
        
        if (Array.isArray(res.courses_list) && res.courses_list.length > 0) {
          list = res.courses_list
        } else if (Array.isArray(payload.courses_list) && payload.courses_list.length > 0) {
          list = payload.courses_list
        } else if (Array.isArray(payload.data) && payload.data.length > 0) {
          list = payload.data
        } else if (Array.isArray(payload.list) && payload.list.length > 0) {
          list = payload.list
        } else if (Array.isArray(payload.courses) && payload.courses.length > 0) {
          list = payload.courses
        }
        
        if (list.length === 0) {
          const found = findCoursesArray(res)
          if (found && found.length > 0) {
            list = found
          }
        }

        if (Array.isArray(list)) {
          courses.value = list.map((c, index) => {
            if (!c || typeof c !== 'object') return null
            
            const courseId = c.id || c.course_id || `temp-${index + 1}`
            
            return {
              ...c,
              id: courseId,
              course_id: courseId,
              unlocked: c.unlocked !== false && c.unlocked !== 0 && c.unlocked !== '0',
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
          }).filter(c => c !== null && c !== undefined)
        } else {
          courses.value = []
        }

        totalCourses.value = courses.value.length
        currencySymbol.value = payload.currency_symbol || res.currency_symbol || '₹'
      } catch (error) {
        console.error('Error loading courses:', error)
        courses.value = []
        loadError.value = 'Failed to load courses. Please try again later.'
      } finally {
        loading.value = false
      }
    }

    const changePage = (page) => {
      if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page
        window.scrollTo({ top: 0, behavior: 'smooth' })
      }
    }

    onMounted(async () => {
      const token = localStorage.getItem('token')
      isAuthenticated.value = !!token
      await fetchCourses()
    })

    return {
      courses,
      currencySymbol,
      loading,
      loadError,
      isAuthenticated,
      currentPage,
      perPage,
      totalCourses,
      totalPages,
      formatAmount,
      getCategoryBadgeClass,
      handleImageError,
      changePage
    }
  }
}
</script>

<style scoped>
.all-courses-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
  background-attachment: fixed;
}

.courses-header {
  background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%);
  padding: 3rem 0 2rem;
  position: relative;
  overflow: hidden;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
}

.courses-header::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-image: 
    url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
  opacity: 0.2;
}

.courses-header .container {
  position: relative;
  z-index: 1;
}

.breadcrumb {
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(10px);
  padding: 0.5rem 1rem;
  border-radius: 8px;
  margin-bottom: 1rem;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.breadcrumb-item a {
  color: rgba(255, 255, 255, 0.9);
  text-decoration: none;
  transition: color 0.3s ease;
}

.breadcrumb-item a:hover {
  color: #ffffff;
}

.breadcrumb-item.active {
  color: #ffffff;
}

.page-title {
  font-size: 3rem;
  font-weight: 800;
  color: #ffffff;
  text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
  margin: 0;
}

.courses-section {
  background: transparent;
  padding: 3rem 0;
}

.course-card {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3), 0 4px 16px rgba(0, 0, 0, 0.2);
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  height: 100%;
  display: flex;
  flex-direction: column;
  position: relative;
}

.course-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 3px;
  background: linear-gradient(90deg, #4f46e5 0%, #6366f1 100%);
  opacity: 0;
  transform: translateY(-100%);
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  z-index: 1;
}

.course-card:hover {
  transform: translateY(-12px) scale(1.02);
  box-shadow: 0 16px 48px rgba(0, 0, 0, 0.4), 0 8px 24px rgba(79, 70, 229, 0.3);
  border-color: rgba(79, 70, 229, 0.4);
}

.course-card:hover::before {
  opacity: 1;
  transform: translateY(0);
}

.course-thumb {
  position: relative;
  overflow: hidden;
  height: 220px;
  background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
}

.course-thumb img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.course-card:hover .course-thumb img {
  transform: scale(1.1);
}

.course-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(79, 70, 229, 0.9) 0%, rgba(99, 102, 241, 0.9) 100%);
  backdrop-filter: blur(5px);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  z-index: 2;
}

.course-card:hover .course-overlay {
  opacity: 1;
}

.course-overlay .btn {
  border-radius: 12px;
  font-weight: 700;
  padding: 12px 24px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
  transition: all 0.3s ease;
}

.course-overlay .btn:hover {
  transform: scale(1.05);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
}

.course-badge {
  position: absolute;
  top: 12px;
  right: 12px;
  z-index: 2;
}

.course-completed-badge {
  position: absolute;
  top: 12px;
  left: 12px;
  z-index: 2;
}

.course-body {
  padding: 1.75rem;
  flex: 1;
  display: flex;
  flex-direction: column;
  background: transparent;
}

.course-title {
  font-size: 1.15rem;
  font-weight: 700;
  color: #ffffff;
  margin-bottom: 0.75rem;
  min-height: 52px;
  line-height: 1.4;
  text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
}

.course-description {
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.7);
  margin-bottom: 1rem;
  flex: 1;
  min-height: 40px;
  line-height: 1.6;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.course-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.875rem;
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(5px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  margin-bottom: 1rem;
  font-size: 0.85rem;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: rgba(255, 255, 255, 0.9);
}

.meta-item i {
  font-size: 0.9rem;
  opacity: 0.8;
}

.meta-item strong {
  color: #ffffff;
  font-weight: 600;
}

.course-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: auto;
  padding-top: 1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.course-price {
  font-size: 1.3rem;
  font-weight: 800;
  background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.pagination-wrapper {
  margin-top: 3rem;
}

.page-link {
  color: rgba(255, 255, 255, 0.8);
  background: rgba(255, 255, 255, 0.05);
  border-color: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(10px);
  transition: all 0.3s ease;
}

.page-link:hover {
  color: #ffffff;
  background: rgba(79, 70, 229, 0.3);
  border-color: rgba(79, 70, 229, 0.5);
}

.page-item.active .page-link {
  background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
  border-color: #4f46e5;
  color: #ffffff;
  box-shadow: 0 4px 12px rgba(79, 70, 229, 0.4);
}

.page-item.disabled .page-link {
  background: rgba(255, 255, 255, 0.05);
  color: rgba(255, 255, 255, 0.3);
  border-color: rgba(255, 255, 255, 0.1);
  cursor: not-allowed;
}

.pagination-wrapper p {
  color: rgba(255, 255, 255, 0.6);
}

@media (max-width: 768px) {
  .page-title {
    font-size: 2rem;
  }
  
  .course-thumb {
    height: 180px;
  }
}
</style>
