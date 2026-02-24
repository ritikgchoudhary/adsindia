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
            {{ footerContent?.description ?? '' }}
          </p>
        </div>
        <div class="footer-item">
          <h6 class="footer-item__title">Quick Links</h6>
          <ul class="footer-menu">
            <li class="footer-menu__item"><a href="#home" class="footer-menu__link" @click.prevent="scrollTo('home')">Home</a></li>
            <li class="footer-menu__item"><a href="#courses" class="footer-menu__link" @click.prevent="scrollTo('courses')">Courses</a></li>
            <li class="footer-menu__item"><a href="#contact" class="footer-menu__link" @click.prevent="scrollTo('contact')">Contact</a></li>
            <li class="footer-menu__item"><router-link to="/register" class="footer-menu__link">Join As Affiliate</router-link></li>
          </ul>
        </div>
        <div class="footer-item">
          <h6 class="footer-item__title">Useful Links</h6>
          <ul class="footer-menu">
            <li class="footer-menu__item" v-for="policy in policyPages" :key="policy.id || policy.slug">
              <router-link :to="`/policy/${policy.slug}`" class="footer-menu__link">
                {{ policy?.data_values?.title ?? policy?.title ?? '' }}
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
            <li class="social-list__item" v-for="(icon, idx) in socialIcons" :key="icon.id || idx">
              <a :href="socialUrl(icon)" class="social-list__link flex-center" target="_blank" rel="noopener noreferrer">
                <span v-if="socialIconHtml(icon)" v-html="socialIconHtml(icon)"></span>
                <span v-if="socialTitle(icon)">{{ socialTitle(icon) }}</span>
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
import { useRoute, useRouter } from 'vue-router'
import { appService } from '../services/appService'

export default {
  name: 'Footer',
  setup() {
    const route = useRoute()
    const router = useRouter()
    const footerContent = ref(null)
    const subscribeData = ref(null)
    const policyPages = ref([])
    const socialIcons = ref([])
    const siteLogo = ref('/assets/images/logo_icon/logo.png?v=' + new Date().getTime())
    const siteName = ref('Ads Skill India')
    const subscribeEmail = ref('')

    onMounted(async () => {
      try {
        const [generalRes, footerRes, policiesRes, sectionsRes, subscribeRes] = await Promise.all([
          appService.getGeneralSettings(),
          appService.getSections('footer.content'),
          appService.getPolicies(),
          appService.getSections('social_icon.element'),
          appService.getSections('subscribe_section.content')
        ])
        const gs = generalRes?.data || generalRes || {}
        if (gs.site_name) siteName.value = 'Ads Skill India'
        if (gs.logo) {
          const logoPath = gs.logo.startsWith('http') ? gs.logo : (gs.logo.startsWith('/') ? gs.logo : `/assets/images/logo_icon/${gs.logo}`)
          siteLogo.value = logoPath + '?v=' + new Date().getTime()
        }
        footerContent.value = appService.getSectionContent(footerRes) || null
        const policiesRaw = policiesRes?.data || policiesRes || []
        policyPages.value = Array.isArray(policiesRaw) ? policiesRaw : []
        const iconsRaw = sectionsRes?.data || sectionsRes || []
        socialIcons.value = Array.isArray(iconsRaw) ? iconsRaw : []
        subscribeData.value = appService.getSectionContent(subscribeRes) || null
      } catch (error) {
        console.error('Error loading footer data:', error)
      }
    })

    const policyTitle = (policy) => policy?.data_values?.title ?? policy?.title ?? ''
    const socialUrl = (icon) => icon?.data_values?.url ?? icon?.url ?? 'javascript:void(0);'
    const socialIconHtml = (icon) => icon?.data_values?.social_icon ?? icon?.social_icon ?? ''
    const socialTitle = (icon) => icon?.data_values?.title ?? icon?.title ?? ''

    const scrollTo = (sectionId) => {
      if (route.path !== '/') {
        router.push('/').then(() => {
          setTimeout(() => {
            const el = document.getElementById(sectionId)
            if (el) {
              const headerH = document.getElementById('header')?.offsetHeight || 80
              window.scrollTo({ top: el.getBoundingClientRect().top + window.scrollY - headerH, behavior: 'smooth' })
            }
          }, 300)
        })
      } else {
        if (sectionId === 'home') { window.scrollTo({ top: 0, behavior: 'smooth' }); return }
        const el = document.getElementById(sectionId)
        if (el) {
          const headerH = document.getElementById('header')?.offsetHeight || 80
          window.scrollTo({ top: el.getBoundingClientRect().top + window.scrollY - headerH, behavior: 'smooth' })
        }
      }
    }

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
      scrollTo,
      handleSubscribe,
      policyTitle,
      socialUrl,
      socialIconHtml,
      socialTitle
    }
  }
}
</script>

<style scoped>
.footer-area {
  padding: 80px 0 0;
  position: relative;
  overflow: hidden;
}

@media (max-width: 991px) {
  .footer-item-wrapper {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 30px;
  }
}

@media (max-width: 767px) {
  .footer-area { padding: 40px 0 0; }
  .footer-item-wrapper {
    display: grid !important;
    grid-template-columns: repeat(2, 1fr) !important;
    text-align: left !important;
    gap: 25px !important;
  }
  
  /* Logo and description full width */
  .footer-item:first-child { grid-column: 1 / -1; text-align: center !important; }
  /* Newsletter section full width */
  .footer-item:last-child { grid-column: 1 / -1; text-align: center !important; }

  .footer-item__logo img { max-height: 40px; }
  .footer-item__desc { font-size: 0.8rem; margin: 10px auto 0; max-width: 300px; }
  .footer-item__title { font-size: 0.95rem; margin-bottom: 10px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 5px; }
  .footer-menu__link { font-size: 0.8rem; }
  .footer-menu { padding-left: 0; list-style: none; }
  .footer-menu__item { margin-bottom: 6px; }

  .subscribe-item { max-width: 100%; margin: 0 auto; }
  .subscribe-item__badge { font-size: 0.7rem; }
  .subscribe-item__title { font-size: 1.1rem; margin-bottom: 15px; }
  .subscribe-form .form--control { font-size: 0.85rem; padding: 0.6rem 1rem !important; border-radius: 0.65rem !important; }
  
  .bottom-footer { padding: 15px 0; border-top: 1px solid rgba(255,255,255,0.05); }
  .flex-between { flex-direction: column !important; gap: 12px !important; align-items: center !important; }
  .bottom-footer-text { font-size: 0.7rem; text-align: center; opacity: 0.7; }
  .social-list { justify-content: center; gap: 10px !important; }
  
  .footer-area__shape-one, .footer-area__shape-two, 
  .footer-area__shape-three, .footer-area__shape-four {
    display: none;
  }
}
</style>
