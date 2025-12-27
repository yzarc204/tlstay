<template>
  <Head :title="`Chi tiết đặt phòng #${booking?.booking_code || ''}`" />
  <AdminLayout title="Chi tiết đặt phòng">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <Link
            href="/admin/bookings"
            class="text-primary hover:text-primary-600 mb-2 inline-flex items-center text-sm"
          >
            <ChevronLeftIcon class="h-4 w-4 mr-1" />
            Quay lại danh sách
          </Link>
          <h1 class="text-2xl font-bold text-gray-900">
            Chi tiết đặt phòng
            <span class="text-primary">{{ booking?.booking_code || `#${booking?.id}` }}</span>
          </h1>
        </div>
      </div>

      <div v-if="!booking" class="text-center py-20">
        <div class="inline-block animate-spin rounded-full h-16 w-16 border-4 border-primary border-t-transparent"></div>
        <p class="mt-4 text-gray-600">Đang tải thông tin...</p>
      </div>

      <div v-else class="grid lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Booking Info Card -->
          <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Thông tin đặt phòng</h2>

            <div class="grid md:grid-cols-2 gap-6">
              <!-- House Info -->
              <div class="space-y-4">
                <div>
                  <label class="text-sm font-medium text-gray-500 block mb-1">Nhà trọ</label>
                  <p class="text-lg font-semibold text-gray-900">{{ booking.house.name }}</p>
                  <p class="text-sm text-gray-600 mt-1">{{ booking.house.address }}</p>
                  <Link
                    :href="`/admin/houses/${booking.house.id}`"
                    class="text-primary hover:text-primary-dark text-sm mt-2 inline-block"
                  >
                    Xem chi tiết nhà trọ →
                  </Link>
                </div>

                <div>
                  <label class="text-sm font-medium text-gray-500 block mb-1">Phòng</label>
                  <p class="text-lg font-semibold text-gray-900">
                    Phòng {{ booking.room.room_number }} - Tầng {{ booking.room.floor }}
                  </p>
                  <p v-if="booking.room.area" class="text-sm text-gray-600 mt-1">
                    Diện tích: {{ booking.room.area }} m²
                  </p>
                  <p class="text-sm text-gray-600 mt-1">
                    Giá: {{ formatCurrency(booking.room.price_per_day) }}/ngày
                  </p>
                </div>
              </div>

              <!-- Dates -->
              <div class="space-y-4">
                <div>
                  <label class="text-sm font-medium text-gray-500 block mb-1">Ngày nhận phòng</label>
                  <p class="text-lg font-semibold text-gray-900">{{ formatDate(booking.start_date) }}</p>
                </div>

                <div>
                  <label class="text-sm font-medium text-gray-500 block mb-1">Ngày trả phòng</label>
                  <p class="text-lg font-semibold text-gray-900">{{ formatDate(booking.end_date) }}</p>
                </div>

                <div>
                  <label class="text-sm font-medium text-gray-500 block mb-1">Số ngày thuê</label>
                  <p class="text-lg font-semibold text-primary">{{ calculateDays(booking.start_date, booking.end_date) }} ngày</p>
                </div>

                <div>
                  <label class="text-sm font-medium text-gray-500 block mb-1">Trạng thái</label>
                  <span
                    :class="[
                      'inline-flex px-3 py-1 text-sm font-semibold rounded-full',
                      getBookingStatusBadgeClass(booking.booking_status)
                    ]"
                  >
                    {{ getBookingStatusText(booking.booking_status) }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Notes -->
            <div v-if="booking.notes" class="mt-6 pt-6 border-t">
              <label class="text-sm font-medium text-gray-500 block mb-2">Ghi chú</label>
              <p class="text-gray-700 bg-gray-50 px-4 py-3 rounded-lg whitespace-pre-wrap">{{ booking.notes }}</p>
            </div>
          </div>

          <!-- Payment Info Card -->
          <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Thông tin thanh toán</h2>

            <div class="space-y-4">
              <div class="flex justify-between items-center py-3 border-b">
                <span class="text-gray-700 font-medium">Tổng tiền phòng</span>
                <span class="font-semibold text-gray-900 text-lg">
                  {{ formatCurrency(booking.total_price + booking.discount_amount) }}
                </span>
              </div>
              <div
                v-if="booking.discount_amount > 0"
                class="flex justify-between items-center py-3 border-b"
              >
                <span class="text-gray-700 font-medium">Giảm giá</span>
                <span class="font-semibold text-green-600 text-lg">
                  -{{ formatCurrency(booking.discount_amount) }}
                </span>
              </div>
              <div class="flex justify-between items-center pt-4 mt-2 border-t-2 border-gray-400">
                <span class="text-xl font-bold text-gray-900">Tổng thanh toán</span>
                <span class="text-3xl font-bold text-primary">
                  {{ formatCurrency(booking.total_price) }}
                </span>
              </div>
            </div>

            <div class="grid md:grid-cols-2 gap-6 mt-6 pt-6 border-t">
              <div>
                <label class="text-sm font-medium text-gray-500 block mb-2">Trạng thái thanh toán</label>
                <span
                  :class="[
                    'inline-flex px-3 py-1 text-sm font-semibold rounded-full',
                    booking.payment_status === 'paid'
                      ? 'bg-green-100 text-green-800'
                      : 'bg-yellow-100 text-yellow-800'
                  ]"
                >
                  {{ booking.payment_status === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán' }}
                </span>
              </div>
              <div v-if="booking.payment_method">
                <label class="text-sm font-medium text-gray-500 block mb-2">Phương thức thanh toán</label>
                <p class="font-semibold text-gray-900">{{ getPaymentMethodText(booking.payment_method) }}</p>
              </div>
              <div v-if="booking.paid_at">
                <label class="text-sm font-medium text-gray-500 block mb-2">Thời gian thanh toán</label>
                <p class="font-semibold text-gray-900">{{ formatDateTime(booking.paid_at) }}</p>
              </div>
              <div v-if="booking.vnpay_transaction_id">
                <label class="text-sm font-medium text-gray-500 block mb-2">Mã giao dịch</label>
                <p class="font-mono text-sm text-gray-900 break-all">{{ booking.vnpay_transaction_id }}</p>
              </div>
            </div>
          </div>

          <!-- Contract Card -->
          <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Hợp đồng</h2>

            <div class="space-y-4">
              <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                <div>
                  <p class="font-semibold text-gray-900">Trạng thái hợp đồng</p>
                  <p class="text-sm text-gray-600 mt-1">
                    <span v-if="booking.contract_signed" class="text-green-600">Đã ký hợp đồng</span>
                    <span v-else class="text-yellow-600">Chưa ký hợp đồng</span>
                  </p>
                  <p v-if="booking.signed_at" class="text-xs text-gray-500 mt-1">
                    Đã ký vào: {{ formatDateTime(booking.signed_at) }}
                  </p>
                </div>
                <div class="flex space-x-2">
                  <a
                    :href="`/contract/${booking.id}/preview`"
                    target="_blank"
                    class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors"
                  >
                    Xem hợp đồng
                  </a>
                  <a
                    :href="`/contract/${booking.id}`"
                    target="_blank"
                    class="px-4 py-2 border-2 border-primary text-primary rounded-lg hover:bg-primary hover:text-white transition-colors"
                  >
                    Tải PDF
                  </a>
                </div>
              </div>
            </div>
          </div>

          <!-- Invoices Card -->
          <div v-if="booking.invoices && booking.invoices.length > 0" class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Hóa đơn dịch vụ</h2>

            <div class="space-y-4">
              <div
                v-for="invoice in booking.invoices"
                :key="invoice.id"
                class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
              >
                <div class="flex items-start justify-between mb-3">
                  <div>
                    <p class="font-semibold text-gray-900">
                      {{ invoice.month_year || `Hóa đơn ${invoice.month}/${invoice.year}` }}
                    </p>
                    <p v-if="invoice.invoice_code" class="text-sm text-gray-500 mt-1">
                      Mã: {{ invoice.invoice_code }}
                    </p>
                  </div>
                  <span
                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                    :class="invoice.status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'"
                  >
                    {{ invoice.status === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán' }}
                  </span>
                </div>

                <div class="grid md:grid-cols-2 gap-4 mb-4">
                  <div>
                    <p class="text-sm text-gray-600">Điện</p>
                    <p class="font-semibold text-gray-900">{{ formatCurrency(invoice.electricity_amount) }}</p>
                  </div>
                  <div>
                    <p class="text-sm text-gray-600">Nước</p>
                    <p class="font-semibold text-gray-900">{{ formatCurrency(invoice.water_amount) }}</p>
                  </div>
                  <div>
                    <p class="text-sm text-gray-600">Phí khác</p>
                    <p class="font-semibold text-gray-900">{{ formatCurrency(invoice.other_fees || 0) }}</p>
                  </div>
                  <div>
                    <p class="text-sm text-gray-600">Tổng tiền</p>
                    <p class="font-semibold text-primary text-lg">{{ formatCurrency(invoice.amount) }}</p>
                  </div>
                </div>

                <div v-if="invoice.due_date" class="text-xs text-gray-500">
                  Hạn thanh toán: {{ formatDate(invoice.due_date) }}
                </div>
                <div v-if="invoice.paid_at" class="text-xs text-green-600 mt-1">
                  Đã thanh toán: {{ formatDateTime(invoice.paid_at) }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Customer Info Card -->
          <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Thông tin khách hàng</h3>
            <div class="space-y-4">
              <div>
                <label class="text-sm font-medium text-gray-500 block mb-1">Họ tên</label>
                <p class="font-semibold text-gray-900">{{ booking.user.name }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-500 block mb-1">Email</label>
                <p class="text-gray-900">{{ booking.user.email }}</p>
              </div>
              <div v-if="booking.user.phone">
                <label class="text-sm font-medium text-gray-500 block mb-1">Số điện thoại</label>
                <p class="text-gray-900">{{ booking.user.phone }}</p>
              </div>
              <div v-if="booking.user.id_card_number">
                <label class="text-sm font-medium text-gray-500 block mb-1">CMND/CCCD</label>
                <p class="text-gray-900">{{ booking.user.id_card_number }}</p>
              </div>
              <div v-if="booking.user.permanent_address">
                <label class="text-sm font-medium text-gray-500 block mb-1">Địa chỉ thường trú</label>
                <p class="text-gray-900">{{ booking.user.permanent_address }}</p>
              </div>
              <div v-if="booking.user.date_of_birth">
                <label class="text-sm font-medium text-gray-500 block mb-1">Ngày sinh</label>
                <p class="text-gray-900">{{ formatDate(booking.user.date_of_birth) }}</p>
              </div>
              <div v-if="booking.user.gender">
                <label class="text-sm font-medium text-gray-500 block mb-1">Giới tính</label>
                <p class="text-gray-900">{{ booking.user.gender === 'male' ? 'Nam' : booking.user.gender === 'female' ? 'Nữ' : 'Khác' }}</p>
              </div>
              <div class="pt-4 border-t">
                <Link
                  :href="`/admin/users/${booking.user.id}`"
                  class="text-primary hover:text-primary-dark text-sm font-medium"
                >
                  Xem chi tiết khách hàng →
                </Link>
              </div>
            </div>
          </div>

          <!-- Booking Meta Card -->
          <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Thông tin khác</h3>
            <div class="space-y-3 text-sm">
              <div>
                <label class="text-gray-500">Ngày tạo đơn</label>
                <p class="font-medium text-gray-900">{{ formatDateTime(booking.created_at) }}</p>
              </div>
              <div>
                <label class="text-gray-500">Mã đặt phòng</label>
                <p class="font-mono text-gray-900">{{ booking.booking_code || `#${booking.id}` }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { ChevronLeftIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  booking: {
    type: Object,
    required: true,
  },
})

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('vi-VN', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
  })
}

const formatDateTime = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleString('vi-VN', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND',
  }).format(amount)
}

const calculateDays = (startDate, endDate) => {
  const start = new Date(startDate)
  const end = new Date(endDate)
  const diffTime = Math.abs(end - start)
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  return diffDays
}

const getBookingStatusText = (status) => {
  const statusMap = {
    upcoming: 'Sắp tới',
    active: 'Đang ở',
    past: 'Đã kết thúc',
  }
  return statusMap[status] || status
}

const getBookingStatusBadgeClass = (status) => {
  const classMap = {
    upcoming: 'bg-blue-100 text-blue-800',
    active: 'bg-green-100 text-green-800',
    past: 'bg-gray-100 text-gray-800',
  }
  return classMap[status] || 'bg-gray-100 text-gray-800'
}

const getPaymentMethodText = (method) => {
  const methodMap = {
    vnpay: 'VNPay',
    cash: 'Tiền mặt',
    bank_transfer: 'Chuyển khoản',
  }
  return methodMap[method] || method
}
</script>
