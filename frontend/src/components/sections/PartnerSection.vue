<template>
  <div class="partner-section my-120" >
    <div class="container">
      <div class="section-heading text-center">
        <span class="section-heading__subtitle">{{ (partnerContent && partnerContent.heading) || '' }}</span>
        <h3 class="section-heading__title">{{ (partnerContent && partnerContent.subheading) || '' }}</h3>
        <p class="section-heading__desc">{{ (partnerContent && partnerContent.description) || '' }}</p>
      </div>

      <div class="partner-wrapper">
        <div class="company-list">
          <div v-for="(partner, index) in partners" :key="index" class="company-name">
            <div class="thumb">
              <img :src="$getImage('partner_section', partner?.data_values?.logo ?? partner?.logo)" alt="Partner Logo">
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
  name: 'PartnerSection',
  setup() {
    const partnerContent = ref(null)
    const partners = ref([])

    onMounted(async () => {
      try {
        const [contentRes, elementsRes] = await Promise.all([
          appService.getSections('partner_section'),
          appService.getSections('partner_section.element')
        ])
        partnerContent.value = appService.getSectionContent(contentRes)
        partners.value = elementsRes?.data ?? []
      } catch (error) {
        console.error('Error loading partner section:', error)
      }
    })

    return {
      partnerContent,
      partners
    }
  }
}
</script>
