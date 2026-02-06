<template>
  <section class="banner-section bg-img" :data-background-image="`/assets/templates/basic/images/shapes/banner-1.png`">
    <div class="shape-one">
      <img :src="'/assets/templates/basic/images/shapes/bs-1.png'" alt="img" />
    </div>
    <div class="shape-two">
      <img :src="'/assets/templates/basic/images/shapes/bs-2.png'" alt="img" />
    </div>
    <div class="shape-three">
      <img :src="'/assets/templates/basic/images/shapes/bs-3.png'" alt="img" />
    </div>
    <div class="banner-thumb" v-if="bannerData?.image">
      <img :src="bannerData.image" alt="img" />
    </div>
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-12">
          <div class="banner-content">
            <h4 class="banner-content__subtitle">
              {{ bannerData?.subtitle || '' }}
            </h4>
            <h1 class="banner-content__title" v-html="bannerTitle">
            </h1>
            <p class="banner-content__desc">
              {{ bannerData?.description || '' }}
            </p>
            <div class="banner-content__btn">
              <router-link :to="bannerData?.button_url || '/register'" class="btn btn--base pill">
                {{ bannerData?.button_name || 'Get Started' }}
                <span class="btn-icon">
                  <i class="las la-arrow-right"></i>
                </span>
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="brand-wrapper" v-if="bannerFeatures.length">
      <span class="title" v-for="(feature, index) in bannerFeatures" :key="index">
        {{ feature.feature_title }}
      </span>
    </div>
  </section>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { appService } from '../../services/appService'

export default {
  name: 'BannerSection',
  setup() {
    const bannerData = ref(null)
    const bannerFeatures = ref([])

    const bannerTitle = computed(() => {
      if (!bannerData.value) return ''
      const title = bannerData.value.title || ''
      const affiliateText = bannerData.value.affiliate_text || ''
      const marketingText = bannerData.value.marketing_text || ''
      
      return `${title} <span class="icon"><img src="${bannerData.value.image_three || ''}" alt="img" /> ${affiliateText}</span> <span class="text--base text"><span class="text-container">${marketingText}</span></span>`
    })

    onMounted(async () => {
      try {
        const [bannerRes, featuresRes] = await Promise.all([
          appService.getSections('banner'),
          appService.getSections('banner.element')
        ])
        bannerData.value = bannerRes.data?.content?.data_values || null
        bannerFeatures.value = featuresRes.data || []
      } catch (error) {
        console.error('Error loading banner:', error)
      }
    })

    return {
      bannerData,
      bannerFeatures,
      bannerTitle
    }
  }
}
</script>
