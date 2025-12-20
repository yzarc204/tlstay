<template>
  <AppLayout>
    <div class="min-h-screen bg-gray-50 py-8">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
          <Link
            href="/invoices"
            class="inline-flex items-center text-primary hover:text-primary-600 mb-4"
          >
            <ArrowLeftIcon class="w-5 h-5 mr-2" />
            Quay lại danh sách hóa đơn
          </Link>
          <h1 class="text-3xl font-bold text-gray-900">Chi tiết hóa đơn</h1>
        </div>

        <!-- Invoice Card -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
          <!-- Invoice Header -->
          <div class="bg-primary text-white p-6">
            <div class="flex items-center justify-between">
              <div>
                <h2 class="text-2xl font-bold">Hóa đơn {{ invoice.month_year }}</h2>
                <p class="text-primary-100 mt-1">Mã hóa đơn: #{{ invoice.id }}</p>
              </div>
              <div class="text-right">
                <span
                  :class="[
                    'px-4 py-2 rounded-full text-sm font-medium',
                    invoice.status === 'paid'
                      ? 'bg-green-500 text-white'
                      : 'bg-yellow-500 text-white'
                  ]"
                >
                  {{ invoice.status === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán' }}
                </span>
              </div>
            </div>
          </div>

          <!-- Invoice Body -->
          <div class="p-6 space-y-6">
            <!-- Customer & Property Info -->
            <div class="grid md:grid-cols-2 gap-6">
              <div>
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Thông tin khách hàng</h3>
                <div class="space-y-2 text-sm">
                  <p><span class="font-medium">Tên:</span> {{ invoice.user_name }}</p>
                  <p><span class="font-medium">Email:</span> {{ invoice.user_email }}</p>
                  <p v-if="invoice.user_phone"><span class="font-medium">Số điện thoại:</span> {{ invoice.user_phone }}</p>
                </div>
              </div>

              <div>
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Thông tin phòng</h3>
                <div class="space-y-2 text-sm">
                  <p><span class="font-medium">Nhà trọ:</span> {{ invoice.house_name }}</p>
                  <p><span class="font-medium">Phòng:</span> {{ invoice.room_number }}</p>
                  <p><span class="font-medium">Địa chỉ:</span> {{ invoice.house_address }}</p>
                </div>
              </div>
            </div>

            <!-- Invoice Period -->
            <div class="border-t pt-6">
              <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Kỳ hóa đơn</h3>
              <div class="grid md:grid-cols-2 gap-4 text-sm">
                <div>
                  <p class="text-gray-600">Từ ngày:</p>
                  <p class="font-medium text-gray-900">
                    {{ invoice.start_date ? formatDate(invoice.start_date) : 'N/A' }}
                  </p>
                </div>
                <div>
                  <p class="text-gray-600">Đến ngày:</p>
                  <p class="font-medium text-gray-900">
                    {{ invoice.end_date ? formatDate(invoice.end_date) : 'N/A' }}
                  </p>
                </div>
                <div>
                  <p class="text-gray-600">Ngày đến hạn:</p>
                  <p class="font-medium text-gray-900">
                    {{ invoice.due_date ? formatDate(invoice.due_date) : 'N/A' }}
                  </p>
                </div>
                <div>
                  <p class="text-gray-600">Ngày tạo:</p>
                  <p class="font-medium text-gray-900">
                    {{ formatDateTime(invoice.created_at) }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Invoice Items -->
            <div class="border-t pt-6">
              <h3 class="text-sm font-semibold text-gray-500 uppercase mb-4">Chi tiết hóa đơn</h3>
              <div class="space-y-3">
                <div class="flex justify-between items-center py-3 border-b">
                  <span class="text-gray-700">Tiền phòng</span>
                  <span class="font-medium text-gray-900">{{ formatPrice(invoice.room_rent) }}</span>
                </div>
                <div class="flex justify-between items-center py-3 border-b">
                  <span class="text-gray-700">Tiền điện</span>
                  <span class="font-medium text-gray-900">{{ formatPrice(invoice.electricity_amount) }}</span>
                </div>
                <div class="flex justify-between items-center py-3 border-b">
                  <span class="text-gray-700">Tiền nước</span>
                  <span class="font-medium text-gray-900">{{ formatPrice(invoice.water_amount) }}</span>
                </div>
                <div v-if="invoice.other_fees > 0" class="flex justify-between items-center py-3 border-b">
                  <span class="text-gray-700">Phí khác</span>
                  <span class="font-medium text-gray-900">{{ formatPrice(invoice.other_fees) }}</span>
                </div>
              </div>
            </div>

            <!-- Total -->
            <div class="border-t pt-6">
              <div class="flex justify-between items-center">
                <span class="text-xl font-semibold text-gray-900">Tổng cộng:</span>
                <span class="text-3xl font-bold text-primary">{{ formatPrice(invoice.total) }}</span>
              </div>
            </div>

            <!-- Payment Info -->
            <div v-if="invoice.status === 'paid' && invoice.paid_at" class="border-t pt-6">
              <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center gap-2 mb-2">
                  <CheckCircleIcon class="w-5 h-5 text-green-600" />
                  <h3 class="font-semibold text-green-900">Đã thanh toán</h3>
                </div>
                <p class="text-sm text-green-800">
                  Thanh toán vào: {{ formatDateTime(invoice.paid_at) }}
                </p>
              </div>
            </div>

            <!-- Payment Button -->
            <div v-else class="border-t pt-6">
              <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                <div class="flex items-center gap-2 mb-2">
                  <ClockIcon class="w-5 h-5 text-yellow-600" />
                  <h3 class="font-semibold text-yellow-900">Chưa thanh toán</h3>
                </div>
                <p class="text-sm text-yellow-800">
                  Ngày đến hạn: {{ invoice.due_date ? formatDate(invoice.due_date) : 'N/A' }}
                </p>
              </div>
              <button
                @click="goToPayment"
                class="w-full px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-semibold text-lg flex items-center justify-center"
              >
                <CreditCardIcon class="w-5 h-5 mr-2" />
                Thanh toán ngay
              </button>
            </div>

            <!-- Notes -->
            <div v-if="invoice.notes" class="border-t pt-6">
              <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Ghi chú</h3>
              <p class="text-sm text-gray-700 bg-gray-50 p-4 rounded-lg">{{ invoice.notes }}</p>
            </div>
          </div>

          <!-- Invoice Footer -->
          <div class="bg-gray-50 px-6 py-4 border-t">
            <div class="flex items-center justify-between">
              <p class="text-sm text-gray-500">
                Hóa đơn được tạo tự động bởi hệ thống TL Stay
              </p>
              <button
                @click="printInvoice"
                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
              >
                <PrinterIcon class="w-5 h-5 mr-2" />
                In hóa đơn
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import {
  ArrowLeftIcon,
  CheckCircleIcon,
  PrinterIcon,
  ClockIcon,
  CreditCardIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  invoice: {
    type: Object,
    required: true,
  },
})

const formatPrice = (price) => {
  if (!price && price !== 0) return '0 ₫'
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND',
  }).format(price)
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('vi-VN', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
}

const formatDateTime = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleString('vi-VN', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const printInvoice = () => {
  window.print()
}

const goToPayment = () => {
  router.visit(`/payment/invoice/${props.invoice.id}`, {
    preserveState: false,
    preserveScroll: false,
  })
}
</script>

<style scoped>
@media print {
  .no-print {
    display: none !important;
  }
}
</style>
