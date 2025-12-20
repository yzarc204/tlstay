<template>
  <AdminLayout title="Quản lý Phòng đang có người thuê">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Quản lý Phòng đang có người thuê</h1>
          <p class="text-gray-600 mt-1">Quản lý các phòng trọ đang có người thuê và tạo hóa đơn điện nước</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow-sm p-4">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-semibold text-gray-900">Bộ lọc</h2>
          <button
            v-if="hasActiveFilters"
            @click="clearFilters"
            class="text-sm text-primary hover:text-primary-600 font-medium"
          >
            Xóa bộ lọc
          </button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Search -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Tìm kiếm
            </label>
            <input
              v-model="search"
              type="text"
              placeholder="Tìm kiếm theo số phòng, tên nhà trọ, khách hàng..."
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
              placeholder="Chọn nhà trọ..."
              @update:modelValue="handleHouseChange"
            />
          </div>
        </div>
      </div>

      <!-- Rooms Table -->
      <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Phòng
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Nhà trọ
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Khách hàng
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Thời gian thuê
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Loại thuê
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Hóa đơn
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Thao tác
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="room in rooms.data" :key="room.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10 rounded-lg bg-primary-100 flex items-center justify-center">
                      <span class="text-primary font-semibold">{{ room.room_number }}</span>
                    </div>
                    <div class="ml-3">
                      <div class="text-sm font-medium text-gray-900">
                        Phòng {{ room.room_number }}
                      </div>
                      <div class="text-xs text-gray-500">Tầng {{ room.floor }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-900">{{ room.house.name }}</div>
                  <div class="text-xs text-gray-500">{{ room.house.address }}</div>
                </td>
                <td class="px-6 py-4">
                  <div v-if="room.active_booking" class="text-sm">
                    <div class="font-medium text-gray-900">{{ room.active_booking.user.name }}</div>
                    <div class="text-xs text-gray-500">{{ room.active_booking.user.email }}</div>
                    <div class="text-xs text-gray-500">{{ room.active_booking.user.phone }}</div>
                  </div>
                  <div v-else-if="room.tenant" class="text-sm">
                    <div class="font-medium text-gray-900">{{ room.tenant.name }}</div>
                  </div>
                  <span v-else class="text-sm text-gray-400">Không có thông tin</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div v-if="room.active_booking" class="text-sm">
                    <div class="text-gray-900">
                      {{ formatDate(room.active_booking.start_date) }}
                    </div>
                    <div class="text-gray-500">đến</div>
                    <div class="text-gray-900">
                      {{ formatDate(room.active_booking.end_date) }}
                    </div>
                  </div>
                  <div v-else-if="room.tenant" class="text-sm">
                    <div class="text-gray-900">
                      {{ formatDate(room.tenant.rental_start_date) }}
                    </div>
                    <div class="text-gray-500">đến</div>
                    <div class="text-gray-900">
                      {{ formatDate(room.tenant.rental_end_date) }}
                    </div>
                  </div>
                  <span v-else class="text-sm text-gray-400">-</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    v-if="room.is_long_term"
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"
                  >
                    Thuê dài ngày ({{ room.rental_days }} ngày)
                  </span>
                  <span
                    v-else
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                  >
                    Thuê ngắn ngày ({{ room.rental_days }} ngày)
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm">
                    <div class="text-gray-900">
                      {{ room.invoices.length }} hóa đơn
                    </div>
                    <div v-if="room.invoices.length > 0" class="text-xs text-gray-500 mt-1">
                      <div v-for="invoice in room.invoices.slice(0, 2)" :key="invoice.id">
                        <span v-if="invoice.start_date && invoice.end_date">
                          {{ formatDate(invoice.start_date) }} - {{ formatDate(invoice.end_date) }}:
                        </span>
                        <span v-else-if="invoice.month && invoice.year">
                          {{ invoice.month }}/{{ invoice.year }}:
                        </span>
                        <span :class="invoice.status === 'paid' ? 'text-green-600' : 'text-orange-600'">
                          {{ formatCurrency(invoice.amount) }}
                        </span>
                      </div>
                      <div v-if="room.invoices.length > 2" class="text-gray-400">
                        +{{ room.invoices.length - 2 }} hóa đơn khác
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex items-center gap-2">
                    <button
                      v-if="room.is_long_term && (room.active_booking || room.has_tenant)"
                      @click="openCreateInvoiceModal(room)"
                      class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium text-white bg-primary hover:bg-primary-600 transition-colors"
                    >
                      <PlusIcon class="h-4 w-4 mr-1" />
                      Tạo hóa đơn
                    </button>
                    <button
                      v-if="room.invoices.length > 0"
                      @click="viewInvoices(room)"
                      class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition-colors"
                    >
                      Xem hóa đơn
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Empty State -->
        <div v-if="!rooms.data || rooms.data.length === 0" class="text-center py-12">
          <HomeIcon class="mx-auto h-12 w-12 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900">Chưa có phòng nào đang được thuê</h3>
          <p class="mt-1 text-sm text-gray-500">Các phòng đang có người thuê sẽ hiển thị ở đây</p>
        </div>

        <!-- Pagination -->
        <div v-if="rooms.links && rooms.links.length > 3" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
          <div class="flex items-center justify-between">
            <div class="flex-1 flex justify-between sm:hidden">
              <Link
                v-if="rooms.links[0].url"
                :href="rooms.links[0].url"
                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
              >
                Trước
              </Link>
              <Link
                v-if="rooms.links[rooms.links.length - 1].url"
                :href="rooms.links[rooms.links.length - 1].url"
                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
              >
                Sau
              </Link>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
              <div>
                <p class="text-sm text-gray-700">
                  Hiển thị
                  <span class="font-medium">{{ rooms.from }}</span>
                  đến
                  <span class="font-medium">{{ rooms.to }}</span>
                  trong tổng số
                  <span class="font-medium">{{ rooms.total }}</span>
                  kết quả
                </p>
              </div>
              <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                  <template v-for="(link, index) in rooms.links" :key="index">
                    <Link
                      v-if="link.url"
                      :href="link.url"
                      v-html="link.label"
                      :class="[
                        'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                        link.active
                          ? 'z-10 bg-primary border-primary text-white'
                          : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                        index === 0 ? 'rounded-l-md' : '',
                        index === rooms.links.length - 1 ? 'rounded-r-md' : ''
                      ]"
                    />
                    <span
                      v-else
                      v-html="link.label"
                      class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-300"
                    />
                  </template>
                </nav>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create Invoice Modal -->
    <div
      v-if="showCreateInvoiceModal"
      class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
      @click.self="closeCreateInvoiceModal"
    >
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Tạo hóa đơn điện nước</h3>
            <button
              @click="closeCreateInvoiceModal"
              class="text-gray-400 hover:text-gray-600"
            >
              <XMarkIcon class="h-6 w-6" />
            </button>
          </div>

          <form @submit.prevent="submitInvoice" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Phòng
              </label>
              <div class="text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-lg">
                Phòng {{ selectedRoom?.room_number }} - {{ selectedRoom?.house.name }}
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Từ ngày <span class="text-red-500">*</span>
              </label>
              <input
                v-model="invoiceForm.start_date"
                type="date"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Đến ngày <span class="text-red-500">*</span>
              </label>
              <input
                v-model="invoiceForm.end_date"
                type="date"
                required
                :min="invoiceForm.start_date"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Tiền điện (VNĐ) <span class="text-red-500">*</span>
              </label>
              <input
                v-model.number="invoiceForm.electricity_amount"
                type="number"
                min="0"
                step="0.01"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                placeholder="0"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Tiền nước (VNĐ) <span class="text-red-500">*</span>
              </label>
              <input
                v-model.number="invoiceForm.water_amount"
                type="number"
                min="0"
                step="0.01"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                placeholder="0"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Phí khác (VNĐ)
              </label>
              <input
                v-model.number="invoiceForm.other_fees"
                type="number"
                min="0"
                step="0.01"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                placeholder="0"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Hạn thanh toán
              </label>
              <input
                v-model="invoiceForm.due_date"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Ghi chú
              </label>
              <textarea
                v-model="invoiceForm.notes"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                placeholder="Nhập ghi chú (nếu có)"
              ></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4">
              <button
                type="button"
                @click="closeCreateInvoiceModal"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
              >
                Hủy
              </button>
              <button
                type="submit"
                :disabled="isSubmitting"
                class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-lg hover:bg-primary-600 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="isSubmitting">Đang tạo...</span>
                <span v-else>Tạo hóa đơn</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- View Invoices Modal -->
    <div
      v-if="showInvoicesModal"
      class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
      @click.self="closeInvoicesModal"
    >
      <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">
              Danh sách hóa đơn - Phòng {{ selectedRoom?.room_number }}
            </h3>
            <button
              @click="closeInvoicesModal"
              class="text-gray-400 hover:text-gray-600"
            >
              <XMarkIcon class="h-6 w-6" />
            </button>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Khoảng thời gian</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tiền điện</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tiền nước</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phí khác</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tổng</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Trạng thái</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hạn thanh toán</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="invoice in selectedRoom?.invoices" :key="invoice.id">
                  <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                    <div v-if="invoice.start_date && invoice.end_date">
                      {{ formatDate(invoice.start_date) }} - {{ formatDate(invoice.end_date) }}
                    </div>
                    <div v-else-if="invoice.month && invoice.year">
                      {{ invoice.month }}/{{ invoice.year }}
                    </div>
                    <span v-else class="text-gray-400">-</span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                    {{ formatCurrency(invoice.electricity_amount) }}
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                    {{ formatCurrency(invoice.water_amount) }}
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                    {{ formatCurrency(invoice.other_fees || 0) }}
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ formatCurrency(invoice.amount) }}
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <span
                      :class="[
                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                        invoice.status === 'paid'
                          ? 'bg-green-100 text-green-800'
                          : 'bg-orange-100 text-orange-800'
                      ]"
                    >
                      {{ invoice.status === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán' }}
                    </span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                    {{ invoice.due_date ? formatDate(invoice.due_date) : '-' }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="mt-4 flex justify-end">
            <button
              @click="closeInvoicesModal"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
            >
              Đóng
            </button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import SelectSearchable from '@/components/ui/SelectSearchable.vue'
import {
  HomeIcon,
  PlusIcon,
  XMarkIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  rooms: Object,
  filters: {
    type: Object,
    default: () => ({}),
  },
  houses: {
    type: Array,
    default: () => [],
  },
})

const search = ref(props.filters.search || '')
const filters = reactive({
  house_id: props.filters.house_id || null,
})

const showCreateInvoiceModal = ref(false)
const showInvoicesModal = ref(false)
const selectedRoom = ref(null)
const isSubmitting = ref(false)

const invoiceForm = reactive({
  booking_id: null,
  start_date: '',
  end_date: '',
  electricity_amount: 0,
  water_amount: 0,
  other_fees: 0,
  due_date: '',
  notes: '',
})

const hasActiveFilters = computed(() => {
  return search.value || filters.house_id
})

let searchTimeout = null

const handleSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    applyFilters()
  }, 300)
}

const handleHouseChange = () => {
  applyFilters()
}

const applyFilters = () => {
  router.get('/admin/occupied-rooms', {
    search: search.value || null,
    house_id: filters.house_id || null,
  }, {
    preserveState: true,
    preserveScroll: true,
  })
}

const clearFilters = () => {
  search.value = ''
  filters.house_id = null
  applyFilters()
}

const formatDate = (date) => {
  if (!date) return '-'
  const d = new Date(date)
  return d.toLocaleDateString('vi-VN')
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND',
  }).format(amount)
}

const openCreateInvoiceModal = (room) => {
  selectedRoom.value = room
  // Set booking_id if available, otherwise null (for tenant invoices)
  invoiceForm.booking_id = room.active_booking?.id || null
  
  // Set default date range based on booking or tenant period
  const today = new Date()
  if (room.active_booking) {
    invoiceForm.start_date = room.active_booking.start_date || today.toISOString().split('T')[0]
    invoiceForm.end_date = room.active_booking.end_date || today.toISOString().split('T')[0]
  } else if (room.tenant) {
    invoiceForm.start_date = room.tenant.rental_start_date || today.toISOString().split('T')[0]
    invoiceForm.end_date = room.tenant.rental_end_date || today.toISOString().split('T')[0]
  } else {
    invoiceForm.start_date = today.toISOString().split('T')[0]
    invoiceForm.end_date = today.toISOString().split('T')[0]
  }
  
  invoiceForm.electricity_amount = 0
  invoiceForm.water_amount = 0
  invoiceForm.other_fees = 0
  invoiceForm.due_date = ''
  invoiceForm.notes = ''
  showCreateInvoiceModal.value = true
}

const closeCreateInvoiceModal = () => {
  showCreateInvoiceModal.value = false
  selectedRoom.value = null
}

const submitInvoice = () => {
  if (!selectedRoom.value) {
    return
  }

  // Validate date range
  if (!invoiceForm.start_date || !invoiceForm.end_date) {
    return
  }

  if (new Date(invoiceForm.start_date) > new Date(invoiceForm.end_date)) {
    alert('Ngày bắt đầu phải nhỏ hơn hoặc bằng ngày kết thúc')
    return
  }

  isSubmitting.value = true

  router.post(
    `/admin/occupied-rooms/${selectedRoom.value.id}/invoices`,
    invoiceForm,
    {
      preserveScroll: true,
      onSuccess: () => {
        closeCreateInvoiceModal()
        isSubmitting.value = false
      },
      onError: () => {
        isSubmitting.value = false
      },
    }
  )
}

const viewInvoices = (room) => {
  selectedRoom.value = room
  showInvoicesModal.value = true
}

const closeInvoicesModal = () => {
  showInvoicesModal.value = false
  selectedRoom.value = null
}
</script>
