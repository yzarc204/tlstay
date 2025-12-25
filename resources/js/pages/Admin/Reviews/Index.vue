<template>
  <Head title="Quản lý Đánh giá" />
  <AdminLayout title="Quản lý Đánh giá">
    <div class="space-y-6">
      <!-- Header -->
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Quản lý Đánh giá</h1>
        <p class="text-gray-600 mt-1">Xem và phản hồi các đánh giá từ khách hàng</p>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow-sm p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- House Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Nhà trọ
            </label>
            <SelectSearchable
              v-model="filters.house_id"
              :options="houses"
              option-value="id"
              option-label="name"
              placeholder="Tất cả nhà trọ..."
              @update:modelValue="applyFilters"
            />
          </div>

          <!-- Rating Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Số sao
            </label>
            <select
              v-model="filters.rating"
              @change="applyFilters"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
            >
              <option value="">Tất cả</option>
              <option value="5">5 sao</option>
              <option value="4">4 sao</option>
              <option value="3">3 sao</option>
              <option value="2">2 sao</option>
              <option value="1">1 sao</option>
            </select>
          </div>

          <!-- Response Status Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Trạng thái phản hồi
            </label>
            <select
              v-model="filters.has_response"
              @change="applyFilters"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
            >
              <option value="">Tất cả</option>
              <option value="1">Đã phản hồi</option>
              <option value="0">Chưa phản hồi</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Reviews List -->
      <div v-if="reviews.data && reviews.data.length > 0" class="space-y-4">
        <div
          v-for="review in reviews.data"
          :key="review.id"
          class="bg-white rounded-lg shadow-sm p-6"
        >
          <!-- Review Header -->
          <div class="flex items-start justify-between mb-4">
            <div class="flex items-center space-x-4 flex-1">
              <div class="w-12 h-12 rounded-full bg-primary-100 flex items-center justify-center">
                <span class="text-primary font-semibold">
                  {{ getUserInitials(review.user) }}
                </span>
              </div>
              <div class="flex-1">
                <p class="font-semibold text-gray-800">{{ review.user?.name || 'Khách hàng' }}</p>
                <p class="text-sm text-gray-500">{{ formatDate(review.created_at) }}</p>
                <p class="text-sm text-gray-600">Nhà trọ: {{ review.house?.name }}</p>
              </div>
            </div>
            <div class="flex items-center space-x-3">
              <div class="flex items-center space-x-1">
                <StarIcon
                  v-for="star in 5"
                  :key="star"
                  :class="[
                    'w-5 h-5',
                    star <= review.rating ? 'text-yellow-400 fill-current' : 'text-gray-300'
                  ]"
                />
              </div>
              <button
                @click="handleDelete(review)"
                class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                title="Xóa đánh giá"
              >
                <TrashIcon class="w-5 h-5" />
              </button>
            </div>
          </div>

          <!-- Comment -->
          <p v-if="review.comment" class="text-gray-700 mb-4 whitespace-pre-wrap">
            {{ review.comment }}
          </p>

          <!-- Images -->
          <div v-if="review.images && review.images.length > 0" class="grid grid-cols-4 gap-2 mb-4">
            <div
              v-for="(image, index) in review.images"
              :key="index"
              class="aspect-square rounded-lg overflow-hidden border border-gray-200 cursor-pointer hover:opacity-90"
              @click="openImage(image)"
            >
              <img :src="image" :alt="`Review image ${index + 1}`" class="w-full h-full object-cover" />
            </div>
          </div>

          <!-- Manager Response -->
          <div v-if="review.manager_response" class="mt-4 pt-4 border-t border-gray-200 bg-gray-50 rounded-lg p-4">
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <div class="flex items-center space-x-2 mb-2">
                  <span class="font-semibold text-secondary">Phản hồi của bạn</span>
                  <span v-if="review.manager_response_at" class="text-xs text-gray-500">
                    {{ formatDate(review.manager_response_at) }}
                  </span>
                </div>
                <p class="text-gray-700 whitespace-pre-wrap">{{ review.manager_response }}</p>
              </div>
              <button
                @click="editResponse(review)"
                class="ml-4 text-primary hover:text-primary-dark text-sm font-medium"
              >
                Chỉnh sửa
              </button>
            </div>
          </div>

          <!-- Response Form -->
          <div v-else class="mt-4 pt-4 border-t border-gray-200">
            <ManagerResponseForm
              :review-id="review.id"
              @response-sent="handleResponseSent"
            />
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="reviews.links && reviews.links.length > 3" class="flex justify-center">
          <div class="flex space-x-2">
            <Link
              v-for="link in reviews.links"
              :key="link.label"
              :href="link.url || '#'"
              :class="[
                'px-4 py-2 rounded-lg border',
                link.active
                  ? 'bg-primary text-white border-primary'
                  : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50',
                !link.url ? 'opacity-50 cursor-not-allowed' : ''
              ]"
              v-html="link.label"
            />
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="bg-white rounded-lg shadow-sm p-12 text-center">
        <ChatBubbleLeftRightIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
        <p class="text-gray-600 text-lg">Chưa có đánh giá nào</p>
      </div>
    </div>

    <!-- Edit Response Modal -->
    <ManagerResponseForm
      v-if="editingReview"
      :review-id="editingReview.id"
      :initial-response="editingReview.manager_response"
      @response-sent="handleResponseSent"
      @cancel="editingReview = null"
    />
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import SelectSearchable from '@/components/ui/SelectSearchable.vue'
import ManagerResponseForm from '@/components/reviews/ManagerResponseForm.vue'
import { StarIcon, ChatBubbleLeftRightIcon, TrashIcon } from '@heroicons/vue/24/outline'
import { useConfirm } from '@/composables/useConfirm'

const props = defineProps({
  reviews: {
    type: Object,
    required: true,
  },
  houses: {
    type: Array,
    default: () => [],
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
})

const editingReview = ref(null)
const confirm = useConfirm()

const getUserInitials = (user) => {
  if (!user?.name) return 'K'
  const parts = user.name.split(' ')
  if (parts.length >= 2) {
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
  }
  return user.name[0].toUpperCase()
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('vi-VN', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const applyFilters = () => {
  router.get('/admin/reviews', props.filters, {
    preserveState: true,
    preserveScroll: true,
  })
}

const editResponse = (review) => {
  editingReview.value = review
}

const handleResponseSent = () => {
  editingReview.value = null
  router.reload({ only: ['reviews'] })
}

const openImage = (imageUrl) => {
  window.open(imageUrl, '_blank')
}

const handleDelete = async (review) => {
  try {
    await confirm.show({
      title: 'Xóa đánh giá',
      message: `Bạn có chắc chắn muốn xóa đánh giá của "${review.user?.name || 'Khách hàng'}"?\n\nHành động này sẽ xóa đánh giá và tất cả ảnh liên quan. Hành động này không thể hoàn tác.`,
      confirmText: 'Xóa',
      cancelText: 'Hủy',
      confirmVariant: 'danger',
    })

    // User confirmed, proceed with deletion
    router.delete(`/admin/reviews/${review.id}`, {
      preserveScroll: true,
      onSuccess: () => {
        // Success message will be shown via Inertia flash message
      },
    })
  } catch (error) {
    // User cancelled - do nothing
    if (error.message !== 'USER_CANCELLED') {
      console.error('Error showing confirm dialog:', error)
    }
  }
}
</script>
