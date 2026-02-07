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
            <li class="nav-item d-block d-xl-none">
              <div class="top-button d-flex flex-wrap justify-content-between align-items-center">
                <ul class="login-registration-list d-flex flex-wrap align-items-center">
                  <li class="login-registration-list__item" v-if="!isAuthenticated">
                    <router-link to="/login" class="login-registration-list__link login">
                      <span class="login-registration-list__icon">
                        <i class="las la-file-export"></i>
                      </span>
                      Login
                    </router-link>
                  </li>
                </ul>
                <LanguageSelector class="my-2" />
              </div>
            </li>
            <li class="nav-item">
              <router-link class="nav-link" :class="{ active: $route.path === '/' }" to="/">
                Home
              </router-link>
            </li>
            <li class="nav-item" v-for="page in pages" :key="page.id">
              <router-link class="nav-link" :to="`/${page.slug}`">
                {{ page.name }}
              </router-link>
            </li>
            <li class="nav-item">
              <router-link class="nav-link" :class="{ active: $route.path === '/campaigns' }" to="/campaigns">
                Campaigns
              </router-link>
            </li>
            <li class="nav-item">
              <router-link class="nav-link" :class="{ active: $route.path === '/blogs' }" to="/blogs">
                Blog
              </router-link>
            </li>
            <li class="nav-item">
              <router-link class="nav-link" :class="{ active: $route.path === '/contact' }" to="/contact">
                Contact
              </router-link>
            </li>
          </ul>
        </div>
        <div class="header-right">
          <div class="top-button d-flex flex-wrap justify-content-between align-items-center">
            <LanguageSelector class="d-none d-xl-block me-3" />
            
            <template v-if="!isAuthenticated">
              <a href="#accountModal" data-bs-toggle="modal" class="btn btn--base pill">
                Account
                <span class="btn-icon">
                  <i class="las la-user"></i>
                </span>
              </a>
            </template>
            <template v-else>
              <router-link to="/dashboard" class="btn btn--base pill">
                Dashboard
                <span class="btn-icon">
                  <i class="las la-user"></i>
                </span>
              </router-link>
            </template>
          </div>
        </div>
      </nav>
    </div>

    <!-- Account Modal -->
    <div class="modal fade custom--modal account--modal" id="accountModal" tabindex="-1" aria-labelledby="accountModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-body">
            <div class="row gy-3">
              <div class="col-sm-6">
                <div class="account-modal-form">
                  <div class="account-modal-form-top">
                    <div class="image">
                      <img :src="$getImage('account_modal', accountModalData?.affiliate_image) || '/assets/images/default.png'" alt="logo">
                    </div>
                    <p class="title">
                      {{ accountModalData?.affiliate_heading || 'Join As Affiliate' }}
                    </p>
                  </div>
                  <div class="account-modal-form-body">
                    <p class="text">
                      {{ accountModalData?.affiliate_description || 'Join as an affiliate and start earning' }}
                    </p>
                    <div class="flex-center gap-2">
                      <router-link to="/login" class="btn btn--base" @click="closeModal">Login</router-link>
                      <router-link to="/register" class="btn btn-outline--white" @click="closeModal">Register</router-link>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-sm-6">
                <div class="account-modal-form advertiser-form">
                  <div class="account-modal-form-top">
                    <div class="image">
                      <img :src="$getImage('account_modal', accountModalData?.advertiser_image) || '/assets/images/default.png'" alt="logo">
                    </div>
                    <p class="title">
                      {{ accountModalData?.advertiser_heading || 'Join As Advertiser' }}
                    </p>
                  </div>
                  <div class="account-modal-form-body">
                    <p class="text">
                      {{ accountModalData?.advertiser_description || 'Join as an advertiser and promote your products' }}
                    </p>
                    <div class="flex-center gap-2">
                      <router-link to="/advertiser/login" class="btn btn--base" @click="closeModal">Login</router-link>
                      <router-link to="/advertiser/register" class="btn btn-outline--white" @click="closeModal">Register</router-link>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { appService } from '../services/appService'
import LanguageSelector from './LanguageSelector.vue'

export default {
  name: 'Header',
  components: {
    LanguageSelector
  },
  setup() {
    const pages = ref([])
    const accountModalData = ref(null)
    const siteLogo = computed(() => {
      return `/assets/images/logo_icon/logo.png`
    })
    const siteName = ref('A22.com')
    const isAuthenticated = computed(() => !!localStorage.getItem('token'))
    const isSticky = ref(false)
    const isSidebarOpen = ref(false)

    const toggleSidebar = () => {
      isSidebarOpen.value = !isSidebarOpen.value
      document.body.classList.toggle('scroll-hide-sm')
      document.querySelector('.body-overlay')?.classList.toggle('show')
    }

    const closeModal = () => {
      const modal = document.getElementById('accountModal')
      if (modal) {
        const modalInstance = bootstrap.Modal.getInstance(modal)
        if (modalInstance) modalInstance.hide()
      }
    }

    const handleScroll = () => {
      isSticky.value = window.scrollY >= 100
    }

    onMounted(async () => {
      window.addEventListener('scroll', handleScroll)
      
      try {
        const [pagesRes, sectionsRes] = await Promise.all([
          appService.getCustomPages(),
          appService.getSections('account_modal')
        ])
        pages.value = pagesRes.data || []
        accountModalData.value = sectionsRes.data?.content?.data_values || null
      } catch (error) {
        console.error('Error loading header data:', error)
      }
      
      // Close sidebar when overlay is clicked
      document.querySelector('.body-overlay')?.addEventListener('click', () => {
        isSidebarOpen.value = false
        document.body.classList.remove('scroll-hide-sm')
        document.querySelector('.body-overlay')?.classList.remove('show')
      })
    })

    onUnmounted(() => {
      window.removeEventListener('scroll', handleScroll)
    })

    return {
      pages,
      accountModalData,
      siteLogo,
      siteName,
      isAuthenticated,
      isSticky,
      isSidebarOpen,
      toggleSidebar,
      closeModal
    }
  }
}
</script>
