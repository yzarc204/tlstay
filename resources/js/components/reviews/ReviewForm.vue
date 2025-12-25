<template>
  <div class="card p-6 mb-6">
    <h3 class="text-xl font-bold text-secondary mb-4">Viết đánh giá</h3>
    
    <form @submit.prevent="submitReview" class="space-y-4">
      <!-- Rating -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Đánh giá sao <span class="text-red-500">*</span>
        </label>
        <div class="flex items-center space-x-2">
          <button
            v-for="star in 5"
            :key="star"
            type="button"
            @click="form.rating = star"
            class="focus:outline-none"
          >
            <StarIcon
              :class="[
                'w-8 h-8 transition-colors',
                star <= form.rating ? 'text-yellow-400 fill-current' : 'text-gray-300'
              ]"
            />
          </button>
          <span v-if="form.rating" class="ml-2 text-sm text-gray-600">
            {{ form.rating }} / 5 sao
          </span>
        </div>
        <p v-if="errors.rating" class="mt-1 text-sm text-red-600">{{ errors.rating }}</p>
      </div>

      <!-- Comment -->
      <div>
        <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">
          Bình luận
        </label>
        <textarea
          id="comment"
          v-model="form.comment"
          rows="4"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
          placeholder="Chia sẻ trải nghiệm của bạn..."
        ></textarea>
        <p v-if="errors.comment" class="mt-1 text-sm text-red-600">{{ errors.comment }}</p>
      </div>

      <!-- Images -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Ảnh đánh giá (tối đa 5 ảnh)
        </label>
        <div class="flex items-center space-x-4">
          <label
            class="flex items-center justify-center px-4 py-2 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-primary transition-colors"
          >
            <input
              ref="fileInput"
              type="file"
              multiple
              accept="image/*"
              @change="handleImageChange"
              class="hidden"
            />
            <div class="text-center">
              <PhotoIcon class="w-8 h-8 text-gray-400 mx-auto mb-1" />
              <span class="text-sm text-gray-600">Thêm ảnh</span>
            </div>
          </label>
          
          <!-- Preview Images -->
          <div v-if="imagePreviews.length > 0" class="flex space-x-2">
            <div
              v-for="(preview, index) in imagePreviews"
              :key="index"
              class="relative w-20 h-20 rounded-lg overflow-hidden border border-gray-300"
            >
              <img :src="preview.url" :alt="`Preview ${index + 1}`" class="w-full h-full object-cover" />
              <button
                type="button"
                @click="removeImage(index)"
                class="absolute top-1 right-1 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600"
              >
                <XMarkIcon class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>
        <p v-if="errors.images" class="mt-1 text-sm text-red-600">{{ errors.images }}</p>
      </div>

      <!-- Submit Button -->
      <div class="flex justify-end space-x-3">
        <button
          type="button"
          @click="resetForm"
          class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
        >
          Hủy
        </button>
        <button
          type="submit"
          :disabled="processing || !form.rating"
          class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <span v-if="processing">Đang gửi...</span>
          <span v-else>Gửi đánh giá</span>
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import { StarIcon, PhotoIcon, XMarkIcon } from '@heroicons/vue/24/outline'
import { useToast } from '@/composables/useToast'

const fileInput = ref(null)

const props = defineProps({
  bookingId: {
    type: Number,
    required: true,
  },
  houseId: {
    type: Number,
    required: true,
  },
})

const emit = defineEmits(['review-created'])
const { showToast } = useToast()

const form = reactive({
  rating: 0,
  comment: '',
  images: [],
})

const imagePreviews = ref([])
const processing = ref(false)
const errors = ref({})

const handleImageChange = (event) => {
  const files = Array.from(event.target.files)
  
  if (files.length + imagePreviews.value.length > 5) {
    showToast('Tối đa 5 ảnh', 'error')
    return
  }

  files.forEach((file) => {
    if (file.size > 2 * 1024 * 1024) {
      showToast('Ảnh không được vượt quá 2MB', 'error')
      return
    }

    const reader = new FileReader()
    reader.onload = (e) => {
      imagePreviews.value.push({
        file,
        url: e.target.result,
      })
    }
    reader.readAsDataURL(file)
  })
}

const removeImage = (index) => {
  imagePreviews.value.splice(index, 1)
}

const resetForm = () => {
  form.rating = 0
  form.comment = ''
  form.images = []
  imagePreviews.value = []
  errors.value = {}
  
  // Reset file input
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const submitReview = () => {
  if (!form.rating) {
    errors.value.rating = 'Vui lòng chọn số sao đánh giá'
    return
  }

  processing.value = true
  errors.value = {}

  const formData = new FormData()
  formData.append('booking_id', props.bookingId)
  formData.append('rating', form.rating)
  formData.append('comment', form.comment || '')
  
  imagePreviews.value.forEach((preview) => {
    formData.append('images[]', preview.file)
  })

  router.post('/reviews', formData, {
    preserveScroll: true,
    onSuccess: () => {
      showToast('Đánh giá đã được gửi thành công!', 'success')
      resetForm()
      // Emit event để parent component reload và ẩn form
      emit('review-created')
    },
    onError: (err) => {
      errors.value = err
      if (err.error) {
        showToast(err.error, 'error')
      }
    },
    onFinish: () => {
      processing.value = false
    },
  })
}
</script>
