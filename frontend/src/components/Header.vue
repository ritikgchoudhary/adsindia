<template>
  <header class="header" id="header">
    <div class="container position-relative">
      <nav class="navbar navbar-expand-xl navbar-light">
        <router-link class="navbar-brand logo" to="/">
          <img :src="siteLogo" :alt="siteName">
        </router-link>
        <button class="navbar-toggler header-button" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                aria-label="Toggle navigation">
          <span id="hiddenNav"><i class="las la-bars"></i></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
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
              </div>
            </li>
            <li class="nav-item">
              <router-link class="nav-link" :class="{ active: $route.name === 'Home' }" to="/">
                Home
              </router-link>
            </li>
            <li class="nav-item" v-for="page in pages" :key="page.id">
              <router-link class="nav-link" :to="`/${page.slug}`">
                {{ page.name }}
              </router-link>
            </li>
            <li class="nav-item">
              <router-link class="nav-link" :class="{ active: $route.name === 'Campaigns' }" to="/campaigns">
                Campaigns
              </router-link>
            </li>
            <li class="nav-item">
              <router-link class="nav-link" :class="{ active: $route.name === 'Blogs' }" to="/blogs">
                Blog
              </router-link>
            </li>
            <li class="nav-item">
              <router-link class="nav-link" :class="{ active: $route.name === 'Contact' }" to="/contact">
                Contact
              </router-link>
            </li>
          </ul>
        </div>
        <div class="header-right">
          <div class="top-button d-flex flex-wrap justify-content-between align-items-center">
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
                      <img :src="accountModalData?.affiliate_image || '/assets/images/default.png'" alt="logo">
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
                      <router-link to="/login" class="btn btn--base">Login</router-link>
                      <router-link to="/register" class="btn btn-outline--white">Register</router-link>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-sm-6">
                <div class="account-modal-form advertiser-form">
                  <div class="account-modal-form-top">
                    <div class="image">
                      <img :src="accountModalData?.advertiser_image || '/assets/images/default.png'" alt="logo">
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
                      <router-link to="/advertiser/login" class="btn btn--base">Login</router-link>
                      <router-link to="/advertiser/register" class="btn btn-outline--white">Register</router-link>
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
import { ref, computed, onMounted } from 'vue'
import { appService } from '../services/appService'

export default {
  name: 'Header',
  setup() {
    const pages = ref([])
    const accountModalData = ref(null)
    const siteLogo = ref('/assets/images/logo.png')
    const siteName = ref('A22.com')
    const isAuthenticated = computed(() => !!localStorage.getItem('token'))

    onMounted(async () => {
      try {
        // Fetch pages and account modal data
        const [pagesRes, sectionsRes] = await Promise.all([
          appService.getCustomPages(),
          appService.getSections('account_modal')
        ])
        pages.value = pagesRes.data || []
        accountModalData.value = sectionsRes.data?.content?.data_values || null
      } catch (error) {
        console.error('Error loading header data:', error)
      }
    })

    return {
      pages,
      accountModalData,
      siteLogo,
      siteName,
      isAuthenticated
    }
  }
}
</script>
