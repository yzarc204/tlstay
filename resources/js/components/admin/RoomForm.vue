<template>
  <SidePanel :is-open="!!room" @close="$emit('close')">
    <div class="flex flex-col h-full">
      <!-- Header -->
      <div class="sticky top-0 z-10 flex items-center justify-between border-b bg-white px-6 py-4">
        <h3 class="text-lg font-semibold text-gray-900">
          {{ room ? `Thông tin phòng ${room.room_number}` : 'Thông tin phòng' }}
        </h3>
        <button
          @click="$emit('close')"
          class="text-gray-400 hover:text-gray-600 transition-colors"
        >
          <XMarkIcon class="h-5 w-5" />
        </button>
      </div>

      <!-- Content -->
      <div class="flex-1 overflow-y-auto p-6">
        <!-- Tabs -->
        <div class="border-b border-gray-200 mb-6">
          <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button
              type="button"
              @click="activeTab = 'room'"
              :class="[
                'py-2 px-1 border-b-2 font-medium text-sm transition-colors whitespace-nowrap',
                activeTab === 'room'
                  ? 'border-blue-600 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              ]"
            >
              Thông tin phòng trọ
            </button>
            <button
              type="button"
              @click="activeTab = 'images'"
              :class="[
                'py-2 px-1 border-b-2 font-medium text-sm transition-colors whitespace-nowrap',
                activeTab === 'images'
                  ? 'border-blue-600 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              ]"
            >
              Hình ảnh
            </button>
            <button
              type="button"
              @click="activeTab = 'tenant'"
              :class="[
                'py-2 px-1 border-b-2 font-medium text-sm transition-colors whitespace-nowrap',
                activeTab === 'tenant'
                  ? 'border-blue-600 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              ]"
            >
              Khách thuê
            </button>
          </nav>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-4">
          <!-- Tab: Thông tin phòng trọ -->
          <RoomInfoTab
            v-show="activeTab === 'room'"
            v-model="form"
            :house="house"
            :errors="errors"
            :has-inherited-amenities="hasInheritedAmenities"
          />

          <!-- Tab: Hình ảnh -->
          <RoomImagesTab
            v-show="activeTab === 'images'"
            :house-images="houseImages"
            :selected-images="selectedRoomImages"
            @update:selectedImages="handleImagesUpdate"
          />

          <!-- Tab: Khách thuê -->
          <TenantInfoTab
            v-show="activeTab === 'tenant'"
            :form="form"
            :users="users"
            :errors="errors"
            :room="room"
            :house="house"
            :is-active="activeTab === 'tenant'"
            @update:form="form = $event"
          />

          <!-- Actions -->
          <div class="flex gap-3 pt-4 border-t">
            <Button
              type="button"
              variant="outline"
              @click="handleDelete"
              class="flex-1"
            >
              Xóa phòng
            </Button>
            <Button
              type="submit"
              :disabled="isSubmitting"
              class="flex-1"
            >
              {{ isSubmitting ? 'Đang lưu...' : 'Lưu thay đổi' }}
            </Button>
          </div>
        </form>
      </div>
    </div>
  </SidePanel>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import Button from '@/components/ui/Button.vue'
import SidePanel from '@/components/ui/SidePanel.vue'
import { XMarkIcon } from '@heroicons/vue/24/outline'
import RoomInfoTab from './RoomInfoTab.vue'
import RoomImagesTab from './RoomImagesTab.vue'
import TenantInfoTab from './TenantInfoTab.vue'

const props = defineProps({
  room: Object,
  house: Object,
  users: {
    type: Array,
    default: () => [],
  },
  errors: {
    type: Object,
    default: () => ({}),
  },
})

const emit = defineEmits(['close'])

const activeTab = ref('room')

const form = ref({
  room_number: '',
  floor: 1,
  price_per_day: '',
  status: 'available',
  area: '',
  amenities: [],
  images: [],
  tenant_name: '',
  tenant_id: null,
  rental_start_date: '',
  rental_end_date: '',
})

const isSubmitting = ref(false)
const selectedRoomImages = ref([]) // URLs of selected images for the room

// Get house images
const houseImages = computed(() => {
  if (!props.house?.images) return []
  if (Array.isArray(props.house.images)) {
    return props.house.images.filter(img => img && img.trim() !== '')
  }
  return []
})

// Check if room is using inherited amenities
const hasInheritedAmenities = computed(() => {
  if (!props.room) return false
  const roomAmenities = props.room.amenities
  return !roomAmenities || !Array.isArray(roomAmenities) || roomAmenities.length === 0
})

watch(() => props.room, (newRoom) => {
  if (newRoom) {
    // Reset về tab thông tin phòng trọ khi chọn phòng mới
    activeTab.value = 'room'
    
    // If room has no amenities or empty array, inherit from house
    const roomAmenities = newRoom.amenities && Array.isArray(newRoom.amenities) && newRoom.amenities.length > 0
      ? newRoom.amenities
      : (props.house?.amenities || [])
    
    // Load room images
    const roomImages = newRoom.images && Array.isArray(newRoom.images) && newRoom.images.length > 0
      ? newRoom.images.filter(img => img && img.trim() !== '')
      : []
    selectedRoomImages.value = [...roomImages]
    
    // Format dates for input fields (YYYY-MM-DD) - DateInput component will convert to dd/mm/yyyy for display
    const formatDate = (date) => {
      if (!date) return ''
      const d = new Date(date)
      const year = d.getFullYear()
      const month = String(d.getMonth() + 1).padStart(2, '0')
      const day = String(d.getDate()).padStart(2, '0')
      return `${year}-${month}-${day}`
    }
    
    form.value = {
      room_number: newRoom.room_number || '',
      floor: newRoom.floor || 1,
      price_per_day: newRoom.price_per_day || '',
      status: newRoom.status || 'available',
      area: newRoom.area || '',
      amenities: roomAmenities,
      images: roomImages,
      tenant_name: newRoom.tenant_name || '',
      tenant_id: newRoom.tenant_id || null,
      rental_start_date: formatDate(newRoom.rental_start_date),
      rental_end_date: formatDate(newRoom.rental_end_date),
    }
  } else {
    selectedRoomImages.value = []
  }
}, { immediate: true })

// Handle images update
const handleImagesUpdate = (images) => {
  selectedRoomImages.value = images
  form.value.images = [...images]
}

// Watch status change to clear tenant_id and rental dates when status is not occupied
watch(() => form.value.status, (newStatus) => {
  if (newStatus !== 'occupied') {
    form.value.tenant_id = null
    form.value.tenant_name = ''
    form.value.rental_start_date = ''
    form.value.rental_end_date = ''
    // Chuyển về tab thông tin phòng trọ nếu đang ở tab khách thuê
    if (activeTab.value === 'tenant') {
      activeTab.value = 'room'
    }
  } else {
    // Tự động chuyển sang tab khách thuê khi chọn "Đã thuê"
    activeTab.value = 'tenant'
  }
})


const handleSubmit = () => {
  isSubmitting.value = true

  // Ensure images are included in form data
  form.value.images = [...selectedRoomImages.value]

  router.put(`/admin/houses/${props.house.id}/rooms/${props.room.id}`, form.value, {
    preserveScroll: true,
    onFinish: () => {
      isSubmitting.value = false
    },
  })
}

const handleDelete = () => {
  if (!confirm('Bạn có chắc chắn muốn xóa phòng này?')) {
    return
  }

  router.delete(`/admin/houses/${props.house.id}/rooms/${props.room.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      emit('close')
    },
  })
}
</script>
