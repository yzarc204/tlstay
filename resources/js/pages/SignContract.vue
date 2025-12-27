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
          <PDFViewer
            :pdf-url="pdfUrl"
            :height="800"
            :max-height="'800px'"
            :show-controls="true"
            @loaded="onPdfLoaded"
            @error="onPdfError"
          />
          
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
import { ref, computed } from 'vue'
import { Head, router, useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import SignaturePad from '@/components/ui/SignaturePad.vue'
import PDFViewer from '@/components/ui/PDFViewer.vue'
import {
  DocumentArrowDownIcon,
  InformationCircleIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  booking: {
    type: Object,
    required: true,
  },
})

const errorMessage = ref('')

const signatureForm = useForm({
  signature: '',
  agree: false,
})

const pdfUrl = computed(() => {
  // Use preview route which checks cache first
  return `/contract/${props.booking.id}/preview`
})

const onPdfLoaded = () => {
  // PDF loaded successfully
}

const onPdfError = () => {
  // Error handled by PDFViewer component
}

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
