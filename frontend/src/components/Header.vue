<template>
  <header class="header" id="header" :class="{ 'fixed-header': isSticky }">
    <div class="container position-relative">
      <nav class="navbar navbar-expand-xl navbar-light">
        <router-link class="navbar-brand logo" to="/">
          <img :src="siteLogo" :alt="siteName">
        </router-link>
        <button class="navbar-toggler header-button" type="button" @click="toggleSidebar">
          <span id="hiddenNav"><i class="las la-bars"></i></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent" :class="{ show: isSidebarOpen }">
          <ul class="navbar-nav nav-menu w-100 align-items-xl-center justify-content-center">

            <!-- Mobile: Login/Register or User Menu (top of sidebar) -->
            <li class="nav-item d-block d-xl-none">
              <div class="top-button d-flex flex-wrap justify-content-between align-items-center">
                <template v-if="!isAuthenticated">
                  <div class="d-flex gap-2 w-100 mb-2">
                    <router-link to="/login" class="btn btn--base btn--sm flex-fill" @click="closeSidebar">
                      <i class="las la-sign-in-alt me-1"></i> Login
                    </router-link>
                    <router-link to="/register" class="btn btn-outline--base btn--sm flex-fill" @click="closeSidebar">
                      <i class="las la-user-plus me-1"></i> Register
                    </router-link>
                  </div>
                </template>
                <template v-else>
                  <div class="d-flex gap-2 w-100 mb-2">
                    <router-link to="/dashboard" class="btn btn--base btn--sm flex-fill" @click="closeSidebar">
                      <i class="las la-tachometer-alt me-1"></i> Dashboard
                    </router-link>
                    <button class="btn btn-outline--danger btn--sm flex-fill" @click="handleLogout">
                      <i class="las la-sign-out-alt me-1"></i> Logout
                    </button>
                  </div>
                </template>
              </div>
            </li>

            <!-- Navigation Links -->
            <li class="nav-item">
              <a class="nav-link" :class="{ active: activeSection === 'home' }" href="#home" @click.prevent="scrollTo('home')">
                <i class="las la-home me-1 d-xl-none"></i> Home
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" :class="{ active: activeSection === 'about' }" href="#about" @click.prevent="scrollTo('about')">
                <i class="las la-info-circle me-1 d-xl-none"></i> About
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" :class="{ active: activeSection === 'courses' }" href="#courses" @click.prevent="scrollTo('courses')">
                <i class="las la-graduation-cap me-1 d-xl-none"></i> Courses
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" :class="{ active: activeSection === 'blog' }" href="#blog" @click.prevent="scrollTo('blog')">
                <i class="las la-blog me-1 d-xl-none"></i> Blog
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" :class="{ active: activeSection === 'faq' }" href="#faq" @click.prevent="scrollTo('faq')">
                <i class="las la-question-circle me-1 d-xl-none"></i> FAQ
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" :class="{ active: activeSection === 'contact' }" href="#contact" @click.prevent="scrollTo('contact')">
                <i class="las la-envelope me-1 d-xl-none"></i> Contact
              </a>
            </li>
          </ul>
        </div>

        <!-- Desktop: Right side buttons -->
        <div class="header-right">
          <div class="top-button d-flex flex-wrap justify-content-between align-items-center gap-2">

            <!-- Not Logged In: Login + Register -->
            <template v-if="!isAuthenticated">
              <router-link to="/login" class="btn btn--base pill header-btn">
                <i class="las la-sign-in-alt me-1"></i> Login
              </router-link>
              <router-link to="/register" class="btn btn-outline--base pill header-btn">
                <i class="las la-user-plus me-1"></i> Register
              </router-link>
            </template>

            <!-- Logged In: User Dropdown -->
            <template v-else>
              <div class="header-user-dropdown" ref="userDropdownRef">
                <button class="btn btn--base pill header-btn" @click="toggleUserMenu">
                  <i class="las la-user-circle me-1"></i>
                  {{ userName || 'My Account' }}
                  <i class="las la-angle-down ms-1" style="font-size: 0.75rem;"></i>
                </button>
                <div class="header-dropdown-menu" :class="{ show: isUserMenuOpen }">
                  <router-link to="/dashboard" class="header-dropdown-item" @click="isUserMenuOpen = false">
                    <i class="las la-tachometer-alt"></i> Dashboard
                  </router-link>
                  <router-link to="/user/courses" class="header-dropdown-item" @click="isUserMenuOpen = false">
                    <i class="las la-play-circle"></i> My Courses
                  </router-link>
                  <router-link to="/user/profile-setting" class="header-dropdown-item" @click="isUserMenuOpen = false">
                    <i class="las la-user-cog"></i> Profile
                  </router-link>
                  <div class="header-dropdown-divider"></div>
                  <a href="#" class="header-dropdown-item text-danger" @click.prevent="handleLogout">
                    <i class="las la-sign-out-alt"></i> Logout
                  </a>
                </div>
              </div>
            </template>

          </div>
        </div>
      </nav>
    </div>
  </header>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { appService } from '../services/appService'
import api from '../services/api'

export default {
  name: 'Header',
  setup() {
    const route = useRoute()
    const router = useRouter()
    const siteLogo = ref('/assets/images/logo_icon/logo.png?v=' + new Date().getTime())
    const siteName = ref('Ads Skill India')
    const isAuthenticated = computed(() => !!localStorage.getItem('token'))
    const isSticky = ref(false)
    const isSidebarOpen = ref(false)
    const activeSection = ref('home')
    const isUserMenuOpen = ref(false)
    const userName = ref('')
    const userDropdownRef = ref(null)

    // ── Sidebar toggle ──
    const toggleSidebar = () => {
      isSidebarOpen.value = !isSidebarOpen.value
      document.body.classList.toggle('scroll-hide-sm')
      document.querySelector('.body-overlay')?.classList.toggle('show')
    }

    const closeSidebar = () => {
      if (isSidebarOpen.value) {
        isSidebarOpen.value = false
        document.body.classList.remove('scroll-hide-sm')
        document.querySelector('.body-overlay')?.classList.remove('show')
      }
    }

    // ── User dropdown toggle ──
    const toggleUserMenu = () => {
      isUserMenuOpen.value = !isUserMenuOpen.value
    }

    const closeUserMenu = (e) => {
      if (userDropdownRef.value && !userDropdownRef.value.contains(e.target)) {
        isUserMenuOpen.value = false
      }
    }

    // ── Smooth scroll navigation ──
    const scrollTo = (sectionId) => {
      closeSidebar()
      if (route.path !== '/') {
        router.push('/').then(() => {
          setTimeout(() => {
            const el = document.getElementById(sectionId)
            if (el) {
              const headerHeight = document.getElementById('header')?.offsetHeight || 80
              const top = el.getBoundingClientRect().top + window.scrollY - headerHeight
              window.scrollTo({ top, behavior: 'smooth' })
            }
          }, 300)
        })
      } else {
        if (sectionId === 'home') {
          window.scrollTo({ top: 0, behavior: 'smooth' })
        } else {
          const el = document.getElementById(sectionId)
          if (el) {
            const headerHeight = document.getElementById('header')?.offsetHeight || 80
            const top = el.getBoundingClientRect().top + window.scrollY - headerHeight
            window.scrollTo({ top, behavior: 'smooth' })
          }
        }
      }
    }

    // ── Active section highlight on scroll ──
    const updateActiveSection = () => {
      if (route.path !== '/') { activeSection.value = ''; return }
      const headerHeight = (document.getElementById('header')?.offsetHeight || 80) + 20
      // Check from bottom to top so the most-scrolled-to section wins
      const sectionIds = ['contact', 'faq', 'blog', 'courses', 'about', 'home']
      for (const id of sectionIds) {
        const el = document.getElementById(id)
        if (el) {
          const rect = el.getBoundingClientRect()
          if (rect.top <= headerHeight + 100) {
            activeSection.value = id
            return
          }
        }
      }
      activeSection.value = 'home'
    }

    // ── Logout ──
    const handleLogout = async () => {
      closeSidebar()
      isUserMenuOpen.value = false
      try {
        const token = localStorage.getItem('token')
        if (token) {
          await fetch('/api/logout', {
            method: 'GET',
            headers: { 'Authorization': 'Bearer ' + token }
          }).catch(() => {})
        }
      } catch (e) { /* ignore */ }
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      if (window.notify) window.notify('success', 'Logged out successfully')
      router.push('/')
      // Force reactivity
      setTimeout(() => location.reload(), 200)
    }

    const handleScroll = () => {
      isSticky.value = window.scrollY >= 100
      updateActiveSection()
    }

    onMounted(async () => {
      window.addEventListener('scroll', handleScroll)
      document.addEventListener('click', closeUserMenu)

      // Load site settings
      try {
        const generalRes = await appService.getGeneralSettings()
        const gs = generalRes?.data || generalRes || {}
        if (gs.site_name) {
          // Convert to Title Case if needed, or just use hardcoded
          // Better to just use the value from API but formatted? 
          // Re-reading user request: "Ads skill india ... small text title me krdo".
          // If the DB has "ADS SKILL INDIA", I should probably force it to "Ads Skill India"
          // regardless of DB. But simpler is to just capitalize key words.
          // Or just hardcode it since the user explicitly asked for this string.
          siteName.value = 'Ads Skill India' 
        }
        if (gs.logo) {
          const logoPath = gs.logo.startsWith('http') ? gs.logo : (gs.logo.startsWith('/') ? gs.logo : `/assets/images/logo_icon/${gs.logo}`)
          siteLogo.value = logoPath + '?v=' + new Date().getTime()
        }
      } catch (error) {
        console.error('Error loading header data:', error)
      }

      // Load user name if logged in
      if (isAuthenticated.value) {
        try {
          const res = await api.get('/user-info')
          const user = res.data?.data ?? res.data
          if (user) {
            userName.value = user.firstname || user.fullname || user.username || ''
          }
        } catch (e) { /* ignore */ }
      }

      // Close sidebar when overlay is clicked
      document.querySelector('.body-overlay')?.addEventListener('click', () => {
        closeSidebar()
      })
    })

    onUnmounted(() => {
      window.removeEventListener('scroll', handleScroll)
      document.removeEventListener('click', closeUserMenu)
    })

    return {
      route,
      siteLogo,
      siteName,
      isAuthenticated,
      isSticky,
      isSidebarOpen,
      activeSection,
      isUserMenuOpen,
      userName,
      userDropdownRef,
      toggleSidebar,
      closeSidebar,
      toggleUserMenu,
      scrollTo,
      handleLogout
    }
  }
}
</script>

<style scoped>
/* ── Header Buttons ── */
.header-btn {
  font-size: 0.875rem;
  padding: 8px 20px;
  font-weight: 600;
  transition: all 0.3s ease;
  white-space: nowrap;
}

.btn-outline--base {
  background: transparent;
  border: 2px solid var(--base-clr, #4f46e5);
  color: var(--base-clr, #4f46e5);
}
.btn-outline--base:hover {
  background: var(--base-clr, #4f46e5);
  color: #fff;
}

.btn-outline--danger {
  background: transparent;
  border: 2px solid #ef4444;
  color: #ef4444;
}
.btn-outline--danger:hover {
  background: #ef4444;
  color: #fff;
}

/* ── User Dropdown ── */
.header-user-dropdown {
  position: relative;
  z-index: 999;
}

.header-dropdown-menu {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  min-width: 220px;
  background: #1e293b;
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 12px;
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4);
  padding: 8px 0;
  opacity: 0;
  visibility: hidden;
  transform: translateY(-10px);
  transition: all 0.25s ease;
}

.header-dropdown-menu.show {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
}

.header-dropdown-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 18px;
  color: rgba(255, 255, 255, 0.85);
  text-decoration: none;
  font-size: 0.9rem;
  font-weight: 500;
  transition: all 0.2s ease;
}

.header-dropdown-item:hover {
  background: rgba(79, 70, 229, 0.15);
  color: #fff;
  padding-left: 22px;
}

.header-dropdown-item i {
  font-size: 1.1rem;
  width: 22px;
  text-align: center;
}

.header-dropdown-item.text-danger {
  color: #ef4444 !important;
}
.header-dropdown-item.text-danger:hover {
  background: rgba(239, 68, 68, 0.12);
  color: #f87171 !important;
}

.header-dropdown-divider {
  height: 1px;
  background: rgba(255, 255, 255, 0.1);
  margin: 6px 0;
}

/* ── Mobile nav icons ── */
@media (max-width: 1199.98px) {
  .nav-link i {
    font-size: 1.1rem;
  }
}
</style>
