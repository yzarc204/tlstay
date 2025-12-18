<template>
  <div v-if="room" class="bg-white rounded-lg border border-gray-200 p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold text-gray-900">Thông tin phòng {{ room.room_number }}</h3>
      <button
        @click="$emit('close')"
        class="text-gray-400 hover:text-gray-600"
      >
        <XMarkIcon class="h-5 w-5" />
      </button>
    </div>

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
          Thông tin người thuê
        </button>
      </nav>
    </div>

    <form @submit.prevent="handleSubmit" class="space-y-4">
      <!-- Tab: Thông tin phòng trọ -->
      <div v-show="activeTab === 'room'" class="space-y-4">
        <!-- Room Number -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Số phòng <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.room_number"
            type="text"
            required
            :class="[
              'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:outline-none transition-colors',
              errors.room_number
                ? 'border-red-500 focus:ring-red-500'
                : 'border-gray-300 focus:ring-primary'
            ]"
          />
          <p v-if="errors.room_number" class="mt-1 text-sm text-red-600">{{ errors.room_number }}</p>
        </div>

        <!-- Floor -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Tầng <span class="text-red-500">*</span>
          </label>
          <input
            v-model.number="form.floor"
            type="number"
            min="1"
            :max="house.floors"
            required
            :class="[
              'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:outline-none transition-colors',
              errors.floor
                ? 'border-red-500 focus:ring-red-500'
                : 'border-gray-300 focus:ring-primary'
            ]"
          />
          <p v-if="errors.floor" class="mt-1 text-sm text-red-600">{{ errors.floor }}</p>
        </div>

        <!-- Price per day -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Giá thuê/ngày (VNĐ) <span class="text-red-500">*</span>
          </label>
          <input
            v-model.number="form.price_per_day"
            type="number"
            step="1000"
            min="0"
            required
            :class="[
              'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:outline-none transition-colors',
              errors.price_per_day
                ? 'border-red-500 focus:ring-red-500'
                : 'border-gray-300 focus:ring-primary'
            ]"
          />
          <p v-if="errors.price_per_day" class="mt-1 text-sm text-red-600">{{ errors.price_per_day }}</p>
        </div>

        <!-- Area -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Diện tích (m²)</label>
          <input
            v-model.number="form.area"
            type="number"
            step="0.01"
            min="0"
            :class="[
              'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:outline-none transition-colors',
              errors.area
                ? 'border-red-500 focus:ring-red-500'
                : 'border-gray-300 focus:ring-primary'
            ]"
          />
          <p v-if="errors.area" class="mt-1 text-sm text-red-600">{{ errors.area }}</p>
        </div>

        <!-- Status -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Trạng thái <span class="text-red-500">*</span>
          </label>
          <select
            v-model="form.status"
            required
            :class="[
              'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:outline-none transition-colors',
              errors.status
                ? 'border-red-500 focus:ring-red-500'
                : 'border-gray-300 focus:ring-primary'
            ]"
          >
            <option value="available">Trống</option>
            <option value="occupied">Đã thuê</option>
          </select>
          <p v-if="errors.status" class="mt-1 text-sm text-red-600">{{ errors.status }}</p>
        </div>

        <!-- Amenities -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Tiện nghi
            <span class="text-xs text-gray-500 font-normal ml-2">
              (Kế thừa từ nhà trọ, có thể chỉnh sửa)
            </span>
          </label>
          <div class="grid grid-cols-2 gap-2 max-h-64 overflow-y-auto border border-gray-200 rounded-lg p-3">
            <label
              v-for="(label, key) in amenityOptions"
              :key="key"
              class="flex items-center space-x-2 cursor-pointer p-2 hover:bg-gray-50 rounded"
            >
              <input
                v-model="form.amenities"
                type="checkbox"
                :value="key"
                class="rounded border-gray-300 text-primary focus:ring-primary"
              />
              <span class="text-sm text-gray-700">{{ label }}</span>
            </label>
          </div>
          <p class="mt-2 text-xs text-gray-500">
            <span v-if="hasInheritedAmenities" class="text-blue-600">
              ⓘ Đang hiển thị tiện nghi kế thừa từ nhà trọ
            </span>
            <span v-else>
              ✓ Phòng có tiện nghi riêng
            </span>
          </p>
        </div>
      </div>

      <!-- Tab: Hình ảnh -->
      <div v-show="activeTab === 'images'" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Hình ảnh phòng
            <span class="text-xs text-gray-500 font-normal ml-2">
              (Chọn từ ảnh đã upload của nhà trọ)
            </span>
          </label>
          <div v-if="houseImages.length > 0" class="space-y-2">
            <p class="text-xs text-gray-500 mb-2">
              Nhấp vào ảnh để chọn hiển thị cho phòng này
            </p>
            <div class="grid grid-cols-2 gap-3 max-h-96 overflow-y-auto border border-gray-200 rounded-lg p-3">
              <div
                v-for="(image, index) in houseImages"
                :key="index"
                class="relative group cursor-pointer"
                @click="toggleRoomImage(image)"
              >
                <div class="relative overflow-hidden rounded-lg border-2 transition-all"
                  :class="isRoomImageSelected(image)
                    ? 'border-primary ring-2 ring-primary ring-offset-2'
                    : 'border-gray-200 hover:border-gray-300'"
                >
                  <img
                    :src="image"
                    :alt="`House image ${index + 1}`"
                    class="w-full h-32 object-cover"
                  />
                  <!-- Checkbox overlay -->
                  <div
                    class="absolute top-1 right-1 rounded-full p-1 shadow-lg transition-all duration-200"
                    :class="isRoomImageSelected(image) 
                      ? 'bg-primary text-white ring-2 ring-primary ring-offset-2' 
                      : 'bg-white text-gray-400 hover:bg-gray-50'"
                  >
                    <svg
                      v-if="isRoomImageSelected(image)"
                      class="w-3 h-3"
                      fill="currentColor"
                      viewBox="0 0 20 20"
                    >
                      <path
                        fill-rule="evenodd"
                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                        clip-rule="evenodd"
                      />
                    </svg>
                    <svg
                      v-else
                      class="w-3 h-3"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M5 13l4 4L19 7"
                      />
                    </svg>
                  </div>
                </div>
              </div>
            </div>
            <p v-if="selectedRoomImages.length > 0" class="text-xs text-green-600 mt-2">
              Đã chọn {{ selectedRoomImages.length }} ảnh cho phòng này
            </p>
            <p v-else class="text-xs text-gray-500 mt-2">
              Chưa chọn ảnh nào. Nhấp vào ảnh để chọn.
            </p>
          </div>
          <div v-else class="text-center py-8 text-sm text-gray-500 border border-gray-200 rounded-lg">
            <p class="mb-2">Nhà trọ chưa có ảnh.</p>
            <p class="text-xs">Vui lòng thêm ảnh cho nhà trọ trước khi chọn ảnh cho phòng.</p>
          </div>
        </div>
      </div>

      <!-- Tab: Thông tin người thuê -->
      <div v-show="activeTab === 'tenant'" class="space-y-4">
        <div v-if="form.status !== 'occupied'" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
          <p class="text-sm text-yellow-800">
            <span class="font-medium">Lưu ý:</span> Phòng đang ở trạng thái "Trống". 
            Để thêm thông tin người thuê, vui lòng chuyển trạng thái phòng sang "Đã thuê" trong tab "Thông tin phòng trọ".
          </p>
        </div>

        <div v-else class="space-y-4">
          <!-- Tenant Selection -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Người đang thuê <span class="text-red-500">*</span>
            </label>
            <SelectSearchable
              v-model="form.tenant_id"
              :options="users"
              option-value="id"
              :option-label="(user) => `${user.name} (${user.email}${user.phone ? ' - ' + user.phone : ''})`"
              placeholder="Tìm kiếm và chọn người thuê..."
              :error="errors.tenant_id"
              hint="Chọn tài khoản người dùng đang thuê phòng này"
              @update:modelValue="handleTenantChange"
            />
          </div>

          <!-- Rental Start Date -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Ngày bắt đầu thuê <span class="text-red-500">*</span>
            </label>
            <DateInput
              v-model="form.rental_start_date"
              :error="errors.rental_start_date"
              placeholder="dd/mm/yyyy"
            />
          </div>

          <!-- Rental End Date -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Ngày kết thúc thuê <span class="text-red-500">*</span>
            </label>
            <DateInput
              v-model="form.rental_end_date"
              :error="errors.rental_end_date"
              :min="form.rental_start_date || undefined"
              placeholder="dd/mm/yyyy"
            />
          </div>
        </div>
      </div>

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
  <div v-else class="bg-white rounded-lg border border-gray-200 p-12 text-center">
    <Squares2X2Icon class="mx-auto h-16 w-16 text-gray-400 mb-4" />
    <p class="text-gray-600">Chọn một phòng để xem và chỉnh sửa thông tin</p>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import Button from '@/components/ui/Button.vue'
import SelectSearchable from '@/components/ui/SelectSearchable.vue'
import DateInput from '@/components/ui/DateInput.vue'
import { XMarkIcon, Squares2X2Icon } from '@heroicons/vue/24/outline'
import { amenityNameMap } from '@/utils/amenityIcons'

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

const amenityOptions = {
  Wifi: 'Wifi',
  AirConditioning: 'Điều hòa',
  HotWater: 'Nóng lạnh',
  PrivateKitchen: 'Bếp riêng',
  SharedKitchen: 'Bếp chung',
  SharedBathroom: 'Vệ sinh chung',
  PrivateBathroom: 'Vệ sinh khép kín',
  Bed: 'Giường',
  Wardrobe: 'Tủ',
  Refrigerator: 'Tủ lạnh',
  Balcony: 'Ban công',
  SharedWashingMachine: 'Máy giặt chung',
  PrivateWashingMachine: 'Máy giặt riêng',
  SharedDryer: 'Máy sấy quần áo chung',
  PrivateDryer: 'Máy sấy quần áo riêng',
}

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

// Watch status change to clear tenant_id and rental dates when status is not occupied
watch(() => form.value.status, (newStatus) => {
  if (newStatus !== 'occupied') {
    form.value.tenant_id = null
    form.value.tenant_name = ''
    form.value.rental_start_date = ''
    form.value.rental_end_date = ''
    // Chuyển về tab thông tin phòng trọ nếu đang ở tab người thuê
    if (activeTab.value === 'tenant') {
      activeTab.value = 'room'
    }
  } else {
    // Tự động chuyển sang tab thông tin người thuê khi chọn "Đã thuê"
    activeTab.value = 'tenant'
  }
})

// Handle tenant selection change
const handleTenantChange = () => {
  if (form.value.tenant_id) {
    const selectedUser = props.users.find(u => u.id === form.value.tenant_id)
    if (selectedUser) {
      form.value.tenant_name = selectedUser.name
    }
  } else {
    form.value.tenant_name = ''
  }
}

const isRoomImageSelected = (imageUrl) => {
  return selectedRoomImages.value.includes(imageUrl)
}

const toggleRoomImage = (imageUrl) => {
  const index = selectedRoomImages.value.indexOf(imageUrl)
  if (index > -1) {
    selectedRoomImages.value.splice(index, 1)
  } else {
    selectedRoomImages.value.push(imageUrl)
  }
  // Update form.images
  form.value.images = [...selectedRoomImages.value]
}

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
