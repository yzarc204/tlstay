<template>
  <Head title="Chi tiết Tài khoản" />
  <AdminLayout title="Chi tiết Tài khoản">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <Link
            href="/admin/users"
            class="text-primary hover:text-primary-600 mb-2 inline-flex items-center text-sm"
          >
            <ChevronLeftIcon class="h-4 w-4 mr-1" />
            Quay lại danh sách
          </Link>
          <h1 class="text-2xl font-bold text-gray-900">Chi tiết Tài khoản</h1>
        </div>
        <div class="flex gap-2">
          <Button
            v-if="!user.banned_at"
            variant="destructive"
            @click="showBanModal = true"
            :disabled="user.role === 'manager' || user.id === $page.props.auth.user.id"
          >
            Khóa tài khoản
          </Button>
          <Button
            v-else
            variant="default"
            @click="handleUnban"
          >
            Mở khóa tài khoản
          </Button>
        </div>
      </div>

      <!-- User Info Card -->
      <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-start gap-6">
          <div class="flex-shrink-0">
            <div class="h-20 w-20 rounded-full bg-primary/10 flex items-center justify-center">
              <span class="text-3xl text-primary font-bold">
                {{ user.name.charAt(0).toUpperCase() }}
              </span>
            </div>
          </div>
          <div class="flex-1">
            <div class="flex items-center gap-3 mb-2">
              <h2 class="text-xl font-bold text-gray-900">{{ user.name }}</h2>
              <span
                :class="[
                  'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                  user.role === 'manager'
                    ? 'bg-purple-100 text-purple-800'
                    : 'bg-blue-100 text-blue-800'
                ]"
              >
                {{ user.role === 'manager' ? 'Quản lý' : 'Khách hàng' }}
              </span>
              <span
                v-if="user.banned_at"
                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800"
              >
                Đã khóa
              </span>
              <span
                v-else
                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800"
              >
                Hoạt động
              </span>
            </div>
            <div class="space-y-2 text-sm text-gray-600">
              <div class="flex items-center gap-2">
                <EnvelopeIcon class="h-4 w-4" />
                <span>{{ user.email }}</span>
              </div>
              <div v-if="user.phone" class="flex items-center gap-2">
                <PhoneIcon class="h-4 w-4" />
                <span>{{ user.phone }}</span>
              </div>
              <div class="flex items-center gap-2">
                <CalendarIcon class="h-4 w-4" />
                <span>Tham gia: {{ formatDate(user.created_at) }}</span>
              </div>
              <div v-if="user.banned_at" class="flex items-center gap-2 text-red-600">
                <ShieldExclamationIcon class="h-4 w-4" />
                <span>Bị khóa vào: {{ formatDate(user.banned_at) }}</span>
              </div>
            </div>
            <div v-if="user.ban_reason" class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
              <p class="text-sm font-medium text-red-800 mb-1">Lý do khóa:</p>
              <p class="text-sm text-red-700">{{ user.ban_reason }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Personal Information Card -->
      <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Thông tin cá nhân</h3>
        <div v-if="hasPersonalInfo" class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Số CCCD -->
          <div v-if="user.id_card_number" class="flex items-start gap-3">
            <div class="flex-shrink-0 mt-0.5">
              <IdentificationIcon class="h-5 w-5 text-gray-400" />
            </div>
            <div>
              <p class="text-xs font-medium text-gray-500 mb-1">Số căn cước công dân</p>
              <p class="text-sm text-gray-900">{{ user.id_card_number }}</p>
            </div>
          </div>

          <!-- Ngày cấp CCCD -->
          <div v-if="user.id_card_issue_date" class="flex items-start gap-3">
            <div class="flex-shrink-0 mt-0.5">
              <CalendarDaysIcon class="h-5 w-5 text-gray-400" />
            </div>
            <div>
              <p class="text-xs font-medium text-gray-500 mb-1">Ngày cấp</p>
              <p class="text-sm text-gray-900">{{ formatDateOnly(user.id_card_issue_date) }}</p>
            </div>
          </div>

          <!-- Nơi cấp CCCD -->
          <div v-if="user.id_card_issue_place" class="flex items-start gap-3 md:col-span-2">
            <div class="flex-shrink-0 mt-0.5">
              <BuildingLibraryIcon class="h-5 w-5 text-gray-400" />
            </div>
            <div class="flex-1">
              <p class="text-xs font-medium text-gray-500 mb-1">Nơi cấp</p>
              <p class="text-sm text-gray-900">{{ user.id_card_issue_place }}</p>
            </div>
          </div>

          <!-- Ngày sinh -->
          <div v-if="user.date_of_birth" class="flex items-start gap-3">
            <div class="flex-shrink-0 mt-0.5">
              <CalendarIcon class="h-5 w-5 text-gray-400" />
            </div>
            <div>
              <p class="text-xs font-medium text-gray-500 mb-1">Ngày sinh</p>
              <p class="text-sm text-gray-900">{{ formatDateOnly(user.date_of_birth) }}</p>
            </div>
          </div>

          <!-- Giới tính -->
          <div v-if="user.gender" class="flex items-start gap-3">
            <div class="flex-shrink-0 mt-0.5">
              <UserIcon class="h-5 w-5 text-gray-400" />
            </div>
            <div>
              <p class="text-xs font-medium text-gray-500 mb-1">Giới tính</p>
              <p class="text-sm text-gray-900">{{ formatGender(user.gender) }}</p>
            </div>
          </div>

          <!-- Địa chỉ thường trú -->
          <div v-if="user.permanent_address" class="flex items-start gap-3 md:col-span-2">
            <div class="flex-shrink-0 mt-0.5">
              <MapPinIcon class="h-5 w-5 text-gray-400" />
            </div>
            <div class="flex-1">
              <p class="text-xs font-medium text-gray-500 mb-1">Địa chỉ thường trú</p>
              <p class="text-sm text-gray-900">{{ user.permanent_address }}</p>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-8 text-gray-500">
          <p class="text-sm">Chưa cập nhật thông tin cá nhân</p>
        </div>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow-sm p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Tổng đặt phòng</p>
              <p class="text-2xl font-bold text-gray-900 mt-1">
                {{ user.bookings?.length || 0 }}
              </p>
            </div>
            <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
              <CalendarIcon class="h-6 w-6 text-blue-600" />
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Tổng hóa đơn</p>
              <p class="text-2xl font-bold text-gray-900 mt-1">
                {{ user.invoices?.length || 0 }}
              </p>
            </div>
            <div class="h-12 w-12 rounded-full bg-green-100 flex items-center justify-center">
              <DocumentTextIcon class="h-6 w-6 text-green-600" />
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Trạng thái</p>
              <p class="text-lg font-semibold mt-1" :class="user.banned_at ? 'text-red-600' : 'text-green-600'">
                {{ user.banned_at ? 'Đã khóa' : 'Hoạt động' }}
              </p>
            </div>
            <div class="h-12 w-12 rounded-full flex items-center justify-center" :class="user.banned_at ? 'bg-red-100' : 'bg-green-100'">
              <ShieldCheckIcon v-if="!user.banned_at" class="h-6 w-6 text-green-600" />
              <ShieldExclamationIcon v-else class="h-6 w-6 text-red-600" />
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Bookings -->
      <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Đặt phòng gần đây</h3>
        <div v-if="user.bookings && user.bookings.length > 0" class="space-y-4">
          <div
            v-for="booking in user.bookings"
            :key="booking.id"
            class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50"
          >
            <div class="flex items-center justify-between">
              <div>
                <p class="font-medium text-gray-900">
                  {{ booking.booking_code ? `Mã: ${booking.booking_code}` : `Đặt phòng #${booking.id}` }}
                </p>
                <p class="text-sm text-gray-500">
                  {{ formatDate(booking.created_at) }}
                </p>
              </div>
              <span
                :class="[
                  'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                  getBookingStatusBadgeClass(booking.booking_status || booking.status)
                ]"
              >
                {{ getBookingStatusText(booking.booking_status || booking.status) }}
              </span>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-8 text-gray-500">
          <p>Chưa có đặt phòng nào</p>
        </div>
      </div>

      <!-- Ban Modal -->
      <div
        v-if="showBanModal"
        class="fixed inset-0 z-50 overflow-y-auto"
        @click.self="showBanModal = false"
      >
        <div class="flex items-center justify-center min-h-screen px-4">
          <div class="fixed inset-0 bg-black opacity-50" @click="showBanModal = false"></div>
          <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Khóa tài khoản</h3>
            <form @submit.prevent="handleBan">
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Lý do khóa <span class="text-red-500">*</span>
                </label>
                <textarea
                  v-model="banReason"
                  rows="4"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                  placeholder="Nhập lý do khóa tài khoản..."
                  required
                ></textarea>
              </div>
              <div class="flex justify-end gap-3">
                <Button
                  type="button"
                  variant="outline"
                  @click="showBanModal = false"
                >
                  Hủy
                </Button>
                <Button
                  type="submit"
                  variant="destructive"
                  :disabled="!banReason || isSubmitting"
                >
                  {{ isSubmitting ? 'Đang xử lý...' : 'Khóa tài khoản' }}
                </Button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, router, Link } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import Button from '@/components/ui/Button.vue'
import {
  ChevronLeftIcon,
  EnvelopeIcon,
  PhoneIcon,
  CalendarIcon,
  CalendarDaysIcon,
  DocumentTextIcon,
  ShieldCheckIcon,
  ShieldExclamationIcon,
  IdentificationIcon,
  UserIcon,
  MapPinIcon,
  BuildingLibraryIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  user: Object,
})

// Check if user has any personal information
const hasPersonalInfo = computed(() => {
  return props.user.id_card_number ||
    props.user.id_card_issue_date ||
    props.user.id_card_issue_place ||
    props.user.permanent_address ||
    props.user.date_of_birth ||
    props.user.gender
})

const showBanModal = ref(false)
const banReason = ref('')
const isSubmitting = ref(false)

const handleBan = () => {
  if (!banReason.value) return

  isSubmitting.value = true
  router.post(`/admin/users/${props.user.id}/ban`, {
    ban_reason: banReason.value,
  }, {
    preserveScroll: true,
    onFinish: () => {
      isSubmitting.value = false
      showBanModal.value = false
      banReason.value = ''
    },
  })
}

const handleUnban = () => {
  if (!confirm('Bạn có chắc chắn muốn mở khóa tài khoản này?')) {
    return
  }

  router.post(`/admin/users/${props.user.id}/unban`, {}, {
    preserveScroll: true,
  })
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('vi-VN', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const formatDateOnly = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('vi-VN', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
  })
}

const getBookingStatusText = (status) => {
  const statusMap = {
    upcoming: 'Sắp tới',
    active: 'Đang ở',
    past: 'Đã ở',
    pending: 'Chờ thanh toán',
    completed: 'Đã kết thúc',
    cancelled: 'Đã hủy',
  }
  return statusMap[status] || status
}

const getBookingStatusBadgeClass = (status) => {
  const classMap = {
    upcoming: 'bg-amber-100 text-amber-800 border border-amber-300',
    active: 'bg-green-100 text-green-700',
    past: 'bg-gray-100 text-gray-600',
    pending: 'bg-yellow-100 text-yellow-700',
    completed: 'bg-gray-100 text-gray-600',
    cancelled: 'bg-red-100 text-red-700',
  }
  return classMap[status] || 'bg-gray-100 text-gray-600'
}

const formatGender = (gender) => {
  const genderMap = {
    'male': 'Nam',
    'female': 'Nữ',
    'other': 'Khác',
  }
  return genderMap[gender] || gender
}
</script>
