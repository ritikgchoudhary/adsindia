<template>
  <section class="testimonials my-120" v-if="testimonialContent">
    <div class="testimonial-shape">
      <img src="/assets/templates/basic/images/shapes/ts-2.png" alt="Shape">
    </div>

    <div class="container">
      <div class="row gy-5">
        <div class="col-lg-5">
          <div class="section-heading style-left">
            <span class="section-heading__subtitle">
              {{ testimonialContent.heading }}
            </span>
            <h3 class="section-heading__title">
              {{ testimonialContent.subheading }}
            </h3>
          </div>
        </div>

        <div class="col-lg-7">
          <div class="testimonial-slider" ref="testimonialSlider">
            <div v-for="(testimonial, index) in testimonials" :key="index" class="testimonails-card">
              <div class="testimonial-item">
                <div class="testimonial-item__content">
                  <h4 class="testimonial-item__name">
                    {{ testimonial.name }}
                  </h4>
                  <p class="testimonial-item__designation">
                    {{ testimonial.designation }}
                  </p>
                  <div class="testimonial-item__rating">
                    <ul class="rating-list">
                      <li v-for="star in (testimonial.rating || 5)" :key="star" class="rating-list__item">
                        <i class="fas fa-star"></i>
                      </li>
                    </ul>
                  </div>
                </div>
                <p class="testimonial-item__desc">
                  {{ testimonial.comment }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import { ref, onMounted, nextTick } from 'vue'
import { appService } from '../../services/appService'

export default {
  name: 'TestimonialsSection',
  setup() {
    const testimonialContent = ref(null)
    const testimonials = ref([])
    const testimonialSlider = ref(null)

    onMounted(async () => {
      try {
        const [contentRes, elementsRes] = await Promise.all([
          appService.getSections('testimonials'),
          appService.getSections('testimonials.element')
        ])
        testimonialContent.value = contentRes.data?.content?.data_values || null
        testimonials.value = elementsRes.data || []

        await nextTick()

        if (window.jQuery && window.jQuery.fn.slick) {
          const $ = window.jQuery
          $(testimonialSlider.value).slick({
            arrows: false,
            infinite: true,
            slidesToShow: 2,
            slidesToScroll: 1,
            speed: 4000,
            cssEase: "linear",
            autoplay: true,
            autoplaySpeed: 0,
            adaptiveHeight: false,
            pauseOnDotsHover: false,
            pauseOnHover: true,
            pauseOnFocus: true,
            dots: true,
            responsive: [
              {
                breakpoint: 1199,
                settings: {
                  arrows: false,
                  slidesToShow: 2,
                  dots: true,
                },
              },
              {
                breakpoint: 991,
                settings: {
                  arrows: false,
                  slidesToShow: 2,
                },
              },
              {
                breakpoint: 464,
                settings: {
                  arrows: false,
                  slidesToShow: 1,
                },
              },
            ],
          })
        }
      } catch (error) {
        console.error('Error loading testimonials:', error)
      }
    })

    return {
      testimonialContent,
      testimonials,
      testimonialSlider
    }
  }
}
</script>
