<template>
  <Head title="Quản lý Đặt phòng" />
  <AdminLayout title="Quản lý Đặt phòng">
    <div class="space-y-6">
      <!-- Header -->
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Quản lý Đặt phòng</h1>
        <p class="text-gray-600 mt-1">Xem và quản lý tất cả đơn đặt phòng</p>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow-sm p-4">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
          <!-- Search -->
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Tìm kiếm
            </label>
            <input
              v-model="search"
              type="text"
              placeholder="Mã đặt phòng, tên khách hàng, nhà trọ..."
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
              @input="handleSearch"
            />
          </div>

          <!-- House Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Nhà trọ
            </label>
            <SelectSearchable
              v-model="filters.house_id"
              :options="houses"
              option-value="id"
              option-label="name"
              placeholder="Tất cả nhà trọ..."
              @update:modelValue="applyFilters"
            />
          </div>

          <!-- Booking Status Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Trạng thái đặt phòng
            </label>
            <select
              v-model="filters.booking_status"
              @change="applyFilters"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
            >
              <option value="">Tất cả</option>
              <option value="upcoming">Sắp tới</option>
              <option value="active">Đang ở</option>
              <option value="past">Đã kết thúc</option>
            </select>
          </div>

          <!-- Payment Status Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Trạng thái thanh toán
            </label>
            <select
              v-model="filters.payment_status"
              @change="applyFilters"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
            >
              <option value="">Tất cả</option>
              <option value="pending">Chưa thanh toán</option>
              <option value="paid">Đã thanh toán</option>
            </select>
          </div>
        </div>

        <!-- Contract Signed Filter -->
        <div class="mt-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Trạng thái hợp đồng
          </label>
          <select
            v-model="filters.contract_signed"
            @change="applyFilters"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
          >
            <option value="">Tất cả</option>
            <option value="1">Đã ký</option>
            <option value="0">Chưa ký</option>
          </select>
        </div>
      </div>

      <!-- Bookings Table -->
      <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Mã đặt phòng
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Khách hàng
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Nhà trọ / Phòng
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Thời gian
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Tổng tiền
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Trạng thái
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Thanh toán
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Hợp đồng
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Thao tác
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="booking in bookings.data" :key="booking.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">
                    {{ booking.booking_code || `#${booking.id}` }}
                  </div>
                  <div class="text-xs text-gray-500">
                    {{ formatDate(booking.created_at) }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ booking.user.name }}</div>
                  <div class="text-sm text-gray-500">{{ booking.user.email }}</div>
                  <div v-if="booking.user.phone" class="text-xs text-gray-400">{{ booking.user.phone }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm font-medium text-gray-900">{{ booking.house.name }}</div>
                  <div class="text-sm text-gray-500">
                    Phòng {{ booking.room.room_number }} - Tầng {{ booking.room.floor }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">
                    {{ formatDate(booking.start_date) }}
                  </div>
                  <div class="text-sm text-gray-500">
                    đến {{ formatDate(booking.end_date) }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">
                    {{ formatCurrency(booking.total_price) }}
                  </div>
                  <div v-if="booking.discount_amount > 0" class="text-xs text-green-600">
                    Giảm: {{ formatCurrency(booking.discount_amount) }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="[
                      'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                      getBookingStatusBadgeClass(booking.booking_status)
                    ]"
                  >
                    {{ getBookingStatusText(booking.booking_status) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="[
                      'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                      booking.payment_status === 'paid'
                        ? 'bg-green-100 text-green-800'
                        : 'bg-yellow-100 text-yellow-800'
                    ]"
                  >
                    {{ booking.payment_status === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán' }}
                  </span>
                  <div v-if="booking.paid_at" class="text-xs text-gray-500 mt-1">
                    {{ formatDate(booking.paid_at) }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="[
                      'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                      booking.contract_signed
                        ? 'bg-blue-100 text-blue-800'
                        : 'bg-gray-100 text-gray-800'
                    ]"
                  >
                    {{ booking.contract_signed ? 'Đã ký' : 'Chưa ký' }}
                  </span>
                  <div v-if="booking.signed_at" class="text-xs text-gray-500 mt-1">
                    {{ formatDate(booking.signed_at) }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center justify-start gap-2">
                    <!-- View Button -->
                    <div class="relative group">
                      <Link
                        :href="`/admin/bookings/${booking.id}`"
                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-gray-600 hover:bg-gray-100 hover:text-primary transition-colors"
                        title="Xem chi tiết"
                      >
                        <EyeIcon class="h-5 w-5" />
                      </Link>
                      <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs font-medium text-white bg-gray-900 rounded shadow-lg whitespace-nowrap opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 pointer-events-none z-50">
                        Xem chi tiết
                        <div class="absolute top-full left-1/2 -translate-x-1/2 -mt-1 border-4 border-transparent border-t-gray-900"></div>
                      </div>
                    </div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="bookings.links && bookings.links.length > 3" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
          <div class="flex items-center justify-between">
            <div class="flex-1 flex justify-between sm:hidden">
              <Link
                v-if="bookings.links[0].url"
                :href="bookings.links[0].url"
                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
              >
                Trước
              </Link>
              <Link
                v-if="bookings.links[bookings.links.length - 1].url"
                :href="bookings.links[bookings.links.length - 1].url"
                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
              >
                Sau
              </Link>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
              <div>
                <p class="text-sm text-gray-700">
                  Hiển thị
                  <span class="font-medium">{{ bookings.from }}</span>
                  đến
                  <span class="font-medium">{{ bookings.to }}</span>
                  trong tổng số
                  <span class="font-medium">{{ bookings.total }}</span>
                  kết quả
                </p>
              </div>
              <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                  <template v-for="(link, index) in bookings.links" :key="index">
                    <Link
                      v-if="link.url"
                      :href="link.url"
                      v-html="getPaginationLabel(link.label)"
                      :class="[
                        'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                        link.active
                          ? 'z-10 bg-primary border-primary text-white'
                          : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                        index === 0 ? 'rounded-l-md' : '',
                        index === bookings.links.length - 1 ? 'rounded-r-md' : ''
                      ]"
                    />
                    <span
                      v-else
                      v-html="getPaginationLabel(link.label)"
                      class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-300"
                      :class="[
                        index === 0 ? 'rounded-l-md' : '',
                        index === bookings.links.length - 1 ? 'rounded-r-md' : ''
                      ]"
                    />
                  </template>
                </nav>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="bookings.data.length === 0" class="text-center py-12">
          <CalendarIcon class="mx-auto h-12 w-12 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900">Không có đặt phòng</h3>
          <p class="mt-1 text-sm text-gray-500">Không tìm thấy đặt phòng nào phù hợp với bộ lọc.</p>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import SelectSearchable from '@/components/ui/SelectSearchable.vue'
import { CalendarIcon, EyeIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  bookings: {
    type: Object,
    required: true,
  },
  houses: {
    type: Array,
    default: () => [],
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
})

const search = ref(props.filters.search || '')
const filters = ref({
  house_id: props.filters.house_id || '',
  booking_status: props.filters.booking_status || '',
  payment_status: props.filters.payment_status || '',
  contract_signed: props.filters.contract_signed || '',
})

let searchTimeout = null

// Debounced search function
const handleSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    applyFilters()
  }, 300)
}

const applyFilters = () => {
  router.get(
    '/admin/bookings',
    {
      search: search.value || undefined,
      house_id: filters.value.house_id || undefined,
      booking_status: filters.value.booking_status || undefined,
      payment_status: filters.value.payment_status || undefined,
      contract_signed: filters.value.contract_signed || undefined,
    },
    {
      preserveState: true,
      preserveScroll: true,
    }
  )
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('vi-VN', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
  })
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND',
  }).format(amount)
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
    upcoming: 'bg-amber-100 text-amber-800 border border-amber-300',
    active: 'bg-green-100 text-green-800',
    past: 'bg-gray-100 text-gray-800',
  }
  return classMap[status] || 'bg-gray-100 text-gray-800'
}

const getPaginationLabel = (label) => {
  if (!label) return label
  
  let translated = label
    .replace(/&laquo;\s*Previous/gi, 'Trước')
    .replace(/Previous/gi, 'Trước')
    .replace(/&laquo;/g, 'Trước')
    .replace(/Next\s*&raquo;/gi, 'Sau')
    .replace(/Next/gi, 'Sau')
    .replace(/&raquo;/g, 'Sau')
  
  return translated
}
</script>
