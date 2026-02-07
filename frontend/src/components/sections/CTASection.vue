<template>
  <div class="cta-section my-120" v-if="ctaData">
    <div class="container">
      <div class="cta-wrapper">
        <div class="cta-content">
          <span class="cta-content__badge">{{ ctaData.title }}</span>
          <h3 class="cta-content__title">{{ ctaData.subtitle }}</h3>
          <p class="cta-content__desc">{{ ctaData.description }}</p>

          <div class="cta-content__btn">
            <router-link :to="ctaData.button_url || '/'" class="btn btn--base pill">
              {{ ctaData.button_name }}
              <span class="btn-icon"><i class="las la-arrow-right"></i></span>
            </router-link>
          </div>
        </div>

        <div class="cta-thumb-wrapper" v-if="ctaData.image">
          <div class="thumb">
            <img :src="ctaData.image" alt="CTA Image">
          </div>
        </div>

        <div class="shape">
          <img :src="'/assets/templates/basic/images/shapes/cta-2.png'" alt="img" />
        </div>
        <div class="shape-two">
          <img :src="'/assets/templates/basic/images/shapes/cta-3.png'" alt="img" />
        </div>
        <div class="shape-three">
          <img :src="'/assets/templates/basic/images/shapes/cta-4.png'" alt="img" />
        </div>
        <div class="shape-four">
          <img :src="'/assets/templates/basic/images/shapes/cta-4.png'" alt="img" />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { appService } from '../../services/appService'

export default {
  name: 'CTASection',
  setup() {
    const ctaData = ref(null)

    onMounted(async () => {
      try {
        const response = await appService.getSections('cta_section')
        ctaData.value = response.data?.content?.data_values || null
      } catch (error) {
        console.error('Error loading CTA section:', error)
      }
    })

    return {
      ctaData
    }
  }
}
</script>
