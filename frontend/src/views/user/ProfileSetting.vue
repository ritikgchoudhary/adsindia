<template>
  <DashboardLayout page-title="Profile Setting">
    <div class="card custom--card">
      <div class="card-body">
        <form @submit.prevent="handleSubmit" enctype="multipart/form-data">
          <div class="profile-item">
            <div class="profile-item__thumb">
              <div class="file-upload">
                <label class="edit" for="profile-image"><i class="las la-camera"></i></label>
                <input type="file" @change="handleImageChange" name="image" class="form-control form--control" id="profile-image" hidden>
              </div>
              <div class="thumb">
                <img id="profileImagePreview" :src="profileImage" alt="User Image">
              </div>
            </div>
            <div class="profile-item__content">
              <p class="text">Hello, {{ userFullname }}</p>
              <p class="mail">{{ userEmail }}</p>
            </div>
          </div>

          <div class="row">
            <div class="col-sm-6">
              <div class="form-group">
                <label class="form--label">First Name</label>
                <input type="text" v-model="form.firstname" class="form--control" name="firstname" required>
              </div>
            </div>

            <div class="col-sm-6">
              <div class="form-group">
                <label class="form--label">Last Name</label>
                <input type="text" v-model="form.lastname" class="form--control" name="lastname" required>
              </div>
            </div>

            <div class="col-sm-6">
              <div class="form-group">
                <label class="form--label">E-mail Address</label>
                <input type="email" class="form--control" :value="userEmail" disabled>
              </div>
            </div>

            <div class="col-sm-6">
              <div class="form-group">
                <label class="form--label">Mobile Number</label>
                <input type="text" class="form--control" :value="userMobile" disabled>
              </div>
            </div>

            <div class="col-sm-6">
              <div class="form-group">
                <label class="form--label">Address</label>
                <input type="text" v-model="form.address" class="form--control" name="address">
              </div>
            </div>

            <div class="col-sm-6">
              <div class="form-group">
                <label class="form--label">State</label>
                <input type="text" v-model="form.state" class="form--control" name="state">
              </div>
            </div>

            <div class="col-sm-4">
              <div class="form-group">
                <label class="form--label">Zip Code</label>
                <input type="text" v-model="form.zip" class="form--control" name="zip">
              </div>
            </div>

            <div class="col-sm-4">
              <div class="form-group">
                <label class="form--label">City</label>
                <input type="text" v-model="form.city" class="form--control" name="city">
              </div>
            </div>

            <div class="col-sm-4">
              <div class="form-group">
                <label class="form--label">Country</label>
                <input type="text" class="form--control" :value="userCountry" disabled>
              </div>
            </div>

          </div>
          <button type="submit" class="btn btn--base btn--lg w-100">Submit</button>
        </form>
      </div>
    </div>
  </DashboardLayout>
</template>

<script>
import { ref, onMounted } from 'vue'
import DashboardLayout from '../../components/DashboardLayout.vue'
import { userService } from '../../services/userService'
import api from '../../services/api'

export default {
  name: 'ProfileSetting',
  components: {
    DashboardLayout
  },
  setup() {
    const form = ref({
      firstname: '',
      lastname: '',
      address: '',
      state: '',
      zip: '',
      city: ''
    })
    const profileImage = ref('/assets/images/default.png')
    const userFullname = ref('User')
    const userEmail = ref('')
    const userMobile = ref('')
    const userCountry = ref('')
    const imageFile = ref(null)

    const handleImageChange = (event) => {
      const file = event.target.files[0]
      if (file) {
        imageFile.value = file
        const reader = new FileReader()
        reader.onload = (e) => {
          profileImage.value = e.target.result
        }
        reader.readAsDataURL(file)
      }
    }

    const handleSubmit = async () => {
      try {
        const formData = new FormData()
        formData.append('firstname', form.value.firstname)
        formData.append('lastname', form.value.lastname)
        formData.append('address', form.value.address)
        formData.append('state', form.value.state)
        formData.append('zip', form.value.zip)
        formData.append('city', form.value.city)
        if (imageFile.value) {
          formData.append('image', imageFile.value)
        }

        const response = await api.post('/profile-setting', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })

        if (response.data.status === 'success') {
          if (window.notify) {
            window.notify('success', 'Profile updated successfully!')
          }
        }
      } catch (error) {
        console.error('Error updating profile:', error)
        if (window.notify) {
          window.notify('error', error.response?.data?.message || 'Failed to update profile')
        }
      }
    }

    const fetchUserInfo = async () => {
      try {
        const response = await userService.getUserInfo()
        if (response.status === 'success' && response.data) {
          const user = response.data
          form.value.firstname = user.firstname || ''
          form.value.lastname = user.lastname || ''
          form.value.address = user.address || ''
          form.value.state = user.state || ''
          form.value.zip = user.zip || ''
          form.value.city = user.city || ''
          userFullname.value = user.fullname || `${user.firstname} ${user.lastname}` || 'User'
          userEmail.value = user.email || ''
          userMobile.value = user.mobile || ''
          userCountry.value = user.country_name || ''
          // profileImage.value = user.image || '/assets/images/default.png'
        }
      } catch (error) {
        console.error('Error loading user info:', error)
      }
    }

    onMounted(() => {
      fetchUserInfo()
    })

    return {
      form,
      profileImage,
      userFullname,
      userEmail,
      userMobile,
      userCountry,
      handleImageChange,
      handleSubmit
    }
  }
}
</script>
