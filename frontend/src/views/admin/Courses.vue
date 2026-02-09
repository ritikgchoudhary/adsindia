<template>
  <AdminLayout page-title="Manage Courses">
    <div class="admin-courses">
      <div class="page-header">
        <h2>Courses Management</h2>
        <router-link to="/admin/courses/create" class="btn btn-primary">
          <i class="fas fa-plus"></i> Add New Course
        </router-link>
      </div>

      <div class="card">
        <div class="card-body">
          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <th>Image</th>
                  <th>Course Name</th>
                  <th>Category</th>
                  <th>Package</th>
                  <th>Price</th>
                  <th>Students</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="loading">
                  <td colspan="8" class="text-center">
                    <div class="spinner-border" role="status">
                      <span class="visually-hidden">Loading...</span>
                    </div>
                  </td>
                </tr>
                <tr v-else-if="courses.length === 0">
                  <td colspan="8" class="text-center text-muted">No courses found</td>
                </tr>
                <tr v-else v-for="course in courses" :key="course.id">
                  <td>
                    <img :src="course.thumbnail || '/assets/images/default.png'" 
                         alt="Course" 
                         style="width: 80px; height: 50px; object-fit: cover; border-radius: 5px;">
                  </td>
                  <td>
                    <strong>{{ course.name }}</strong>
                    <br>
                    <small class="text-muted">{{ course.description ? course.description.substring(0, 50) + '...' : '' }}</small>
                  </td>
                  <td>{{ course.category || 'N/A' }}</td>
                  <td>
                    <span class="badge bg-info">{{ getPackageName(course.required_package_id) }}</span>
                  </td>
                  <td>₹{{ formatAmount(course.price) }}</td>
                  <td>{{ course.students_count || 0 }}</td>
                  <td>
                    <span :class="course.status === 1 ? 'badge bg-success' : 'badge bg-danger'">
                      {{ course.status === 1 ? 'Active' : 'Inactive' }}
                    </span>
                  </td>
                  <td>
                    <div class="btn-group">
                      <router-link :to="`/admin/courses/edit/${course.id}`" class="btn btn-sm btn-primary">
                        <i class="fas fa-edit"></i>
                      </router-link>
                      <button @click="deleteCourse(course.id)" class="btn btn-sm btn-danger">
                        <i class="fas fa-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import AdminLayout from '../../components/admin/AdminLayout.vue'
import api from '../../services/api'

export default {
  name: 'AdminCourses',
  components: {
    AdminLayout
  },
  setup() {
    const router = useRouter()
    const courses = ref([])
    const loading = ref(true)

    const formatAmount = (amount) => {
      return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }

    const getPackageName = (packageId) => {
      const packages = {
        1: 'AdsLite',
        2: 'AdsPro',
        3: 'AdsSupreme',
        4: 'AdsPremium',
        5: 'AdsPremium+'
      }
      return packages[packageId] || 'N/A'
    }

    const fetchCourses = async () => {
      loading.value = true
      try {
        const response = await api.get('/admin/course')
        if (response.data.status === 'success') {
          // Handle both array and object response structures
          const data = response.data.data
          courses.value = Array.isArray(data) ? data : (data?.courses || data || [])
        }
      } catch (error) {
        console.error('Error fetching courses:', error)
        const errorMessage = error.response?.data?.message?.error?.[0] || 
                            error.response?.data?.message || 
                            'Failed to load courses'
        if (window.notify) {
          window.notify('error', errorMessage)
        }
      } finally {
        loading.value = false
      }
    }

    const deleteCourse = async (id) => {
      if (!confirm('Are you sure you want to delete this course?')) {
        return
      }

      try {
        const response = await api.post(`/admin/course/delete/${id}`)
        if (response.data.status === 'success') {
          if (window.notify) {
            window.notify('success', 'Course deleted successfully')
          }
          fetchCourses()
        }
      } catch (error) {
        console.error('Error deleting course:', error)
        const errorMessage = error.response?.data?.message?.error?.[0] || 
                            error.response?.data?.message || 
                            'Failed to delete course'
        if (window.notify) {
          window.notify('error', errorMessage)
        }
      }
    }

    onMounted(() => {
      fetchCourses()
    })

    return {
      courses,
      loading,
      formatAmount,
      getPackageName,
      deleteCourse
    }
  }
}
</script>

<style scoped>
.admin-courses {
  padding: 20px 0;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
}

.page-header h2 {
  margin: 0;
  font-weight: 700;
  color: #2d3748;
}

.card {
  background: white;
  border-radius: 15px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  border: none;
}

.card-body {
  padding: 25px;
}

.table {
  margin: 0;
}

.table thead th {
  background: #f7fafc;
  font-weight: 600;
  color: #4a5568;
  border-bottom: 2px solid #e2e8f0;
  padding: 15px;
}

.table tbody td {
  padding: 15px;
  vertical-align: middle;
}

.btn-group {
  display: flex;
  gap: 5px;
}

.btn {
  border-radius: 8px;
  padding: 8px 15px;
  font-weight: 600;
  transition: all 0.3s;
}

.btn-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-danger {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  border: none;
}

.btn-danger:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(245, 87, 108, 0.4);
}
</style>
