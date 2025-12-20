<template>
  <div class="space-y-4">
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
            @click="toggleImage(image)"
          >
            <div class="relative overflow-hidden rounded-lg border-2 transition-all"
              :class="isSelected(image)
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
                :class="isSelected(image) 
                  ? 'bg-primary text-white ring-2 ring-primary ring-offset-2' 
                  : 'bg-white text-gray-400 hover:bg-gray-50'"
              >
                <svg
                  v-if="isSelected(image)"
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
        <p v-if="selectedImages.length > 0" class="text-xs text-green-600 mt-2">
          Đã chọn {{ selectedImages.length }} ảnh cho phòng này
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
</template>

<script setup>
const props = defineProps({
  houseImages: {
    type: Array,
    default: () => [],
  },
  selectedImages: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits(['update:selectedImages'])

const isSelected = (imageUrl) => {
  return props.selectedImages.includes(imageUrl)
}

const toggleImage = (imageUrl) => {
  const images = [...props.selectedImages]
  const index = images.indexOf(imageUrl)
  if (index > -1) {
    images.splice(index, 1)
  } else {
    images.push(imageUrl)
  }
  emit('update:selectedImages', images)
}
</script>
