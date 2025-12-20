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
import { useToast } from '@/composables/useToast'
import { useConfirm } from '@/composables/useConfirm'
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

const toast = useToast()
const confirm = useConfirm()
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

// Watch status change to clear tenant_id and rental dates when status is not active
watch(() => form.value.status, (newStatus) => {
  if (newStatus !== 'active') {
    form.value.tenant_id = null
    form.value.tenant_name = ''
    form.value.rental_start_date = ''
    form.value.rental_end_date = ''
    // Chuyển về tab thông tin phòng trọ nếu đang ở tab khách thuê
    if (activeTab.value === 'tenant') {
      activeTab.value = 'room'
    }
  } else {
    // Tự động chuyển sang tab khách thuê khi chọn "Đang thuê"
    activeTab.value = 'tenant'
  }
})


const handleSubmit = async () => {
  // Show confirmation dialog for tenant tab
  if (activeTab.value === 'tenant') {
    try {
      await confirm.show({
        title: 'Cập nhật thông tin khách thuê',
        message: 'Bạn có chắc chắn muốn cập nhật thông tin khách thuê? Hành động này có thể ảnh hưởng đến các hóa đơn và đặt phòng liên quan.',
        confirmText: 'Cập nhật',
        cancelText: 'Hủy',
        confirmVariant: 'primary',
      })
    } catch (error) {
      // User cancelled - do nothing
      if (error.message !== 'USER_CANCELLED') {
        console.error('Error showing confirm dialog:', error)
      }
      return
    }
  }

  isSubmitting.value = true

  // Prepare data based on active tab
  // Only submit fields relevant to the current tab
  let submitData = {}
  
  if (activeTab.value === 'room') {
    // Only submit room information (not tenant info, not images)
    submitData = {
      room_number: form.value.room_number,
      floor: form.value.floor,
      price_per_day: form.value.price_per_day,
      status: form.value.status,
      area: form.value.area,
      amenities: form.value.amenities,
    }
    
    // If status is not active, explicitly clear tenant info
    if (form.value.status !== 'active') {
      submitData.tenant_id = null
      submitData.tenant_name = null
      submitData.rental_start_date = null
      submitData.rental_end_date = null
    } else {
      // If status is active, include existing tenant info from room (not from form)
      // This ensures validation passes but doesn't change tenant info if user modified it
      if (props.room?.tenant_id) {
        submitData.tenant_id = props.room.tenant_id
        submitData.tenant_name = props.room.tenant_name
        if (props.room.rental_start_date) {
          const d = new Date(props.room.rental_start_date)
          submitData.rental_start_date = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`
        }
        if (props.room.rental_end_date) {
          const d = new Date(props.room.rental_end_date)
          submitData.rental_end_date = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`
        }
      }
    }
  } else if (activeTab.value === 'images') {
    // Only submit images (keep other room info unchanged)
    submitData = {
      room_number: form.value.room_number,
      floor: form.value.floor,
      price_per_day: form.value.price_per_day,
      status: form.value.status,
      area: form.value.area,
      amenities: form.value.amenities,
      images: [...selectedRoomImages.value],
    }
    
    // If status is active, include existing tenant info from room for validation
    if (form.value.status === 'active' && props.room?.tenant_id) {
      submitData.tenant_id = props.room.tenant_id
      submitData.tenant_name = props.room.tenant_name
      if (props.room.rental_start_date) {
        const d = new Date(props.room.rental_start_date)
        submitData.rental_start_date = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`
      }
      if (props.room.rental_end_date) {
        const d = new Date(props.room.rental_end_date)
        submitData.rental_end_date = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`
      }
    }
  } else if (activeTab.value === 'tenant') {
    // Only submit tenant information (keep room info and images unchanged)
    submitData = {
      room_number: form.value.room_number,
      floor: form.value.floor,
      price_per_day: form.value.price_per_day,
      status: form.value.status,
      tenant_id: form.value.tenant_id,
      tenant_name: form.value.tenant_name,
      rental_start_date: form.value.rental_start_date,
      rental_end_date: form.value.rental_end_date,
    }
  }

  // Determine success message based on active tab
  const successMessages = {
    room: 'Cập nhật thông tin phòng thành công',
    images: 'Cập nhật hình ảnh phòng thành công',
    tenant: 'Cập nhật thông tin khách thuê thành công',
  }
  const successMessage = successMessages[activeTab.value] || 'Cập nhật thành công'

  router.put(`/admin/houses/${props.house.id}/rooms/${props.room.id}`, submitData, {
    preserveScroll: true,
    onSuccess: () => {
      toast.success(successMessage)
    },
    onError: () => {
      toast.error('Có lỗi xảy ra khi cập nhật')
    },
    onFinish: () => {
      isSubmitting.value = false
    },
  })
}

const handleDelete = async () => {
  try {
    await confirm.show({
      title: 'Xóa phòng',
      message: 'Bạn có chắc chắn muốn xóa phòng này? Hành động này không thể hoàn tác.',
      confirmText: 'Xóa',
      cancelText: 'Hủy',
      confirmVariant: 'danger',
    })

    // User confirmed, proceed with deletion
    router.delete(`/admin/houses/${props.house.id}/rooms/${props.room.id}`, {
      preserveScroll: true,
      onSuccess: () => {
        emit('close')
      },
    })
  } catch (error) {
    // User cancelled - do nothing
    if (error.message !== 'USER_CANCELLED') {
      console.error('Error showing confirm dialog:', error)
    }
  }
}
</script>
