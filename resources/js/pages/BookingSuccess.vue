<template>
  <AppLayout>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8 md:py-12">
      <div class="max-w-4xl mx-auto px-4">
        <!-- Success Header -->
        <div class="text-center mb-8">
          <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-6 animate-pulse">
            <CheckCircleIcon class="w-12 h-12 text-green-600" />
          </div>
          <h1 class="text-4xl font-bold text-gray-900 mb-3">Đặt phòng thành công!</h1>
          <p class="text-lg text-gray-600">Vui lòng hoàn tất các bước sau để hoàn thành đặt phòng</p>
        </div>

        <!-- Flash Messages -->
        <div v-if="$page.props.flash?.success" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
          <p class="text-green-800">{{ $page.props.flash.success }}</p>
        </div>

        <!-- Step Indicator -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
          <div class="relative">
            <div class="grid grid-cols-3 items-center">
              <!-- Step 1 -->
              <div class="flex flex-col items-center relative z-10">
                <div
                  class="flex items-center justify-center w-14 h-14 rounded-full font-bold text-lg transition-all duration-300"
                  :class="
                    currentStep === 1
                      ? 'bg-primary text-white ring-4 ring-primary/20'
                      : step1Confirmed
                      ? 'bg-secondary text-white'
                      : 'bg-gray-200 text-gray-500'
                  "
                >
                  <CheckIcon v-if="step1Confirmed" class="w-7 h-7" />
                  <span v-else class="text-xl">1</span>
                </div>
                <p
                  class="mt-3 text-sm font-medium text-center"
                  :class="
                    currentStep === 1
                      ? 'text-primary font-semibold'
                      : step1Confirmed
                      ? 'text-secondary'
                      : 'text-gray-500'
                  "
                >
                  Thông tin đặt phòng
                </p>
              </div>

              <!-- Step 2 (Center) -->
              <div class="flex flex-col items-center relative z-10">
                <div
                  class="flex items-center justify-center w-14 h-14 rounded-full font-bold text-lg transition-all duration-300"
                  :class="
                    currentStep === 2
                      ? 'bg-primary text-white ring-4 ring-primary/20'
                      : step2Confirmed
                      ? 'bg-secondary text-white'
                      : step1Confirmed
                      ? 'bg-gray-300 text-gray-600'
                      : 'bg-gray-200 text-gray-400'
                  "
                >
                  <CheckIcon v-if="step2Confirmed" class="w-7 h-7" />
                  <span v-else class="text-xl">2</span>
                </div>
                <p
                  class="mt-3 text-sm font-medium text-center"
                  :class="
                    currentStep === 2
                      ? 'text-primary font-semibold'
                      : step2Confirmed
                      ? 'text-secondary'
                      : step1Confirmed
                      ? 'text-gray-600'
                      : 'text-gray-400'
                  "
                >
                  Thông tin thanh toán
                </p>
              </div>

              <!-- Step 3 -->
              <div class="flex flex-col items-center relative z-10">
                <div
                  class="flex items-center justify-center w-14 h-14 rounded-full font-bold text-lg transition-all duration-300"
                  :class="
                    currentStep === 3
                      ? 'bg-primary text-white ring-4 ring-primary/20'
                      : booking.contract_signed
                      ? 'bg-secondary text-white'
                      : step2Confirmed
                      ? 'bg-gray-300 text-gray-600'
                      : 'bg-gray-200 text-gray-400'
                  "
                >
                  <CheckIcon v-if="booking.contract_signed" class="w-7 h-7" />
                  <span v-else class="text-xl">3</span>
                </div>
                <p
                  class="mt-3 text-sm font-medium text-center"
                  :class="
                    currentStep === 3
                      ? 'text-primary font-semibold'
                      : booking.contract_signed
                      ? 'text-secondary'
                      : step2Confirmed
                      ? 'text-gray-600'
                      : 'text-gray-400'
                  "
                >
                  Ký hợp đồng
                </p>
              </div>
            </div>
            <!-- Connector Lines - positioned absolutely between step circles -->
            <div class="absolute top-7 left-0 right-0 h-1 z-0">
              <!-- Line from Step 1 to Step 2 -->
              <div
                class="absolute h-1 transition-all duration-300"
                style="left: calc(16.666% + 28px); right: calc(50% + 28px);"
                :class="step1Confirmed ? 'bg-secondary' : 'bg-gray-200'"
              ></div>
              <!-- Line from Step 2 to Step 3 -->
              <div
                class="absolute h-1 transition-all duration-300"
                style="left: calc(50% + 28px); right: calc(16.666% + 28px);"
                :class="step2Confirmed ? 'bg-secondary' : 'bg-gray-200'"
              ></div>
            </div>
          </div>
        </div>

        <!-- Step Content -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
          <!-- Step 1: Thông tin đặt phòng -->
          <div v-show="currentStep === 1" class="p-8">
            <div class="mb-6">
              <h2 class="text-2xl font-bold text-gray-900 mb-2">Thông tin đặt phòng</h2>
              <p class="text-gray-600">Vui lòng kiểm tra và xác nhận thông tin đặt phòng của bạn</p>
            </div>

            <div class="space-y-6">
              <div class="grid md:grid-cols-2 gap-6">
                <div class="space-y-5">
                  <div class="bg-blue-50 border-l-4 border-blue-500 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                      <label class="text-sm font-medium text-blue-900">Mã đặt phòng</label>
                      <span class="px-2 py-1 text-xs font-semibold bg-blue-200 text-blue-800 rounded-full">
                        Quan trọng
                      </span>
                    </div>
                    <p class="text-lg font-bold text-blue-900 font-mono">
                      {{ booking.booking_code }}
                    </p>
                    <p class="text-xs text-blue-700 mt-1">Vui lòng lưu mã này để tra cứu sau</p>
                  </div>
                  <div class="bg-purple-50 border-l-4 border-purple-500 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                      <label class="text-sm font-medium text-purple-900">Nhà trọ</label>
                      <span class="px-2 py-1 text-xs font-semibold bg-purple-200 text-purple-800 rounded-full">
                        Địa điểm
                      </span>
                    </div>
                    <p class="text-lg font-bold text-purple-900">{{ booking.house_name }}</p>
                    <p class="text-xs text-purple-700 mt-1">{{ booking.house_address }}</p>
                  </div>
                  <div class="bg-teal-50 border-l-4 border-teal-500 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                      <label class="text-sm font-medium text-teal-900">Phòng</label>
                      <span class="px-2 py-1 text-xs font-semibold bg-teal-200 text-teal-800 rounded-full">
                        Phòng
                      </span>
                    </div>
                    <p class="text-lg font-bold text-teal-900">
                      Phòng {{ booking.room_number }} - Tầng {{ booking.floor }}
                    </p>
                  </div>
                </div>
                <div class="space-y-5">
                  <div class="bg-amber-50 border-l-4 border-amber-500 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                      <label class="text-sm font-medium text-amber-900">Ngày nhận phòng</label>
                      <span class="px-2 py-1 text-xs font-semibold bg-amber-200 text-amber-800 rounded-full">
                        Lưu ý
                      </span>
                    </div>
                    <p class="text-lg font-bold text-amber-900">{{ formatDate(booking.start_date) }}</p>
                    <p class="text-xs text-amber-700 mt-1">Vui lòng đến đúng ngày để nhận phòng</p>
                  </div>
                  <div class="bg-orange-50 border-l-4 border-orange-500 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                      <label class="text-sm font-medium text-orange-900">Ngày trả phòng</label>
                      <span class="px-2 py-1 text-xs font-semibold bg-orange-200 text-orange-800 rounded-full">
                        Hạn chót
                      </span>
                    </div>
                    <p class="text-lg font-bold text-orange-900">{{ formatDate(booking.end_date) }}</p>
                    <p class="text-xs text-orange-700 mt-1">Vui lòng trả phòng đúng hạn</p>
                  </div>
                  <div class="bg-green-50 border-l-4 border-green-500 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                      <label class="text-sm font-medium text-green-900">Số ngày thuê</label>
                      <span class="px-2 py-1 text-xs font-semibold bg-green-200 text-green-800 rounded-full">
                        Thông tin
                      </span>
                    </div>
                    <p class="text-lg font-bold text-green-900">
                      {{ calculateDays(booking.start_date, booking.end_date) }} ngày
                    </p>
                  </div>
                </div>
              </div>

              <div v-if="booking.notes" class="pt-6 border-t border-gray-200">
                <label class="text-sm font-medium text-gray-500 block mb-2">Ghi chú</label>
                <p class="text-gray-700 bg-gray-50 px-4 py-3 rounded-lg">{{ booking.notes }}</p>
              </div>

              <div class="pt-6 border-t border-gray-200 flex justify-end">
                <button
                  @click="confirmStep1"
                  class="px-8 py-3 bg-primary text-white rounded-lg hover:bg-primary-700 transition-colors font-medium shadow-sm hover:shadow-md"
                >
                  Xác nhận và tiếp tục
                  <ArrowRightIcon class="w-5 h-5 inline-block ml-2" />
                </button>
              </div>
            </div>
          </div>

          <!-- Step 2: Thông tin thanh toán -->
          <div v-show="currentStep === 2" class="p-8">
            <div class="mb-6">
              <h2 class="text-2xl font-bold text-gray-900 mb-2">Thông tin thanh toán</h2>
              <p class="text-gray-600">Vui lòng kiểm tra và xác nhận thông tin thanh toán</p>
            </div>

            <div class="space-y-6">
              <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-6 border border-gray-200">
                <div class="space-y-4">
                  <div class="flex justify-between items-center py-3 border-b border-gray-300">
                    <span class="text-gray-700 font-medium">Tổng tiền phòng</span>
                    <span class="font-semibold text-gray-900 text-lg">
                      {{ formatPrice(booking.total_price + booking.discount_amount) }}
                    </span>
                  </div>
                  <div
                    v-if="booking.discount_amount > 0"
                    class="flex justify-between items-center py-3 border-b border-gray-300"
                  >
                    <span class="text-gray-700 font-medium">Giảm giá</span>
                    <span class="font-semibold text-green-600 text-lg">
                      -{{ formatPrice(booking.discount_amount) }}
                    </span>
                  </div>
                  <div class="flex justify-between items-center pt-4 mt-2 border-t-2 border-gray-400">
                    <span class="text-xl font-bold text-gray-900">Tổng thanh toán</span>
                    <span class="text-3xl font-bold text-primary">
                      {{ formatPrice(booking.total_price) }}
                    </span>
                  </div>
                </div>
              </div>

              <div class="grid md:grid-cols-2 gap-6 pt-4">
                <div class="bg-gray-50 rounded-lg p-4">
                  <label class="text-sm font-medium text-gray-500 block mb-2">Phương thức thanh toán</label>
                  <p class="font-semibold text-gray-900 text-lg">
                    {{ getPaymentMethodText(booking.payment_method) }}
                  </p>
                </div>
                <div v-if="booking.paid_at" class="bg-gray-50 rounded-lg p-4">
                  <label class="text-sm font-medium text-gray-500 block mb-2">Thời gian thanh toán</label>
                  <p class="font-semibold text-gray-900 text-lg">{{ formatDateTime(booking.paid_at) }}</p>
                </div>
              </div>

              <div v-if="booking.vnpay_transaction_id" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <label class="text-sm font-medium text-blue-900 block mb-2">Mã giao dịch</label>
                <p class="font-mono text-sm text-blue-900 break-all">{{ booking.vnpay_transaction_id }}</p>
              </div>

              <div class="pt-6 border-t border-gray-200 flex justify-between">
                <button
                  @click="goToStep(1)"
                  class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium"
                >
                  <ArrowLeftIcon class="w-5 h-5 inline-block mr-2" />
                  Quay lại
                </button>
                <button
                  @click="confirmStep2"
                  class="px-8 py-3 bg-primary text-white rounded-lg hover:bg-primary-700 transition-colors font-medium shadow-sm hover:shadow-md"
                >
                  Xác nhận và tiếp tục
                  <ArrowRightIcon class="w-5 h-5 inline-block ml-2" />
                </button>
              </div>
            </div>
          </div>

          <!-- Step 3: Ký hợp đồng -->
          <div v-show="currentStep === 3" class="p-8">
            <div class="mb-6">
              <h2 class="text-2xl font-bold text-gray-900 mb-2">Ký hợp đồng</h2>
              <p class="text-gray-600">Ký hợp đồng để hoàn tất thủ tục đặt phòng</p>
            </div>

            <div v-if="!booking.contract_signed" class="space-y-6">
              <div class="bg-blue-50 border-l-4 border-blue-500 rounded-lg p-6">
                <div class="flex items-start">
                  <InformationCircleIcon class="w-6 h-6 text-blue-600 mr-3 flex-shrink-0 mt-0.5" />
                  <div>
                    <h3 class="font-semibold text-blue-900 mb-3">Thông tin quan trọng</h3>
                    <ul class="space-y-2 text-sm text-blue-800">
                      <li class="flex items-start">
                        <span class="text-blue-600 mr-2">•</span>
                        <span>Vui lòng đọc kỹ hợp đồng trước khi ký</span>
                      </li>
                      <li class="flex items-start">
                        <span class="text-blue-600 mr-2">•</span>
                        <span>Hợp đồng có hiệu lực pháp lý sau khi được ký</span>
                      </li>
                      <li class="flex items-start">
                        <span class="text-blue-600 mr-2">•</span>
                        <span>Bạn có thể tải hợp đồng bất cứ lúc nào</span>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>

              <div class="flex flex-col sm:flex-row gap-4">
                <a
                  :href="`/contract/${booking.id}`"
                  target="_blank"
                  class="flex-1 px-6 py-4 border-2 border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors font-medium text-center flex items-center justify-center"
                >
                  <DocumentArrowDownIcon class="w-5 h-5 mr-2" />
                  Xem hợp đồng
                </a>
                <Link
                  :href="`/contract/${booking.id}/sign`"
                  class="flex-1 px-6 py-4 bg-primary text-white rounded-lg hover:bg-primary-700 transition-colors font-medium text-center flex items-center justify-center shadow-sm hover:shadow-md"
                >
                  <PencilIcon class="w-5 h-5 mr-2" />
                  Ký hợp đồng
                </Link>
              </div>

              <div class="pt-6 border-t border-gray-200 flex justify-start">
                <button
                  @click="goToStep(2)"
                  class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium"
                >
                  <ArrowLeftIcon class="w-5 h-5 inline-block mr-2" />
                  Quay lại
                </button>
              </div>
            </div>

            <div v-else class="space-y-6">
              <div class="text-center py-8">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-4">
                  <CheckCircleIcon class="w-12 h-12 text-green-600" />
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Đã ký hợp đồng</h3>
                <p v-if="booking.signed_at" class="text-gray-600">
                  Đã ký vào: {{ formatDateTime(booking.signed_at) }}
                </p>
              </div>

              <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                <div class="flex items-center justify-between mb-4">
                  <div>
                    <p class="text-sm font-medium text-gray-500">Hợp đồng thuê trọ</p>
                    <p class="text-sm text-gray-600 mt-1">Mã đặt phòng: {{ booking.booking_code }}</p>
                  </div>
                  <a
                    :href="`/contract/${booking.id}`"
                    target="_blank"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors text-sm font-medium text-gray-700"
                  >
                    <DocumentArrowDownIcon class="w-4 h-4 mr-2" />
                    Tải PDF
                  </a>
                </div>
              </div>

              <div class="bg-green-50 border-l-4 border-green-500 rounded-lg p-6">
                <h3 class="font-semibold text-green-900 mb-3">Thông tin quan trọng</h3>
                <ul class="space-y-2 text-sm text-green-800">
                  <li class="flex items-start">
                    <span class="text-green-600 mr-2">✓</span>
                    <span>Vui lòng đến nhận phòng đúng ngày {{ formatDate(booking.start_date) }}</span>
                  </li>
                  <li class="flex items-start">
                    <span class="text-green-600 mr-2">✓</span>
                    <span>Mang theo CMND/CCCD và hợp đồng thuê khi đến nhận phòng</span>
                  </li>
                  <li class="flex items-start">
                    <span class="text-green-600 mr-2">✓</span>
                    <span>Nếu có thắc mắc, vui lòng liên hệ với chủ nhà trọ</span>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-8 flex flex-wrap gap-4 justify-center">
          <Link
            href="/history"
            class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors font-medium"
          >
            Lịch sử thuê
          </Link>
          <Link
            href="/"
            class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary-700 transition-colors font-medium"
          >
            Về trang chủ
          </Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import {
  CheckCircleIcon,
  CheckIcon,
  DocumentArrowDownIcon,
  PencilIcon,
  InformationCircleIcon,
  ArrowRightIcon,
  ArrowLeftIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  booking: {
    type: Object,
    required: true,
  },
})

const currentStep = ref(1)
const step1Confirmed = ref(false)
const step2Confirmed = ref(false)

// Load confirmed steps and current step from localStorage
onMounted(() => {
  const step1 = localStorage.getItem(`booking_${props.booking.id}_step1`)
  const step2 = localStorage.getItem(`booking_${props.booking.id}_step2`)
  const savedStep = localStorage.getItem(`booking_${props.booking.id}_currentStep`)

  if (step1 === 'confirmed') {
    step1Confirmed.value = true
  }
  if (step2 === 'confirmed') {
    step2Confirmed.value = true
  }

  // Determine current step based on confirmed steps
  if (savedStep) {
    const step = parseInt(savedStep)
    if (step >= 1 && step <= 3) {
      currentStep.value = step
    }
  } else {
    // Auto-determine step based on confirmed status
    if (!step1Confirmed.value) {
      currentStep.value = 1
    } else if (!step2Confirmed.value) {
      currentStep.value = 2
    } else if (!props.booking.contract_signed) {
      currentStep.value = 3
    } else {
      currentStep.value = 3 // Show step 3 even if contract is signed
    }
  }
})

const goToStep = (step) => {
  if (step >= 1 && step <= 3) {
    currentStep.value = step
    localStorage.setItem(`booking_${props.booking.id}_currentStep`, step.toString())
  }
}

const confirmStep1 = () => {
  step1Confirmed.value = true
  localStorage.setItem(`booking_${props.booking.id}_step1`, 'confirmed')
  goToStep(2)
}

const confirmStep2 = () => {
  step2Confirmed.value = true
  localStorage.setItem(`booking_${props.booking.id}_step2`, 'confirmed')
  goToStep(3)
}

const formatPrice = (price) => {
  if (!price && price !== 0) return '0 ₫'
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(price)
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

const formatDateTime = (dateTimeString) => {
  if (!dateTimeString) return ''
  const date = new Date(dateTimeString)
  return date.toLocaleString('vi-VN', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const calculateDays = (startDate, endDate) => {
  if (!startDate || !endDate) return 0
  const start = new Date(startDate)
  const end = new Date(endDate)
  const diffTime = Math.abs(end - start)
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  return diffDays + 1
}

const getPaymentMethodText = (method) => {
  const methods = {
    bank_transfer: 'Chuyển khoản ngân hàng',
    vnpay: 'VNPay',
    wallet: 'Ví điện tử',
    cash: 'Tiền mặt',
  }
  return methods[method] || method || 'Chưa xác định'
}
</script>
