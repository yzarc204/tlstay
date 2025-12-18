<template>
  <AppLayout>
    <div class="min-h-screen bg-gray-50 py-8">
      <div class="max-w-4xl mx-auto px-4">
        <!-- Header -->
        <div class="mb-6">
          <h1 class="text-3xl font-bold text-gray-900">Thông tin của tôi</h1>
          <p class="text-gray-600 mt-1">Quản lý thông tin cá nhân và tài khoản của bạn</p>
        </div>

        <!-- Flash Messages -->
        <div v-if="$page.props.flash?.success" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
          <p class="text-green-800">{{ $page.props.flash.success }}</p>
        </div>
        <div v-if="$page.props.flash?.error" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
          <p class="text-red-800">{{ $page.props.flash.error }}</p>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleSubmit" class="bg-white rounded-lg shadow-sm p-6 space-y-8">
          <!-- Basic Information -->
          <div class="space-y-6">
            <div>
              <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <UserIcon class="w-5 h-5 mr-2 text-primary" />
                Thông tin cơ bản
              </h2>
              <div class="grid md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Họ và tên <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="form.name"
                    type="text"
                    :class="[
                      'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:outline-none transition-colors',
                      form.errors.name
                        ? 'border-red-500 focus:ring-red-500 focus:border-red-500'
                        : 'border-gray-300 focus:ring-primary focus:border-transparent'
                    ]"
                    placeholder="Nhập họ và tên"
                    required
                  />
                  <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                    {{ form.errors.name }}
                  </p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Email
                  </label>
                  <input
                    :value="user.email"
                    type="email"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed"
                    disabled
                  />
                  <p class="mt-1 text-xs text-gray-500">Email không thể thay đổi</p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Số điện thoại
                  </label>
                  <input
                    v-model="form.phone"
                    type="text"
                    :class="[
                      'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:outline-none transition-colors',
                      form.errors.phone
                        ? 'border-red-500 focus:ring-red-500 focus:border-red-500'
                        : 'border-gray-300 focus:ring-primary focus:border-transparent'
                    ]"
                    placeholder="Nhập số điện thoại"
                  />
                  <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600">
                    {{ form.errors.phone }}
                  </p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Ngày sinh <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="form.date_of_birth"
                    type="date"
                    :max="today"
                    :class="[
                      'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:outline-none transition-colors',
                      form.errors.date_of_birth
                        ? 'border-red-500 focus:ring-red-500 focus:border-red-500'
                        : 'border-gray-300 focus:ring-primary focus:border-transparent'
                    ]"
                    required
                  />
                  <p v-if="form.errors.date_of_birth" class="mt-1 text-sm text-red-600">
                    {{ form.errors.date_of_birth }}
                  </p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Giới tính <span class="text-red-500">*</span>
                  </label>
                  <select
                    v-model="form.gender"
                    :class="[
                      'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:outline-none transition-colors',
                      form.errors.gender
                        ? 'border-red-500 focus:ring-red-500 focus:border-red-500'
                        : 'border-gray-300 focus:ring-primary focus:border-transparent'
                    ]"
                    required
                  >
                    <option value="">Chọn giới tính</option>
                    <option value="male">Nam</option>
                    <option value="female">Nữ</option>
                    <option value="other">Khác</option>
                  </select>
                  <p v-if="form.errors.gender" class="mt-1 text-sm text-red-600">
                    {{ form.errors.gender }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- ID Card Information -->
          <div class="space-y-6 border-t pt-6">
            <div>
              <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <IdentificationIcon class="w-5 h-5 mr-2 text-primary" />
                Thông tin căn cước công dân
              </h2>
              <div class="grid md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Số CCCD <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="form.id_card_number"
                    type="text"
                    :class="[
                      'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:outline-none transition-colors',
                      form.errors.id_card_number
                        ? 'border-red-500 focus:ring-red-500 focus:border-red-500'
                        : 'border-gray-300 focus:ring-primary focus:border-transparent'
                    ]"
                    placeholder="Nhập số căn cước công dân"
                    required
                  />
                  <p v-if="form.errors.id_card_number" class="mt-1 text-sm text-red-600">
                    {{ form.errors.id_card_number }}
                  </p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Ngày cấp <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="form.id_card_issue_date"
                    type="date"
                    :max="today"
                    :class="[
                      'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:outline-none transition-colors',
                      form.errors.id_card_issue_date
                        ? 'border-red-500 focus:ring-red-500 focus:border-red-500'
                        : 'border-gray-300 focus:ring-primary focus:border-transparent'
                    ]"
                    required
                  />
                  <p v-if="form.errors.id_card_issue_date" class="mt-1 text-sm text-red-600">
                    {{ form.errors.id_card_issue_date }}
                  </p>
                </div>

                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Nơi cấp <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="form.id_card_issue_place"
                    type="text"
                    list="id-card-issue-places"
                    :class="[
                      'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:outline-none transition-colors',
                      form.errors.id_card_issue_place
                        ? 'border-red-500 focus:ring-red-500 focus:border-red-500'
                        : 'border-gray-300 focus:ring-primary focus:border-transparent'
                    ]"
                    placeholder="Chọn hoặc nhập nơi cấp CCCD"
                    required
                  />
                  <datalist id="id-card-issue-places">
                    <option value="Cục Cảnh sát quản lý hành chính về trật tự xã hội">Cục Cảnh sát quản lý hành chính về trật tự xã hội</option>
                    <option value="Cục Cảnh sát đăng ký quản lý cư trú và dữ liệu Quốc gia về dân cư">Cục Cảnh sát đăng ký quản lý cư trú và dữ liệu Quốc gia về dân cư</option>
                  </datalist>
                  <p v-if="form.errors.id_card_issue_place" class="mt-1 text-sm text-red-600">
                    {{ form.errors.id_card_issue_place }}
                  </p>
                  <p class="mt-1 text-xs text-gray-500">Bạn có thể chọn từ danh sách hoặc nhập tùy chỉnh</p>
                </div>

                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Địa chỉ thường trú <span class="text-red-500">*</span>
                  </label>
                  <textarea
                    v-model="form.permanent_address"
                    rows="3"
                    :class="[
                      'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:outline-none transition-colors',
                      form.errors.permanent_address
                        ? 'border-red-500 focus:ring-red-500 focus:border-red-500'
                        : 'border-gray-300 focus:ring-primary focus:border-transparent'
                    ]"
                    placeholder="Nhập địa chỉ thường trú"
                    required
                  ></textarea>
                  <p v-if="form.errors.permanent_address" class="mt-1 text-sm text-red-600">
                    {{ form.errors.permanent_address }}
                  </p>
                </div>

                <!-- Upload Ảnh CCCD -->
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Ảnh căn cước công dân
                  </label>
                  <div class="space-y-3">
                    <!-- Preview ảnh hiện tại hoặc ảnh mới -->
                    <div v-if="idCardImagePreview || user.id_card_image" class="relative">
                      <div class="relative inline-block">
                        <img
                          :src="idCardImagePreview || user.id_card_image"
                          alt="Ảnh CCCD"
                          class="max-w-md w-full h-auto rounded-lg border-2 border-gray-300 object-contain"
                          style="max-height: 400px;"
                        />
                        <button
                          v-if="idCardImagePreview"
                          type="button"
                          @click="clearIdCardImage"
                          class="absolute top-2 right-2 p-1 bg-red-500 text-white rounded-full hover:bg-red-600 transition-colors"
                          title="Xóa ảnh"
                        >
                          <XMarkIcon class="w-5 h-5" />
                        </button>
                      </div>
                      <p v-if="user.id_card_image && !idCardImagePreview" class="mt-2 text-xs text-gray-500">
                        Ảnh CCCD hiện tại
                      </p>
                      <p v-else-if="idCardImagePreview" class="mt-2 text-xs text-blue-600">
                        Ảnh mới (chưa lưu)
                      </p>
                    </div>

                    <!-- Input file -->
                    <input
                      ref="idCardImageInput"
                      type="file"
                      accept="image/*"
                      @change="handleIdCardImageUpload"
                      :class="[
                        'block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:bg-secondary',
                        form.errors.id_card_image ? 'border-red-500' : ''
                      ]"
                    />
                    <p v-if="form.errors.id_card_image" class="mt-1 text-sm text-red-600">
                      {{ form.errors.id_card_image }}
                    </p>
                    <p class="text-xs text-gray-500">
                      Tải lên ảnh căn cước công dân (JPG, PNG, tối đa 5MB)
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Signature (Only for Managers) -->
          <div v-if="user.role === 'manager'" class="space-y-6 border-t pt-6">
            <div>
              <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <PencilIcon class="w-5 h-5 mr-2 text-primary" />
                Chữ ký
              </h2>
              <p class="text-sm text-gray-600 mb-4">
                Chữ ký của bạn sẽ được hiển thị trên hợp đồng thuê trọ khi bạn là quản lý nhà trọ
              </p>
              <SignaturePad
                v-model="form.signature"
                :has-error="!!form.errors.signature"
                :error-message="form.errors.signature"
                label="Chữ ký của bạn"
                hint="Vẽ chữ ký của bạn bằng chuột hoặc ngón tay. Chữ ký này sẽ được sử dụng trên hợp đồng thuê trọ."
                :required="false"
                :width="600"
                :height="200"
              />
            </div>
          </div>

          <!-- Submit Button -->
          <div class="flex justify-end space-x-4 pt-6 border-t">
            <Link
              href="/"
              class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
            >
              Hủy
            </Link>
            <button
              type="submit"
              :disabled="form.processing"
              class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="!form.processing">Lưu thông tin</span>
              <span v-else>Đang lưu...</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import SignaturePad from '@/components/ui/SignaturePad.vue'
import {
  UserIcon,
  IdentificationIcon,
  PencilIcon,
  XMarkIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  user: {
    type: Object,
    required: true,
  },
})

const today = computed(() => {
  const date = new Date()
  return date.toISOString().split('T')[0]
})

const idCardImageInput = ref(null)
const idCardImagePreview = ref(null)

const form = useForm({
  name: props.user.name || '',
  phone: props.user.phone || '',
  id_card_number: props.user.id_card_number || '',
  id_card_issue_date: props.user.id_card_issue_date || '',
  id_card_issue_place: props.user.id_card_issue_place || '',
  permanent_address: props.user.permanent_address || '',
  date_of_birth: props.user.date_of_birth || '',
  gender: props.user.gender || '',
  signature: props.user.signature || '',
  id_card_image: null,
}, {
  forceFormData: true,
})

// Xử lý upload ảnh CCCD
const handleIdCardImageUpload = async (event) => {
  const file = event.target.files[0]
  if (!file) return
  
  // Kiểm tra loại file
  if (!file.type.startsWith('image/')) {
    return
  }
  
  // Kiểm tra kích thước file (5MB)
  if (file.size > 5 * 1024 * 1024) {
    alert('Kích thước ảnh không được vượt quá 5MB')
    if (idCardImageInput.value) {
      idCardImageInput.value.value = ''
    }
    return
  }
  
  // Hiển thị preview
  const reader = new FileReader()
  reader.onload = (e) => {
    idCardImagePreview.value = e.target.result
  }
  reader.readAsDataURL(file)
  
  // Lưu file vào form
  form.id_card_image = file
}

// Xóa ảnh preview
const clearIdCardImage = () => {
  idCardImagePreview.value = null
  form.id_card_image = null
  if (idCardImageInput.value) {
    idCardImageInput.value.value = ''
  }
}

const handleSubmit = () => {
  form.post('/profile/personal-info', {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => {
      // Reset preview sau khi submit thành công
      idCardImagePreview.value = null
      if (idCardImageInput.value) {
        idCardImageInput.value.value = ''
      }
      // Reload to get updated user data
      window.location.reload()
    },
    onError: (errors) => {
      console.error('Form errors:', errors)
    },
  })
}
</script>
