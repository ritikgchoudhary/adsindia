<template>
  <div class="dashboard-header">
    <div class="dashboard-header__inner flex-between">
      <div class="dashboard-header__left">
        <div class="dashboard-body__bar d-lg-none d-block">
          <span class="dashboard-body__bar-icon"><i class="fas fa-bars"></i></span>
        </div>
        <div class="search-form-wrapper">
        </div>
      </div>
      <div class="user-info">
        <div class="user-info__right">
          <div class="user-info__button">
            <p class="user-info__name">Hello, {{ userFullname }}</p>
            <div class="user-info__thumb">
              <img :src="userImage" alt="User">
            </div>
          </div>
        </div>
        <ul class="user-info-dropdown">
          <li class="user-info-dropdown__item">
            <router-link to="/user/profile-setting" class="user-info-dropdown__link">
              <span class="icon"><i class="far fa-user-circle"></i></span>
              <span class="text">Profile Setting</span>
            </router-link>
          </li>

          <li class="user-info-dropdown__item">
            <router-link to="/user/change-password" class="user-info-dropdown__link">
              <span class="icon"><i class="fas fa-key"></i></span>
              <span class="text">Change Password</span>
            </router-link>
          </li>

          <li class="user-info-dropdown__item">
            <router-link to="/user/twofactor" class="user-info-dropdown__link">
              <span class="icon"><i class="fas fa-shield-alt"></i></span>
              <span class="text">2FA Security</span>
            </router-link>
          </li>

          <li class="user-info-dropdown__item">
            <a href="#" @click.prevent="handleLogout" class="user-info-dropdown__link">
              <span class="icon"><i class="fas fa-sign-out-alt"></i></span>
              <span class="text">Logout</span>
            </a>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { authService } from '../../services/authService'
import { userService } from '../../services/userService'

export default {
  name: 'Topbar',
  setup() {
    const router = useRouter()
    const userFullname = ref('User')
    const userImage = ref('/assets/images/default.png')

    const handleLogout = async () => {
      try {
        await authService.logout()
        router.push('/')
      } catch (error) {
        console.error('Logout error:', error)
        router.push('/')
      }
    }

    const fetchUserInfo = async () => {
      try {
        const response = await userService.getUserInfo()
        if (response.status === 'success' && response.data) {
          userFullname.value = response.data.fullname || `${response.data.firstname} ${response.data.lastname}` || 'User'
          // userImage.value = response.data.image || '/assets/images/default.png'
        }
      } catch (error) {
        console.error('Error loading user info:', error)
      }
    }

    onMounted(() => {
      fetchUserInfo()
    })

    return {
      userFullname,
      userImage,
      handleLogout
    }
  }
}
</script>
