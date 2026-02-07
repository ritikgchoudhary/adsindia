<template>
  <div class="why-choose-section my-120" v-if="chooseUsContent">
    <div class="container">
      <div class="row gy-5 justify-content-center flex-wrap-reverse">
        <div class="col-xl-6 col-lg-7 pe-xl-5">
          <div class="choose-thumb-container">
            <div class="why-choose-wrapper"></div>
            <div class="row gy-4 align-items-center">
              <div class="col-6">
                <div class="choose-item">
                  <div class="choose-item__thumb">
                    <img :src="chooseUsContent.thumb_one" alt="thumb one">
                  </div>
                </div>
              </div>
              <div class="col-6">
                <div class="choose-item">
                  <div class="choose-item__thumb">
                    <img :src="chooseUsContent.thumb_two" alt="thumb two">
                  </div>
                </div>
              </div>
              <div class="col-6">
                <div class="item-shape">
                  <div class="shape-one">
                    <img :src="chooseUsContent.thumb_four" alt="shape one">
                  </div>
                  <div class="shape-two">
                    <img src="/assets/templates/basic/images/shapes/shape-1.png" alt="shape two">
                  </div>

                  <div class="choose-btn">
                    <a :href="chooseUsContent.video_url" class="play-button popup-youtube">
                      <span class="icon"><i class="fas fa-play"></i></span>
                    </a>
                  </div>
                </div>
              </div>
              <div class="col-6">
                <div class="choose-item">
                  <div class="choose-item__thumb">
                    <img :src="chooseUsContent.thumb_three" alt="thumb four">
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-xl-6 col-lg-5 ps-lg-5">
          <div class="section-heading style-left">
            <span class="section-heading__subtitle">{{ chooseUsContent.heading }}</span>
            <h3 class="section-heading__title">{{ chooseUsContent.subheading }}</h3>
            <p class="section-heading__desc">{{ chooseUsContent.description }}</p>
          </div>
          <div>
            <div 
              v-for="(item, index) in chooseUsItems" 
              :key="index" 
              class="choose-card" 
              :class="getCardClass(index)"
            >
              <div class="choose-card__icon">
                <img :src="item.image" alt="">
              </div>
              <div class="choose-card__content">
                <h5 class="choose-card__title">{{ item.title }}</h5>
                <p class="choose-card__text">{{ item.subtitle }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { appService } from '../../services/appService'

export default {
  name: 'WhyChooseUsSection',
  setup() {
    const chooseUsContent = ref(null)
    const chooseUsItems = ref([])

    const getCardClass = (index) => {
      const classes = ['card-base-two-bg', 'card-warning-bg', 'card-success-bg']
      return classes[index % classes.length]
    }

    onMounted(async () => {
      try {
        const [contentRes, elementsRes] = await Promise.all([
          appService.getSections('why_choose_us'),
          appService.getSections('why_choose_us.element')
        ])
        chooseUsContent.value = contentRes.data?.content?.data_values || null
        chooseUsItems.value = elementsRes.data || []
      } catch (error) {
        console.error('Error loading why choose us:', error)
      }
    })

    return {
      chooseUsContent,
      chooseUsItems,
      getCardClass
    }
  }
}
</script>
