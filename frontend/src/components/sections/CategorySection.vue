<template>
  <div class="category-section my-60" v-if="categoryContent">
    <div class="container">
      <div class="section-heading">
        <h3 class="section-heading__title">
          <span class="text--base">
            {{ categoryContent.heading_base }}
          </span>
          <span class="title-text">
            {{ categoryContent.heading_text }}
          </span>
        </h3>
      </div>
      <div class="row gy-4 justify-content-center">
        <div 
          v-for="(category, index) in categories" 
          :key="index" 
          class="col-xxl-2 col-lg-3 col-sm-4 col-6"
        >
          <router-link :to="`/campaigns?category=${category.id}`" class="category-item">
            <div class="category-item__thumb">
              <img :src="category.image" alt="img">
            </div>
            <div class="category-item__content">
              <h5 class="category-item__title">
                {{ category.name }}
              </h5>
              <span class="category-item__number"> ({{ category.campaigns_count || 0 }}) </span>
            </div>
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { appService } from '../../services/appService'

export default {
  name: 'CategorySection',
  setup() {
    const categoryContent = ref(null)
    const categories = ref([])

    onMounted(async () => {
      try {
        const [contentRes, categoriesRes] = await Promise.all([
          appService.getSections('category'),
          appService.getCategories()
        ])
        categoryContent.value = contentRes.data?.content?.data_values || null
        categories.value = categoriesRes.data || []
      } catch (error) {
        console.error('Error loading category section:', error)
      }
    })

    return {
      categoryContent,
      categories
    }
  }
}
</script>
