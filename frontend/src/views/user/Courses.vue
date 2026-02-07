<template>
  <DashboardLayout page-title="Courses">
    <div class="row">
      <div v-for="course in courses" :key="course?.id || Math.random()" class="col-lg-4 col-md-6 mb-4" v-if="course && course.id">
        <div class="card custom--card">
          <div class="course-thumb">
            <img :src="course.thumbnail || '/assets/images/default-course.jpg'" :alt="course.title" class="img-fluid">
            <div class="course-overlay">
              <router-link :to="`/user/courses/${course.id}`" class="btn btn--base">
                <i class="fas fa-play me-2"></i>View Course
              </router-link>
            </div>
          </div>
          <div class="card-body">
            <h5 class="mb-2">{{ course.title }}</h5>
            <p class="text-muted mb-3">{{ course.description }}</p>
            <div class="d-flex justify-content-between align-items-center">
              <span><i class="fas fa-clock me-1"></i>{{ course.duration }}</span>
              <span><i class="fas fa-users me-1"></i>{{ course.students_count }} Students</span>
            </div>
            <div class="mt-3">
              <span class="badge badge--success" v-if="course.is_free">Free</span>
              <span v-else class="text-primary">
                <strong>{{ currencySymbol }}{{ formatAmount(course.price) }}</strong>
              </span>
            </div>
          </div>
        </div>
      </div>
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

    const formatAmount = (amount) => {
      if (!amount && amount !== 0) return '0.00'
      return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }

    const fetchCourses = async () => {
      try {
        const response = await api.get('/courses')
        if (response.data.status === 'success') {
          courses.value = response.data.data || []
          currencySymbol.value = response.data.currency_symbol || '₹'
        }
      } catch (error) {
        console.error('Error loading courses:', error)
      }
    }

    onMounted(() => {
      fetchCourses()
    })

    return {
      courses,
      currencySymbol,
      formatAmount
    }
  }
}
</script>

<style scoped>
.course-thumb {
  position: relative;
  overflow: hidden;
}

.course-thumb img {
  width: 100%;
  height: 200px;
  object-fit: cover;
  transition: transform 0.3s;
}

.course-thumb:hover img {
  transform: scale(1.1);
}

.course-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.3s;
}

.course-thumb:hover .course-overlay {
  opacity: 1;
}
</style>
