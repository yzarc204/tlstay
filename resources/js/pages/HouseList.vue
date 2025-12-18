<template>
  <div class="house-list py-12 bg-light min-h-screen">
    <div class="container mx-auto px-4">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-4xl font-bold text-secondary mb-2">Available Properties</h1>
        <p class="text-gray-600">Find your perfect rental home</p>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-2xl shadow-md p-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <!-- Location Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
            <input
              v-model="filters.location"
              type="text"
              placeholder="Search location..."
              class="input-field"
              @input="applyFilters"
            />
          </div>

          <!-- Price Range -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Max Price: {{ formatPrice(filters.priceRange[1]) }}
            </label>
            <input
              v-model="filters.priceRange[1]"
              type="range"
              min="1000000"
              max="10000000"
              step="500000"
              class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-primary"
              @input="applyFilters"
            />
          </div>

          <!-- Sort By -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
            <select v-model="sortBy" class="input-field" @change="applySorting">
              <option value="default">Default</option>
              <option value="price-asc">Price: Low to High</option>
              <option value="price-desc">Price: High to Low</option>
              <option value="rating">Highest Rated</option>
            </select>
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
          Found
          <span class="font-semibold text-secondary">{{ filteredHouses.length }}</span> properties
        </p>
      </div>

      <!-- Loading State -->
      <div v-if="houseStore.loading" class="text-center py-20">
        <div
          class="inline-block animate-spin rounded-full h-16 w-16 border-4 border-primary border-t-transparent"
        ></div>
        <p class="mt-4 text-gray-600">Loading properties...</p>
      </div>

      <!-- Houses Grid/List -->
      <div v-else>
        <!-- Grid View -->
        <div
          v-if="viewType === 'grid'"
          class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8"
        >
          <HouseCard v-for="house in filteredHouses" :key="house.id" :house="house" />
        </div>

        <!-- List View -->
        <div v-else class="space-y-6">
          <div
            v-for="house in filteredHouses"
            :key="house.id"
            class="card flex flex-col md:flex-row cursor-pointer"
            @click="goToDetail(house.id)"
          >
            <div class="md:w-1/3 h-64 md:h-auto">
              <img :src="house.image" :alt="house.name" class="w-full h-full object-cover" />
            </div>
            <div class="flex-1 p-6">
              <div class="flex items-start justify-between mb-4">
                <div>
                  <h3 class="text-2xl font-bold text-secondary mb-2">{{ house.name }}</h3>
                  <p class="text-gray-600 flex items-center">
                    <MapPinIcon class="w-5 h-5 mr-2" />
                    {{ house.address }}
                  </p>
                </div>
                <span class="badge bg-primary-100 text-primary-700">
                  {{ house.availableRooms }} phòng trống
                </span>
              </div>

              <p class="text-gray-600 mb-4">{{ house.description }}</p>

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
                  <p class="text-3xl font-bold text-primary">
                    {{ formatPrice(house.pricePerMonth) }}
                    <span class="text-sm text-gray-500 font-normal">/tháng</span>
                  </p>
                </div>
                <button class="btn-secondary">View Details</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="filteredHouses.length === 0" class="text-center py-20">
          <BuildingOfficeIcon class="w-24 h-24 mx-auto text-gray-300 mb-4" />
          <h3 class="text-2xl font-bold text-gray-400 mb-2">No properties found</h3>
          <p class="text-gray-500">Try adjusting your filters</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { useHouseStore } from '@/stores/house'
import HouseCard from '@/components/house/HouseCard.vue'
import { getAmenityIcon, getAmenityName } from '@/utils/amenityIcons'
import { Squares2X2Icon, ListBulletIcon, MapPinIcon, BuildingOfficeIcon } from '@heroicons/vue/24/outline'

const houseStore = useHouseStore()

const viewType = ref('grid')
const sortBy = ref('default')

const filters = ref({
  location: '',
  priceRange: [0, 10000000],
})

onMounted(() => {
  houseStore.fetchHouses()
})

// Tính giá theo tháng từ giá ngày (giảm 20%)
const calculateMonthlyPrice = (pricePerDay) => {
  if (!pricePerDay) return 0
  return Math.round(pricePerDay * 30 * 0.8)
}

const filteredHouses = computed(() => {
  let houses = houseStore.houses.filter((house) => {
    // Tính giá theo tháng từ pricePerDay
    const monthlyPrice = calculateMonthlyPrice(house.pricePerDay)
    const priceMatch =
      monthlyPrice >= filters.value.priceRange[0] &&
      monthlyPrice <= filters.value.priceRange[1]
    const locationMatch =
      !filters.value.location ||
      house.address.toLowerCase().includes(filters.value.location.toLowerCase())
    return priceMatch && locationMatch
  })

  // Apply sorting
  if (sortBy.value === 'price-asc') {
    houses.sort((a, b) => calculateMonthlyPrice(a.pricePerDay) - calculateMonthlyPrice(b.pricePerDay))
  } else if (sortBy.value === 'price-desc') {
    houses.sort((a, b) => calculateMonthlyPrice(b.pricePerDay) - calculateMonthlyPrice(a.pricePerDay))
  } else if (sortBy.value === 'rating') {
    houses.sort((a, b) => b.rating - a.rating)
  }

  return houses
})

const formatPrice = (price) => {
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND',
  }).format(price)
}

const applyFilters = () => {
  houseStore.setFilters(filters.value)
}

const applySorting = () => {
  // Sorting is handled in computed property
}

const goToDetail = (id) => {
  router.visit(`/houses/${id}`)
}
</script>
