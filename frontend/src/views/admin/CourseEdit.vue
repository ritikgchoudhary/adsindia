<template>
  <AdminLayout page-title="Edit Course">
    <div class="admin-course-form">
      <div class="card">
        <div class="card-body">
          <form @submit.prevent="submitForm" v-if="!loading">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Course Name <span class="text-danger">*</span></label>
                <input type="text" v-model="formData.name" class="form-control" required>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Slug</label>
                <input type="text" v-model="formData.slug" class="form-control" placeholder="Auto-generated if empty">
              </div>
              <div class="col-md-12 mb-3">
                <label class="form-label">Description</label>
                <textarea v-model="formData.description" class="form-control" rows="4"></textarea>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Category</label>
                <input type="text" v-model="formData.category" class="form-control" placeholder="e.g., Video Editing">
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Duration</label>
                <input type="text" v-model="formData.duration" class="form-control" placeholder="e.g., 10 hours">
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Video URL</label>
                <input type="url" v-model="formData.video_url" class="form-control" placeholder="https://example.com/video.mp4">
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Required Package <span class="text-danger">*</span></label>
                <select v-model.number="formData.required_package_id" class="form-control" required>
                  <option :value="1">AdsLite (Package 1)</option>
                  <option :value="2">AdsPro (Package 2)</option>
                  <option :value="3">AdsSupreme (Package 3)</option>
                  <option :value="4">AdsPremium (Package 4)</option>
                  <option :value="5">AdsPremium+ (Package 5)</option>
                </select>
              </div>
              <div class="col-md-4 mb-3">
                <label class="form-label">Price <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text">₹</span>
                  <input type="number" v-model.number="formData.price" class="form-control" step="0.01" min="0" required>
                </div>
              </div>
              <div class="col-md-4 mb-3">
                <label class="form-label">Students Count</label>
                <input type="number" v-model.number="formData.students_count" class="form-control" min="0">
              </div>
              <div class="col-md-4 mb-3">
                <label class="form-label">Sort Order</label>
                <input type="number" v-model.number="formData.sort_order" class="form-control" min="0">
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Affiliate Commission (%)</label>
                <input type="number" v-model.number="formData.affiliate_commission_percent" class="form-control" step="0.01" min="0" max="100">
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Affiliate Commission (Fixed)</label>
                <div class="input-group">
                  <span class="input-group-text">₹</span>
                  <input type="number" v-model.number="formData.affiliate_commission_fixed" class="form-control" step="0.01" min="0">
                </div>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Course Image</label>
                <input type="file" @change="handleImageChange" accept="image/*" class="form-control">
                <small class="text-muted">JPG, PNG only. Max 2MB</small>
                <div v-if="imagePreview || currentImage" class="mt-2">
                  <img :src="imagePreview || currentImage" alt="Preview" style="max-width: 200px; border-radius: 8px;">
                </div>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Status <span class="text-danger">*</span></label>
                <select v-model.number="formData.status" class="form-control" required>
                  <option :value="1">Active</option>
                  <option :value="0">Inactive</option>
                </select>
                <div class="form-check mt-3">
                  <input class="form-check-input" type="checkbox" v-model="formData.is_recommended" id="is_recommended">
                  <label class="form-check-label" for="is_recommended">
                    Mark as Recommended
                  </label>
                </div>
              </div>
            </div>
            <div class="form-actions mt-4">
              <router-link to="/admin/courses" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Cancel
              </router-link>
              <button type="submit" class="btn btn-primary" :disabled="submitting">
                <i class="fas fa-save"></i> {{ submitting ? 'Updating...' : 'Update Course' }}
              </button>
            </div>
          </form>
          <div v-else class="text-center py-5">
            <div class="spinner-border" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import AdminLayout from '../../components/admin/AdminLayout.vue'
import api from '../../services/api'

export default {
  name: 'CourseEdit',
  components: {
    AdminLayout
  },
  setup() {
    const router = useRouter()
    const route = useRoute()
    const submitting = ref(false)
    const loading = ref(true)
    const imagePreview = ref(null)
    const currentImage = ref(null)
    const formData = ref({
      name: '',
      slug: '',
      description: '',
      category: '',
      duration: '',
      video_url: '',
      required_package_id: 1,
      price: 0,
      students_count: 0,
      sort_order: 0,
      affiliate_commission_percent: 0,
      affiliate_commission_fixed: 0,
      status: 1,
      is_recommended: false,
      image: null
    })

    const fetchCourse = async () => {
      loading.value = true
      try {
        const response = await api.get(`/admin/course/edit/${route.params.id}`)
        if (response.data.status === 'success') {
          const course = response.data.data
          formData.value = {
            name: course.name || '',
            slug: course.slug || '',
            description: course.description || '',
            category: course.category || '',
            duration: course.duration || '',
            video_url: course.video_url || '',
            required_package_id: course.required_package_id || 1,
            price: course.price || 0,
            students_count: course.students_count || 0,
            sort_order: course.sort_order || 0,
            affiliate_commission_percent: course.affiliate_commission_percent || 0,
            affiliate_commission_fixed: course.affiliate_commission_fixed || 0,
            status: course.status ?? 1,
            is_recommended: course.is_recommended || false,
            image: null
          }
          if (course.image) {
            currentImage.value = course.image_url || `/assets/images/course/${course.image}`
          }
        }
      } catch (error) {
        console.error('Error fetching course:', error)
        const errorMessage = error.response?.data?.message?.error?.[0] || 
                              error.response?.data?.message || 
                              'Failed to load course'
        if (window.notify) {
          window.notify('error', errorMessage)
        }
        router.push('/admin/courses')
      } finally {
        loading.value = false
      }
    }

    const handleImageChange = (event) => {
      const file = event.target.files[0]
      if (file) {
        formData.value.image = file
        const reader = new FileReader()
        reader.onload = (e) => {
          imagePreview.value = e.target.result
        }
        reader.readAsDataURL(file)
      }
    }

    const submitForm = async () => {
      submitting.value = true
      try {
        const formDataToSend = new FormData()
        Object.keys(formData.value).forEach(key => {
          if (key === 'image' && formData.value.image) {
            formDataToSend.append('image', formData.value.image)
          } else if (key !== 'image') {
            formDataToSend.append(key, formData.value[key])
          }
        })

        const response = await api.post(`/admin/course/update/${route.params.id}`, formDataToSend, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })

        if (response.data.status === 'success') {
          if (window.notify) {
            window.notify('success', 'Course updated successfully')
          }
          router.push('/admin/courses')
        }
      } catch (error) {
        console.error('Error updating course:', error)
        const errorMessage = error.response?.data?.message?.error?.[0] || 
                              error.response?.data?.message || 
                              error.message || 
                              'Failed to update course'
        if (window.notify) {
          window.notify('error', errorMessage)
        }
      } finally {
        submitting.value = false
      }
    }

    onMounted(() => {
      fetchCourse()
    })

    return {
      formData,
      submitting,
      loading,
      imagePreview,
      currentImage,
      handleImageChange,
      submitForm
    }
  }
}
</script>

<style scoped>
.admin-course-form {
  padding: 20px 0;
}

.card {
  background: white;
  border-radius: 15px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  border: none;
}

.card-body {
  padding: 30px;
}

.form-label {
  font-weight: 600;
  color: #4a5568;
  margin-bottom: 8px;
}

.form-control {
  border-radius: 8px;
  border: 1px solid #e2e8f0;
  padding: 10px 15px;
}

.form-control:focus {
  border-color: #667eea;
  box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.form-actions {
  display: flex;
  gap: 15px;
  justify-content: flex-end;
}

.btn {
  border-radius: 8px;
  padding: 10px 20px;
  font-weight: 600;
}

.btn-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
}

.btn-secondary {
  background: #e2e8f0;
  color: #4a5568;
  border: none;
}
</style>
