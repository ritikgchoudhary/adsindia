<template>
  <div class="benefit-section my-120" v-if="benefitContent">
    <div class="container">
      <div class="row gy-5 justify-content-center">
        <div class="col-lg-6 pe-xl-5">
          <div class="section-heading style-left">
            <span class="section-heading__subtitle">
              {{ benefitContent.heading }}
            </span>
            <h3 class="section-heading__title">
              {{ benefitContent.subheading }}
            </h3>
            <p class="section-heading__desc">
              {{ benefitContent.description }}
            </p>
          </div>

          <ul class="benefit-list" v-if="benefitItems.length">
            <li v-for="(item, index) in benefitItems" :key="index" class="benefit-list__item">
              <span v-if="item.data_values">{{ item.data_values.benefit }}</span>
              <span v-else>{{ item.benefit }}</span>
            </li>
          </ul>
        </div>
        <div class="col-lg-6 ps-xl-5">
          <div class="benefit-wrapper">
            <div class="benefit-wrapper__shape"></div>
            <div class="benefit-thumb-item">
              <div class="thumb" v-if="benefitContent.thumb">
                <img :src="$getImage('benefit_section', benefitContent.thumb)" alt="Benefit Image" class="fit-image">
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
  name: 'BenefitSection',
  setup() {
    const benefitContent = ref(null)
    const benefitItems = ref([])

    onMounted(async () => {
      try {
        const [contentRes, elementsRes] = await Promise.all([
          appService.getSections('benefit_section'),
          appService.getSections('benefit_section.element')
        ])
        benefitContent.value = contentRes.data?.content?.data_values || null
        benefitItems.value = elementsRes.data || []
      } catch (error) {
        console.error('Error loading benefit section:', error)
      }
    })

    return {
      benefitContent,
      benefitItems
    }
  }
}
</script>
