<template>
  <div class="card group cursor-pointer" @click="goToBooking">
    <!-- Image -->
    <div class="relative overflow-hidden h-56">
      <img
        :src="roomImage"
        :alt="roomTitle"
        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
      />
      <div class="absolute top-4 left-4">
        <span class="badge bg-primary text-white"> Phòng {{ room.roomNumber }} </span>
      </div>
      <div class="absolute top-4 right-4">
        <span
          class="badge"
          :class="statusBadgeClass"
        >
          {{ statusText }}
        </span>
      </div>
    </div>

    <!-- Content -->
    <div class="p-5">
      <!-- Title -->
      <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-primary transition-colors">
        {{ roomTitle }}
      </h3>

      <!-- Location -->
      <div class="flex items-start space-x-2 text-gray-600 mb-4">
        <MapPinIcon class="w-5 h-5 mt-0.5 flex-shrink-0" />
        <span class="text-sm">{{ room.houseAddress }}</span>
      </div>

      <!-- Features -->
      <div class="flex items-center space-x-4 text-sm text-gray-600 mb-4 pb-4 border-b">
        <div class="flex items-center space-x-1">
          <HomeIcon class="w-5 h-5" />
          <span>Tầng {{ room.floor }}</span>
        </div>
        <div v-if="room.area" class="flex items-center space-x-1">
          <ArrowsPointingOutIcon class="w-5 h-5" />
          <span>{{ room.area }}m²</span>
        </div>
        <div v-if="room.houseRating" class="flex items-center space-x-1">
          <StarIconSolid class="w-5 h-5 text-yellow-400" />
          <span>{{ room.houseRating }}</span>
        </div>
      </div>

      <!-- Price & Action -->
      <div class="flex items-center justify-between">
        <div>
          <p class="text-2xl font-bold text-primary">
            {{ formatPrice(calculateMonthlyPrice(room.pricePerDay)) }}
            <span class="text-sm text-gray-500 font-normal">/tháng</span>
          </p>
          <p class="text-sm text-gray-500">{{ formatPrice(room.pricePerDay) }}/ngày</p>
        </div>
        <button
          class="px-5 py-2.5 bg-primary text-white rounded-lg hover:bg-secondary transition-all duration-300 font-medium shadow-md hover:shadow-lg transform hover:-translate-y-0.5"
        >
          Xem chi tiết
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import {
  MapPinIcon,
  HomeIcon,
  ArrowsPointingOutIcon,
  StarIcon,
} from '@heroicons/vue/24/outline'
import { StarIcon as StarIconSolid } from '@heroicons/vue/24/solid'

const props = defineProps({
  room: {
    type: Object,
    required: true,
  },
})

const roomImage = computed(() => {
  if (props.room.images && props.room.images.length > 0) {
    return props.room.images[0]
  }
  if (props.room.houseImage) {
    return props.room.houseImage
  }
  return 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800'
})

const roomTitle = computed(() => {
  return `Phòng ${props.room.roomNumber} - ${props.room.houseName || 'Nhà trọ'}`
})

const formatPrice = (price) => {
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND',
  }).format(price)
}

// Tính giá theo tháng từ giá ngày (giảm 20%)
const calculateMonthlyPrice = (pricePerDay) => {
  if (!pricePerDay) return 0
  return Math.round(pricePerDay * 30 * 0.8)
}

const statusText = computed(() => {
  const statusMap = {
    'available': 'Trống',
    'upcoming': 'Sắp tới',
    'active': 'Đang ở',
    'past': 'Đã ở',
  }
  return statusMap[props.room.status] || 'N/A'
})

const statusBadgeClass = computed(() => {
  const status = props.room.status
  const classes = {
    'available': 'bg-green-500 text-white',
    'upcoming': 'bg-amber-500 text-white',
    'active': 'bg-red-500 text-white',
    'past': 'bg-gray-500 text-white',
  }
  return classes[status] || classes['available']
})

const goToBooking = () => {
  if (props.room.houseId) {
    router.visit(`/booking/${props.room.houseId}`)
  }
}
</script>
