<template>
  <Head title="Danh sách nhà trọ" />
  <AppLayout>
    <div class="house-list py-12 bg-light min-h-screen">
      <div class="container mx-auto px-4">
        <!-- Header -->
        <div class="mb-8">
          <h1 class="text-4xl font-bold text-secondary mb-2">Danh sách nhà trọ</h1>
          <p class="text-gray-600">Tìm phòng trọ phù hợp với bạn</p>
        </div>

        <!-- Active Filters -->
        <div v-if="hasActiveFilters" class="mb-6 flex flex-wrap gap-2">
          <span class="text-sm text-gray-600 font-medium">Bộ lọc đang áp dụng:</span>
          <span
            v-if="filters.ward"
            class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-primary-100 text-primary-700"
          >
            Phường: {{ filters.ward }}
            <button
              @click="clearFilter('ward')"
              class="ml-2 hover:text-primary-900"
            >
              <XMarkIcon class="w-4 h-4" />
            </button>
          </span>
          <span
            v-if="filters.street"
            class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-primary-100 text-primary-700"
          >
            Đường: {{ filters.street }}
            <button
              @click="clearFilter('street')"
              class="ml-2 hover:text-primary-900"
            >
              <XMarkIcon class="w-4 h-4" />
            </button>
          </span>
          <span
            v-if="filters.priceRange"
            class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-primary-100 text-primary-700"
          >
            Giá: {{ getPriceRangeText(filters.priceRange) }}
            <button
              @click="clearFilter('priceRange')"
              class="ml-2 hover:text-primary-900"
            >
              <XMarkIcon class="w-4 h-4" />
            </button>
          </span>
          <button
            v-if="hasActiveFilters"
            @click="clearAllFilters"
            class="text-sm text-red-600 hover:text-red-700 font-medium"
          >
            Xóa tất cả bộ lọc
          </button>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-2xl shadow-md p-6 mb-8">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Ward Filter -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Tên phường</label>
              <div class="relative">
                <MapPinIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 z-10" />
                <select
                  v-model="filters.ward"
                  @change="applyFilters"
                  class="w-full pl-10 pr-8 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-all text-gray-900 appearance-none bg-white cursor-pointer"
                >
                  <option value="">Tất cả phường</option>
                  <option
                    v-for="ward in wards"
                    :key="ward.id"
                    :value="ward.name"
                  >
                    {{ ward.name }}
                  </option>
                </select>
                <ChevronDownIcon class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none z-10" />
              </div>
            </div>

            <!-- Street Filter -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Tên đường</label>
              <div class="relative">
                <MapPinIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 z-10" />
                <select
                  v-model="filters.street"
                  @change="applyFilters"
                  class="w-full pl-10 pr-8 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-all text-gray-900 appearance-none bg-white cursor-pointer"
                >
                  <option value="">Tất cả đường</option>
                  <option
                    v-for="street in filteredStreets"
                    :key="street.id"
                    :value="street.name"
                  >
                    {{ street.name }}{{ street.ward_name ? ` (${street.ward_name})` : '' }}
                  </option>
                </select>
                <ChevronDownIcon class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none z-10" />
              </div>
            </div>

            <!-- Price Range -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Khoảng giá</label>
              <div class="relative">
                <select
                  v-model="filters.priceRange"
                  @change="applyFilters"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-all text-gray-900 appearance-none bg-white cursor-pointer"
                >
                  <option value="">Tất cả mức giá</option>
                  <option value="0-2000000">Dưới 2 triệu/ngày</option>
                  <option value="2000000-5000000">2 - 5 triệu/ngày</option>
                  <option value="5000000-10000000">5 - 10 triệu/ngày</option>
                  <option value="10000000+">Trên 10 triệu/ngày</option>
                </select>
                <ChevronDownIcon class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none" />
              </div>
            </div>

            <!-- Sort By -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Sắp xếp</label>
              <div class="relative">
                <select
                  v-model="sortBy"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-all text-gray-900 appearance-none bg-white cursor-pointer"
                >
                  <option value="default">Mặc định</option>
                  <option value="price-asc">Giá: Thấp đến cao</option>
                  <option value="price-desc">Giá: Cao đến thấp</option>
                  <option value="rating">Đánh giá cao nhất</option>
                </select>
                <ChevronDownIcon class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none" />
              </div>
            </div>

            <!-- View Type -->
            <div class="flex items-end space-x-2">
              <button
                @click="viewType = 'grid'"
                class="flex-1 px-4 py-3 rounded-lg transition-all"
                :class="
                  viewType === 'grid'
                    ? 'bg-primary text-white'
                    : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                "
              >
                <Squares2X2Icon class="w-5 h-5 mx-auto" />
              </button>
              <button
                @click="viewType = 'list'"
                class="flex-1 px-4 py-3 rounded-lg transition-all"
                :class="
                  viewType === 'list'
                    ? 'bg-primary text-white'
                    : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                "
              >
                <ListBulletIcon class="w-5 h-5 mx-auto" />
              </button>
            </div>
          </div>
        </div>

        <!-- Results Count -->
        <div class="flex items-center justify-between mb-6">
          <p class="text-gray-600">
            Tìm thấy
            <span class="font-semibold text-secondary">{{ houses.length }}</span> nhà trọ
          </p>
        </div>

        <!-- Houses Grid/List -->
        <div>
          <!-- Empty State -->
          <div v-if="houses.length === 0" class="text-center py-20">
            <BuildingOfficeIcon class="w-24 h-24 mx-auto text-gray-300 mb-4" />
            <h3 class="text-2xl font-bold text-gray-400 mb-2">Không tìm thấy nhà trọ</h3>
            <p class="text-gray-500 mb-4">Thử điều chỉnh bộ lọc của bạn</p>
            <button @click="clearAllFilters" class="btn-primary">Xóa tất cả bộ lọc</button>
          </div>

          <!-- Grid View -->
          <div
            v-else-if="viewType === 'grid'"
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8"
          >
            <HouseCard v-for="house in sortedHouses" :key="house.id" :house="house" />
          </div>

          <!-- List View -->
          <div v-else class="space-y-6">
            <div
              v-for="house in sortedHouses"
              :key="house.id"
              class="card flex flex-col md:flex-row cursor-pointer hover:shadow-lg transition-shadow"
              @click="goToDetail(house.id)"
            >
              <div class="md:w-1/3 h-64 md:h-auto">
                <img :src="house.image" :alt="house.name" class="w-full h-full object-cover rounded-l-lg" />
              </div>
              <div class="flex-1 p-6">
                <div class="flex items-start justify-between mb-4">
                  <div>
                    <h3 class="text-2xl font-bold text-secondary mb-2">{{ house.name }}</h3>
                    <p class="text-gray-600 flex items-center mb-1">
                      <MapPinIcon class="w-5 h-5 mr-2" />
                      {{ house.address }}
                    </p>
                    <div v-if="house.ward_name || house.street_name" class="flex items-center gap-2 text-sm text-gray-500">
                      <span v-if="house.ward_name">{{ house.ward_name }}</span>
                      <span v-if="house.ward_name && house.street_name">•</span>
                      <span v-if="house.street_name">{{ house.street_name }}</span>
                    </div>
                  </div>
                  <span class="badge bg-primary-100 text-primary-700">
                    {{ house.availableRooms }} phòng trống
                  </span>
                </div>

                <div class="flex flex-wrap gap-2 mb-4">
                  <template v-for="(value, amenity) in house.amenities" :key="amenity">
                    <span
                      v-if="value"
                      class="badge bg-primary-50 text-primary flex items-center space-x-1.5"
                    >
                      <component
                        :is="getAmenityIcon(amenity)"
                        class="w-4 h-4"
                      />
                      <span>{{ getAmenityName(amenity) }}</span>
                    </span>
                  </template>
                </div>

                <div class="flex items-center justify-between pt-4 border-t">
                  <div>
                    <p class="text-2xl font-bold text-primary">
                      {{ formatPrice(house.pricePerDay) }}
                      <span class="text-sm text-gray-500 font-normal">/ngày</span>
                    </p>
                  </div>
                  <button class="btn-secondary">Xem chi tiết</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import HouseCard from '@/components/house/HouseCard.vue'
import { getAmenityIcon, getAmenityName } from '@/utils/amenityIcons'
import {
  Squares2X2Icon,
  ListBulletIcon,
  MapPinIcon,
  BuildingOfficeIcon,
  ChevronDownIcon,
  XMarkIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  houses: {
    type: Array,
    default: () => [],
  },
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

const viewType = ref('grid')
const sortBy = ref('default')

const filters = ref({
  ward: props.filters?.ward || '',
  street: props.filters?.street || '',
  priceRange: props.filters?.priceRange || '',
})

// Find selected ward ID
const selectedWardId = computed(() => {
  if (!filters.value.ward) {
    return null
  }
  const ward = props.wards.find(w => w.name === filters.value.ward)
  return ward ? ward.id : null
})

// Filter streets based on selected ward
const filteredStreets = computed(() => {
  if (!selectedWardId.value) {
    return props.streets
  }
  return props.streets.filter(street => street.ward_id === selectedWardId.value)
})

// Watch ward change to reset street selection
watch(() => filters.value.ward, () => {
  filters.value.street = ''
})

const hasActiveFilters = computed(() => {
  return !!(filters.value.ward || filters.value.street || filters.value.priceRange)
})

// Apply sorting
const sortedHouses = computed(() => {
  let houses = [...props.houses]

  if (sortBy.value === 'price-asc') {
    houses.sort((a, b) => a.pricePerDay - b.pricePerDay)
  } else if (sortBy.value === 'price-desc') {
    houses.sort((a, b) => b.pricePerDay - a.pricePerDay)
  } else if (sortBy.value === 'rating') {
    houses.sort((a, b) => b.rating - a.rating)
  }

  return houses
})

const formatPrice = (price) => {
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(price)
}

const getPriceRangeText = (priceRange) => {
  if (!priceRange) return ''
  if (priceRange === '0-2000000') return 'Dưới 2 triệu/ngày'
  if (priceRange === '2000000-5000000') return '2 - 5 triệu/ngày'
  if (priceRange === '5000000-10000000') return '5 - 10 triệu/ngày'
  if (priceRange === '10000000+') return 'Trên 10 triệu/ngày'
  return priceRange
}

const applyFilters = () => {
  const params = new URLSearchParams()
  
  if (filters.value.ward) {
    params.append('ward', filters.value.ward)
  }
  
  if (filters.value.street) {
    params.append('street', filters.value.street)
  }
  
  if (filters.value.priceRange) {
    params.append('priceRange', filters.value.priceRange)
  }
  
  const queryString = params.toString()
  router.visit(`/houses${queryString ? '?' + queryString : ''}`, {
    preserveState: true,
    preserveScroll: true,
  })
}

const clearFilter = (filterName) => {
  filters.value[filterName] = ''
  applyFilters()
}

const clearAllFilters = () => {
  filters.value = {
    ward: '',
    street: '',
    priceRange: '',
  }
  router.visit('/houses', {
    preserveState: false,
    preserveScroll: true,
  })
}

const goToDetail = (id) => {
  router.visit(`/houses/${id}`)
}
</script>
