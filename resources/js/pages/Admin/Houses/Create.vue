<template>
  <Head title="Thêm nhà trọ mới" />
  <AdminLayout title="Thêm nhà trọ mới">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <Link
            href="/admin/houses"
            class="text-primary hover:text-primary-600 mb-2 inline-flex items-center text-sm"
          >
            <ChevronLeftIcon class="h-4 w-4 mr-1" />
            Quay lại danh sách
          </Link>
          <h1 class="text-2xl font-bold text-gray-900">Thêm nhà trọ mới</h1>
        </div>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleSubmit" class="bg-white rounded-lg shadow-sm p-6 space-y-6">
        <!-- Basic Information -->
        <div>
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Thông tin cơ bản</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Name -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Tên nhà trọ <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.name"
                type="text"
                required
                :class="[
                  'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:outline-none transition-colors',
                  errors.name
                    ? 'border-red-500 focus:ring-red-500 focus:border-red-500'
                    : 'border-gray-300 focus:ring-primary focus:border-transparent'
                ]"
                placeholder="Nhập tên nhà trọ"
                @blur="validateField('name')"
              />
              <p v-if="errors.name" class="mt-1 text-sm text-red-600 flex items-center gap-1">
                <ExclamationCircleIcon class="h-4 w-4" />
                {{ errors.name }}
              </p>
            </div>

            <!-- Street Detail -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Địa chỉ cụ thể <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.street_detail"
                type="text"
                required
                :class="[
                  'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:outline-none transition-colors',
                  errors.street_detail
                    ? 'border-red-500 focus:ring-red-500 focus:border-red-500'
                    : 'border-gray-300 focus:ring-primary focus:border-transparent'
                ]"
                placeholder="Số nhà, ngõ ngách..."
                @blur="validateField('street_detail')"
              />
              <p v-if="errors.street_detail" class="mt-1 text-sm text-red-600 flex items-center gap-1">
                <ExclamationCircleIcon class="h-4 w-4" />
                {{ errors.street_detail }}
              </p>
            </div>

            <!-- Street -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Đường <span class="text-red-500">*</span>
              </label>
              <SelectSearchable
                v-model="form.street_id"
                :options="streets"
                option-value="id"
                :option-label="(street) => street.parent ? `${street.name} (${street.parent.name})` : street.name"
                :filter-by="(street) => street.parent ? `${street.name} ${street.parent.name}` : street.name"
                placeholder="Tìm kiếm và chọn đường..."
                :error="errors.street_id"
                @update:modelValue="validateField('street_id')"
              />
            </div>
          </div>

          <!-- Description -->
          <div class="mt-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Mô tả</label>
            <textarea
              v-model="form.description"
              rows="4"
              :class="[
                'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:outline-none transition-colors',
                errors.description
                  ? 'border-red-500 focus:ring-red-500 focus:border-red-500'
                  : 'border-gray-300 focus:ring-primary focus:border-transparent'
              ]"
              placeholder="Nhập mô tả về nhà trọ..."
              @blur="validateField('description')"
            ></textarea>
            <p v-if="errors.description" class="mt-1 text-sm text-red-600 flex items-center gap-1">
              <ExclamationCircleIcon class="h-4 w-4" />
              {{ errors.description }}
            </p>
          </div>
        </div>

        <!-- Location -->
        <div>
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Vị trí</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Latitude -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Vĩ độ</label>
              <input
                v-model="form.latitude"
                type="number"
                step="any"
                :class="[
                  'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:outline-none transition-colors',
                  errors.latitude
                    ? 'border-red-500 focus:ring-red-500 focus:border-red-500'
                    : 'border-gray-300 focus:ring-primary focus:border-transparent'
                ]"
                placeholder="21.0285"
                @blur="validateField('latitude')"
              />
              <p v-if="errors.latitude" class="mt-1 text-sm text-red-600 flex items-center gap-1">
                <ExclamationCircleIcon class="h-4 w-4" />
                {{ errors.latitude }}
              </p>
            </div>

            <!-- Longitude -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Kinh độ</label>
              <input
                v-model="form.longitude"
                type="number"
                step="any"
                :class="[
                  'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:outline-none transition-colors',
                  errors.longitude
                    ? 'border-red-500 focus:ring-red-500 focus:border-red-500'
                    : 'border-gray-300 focus:ring-primary focus:border-transparent'
                ]"
                placeholder="105.8542"
                @blur="validateField('longitude')"
              />
              <p v-if="errors.longitude" class="mt-1 text-sm text-red-600 flex items-center gap-1">
                <ExclamationCircleIcon class="h-4 w-4" />
                {{ errors.longitude }}
              </p>
            </div>
          </div>
        </div>

        <!-- Pricing & Details -->
        <div>
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Giá và thông tin</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Price per day -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Giá thuê/ngày (VNĐ) <span class="text-red-500">*</span>
              </label>
              <div class="flex items-center gap-2">
                <input
                  v-model.number="form.price_per_day"
                  type="number"
                  step="1000"
                  min="0"
                  required
                  :class="[
                    'flex-1 px-4 py-2 border rounded-lg focus:ring-2 focus:outline-none transition-colors',
                    errors.price_per_day
                      ? 'border-red-500 focus:ring-red-500 focus:border-red-500'
                      : 'border-gray-300 focus:ring-primary focus:border-transparent'
                  ]"
                  placeholder="500000"
                  @blur="validateField('price_per_day')"
                />
                <span class="text-xs text-gray-500 font-medium whitespace-nowrap">
                  {{ formatPriceDisplay(form.price_per_day) }}
                </span>
              </div>
              <p v-if="errors.price_per_day" class="mt-1 text-sm text-red-600 flex items-center gap-1">
                <ExclamationCircleIcon class="h-4 w-4" />
                {{ errors.price_per_day }}
              </p>
            </div>
          </div>
        </div>

        <!-- Amenities -->
        <div>
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Tiện nghi</h2>
          <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <label
              v-for="(label, key) in amenityOptions"
              :key="key"
              class="flex items-center space-x-2 cursor-pointer p-3 border border-gray-200 rounded-lg hover:bg-gray-50"
            >
              <input
                v-model="form.amenities"
                type="checkbox"
                :value="key"
                class="rounded border-gray-300 text-primary focus:ring-primary"
              />
              <span class="text-sm text-gray-700">{{ label }}</span>
            </label>
          </div>
          <p v-if="errors.amenities" class="mt-1 text-sm text-red-600">{{ errors.amenities }}</p>
        </div>

        <!-- Images -->
        <div>
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Hình ảnh</h2>
          <div class="space-y-4">
            <!-- Drop Zone -->
            <div
              ref="dropZoneRef"
              :class="[
                'border-2 border-dashed rounded-lg p-8 text-center transition-colors cursor-pointer',
                isOverDropZone
                  ? 'border-primary bg-primary/5'
                  : 'border-gray-300 hover:border-gray-400',
              ]"
              @click="fileInput?.click()"
            >
              <input
                ref="fileInput"
                type="file"
                multiple
                accept="image/*"
                @change="handleImageChange"
                class="hidden"
              />
              <div class="space-y-4">
                <div class="flex flex-col items-center">
                  <PhotoIcon class="h-12 w-12 text-gray-400 mb-2" />
                  <p class="text-sm font-medium text-gray-700">
                    Kéo thả ảnh vào đây hoặc
                    <span class="text-primary hover:text-primary-600 font-medium underline">
                      chọn từ máy tính
                    </span>
                  </p>
                  <p class="mt-1 text-xs text-gray-500">
                    Tối đa 10 ảnh, mỗi ảnh tối đa 5MB
                  </p>
                  <p class="mt-1 text-xs text-gray-500">
                    Định dạng: JPEG, PNG, JPG, GIF, WEBP
                  </p>
                </div>
              </div>
            </div>
            <p v-if="errors.images" class="text-sm text-red-600">{{ errors.images }}</p>

            <!-- Preview Images -->
            <div v-if="imagePreviews.length > 0" class="space-y-4">
              <p class="text-sm text-gray-600">
                <span class="font-medium">Lưu ý:</span> Chọn các ảnh sẽ hiển thị trong trang chi tiết nhà trọ
              </p>
              <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div
                  v-for="(preview, index) in imagePreviews"
                  :key="index"
                  class="relative group"
                >
                  <div class="relative">
                    <img
                      :src="preview"
                      :alt="`Preview ${index + 1}`"
                      :class="[
                        'w-full h-32 object-cover rounded-lg border-2 transition-all cursor-pointer',
                        featuredImages.includes(index)
                          ? 'border-primary ring-2 ring-primary ring-offset-2'
                          : 'border-gray-200 hover:border-gray-300'
                      ]"
                      @click="toggleFeaturedImage(index)"
                    />
                    <button
                      type="button"
                      @click.stop="removeImage(index)"
                      class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity z-10"
                    >
                      <XMarkIcon class="h-4 w-4" />
                    </button>
                    <!-- Checkbox overlay -->
                    <div
                      class="absolute top-2 left-2 rounded-full p-1 shadow-lg transition-all duration-200"
                      :class="featuredImages.includes(index) 
                        ? 'bg-primary text-white ring-2 ring-primary ring-offset-2' 
                        : 'bg-white text-gray-400 hover:bg-gray-50'"
                    >
                      <svg
                        v-if="featuredImages.includes(index)"
                        class="w-4 h-4"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                      >
                        <path
                          fill-rule="evenodd"
                          d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                          clip-rule="evenodd"
                        />
                      </svg>
                      <svg
                        v-else
                        class="w-4 h-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M5 13l4 4L19 7"
                        />
                      </svg>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Error Message -->
        <div v-if="errorMessage" class="p-4 bg-red-50 border border-red-200 rounded-lg">
          <p class="text-sm text-red-600">{{ errorMessage }}</p>
        </div>

        <!-- Success Message -->
        <div v-if="successMessage" class="p-4 bg-green-50 border border-green-200 rounded-lg">
          <p class="text-sm text-green-600">{{ successMessage }}</p>
        </div>

        <!-- Submit Buttons -->
        <div class="flex justify-end gap-3 pt-4 border-t">
          <Link
            href="/admin/houses"
            class="px-6 py-2 border-2 border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
          >
            Hủy
          </Link>
          <button
            type="submit"
            :disabled="isSubmitting"
            class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            {{ isSubmitting ? 'Đang lưu...' : 'Thêm nhà trọ' }}
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, router, Link } from '@inertiajs/vue3'
import { useDropZone } from '@vueuse/core'
import AdminLayout from '@/layouts/AdminLayout.vue'
import SelectSearchable from '@/components/ui/SelectSearchable.vue'
import { ChevronLeftIcon, XMarkIcon, PhotoIcon, ExclamationCircleIcon } from '@heroicons/vue/24/outline'
import { amenityNameMap } from '@/utils/amenityIcons'

const amenityOptions = {
  Wifi: 'Wifi',
  AirConditioning: 'Điều hòa',
  HotWater: 'Nóng lạnh',
  PrivateKitchen: 'Bếp riêng',
  SharedKitchen: 'Bếp chung',
  SharedBathroom: 'Vệ sinh chung',
  PrivateBathroom: 'Vệ sinh khép kín',
  Bed: 'Giường',
  Wardrobe: 'Tủ',
  Refrigerator: 'Tủ lạnh',
  Balcony: 'Ban công',
  SharedWashingMachine: 'Máy giặt chung',
  PrivateWashingMachine: 'Máy giặt riêng',
  SharedDryer: 'Máy sấy quần áo chung',
  PrivateDryer: 'Máy sấy quần áo riêng',
}

const props = defineProps({
  streets: Array,
  errors: Object,
})

const form = ref({
  name: '',
  street_detail: '',
  street_id: '',
  description: '',
  amenities: [],
  price_per_day: '',
  latitude: '',
  longitude: '',
})

const fileInput = ref(null)
const dropZoneRef = ref(null)
const imagePreviews = ref([])
const selectedImages = ref([])
const featuredImages = ref([]) // Indices of selected featured images
const isSubmitting = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const errors = ref({})

// Format price for display
const formatPrice = (price) => {
  if (!price) return '0'
  return new Intl.NumberFormat('vi-VN').format(price)
}

const formatPriceDisplay = (price) => {
  if (!price || price === 0) return ''
  return formatPrice(price) + ' đ'
}

// Drop zone functionality
const { isOverDropZone } = useDropZone(dropZoneRef, {
  onDrop: (files) => {
    if (files && files.length > 0) {
      handleFiles(Array.from(files))
    }
  },
})

const handleFiles = (files) => {
  errorMessage.value = ''
  
  // Limit to 10 images
  if (selectedImages.value.length + files.length > 10) {
    errorMessage.value = 'Tối đa 10 ảnh'
    return
  }

  files.forEach((file) => {
    // Validate file size (5MB)
    if (file.size > 5 * 1024 * 1024) {
      errorMessage.value = `Ảnh ${file.name} vượt quá 5MB`
      return
    }

    // Validate file type
    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp']
    if (!allowedTypes.includes(file.type)) {
      errorMessage.value = `File ${file.name} không phải là ảnh hợp lệ (JPEG, PNG, JPG, GIF, WEBP)`
      return
    }

    selectedImages.value.push(file)
    
    // Create preview
    const reader = new FileReader()
    reader.onload = (e) => {
      imagePreviews.value.push(e.target.result)
    }
    reader.readAsDataURL(file)
  })
}

const handleImageChange = (event) => {
  const files = Array.from(event.target.files)
  handleFiles(files)

  // Reset input
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const removeImage = (index) => {
  selectedImages.value.splice(index, 1)
  imagePreviews.value.splice(index, 1)
  // Remove from featured images and adjust indices
  const featuredIndex = featuredImages.value.indexOf(index)
  if (featuredIndex > -1) {
    featuredImages.value.splice(featuredIndex, 1)
  }
  // Adjust indices of featured images after the removed one
  featuredImages.value = featuredImages.value.map(idx => idx > index ? idx - 1 : idx)
}

const toggleFeaturedImage = (index) => {
  const featuredIndex = featuredImages.value.indexOf(index)
  if (featuredIndex > -1) {
    featuredImages.value.splice(featuredIndex, 1)
  } else {
    featuredImages.value.push(index)
  }
}

const validateField = (fieldName) => {
  // Clear error for this field when user starts typing
  if (errors.value[fieldName]) {
    delete errors.value[fieldName]
  }
}

const validateForm = () => {
  const newErrors = {}
  
  // Required fields
  if (!form.value.name?.trim()) {
    newErrors.name = 'Vui lòng nhập tên nhà trọ'
  }
  
  if (!form.value.street_detail?.trim()) {
    newErrors.street_detail = 'Vui lòng nhập địa chỉ cụ thể'
  }
  
  if (!form.value.street_id) {
    newErrors.street_id = 'Vui lòng chọn đường'
  }
  
  if (!form.value.price_per_day || form.value.price_per_day <= 0) {
    newErrors.price_per_day = 'Vui lòng nhập giá thuê hợp lệ'
  }
  
  // Optional field validations
  if (form.value.latitude && (form.value.latitude < -90 || form.value.latitude > 90)) {
    newErrors.latitude = 'Vĩ độ phải trong khoảng -90 đến 90'
  }
  
  if (form.value.longitude && (form.value.longitude < -180 || form.value.longitude > 180)) {
    newErrors.longitude = 'Kinh độ phải trong khoảng -180 đến 180'
  }
  
  
  errors.value = newErrors
  return Object.keys(newErrors).length === 0
}

const handleSubmit = () => {
  errorMessage.value = ''
  successMessage.value = ''
  
  // Validate form
  if (!validateForm()) {
    errorMessage.value = 'Vui lòng kiểm tra lại các trường bắt buộc'
    return
  }
  
  isSubmitting.value = true

  // Create FormData for file upload
  const formData = new FormData()
  formData.append('name', form.value.name)
  formData.append('street_detail', form.value.street_detail)
  formData.append('street_id', form.value.street_id)
  if (form.value.description) {
    formData.append('description', form.value.description)
  }
  formData.append('price_per_day', form.value.price_per_day)
  if (form.value.latitude) {
    formData.append('latitude', form.value.latitude)
  }
  if (form.value.longitude) {
    formData.append('longitude', form.value.longitude)
  }

  // Append amenities
  form.value.amenities.forEach((amenity) => {
    formData.append('amenities[]', amenity)
  })

  // Append images
  selectedImages.value.forEach((image) => {
    formData.append('images[]', image)
  })

  // Append featured images (image URLs after upload, we'll send indices for now)
  // The backend will map indices to actual image URLs
  featuredImages.value.forEach((index) => {
    // We'll send the index, backend will map to actual image after upload
    formData.append('featured_image_indices[]', index)
  })

  router.post('/admin/houses', formData, {
    preserveScroll: true,
    onSuccess: () => {
      successMessage.value = 'Đã thêm nhà trọ thành công!'
      // Reset form
      form.value = {
        name: '',
        street_detail: '',
        street_id: '',
        description: '',
        amenities: [],
        price_per_day: '',
        latitude: '',
        longitude: '',
      }
      selectedImages.value = []
      imagePreviews.value = []
      featuredImages.value = []
    },
    onError: (pageErrors) => {
      errors.value = pageErrors
      if (pageErrors.message) {
        errorMessage.value = pageErrors.message
      }
    },
    onFinish: () => {
      isSubmitting.value = false
    },
  })
}
</script>
