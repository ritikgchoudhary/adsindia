<template>
  <section class="blog my-120" v-if="blogContent">
    <div class="container">
      <div class="section-heading text-center">
        <span class="section-heading__subtitle">{{ blogContent.subheading }}</span>
        <h3 class="section-heading__title">{{ blogContent.heading }}</h3>
        <p class="section-heading__desc">{{ blogContent.description }}</p>
      </div>
      <div class="row gy-4 justify-content-center">
        <div v-for="(blog, index) in blogPosts" :key="index" class="col-lg-4 col-md-6">
          <div class="blog-item" :class="getBlogClass(index)">
            <div class="blog-item__thumb">
              <router-link :to="`/blog/${blog.slug}`" class="blog-item__thumb-link">
                <img :src="$getImage('blog', blog.image)" class="fit-image" alt="img">
              </router-link>
              <div class="blog-item__date">
                <h4 class="date-time">{{ formatDate(blog.created_at, 'DD') }}</h4>
                <h6 class="month">{{ formatDate(blog.created_at, 'MMM') }}</h6>
              </div>
            </div>
            <div class="blog-item__content">
              <span class="blog-item__badge">{{ blog.badge }}</span>
              <h5 class="blog-item__title">
                <router-link :to="`/blog/${blog.slug}`" class="blog-item__title-link">
                  {{ blog.title }}
                </router-link>
              </h5>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import { ref, onMounted } from 'vue'
import { appService } from '../../services/appService'

export default {
  name: 'BlogSection',
  setup() {
    const blogContent = ref(null)
    const blogPosts = ref([])

    const getBlogClass = (index) => {
      const classes = ['card-base-two-bg', 'card-success-bg']
      return classes[index % classes.length]
    }

    const formatDate = (dateStr, format) => {
      if (!dateStr) return ''
      const date = new Date(dateStr)
      if (format === 'DD') return date.getDate().toString().padStart(2, '0')
      if (format === 'MMM') return date.toLocaleString('default', { month: 'short' })
      return date.toLocaleDateString()
    }

    onMounted(async () => {
      try {
        const [contentRes, elementsRes] = await Promise.all([
          appService.getSections('blog'),
          appService.getSections('blog.element')
        ])
        blogContent.value = contentRes.data?.content?.data_values || null
        blogPosts.value = (elementsRes.data || []).slice(0, 3)
      } catch (error) {
        console.error('Error loading blog section:', error)
      }
    })

    return {
      blogContent,
      blogPosts,
      getBlogClass,
      formatDate
    }
  }
}
</script>
