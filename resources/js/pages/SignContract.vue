<template>
  <Head title="Ký hợp đồng" />
  <AppLayout>
    <div class="sign-contract bg-light min-h-screen py-12">
      <div class="container mx-auto px-4 max-w-4xl">
        <!-- Header -->
        <div class="text-center mb-8">
          <h1 class="text-4xl font-bold text-secondary mb-2">Ký hợp đồng thuê trọ</h1>
          <p class="text-gray-600">Vui lòng ký hợp đồng để hoàn tất thủ tục</p>
        </div>

        <!-- Booking Info Card -->
        <div class="card p-6 mb-6">
          <h2 class="text-xl font-bold text-secondary mb-4">Thông tin đặt phòng</h2>
          <div class="grid md:grid-cols-2 gap-4">
            <div>
              <p class="text-sm text-gray-500 mb-1">Mã đặt phòng</p>
              <p class="font-semibold text-gray-900 font-mono">{{ booking.booking_code || `#${booking.id}` }}</p>
              <p v-if="booking.booking_code" class="text-xs text-gray-500 mt-0.5">ID: #{{ booking.id }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500 mb-1">Nhà trọ</p>
              <p class="font-semibold text-gray-900">{{ booking.house_name }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500 mb-1">Phòng</p>
              <p class="font-semibold text-gray-900">
                Phòng {{ booking.room_number }} - Tầng {{ booking.floor }}
              </p>
            </div>
            <div>
              <p class="text-sm text-gray-500 mb-1">Thời gian thuê</p>
              <p class="font-semibold text-gray-900">
                {{ formatDate(booking.start_date) }} - {{ formatDate(booking.end_date) }}
              </p>
            </div>
          </div>
        </div>

        <!-- Contract Preview -->
        <div class="card p-6 mb-6">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-secondary">Xem trước hợp đồng</h2>
            <a
              :href="`/contract/${booking.id}`"
              target="_blank"
              class="text-primary hover:text-secondary text-sm font-medium flex items-center"
            >
              <DocumentArrowDownIcon class="w-4 h-4 mr-1" />
              Tải PDF
            </a>
          </div>
          
          <!-- PDF Viewer -->
          <div class="border-2 border-gray-200 rounded-lg overflow-hidden bg-gray-50">
            <!-- PDF Controls -->
            <div class="bg-white border-b border-gray-200 px-4 py-2 flex items-center justify-end">
              <a
                :href="`/contract/${booking.id}`"
                target="_blank"
                class="text-sm text-primary hover:text-secondary font-medium flex items-center"
              >
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Mở trong tab mới
              </a>
            </div>
            
            <div v-if="pdfLoading" class="flex items-center justify-center h-96">
              <div class="text-center">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-primary border-t-transparent mb-4"></div>
                <p class="text-gray-600">Đang tải hợp đồng...</p>
              </div>
            </div>
            
            <div v-else-if="pdfError" class="p-8 text-center">
              <ExclamationCircleIcon class="w-12 h-12 text-red-500 mx-auto mb-4" />
              <p class="text-red-600 mb-4">{{ pdfError }}</p>
              <div class="flex gap-3 justify-center">
                <button
                  @click="loadPdf"
                  class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-secondary transition-colors"
                >
                  Thử lại
                </button>
                <a
                  :href="`/contract/${booking.id}`"
                  target="_blank"
                  class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
                >
                  Tải PDF
                </a>
              </div>
            </div>
            
            <div v-else class="pdf-viewer-container bg-gray-100" style="height: 800px; overflow: auto;">
              <iframe
                :src="pdfUrl"
                class="w-full h-full"
                style="border: none; min-height: 800px;"
                @load="onPdfLoad"
                @error="onPdfError"
              ></iframe>
            </div>
          </div>
          
          <p class="text-xs text-gray-500 mt-3 text-center">
            Vui lòng đọc kỹ hợp đồng trước khi ký. Bạn có thể tải PDF để xem chi tiết hơn.
          </p>
        </div>

        <!-- Signature Form -->
        <div class="card p-6">
          <h2 class="text-xl font-bold text-secondary mb-4">Ký hợp đồng</h2>
          
          <form @submit.prevent="handleSign" class="space-y-6">
            <!-- Signature Pad -->
            <SignaturePad
              v-model="signatureForm.signature"
              :has-error="!!signatureForm.errors.signature"
              :error-message="signatureForm.errors.signature"
              label="Chữ ký của bạn"
              hint="Vẽ chữ ký của bạn bằng chuột hoặc ngón tay trên vùng bên dưới"
              required
            />

            <!-- Agreement Checkbox -->
            <div class="flex items-start">
              <input
                id="agree"
                v-model="signatureForm.agree"
                type="checkbox"
                class="mt-1 w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary"
                required
              />
              <label for="agree" class="ml-3 text-sm text-gray-700">
                Tôi đã đọc và đồng ý với tất cả các điều khoản trong hợp đồng thuê trọ này.
                <span class="text-red-500">*</span>
              </label>
            </div>
            <p v-if="signatureForm.errors.agree" class="text-sm text-red-600">
              {{ signatureForm.errors.agree }}
            </p>

            <!-- Error Message -->
            <div v-if="errorMessage" class="p-4 bg-red-50 border border-red-200 rounded-lg">
              <p class="text-sm text-red-600">{{ errorMessage }}</p>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 pt-4">
              <button
                type="submit"
                :disabled="signatureForm.processing || !signatureForm.signature || !signatureForm.agree"
                class="flex-1 btn-primary disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="!signatureForm.processing">Xác nhận và ký hợp đồng</span>
                <span v-else>Đang xử lý...</span>
              </button>
              <Link
                :href="`/booking/${booking.id}/success`"
                class="flex-1 btn-outline text-center"
              >
                Hủy
              </Link>
            </div>
          </form>
        </div>

        <!-- Important Notice -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-6">
          <div class="flex items-start">
            <InformationCircleIcon class="w-6 h-6 text-blue-600 mr-3 mt-0.5 flex-shrink-0" />
            <div>
              <h3 class="text-lg font-semibold text-blue-900 mb-2">Lưu ý quan trọng</h3>
              <ul class="space-y-2 text-blue-800 text-sm">
                <li class="flex items-start">
                  <span class="mr-2">•</span>
                  <span>
                    Chữ ký của bạn sẽ được lưu trữ và hiển thị trên hợp đồng PDF
                  </span>
                </li>
                <li class="flex items-start">
                  <span class="mr-2">•</span>
                  <span>
                    Sau khi ký, hợp đồng sẽ có hiệu lực pháp lý
                  </span>
                </li>
                <li class="flex items-start">
                  <span class="mr-2">•</span>
                  <span>
                    Bạn có thể tải hợp đồng đã ký bất cứ lúc nào trong lịch sử thuê
                  </span>
                </li>
                <li class="flex items-start">
                  <span class="mr-2">•</span>
                  <span>
                    Vui lòng đảm bảo chữ ký của bạn rõ ràng và khớp với chữ ký trên CMND/CCCD
                  </span>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, router, useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import SignaturePad from '@/components/ui/SignaturePad.vue'
import {
  DocumentArrowDownIcon,
  InformationCircleIcon,
  ExclamationCircleIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  booking: {
    type: Object,
    required: true,
  },
})

const errorMessage = ref('')
const pdfLoading = ref(true)
const pdfError = ref('')

const signatureForm = useForm({
  signature: '',
  agree: false,
})

const pdfUrl = computed(() => {
  return `/contract/${props.booking.id}?embed=true`
})

const onPdfLoad = () => {
  pdfLoading.value = false
  pdfError.value = ''
}

const onPdfError = () => {
  pdfLoading.value = false
  pdfError.value = 'Không thể tải hợp đồng. Vui lòng thử lại hoặc tải PDF để xem.'
}

const loadPdf = () => {
  pdfLoading.value = true
  pdfError.value = ''
  // Force reload iframe
  const iframe = document.querySelector('.pdf-viewer-container iframe')
  if (iframe) {
    iframe.src = iframe.src
  }
}

onMounted(() => {
  // Set timeout to hide loading if PDF takes too long
  setTimeout(() => {
    if (pdfLoading.value) {
      pdfLoading.value = false
    }
  }, 10000) // 10 seconds timeout
})

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('vi-VN', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
  })
}

const handleSign = () => {
  errorMessage.value = ''
  
  signatureForm.post(`/contract/${props.booking.id}/sign`, {
    preserveScroll: true,
    onSuccess: () => {
      router.visit(`/booking/${props.booking.id}/success`, {
        data: { signed: true },
      })
    },
    onError: (errors) => {
      console.error('Sign contract errors:', errors)
      if (errors.message) {
        errorMessage.value = errors.message
      }
    },
  })
}
</script>
