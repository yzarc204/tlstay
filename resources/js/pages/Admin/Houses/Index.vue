<template>
  <AdminLayout title="Quản lý Nhà trọ">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Quản lý Nhà trọ</h1>
          <p class="text-gray-600 mt-1">Quản lý tất cả nhà trọ trong hệ thống</p>
        </div>
        <Link href="/admin/houses/create">
          <Button>
            <PlusIcon class="h-5 w-5 mr-2" />
            Thêm nhà trọ mới
          </Button>
        </Link>
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
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- Search -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Tìm kiếm
            </label>
            <input
              v-model="search"
              type="text"
              placeholder="Tìm kiếm theo tên, địa chỉ, quản lý..."
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
              @input="handleSearch"
            />
          </div>

          <!-- Ward Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Phường/Xã
            </label>
            <SelectSearchable
              v-model="filters.ward_id"
              :options="wards"
              option-value="id"
              option-label="name"
              placeholder="Chọn phường/xã..."
              @update:modelValue="handleWardChange"
            />
          </div>

          <!-- Street Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Đường
            </label>
            <SelectSearchable
              v-model="filters.street_id"
              :options="streets"
              option-value="id"
              :option-label="(street) => street.parent ? `${street.name} (${street.parent.name})` : street.name"
              :filter-by="(street) => street.parent ? `${street.name} ${street.parent.name}` : street.name"
              placeholder="Chọn đường..."
              @update:modelValue="handleStreetChange"
            />
          </div>
        </div>
      </div>

      <!-- Houses Table -->
      <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Nhà trọ
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Địa chỉ
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Tầng / Số phòng
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Trạng thái
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Giá/ngày
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
              <tr v-for="house in houses.data" :key="house.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-16 w-16">
                      <img
                        v-if="house.image"
                        :src="house.image"
                        :alt="house.name"
                        class="h-16 w-16 rounded-lg object-cover border-2 border-gray-200"
                      />
                      <div v-else class="h-16 w-16 rounded-lg bg-gray-100 flex items-center justify-center border-2 border-gray-200">
                        <BuildingOffice2Icon class="h-8 w-8 text-gray-400" />
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ house.name }}</div>
                      <div v-if="house.owner" class="text-xs text-gray-500 mt-1">
                        Quản lý: {{ house.owner.name }}
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center text-sm text-gray-900 max-w-xs">
                    <MapPinIcon class="h-4 w-4 text-gray-400 mr-2 flex-shrink-0" />
                    <span class="truncate">{{ house.address }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex flex-col gap-1 text-sm text-gray-500">
                    <div class="flex items-center">
                      <BuildingOffice2Icon class="h-4 w-4 text-gray-400 mr-2" />
                      <span>{{ house.floors }} tầng</span>
                    </div>
                    <div class="flex items-center">
                      <Squares2X2Icon class="h-4 w-4 text-gray-400 mr-2" />
                      <span>{{ house.rooms_count || 0 }} phòng</span>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex flex-col gap-1">
                    <div class="flex items-center text-sm">
                      <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-1.5"></span>
                        {{ house.available_rooms_count || 0 }} trống
                      </span>
                    </div>
                    <div class="flex items-center text-sm">
                      <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        <span class="w-2 h-2 bg-blue-500 rounded-full mr-1.5"></span>
                        {{ house.occupied_rooms_count || 0 }} đã thuê
                      </span>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="text-sm font-semibold text-primary">
                    {{ formatPrice(house.price_per_day) }} VNĐ/ngày
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(house.created_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center justify-start gap-2">
                    <!-- Manage Rooms Button -->
                    <div class="relative group">
                      <Link
                        :href="`/admin/houses/${house.id}/manage`"
                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-gray-600 hover:bg-primary hover:text-white transition-colors"
                        title="Quản lý phòng"
                      >
                        <Squares2X2Icon class="h-5 w-5" />
                      </Link>
                      <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs font-medium text-white bg-gray-900 rounded shadow-lg whitespace-nowrap opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 pointer-events-none z-50">
                        Quản lý phòng
                        <div class="absolute top-full left-1/2 -translate-x-1/2 -mt-1 border-4 border-transparent border-t-gray-900"></div>
                      </div>
                    </div>

                    <!-- Edit Button -->
                    <div class="relative group">
                      <Link
                        :href="`/admin/houses/${house.id}/edit`"
                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-gray-600 hover:bg-gray-100 hover:text-primary transition-colors"
                        title="Chỉnh sửa"
                      >
                        <PencilIcon class="h-5 w-5" />
                      </Link>
                      <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs font-medium text-white bg-gray-900 rounded shadow-lg whitespace-nowrap opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 pointer-events-none z-50">
                        Chỉnh sửa
                        <div class="absolute top-full left-1/2 -translate-x-1/2 -mt-1 border-4 border-transparent border-t-gray-900"></div>
                      </div>
                    </div>

                    <!-- Delete Button -->
                    <div class="relative group">
                      <button
                        @click="handleDelete(house)"
                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-gray-600 hover:bg-red-50 hover:text-red-600 transition-colors"
                        title="Xóa nhà trọ"
                      >
                        <TrashIcon class="h-5 w-5" />
                      </button>
                      <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs font-medium text-white bg-gray-900 rounded shadow-lg whitespace-nowrap opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 pointer-events-none z-50">
                        Xóa nhà trọ
                        <div class="absolute top-full left-1/2 -translate-x-1/2 -mt-1 border-4 border-transparent border-t-gray-900"></div>
                      </div>
                    </div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Empty State -->
        <div v-if="!houses.data || houses.data.length === 0" class="text-center py-12">
          <BuildingOffice2Icon class="mx-auto h-12 w-12 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900">Chưa có nhà trọ nào</h3>
          <p class="mt-1 text-sm text-gray-500 mb-4">Bắt đầu bằng cách thêm nhà trọ đầu tiên</p>
          <Link href="/admin/houses/create">
            <Button>
              <PlusIcon class="h-5 w-5 mr-2" />
              Thêm nhà trọ mới
            </Button>
          </Link>
        </div>

        <!-- Pagination -->
        <div v-if="houses.links && houses.links.length > 3" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
          <div class="flex items-center justify-between">
            <div class="flex-1 flex justify-between sm:hidden">
              <Link
                v-if="houses.links[0].url"
                :href="houses.links[0].url"
                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
              >
                Trước
              </Link>
              <Link
                v-if="houses.links[houses.links.length - 1].url"
                :href="houses.links[houses.links.length - 1].url"
                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
              >
                Sau
              </Link>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
              <div>
                <p class="text-sm text-gray-700">
                  Hiển thị
                  <span class="font-medium">{{ houses.from }}</span>
                  đến
                  <span class="font-medium">{{ houses.to }}</span>
                  trong tổng số
                  <span class="font-medium">{{ houses.total }}</span>
                  kết quả
                </p>
              </div>
              <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                  <template v-for="(link, index) in houses.links" :key="index">
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
                        index === houses.links.length - 1 ? 'rounded-r-md' : ''
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
  </AdminLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import Button from '@/components/ui/Button.vue'
import SelectSearchable from '@/components/ui/SelectSearchable.vue'
import {
  BuildingOffice2Icon,
  MapPinIcon,
  Squares2X2Icon,
  PlusIcon,
  PencilIcon,
  TrashIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  houses: Object,
  filters: {
    type: Object,
    default: () => ({}),
  },
  wards: {
    type: Array,
    default: () => [],
  },
  streets: {
    type: Array,
    default: () => [],
  },
})

const search = ref(props.filters.search || '')
const filters = reactive({
  ward_id: props.filters.ward_id || null,
  street_id: props.filters.street_id || null,
})

let searchTimeout = null

const handleSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    applyFilters()
  }, 300)
}

const handleWardChange = () => {
  // Khi chọn phường, xóa filter đường
  if (filters.ward_id) {
    filters.street_id = null
  }
  applyFilters()
}

const handleStreetChange = () => {
  // Khi chọn đường, xóa filter phường
  if (filters.street_id) {
    filters.ward_id = null
  }
  applyFilters()
}

const applyFilters = () => {
  const params = {}
  if (search.value) params.search = search.value
  if (filters.ward_id) params.ward_id = filters.ward_id
  if (filters.street_id) params.street_id = filters.street_id
  
  router.get('/admin/houses', params, {
    preserveState: true,
    replace: true,
  })
}

const hasActiveFilters = computed(() => {
  return !!(search.value || filters.ward_id || filters.street_id)
})

const clearFilters = () => {
  search.value = ''
  filters.ward_id = null
  filters.street_id = null
  applyFilters()
}

const formatPrice = (price) => {
  if (!price) return '0'
  return new Intl.NumberFormat('vi-VN').format(price)
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('vi-VN', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
  })
}

const handleDelete = (house) => {
  if (!confirm(`Bạn có chắc chắn muốn xóa nhà trọ "${house.name}"?\n\nHành động này sẽ xóa tất cả phòng và đặt phòng liên quan.`)) {
    return
  }

  router.delete(`/admin/houses/${house.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      // Success message will be shown via Inertia flash message
    },
  })
}
</script>
