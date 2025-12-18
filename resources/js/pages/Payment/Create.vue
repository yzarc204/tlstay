<template>
  <AppLayout title="Thanh toán đặt phòng">
    <div class="min-h-screen bg-gray-50 py-8">
      <div class="max-w-4xl mx-auto px-4">
        <!-- Header -->
        <div class="mb-6">
          <h1 class="text-2xl font-bold text-gray-900">Thanh toán đặt phòng</h1>
          <p class="text-gray-600 mt-1">Vui lòng quét mã QR để thanh toán</p>
        </div>

        <!-- Flash Messages -->
        <div v-if="$page.props.flash?.info" class="p-4 bg-blue-50 border border-blue-200 rounded-lg mb-6">
          <p class="text-blue-800">{{ $page.props.flash.info }}</p>
        </div>
        <div v-if="$page.props.flash?.error" class="p-4 bg-red-50 border border-red-200 rounded-lg mb-6">
          <p class="text-red-800">{{ $page.props.flash.error }}</p>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 md:p-8 space-y-8">
          <!-- Booking Information -->
          <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Thông tin đặt phòng</h2>
            <div class="grid md:grid-cols-2 gap-4">
              <div>
                <p class="text-sm text-gray-600">Mã đặt phòng</p>
                <p class="text-lg font-semibold text-gray-900">{{ booking.booking_code }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-600">Nhà trọ</p>
                <p class="text-lg font-semibold text-gray-900">{{ booking.house_name }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-600">Phòng</p>
                <p class="text-lg font-semibold text-gray-900">Phòng {{ booking.room_number }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-600">Thời gian</p>
                <p class="text-lg font-semibold text-gray-900">
                  {{ formatDate(booking.start_date) }} - {{ formatDate(booking.end_date) }}
                </p>
              </div>
              <div class="md:col-span-2">
                <p class="text-sm text-gray-600">Tổng tiền</p>
                <p class="text-2xl font-bold text-primary">{{ formatPrice(booking.total_price) }}</p>
              </div>
            </div>
          </div>

          <!-- Payment Information -->
          <div class="border-t pt-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Thông tin chuyển khoản</h2>
            <div class="bg-gray-50 rounded-lg p-4 space-y-2">
              <div class="flex justify-between items-center">
                <span class="text-gray-600">Ngân hàng:</span>
                <div class="flex items-center space-x-2">
                  <img
                    v-if="payment_info.bank_name"
                    :src="getBankLogo(payment_info.bank_name)"
                    :alt="getBankName(payment_info.bank_name)"
                    class="h-6 w-6 object-contain"
                    @error="handleImageError"
                  />
                  <span class="font-semibold text-gray-900">{{ getBankName(payment_info.bank_name) }}</span>
                </div>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Số tài khoản:</span>
                <span class="font-semibold text-gray-900">{{ payment_info.bank_account_number }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Tên chủ tài khoản:</span>
                <span class="font-semibold text-gray-900">{{ payment_info.bank_account_holder }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Số tiền:</span>
                <span class="font-semibold text-primary">{{ formatPrice(booking.total_price) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Nội dung chuyển khoản:</span>
                <span class="font-semibold text-gray-900">{{ booking.booking_code }}</span>
              </div>
            </div>
          </div>

          <!-- QR Code -->
          <div class="border-t pt-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 text-center">Quét mã QR để thanh toán</h2>
            <div class="flex justify-center">
              <div class="bg-white p-4 rounded-lg border-2 border-gray-200">
                <img
                  :src="qr_url"
                  alt="VietQR Code"
                  class="w-64 h-64 md:w-80 md:h-80"
                />
              </div>
            </div>
            <p class="text-center text-sm text-gray-600 mt-4">
              Vui lòng quét mã QR bằng ứng dụng ngân hàng của bạn để thanh toán
            </p>
          </div>

          <!-- Confirm Button -->
          <div class="border-t pt-6">
            <form @submit.prevent="handleConfirm">
              <button
                type="submit"
                :disabled="form.processing || isChecking"
                class="w-full px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed font-semibold text-lg"
              >
                <span v-if="!form.processing && !isChecking">Xác nhận đã chuyển khoản</span>
                <span v-else-if="isChecking" class="flex items-center justify-center">
                  <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Đang kiểm tra thanh toán...
                </span>
                <span v-else>Đang xử lý...</span>
              </button>
              <p class="text-center text-sm text-gray-500 mt-2">
                Sau khi đã chuyển khoản thành công, vui lòng nhấn nút trên để xác nhận
              </p>
            </form>
          </div>
          
          <!-- Loading Overlay -->
          <Transition
            enter-active-class="transition-opacity duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-300"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
          >
            <div
              v-if="isChecking"
              class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center"
            >
              <div class="bg-white rounded-lg p-8 max-w-md mx-4 text-center">
                <svg class="animate-spin h-12 w-12 text-primary mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Đang kiểm tra thanh toán</h3>
                <p class="text-sm text-gray-600">Vui lòng đợi trong giây lát...</p>
              </div>
            </div>
          </Transition>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({
  booking: {
    type: Object,
    required: true,
  },
  payment_info: {
    type: Object,
    required: true,
  },
  qr_url: {
    type: String,
    required: true,
  },
})

const form = useForm({})
const isChecking = ref(false)

const formatPrice = (price) => {
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(price)
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('vi-VN', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
  })
}

const getBankLogo = (bankCode) => {
  if (!bankCode) return null
  // Use API logo URL format
  return `https://api.vietqr.io/img/${bankCode}.png`
}

const getBankName = (bankCode) => {
  const banks = {
    VCB: 'Vietcombank',
    TCB: 'Techcombank',
    BID: 'BIDV',
    VTB: 'VietinBank',
    ACB: 'ACB',
    TPB: 'TPBank',
    VPB: 'VPBank',
    MSB: 'MSB',
    HDB: 'HDBank',
    VIB: 'VIB',
    SHB: 'SHB',
    STB: 'Sacombank',
    OCB: 'OCB',
    MB: 'MB Bank',
    VCCB: 'Viet Capital Bank',
    NAB: 'Nam A Bank',
    BAB: 'Bac A Bank',
    SCB: 'SCB',
    VAB: 'Viet A Bank',
    PGB: 'PGBank',
    ABB: 'An Bình',
    DAB: 'Dong A Bank',
    EIB: 'Eximbank',
    GPB: 'GPBank',
    KLB: 'Kienlongbank',
    LPB: 'LienVietPostBank',
    NCB: 'NCB',
    OJB: 'OceanBank',
    PUB: 'PublicBank',
    SEAB: 'SeABank',
    VDB: 'VietABank',
  }
  return banks[bankCode] || bankCode
}

const handleImageError = (event) => {
  // Hide broken image
  event.target.style.display = 'none'
}

const handleConfirm = () => {
  // Show loading spinner for 3 seconds (simulating payment check)
  isChecking.value = true
  
  setTimeout(() => {
    // After 3 seconds, submit the form
    form.post(`/payment/${props.booking.id}/confirm`, {
      preserveScroll: true,
      onFinish: () => {
        isChecking.value = false
      },
    })
  }, 3000)
}
</script>
