<template>
  <div class="sidebar-menu flex-between">
    <div class="sidebar-menu__inner">
      <span class="sidebar-menu__close d-lg-none d-block"><i class="fas fa-times"></i></span>

      <div class="sidebar-logo">
        <router-link to="/" class="sidebar-logo__link">
          <img :src="siteLogo" alt="Logo">
        </router-link>
      </div>
      <ul class="sidebar-menu-list">
        <li class="sidebar-menu-list__item" :class="{ active: $route.name === 'Dashboard' }" role="presentation">
          <router-link to="/user/dashboard" class="sidebar-menu-list__link">
            <span class="icon"><i class="fa-solid fa-border-all"></i></span>
            <span class="text">Dashboard</span>
          </router-link>
        </li>
        <li class="sidebar-menu-list__item" role="presentation">
          <router-link to="/user/ads-work" class="sidebar-menu-list__link">
            <span class="icon"><i class="fas fa-video"></i></span>
            <span class="text">Ads Work</span>
          </router-link>
        </li>
        <li class="sidebar-menu-list__item has-dropdown" role="presentation">
          <a href="javascript:void(0)" class="sidebar-menu-list__link">
            <span class="icon"><i class="fas fa-user-shield"></i></span>
            <span class="text">Account & KYC</span>
          </a>
          <div class="sidebar-submenu">
            <ul class="sidebar-submenu-list">
              <li class="sidebar-submenu-list__item">
                <router-link to="/user/profile-setting" class="sidebar-submenu-list__link">
                  <span class="text">Profile Setting</span>
                </router-link>
              </li>
              <li class="sidebar-submenu-list__item">
                <router-link to="/user/account-kyc" class="sidebar-submenu-list__link">
                  <span class="text">KYC & Bank Details</span>
                </router-link>
              </li>
              <li class="sidebar-submenu-list__item">
                <router-link to="/user/kyc-form" class="sidebar-submenu-list__link">
                  <span class="text">KYC Form</span>
                </router-link>
              </li>
              <li class="sidebar-submenu-list__item">
                <router-link to="/user/kyc-data" class="sidebar-submenu-list__link">
                  <span class="text">KYC Data</span>
                </router-link>
              </li>
            </ul>
          </div>
        </li>
        <li class="sidebar-menu-list__item has-dropdown" role="presentation">
          <a href="javascript:void(0)" class="sidebar-menu-list__link">
            <span class="icon"><i class="fas fa-box"></i></span>
            <span class="text">Packages</span>
          </a>
          <div class="sidebar-submenu">
            <ul class="sidebar-submenu-list">
              <li class="sidebar-submenu-list__item">
                <router-link to="/user/packages" class="sidebar-submenu-list__link">
                  <span class="text">My Package</span>
                </router-link>
              </li>
              <li class="sidebar-submenu-list__item">
                <router-link to="/user/upgrade-package" class="sidebar-submenu-list__link">
                  <span class="text">Upgrade Package</span>
                </router-link>
              </li>
            </ul>
          </div>
        </li>
        <li class="sidebar-menu-list__item" role="presentation">
          <router-link to="/user/ad-plans" class="sidebar-menu-list__link">
            <span class="icon"><i class="fas fa-shopping-cart"></i></span>
            <span class="text">Ad Plans</span>
          </router-link>
        </li>
        <li class="sidebar-menu-list__item" role="presentation">
          <router-link to="/user/courses" class="sidebar-menu-list__link">
            <span class="icon"><i class="fas fa-graduation-cap"></i></span>
            <span class="text">Courses</span>
          </router-link>
        </li>
        <li class="sidebar-menu-list__item has-dropdown" role="presentation">
          <a href="javascript:void(0)" class="sidebar-menu-list__link">
            <span class="icon"><i class="fas fa-users"></i></span>
            <span class="text">My Team</span>
          </a>
          <div class="sidebar-submenu">
            <ul class="sidebar-submenu-list">
              <li class="sidebar-submenu-list__item">
                <router-link to="/user/referral" class="sidebar-submenu-list__link">
                  <span class="text">Referral</span>
                </router-link>
              </li>
              <li class="sidebar-submenu-list__item">
                <router-link to="/user/affiliate-income" class="sidebar-submenu-list__link">
                  <span class="text">Affiliate Income</span>
                </router-link>
              </li>
              <li class="sidebar-submenu-list__item">
                <router-link to="/user/partner-program" class="sidebar-submenu-list__link">
                  <span class="text">Partner Program</span>
                </router-link>
              </li>
            </ul>
          </div>
        </li>
        <li class="sidebar-menu-list__item" role="presentation">
          <router-link to="/user/certificates" class="sidebar-menu-list__link">
            <span class="icon"><i class="fas fa-certificate"></i></span>
            <span class="text">Certificates</span>
          </router-link>
        </li>
        <li class="sidebar-menu-list__item" role="presentation">
          <router-link to="/user/conversion-log" class="sidebar-menu-list__link">
            <span class="icon"><i class="fas fa-list"></i></span>
            <span class="text">Conversion Log</span>
          </router-link>
        </li>
        <li class="sidebar-menu-list__item has-dropdown" role="presentation">
          <a href="javascript:void(0)" class="sidebar-menu-list__link">
            <span class="icon"><i class="fas fa-hand-holding-usd"></i></span>
            <span class="text">Withdraw</span>
          </a>
          <div class="sidebar-submenu">
            <ul class="sidebar-submenu-list">
              <li class="sidebar-submenu-list__item">
                <router-link to="/user/withdraw" class="sidebar-submenu-list__link">
                  <span class="text">Withdraw Money</span>
                </router-link>
              </li>
              <li class="sidebar-submenu-list__item">
                <router-link to="/user/withdraw/history" class="sidebar-submenu-list__link">
                  <span class="text">Withdraw Log</span>
                </router-link>
              </li>
            </ul>
          </div>
        </li>

        <li class="sidebar-menu-list__item" role="presentation">
          <router-link to="/user/transactions" class="sidebar-menu-list__link">
            <span class="icon"><i class="fas fa-random"></i></span>
            <span class="text">Transactions</span>
          </router-link>
        </li>

        <li class="sidebar-menu-list__item has-dropdown" role="presentation">
          <a href="javascript:void(0)" class="sidebar-menu-list__link">
            <span class="icon"><i class="fas fa-headset"></i></span>
            <span class="text">Support Ticket</span>
          </a>
          <div class="sidebar-submenu">
            <ul class="sidebar-submenu-list">
              <li class="sidebar-submenu-list__item">
                <router-link to="/user/ticket/open" class="sidebar-submenu-list__link">
                  <span class="text">Open New Ticket</span>
                </router-link>
              </li>
              <li class="sidebar-submenu-list__item">
                <router-link to="/user/ticket" class="sidebar-submenu-list__link">
                  <span class="text">My Tickets</span>
                </router-link>
              </li>
            </ul>
          </div>
        </li>

        <li class="sidebar-menu-list__item" role="presentation">
          <router-link to="/user/leaderboard" class="sidebar-menu-list__link">
            <span class="icon"><i class="fas fa-trophy"></i></span>
            <span class="text">Leaderboard</span>
          </router-link>
        </li>
        <li class="sidebar-menu-list__item" role="presentation">
          <router-link to="/user/customer-support" class="sidebar-menu-list__link">
            <span class="icon"><i class="fas fa-headset"></i></span>
            <span class="text">Customer Support</span>
          </router-link>
        </li>
        <li class="sidebar-menu-list__item" role="presentation">
          <router-link to="/user/change-password" class="sidebar-menu-list__link">
            <span class="icon"><i class="fa-solid fa-lock"></i></span>
            <span class="text">Password Change</span>
          </router-link>
        </li>
        <li class="sidebar-menu-list__item" role="presentation">
          <router-link to="/user/twofactor" class="sidebar-menu-list__link">
            <span class="icon"><i class="fas fa-shield-alt"></i></span>
            <span class="text">2FA Security</span>
          </router-link>
        </li>

        <li class="sidebar-menu-list__item" role="presentation">
          <a href="#" @click.prevent="handleLogout" class="sidebar-menu-list__link">
            <span class="icon"><i class="fas fa-sign-out-alt"></i></span>
            <span class="text">Logout</span>
          </a>
        </li>

      </ul>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { authService } from '../../services/authService'

export default {
  name: 'UserSidebar',
  setup() {
    const router = useRouter()
    const siteLogo = ref('/assets/images/logo.png')

    const handleLogout = async () => {
      try {
        await authService.logout()
        router.push('/')
      } catch (error) {
        console.error('Logout error:', error)
        router.push('/')
      }
    }

    onMounted(async () => {
      // Fetch site logo from API if needed
      try {
        // const response = await appService.getGeneralSettings()
        // if (response.data?.logo) {
        //   siteLogo.value = response.data.logo
        // }
      } catch (error) {
        console.error('Error loading logo:', error)
      }
    })

    return {
      siteLogo,
      handleLogout
    }
  }
}
</script>
