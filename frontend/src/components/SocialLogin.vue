<template>
  <div class="social-login" v-if="socialLogins.length">
    <div class="social-login__divider">
      <span>Or login with</span>
    </div>
    <div class="social-login__buttons">
      <a 
        v-for="social in socialLogins" 
        :key="social.id"
        :href="social.url" 
        class="social-login__button"
        :class="`social-login__button--${social.provider}`"
      >
        <i :class="getSocialIcon(social.provider)"></i>
        {{ social.name }}
      </a>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { appService } from '../services/appService'

export default {
  name: 'SocialLogin',
  setup() {
    const socialLogins = ref([])

    const getSocialIcon = (provider) => {
      const icons = {
        'google': 'fab fa-google',
        'facebook': 'fab fa-facebook',
        'twitter': 'fab fa-twitter',
        'github': 'fab fa-github',
        'linkedin': 'fab fa-linkedin'
      }
      return icons[provider] || 'fas fa-sign-in-alt'
    }

    onMounted(async () => {
      try {
        // Load social login providers from API
        const response = await appService.getGeneralSettings()
        // This would come from your API - adjust based on your data structure
        // socialLogins.value = response.data?.social_logins || []
      } catch (error) {
        console.error('Error loading social logins:', error)
      }
    })

    return {
      socialLogins,
      getSocialIcon
    }
  }
}
</script>

<style>
/* Styles are loaded from external CSS files */
</style>
