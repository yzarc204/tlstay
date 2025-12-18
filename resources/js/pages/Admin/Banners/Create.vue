<template>
  <AdminLayout title="Thêm banner mới">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <Link
            href="/admin/banners"
            class="text-primary hover:text-primary-600 mb-2 inline-flex items-center text-sm"
          >
            <ChevronLeftIcon class="h-4 w-4 mr-1" />
            Quay lại danh sách
          </Link>
          <h1 class="text-2xl font-bold text-gray-900">Thêm slider mới</h1>
        </div>
      </div>

      <!-- Flash Messages -->
      <div v-if="$page.props.flash?.success" class="p-4 bg-green-50 border border-green-200 rounded-lg">
        <p class="text-green-800">{{ $page.props.flash.success }}</p>
      </div>
      <div v-if="$page.props.flash?.error" class="p-4 bg-red-50 border border-red-200 rounded-lg">
        <p class="text-red-800">{{ $page.props.flash.error }}</p>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleSubmit" class="bg-white rounded-lg shadow-sm p-6 space-y-6">
        <!-- Basic Information -->
        <div>
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Thông tin cơ bản</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Title -->
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Tiêu đề <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.title"
                type="text"
                required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                placeholder="Nhập tiêu đề banner"
              />
            </div>

            <!-- Description -->
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">Mô tả</label>
              <textarea
                v-model="form.description"
                rows="3"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                placeholder="Nhập mô tả banner..."
              ></textarea>
            </div>

            <!-- Order -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Thứ tự hiển thị</label>
              <input
                v-model.number="form.order"
                type="number"
                min="0"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                placeholder="0"
              />
            </div>

            <!-- Link -->
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">Link (URL)</label>
              <input
                v-model="form.link"
                type="url"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                placeholder="https://example.com"
              />
            </div>

            <!-- Is Active -->
            <div class="md:col-span-2">
              <label class="flex items-center">
                <input
                  v-model="form.is_active"
                  type="checkbox"
                  class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary"
                />
                <span class="ml-2 text-sm text-gray-700">Kích hoạt banner</span>
              </label>
            </div>
          </div>
        </div>

        <!-- Text Overlay -->
        <div>
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Text Overlay (Chữ trên slider)</h2>
          <div class="space-y-4">
            <div>
              <label class="flex items-center">
                <input
                  v-model="form.show_text"
                  type="checkbox"
                  class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary"
                />
                <span class="ml-2 text-sm text-gray-700">Hiển thị chữ trên slider</span>
              </label>
            </div>

            <div v-if="form.show_text" class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Dòng 1 (chữ nhỏ) -->
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Dòng 1 (chữ nhỏ)</label>
                <input
                  v-model="form.text_line1"
                  type="text"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                  placeholder="Nhập dòng 1 (chữ nhỏ)"
                />
              </div>

              <!-- Dòng 2 (chữ to) -->
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Dòng 2 (chữ to)</label>
                <input
                  v-model="form.text_line2"
                  type="text"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                  placeholder="Nhập dòng 2 (chữ to)"
                />
              </div>

              <!-- Dòng 3 (chữ nhỏ) -->
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Dòng 3 (chữ nhỏ)</label>
                <input
                  v-model="form.text_line3"
                  type="text"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                  placeholder="Nhập dòng 3 (chữ nhỏ)"
                />
              </div>

              <!-- Text Position -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Vị trí chữ</label>
                <select
                  v-model="form.text_position"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                >
                  <option value="left">Trái</option>
                  <option value="center">Giữa</option>
                  <option value="right">Phải</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- Image Upload -->
        <div>
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Hình ảnh</h2>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Hình ảnh banner <span class="text-red-500">*</span>
            </label>
            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-primary transition-colors">
              <div class="space-y-1 text-center">
                <PhotoIcon class="mx-auto h-12 w-12 text-gray-400" />
                <div class="flex text-sm text-gray-600">
                  <label class="relative cursor-pointer bg-white rounded-md font-medium text-primary hover:text-primary-700 focus-within:outline-none">
                    <span>Chọn file</span>
                    <input
                      ref="fileInput"
                      type="file"
                      accept="image/*"
                      class="sr-only"
                      @change="handleImageChange"
                      required
                    />
                  </label>
                  <p class="pl-1">hoặc kéo thả vào đây</p>
                </div>
                <p class="text-xs text-gray-500">PNG, JPG, GIF tối đa 5MB</p>
              </div>
            </div>
            <div v-if="imagePreview" class="mt-4">
              <img
                :src="imagePreview"
                alt="Preview"
                class="max-w-full h-64 object-contain rounded-lg border border-gray-300"
              />
            </div>
          </div>
        </div>

        <!-- Date Range -->
        <div>
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Thời gian hiển thị (Tùy chọn)</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Start Date -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Ngày bắt đầu</label>
              <input
                v-model="form.start_date"
                type="datetime-local"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
              />
            </div>

            <!-- End Date -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Ngày kết thúc</label>
              <input
                v-model="form.end_date"
                type="datetime-local"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
              />
            </div>
          </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end space-x-4 pt-6 border-t">
          <Link
            href="/admin/banners"
            class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
          >
            Hủy
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="!form.processing">Thêm slider</span>
            <span v-else>Đang thêm...</span>
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { ChevronLeftIcon, PhotoIcon } from '@heroicons/vue/24/outline'

const form = useForm({
  title: '',
  description: '',
  image: null,
  link: '',
  order: 0,
  is_active: true,
  start_date: '',
  end_date: '',
  show_text: false,
  text_title: '',
  text_subtitle: '',
  text_line1: '',
  text_line2: '',
  text_line3: '',
  text_position: 'center',
})

const fileInput = ref(null)
const imagePreview = ref(null)

const handleImageChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    form.image = file
    // Create preview
    const reader = new FileReader()
    reader.onload = (e) => {
      imagePreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

const handleSubmit = () => {
  form.post('/admin/banners', {
    preserveScroll: true,
    onSuccess: () => {
      // Redirect handled by controller
    },
  })
}
</script>
