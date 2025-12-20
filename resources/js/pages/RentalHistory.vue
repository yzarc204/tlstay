<template>
  <AppLayout>
    <div class="rental-history bg-light min-h-screen py-12">
      <div class="container mx-auto px-4">
        <!-- Flash Messages -->
        <div v-if="$page.props.flash?.success" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
          <p class="text-green-800">{{ $page.props.flash.success }}</p>
        </div>
        <div v-if="$page.props.flash?.error" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
          <p class="text-red-800">{{ $page.props.flash.error }}</p>
        </div>
        <div v-if="$page.props.flash?.info" class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
          <p class="text-blue-800">{{ $page.props.flash.info }}</p>
        </div>

        <!-- Header -->
        <div class="mb-8">
          <h1 class="text-4xl font-bold text-secondary mb-2">Lịch sử thuê phòng</h1>
          <p class="text-gray-600">Quản lý các phòng bạn đã thuê</p>
        </div>

        <!-- Content -->
        <div class="grid lg:grid-cols-3 gap-8">
          <!-- Bookings List -->
          <div class="lg:col-span-2 space-y-6">
            <div v-if="bookings.length === 0" class="card p-12 text-center">
              <ClipboardDocumentListIcon class="w-24 h-24 mx-auto text-gray-300 mb-4" />
              <h3 class="text-2xl font-bold text-gray-400 mb-2">Chưa có lịch sử thuê</h3>
              <p class="text-gray-500 mb-6">Bạn chưa thuê phòng nào</p>
              <Link href="/houses" class="btn-primary inline-block">
                Tìm phòng ngay
              </Link>
            </div>

            <div v-for="booking in bookings" :key="booking.id" class="card p-6">
              <div class="flex items-start justify-between mb-4">
                <div class="flex-1">
                  <div class="flex items-center gap-3 mb-1">
                    <h3 class="text-xl font-bold text-secondary">{{ booking.room_name }}</h3>
                    <span v-if="booking.booking_code" class="px-2 py-1 bg-primary-100 text-primary-700 rounded text-xs font-mono font-semibold">
                      {{ booking.booking_code }}
                    </span>
                  </div>
                  <p class="text-gray-600">{{ booking.house_name }}</p>
                </div>
                <span
                  class="badge"
                  :class="getStatusBadgeClass(booking.status)"
                >
                  {{ getStatusText(booking.status) }}
                </span>
              </div>

              <div class="grid md:grid-cols-2 gap-4 mb-4">
                <div class="flex items-center space-x-2 text-gray-600">
                  <CalendarIcon class="w-5 h-5" />
                  <div>
                    <p class="text-xs text-gray-500">Ngày bắt đầu</p>
                    <p class="font-medium">{{ formatDate(booking.start_date) }}</p>
                  </div>
                </div>
                <div class="flex items-center space-x-2 text-gray-600">
                  <CalendarIcon class="w-5 h-5" />
                  <div>
                    <p class="text-xs text-gray-500">Ngày kết thúc</p>
                    <p class="font-medium">{{ formatDate(booking.end_date) }}</p>
                  </div>
                </div>
              </div>

              <div class="mb-4">
                <span
                  class="inline-block px-3 py-1 text-xs rounded-full"
                  :class="getPaymentStatusBadgeClass(booking.payment_status)"
                >
                  {{ getPaymentStatusText(booking.payment_status) }}
                </span>
              </div>

              <div class="flex items-center justify-between pt-4 border-t">
                <div>
                  <p class="text-sm text-gray-600">Tổng tiền</p>
                  <p class="text-2xl font-bold text-primary">{{ formatPrice(booking.total_price) }}</p>
                </div>
                <div class="flex space-x-2">
                  <Link
                    :href="`/booking/${booking.id}`"
                    class="px-4 py-2 bg-secondary text-white rounded-lg hover:bg-secondary-700 transition-all"
                  >
                    Chi tiết
                  </Link>
                  <button
                    v-if="booking.payment_status === 'pending'"
                    @click="goToPayment(booking.id)"
                    class="px-4 py-2 border-2 border-primary text-primary rounded-lg hover:bg-primary hover:text-white transition-all"
                  >
                    Thanh toán
                  </button>
                  <a
                    v-if="booking.payment_status === 'paid'"
                    :href="`/contract/${booking.id}`"
                    target="_blank"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-all"
                  >
                    Xem hợp đồng
                  </a>
                </div>
              </div>
            </div>
          </div>

          <!-- Invoices Sidebar -->
          <div class="lg:col-span-1">
            <div class="card p-6 sticky top-24 space-y-6">
              <div>
                <h2 class="text-xl font-bold text-secondary mb-6">Hóa đơn tháng này</h2>

              <div v-if="currentMonthInvoices.length === 0" class="text-center py-8">
                <DocumentTextIcon class="w-16 h-16 mx-auto text-gray-300 mb-2" />
                <p class="text-gray-500 text-sm">Chưa có hóa đơn</p>
              </div>

              <div v-else class="space-y-4">
                <div
                  v-for="invoice in currentMonthInvoices"
                  :key="invoice.id"
                  class="p-4 bg-light rounded-lg"
                >
                  <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-medium text-gray-700">Hóa đơn {{ invoice.month_year }}</span>
                    <span
                      class="badge text-xs"
                      :class="
                        invoice.status === 'paid'
                          ? 'bg-primary-100 text-primary-700'
                          : 'bg-secondary-100 text-secondary-700'
                      "
                    >
                      {{ invoice.status === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán' }}
                    </span>
                  </div>

                  <div class="space-y-2 text-sm mb-3">
                    <div class="flex justify-between">
                      <span class="text-gray-600">Tiền phòng</span>
                      <span class="font-medium">{{ formatPrice(invoice.room_rent) }}</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-gray-600">Tiền điện</span>
                      <span class="font-medium">{{ formatPrice(invoice.electricity) }}</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-gray-600">Tiền nước</span>
                      <span class="font-medium">{{ formatPrice(invoice.water) }}</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-gray-600">Internet</span>
                      <span class="font-medium">{{ formatPrice(invoice.internet) }}</span>
                    </div>
                  </div>

                  <div class="pt-3 border-t flex items-center justify-between">
                    <span class="font-semibold text-gray-700">Tổng cộng</span>
                    <span class="text-lg font-bold text-primary">{{
                      formatPrice(invoice.total)
                    }}</span>
                  </div>

                </div>
              </div>

              <!-- Summary -->
              <div class="mt-6 pt-6 border-t">
                <h3 class="font-semibold text-gray-800 mb-3">Thống kê</h3>
                <div class="space-y-3">
                  <div class="flex items-center justify-between p-3 bg-primary-50 rounded-lg">
                    <span class="text-sm text-gray-700">Tổng phòng đang thuê</span>
                    <span class="font-bold text-primary">{{
                      activeBookings.length
                    }}</span>
                  </div>
                  <div class="flex items-center justify-between p-3 bg-primary-50 rounded-lg">
                    <span class="text-sm text-gray-700">Hóa đơn đã thanh toán</span>
                    <span class="font-bold text-primary">
                      {{ paidInvoicesCount }}
                    </span>
                  </div>
                  <div class="flex items-center justify-between p-3 bg-secondary-50 rounded-lg">
                    <span class="text-sm text-gray-700">Hóa đơn chưa thanh toán</span>
                    <span class="font-bold text-secondary">
                      {{ unpaidInvoicesCount }}
                    </span>
                  </div>
                </div>
              </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import {
  ClipboardDocumentListIcon,
  CalendarIcon,
  DocumentTextIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  bookings: {
    type: Array,
    default: () => [],
  },
  invoices: {
    type: Array,
    default: () => [],
  },
})

// Computed properties
const activeBookings = computed(() => {
  return props.bookings.filter((b) => b.status === 'active')
})

const currentMonthInvoices = computed(() => {
  const currentMonth = new Date().getMonth() + 1
  const currentYear = new Date().getFullYear()
  return props.invoices.filter(
    (inv) => inv.month === currentMonth && inv.year === currentYear
  )
})

const paidInvoicesCount = computed(() => {
  return props.invoices.filter((i) => i.status === 'paid').length
})

const unpaidInvoicesCount = computed(() => {
  return props.invoices.filter((i) => i.status !== 'paid').length
})

// Helper functions
const formatPrice = (price) => {
  if (!price && price !== 0) return '0 ₫'
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND',
  }).format(price)
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleDateString('vi-VN', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
}

const getStatusText = (status) => {
  const statusMap = {
    pending: 'Chờ thanh toán',
    active: 'Đang thuê',
    upcoming: 'Sắp tới',
    completed: 'Đã kết thúc',
  }
  return statusMap[status] || status
}

const getStatusBadgeClass = (status) => {
  const classMap = {
    pending: 'bg-yellow-100 text-yellow-700',
    active: 'bg-primary-100 text-primary-700',
    upcoming: 'bg-blue-100 text-blue-700',
    completed: 'bg-gray-100 text-gray-600',
  }
  return classMap[status] || 'bg-gray-100 text-gray-600'
}

const getPaymentStatusText = (status) => {
  const statusMap = {
    pending: 'Chờ thanh toán',
    paid: 'Đã thanh toán',
    failed: 'Thanh toán thất bại',
    cancelled: 'Đã hủy',
  }
  return statusMap[status] || status
}

const getPaymentStatusBadgeClass = (status) => {
  const classMap = {
    pending: 'bg-yellow-100 text-yellow-700',
    paid: 'bg-green-100 text-green-700',
    failed: 'bg-red-100 text-red-700',
    cancelled: 'bg-gray-100 text-gray-600',
  }
  return classMap[status] || 'bg-gray-100 text-gray-600'
}

// Navigate to payment page
const goToPayment = (bookingId) => {
  router.visit(`/payment/${bookingId}`, {
    preserveState: false,
    preserveScroll: false,
  })
}

// Auto-open contract PDF after successful payment
onMounted(() => {
  const page = usePage()
  const flash = page.props.flash
  const urlParams = new URLSearchParams(window.location.search)
  
  // Check for contract_booking_id in flash message or URL parameter
  const contractBookingId = flash?.contract_booking_id || urlParams.get('contract')
  
  if (contractBookingId) {
    // Open contract PDF in new tab
    const contractUrl = `/contract/${contractBookingId}`
    window.open(contractUrl, '_blank')
    
    // Clean up URL parameter
    if (urlParams.has('contract')) {
      urlParams.delete('contract')
      const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '')
      window.history.replaceState({}, '', newUrl)
    }
  }
})
</script>
