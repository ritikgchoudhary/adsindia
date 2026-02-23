<template>
  <div class="how-work-section my-120">
    <div class="container">
      <div class="section-heading text-center">
        <span class="section-heading__subtitle">
          {{ workProcess?.subtitle ?? '' }}
        </span>
        <h3 class="section-heading__title">
          {{ workProcess?.heading ?? '' }}
        </h3>
        <p class="section-heading__desc">
          {{ workProcess?.description ?? '' }}
        </p>
      </div>

      <div class="how-work-wrapper">
        <span class="bg-color"></span>
        <div class="row gy-5 justify-content-center">
          <div 
            v-for="(step, index) in workSteps" 
            :key="index" 
            class="col-lg-4 col-sm-6" 
            :class="{ 'pe-lg-5': index !== workSteps.length - 1 }"
          >
            <div class="how-work-item">
              <span class="how-work-item__icon">
                <span v-html="step?.data_values?.icon ?? step?.icon ?? ''"></span>
                <span class="how-work-item__number">{{ index + 1 }}</span>
              </span>

              <div class="how-work-item__content">
                <h4 class="how-work-item__title">
                  {{ step.title }}
                </h4>
                <p class="how-work-item__desc">
                  {{ step.description }}
                </p>
              </div>
              <div class="how-work-item__shape">
                <img :src="'/assets/templates/basic/images/shapes/hw-2.png'" alt="shape">
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
  name: 'WorkProcessSection',
  setup() {
    const workProcess = ref(null)
    const workSteps = ref([])

    onMounted(async () => {
      try {
        const [contentRes, elementsRes] = await Promise.all([
          appService.getSections('work_process'),
          appService.getSections('work_process.element')
        ])
        workProcess.value = appService.getSectionContent(contentRes)
        workSteps.value = elementsRes?.data ?? []
      } catch (error) {
        console.error('Error loading work process:', error)
      }
    })

    return {
      workProcess,
      workSteps
    }
  }
}
</script>
