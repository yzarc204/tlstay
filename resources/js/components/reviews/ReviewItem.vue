<template>
  <div class="card p-6">
    <!-- User Info and Rating -->
    <div class="flex items-start justify-between mb-4">
      <div class="flex items-center space-x-3">
        <div class="w-12 h-12 rounded-full bg-primary-100 flex items-center justify-center">
          <span class="text-primary font-semibold text-lg">
            {{ userInitials }}
          </span>
        </div>
        <div>
          <p class="font-semibold text-gray-800">{{ review.user?.name || 'Khách hàng' }}</p>
          <p class="text-sm text-gray-500">{{ formatDate(review.created_at) }}</p>
        </div>
      </div>
      <div class="flex items-center space-x-1">
        <StarIcon
          v-for="star in 5"
          :key="star"
          :class="[
            'w-5 h-5',
            star <= review.rating ? 'text-yellow-400 fill-current' : 'text-gray-300'
          ]"
        />
      </div>
    </div>

    <!-- Comment -->
    <p v-if="review.comment" class="text-gray-700 mb-4 whitespace-pre-wrap">
      {{ review.comment }}
    </p>

    <!-- Images -->
    <div v-if="review.images && review.images.length > 0" class="grid grid-cols-2 md:grid-cols-4 gap-2 mb-4">
      <div
        v-for="(image, index) in review.images"
        :key="index"
        class="relative aspect-square rounded-lg overflow-hidden border border-gray-200 cursor-pointer hover:opacity-90 transition-opacity"
        @click="openImageModal(image)"
      >
        <img :src="image" :alt="`Review image ${index + 1}`" class="w-full h-full object-cover" />
      </div>
    </div>

    <!-- Manager Response -->
    <div v-if="review.manager_response" class="mt-4 pt-4 border-t border-gray-200 bg-gray-50 rounded-lg p-4">
      <div class="flex items-start space-x-2">
        <div class="w-8 h-8 rounded-full bg-secondary-100 flex items-center justify-center flex-shrink-0">
          <BuildingOfficeIcon class="w-5 h-5 text-secondary" />
        </div>
        <div class="flex-1">
          <div class="flex items-center space-x-2 mb-1">
            <span class="font-semibold text-secondary">Phản hồi từ quản lý</span>
            <span v-if="review.manager_response_at" class="text-xs text-gray-500">
              {{ formatDate(review.manager_response_at) }}
            </span>
          </div>
          <p class="text-gray-700 whitespace-pre-wrap">{{ review.manager_response }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { StarIcon, BuildingOfficeIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  review: {
    type: Object,
    required: true,
  },
})

const userInitials = computed(() => {
  const name = props.review.user?.name || 'K'
  const parts = name.split(' ')
  if (parts.length >= 2) {
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
  }
  return name[0].toUpperCase()
})

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('vi-VN', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
}

const openImageModal = (imageUrl) => {
  // Simple image modal - can be enhanced with a proper modal component
  window.open(imageUrl, '_blank')
}
</script>
