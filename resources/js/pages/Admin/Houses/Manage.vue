<template>
  <Head title="Quản lý Phòng" />
  <AdminLayout title="Quản lý Phòng">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <Link
            href="/admin/houses"
            class="text-primary hover:text-primary-600 mb-2 inline-flex items-center text-sm"
          >
            <ChevronLeftIcon class="h-4 w-4 mr-1" />
            Quay lại danh sách
          </Link>
          <h1 class="text-2xl font-bold text-gray-900">{{ house.name }}</h1>
          <p class="text-gray-600 mt-1">{{ house.address }}</p>
        </div>
      </div>

      <!-- Main Content: Room Diagram -->
      <div>
        <RoomDiagram
          ref="roomDiagramRef"
          :rooms-by-floor="roomsByFloor"
          :house="house"
          @select-room="handleSelectRoom"
          @add-room="handleAddRoomClick"
          @add-floor="handleAddFloor"
          @delete-floor="handleDeleteFloor"
        />
      </div>

      <!-- Room Form Side Panel -->
      <RoomForm
        :room="selectedRoom"
        :house="house"
        :users="users"
        :errors="errors"
        @close="selectedRoom = null"
      />

      <!-- Add Room Modal -->
      <div
        v-if="showAddRoomModal"
        class="fixed inset-0 z-50 overflow-y-auto"
        @click.self="closeAddRoomModal"
      >
        <div class="flex items-center justify-center min-h-screen px-4">
          <div class="fixed inset-0 bg-black opacity-50" @click="closeAddRoomModal"></div>
          <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Thêm phòng mới</h3>
            <p class="text-sm text-gray-600 mb-4">Tầng: <span class="font-semibold text-primary">Tầng {{ newRoomForm.floor }}</span></p>
            <form @submit.prevent="handleAddRoom">
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Số phòng <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="newRoomForm.room_number"
                    type="text"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                    placeholder="101"
                  />
                </div>


                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Giá thuê/ngày (VNĐ) <span class="text-red-500">*</span>
                  </label>
                  <div class="flex items-center gap-2">
                    <input
                      v-model.number="newRoomForm.price_per_day"
                      type="number"
                      step="1000"
                      min="0"
                      required
                      class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                      placeholder="500000"
                    />
                    <span class="text-xs text-gray-500 font-medium whitespace-nowrap">
                      {{ formatPriceDisplay(newRoomForm.price_per_day) }}
                    </span>
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Giá thuê/tuần (VNĐ)
                  </label>
                  <div class="flex items-center gap-2">
                    <input
                      v-model.number="newRoomForm.price_per_week"
                      type="number"
                      step="1000"
                      min="0"
                      class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                      placeholder="3000000"
                    />
                    <span class="text-xs text-gray-500 font-medium whitespace-nowrap">
                      {{ formatPriceDisplay(newRoomForm.price_per_week) }}
                    </span>
                  </div>
                  <p class="mt-1 text-xs text-gray-500">Để trống nếu tính theo giá ngày × 7</p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Giá thuê/tháng (VNĐ)
                  </label>
                  <div class="flex items-center gap-2">
                    <input
                      v-model.number="newRoomForm.price_per_month"
                      type="number"
                      step="1000"
                      min="0"
                      class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                      placeholder="12000000"
                    />
                    <span class="text-xs text-gray-500 font-medium whitespace-nowrap">
                      {{ formatPriceDisplay(newRoomForm.price_per_month) }}
                    </span>
                  </div>
                  <p class="mt-1 text-xs text-gray-500">Để trống nếu tính theo giá ngày × 30</p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Trạng thái <span class="text-red-500">*</span>
                  </label>
                  <select
                    v-model="newRoomForm.status"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                  >
                    <option value="available">Trống</option>
                    <option value="active">Đang thuê</option>
                  </select>
                </div>

                <!-- Tenant Selection (if active) -->
                <div v-if="newRoomForm.status === 'active'">
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Người đang thuê <span class="text-red-500">*</span>
                  </label>
                  <SelectSearchable
                    v-model="newRoomForm.tenant_id"
                    :options="users"
                    option-value="id"
                    :option-label="(user) => `${user.name} (${user.email}${user.phone ? ' - ' + user.phone : ''})`"
                    placeholder="Tìm kiếm và chọn người thuê..."
                    hint="Chọn tài khoản người dùng đang thuê phòng này"
                    @update:modelValue="handleNewRoomTenantChange"
                  />
                </div>

                <!-- Amenities -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Tiện nghi</label>
                  <div class="grid grid-cols-2 gap-2 max-h-48 overflow-y-auto border border-gray-200 rounded-lg p-3">
                    <label
                      v-for="(label, key) in amenityOptions"
                      :key="key"
                      class="flex items-center space-x-2 cursor-pointer p-2 hover:bg-gray-50 rounded"
                    >
                      <input
                        v-model="newRoomForm.amenities"
                        type="checkbox"
                        :value="key"
                        class="rounded border-gray-300 text-primary focus:ring-primary"
                      />
                      <span class="text-sm text-gray-700">{{ label }}</span>
                    </label>
                  </div>
                  <p class="mt-1 text-xs text-gray-500">Tiện nghi đã được kế thừa từ nhà trọ, bạn có thể chỉnh sửa</p>
                </div>
              </div>

              <div class="flex justify-end gap-3 mt-6">
                <button
                  type="button"
                  @click="closeAddRoomModal"
                  class="px-4 py-2 border-2 border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                >
                  Hủy
                </button>
                <button
                  type="submit"
                  :disabled="isAdding"
                  class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                  {{ isAdding ? 'Đang thêm...' : 'Thêm phòng' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, watch } from 'vue'
import { Head, router, Link } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import RoomDiagram from '@/components/admin/RoomDiagram.vue'
import RoomForm from '@/components/admin/RoomForm.vue'
import SelectSearchable from '@/components/ui/SelectSearchable.vue'
import { ChevronLeftIcon } from '@heroicons/vue/24/outline'
import { amenityNameMap } from '@/utils/amenityIcons'

const amenityOptions = amenityNameMap

const props = defineProps({
  house: Object,
  roomsByFloor: Object,
  users: {
    type: Array,
    default: () => [],
  },
  errors: {
    type: Object,
    default: () => ({}),
  },
})

const selectedRoom = ref(null)
const showAddRoomModal = ref(false)
const isAdding = ref(false)
const roomDiagramRef = ref(null)

const newRoomForm = ref({
  room_number: '',
  floor: 1,
  price_per_day: '',
  price_per_week: '',
  price_per_month: '',
  status: 'available',
  amenities: [],
  tenant_id: null,
  tenant_name: '',
})

// Format price for display
const formatPrice = (price) => {
  if (!price) return '0'
  return new Intl.NumberFormat('vi-VN').format(price)
}

const formatPriceDisplay = (price) => {
  if (!price || price === 0) return ''
  return formatPrice(price) + ' đ'
}

const handleSelectRoom = (room) => {
  selectedRoom.value = room
}

const handleAddRoomClick = (floor) => {
  // Set the floor automatically based on where the button was clicked
  if (floor) {
    newRoomForm.value.floor = floor
  } else {
    // Default to first floor if no floor specified
    newRoomForm.value.floor = 1
  }
  
  // Inherit price and amenities from house
  newRoomForm.value.price_per_day = props.house.price_per_day || ''
  newRoomForm.value.price_per_week = props.house.price_per_week || ''
  newRoomForm.value.price_per_month = props.house.price_per_month || ''
  newRoomForm.value.amenities = props.house.amenities ? [...props.house.amenities] : []
  
  showAddRoomModal.value = true
}

const closeAddRoomModal = () => {
  showAddRoomModal.value = false
  newRoomForm.value = {
    room_number: '',
    floor: 1,
    price_per_day: '',
    price_per_week: '',
    price_per_month: '',
    status: 'available',
    amenities: [],
    tenant_id: null,
    tenant_name: '',
  }
  if (roomDiagramRef.value) {
    roomDiagramRef.value.newRoomFloor = null
  }
}

const handleNewRoomTenantChange = () => {
  if (newRoomForm.value.tenant_id) {
    const selectedUser = props.users.find(u => u.id === newRoomForm.value.tenant_id)
    if (selectedUser) {
      newRoomForm.value.tenant_name = selectedUser.name
    }
  } else {
    newRoomForm.value.tenant_name = ''
  }
}


const handleAddRoom = () => {
  isAdding.value = true

  router.post(`/admin/houses/${props.house.id}/rooms`, newRoomForm.value, {
    preserveScroll: true,
    onSuccess: () => {
      closeAddRoomModal()
    },
    onFinish: () => {
      isAdding.value = false
    },
  })
}

const handleAddFloor = () => {
  if (!confirm('Bạn có chắc chắn muốn thêm tầng mới?')) {
    return
  }

  router.post(`/admin/houses/${props.house.id}/floors`, {}, {
    preserveScroll: true,
  })
}

const handleDeleteFloor = (floor) => {
  if (!confirm(`Bạn có chắc chắn muốn xóa tầng ${floor}? Các phòng ở tầng trên sẽ được di chuyển xuống.`)) {
    return
  }

  router.delete(`/admin/houses/${props.house.id}/floors`, {
    data: { floor },
    preserveScroll: true,
  })
}
</script>
