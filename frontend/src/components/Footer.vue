<template>
  <footer class="footer-area">
    <div class="bg-area"></div>

    <div class="footer-area__shape-one">
      <img :src="'/assets/templates/basic/images/shapes/fs-3.png'" alt="">
    </div>
    <div class="footer-area__shape-two">
      <img :src="'/assets/templates/basic/images/shapes/fs-4.png'" alt="">
    </div>
    <div class="footer-area__shape-three">
      <img :src="'/assets/templates/basic/images/shapes/fs-1.png'" alt="">
    </div>
    <div class="footer-area__shape-four">
      <img :src="'/assets/templates/basic/images/shapes/fs-2.png'" alt="">
    </div>

    <div class="container">
      <div class="footer-item-wrapper">
        <div class="footer-item">
          <router-link to="/" class="footer-item__logo">
            <img :src="siteLogo" alt="Site Logo">
          </router-link>
          <p class="footer-item__desc">
            {{ footerContent?.description || '' }}
          </p>
        </div>
        <div class="footer-item">
          <h6 class="footer-item__title">Quick Links</h6>
          <ul class="footer-menu">
            <li class="footer-menu__item"><router-link to="/" class="footer-menu__link">Home</router-link></li>
            <li class="footer-menu__item"><router-link to="/register" class="footer-menu__link">Join As Affiliate</router-link></li>
            <li class="footer-menu__item"><router-link to="/advertiser/register" class="footer-menu__link">Join As Advertiser</router-link></li>
          </ul>
        </div>
        <div class="footer-item">
          <h6 class="footer-item__title">Useful Links</h6>
          <ul class="footer-menu">
            <li class="footer-menu__item" v-for="policy in policyPages" :key="policy.id">
              <router-link :to="`/policy/${policy.slug}`" class="footer-menu__link">
                {{ policy.title }}
              </router-link>
            </li>
          </ul>
        </div>
        <div class="footer-item">
          <div class="subscribe-item">
            <span class="subscribe-item__badge">{{ subscribeData?.heading || 'Subscribe' }}</span>
            <h6 class="subscribe-item__title">{{ subscribeData?.subheading || 'Get Updates' }}</h6>
            <form @submit.prevent="handleSubscribe" class="subscribe-form">
              <input v-model="subscribeEmail" type="email" class="form--control" placeholder="Enter email address" required>
              <button type="submit" class="icon">
                <i class="las la-paper-plane"></i>
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Bottom Footer -->
    <div class="bottom-footer py-3">
      <div class="container">
        <div class="flex-between gap-3">
          <div class="bottom-footer-text">
            Copyright &copy; {{ new Date().getFullYear() }} <router-link to="/">{{ siteName }}</router-link> All rights reserved
          </div>
          <ul class="social-list">
            <li class="social-list__item" v-for="icon in socialIcons" :key="icon.id">
              <a :href="icon.url || 'javascript:void(0);'" class="social-list__link flex-center" target="_blank" v-html="icon.social_icon + ' ' + (icon.title || '')">
              </a>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </footer>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { appService } from '../services/appService'

export default {
  name: 'Footer',
  setup() {
    const footerContent = ref(null)
    const subscribeData = ref(null)
    const policyPages = ref([])
    const socialIcons = ref([])
    const siteLogo = computed(() => '/assets/images/logo_icon/logo.png')
    const siteName = ref('A22.com')
    const subscribeEmail = ref('')

    onMounted(async () => {
      try {
        const [footerRes, policiesRes, sectionsRes, subscribeRes] = await Promise.all([
          appService.getSections('footer'),
          appService.getPolicies(),
          appService.getSections('social_icon.element'),
          appService.getSections('subscribe_section')
        ])
        footerContent.value = footerRes.data?.content?.data_values || null
        policyPages.value = policiesRes.data || []
        socialIcons.value = sectionsRes.data || []
        subscribeData.value = subscribeRes.data?.content?.data_values || null
      } catch (error) {
        console.error('Error loading footer data:', error)
      }
    })

    const handleSubscribe = async () => {
      try {
        await appService.submitContact({ email: subscribeEmail.value, type: 'subscribe' })
        subscribeEmail.value = ''
        if (window.notify) {
          window.notify('success', 'Subscribed successfully!')
        }
      } catch (error) {
        if (window.notify) {
          window.notify('error', 'Subscription failed')
        }
      }
    }

    return {
      footerContent,
      subscribeData,
      policyPages,
      socialIcons,
      siteLogo,
      siteName,
      subscribeEmail,
      handleSubscribe
    }
  }
}
</script>
