<template>
  <div class="about-section my-120" v-if="aboutData">
    <div class="container">
      <div class="row gy-4 justify-content-center">
        <div class="col-xl-6 pe-xxl-5">
          <div class="about-thumb-wrapper">
            <div class="left-thumb-wrapper">
              <div class="left-thumb"></div>
              <div class="border-shape"></div>
            </div>
            <div class="about-thumb-wrapper__thumb" v-if="aboutData.image">
              <img :src="$getImage('about', aboutData.image)" alt="Affiliate Image" />
            </div>
            <div class="thumb-text-wrapper">
              <span class="text base--outline" v-if="aboutData.tag_text_one">{{ aboutData.tag_text_one }}</span>
              <span class="text success--outline" v-if="aboutData.tag_text_two">{{ aboutData.tag_text_two }}</span>
              <span class="text warning--outline" v-if="aboutData.tag_text_three">{{ aboutData.tag_text_three }}</span>
              <span class="text success--outline" v-if="aboutData.tag_text_four">{{ aboutData.tag_text_four }}</span>
              <span class="text base--outline" v-if="aboutData.tag_text_five">{{ aboutData.tag_text_five }}</span>
            </div>
          </div>
        </div>
        <div class="col-xl-6 ps-lg-5">
          <div class="about-content">
            <span class="about-content__badge" v-if="aboutData.badge">{{ aboutData.badge }}</span>
            <h2 class="about-content__title" v-if="aboutData.heading">{{ aboutData.heading }}</h2>
            <p class="about-content__desc" v-if="aboutData.subheading">{{ aboutData.subheading }}</p>
            <ul class="text-list" v-if="aboutElements.length">
              <li class="text-list__item" v-for="(element, index) in aboutElements" :key="index">
                {{ element.data_values.feature }}
              </li>
            </ul>
            <div class="about-content__btn" v-if="aboutData.button_name">
              <router-link :to="aboutData.button_url || '/'" class="btn btn--base pill">
                {{ aboutData.button_name }}
                <span class="btn-icon"><i class="las la-arrow-right"></i></span>
              </router-link>
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
  name: 'AboutSection',
  setup() {
    const aboutData = ref(null)
    const aboutElements = ref([])

    onMounted(async () => {
      try {
        const [contentRes, elementsRes] = await Promise.all([
          appService.getSections('about'),
          appService.getSections('about.element')
        ])
        aboutData.value = contentRes.data?.content?.data_values || null
        aboutElements.value = elementsRes.data || []
      } catch (error) {
        console.error('Error loading about section:', error)
      }
    })

    return {
      aboutData,
      aboutElements
    }
  }
}
</script>
