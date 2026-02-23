<template>
  <div class="cookies-card text-center" :class="{ hide: !visible }">
    <div class="cookies-card__icon bg--base">
      <i class="las la-cookie-bite"></i>
    </div>
    <p class="mt-4 cookies-card__content">
      {{ cookieData?.short_desc || 'We use cookies to enhance your experience.' }}
      <router-link to="/cookie-policy" target="_blank" class="text--base">Learn more</router-link>
    </p>
    <div class="cookies-card__btn mt-4">
      <a href="javascript:void(0)" @click="acceptCookie" class="btn btn--base w-100 policy">Allow</a>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { appService } from '../services/appService'

export default {
  name: 'CookieNotice',
  setup() {
    const visible = ref(false)
    const cookieData = ref(null)

    onMounted(async () => {
      if (getCookie('gdpr_cookie')) {
        return
      }

      try {
        const response = await appService.getSections('cookie')
        cookieData.value = response.data?.content?.data_values || null
        setTimeout(() => {
          visible.value = true
        }, 2000)
      } catch (error) {
        console.error('Error loading cookie data:', error)
      }
    })

    const getCookie = (name) => {
      const value = `; ${document.cookie}`;
      const parts = value.split(`; ${name}=`);
      if (parts.length === 2) return parts.pop().split(';').shift();
    }

    const acceptCookie = async () => {
      try {
        await appService.submitContact({ type: 'cookie_accept' })
        document.cookie = 'gdpr_cookie=1; path=/; max-age=31536000'
        visible.value = false
      } catch (error) {
        console.error('Error accepting cookie:', error)
        document.cookie = 'gdpr_cookie=1; path=/; max-age=31536000'
        visible.value = false
      }
    }

    return {
      visible,
      cookieData,
      acceptCookie
    }
  }
}
</script>
