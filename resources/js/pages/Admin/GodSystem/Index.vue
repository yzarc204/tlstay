<template>
  <Head title="God System" />
  <AdminLayout title="God System">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">God System</h1>
          <p class="text-gray-600 mt-1">Quản lý thời gian hệ thống và cập nhật trạng thái phòng</p>
        </div>
      </div>

      <!-- Flash Messages -->
      <div v-if="$page.props.flash?.success" class="p-4 bg-green-50 border border-green-200 rounded-lg">
        <p class="text-green-800 whitespace-pre-line">{{ $page.props.flash.success }}</p>
      </div>
      <div v-if="$page.props.flash?.error" class="p-4 bg-red-50 border border-red-200 rounded-lg">
        <p class="text-red-800 whitespace-pre-line">{{ $page.props.flash.error }}</p>
      </div>

      <!-- System Time Management -->
      <div class="bg-white rounded-lg shadow-sm p-6 space-y-6">
        <div>
          <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <Cog6ToothIcon class="w-5 h-5 mr-2 text-purple-600" />
            Quản lý thời gian hệ thống
          </h2>
          <p class="text-sm text-gray-600 mb-6">
            Cho phép thay đổi ngày và giờ của website thủ công để test hoặc điều chỉnh hệ thống.
            Thời gian này sẽ được sử dụng bởi tất cả các chức năng liên quan đến ngày tháng.
          </p>

          <!-- Current Time Info -->
          <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <p class="text-sm text-gray-600 mb-1">Thời gian thực:</p>
                <p class="font-mono text-lg font-semibold text-gray-900">
                  {{ systemTimeInfo?.real_datetime || 'N/A' }}
                </p>
              </div>
              <div>
                <p class="text-sm text-gray-600 mb-1">Thời gian hệ thống đang sử dụng:</p>
                <p class="font-mono text-lg font-semibold" :class="systemTimeInfo?.is_manual ? 'text-purple-600' : 'text-gray-900'">
                  {{ systemTimeInfo?.current_datetime || 'N/A' }}
                </p>
                <span v-if="systemTimeInfo?.is_manual" class="inline-block mt-1 px-2 py-1 text-xs bg-purple-100 text-purple-800 rounded">
                  Manual Mode
                </span>
                <span v-else class="inline-block mt-1 px-2 py-1 text-xs bg-green-100 text-green-800 rounded">
                  Real Time
                </span>
              </div>
            </div>
          </div>

          <!-- Manual Time Form -->
          <form @submit.prevent="handleSystemTimeSubmit" class="space-y-4">
            <div class="flex items-center space-x-4 mb-4">
              <label class="flex items-center space-x-2 cursor-pointer">
                <input
                  v-model="systemTimeForm.enabled"
                  type="checkbox"
                  class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500"
                />
                <span class="text-sm font-medium text-gray-700">Bật chế độ thời gian thủ công</span>
              </label>
            </div>

            <div v-if="systemTimeForm.enabled" class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Ngày (DD/MM/YYYY)
                </label>
                <input
                  v-model="systemTimeForm.date"
                  type="text"
                  placeholder="dd/mm/yyyy"
                  pattern="\d{2}/\d{2}/\d{4}"
                  maxlength="10"
                  required
                  @input="formatDateInput"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500"
                />
                <p class="text-xs text-gray-500 mt-1">Ví dụ: 01/03/2025</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Giờ (HH:MM)
                </label>
                <input
                  v-model="systemTimeForm.time"
                  type="time"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500"
                />
              </div>
            </div>

            <div class="flex items-center space-x-3">
              <button
                type="submit"
                :disabled="systemTimeForm.processing"
                class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {{ systemTimeForm.processing ? 'Đang xử lý...' : 'Cập nhật thời gian' }}
              </button>
              <button
                v-if="systemTimeInfo?.is_manual"
                type="button"
                @click="resetSystemTime"
                :disabled="resetTimeForm.processing"
                class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {{ resetTimeForm.processing ? 'Đang xử lý...' : 'Reset về thời gian thực' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Trigger Update Room -->
      <div class="bg-white rounded-lg shadow-sm p-6 space-y-6">
        <div>
          <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <Cog6ToothIcon class="w-5 h-5 mr-2 text-blue-600" />
            Cập nhật trạng thái phòng
          </h2>
          <p class="text-sm text-gray-600 mb-4">
            Chạy thủ công command cập nhật trạng thái phòng (loại bỏ người hết hạn, chuyển người vào ngày check-in).
            Command này sẽ sử dụng thời gian hệ thống hiện tại (real hoặc manual).
          </p>
          <button
            @click="triggerUpdateRoom"
            :disabled="updateRoomForm.processing"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ updateRoomForm.processing ? 'Đang xử lý...' : 'Chạy cập nhật trạng thái phòng' }}
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, watch } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { Cog6ToothIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  systemTimeInfo: {
    type: Object,
    default: () => ({
      is_manual: false,
      current_datetime: null,
      real_datetime: null,
      manual_date: null,
      manual_time: null,
    }),
  },
})

// God System - System Time Management
const getDefaultTime = () => {
  const time = props.systemTimeInfo?.manual_time || '00:00:00'
  // Convert HH:MM:SS to HH:MM for time input
  return time.substring(0, 5)
}

const formatDateToDDMMYYYY = (dateString) => {
  if (!dateString) {
    const today = new Date()
    const dd = String(today.getDate()).padStart(2, '0')
    const mm = String(today.getMonth() + 1).padStart(2, '0')
    const yyyy = today.getFullYear()
    return `${dd}/${mm}/${yyyy}`
  }
  
  // If already in YYYY-MM-DD format, convert to DD/MM/YYYY
  if (dateString.match(/^\d{4}-\d{2}-\d{2}$/)) {
    const [year, month, day] = dateString.split('-')
    return `${day}/${month}/${year}`
  }
  
  // If already in DD/MM/YYYY format, return as is
  if (dateString.match(/^\d{2}\/\d{2}\/\d{4}$/)) {
    return dateString
  }
  
  return dateString
}

const getInitialDate = () => {
  // Prefer formatted date from backend if available
  if (props.systemTimeInfo?.manual_date_formatted) {
    return props.systemTimeInfo.manual_date_formatted
  }
  // Fallback to manual_date and convert
  if (props.systemTimeInfo?.manual_date) {
    return formatDateToDDMMYYYY(props.systemTimeInfo.manual_date)
  }
  // Default to today
  return formatDateToDDMMYYYY(null)
}

const formatDateInput = (event) => {
  let value = event.target.value.replace(/\D/g, '') // Remove non-digits
  
  // Add slashes automatically
  if (value.length > 2 && value.length <= 4) {
    value = value.substring(0, 2) + '/' + value.substring(2)
  } else if (value.length > 4) {
    value = value.substring(0, 2) + '/' + value.substring(2, 4) + '/' + value.substring(4, 8)
  }
  
  systemTimeForm.date = value
}

const systemTimeForm = useForm({
  enabled: props.systemTimeInfo?.is_manual || false,
  date: getInitialDate(),
  time: getDefaultTime(),
})

const resetTimeForm = useForm({})

const updateRoomForm = useForm({})

const convertDDMMYYYYToYYYYMMDD = (dateString) => {
  if (!dateString) return null
  
  // If already in YYYY-MM-DD format, return as is
  if (dateString.match(/^\d{4}-\d{2}-\d{2}$/)) {
    return dateString
  }
  
  // Convert DD/MM/YYYY to YYYY-MM-DD
  const match = dateString.match(/^(\d{2})\/(\d{2})\/(\d{4})$/)
  if (match) {
    const [, day, month, year] = match
    return `${year}-${month}-${day}`
  }
  
  return dateString
}

const handleSystemTimeSubmit = () => {
  // Validate date format before submitting
  if (systemTimeForm.enabled && systemTimeForm.date) {
    const datePattern = /^\d{2}\/\d{2}\/\d{4}$/
    if (!datePattern.test(systemTimeForm.date)) {
      alert('Vui lòng nhập ngày theo định dạng DD/MM/YYYY (ví dụ: 01/03/2025)')
      return
    }
    
    // Validate date is valid
    const [day, month, year] = systemTimeForm.date.split('/')
    const date = new Date(year, month - 1, day)
    if (date.getDate() != day || date.getMonth() != month - 1 || date.getFullYear() != year) {
      alert('Ngày không hợp lệ. Vui lòng kiểm tra lại.')
      return
    }
    
    // Convert to YYYY-MM-DD for backend
    const formData = {
      ...systemTimeForm.data(),
      date: convertDDMMYYYYToYYYYMMDD(systemTimeForm.date),
    }
    
    systemTimeForm.transform(() => formData).post('/admin/god-system/system-time', {
      preserveScroll: true,
      onSuccess: () => {
        router.reload({ only: ['systemTimeInfo'] })
      },
    })
  } else {
    systemTimeForm.post('/admin/god-system/system-time', {
      preserveScroll: true,
      onSuccess: () => {
        router.reload({ only: ['systemTimeInfo'] })
      },
    })
  }
}

const resetSystemTime = () => {
  if (confirm('Bạn có chắc chắn muốn reset về thời gian thực?')) {
    resetTimeForm.post('/admin/god-system/system-time/reset', {
      preserveScroll: true,
      onSuccess: () => {
        router.reload({ only: ['systemTimeInfo'] })
        systemTimeForm.enabled = false
      },
    })
  }
}

const triggerUpdateRoom = () => {
  if (confirm('Bạn có chắc chắn muốn chạy cập nhật trạng thái phòng ngay bây giờ?')) {
    updateRoomForm.post('/admin/god-system/trigger-update-room', {
      preserveScroll: true,
      onSuccess: () => {
        // Success message will be shown via flash message
      },
    })
  }
}

// Watch systemTimeInfo to update form
watch(() => props.systemTimeInfo, (newInfo) => {
  if (newInfo) {
    systemTimeForm.enabled = newInfo.is_manual || false
    if (newInfo.is_manual) {
      // Prefer formatted date from backend
      if (newInfo.manual_date_formatted) {
        systemTimeForm.date = newInfo.manual_date_formatted
      } else if (newInfo.manual_date) {
        systemTimeForm.date = formatDateToDDMMYYYY(newInfo.manual_date)
      }
    }
    if (newInfo.manual_time) {
      // Convert HH:MM:SS to HH:MM for time input
      systemTimeForm.time = newInfo.manual_time.substring(0, 5)
    }
  }
}, { deep: true, immediate: true })
</script>
