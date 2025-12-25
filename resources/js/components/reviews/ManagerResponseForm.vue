<template>
  <div>
    <!-- Modal Overlay -->
    <div
      v-if="showAsModal"
      class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
      @click.self="$emit('cancel')"
    >
      <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold text-gray-900">
              {{ initialResponse ? 'Chỉnh sửa phản hồi' : 'Phản hồi đánh giá' }}
            </h3>
            <button
              v-if="showAsModal"
              @click="$emit('cancel')"
              class="text-gray-400 hover:text-gray-600"
            >
              <XMarkIcon class="w-6 h-6" />
            </button>
          </div>
          <form @submit.prevent="submitResponse" class="space-y-4">
            <div>
              <label for="response" class="block text-sm font-medium text-gray-700 mb-2">
                Phản hồi <span class="text-red-500">*</span>
              </label>
              <textarea
                id="response"
                v-model="form.manager_response"
                rows="6"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                placeholder="Nhập phản hồi của bạn..."
                required
              ></textarea>
              <p v-if="errors.manager_response" class="mt-1 text-sm text-red-600">
                {{ errors.manager_response }}
              </p>
            </div>
            <div class="flex justify-end space-x-3">
              <button
                v-if="showAsModal"
                type="button"
                @click="$emit('cancel')"
                class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
              >
                Hủy
              </button>
              <button
                type="submit"
                :disabled="processing"
                class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="processing">Đang gửi...</span>
                <span v-else>{{ initialResponse ? 'Cập nhật' : 'Gửi phản hồi' }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Inline Form -->
    <div v-else class="space-y-4">
      <div>
        <label for="response" class="block text-sm font-medium text-gray-700 mb-2">
          Phản hồi đánh giá <span class="text-red-500">*</span>
        </label>
        <textarea
          id="response"
          v-model="form.manager_response"
          rows="4"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
          placeholder="Nhập phản hồi của bạn..."
          required
        ></textarea>
        <p v-if="errors.manager_response" class="mt-1 text-sm text-red-600">
          {{ errors.manager_response }}
        </p>
      </div>
      <div class="flex justify-end space-x-3">
        <button
          type="button"
          @click="resetForm"
          class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 text-sm"
        >
          Hủy
        </button>
        <button
          type="submit"
          @click="submitResponse"
          :disabled="processing"
          class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark disabled:opacity-50 disabled:cursor-not-allowed text-sm"
        >
          <span v-if="processing">Đang gửi...</span>
          <span v-else>Gửi phản hồi</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { XMarkIcon } from '@heroicons/vue/24/outline'
import { useToast } from '@/composables/useToast'

const props = defineProps({
  reviewId: {
    type: Number,
    required: true,
  },
  initialResponse: {
    type: String,
    default: null,
  },
  showAsModal: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['response-sent', 'cancel'])

const { showToast } = useToast()

const form = reactive({
  manager_response: props.initialResponse || '',
})

const processing = ref(false)
const errors = ref({})

watch(() => props.initialResponse, (newValue) => {
  form.manager_response = newValue || ''
})

const resetForm = () => {
  form.manager_response = props.initialResponse || ''
  errors.value = {}
}

const submitResponse = () => {
  if (!form.manager_response.trim()) {
    errors.value.manager_response = 'Vui lòng nhập phản hồi'
    return
  }

  processing.value = true
  errors.value = {}

  const url = props.initialResponse
    ? `/admin/reviews/${props.reviewId}/response`
    : `/admin/reviews/${props.reviewId}/respond`

  // Use router.post with _method for PUT requests
  const formData = props.initialResponse
    ? { ...form, _method: 'PUT' }
    : form

  router.post(url, formData, {
    preserveScroll: true,
    onSuccess: () => {
      showToast(
        props.initialResponse ? 'Phản hồi đã được cập nhật!' : 'Phản hồi đã được gửi thành công!',
        'success'
      )
      resetForm()
      emit('response-sent')
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
