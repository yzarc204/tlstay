<template>
  <div class="space-y-4">
    <!-- Room Number -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-2">
        Số phòng <span class="text-red-500">*</span>
      </label>
      <input
        :value="modelValue.room_number"
        @input="$emit('update:modelValue', { ...modelValue, room_number: $event.target.value })"
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
        :value="modelValue.floor"
        @input="$emit('update:modelValue', { ...modelValue, floor: Number($event.target.value) })"
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
      <div class="flex items-center gap-2">
        <input
          :value="modelValue.price_per_day"
          @input="$emit('update:modelValue', { ...modelValue, price_per_day: $event.target.value })"
          type="number"
          step="1000"
          min="0"
          required
          :class="[
            'flex-1 px-4 py-2 border rounded-lg focus:ring-2 focus:outline-none transition-colors',
            errors.price_per_day
              ? 'border-red-500 focus:ring-red-500'
              : 'border-gray-300 focus:ring-primary'
          ]"
        />
        <span class="text-xs text-gray-500 font-medium whitespace-nowrap">
          {{ formatPriceDisplay(modelValue.price_per_day) }}
        </span>
      </div>
      <p v-if="errors.price_per_day" class="mt-1 text-sm text-red-600">{{ errors.price_per_day }}</p>
    </div>

    <!-- Area -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-2">Diện tích (m²)</label>
      <input
        :value="modelValue.area"
        @input="$emit('update:modelValue', { ...modelValue, area: $event.target.value })"
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
        :value="modelValue.status"
        @change="$emit('update:modelValue', { ...modelValue, status: $event.target.value })"
        required
        :class="[
          'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:outline-none transition-colors',
          errors.status
            ? 'border-red-500 focus:ring-red-500'
            : 'border-gray-300 focus:ring-primary'
        ]"
      >
        <option value="available">Trống</option>
        <option value="active">Đang thuê</option>
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
            :checked="modelValue.amenities.includes(key)"
            @change="handleAmenityChange(key, $event.target.checked)"
            type="checkbox"
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
</template>

<script setup>
import { computed } from 'vue'

// Format price for display
const formatPrice = (price) => {
  if (!price) return '0'
  return new Intl.NumberFormat('vi-VN').format(price)
}

const formatPriceDisplay = (price) => {
  if (!price || price === 0) return ''
  return formatPrice(price) + ' đ'
}

const props = defineProps({
  modelValue: {
    type: Object,
    required: true,
  },
  house: {
    type: Object,
    required: true,
  },
  errors: {
    type: Object,
    default: () => ({}),
  },
  hasInheritedAmenities: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['update:modelValue'])

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

const handleAmenityChange = (key, checked) => {
  const amenities = [...props.modelValue.amenities]
  if (checked) {
    if (!amenities.includes(key)) {
      amenities.push(key)
    }
  } else {
    const index = amenities.indexOf(key)
    if (index > -1) {
      amenities.splice(index, 1)
    }
  }
  emit('update:modelValue', { ...props.modelValue, amenities })
}
</script>
