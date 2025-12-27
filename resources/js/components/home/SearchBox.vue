<template>
  <div
    class="relative md:absolute bottom-0 left-0 right-0 md:transform md:translate-y-1/2 z-30 pt-6 md:pt-0"
  >
    <div class="container mx-auto px-4">
      <div class="max-w-5xl mx-auto">
        <div
          class="bg-white rounded-2xl p-6 md:p-8 shadow-2xl"
        >
          <div
            class="grid grid-cols-1 md:grid-cols-4 gap-4 md:gap-6"
          >
            <!-- Ward (Phường) -->
            <div class="relative">
              <label
                class="block text-sm font-semibold text-gray-700 mb-2"
                >Tên phường</label
              >
              <div class="relative">
                <MapPinIcon
                  class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 z-10"
                />
                <select
                  v-model="localSearchForm.ward"
                  class="w-full pl-10 pr-8 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary transition-all text-gray-900 appearance-none bg-white cursor-pointer"
                >
                  <option value="">
                    Tất cả phường
                  </option>
                  <option
                    v-for="ward in wards"
                    :key="ward.id"
                    :value="ward.name"
                  >
                    {{ ward.name }}
                  </option>
                </select>
                <ChevronDownIcon
                  class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none z-10"
                />
              </div>
            </div>

            <!-- Street (Đường) -->
            <div class="relative">
              <label
                class="block text-sm font-semibold text-gray-700 mb-2"
                >Tên đường</label
              >
              <div class="relative">
                <MapPinIcon
                  class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 z-10"
                />
                <select
                  v-model="localSearchForm.street"
                  class="w-full pl-10 pr-8 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary transition-all text-gray-900 appearance-none bg-white cursor-pointer"
                >
                  <option value="">
                    Tất cả đường
                  </option>
                  <option
                    v-for="street in filteredStreets"
                    :key="street.id"
                    :value="street.name"
                  >
                    {{ street.name
                    }}{{
                      street.ward_name
                        ? ` (${street.ward_name})`
                        : ""
                    }}
                  </option>
                </select>
                <ChevronDownIcon
                  class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none z-10"
                />
              </div>
            </div>

            <!-- Price Range -->
            <div>
              <label
                class="block text-sm font-semibold text-gray-700 mb-2"
                >Khoảng giá</label
              >
              <div class="relative">
                <select
                  v-model="localSearchForm.priceRange"
                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary transition-all text-gray-900 appearance-none bg-white cursor-pointer"
                >
                  <option value="">
                    Tất cả mức giá
                  </option>
                  <option value="0-2000000">
                    Dưới 2 triệu/ngày
                  </option>
                  <option value="2000000-5000000">
                    2 - 5 triệu/ngày
                  </option>
                  <option
                    value="5000000-10000000"
                  >
                    5 - 10 triệu/ngày
                  </option>
                  <option value="10000000+">
                    Trên 10 triệu/ngày
                  </option>
                </select>
                <ChevronDownIcon
                  class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none"
                />
              </div>
            </div>

            <!-- Search Button -->
            <div class="flex items-end">
              <button
                @click="handleSearch"
                class="w-full bg-primary hover:bg-secondary text-white rounded-xl font-semibold py-3 px-6 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center space-x-2"
              >
                <MagnifyingGlassIcon
                  class="w-5 h-5"
                />
                <span>Tìm kiếm</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import {
  MapPinIcon,
  ChevronDownIcon,
  MagnifyingGlassIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  wards: {
    type: Array,
    default: () => [],
  },
  streets: {
    type: Array,
    default: () => [],
  },
})

const localSearchForm = ref({
  ward: '',
  street: '',
  priceRange: '',
})

// Find selected ward ID
const selectedWardId = computed(() => {
  if (!localSearchForm.value.ward) {
    return null
  }
  const ward = props.wards.find((w) => w.name === localSearchForm.value.ward)
  return ward ? ward.id : null
})

// Filter streets based on selected ward
const filteredStreets = computed(() => {
  if (!selectedWardId.value) {
    return props.streets
  }
  return props.streets.filter(
    (street) => street.ward_id === selectedWardId.value
  )
})

// Watch ward change to reset street selection
watch(
  () => localSearchForm.value.ward,
  () => {
    localSearchForm.value.street = ''
  }
)

const handleSearch = () => {
  const params = new URLSearchParams()

  if (localSearchForm.value.ward) {
    params.append('ward', localSearchForm.value.ward)
  }

  if (localSearchForm.value.street) {
    params.append('street', localSearchForm.value.street)
  }

  if (localSearchForm.value.priceRange) {
    params.append('priceRange', localSearchForm.value.priceRange)
  }

  const queryString = params.toString()
  router.visit(`/houses${queryString ? '?' + queryString : ''}`)
}
</script>
