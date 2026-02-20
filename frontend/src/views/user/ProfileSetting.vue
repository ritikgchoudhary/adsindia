<template>
  <DashboardLayout page-title="Profile Setting" :dark-theme="true">
    <div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-slate-200 tw-overflow-hidden">
      <div class="tw-p-6 md:tw-p-8">
        <form @submit.prevent="handleSubmit" enctype="multipart/form-data">
          <!-- Profile Locked Notice -->
          <div class="tw-mb-6 tw-bg-amber-50 tw-border tw-border-amber-200 tw-rounded-xl tw-p-4 tw-flex tw-items-start tw-gap-3">
            <i class="fas fa-lock tw-text-amber-600 tw-mt-0.5"></i>
            <div>
              <div class="tw-text-amber-900 tw-font-bold tw-text-sm">Profile editing is disabled</div>
              <div class="tw-text-amber-800/90 tw-text-sm">If you need to change your profile details, please contact support.</div>
            </div>
          </div>

          <!-- Profile Header -->
          <div class="tw-flex tw-flex-col md:tw-flex-row tw-items-center tw-gap-6 tw-mb-8 tw-bg-slate-50 tw-p-6 tw-rounded-xl tw-border tw-border-slate-100">
            <div class="tw-relative tw-group">
              <div class="tw-w-28 tw-h-28 tw-rounded-full tw-overflow-hidden tw-border-4 tw-border-white tw-shadow-md">
                <img id="profileImagePreview" :src="profileImage" alt="User Image" class="tw-w-full tw-h-full tw-object-cover">
              </div>
              <label
                v-if="!profileLocked"
                for="profile-image"
                class="tw-absolute tw-bottom-1 tw-right-1 tw-w-8 tw-h-8 tw-bg-indigo-600 tw-text-white tw-rounded-full tw-flex tw-items-center tw-justify-center tw-cursor-pointer tw-shadow-lg tw-transition-transform hover:tw-scale-110 active:tw-scale-95"
              >
                <i class="fas fa-camera tw-text-sm"></i>
              </label>
              <input v-if="!profileLocked" type="file" @change="handleImageChange" name="image" id="profile-image" class="tw-hidden" accept="image/*">
            </div>
            <div class="tw-text-center md:tw-text-left">
              <h3 class="tw-text-xl tw-font-bold tw-text-slate-900 tw-mb-1">Hello, {{ userFullname }}</h3>
              <p class="tw-text-slate-500 tw-text-sm tw-font-medium tw-mb-0">{{ userEmail }}</p>
            </div>
          </div>

          <!-- Form Grid -->
          <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-6 tw-mb-8">
            <div class="tw-flex tw-flex-col tw-gap-2">
              <label class="tw-text-sm tw-font-semibold tw-text-slate-700">First Name</label>
              <input 
                type="text" 
                v-model="form.firstname" 
                :disabled="profileLocked"
                class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-slate-200 tw-rounded-xl tw-font-medium tw-transition-all"
                :class="profileLocked ? 'tw-bg-slate-50 tw-text-slate-500 tw-cursor-not-allowed' : 'tw-bg-white tw-text-slate-700 focus:tw-outline-none focus:tw-border-indigo-500 focus:tw-ring-4 focus:tw-ring-indigo-500/10'"
                name="firstname" 
                required
              >
            </div>

            <div class="tw-flex tw-flex-col tw-gap-2">
              <label class="tw-text-sm tw-font-semibold tw-text-slate-700">Last Name</label>
              <input 
                type="text" 
                v-model="form.lastname" 
                :disabled="profileLocked"
                class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-slate-200 tw-rounded-xl tw-font-medium tw-transition-all"
                :class="profileLocked ? 'tw-bg-slate-50 tw-text-slate-500 tw-cursor-not-allowed' : 'tw-bg-white tw-text-slate-700 focus:tw-outline-none focus:tw-border-indigo-500 focus:tw-ring-4 focus:tw-ring-indigo-500/10'"
                name="lastname" 
                required
              >
            </div>

            <div class="tw-flex tw-flex-col tw-gap-2">
              <label class="tw-text-sm tw-font-semibold tw-text-slate-700">E-mail Address</label>
              <input 
                type="email" 
                class="tw-w-full tw-px-4 tw-py-3 tw-bg-slate-50 tw-border tw-border-slate-200 tw-rounded-xl tw-text-slate-500 tw-font-medium tw-cursor-not-allowed"
                :value="userEmail" 
                disabled
              >
            </div>

            <div class="tw-flex tw-flex-col tw-gap-2">
              <label class="tw-text-sm tw-font-semibold tw-text-slate-700">Mobile Number</label>
              <input 
                type="text" 
                class="tw-w-full tw-px-4 tw-py-3 tw-bg-slate-50 tw-border tw-border-slate-200 tw-rounded-xl tw-text-slate-500 tw-font-medium tw-cursor-not-allowed"
                :value="userMobile" 
                disabled
              >
            </div>

            <div class="tw-flex tw-flex-col tw-gap-2">
              <label class="tw-text-sm tw-font-semibold tw-text-slate-700">Address</label>
              <input 
                type="text" 
                v-model="form.address" 
                :disabled="profileLocked"
                class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-slate-200 tw-rounded-xl tw-font-medium tw-transition-all"
                :class="profileLocked ? 'tw-bg-slate-50 tw-text-slate-500 tw-cursor-not-allowed' : 'tw-bg-white tw-text-slate-700 focus:tw-outline-none focus:tw-border-indigo-500 focus:tw-ring-4 focus:tw-ring-indigo-500/10'"
                name="address"
              >
            </div>

            <div class="tw-flex tw-flex-col tw-gap-2">
              <label class="tw-text-sm tw-font-semibold tw-text-slate-700">State</label>
              <input 
                type="text" 
                v-model="form.state" 
                :disabled="profileLocked"
                class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-slate-200 tw-rounded-xl tw-font-medium tw-transition-all"
                :class="profileLocked ? 'tw-bg-slate-50 tw-text-slate-500 tw-cursor-not-allowed' : 'tw-bg-white tw-text-slate-700 focus:tw-outline-none focus:tw-border-indigo-500 focus:tw-ring-4 focus:tw-ring-indigo-500/10'"
                name="state"
              >
            </div>

            <div class="tw-flex tw-flex-col tw-gap-2 md:tw-col-span-2 lg:tw-col-span-1">
              <div class="tw-grid tw-grid-cols-2 tw-gap-6">
                 <div class="tw-flex tw-flex-col tw-gap-2">
                    <label class="tw-text-sm tw-font-semibold tw-text-slate-700">Zip Code</label>
                    <input 
                      type="text" 
                      v-model="form.zip" 
                      :disabled="profileLocked"
                      class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-slate-200 tw-rounded-xl tw-font-medium tw-transition-all"
                      :class="profileLocked ? 'tw-bg-slate-50 tw-text-slate-500 tw-cursor-not-allowed' : 'tw-bg-white tw-text-slate-700 focus:tw-outline-none focus:tw-border-indigo-500 focus:tw-ring-4 focus:tw-ring-indigo-500/10'"
                      name="zip"
                    >
                 </div>
                 <div class="tw-flex tw-flex-col tw-gap-2">
                    <label class="tw-text-sm tw-font-semibold tw-text-slate-700">City</label>
                    <input 
                      type="text" 
                      v-model="form.city" 
                      :disabled="profileLocked"
                      class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-slate-200 tw-rounded-xl tw-font-medium tw-transition-all"
                      :class="profileLocked ? 'tw-bg-slate-50 tw-text-slate-500 tw-cursor-not-allowed' : 'tw-bg-white tw-text-slate-700 focus:tw-outline-none focus:tw-border-indigo-500 focus:tw-ring-4 focus:tw-ring-indigo-500/10'"
                      name="city"
                    >
                 </div>
              </div>
            </div>

            <div class="tw-flex tw-flex-col tw-gap-2">
              <label class="tw-text-sm tw-font-semibold tw-text-slate-700">Country</label>
              <input 
                type="text" 
                class="tw-w-full tw-px-4 tw-py-3 tw-bg-slate-50 tw-border tw-border-slate-200 tw-rounded-xl tw-text-slate-500 tw-font-medium tw-cursor-not-allowed"
                :value="userCountry" 
                disabled
              >
            </div>
          </div>

          <button 
            type="submit" 
            :disabled="profileLocked"
            class="tw-w-full tw-py-4 tw-text-white tw-font-bold tw-rounded-xl tw-transition-all tw-transform tw-shadow-lg tw-border-0 tw-text-base"
            :class="profileLocked ? 'tw-bg-slate-400 tw-cursor-not-allowed tw-shadow-slate-500/10' : 'tw-bg-indigo-600 hover:tw-bg-indigo-700 active:tw-scale-[0.99] tw-shadow-indigo-500/30 tw-cursor-pointer'"
          >
            Update Profile
          </button>
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
    const profileLocked = ref(true)
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
      if (profileLocked.value) {
        if (window.notify) window.notify('warning', 'Profile editing is disabled. Please contact support.')
        else alert('Profile editing is disabled. Please contact support.')
        return
      }
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
          } else {
             alert('Profile updated successfully!')
          }
          // Reload user info to update global state if needed
          fetchUserInfo()
        }
      } catch (error) {
        console.error('Error updating profile:', error)
          if (window.notify) {
            window.notify('error', error.response?.data?.message || 'Failed to update profile')
          } else {
            alert(error.response?.data?.message || 'Failed to update profile')
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
          userFullname.value = user.fullname || [user.firstname, user.lastname].filter(Boolean).join(' ') || 'User'
          userEmail.value = user.email || ''
          userMobile.value = user.mobile || ''
          userCountry.value = user.country_name || ''
          if(user.image) profileImage.value = user.image
        }
      } catch (error) {
        console.error('Error loading user info:', error)
      }
    }

    onMounted(() => {
      fetchUserInfo()
    })

    return {
      profileLocked,
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
