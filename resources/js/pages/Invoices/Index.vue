<template>
  <Head title="Hóa đơn" />
  <AppLayout>
    <div class="min-h-screen bg-gray-50 py-8">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
          <h1 class="text-3xl font-bold text-gray-900">Hóa đơn của tôi</h1>
          <p class="text-gray-600 mt-1">Xem và quản lý tất cả hóa đơn điện nước</p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
          <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600">Tổng hóa đơn</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ stats.total }}</p>
              </div>
              <div class="p-3 bg-blue-100 rounded-lg">
                <DocumentTextIcon class="w-6 h-6 text-blue-600" />
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600">Đã thanh toán</p>
                <p class="text-2xl font-bold text-green-600 mt-1">{{ stats.paid }}</p>
              </div>
              <div class="p-3 bg-green-100 rounded-lg">
                <CheckCircleIcon class="w-6 h-6 text-green-600" />
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600">Chưa thanh toán</p>
                <p class="text-2xl font-bold text-yellow-600 mt-1">{{ stats.pending }}</p>
              </div>
              <div class="p-3 bg-yellow-100 rounded-lg">
                <ClockIcon class="w-6 h-6 text-yellow-600" />
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600">Tổng tiền chưa thanh toán</p>
                <p class="text-xl font-bold text-red-600 mt-1">{{ formatPrice(stats.pending_amount) }}</p>
              </div>
              <div class="p-3 bg-red-100 rounded-lg">
                <ExclamationTriangleIcon class="w-6 h-6 text-red-600" />
              </div>
            </div>
          </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
          <div class="flex items-center gap-4">
            <label class="text-sm font-medium text-gray-700">Lọc theo trạng thái:</label>
            <select
              v-model="selectedStatus"
              @change="handleFilterChange"
              class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
            >
              <option value="all">Tất cả</option>
              <option value="paid">Đã thanh toán</option>
              <option value="pending">Chưa thanh toán</option>
            </select>
          </div>
        </div>

        <!-- Invoices List -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
          <div v-if="invoices.length === 0" class="text-center py-12">
            <DocumentTextIcon class="w-16 h-16 mx-auto text-gray-300 mb-4" />
            <h3 class="text-lg font-medium text-gray-900 mb-2">Chưa có hóa đơn nào</h3>
            <p class="text-gray-500">Bạn chưa có hóa đơn điện nước nào</p>
          </div>

          <div v-else class="divide-y divide-gray-200">
            <div
              v-for="invoice in invoices"
              :key="invoice.id"
              class="p-6 hover:bg-gray-50 transition-colors"
            >
              <div>
                <div class="flex items-center gap-3 mb-2">
                  <h3 class="text-lg font-semibold text-gray-900">
                    Hóa đơn {{ invoice.month_year }}
                  </h3>
                  <span
                    :class="[
                      'px-2 py-1 rounded-full text-xs font-medium',
                      invoice.status === 'paid'
                        ? 'bg-green-100 text-green-800'
                        : 'bg-yellow-100 text-yellow-800'
                    ]"
                  >
                    {{ invoice.status === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán' }}
                  </span>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4 text-sm">
                  <div>
                    <p class="text-gray-500">Nhà trọ</p>
                    <p class="font-medium text-gray-900">{{ invoice.house_name }}</p>
                  </div>
                  <div>
                    <p class="text-gray-500">Phòng</p>
                    <p class="font-medium text-gray-900">{{ invoice.room_number }}</p>
                  </div>
                  <div>
                    <p class="text-gray-500">Ngày đến hạn</p>
                    <p class="font-medium text-gray-900">
                      {{ invoice.due_date ? formatDate(invoice.due_date) : 'N/A' }}
                    </p>
                  </div>
                  <div>
                    <p class="text-gray-500">Ngày thanh toán</p>
                    <p class="font-medium text-gray-900">
                      {{ invoice.paid_at ? formatDateTime(invoice.paid_at) : 'Chưa thanh toán' }}
                    </p>
                  </div>
                </div>

                <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                  <div class="flex justify-between">
                    <span class="text-gray-600">Tiền phòng:</span>
                    <span class="font-medium">{{ formatPrice(invoice.room_rent) }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600">Tiền điện:</span>
                    <span class="font-medium">{{ formatPrice(invoice.electricity_amount) }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600">Tiền nước:</span>
                    <span class="font-medium">{{ formatPrice(invoice.water_amount) }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600">Phí khác:</span>
                    <span class="font-medium">{{ formatPrice(invoice.other_fees) }}</span>
                  </div>
                </div>

                <div class="mt-4 pt-4 border-t flex items-center justify-between">
                  <span class="text-lg font-semibold text-gray-900">Tổng cộng:</span>
                  <span class="text-2xl font-bold text-primary">{{ formatPrice(invoice.total) }}</span>
                </div>

                <!-- Action Buttons -->
                <div class="mt-4 pt-4 border-t flex flex-col md:flex-row gap-2">
                  <Link
                    :href="`/invoices/${invoice.id}`"
                    class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-600 transition-colors"
                  >
                    <EyeIcon class="w-5 h-5 mr-2" />
                    Xem chi tiết
                  </Link>
                  <button
                    v-if="invoice.status === 'pending'"
                    @click="goToPayment(invoice.id)"
                    class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
                  >
                    <CreditCardIcon class="w-5 h-5 mr-2" />
                    Thanh toán
                  </button>
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
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import {
  DocumentTextIcon,
  CheckCircleIcon,
  ClockIcon,
  ExclamationTriangleIcon,
  EyeIcon,
  CreditCardIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  invoices: {
    type: Array,
    default: () => [],
  },
  stats: {
    type: Object,
    default: () => ({
      total: 0,
      paid: 0,
      pending: 0,
      total_amount: 0,
      paid_amount: 0,
      pending_amount: 0,
    }),
  },
  filters: {
    type: Object,
    default: () => ({
      status: 'all',
    }),
  },
})

const selectedStatus = ref(props.filters.status)

const handleFilterChange = () => {
  router.get('/invoices', {
    status: selectedStatus.value === 'all' ? null : selectedStatus.value,
  }, {
    preserveState: true,
    preserveScroll: true,
  })
}

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
    month: '2-digit',
    day: '2-digit',
  })
}

const formatDateTime = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleString('vi-VN', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const goToPayment = (invoiceId) => {
  router.visit(`/payment/invoice/${invoiceId}`, {
    preserveState: false,
    preserveScroll: false,
  })
}
</script>
