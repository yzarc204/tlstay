<template>
  <Head title="Quản lý Banner" />
  <AdminLayout title="Quản lý Banner">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Quản lý Slider</h1>
          <p class="text-gray-600 mt-1">Quản lý slider trên website</p>
        </div>
        <Link href="/admin/banners/create">
          <Button>
            <PlusIcon class="h-5 w-5 mr-2" />
            Thêm slider mới
          </Button>
        </Link>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow-sm p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Tìm kiếm</label>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Tìm theo tiêu đề, mô tả..."
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
              @input="handleFilter"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Trạng thái</label>
            <select
              v-model="filters.status"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
              @change="handleFilter"
            >
              <option value="">Tất cả</option>
              <option value="active">Đang hoạt động</option>
              <option value="inactive">Tắt</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Banners Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="banner in banners.data"
          :key="banner.id"
          class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow"
        >
          <div class="relative">
            <img
              :src="banner.image"
              :alt="banner.title"
              class="w-full h-48 object-cover"
            />
            <span
              class="absolute top-2 right-2 px-2 py-1 text-xs font-semibold rounded"
              :class="
                banner.is_active
                  ? 'bg-green-100 text-green-800'
                  : 'bg-gray-100 text-gray-600'
              "
            >
              {{ banner.is_active ? 'Hoạt động' : 'Tắt' }}
            </span>
          </div>
          <div class="p-4">
            <h3 class="font-semibold text-gray-900 mb-1">{{ banner.title }}</h3>
            <p v-if="banner.description" class="text-sm text-gray-600 mb-2 line-clamp-2">
              {{ banner.description }}
            </p>
            <div class="flex items-center justify-between text-xs text-gray-500 mb-3">
              <span>Thứ tự: {{ banner.order }}</span>
              <span v-if="banner.link" class="text-primary">Có link</span>
            </div>
            <div class="flex items-center space-x-2">
              <Link
                :href="`/admin/banners/${banner.id}/edit`"
                class="flex-1 px-3 py-2 text-sm bg-primary text-white rounded-lg hover:bg-primary-700 transition-colors text-center"
              >
                Chỉnh sửa
              </Link>
              <button
                @click="deleteBanner(banner.id)"
                class="px-3 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
              >
                <TrashIcon class="h-4 w-4" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="banners.data.length === 0" class="text-center py-12 bg-white rounded-lg">
        <PhotoIcon class="w-16 h-16 mx-auto text-gray-300 mb-4" />
        <p class="text-gray-500">Chưa có slider nào</p>
      </div>

      <!-- Pagination -->
      <div v-if="banners.links && banners.links.length > 3" class="flex justify-center">
        <div class="flex space-x-2">
          <Link
            v-for="link in banners.links"
            :key="link.label"
            :href="link.url || '#'"
            :class="[
              'px-4 py-2 rounded-lg border transition-colors',
              link.active
                ? 'bg-primary text-white border-primary'
                : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50',
              !link.url && 'opacity-50 cursor-not-allowed'
            ]"
            v-html="link.label"
          />
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import Button from '@/components/ui/Button.vue'
import { PlusIcon, PhotoIcon, TrashIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  banners: {
    type: Object,
    required: true,
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
})

const filters = reactive({
  search: props.filters.search || '',
  status: props.filters.status || '',
})

const handleFilter = () => {
  router.get('/admin/banners', filters, {
    preserveState: true,
    preserveScroll: true,
  })
}

const deleteBanner = (id) => {
  if (confirm('Bạn có chắc chắn muốn xóa banner này?')) {
    router.delete(`/admin/banners/${id}`, {
      preserveScroll: true,
      onSuccess: () => {
        // Banner deleted
      },
    })
  }
}
</script>
