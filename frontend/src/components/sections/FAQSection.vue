<template>
  <div class="faq-section my-120" v-if="faqContent">
    <div class="container">
      <div class="section-heading">
        <div class="left-thumb-wrapper">
          <div class="left-thumb"></div>
          <div class="border-shape"></div>
        </div>
        <div class="right-thumb-wrapper">
          <div class="right-thumb"></div>
          <div class="border-shape"></div>
        </div>
        <span class="section-heading__subtitle">{{ faqContent.heading }}</span>
        <h3 class="section-heading__title">{{ faqContent.subheading }}</h3>
        <p class="section-heading__desc">{{ faqContent.description }}</p>
      </div>

      <div class="accordion custom--accordion" id="accordionExample">
        <div class="row gy-3 justify-content-center">
          <div v-for="(chunk, chunkIndex) in faqChunks" :key="chunkIndex" class="col-lg-6">
            <div v-for="(faq, index) in chunk" :key="index" class="accordion-item">
              <h2 class="accordion-header" :id="'heading' + chunkIndex + index">
                <button 
                  class="accordion-button collapsed" 
                  type="button" 
                  data-bs-toggle="collapse" 
                  :data-bs-target="'#collapse' + chunkIndex + index" 
                  aria-expanded="false" 
                  :aria-controls="'collapse' + chunkIndex + index"
                >
                  {{ faq.question }}
                </button>
              </h2>
              <div 
                :id="'collapse' + chunkIndex + index" 
                class="accordion-collapse collapse" 
                :aria-labelledby="'heading' + chunkIndex + index" 
                data-bs-parent="#accordionExample"
              >
                <div class="accordion-body">
                  <p class="text">
                    {{ faq.answer }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="faq-section__bottom text-center mt-4" v-if="faqContent.button_name">
        <router-link :to="faqContent.button_url || '/'" class="btn btn--base pill">
          {{ faqContent.button_name }}
          <span class="btn-icon"><i class="las la-arrow-right"></i></span>
        </router-link>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { appService } from '../../services/appService'

export default {
  name: 'FAQSection',
  setup() {
    const faqContent = ref(null)
    const faqs = ref([])

    const faqChunks = computed(() => {
      if (!faqs.value.length) return []
      const mid = Math.ceil(faqs.value.length / 2)
      return [
        faqs.value.slice(0, mid),
        faqs.value.slice(mid)
      ]
    })

    onMounted(async () => {
      try {
        const [contentRes, elementsRes] = await Promise.all([
          appService.getSections('faq_section'),
          appService.getSections('faq_section.element')
        ])
        faqContent.value = contentRes.data?.content?.data_values || null
        faqs.value = elementsRes.data || []
      } catch (error) {
        console.error('Error loading FAQ section:', error)
      }
    })

    return {
      faqContent,
      faqs,
      faqChunks
    }
  }
}
</script>
