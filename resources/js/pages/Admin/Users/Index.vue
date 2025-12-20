<template>
  <Head title="Quản lý Tài khoản" />
  <AdminLayout title="Quản lý Tài khoản">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Quản lý Tài khoản</h1>
          <p class="text-gray-600 mt-1">Quản lý tất cả tài khoản trong hệ thống</p>
        </div>
        <Button @click="showCreateModal = true">
          <PlusIcon class="h-5 w-5 mr-2" />
          Thêm tài khoản
        </Button>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow-sm p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <!-- Search -->
          <div class="md:col-span-2">
            <input
              v-model="search"
              type="text"
              placeholder="Tìm kiếm theo tên, email, số điện thoại..."
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
              @input="handleSearch"
            />
          </div>

          <!-- Role Filter -->
          <div>
            <select
              v-model="roleFilter"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
              @change="handleFilter"
            >
              <option value="">Tất cả vai trò</option>
              <option value="customer">Khách hàng</option>
              <option value="manager">Quản lý</option>
            </select>
          </div>

          <!-- Status Filter -->
          <div>
            <select
              v-model="statusFilter"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
              @change="handleFilter"
            >
              <option value="">Tất cả trạng thái</option>
              <option value="active">Đang hoạt động</option>
              <option value="banned">Đã khóa</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Users Table -->
      <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Người dùng
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Vai trò
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Trạng thái
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Đặt phòng
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Ngày tạo
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Thao tác
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <img
                        :src="getAvatarUrl(user)"
                        :alt="user.name"
                        class="h-10 w-10 rounded-full object-cover border-2 border-gray-200"
                        @error="(e) => handleAvatarError(e, user)"
                      />
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                      <div class="text-sm text-gray-500">{{ user.email }}</div>
                      <div v-if="user.phone" class="text-xs text-gray-400">{{ user.phone }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
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
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
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
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ user.bookings_count || 0 }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(user.created_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center justify-start gap-2">
                    <!-- View Button -->
                    <div class="relative group">
                      <Link
                        :href="`/admin/users/${user.id}`"
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

                    <!-- Ban/Unban Button -->
                    <div
                      v-if="user.role !== 'manager' && user.id !== $page.props.auth.user.id"
                      class="relative group"
                    >
                      <button
                        v-if="!user.banned_at"
                        @click="showBanModal(user)"
                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-gray-600 hover:bg-red-50 hover:text-red-600 transition-colors"
                        title="Khóa tài khoản"
                      >
                        <LockClosedIcon class="h-5 w-5" />
                      </button>
                      <button
                        v-else
                        @click="handleUnban(user.id)"
                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-gray-600 hover:bg-green-50 hover:text-green-600 transition-colors"
                        title="Mở khóa tài khoản"
                      >
                        <LockOpenIcon class="h-5 w-5" />
                      </button>
                      <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs font-medium text-white bg-gray-900 rounded shadow-lg whitespace-nowrap opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 pointer-events-none z-50">
                        {{ user.banned_at ? 'Mở khóa tài khoản' : 'Khóa tài khoản' }}
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
        <div v-if="users.links && users.links.length > 3" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
          <div class="flex items-center justify-between">
            <div class="flex-1 flex justify-between sm:hidden">
              <Link
                v-if="users.links[0].url"
                :href="users.links[0].url"
                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
              >
                Trước
              </Link>
              <Link
                v-if="users.links[users.links.length - 1].url"
                :href="users.links[users.links.length - 1].url"
                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
              >
                Sau
              </Link>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
              <div>
                <p class="text-sm text-gray-700">
                  Hiển thị
                  <span class="font-medium">{{ users.from }}</span>
                  đến
                  <span class="font-medium">{{ users.to }}</span>
                  trong tổng số
                  <span class="font-medium">{{ users.total }}</span>
                  kết quả
                </p>
              </div>
              <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                  <template v-for="(link, index) in users.links" :key="index">
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
                        index === users.links.length - 1 ? 'rounded-r-md' : ''
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

        <!-- Empty State -->
        <div v-if="users.data.length === 0" class="text-center py-12">
          <UsersIcon class="mx-auto h-12 w-12 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900">Không có người dùng</h3>
          <p class="mt-1 text-sm text-gray-500">Không tìm thấy người dùng nào phù hợp với bộ lọc.</p>
        </div>
      </div>

      <!-- Create User Modal -->
      <div
        v-if="showCreateModal"
        class="fixed inset-0 z-50 overflow-y-auto"
        @click.self="closeCreateModal"
      >
        <div class="flex items-center justify-center min-h-screen px-4">
          <div class="fixed inset-0 bg-black opacity-50" @click="closeCreateModal"></div>
          <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Thêm tài khoản mới</h3>
            <form @submit.prevent="handleCreate">
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Họ tên <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="createForm.name"
                    type="text"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                    placeholder="Nhập họ tên"
                    required
                  />
                  <p v-if="createErrors.name" class="mt-1 text-sm text-red-600">{{ createErrors.name }}</p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Email <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="createForm.email"
                    type="email"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                    placeholder="example@email.com"
                    required
                  />
                  <p v-if="createErrors.email" class="mt-1 text-sm text-red-600">{{ createErrors.email }}</p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Số điện thoại <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="createForm.phone"
                    type="tel"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                    placeholder="0123456789"
                    required
                  />
                  <p v-if="createErrors.phone" class="mt-1 text-sm text-red-600">{{ createErrors.phone }}</p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Mật khẩu <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="createForm.password"
                    type="password"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                    placeholder="Tối thiểu 6 ký tự"
                    required
                  />
                  <p v-if="createErrors.password" class="mt-1 text-sm text-red-600">{{ createErrors.password }}</p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Vai trò <span class="text-red-500">*</span>
                  </label>
                  <select
                    v-model="createForm.role"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                    required
                  >
                    <option value="customer">Khách hàng</option>
                    <option value="manager">Quản lý</option>
                  </select>
                  <p v-if="createErrors.role" class="mt-1 text-sm text-red-600">{{ createErrors.role }}</p>
                </div>
              </div>

              <div class="flex justify-end gap-3 mt-6">
                <button
                  type="button"
                  @click="closeCreateModal"
                  class="px-4 py-2 border-2 border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                >
                  Hủy
                </button>
                <button
                  type="submit"
                  :disabled="isCreating"
                  class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                  {{ isCreating ? 'Đang tạo...' : 'Tạo tài khoản' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Ban Modal -->
      <div
        v-if="showBanModalFor"
        class="fixed inset-0 z-50 overflow-y-auto"
        @click.self="closeBanModal"
      >
        <div class="flex items-center justify-center min-h-screen px-4">
          <div class="fixed inset-0 bg-black opacity-50" @click="closeBanModal"></div>
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
                <button
                  type="button"
                  @click="closeBanModal"
                  class="px-4 py-2 border-2 border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                >
                  Hủy
                </button>
                <button
                  type="submit"
                  :disabled="!banReason || isSubmitting"
                  class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                  {{ isSubmitting ? 'Đang xử lý...' : 'Khóa tài khoản' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, router, Link, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import Button from '@/components/ui/Button.vue'
import { UsersIcon, EyeIcon, LockClosedIcon, LockOpenIcon, PlusIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  users: Object,
  filters: Object,
})

const page = usePage()
const search = ref(props.filters.search || '')
const roleFilter = ref(props.filters.role || '')
const statusFilter = ref(props.filters.status || '')
const showCreateModal = ref(false)
const showBanModalFor = ref(null)
const banReason = ref('')
const isSubmitting = ref(false)
const isCreating = ref(false)

const createForm = ref({
  name: '',
  email: '',
  phone: '',
  password: '',
  role: 'customer',
})

const createErrors = ref({})

let searchTimeout = null

const handleSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    const params = {}
    if (search.value) params.search = search.value
    if (roleFilter.value) params.role = roleFilter.value
    if (statusFilter.value) params.status = statusFilter.value
    
    router.get('/admin/users', params, {
      preserveState: true,
      replace: true,
    })
  }, 300)
}

const handleFilter = () => {
  const params = {}
  if (search.value) params.search = search.value
  if (roleFilter.value) params.role = roleFilter.value
  if (statusFilter.value) params.status = statusFilter.value
  
  router.get('/admin/users', params, {
    preserveState: true,
    replace: true,
  })
}

const showBanModal = (user) => {
  showBanModalFor.value = user
  banReason.value = ''
}

const closeBanModal = () => {
  showBanModalFor.value = null
  banReason.value = ''
}

const handleBan = () => {
  if (!banReason.value || !showBanModalFor.value) return

  isSubmitting.value = true
  router.post(`/admin/users/${showBanModalFor.value.id}/ban`, {
    ban_reason: banReason.value,
  }, {
    preserveScroll: true,
    onFinish: () => {
      isSubmitting.value = false
      closeBanModal()
    },
  })
}

const handleUnban = (userId) => {
  if (!confirm('Bạn có chắc chắn muốn mở khóa tài khoản này?')) {
    return
  }

  router.post(`/admin/users/${userId}/unban`, {}, {
    preserveScroll: true,
  })
}

const closeCreateModal = () => {
  showCreateModal.value = false
  createForm.value = {
    name: '',
    email: '',
    phone: '',
    password: '',
    role: 'customer',
  }
  createErrors.value = {}
}

const handleCreate = () => {
  isCreating.value = true
  createErrors.value = {}

  router.post('/admin/users', createForm.value, {
    preserveScroll: true,
    onSuccess: () => {
      closeCreateModal()
    },
    onError: (errors) => {
      createErrors.value = errors
    },
    onFinish: () => {
      isCreating.value = false
    },
  })
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('vi-VN', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
  })
}

const getAvatarUrl = (user) => {
  // Return avatar from database if exists
  if (user?.avatar) {
    return user.avatar
  }
  // Generate fallback avatar from name
  const name = user?.name || 'User'
  return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=000066&color=fff&size=128`
}

const handleAvatarError = (event, user) => {
  // If avatar fails to load, use fallback
  if (user?.name) {
    event.target.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=000066&color=fff&size=128`
  }
}
</script>
