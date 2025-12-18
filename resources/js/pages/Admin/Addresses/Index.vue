<template>
  <AdminLayout title="Quản lý Địa điểm">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Quản lý Địa điểm</h1>
          <p class="text-gray-600 mt-1">Quản lý danh sách phường/xã và đường</p>
        </div>
        <Button @click="showCreateModal = true">
          <PlusIcon class="h-5 w-5 mr-2" />
          Thêm địa điểm
        </Button>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow-sm p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Search -->
          <div>
            <input
              v-model="search"
              type="text"
              placeholder="Tìm kiếm theo tên địa điểm..."
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
              @input="handleSearch"
            />
          </div>

          <!-- Ward Filter -->
          <div v-if="parentAddresses.length > 0">
            <select
              v-model="parentFilter"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
              @change="handleFilter"
            >
              <option value="">Tất cả phường/xã</option>
              <option v-for="parent in parentAddresses" :key="parent.id" :value="parent.id">
                {{ parent.name }}
              </option>
            </select>
          </div>
        </div>
      </div>

      <!-- Addresses Tree -->
      <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="divide-y divide-gray-200">
          <div
            v-for="ward in wards"
            :key="ward.id"
            class="transition-colors hover:bg-gray-50"
          >
            <!-- Ward Row -->
            <div class="px-6 py-4 flex items-center justify-between">
              <div class="flex items-center flex-1 min-w-0">
                <!-- Expand/Collapse Button -->
                <button
                  @click="toggleWard(ward.id)"
                  class="mr-3 flex-shrink-0 text-gray-400 hover:text-gray-600 transition-colors"
                >
                  <ChevronRightIcon
                    :class="[
                      'h-5 w-5 transition-transform duration-200',
                      expandedWards[ward.id] ? 'transform rotate-90' : ''
                    ]"
                  />
                </button>

                <!-- Ward Info -->
                <div class="flex items-center flex-1 min-w-0">
                  <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 mr-3">
                    Phường/Xã
                  </span>
                  <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium text-gray-900">{{ ward.name }}</div>
                    <div class="text-xs text-gray-500 mt-0.5">
                      {{ ward.children_count || 0 }} đường
                    </div>
                  </div>
                </div>

                <!-- Ward Details -->
                <div class="flex items-center gap-4 ml-4">
                </div>

                <!-- Ward Actions -->
                <div class="flex items-center gap-2 ml-4">
                  <div class="relative group">
                    <button
                      @click="showQuickAddStreet(ward)"
                      class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-gray-600 hover:bg-primary/10 hover:text-primary transition-colors"
                      title="Thêm đường"
                    >
                      <PlusIcon class="h-5 w-5" />
                    </button>
                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs font-medium text-white bg-gray-900 rounded shadow-lg whitespace-nowrap opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 pointer-events-none z-50">
                      Thêm đường
                      <div class="absolute top-full left-1/2 -translate-x-1/2 -mt-1 border-4 border-transparent border-t-gray-900"></div>
                    </div>
                  </div>
                  <div class="relative group">
                    <button
                      @click="showEditModal(ward)"
                      class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-gray-600 hover:bg-gray-100 hover:text-primary transition-colors"
                      title="Chỉnh sửa"
                    >
                      <PencilIcon class="h-5 w-5" />
                    </button>
                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs font-medium text-white bg-gray-900 rounded shadow-lg whitespace-nowrap opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 pointer-events-none z-50">
                      Chỉnh sửa
                      <div class="absolute top-full left-1/2 -translate-x-1/2 -mt-1 border-4 border-transparent border-t-gray-900"></div>
                    </div>
                  </div>
                  <div class="relative group">
                    <button
                      @click="showDeleteModal(ward)"
                      class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-gray-600 hover:bg-red-50 hover:text-red-600 transition-colors"
                      title="Xóa"
                    >
                      <TrashIcon class="h-5 w-5" />
                    </button>
                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs font-medium text-white bg-gray-900 rounded shadow-lg whitespace-nowrap opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 pointer-events-none z-50">
                      Xóa
                      <div class="absolute top-full left-1/2 -translate-x-1/2 -mt-1 border-4 border-transparent border-t-gray-900"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Streets (Children) -->
            <div
              v-if="expandedWards[ward.id] && ward.children && ward.children.length > 0"
              class="bg-gray-50 border-t border-gray-200"
            >
              <div
                v-for="street in ward.children"
                :key="street.id"
                class="px-6 py-3 pl-16 flex items-center justify-between hover:bg-gray-100 transition-colors"
              >
                <div class="flex items-center flex-1 min-w-0">
                  <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800 mr-3">
                    Đường
                  </span>
                  <div class="text-sm font-medium text-gray-900">{{ street.name }}</div>
                </div>

                <div class="flex items-center gap-4 ml-4">
                </div>

                <div class="flex items-center gap-2 ml-4">
                  <button
                    @click="showEditModal(street)"
                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-gray-600 hover:bg-gray-100 hover:text-primary transition-colors"
                    title="Chỉnh sửa"
                  >
                    <PencilIcon class="h-5 w-5" />
                  </button>
                  <button
                    @click="showDeleteModal(street)"
                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-gray-600 hover:bg-red-50 hover:text-red-600 transition-colors"
                    title="Xóa"
                  >
                    <TrashIcon class="h-5 w-5" />
                  </button>
                </div>
              </div>

              <!-- Empty state for streets -->
              <div
                v-if="ward.children && ward.children.length === 0"
                class="px-6 py-4 pl-16 text-sm text-gray-500 italic"
              >
                Chưa có đường nào
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="wards.length === 0" class="text-center py-12">
          <MapPinIcon class="mx-auto h-12 w-12 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900">Không có địa điểm</h3>
          <p class="mt-1 text-sm text-gray-500">Không tìm thấy địa điểm nào phù hợp với bộ lọc.</p>
        </div>
      </div>

      <!-- Create/Edit Modal -->
      <div
        v-if="showCreateModal || showEditModalFor"
        class="fixed inset-0 z-50 overflow-y-auto"
        @click.self="closeModal"
      >
        <div class="flex items-center justify-center min-h-screen px-4">
          <div class="fixed inset-0 bg-black opacity-50" @click="closeModal"></div>
          <div class="relative bg-white rounded-lg shadow-xl max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
              {{ showEditModalFor ? 'Chỉnh sửa địa điểm' : 'Thêm địa điểm mới' }}
            </h3>
            <form @submit.prevent="handleSubmit">
              <div class="space-y-4">
                <!-- Type -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Loại địa điểm <span class="text-red-500">*</span>
                  </label>
                  <select
                    v-model="form.type"
                    :disabled="!!showEditModalFor"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent disabled:bg-gray-100 disabled:cursor-not-allowed"
                    required
                    @change="handleTypeChange"
                  >
                    <option value="">Chọn loại địa điểm</option>
                    <option value="ward">Phường/Xã</option>
                    <option value="street">Đường</option>
                  </select>
                  <p v-if="formErrors.type" class="mt-1 text-sm text-red-600">{{ formErrors.type }}</p>
                </div>

                <!-- Parent (shown when type is street) -->
                <div v-if="form.type === 'street'">
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Phường/Xã <span class="text-red-500">*</span>
                  </label>
                  <select
                    v-model="form.parent_id"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                    required
                    @change="loadParentAddresses"
                  >
                    <option value="">Chọn Phường/Xã</option>
                    <option v-for="parent in availableParents" :key="parent.id" :value="parent.id">
                      {{ parent.name }}
                    </option>
                  </select>
                  <p v-if="formErrors.parent_id" class="mt-1 text-sm text-red-600">{{ formErrors.parent_id }}</p>
                </div>

                <!-- Name -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Tên địa điểm <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="form.name"
                    type="text"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                    placeholder="Nhập tên địa điểm"
                    required
                  />
                  <p v-if="formErrors.name" class="mt-1 text-sm text-red-600">{{ formErrors.name }}</p>
                </div>
              </div>

              <div class="flex justify-end gap-3 mt-6">
                <button
                  type="button"
                  @click="closeModal"
                  class="px-4 py-2 border-2 border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                >
                  Hủy
                </button>
                <button
                  type="submit"
                  :disabled="isSubmitting"
                  class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                  {{ isSubmitting ? 'Đang xử lý...' : (showEditModalFor ? 'Cập nhật' : 'Tạo địa điểm') }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Delete Confirmation Modal -->
      <div
        v-if="showDeleteModalFor"
        class="fixed inset-0 z-50 overflow-y-auto"
        @click.self="closeDeleteModal"
      >
        <div class="flex items-center justify-center min-h-screen px-4">
          <div class="fixed inset-0 bg-black opacity-50" @click="closeDeleteModal"></div>
          <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Xác nhận xóa</h3>
            <p class="text-sm text-gray-600 mb-6">
              Bạn có chắc chắn muốn xóa địa điểm "<strong>{{ showDeleteModalFor?.name }}</strong>"?
              <span v-if="showDeleteModalFor?.children_count > 0" class="block mt-2 text-red-600">
                Cảnh báo: Địa điểm này có {{ showDeleteModalFor.children_count }} địa điểm con.
              </span>
            </p>
            <div class="flex justify-end gap-3">
              <button
                type="button"
                @click="closeDeleteModal"
                class="px-4 py-2 border-2 border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
              >
                Hủy
              </button>
              <button
                @click="handleDelete"
                :disabled="isDeleting"
                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                {{ isDeleting ? 'Đang xóa...' : 'Xóa' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import Button from '@/components/ui/Button.vue'
import {
  MapPinIcon,
  PlusIcon,
  PencilIcon,
  TrashIcon,
  ChevronRightIcon,
} from '@heroicons/vue/24/outline'
import axios from 'axios'

const props = defineProps({
  wards: Array,
  parentAddresses: Array,
  filters: Object,
})

const page = usePage()
const search = ref(props.filters.search || '')
const parentFilter = ref(props.filters.parent_id || '')
const showCreateModal = ref(false)
const showEditModalFor = ref(null)
const showDeleteModalFor = ref(null)
const isSubmitting = ref(false)
const isDeleting = ref(false)
const availableParents = ref([])
const expandedWards = ref({})

const form = ref({
  type: '',
  name: '',
  parent_id: '',
})

const formErrors = ref({})

let searchTimeout = null

// Initialize expanded wards (expand all by default)
if (props.wards) {
  props.wards.forEach(ward => {
    expandedWards.value[ward.id] = true
  })
}

const toggleWard = (wardId) => {
  expandedWards.value[wardId] = !expandedWards.value[wardId]
}

const handleSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    handleFilter()
  }, 300)
}

const handleFilter = () => {
  const params = {}
  if (search.value) params.search = search.value
  if (parentFilter.value) params.parent_id = parentFilter.value

  router.get('/admin/addresses', params, {
    preserveState: true,
    replace: true,
    onSuccess: () => {
      // Expand all wards after filter
      if (props.wards) {
        props.wards.forEach(ward => {
          expandedWards.value[ward.id] = true
        })
      }
    },
  })
}

const handleTypeChange = async () => {
  form.value.parent_id = ''
  if (form.value.type === 'street') {
    await loadParentAddresses()
  }
}

const loadParentAddresses = async () => {
  if (form.value.type !== 'street') {
    availableParents.value = []
    return
  }

  // Load wards (parent of streets)
  try {
    const response = await axios.get('/admin/addresses/by-parent', {
      params: {
        type: 'ward',
        parent_id: null,
      },
    })
    availableParents.value = response.data
  } catch (error) {
    console.error('Error loading parent addresses:', error)
    availableParents.value = []
  }
}

const showQuickAddStreet = async (ward) => {
  showCreateModal.value = true
  showEditModalFor.value = null
  form.value = {
    type: 'street',
    name: '',
    parent_id: ward.id,
  }
  formErrors.value = {}
  
  // Load available parents and ensure the selected ward is in the list
  await loadParentAddresses()
  
  // Expand the ward to show the new street after adding
  expandedWards.value[ward.id] = true
}

const showEditModal = async (address) => {
  showEditModalFor.value = address
  form.value = {
    type: address.type,
    name: address.name,
    parent_id: address.parent_id || '',
  }
  formErrors.value = {}
  
  if (form.value.type === 'street') {
    await loadParentAddresses()
  }
}

const showDeleteModal = (address) => {
  showDeleteModalFor.value = address
}

const closeModal = () => {
  showCreateModal.value = false
  showEditModalFor.value = null
  form.value = {
    type: '',
    name: '',
    parent_id: '',
  }
  formErrors.value = {}
  availableParents.value = []
}

const closeDeleteModal = () => {
  showDeleteModalFor.value = null
}

const handleSubmit = () => {
  isSubmitting.value = true
  formErrors.value = {}

  const url = showEditModalFor.value
    ? `/admin/addresses/${showEditModalFor.value.id}`
    : '/admin/addresses'

  const method = showEditModalFor.value ? 'put' : 'post'

  router[method](url, form.value, {
    preserveScroll: true,
    onSuccess: () => {
      closeModal()
    },
    onError: (errors) => {
      formErrors.value = errors
    },
    onFinish: () => {
      isSubmitting.value = false
    },
  })
}

const handleDelete = () => {
  if (!showDeleteModalFor.value) return

  isDeleting.value = true
  router.delete(`/admin/addresses/${showDeleteModalFor.value.id}`, {
    preserveScroll: true,
    onFinish: () => {
      isDeleting.value = false
      closeDeleteModal()
    },
  })
}
</script>
