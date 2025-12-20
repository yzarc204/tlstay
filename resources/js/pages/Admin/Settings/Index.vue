<template>
  <Head title="Cài đặt website" />
  <AdminLayout title="Cài đặt website">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Cài đặt website</h1>
          <p class="text-gray-600 mt-1">Quản lý thông tin cơ bản của website</p>
        </div>
      </div>

      <!-- Flash Messages -->
      <div v-if="$page.props.flash?.success" class="p-4 bg-green-50 border border-green-200 rounded-lg">
        <p class="text-green-800 whitespace-pre-line">{{ $page.props.flash.success }}</p>
      </div>
      <div v-if="$page.props.flash?.error" class="p-4 bg-red-50 border border-red-200 rounded-lg">
        <p class="text-red-800 whitespace-pre-line">{{ $page.props.flash.error }}</p>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleSubmit" enctype="multipart/form-data" class="bg-white rounded-lg shadow-sm p-6 space-y-8">
        <!-- General Settings -->
        <div v-if="settings && settings.general" class="space-y-6">
          <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
              <Cog6ToothIcon class="w-5 h-5 mr-2 text-primary" />
              Thông tin chung
            </h2>
            <div class="space-y-6">
              <!-- Logo Upload Section -->
              <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">
                  Logo website
                  <span class="text-xs text-gray-500 font-normal block mt-1">
                    Logo của website. Nếu không có logo, sẽ hiển thị tên website dạng text.
                  </span>
                </label>
                <div class="space-y-4">
                  <!-- Current Logo Preview -->
                  <div v-if="logoPreview || currentLogo" class="flex items-center space-x-4">
                    <div class="relative">
                      <img
                        :src="logoPreview || currentLogo"
                        alt="Logo preview"
                        class="h-20 w-auto object-contain border border-gray-300 rounded-lg p-2 bg-white"
                      />
                    </div>
                    <div class="flex-1">
                      <p class="text-sm text-gray-600">Logo hiện tại</p>
                      <button
                        type="button"
                        @click="deleteLogo"
                        class="mt-2 text-sm text-red-600 hover:text-red-700"
                      >
                        Xóa logo
                      </button>
                    </div>
                  </div>
                  
                  <!-- Logo Upload Input -->
                  <div>
                    <input
                      :ref="(el) => { if (el) logoInput = el }"
                      type="file"
                      accept="image/*"
                      @change="handleLogoChange"
                      class="hidden"
                    />
                    <button
                      type="button"
                      @click="() => logoInput && logoInput.click()"
                      class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors flex items-center space-x-2"
                    >
                      <PhotoIcon class="w-5 h-5" />
                      <span>{{ logoPreview || currentLogo ? 'Thay đổi logo' : 'Chọn logo' }}</span>
                    </button>
                    <p v-if="form.errors.logo" class="text-sm text-red-600 mt-1">
                      {{ form.errors.logo }}
                    </p>
                  </div>
                  
                  <!-- Show text with logo option -->
                  <div v-if="logoPreview || currentLogo" class="flex items-center space-x-2 pt-2 border-t">
                    <input
                      id="logo-show-text"
                      v-model="logoShowText"
                      type="checkbox"
                      class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary"
                    />
                    <label for="logo-show-text" class="text-sm text-gray-700 cursor-pointer">
                      Hiển thị tên website cùng logo
                    </label>
                  </div>
                </div>
              </div>
              
              <template
                v-for="setting in (settings.general || [])"
                :key="setting.id"
              >
                <div
                  v-if="setting && setting.type && setting.type !== 'file'"
                  class="space-y-2"
                >
                  <label class="block text-sm font-medium text-gray-700">
                    {{ setting.label }}
                    <span v-if="setting.description" class="text-xs text-gray-500 font-normal block mt-1">
                      {{ setting.description }}
                    </span>
                  </label>
                  <input
                    v-if="setting.type === 'text'"
                    v-model="formData.settings[setting.key]"
                    type="text"
                    :class="[
                      'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:outline-none transition-colors',
                      form.errors[`settings.${setting.key}.value`]
                        ? 'border-red-500 focus:ring-red-500 focus:border-red-500'
                        : 'border-gray-300 focus:ring-primary focus:border-transparent'
                    ]"
                    :placeholder="`Nhập ${setting.label.toLowerCase()}`"
                  />
                  <textarea
                    v-else-if="setting.type === 'textarea'"
                    v-model="formData.settings[setting.key]"
                    rows="3"
                    :class="[
                      'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:outline-none transition-colors',
                      form.errors[`settings.${setting.key}.value`]
                        ? 'border-red-500 focus:ring-red-500 focus:border-red-500'
                        : 'border-gray-300 focus:ring-primary focus:border-transparent'
                    ]"
                    :placeholder="`Nhập ${setting.label.toLowerCase()}`"
                  ></textarea>
                  <input
                    v-else-if="setting.type === 'email'"
                    v-model="formData.settings[setting.key]"
                    type="email"
                    :class="[
                      'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:outline-none transition-colors',
                      form.errors[`settings.${setting.key}.value`]
                        ? 'border-red-500 focus:ring-red-500 focus:border-red-500'
                        : 'border-gray-300 focus:ring-primary focus:border-transparent'
                    ]"
                    :placeholder="`Nhập ${setting.label.toLowerCase()}`"
                  />
                  <p
                    v-if="form.errors[`settings.${setting.key}.value`]"
                    class="text-sm text-red-600"
                  >
                    {{ form.errors[`settings.${setting.key}.value`] }}
                  </p>
                </div>
              </template>
            </div>
          </div>
        </div>

        <!-- Contact Settings -->
        <div v-if="settings && settings.contact" class="space-y-6 border-t pt-6">
          <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
              <PhoneIcon class="w-5 h-5 mr-2 text-primary" />
              Thông tin liên hệ
            </h2>
            <div class="space-y-6">
              <template
                v-for="setting in (settings.contact || [])"
                :key="setting.id"
              >
                <div
                  v-if="setting && setting.type"
                  class="space-y-2"
                >
                <label class="block text-sm font-medium text-gray-700">
                  {{ setting.label }}
                  <span v-if="setting.description" class="text-xs text-gray-500 font-normal block mt-1">
                    {{ setting.description }}
                  </span>
                </label>
                <input
                  v-if="setting.type === 'text'"
                  v-model="formData.settings[setting.key]"
                  type="text"
                  :class="[
                    'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:outline-none transition-colors',
                    form.errors[`settings.${setting.key}.value`]
                      ? 'border-red-500 focus:ring-red-500 focus:border-red-500'
                      : 'border-gray-300 focus:ring-primary focus:border-transparent'
                  ]"
                  :placeholder="`Nhập ${setting.label.toLowerCase()}`"
                />
                <input
                  v-else-if="setting.type === 'email'"
                  v-model="formData.settings[setting.key]"
                  type="email"
                  :class="[
                    'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:outline-none transition-colors',
                    form.errors[`settings.${setting.key}.value`]
                      ? 'border-red-500 focus:ring-red-500 focus:border-red-500'
                      : 'border-gray-300 focus:ring-primary focus:border-transparent'
                  ]"
                  :placeholder="`Nhập ${setting.label.toLowerCase()}`"
                />
                  <p
                    v-if="form.errors[`settings.${setting.key}.value`]"
                    class="text-sm text-red-600"
                  >
                    {{ form.errors[`settings.${setting.key}.value`] }}
                  </p>
                </div>
              </template>
            </div>
          </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end space-x-4 pt-6 border-t">
          <Link
            href="/admin"
            class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
          >
            Hủy
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="!form.processing">Lưu cài đặt</span>
            <span v-else>Đang lưu...</span>
          </button>
        </div>
      </form>

      <!-- Social Links Section -->
      <div class="bg-white rounded-lg shadow-sm p-6 space-y-6">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-1 flex items-center">
              <ShareIcon class="w-5 h-5 mr-2 text-primary" />
              Link mạng xã hội
            </h2>
            <p class="text-sm text-gray-600">Quản lý các link mạng xã hội hiển thị trên website</p>
          </div>
          <button
            @click="showAddForm = !showAddForm"
            class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-700 transition-colors flex items-center"
          >
            <PlusIcon class="w-5 h-5 mr-2" />
            Thêm link
          </button>
        </div>

        <!-- Add/Edit Form -->
        <div v-if="showAddForm || editingLink" class="border rounded-lg p-4 bg-gray-50">
          <h3 class="font-semibold text-gray-900 mb-4">
            {{ editingLink ? 'Chỉnh sửa link' : 'Thêm link mới' }}
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Tên mạng xã hội <span class="text-red-500">*</span>
              </label>
              <input
                v-model="socialLinkForm.name"
                type="text"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                placeholder="Ví dụ: Facebook"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Icon <span class="text-red-500">*</span>
              </label>
              <select
                v-model="socialLinkForm.icon"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
              >
                <option value="">Chọn icon</option>
                <option value="facebook">Facebook</option>
                <option value="twitter">Twitter</option>
                <option value="instagram">Instagram</option>
                <option value="youtube">YouTube</option>
                <option value="linkedin">LinkedIn</option>
                <option value="tiktok">TikTok</option>
                <option value="zalo">Zalo</option>
                <option value="telegram">Telegram</option>
              </select>
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                URL <span class="text-red-500">*</span>
              </label>
              <input
                v-model="socialLinkForm.url"
                type="url"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                placeholder="https://facebook.com/yourpage"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Thứ tự hiển thị
              </label>
              <input
                v-model.number="socialLinkForm.order"
                type="number"
                min="0"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                placeholder="0"
              />
            </div>
            <div class="flex items-end">
              <label class="flex items-center">
                <input
                  v-model="socialLinkForm.is_active"
                  type="checkbox"
                  class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary"
                />
                <span class="ml-2 text-sm text-gray-700">Kích hoạt</span>
              </label>
            </div>
          </div>
          <div class="flex justify-end space-x-3 mt-4">
            <button
              @click="cancelSocialLinkForm"
              class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
            >
              Hủy
            </button>
            <button
              @click="saveSocialLink"
              :disabled="socialLinkForm.processing"
              class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-700 transition-colors disabled:opacity-50"
            >
              {{ editingLink ? 'Cập nhật' : 'Thêm' }}
            </button>
          </div>
        </div>

        <!-- Social Links List -->
        <div v-if="socialLinks.length > 0" class="space-y-3">
          <div
            v-for="link in socialLinks"
            :key="link.id"
            class="flex items-center justify-between p-4 border rounded-lg hover:bg-gray-50 transition-colors"
          >
            <div class="flex items-center space-x-4 flex-1">
              <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center">
                <span class="text-sm font-semibold text-gray-600 uppercase">
                  {{ getIconInitial(link.icon) }}
                </span>
              </div>
              <div class="flex-1">
                <div class="flex items-center space-x-2">
                  <span class="font-medium text-gray-900">{{ link.name }}</span>
                  <span
                    :class="[
                      'px-2 py-1 text-xs rounded-full',
                      link.is_active
                        ? 'bg-green-100 text-green-800'
                        : 'bg-gray-100 text-gray-600'
                    ]"
                  >
                    {{ link.is_active ? 'Hoạt động' : 'Tắt' }}
                  </span>
                </div>
                <p class="text-sm text-gray-600 mt-1">{{ link.url }}</p>
              </div>
              <div class="text-sm text-gray-500">
                Thứ tự: {{ link.order }}
              </div>
            </div>
            <div class="flex items-center space-x-2 ml-4">
              <button
                @click="editSocialLink(link)"
                class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                title="Chỉnh sửa"
              >
                <PencilIcon class="w-5 h-5" />
              </button>
              <button
                @click="deleteSocialLink(link.id)"
                class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                title="Xóa"
              >
                <TrashIcon class="w-5 h-5" />
              </button>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-8 text-gray-500">
          <ShareIcon class="w-12 h-12 mx-auto mb-2 text-gray-400" />
          <p>Chưa có link mạng xã hội nào</p>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue'
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import {
  Cog6ToothIcon,
  PhoneIcon,
  ShareIcon,
  PlusIcon,
  PencilIcon,
  TrashIcon,
  PhotoIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  settings: {
    type: Object,
    required: true,
  },
  socialLinks: {
    type: Array,
    default: () => [],
  },
})

// Prepare form data
const formData = reactive({
  settings: {},
})

// Logo state
const logoPreview = ref(null)
const currentLogo = ref(null)
const logoFile = ref(null)
const logoShowText = ref(true) // Default: show text with logo

// Initialize form data from props
onMounted(() => {
  if (props.settings && typeof props.settings === 'object') {
    Object.keys(props.settings).forEach((group) => {
      if (props.settings[group] && Array.isArray(props.settings[group])) {
        props.settings[group].forEach((setting) => {
          if (setting && setting.key) {
            formData.settings[setting.key] = setting.value || ''
            if (setting.key === 'site_logo' && setting.value) {
              currentLogo.value = setting.value
            }
            if (setting.key === 'site_logo_show_text') {
              logoShowText.value = setting.value === '1' || setting.value === 'true' || setting.value === true || setting.value === 1
            }
          }
        })
      }
    })
  }
  
  // If logo_show_text setting doesn't exist, default to true
  if (logoShowText.value === undefined) {
    logoShowText.value = true
  }
})

// Create Inertia form
const form = useForm({
  settings: [],
  logo: null,
  delete_logo: false,
})

// Logo input ref
const logoInput = ref(null)

// Handle logo file change
const handleLogoChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    logoFile.value = file
    form.delete_logo = false // Reset delete flag when new logo is selected
    // Create preview
    const reader = new FileReader()
    reader.onload = (e) => {
      logoPreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

// Delete logo
const deleteLogo = () => {
  logoPreview.value = null
  logoFile.value = null
  currentLogo.value = null
  form.delete_logo = true
  if (logoInput.value) {
    logoInput.value.value = ''
  }
}

// Handle form submit
const handleSubmit = () => {
  // Convert formData.settings to array format expected by backend
  const settingsArray = Object.keys(formData.settings).map((key) => ({
    key: key,
    value: formData.settings[key] || '',
  }))
  
  // Add logo_show_text setting
  settingsArray.push({
    key: 'site_logo_show_text',
    value: logoShowText.value ? '1' : '0',
  })

  form.settings = settingsArray
  form.logo = logoFile.value
  // If delete_logo is true, it's already set

  // Use POST for file uploads (route accepts both PUT and POST)
  form.post('/admin/settings', {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: (page) => {
      // Reset logo preview and file
      logoPreview.value = null
      logoFile.value = null
      
      // Update currentLogo from response if available
      if (page.props.settings) {
        const logoSetting = Object.values(page.props.settings)
          .flat()
          .find(s => s && s.key === 'site_logo')
        if (logoSetting) {
          currentLogo.value = logoSetting.value || null
        } else {
          currentLogo.value = null
        }
        
        // Update logoShowText from response
        const logoShowTextSetting = Object.values(page.props.settings)
          .flat()
          .find(s => s && s.key === 'site_logo_show_text')
        if (logoShowTextSetting) {
          logoShowText.value = logoShowTextSetting.value === '1' || logoShowTextSetting.value === 'true'
        }
      }
      
      // Reload to get updated settings
      router.reload({ only: ['settings'] })
    },
  })
}

// Social Links Management
const showAddForm = ref(false)
const editingLink = ref(null)
const socialLinks = ref([...props.socialLinks])

const socialLinkForm = useForm({
  name: '',
  icon: '',
  url: '',
  order: 0,
  is_active: true,
})

const getIconInitial = (icon) => {
  if (!icon) return '?'
  return icon.charAt(0).toUpperCase()
}

const editSocialLink = (link) => {
  editingLink.value = link
  socialLinkForm.name = link.name
  socialLinkForm.icon = link.icon
  socialLinkForm.url = link.url
  socialLinkForm.order = link.order
  socialLinkForm.is_active = link.is_active
  showAddForm.value = false
}

const cancelSocialLinkForm = () => {
  showAddForm.value = false
  editingLink.value = null
  socialLinkForm.reset()
  socialLinkForm.clearErrors()
}

const saveSocialLink = () => {
  if (editingLink.value) {
    // Update existing link
    socialLinkForm.put(`/admin/settings/social-links/${editingLink.value.id}`, {
      preserveScroll: true,
      onSuccess: () => {
        router.reload({ only: ['socialLinks'] })
        cancelSocialLinkForm()
      },
    })
  } else {
    // Create new link
    socialLinkForm.post('/admin/settings/social-links', {
      preserveScroll: true,
      onSuccess: () => {
        router.reload({ only: ['socialLinks'] })
        cancelSocialLinkForm()
      },
    })
  }
}

const deleteSocialLink = (id) => {
  if (confirm('Bạn có chắc chắn muốn xóa link này?')) {
    router.delete(`/admin/settings/social-links/${id}`, {
      preserveScroll: true,
      onSuccess: () => {
        router.reload({ only: ['socialLinks'] })
      },
    })
  }
}

// Watch for changes in props.socialLinks
watch(() => props.socialLinks, (newLinks) => {
  socialLinks.value = [...newLinks]
}, { deep: true })
</script>
