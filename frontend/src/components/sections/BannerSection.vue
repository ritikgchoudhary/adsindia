<template>
  <section class="banner-section bg-img" :data-background-image="`/assets/templates/basic/images/shapes/banner-1.png`" :style="bannerStyle">
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
      <img :src="$getImage('banner', bannerData.image)" alt="img" />
    </div>
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-12">
          <div class="banner-content">
            <h4 class="banner-content__subtitle">
              {{ bannerData?.subtitle || 'Affiliate Marketing Platform' }}
            </h4>
            <h1 class="banner-content__title">
              {{ bannerData?.title || 'Earn Money With Us' }}
              <span class="icon" v-if="bannerData?.image_three">
                <img :src="$getImage('banner', bannerData.image_three)" alt="img" />
                {{ bannerData?.affiliate_text || '' }}
              </span>
              <span class="text--base text">
                <span class="text-container">
                  <span v-for="(char, index) in marketingChars" :key="index" class="letter">
                    <img v-if="index === 2" :src="'/assets/templates/basic/images/thumbs/st.png'" alt="img" />
                    {{ char }}
                  </span>
                </span>
              </span>
            </h1>
            <p class="banner-content__desc">
              {{ bannerData?.description || 'Join our affiliate program, promote campaigns and earn commission on every conversion. No investment required.' }}
            </p>
            <div class="banner-content__btn">
              <router-link :to="bannerData?.button_url || '/register'" class="btn btn--base pill">
                {{ bannerData?.button_name || 'Get Started' }}
                <span class="btn-icon">
                  <i class="las la-arrow-right"></i>
                </span>
              </router-link>
            </div>
            <div class="banner-content__shape" v-if="bannerData?.image_two">
                <img :src="$getImage('banner', bannerData.image_two)" alt="img" />
            </div>
            <div class="banner-content__shape-two" v-if="bannerData?.image_one">
                <img :src="$getImage('banner', bannerData.image_one)" alt="img" />
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="brand-wrapper" ref="brandWrapper" v-if="bannerFeatures.length">
      <span class="title" v-for="(feature, index) in bannerFeatures" :key="index">
        {{ feature.feature_title }}
      </span>
    </div>
  </section>
</template>

<script>
import { ref, computed, onMounted, nextTick } from 'vue'
import { appService } from '../../services/appService'

export default {
  name: 'BannerSection',
  setup() {
    const bannerData = ref(null)
    const bannerFeatures = ref([])
    const brandWrapper = ref(null)

    const marketingChars = computed(() => {
      const text = bannerData.value?.marketing_text || ''
      return text.replace(/<[^>]*>?/gm, '').split('')
    })

    const bannerStyle = computed(() => {
      return {
        backgroundImage: `url(/assets/templates/basic/images/shapes/banner-1.png)`
      }
    })

    onMounted(async () => {
      try {
        const [bannerRes, featuresRes] = await Promise.all([
          appService.getSections('banner'),
          appService.getSections('banner.element')
        ])
        bannerData.value = bannerRes.data?.data_values || bannerRes.data?.content?.data_values || null
        bannerFeatures.value = Array.isArray(featuresRes.data) ? featuresRes.data : (featuresRes.data?.data || [])

        await nextTick()
        
        // Initialize Slick Slider for brand-wrapper
        if (window.jQuery && window.jQuery.fn.slick) {
            const $ = window.jQuery
            $(brandWrapper.value).slick({
                arrows: false,
                infinite: true,
                slidesToShow: 8,
                slidesToScroll: 1,
                speed: 4000,
                cssEase: "linear",
                autoplay: true,
                autoplaySpeed: 0,
                adaptiveHeight: false,
                pauseOnDotsHover: false,
                pauseOnHover: true,
                pauseOnFocus: true,
                responsive: [
                    {
                        breakpoint: 1399,
                        settings: {
                            variableWidth: true,
                        },
                    },
                ],
            })
        }
      } catch (error) {
        console.error('Error loading banner:', error)
      }
    })

    return {
      bannerData,
      bannerFeatures,
      marketingChars,
      bannerStyle,
      brandWrapper
    }
  }
}
</script>
