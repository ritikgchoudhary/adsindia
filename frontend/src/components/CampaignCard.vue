<template>
  <div class="card-item" :class="getBgClass(iteration)">
    <router-link :to="`/campaign/${campaign.slug}`" class="card-item__thumb">
      <img :src="$getImage('campaign', campaign && campaign.image)" alt="img">
    </router-link>
    <div class="card-item__content">
      <h5 class="card-item__title">
        <router-link :to="`/campaign/${campaign.slug}`" class="card-item__title-link">
          {{ campaign.title }}
        </router-link>
      </h5>
      <div class="card-item__button">
        <router-link :to="`/campaign/${campaign.slug}`" class="btn-outline--white btn w-100">
          {{ campaign.payout_text || campaign.payout_per_conversion }}
        </router-link>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'CampaignCard',
  props: {
    campaign: {
      type: Object,
      required: true
    },
    iteration: {
      type: Number,
      default: 1
    }
  },
  setup() {
    const getBgClass = (iteration) => {
      if (iteration % 3 === 0) return 'card-success-bg'
      if (iteration % 2 === 0) return 'card-base-two-bg'
      return ''
    }

    return {
      getBgClass
    }
  }
}
</script>
