<template>
  <div class="space-y-4">
    <!-- Header Actions -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-3">
        <Button @click="$emit('add-floor')" size="sm" variant="outline">
          <PlusIcon class="h-4 w-4 mr-1" />
          Thêm tầng
        </Button>
        <Button @click="handleAddRoomFromHeader()" size="sm">
          <PlusIcon class="h-4 w-4 mr-1" />
          Thêm phòng
        </Button>
      </div>
      <div class="text-sm text-gray-600">
        Tổng: {{ house.floors }} tầng, {{ totalRooms }} phòng
      </div>
    </div>

    <!-- All Floors -->
    <div class="space-y-4">
      <div
        v-for="floor in floors"
        :key="floor"
        class="bg-white rounded-lg border border-gray-200 overflow-hidden"
      >
        <!-- Floor Header -->
        <div
          class="flex items-center justify-between p-4 bg-gray-50 border-b border-gray-200 cursor-pointer hover:bg-gray-100 transition-colors"
          @click="toggleFloor(floor)"
        >
          <div class="flex items-center gap-3">
            <ChevronDownIcon
              :class="[
                'h-5 w-5 text-gray-500 transition-transform',
                expandedFloors.includes(floor) ? 'rotate-0' : '-rotate-90'
              ]"
            />
            <h3 class="text-lg font-semibold text-gray-900">
              Tầng {{ floor }}
            </h3>
            <span class="text-sm text-gray-600">
              ({{ getFloorRooms(floor).length }} phòng)
            </span>
          </div>
          <div class="flex items-center gap-2">
            <button
              v-if="canDeleteFloor(floor)"
              @click.stop="$emit('delete-floor', floor)"
              class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
              title="Xóa tầng"
            >
              <TrashIcon class="h-5 w-5" />
            </button>
          </div>
        </div>

        <!-- Floor Content (Collapsible) -->
        <div
          v-show="expandedFloors.includes(floor)"
          class="p-6"
        >
          <!-- Grid of Rooms -->
          <div class="grid grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-3">
              <div
                v-for="room in getFloorRooms(floor)"
                :key="room.id"
                @click="selectRoom(room)"
                :class="[
                  'relative p-4 border-2 rounded-lg cursor-pointer transition-all',
                  selectedRoom?.id === room.id
                    ? 'border-primary bg-primary/10 shadow-md'
                    : getRoomStatusClass(room.status)
                ]"
              >
                <div class="text-center">
                  <div class="text-sm font-semibold text-gray-900 mb-1">
                    {{ room.room_number }}
                  </div>
                  <div class="text-xs text-gray-600">
                    {{ formatPrice(room.price_per_day) }}đ/ngày
                  </div>
                  <div
                    :class="[
                      'mt-2 text-xs px-2 py-1 rounded-full inline-block',
                      getStatusBadgeClass(room.status)
                    ]"
                  >
                    {{ getStatusText(room.status) }}
                  </div>
                </div>
              </div>

            <!-- Add Room Button for this floor -->
            <div
              @click="handleAddRoomForFloor(floor)"
              class="p-4 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-primary hover:bg-primary/5 transition-all flex items-center justify-center"
            >
              <PlusIcon class="h-8 w-8 text-gray-400" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import Button from '@/components/ui/Button.vue'
import { PlusIcon, ChevronDownIcon, TrashIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  roomsByFloor: {
    type: Object,
    required: true,
  },
  house: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(['select-room', 'add-room', 'add-floor', 'delete-floor'])

const selectedRoom = ref(null)
const showAddRoomModal = ref(false)
const newRoomFloor = ref(null)
const expandedFloors = ref([])

// Initialize: expand all floors by default
watch(() => props.house.floors, (newFloors) => {
  if (newFloors && expandedFloors.value.length === 0) {
    expandedFloors.value = Array.from({ length: newFloors }, (_, i) => i + 1)
  }
}, { immediate: true })

const floors = computed(() => {
  // Include all floors from 1 to house.floors
  const allFloors = []
  for (let i = 1; i <= props.house.floors; i++) {
    allFloors.push(i)
  }
  return allFloors.sort((a, b) => b - a) // Show from top floor to bottom
})

const totalRooms = computed(() => {
  let total = 0
  Object.values(props.roomsByFloor).forEach((rooms) => {
    const roomArray = Array.isArray(rooms) ? rooms : Object.values(rooms)
    total += roomArray.length
  })
  return total
})

const getFloorRooms = (floor) => {
  const floorKey = String(floor)
  const rooms = props.roomsByFloor[floorKey]
  if (!rooms) return []
  
  // If it's an array, return it; if it's an object with numeric keys, convert to array
  return Array.isArray(rooms) ? rooms : Object.values(rooms)
}

const toggleFloor = (floor) => {
  const index = expandedFloors.value.indexOf(floor)
  if (index > -1) {
    expandedFloors.value.splice(index, 1)
  } else {
    expandedFloors.value.push(floor)
  }
}

const canDeleteFloor = (floor) => {
  // Can only delete if floor has no rooms
  return getFloorRooms(floor).length === 0 && props.house.floors > 1
}

const selectRoom = (room) => {
  selectedRoom.value = room
  emit('select-room', room)
}

const handleAddRoomForFloor = (floor) => {
  newRoomFloor.value = floor
  emit('add-room', floor)
}

const handleAddRoomFromHeader = () => {
  // Use the first expanded floor, or the first floor if none expanded
  const defaultFloor = expandedFloors.value.length > 0 
    ? Math.max(...expandedFloors.value) // Use the highest expanded floor
    : 1
  emit('add-room', defaultFloor)
}

const formatPrice = (price) => {
  if (!price) return '0'
  return new Intl.NumberFormat('vi-VN').format(price)
}

const getStatusText = (status) => {
  const statusMap = {
    'available': 'Trống',
    'active': 'Đang ở',
  }
  return statusMap[status] || 'Trống'
}

const getStatusBadgeClass = (status) => {
  const classes = {
    'available': 'bg-green-100 text-green-800',
    'active': 'bg-red-100 text-red-800',
  }
  return classes[status] || classes['available']
}

const getRoomStatusClass = (status) => {
  if (status === 'active') {
    return 'border-red-300 bg-red-50 hover:border-red-400'
  } else {
    // Default to available style
    return 'border-gray-200 bg-gray-50 hover:border-gray-300 hover:shadow-sm'
  }
}

// Expose newRoomFloor for parent component
defineExpose({
  newRoomFloor,
  showAddRoomModal,
})
</script>
