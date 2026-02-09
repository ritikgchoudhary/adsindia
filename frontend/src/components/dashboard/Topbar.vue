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

<style scoped>
.dashboard-header {
  background: white;
  padding: 0.75rem 2rem;
  border-bottom: 1px solid #e2e8f0;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
  z-index: 100;
  font-family: 'Outfit', sans-serif;
}

.dashboard-body__bar-icon {
  font-size: 1.25rem;
  color: #1e293b;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 8px;
  transition: background 0.2s;
}

.dashboard-body__bar-icon:hover {
  background: #f1f5f9;
}

.user-info__button {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  cursor: pointer;
  padding: 0.5rem 0.75rem;
  border-radius: 12px;
  transition: all 0.2s;
}

.user-info__button:hover {
  background: #f8fafc;
}

.user-info__name {
  font-weight: 600;
  color: #1e293b;
  margin: 0;
  font-size: 0.95rem;
}

.user-info__thumb {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  overflow: hidden;
  border: 2px solid #f1f5f9;
}

.user-info__thumb img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.user-info-dropdown {
  position: absolute;
  top: 100%;
  right: 2rem;
  background: white;
  min-width: 220px;
  border-radius: 16px;
  padding: 0.75rem;
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
  border: 1px solid #e2e8f0;
  list-style: none;
  visibility: hidden;
  opacity: 0;
  transform: translateY(10px);
  transition: all 0.2s;
  z-index: 1000;
}

.user-info-dropdown.show {
  visibility: visible;
  opacity: 1;
  transform: translateY(0);
}

.user-info-dropdown__link {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  color: #475569;
  text-decoration: none;
  border-radius: 10px;
  font-size: 0.9rem;
  font-weight: 500;
  transition: all 0.2s;
}

.user-info-dropdown__link:hover {
  background: #f1f5f9;
  color: #4f46e5;
}

.user-info-dropdown__item:not(:last-child) {
  margin-bottom: 0.25rem;
}
</style>
