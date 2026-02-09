<template>
  <div class="sidebar-menu flex-between">
    <div class="sidebar-menu__inner">
      <span class="sidebar-menu__close d-lg-none d-block"><i class="fas fa-times"></i></span>

      <div class="sidebar-logo">
        <router-link to="/" class="sidebar-logo__link">
          <img v-if="siteLogo" :src="siteLogo" alt="Logo" @error="siteLogo = null">
        <span v-else class="sidebar-logo-text">Logo</span>
        </router-link>
      </div>
      <ul class="sidebar-menu-list">
        <li class="sidebar-menu-list__item" :class="{ active: isActive('/user/dashboard') }" role="presentation">
          <router-link to="/user/dashboard" class="sidebar-menu-list__link">
            <span class="icon"><i class="fas fa-border-all"></i></span>
            <span class="text">Dashboard</span>
          </router-link>
        </li>
        <li class="sidebar-menu-list__item has-dropdown" :class="{ active: isDropdownActive(['/user/ads-work', '/user/ad-plans']) }" role="presentation">
          <a href="javascript:void(0)" class="sidebar-menu-list__link">
            <span class="icon"><i class="fas fa-video"></i></span>
            <span class="text">Ads</span>
          </a>
          <div class="sidebar-submenu">
            <ul class="sidebar-submenu-list">
              <li class="sidebar-submenu-list__item" :class="{ active: isActive('/user/ads-work') }">
                <router-link to="/user/ads-work" class="sidebar-submenu-list__link">
                  <span class="text">Ads Work</span>
                </router-link>
              </li>
              <li class="sidebar-submenu-list__item" :class="{ active: isActive('/user/ad-plans') }">
                <router-link to="/user/ad-plans" class="sidebar-submenu-list__link">
                  <span class="text">Ad Plans</span>
                </router-link>
              </li>
            </ul>
          </div>
        </li>
        <li class="sidebar-menu-list__item" :class="{ active: isActive(['/user/account-kyc', '/user/kyc-form', '/user/kyc-data']) }" role="presentation">
          <router-link to="/user/account-kyc" class="sidebar-menu-list__link">
            <span class="icon"><i class="fas fa-user-shield"></i></span>
            <span class="text">Account & KYC</span>
          </router-link>
        </li>
        <li class="sidebar-menu-list__item" :class="{ active: isActive('/user/packages') }" role="presentation">
          <router-link to="/user/packages" class="sidebar-menu-list__link">
            <span class="icon"><i class="fas fa-graduation-cap"></i></span>
            <span class="text">Courses Packages</span>
          </router-link>
        </li>
        <li class="sidebar-menu-list__item" :class="{ active: isActive('/user/courses') }" role="presentation">
          <router-link to="/user/courses" class="sidebar-menu-list__link">
            <span class="icon"><i class="fas fa-play-circle"></i></span>
            <span class="text">Courses</span>
          </router-link>
        </li>
        <li class="sidebar-menu-list__item has-dropdown" :class="{ active: isDropdownActive(['/user/referral', '/user/affiliate-income', '/user/partner-program']) }" role="presentation">
          <a href="javascript:void(0)" class="sidebar-menu-list__link">
            <span class="icon"><i class="fas fa-users"></i></span>
            <span class="text">My Team</span>
          </a>
          <div class="sidebar-submenu">
            <ul class="sidebar-submenu-list">
              <li class="sidebar-submenu-list__item" :class="{ active: isActive('/user/referral') }">
                <router-link to="/user/referral" class="sidebar-submenu-list__link">
                  <span class="text">Referral</span>
                </router-link>
              </li>
              <li class="sidebar-submenu-list__item" :class="{ active: isActive('/user/affiliate-income') }">
                <router-link to="/user/affiliate-income" class="sidebar-submenu-list__link">
                  <span class="text">Affiliate Income</span>
                </router-link>
              </li>
              <li class="sidebar-submenu-list__item" :class="{ active: isActive('/user/partner-program') }">
                <router-link to="/user/partner-program" class="sidebar-submenu-list__link">
                  <span class="text">Partner Program</span>
                </router-link>
              </li>
            </ul>
          </div>
        </li>
        <li class="sidebar-menu-list__item" :class="{ active: isActive('/user/certificates') }" role="presentation">
          <router-link to="/user/certificates" class="sidebar-menu-list__link">
            <span class="icon"><i class="fas fa-certificate"></i></span>
            <span class="text">Certificates</span>
          </router-link>
        </li>
        <li class="sidebar-menu-list__item has-dropdown" :class="{ active: isDropdownActive(['/user/withdraw', '/user/withdraw/history']) }" role="presentation">
          <a href="javascript:void(0)" class="sidebar-menu-list__link">
            <span class="icon"><i class="fas fa-hand-holding-usd"></i></span>
            <span class="text">Withdraw</span>
          </a>
          <div class="sidebar-submenu">
            <ul class="sidebar-submenu-list">
              <li class="sidebar-submenu-list__item" :class="{ active: isActive('/user/withdraw') && !isActive('/user/withdraw/history') }">
                <router-link to="/user/withdraw" class="sidebar-submenu-list__link">
                  <span class="text">Withdraw Money</span>
                </router-link>
              </li>
              <li class="sidebar-submenu-list__item" :class="{ active: isActive('/user/withdraw/history') }">
                <router-link to="/user/withdraw/history" class="sidebar-submenu-list__link">
                  <span class="text">Withdraw Log</span>
                </router-link>
              </li>
            </ul>
          </div>
        </li>

        <li class="sidebar-menu-list__item" :class="{ active: isActive('/user/transactions') }" role="presentation">
          <router-link to="/user/transactions" class="sidebar-menu-list__link">
            <span class="icon"><i class="fas fa-random"></i></span>
            <span class="text">Transactions</span>
          </router-link>
        </li>

        <li class="sidebar-menu-list__item has-dropdown" :class="{ active: isDropdownActive(['/user/ticket', '/user/ticket/open']) }" role="presentation">
          <a href="javascript:void(0)" class="sidebar-menu-list__link">
            <span class="icon"><i class="fas fa-headset"></i></span>
            <span class="text">Support Ticket</span>
          </a>
          <div class="sidebar-submenu">
            <ul class="sidebar-submenu-list">
              <li class="sidebar-submenu-list__item" :class="{ active: isActive('/user/ticket/open') }">
                <router-link to="/user/ticket/open" class="sidebar-submenu-list__link">
                  <span class="text">Open New Ticket</span>
                </router-link>
              </li>
              <li class="sidebar-submenu-list__item" :class="{ active: isActive('/user/ticket') && !isActive('/user/ticket/open') }">
                <router-link to="/user/ticket" class="sidebar-submenu-list__link">
                  <span class="text">My Tickets</span>
                </router-link>
              </li>
            </ul>
          </div>
        </li>

        <li class="sidebar-menu-list__item" :class="{ active: isActive('/user/leaderboard') }" role="presentation">
          <router-link to="/user/leaderboard" class="sidebar-menu-list__link">
            <span class="icon"><i class="fas fa-trophy"></i></span>
            <span class="text">Leaderboard</span>
          </router-link>
        </li>
        <li class="sidebar-menu-list__item" :class="{ active: isActive('/user/customer-support') }" role="presentation">
          <router-link to="/user/customer-support" class="sidebar-menu-list__link">
            <span class="icon"><i class="fas fa-headset"></i></span>
            <span class="text">Customer Support</span>
          </router-link>
        </li>
        <li class="sidebar-menu-list__item" :class="{ active: isActive('/user/change-password') }" role="presentation">
          <router-link to="/user/change-password" class="sidebar-menu-list__link">
            <span class="icon"><i class="fas fa-lock"></i></span>
            <span class="text">Password Change</span>
          </router-link>
        </li>
        <li class="sidebar-menu-list__item" :class="{ active: isActive('/user/twofactor') }" role="presentation">
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
import { ref, onMounted, watch, nextTick } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { authService } from '../../services/authService'

export default {
  name: 'UserSidebar',
  setup() {
    const router = useRouter()
    const route = useRoute()
    const siteLogo = ref('/assets/images/logo_icon/logo.png')

    // Helper function to check if a route is active
    const isActive = (path) => {
      if (Array.isArray(path)) {
        return path.some(p => route.path === p || route.path.startsWith(p + '/'))
      }
      return route.path === path || route.path.startsWith(path + '/')
    }

    // Check if any submenu item is active (for dropdowns)
    const isDropdownActive = (paths) => {
      return paths.some(path => isActive(path))
    }

    // Auto-open dropdowns when submenu items are active
    const openActiveDropdowns = () => {
      nextTick(() => {
        const dropdowns = document.querySelectorAll('.has-dropdown')
        dropdowns.forEach(dropdown => {
          const submenu = dropdown.querySelector('.sidebar-submenu')
          const activeItem = submenu?.querySelector('.sidebar-submenu-list__item.active')
          if (activeItem && submenu) {
            dropdown.classList.add('active')
            submenu.style.display = 'block'
          }
        })
      })
    }

    const handleLogout = async () => {
      try {
        await authService.logout()
        router.push('/')
      } catch (error) {
        console.error('Logout error:', error)
        router.push('/')
      }
    }

    // Watch route changes to open active dropdowns
    watch(() => route.path, () => {
      openActiveDropdowns()
    })

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
      
      // Open active dropdowns on mount
      openActiveDropdowns()
    })

    return {
      siteLogo,
      handleLogout,
      isActive,
      isDropdownActive,
      route
    }
  }
}
</script>

<style scoped>
.sidebar-menu {
  background-color: #1e293b;
  width: 280px;
  min-height: 100vh;
  color: #94a3b8;
  font-family: 'Outfit', sans-serif;
  box-shadow: 4px 0 10px rgba(0, 0, 0, 0.05);
  display: flex !important;
  flex-direction: column;
}

.sidebar-logo {
  padding: 1.25rem 1.5rem;
  text-align: center;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  margin-bottom: 1rem;
  min-height: 56px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.sidebar-logo__link {
  display: block;
  width: 100%;
}

.sidebar-logo img {
  max-width: 160px;
  height: auto;
}

.sidebar-menu-list {
  list-style: none;
  padding: 0 1rem;
  margin: 0;
  flex: 1;
}

.sidebar-menu-list__item {
  margin-bottom: 0.5rem;
}

.sidebar-menu-list__link {
  display: flex;
  align-items: center;
  padding: 0.75rem 1rem;
  color: #94a3b8;
  text-decoration: none;
  border-radius: 12px;
  transition: all 0.2s;
  font-weight: 500;
  gap: 1rem;
}

.sidebar-menu-list__link:hover {
  background: rgba(255, 255, 255, 0.05);
  color: white;
}

.sidebar-menu-list__item.active > .sidebar-menu-list__link {
  background: #4f46e5;
  color: white;
  box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
}

.sidebar-menu-list__link .icon {
  width: 24px;
  min-width: 24px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
}

/* Ensure Font Awesome icons render in sidebar */
.sidebar-menu-list__link .icon i,
.sidebar-menu .icon i {
  font-family: 'Font Awesome 6 Free', 'Font Awesome 5 Free', FontAwesome !important;
  font-weight: 900 !important;
  font-style: normal !important;
  display: inline-block !important;
}

.sidebar-logo-text {
  font-weight: 700;
  font-size: 1.25rem;
  color: #fff;
  letter-spacing: 0.02em;
}

.sidebar-logo img {
  max-width: 100%;
  height: auto;
  max-height: 48px;
  object-fit: contain;
}

.sidebar-submenu {
  padding-left: 2.5rem;
  margin-top: 0.25rem;
}

.sidebar-submenu-list {
  list-style: none;
  padding: 0;
  border-left: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar-submenu-list__link {
  display: block;
  padding: 0.5rem 1rem;
  color: #64748b;
  text-decoration: none;
  font-size: 0.9rem;
  transition: all 0.2s;
}

.sidebar-submenu-list__link:hover {
  color: white;
}

.sidebar-submenu-list__item.active .sidebar-submenu-list__link {
  color: white;
  font-weight: 600;
}

/* Mobile Toggle */
@media (max-width: 991px) {
  .sidebar-menu {
    position: fixed;
    left: -280px;
    z-index: 1050;
    transition: all 0.3s;
  }
  .sidebar-menu.show-sidebar {
    left: 0;
  }
}
</style>
