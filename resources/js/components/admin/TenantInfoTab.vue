<template>
  <div class="space-y-4">
    <div v-if="form.status !== 'active'" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
      <p class="text-sm text-yellow-800">
        <span class="font-medium">Lưu ý:</span> Phòng đang ở trạng thái "Trống". 
        Để thêm khách thuê, vui lòng chuyển trạng thái phòng sang "Đang thuê" trong tab "Thông tin phòng trọ".
      </p>
    </div>

    <div v-else class="space-y-4">
      <!-- Tenant Selection -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Người đang thuê <span class="text-red-500">*</span>
        </label>
        <SelectSearchable
          :model-value="form.tenant_id"
          @update:modelValue="handleTenantChange"
          :options="users"
          option-value="id"
          :option-label="(user) => `${user.name} (${user.email}${user.phone ? ' - ' + user.phone : ''})`"
          placeholder="Tìm kiếm và chọn người thuê..."
          :error="errors.tenant_id"
          hint="Chọn tài khoản người dùng đang thuê phòng này"
        />
      </div>

      <!-- Rental Start Date -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Ngày bắt đầu thuê <span class="text-red-500">*</span>
        </label>
        <DateInput
          :model-value="form.rental_start_date"
          @update:modelValue="$emit('update:form', { ...form, rental_start_date: $event })"
          :error="errors.rental_start_date"
          placeholder="dd/mm/yyyy"
        />
      </div>

      <!-- Rental End Date -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Ngày kết thúc thuê <span class="text-red-500">*</span>
        </label>
        <DatePicker
          :model-value="form.rental_end_date"
          @update:modelValue="$emit('update:form', { ...form, rental_end_date: $event })"
          :error="errors.rental_end_date"
          :min="form.rental_start_date || undefined"
          placeholder="dd/mm/yyyy"
        />
      </div>

      <!-- Invoice Section -->
      <div class="pt-4 border-t">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900">Hóa đơn điện nước</h3>
          <button
            type="button"
            @click="showCreateForm = !showCreateForm"
            class="text-sm text-primary hover:text-primary-600 font-medium"
          >
            {{ showCreateForm ? 'Xem danh sách' : 'Tạo hóa đơn mới' }}
          </button>
        </div>

        <!-- Invoice List -->
        <div v-if="!showCreateForm" class="space-y-3">
          <div v-if="invoices.length === 0" class="text-center py-8 text-sm text-gray-500 border border-gray-200 rounded-lg">
            <p>Chưa có hóa đơn nào</p>
          </div>
          <div
            v-for="invoice in invoices"
            :key="invoice.id"
            class="border border-gray-200 rounded-lg p-4 hover:border-gray-300 transition-colors"
          >
            <div class="flex items-start justify-between mb-2">
              <div>
                <p class="font-medium text-gray-900">
                  {{ formatDateRange(invoice.start_date, invoice.end_date) }}
                </p>
                <p class="text-xs text-gray-500 mt-1">
                  Ngày đến hạn: {{ invoice.due_date ? formatDate(invoice.due_date) : 'N/A' }}
                </p>
              </div>
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
            <div class="space-y-1 text-sm mt-3">
              <div class="flex justify-between">
                <span class="text-gray-600">Tiền điện:</span>
                <span class="font-medium">{{ formatPrice(invoice.electricity_amount) }} VNĐ</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Tiền nước:</span>
                <span class="font-medium">{{ formatPrice(invoice.water_amount) }} VNĐ</span>
              </div>
              <div v-if="invoice.other_fees > 0" class="flex justify-between">
                <span class="text-gray-600">Phí khác:</span>
                <span class="font-medium">{{ formatPrice(invoice.other_fees) }} VNĐ</span>
              </div>
              <div class="flex justify-between pt-2 border-t">
                <span class="font-semibold text-gray-900">Tổng cộng:</span>
                <span class="font-bold text-primary">{{ formatPrice(invoice.total) }} VNĐ</span>
              </div>
            </div>
            <div v-if="invoice.paid_at" class="mt-2 text-xs text-gray-500">
              Đã thanh toán: {{ formatDateTime(invoice.paid_at) }}
            </div>
          </div>
        </div>

        <!-- Create Invoice Form -->
        <form v-else @submit.prevent="handleCreateInvoice" class="space-y-4">
          <!-- Invoice Date Range -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Từ ngày <span class="text-red-500">*</span>
              </label>
              <DateInput
                v-model="invoiceForm.start_date"
                :error="invoiceErrors.start_date"
                placeholder="dd/mm/yyyy"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Đến ngày <span class="text-red-500">*</span>
              </label>
              <DateInput
                v-model="invoiceForm.end_date"
                :error="invoiceErrors.end_date"
                :min="invoiceForm.start_date || undefined"
                placeholder="dd/mm/yyyy"
              />
            </div>
          </div>

          <!-- Electricity Amount -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Tiền điện (VNĐ) <span class="text-red-500">*</span>
            </label>
            <div class="flex items-center gap-2">
              <input
                v-model.number="invoiceForm.electricity_amount"
                type="number"
                min="0"
                step="1000"
                placeholder="0"
                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                :class="{ 'border-red-500': invoiceErrors.electricity_amount }"
              />
              <span class="text-xs text-gray-500 font-medium whitespace-nowrap">
                {{ formatPriceDisplay(invoiceForm.electricity_amount) }}
              </span>
            </div>
            <p v-if="invoiceErrors.electricity_amount" class="mt-1 text-sm text-red-600">
              {{ invoiceErrors.electricity_amount }}
            </p>
          </div>

          <!-- Water Amount -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Tiền nước (VNĐ) <span class="text-red-500">*</span>
            </label>
            <div class="flex items-center gap-2">
              <input
                v-model.number="invoiceForm.water_amount"
                type="number"
                min="0"
                step="1000"
                placeholder="0"
                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                :class="{ 'border-red-500': invoiceErrors.water_amount }"
              />
              <span class="text-xs text-gray-500 font-medium whitespace-nowrap">
                {{ formatPriceDisplay(invoiceForm.water_amount) }}
              </span>
            </div>
            <p v-if="invoiceErrors.water_amount" class="mt-1 text-sm text-red-600">
              {{ invoiceErrors.water_amount }}
            </p>
          </div>

          <!-- Other Fees -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Phí khác (VNĐ)
            </label>
            <div class="flex items-center gap-2">
              <input
                v-model.number="invoiceForm.other_fees"
                type="number"
                min="0"
                step="1000"
                placeholder="0"
                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
              />
              <span class="text-xs text-gray-500 font-medium whitespace-nowrap">
                {{ formatPriceDisplay(invoiceForm.other_fees) }}
              </span>
            </div>
          </div>

          <!-- Due Date -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Ngày đến hạn
            </label>
            <DateInput
              v-model="invoiceForm.due_date"
              :error="invoiceErrors.due_date"
              placeholder="dd/mm/yyyy"
            />
          </div>

          <!-- Notes -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Ghi chú
            </label>
            <textarea
              v-model="invoiceForm.notes"
              rows="3"
              placeholder="Nhập ghi chú (nếu có)..."
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
            ></textarea>
          </div>

          <!-- Create Invoice Button -->
          <Button
            type="submit"
            :disabled="isCreatingInvoice"
            class="w-full"
          >
            <span v-if="isCreatingInvoice">Đang tạo...</span>
            <span v-else>Tạo hóa đơn</span>
          </Button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { useToast } from '@/composables/useToast'
import Button from '@/components/ui/Button.vue'
import SelectSearchable from '@/components/ui/SelectSearchable.vue'
import DateInput from '@/components/ui/DateInput.vue'
import DatePicker from '@/components/ui/DatePicker.vue'

const props = defineProps({
  form: {
    type: Object,
    required: true,
  },
  users: {
    type: Array,
    default: () => [],
  },
  errors: {
    type: Object,
    default: () => ({}),
  },
  room: {
    type: Object,
    default: null,
  },
  house: {
    type: Object,
    required: true,
  },
  isActive: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['update:form'])

const toast = useToast()

// Invoice form
const invoiceForm = ref({
  start_date: '',
  end_date: '',
  electricity_amount: 0,
  water_amount: 0,
  other_fees: 0,
  due_date: '',
  notes: '',
})

const invoiceErrors = ref({})
const isCreatingInvoice = ref(false)
const showCreateForm = ref(false)
const invoices = ref([])

// Load invoices when room or tenant changes
watch([() => props.room, () => props.form.tenant_id], async ([newRoom, newTenantId]) => {
  if (newRoom?.id && newTenantId) {
    await loadInvoices()
  } else {
    invoices.value = []
  }
}, { immediate: true })

// Load invoices when tab becomes active
watch(() => props.isActive, async (isActive) => {
  if (isActive && props.room?.id && props.form.tenant_id) {
    await loadInvoices()
  }
})

// Load invoices on mount if tab is already active
onMounted(async () => {
  if (props.isActive && props.room?.id && props.form.tenant_id) {
    await loadInvoices()
  }
})

// Load invoices from backend
const loadInvoices = async () => {
  if (!props.room?.id || !props.form.tenant_id) {
    invoices.value = []
    return
  }

  try {
    const response = await fetch(`/admin/houses/${props.house.id}/rooms/${props.room.id}/invoices`)
    if (response.ok) {
      const data = await response.json()
      invoices.value = data.invoices || []
    }
  } catch (error) {
    console.error('Error loading invoices:', error)
    invoices.value = []
  }
}

// Format price
const formatPrice = (price) => {
  if (!price) return '0'
  return new Intl.NumberFormat('vi-VN').format(price)
}

// Format price for display in input (with VNĐ suffix)
const formatPriceDisplay = (price) => {
  if (!price || price === 0) return ''
  return formatPrice(price) + ' đ'
}

// Format date
const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString('vi-VN', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  })
}

// Format date range
const formatDateRange = (startDate, endDate) => {
  if (!startDate || !endDate) return 'N/A'
  return `${formatDate(startDate)} - ${formatDate(endDate)}`
}

// Format date time
const formatDateTime = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleString('vi-VN', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Handle tenant selection change
const handleTenantChange = (tenantId) => {
  const selectedUser = props.users.find(u => u.id === tenantId)
  emit('update:form', {
    ...props.form,
    tenant_id: tenantId,
    tenant_name: selectedUser ? selectedUser.name : '',
  })
}

// Handle create invoice
const handleCreateInvoice = () => {
  if (!props.room?.id) {
    return
  }

  // Reset errors
  invoiceErrors.value = {}

  // Validate
  if (!invoiceForm.value.start_date) {
    invoiceErrors.value.start_date = 'Vui lòng chọn ngày bắt đầu'
    return
  }

  if (!invoiceForm.value.end_date) {
    invoiceErrors.value.end_date = 'Vui lòng chọn ngày kết thúc'
    return
  }

  if (invoiceForm.value.electricity_amount === null || invoiceForm.value.electricity_amount < 0) {
    invoiceErrors.value.electricity_amount = 'Vui lòng nhập tiền điện'
    return
  }

  if (invoiceForm.value.water_amount === null || invoiceForm.value.water_amount < 0) {
    invoiceErrors.value.water_amount = 'Vui lòng nhập tiền nước'
    return
  }

  isCreatingInvoice.value = true

  router.post(
    `/admin/houses/${props.house.id}/rooms/${props.room.id}/invoices`,
    invoiceForm.value,
    {
      preserveScroll: true,
      onSuccess: () => {
        toast.success('Tạo hóa đơn điện nước thành công')
        // Reset form
        invoiceForm.value = {
          start_date: '',
          end_date: '',
          electricity_amount: 0,
          water_amount: 0,
          other_fees: 0,
          due_date: '',
          notes: '',
        }
        invoiceErrors.value = {}
        isCreatingInvoice.value = false
        showCreateForm.value = false
        // Reload invoices
        loadInvoices()
      },
      onError: (errors) => {
        toast.error('Có lỗi xảy ra khi tạo hóa đơn')
        invoiceErrors.value = errors
        isCreatingInvoice.value = false
      },
    }
  )
}
</script>
