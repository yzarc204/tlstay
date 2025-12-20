<template>
  <Head title="Trang chủ" />
  <AppLayout>
  <div class="home">
    <!-- Slider Section -->
    <section
      class="relative text-white overflow-visible w-full"
      :class="sliders.length > 0 ? 'h-[600px] md:h-[700px]' : 'min-h-[500px]'"
    >
      <!-- Fallback Hero Section if no sliders -->
      <template v-if="sliders.length === 0">
        <div
          class="absolute inset-0 w-full h-full bg-cover bg-center bg-no-repeat"
          style="
            background-image: url('https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=1600');
          "
        >
          <div class="absolute inset-0 w-full h-full bg-black/40"></div>
        </div>
        <div class="container mx-auto px-4 py-12 md:py-16 relative z-10 min-h-[500px] flex items-center">
          <div class="max-w-4xl mx-auto text-center mb-6 md:mb-8">
            <p class="text-sm md:text-base mb-2 md:mb-3 text-white/90">
              Hiện có mặt trên toàn thế giới, và là cộng đồng bất động sản lớn nhất tại Việt Nam năm 2024
            </p>
            <h1 class="text-4xl md:text-6xl font-bold mb-3 md:mb-4 leading-tight">
              Khám phá những căn phòng trọ tốt nhất tại Việt Nam
            </h1>
            <p class="text-lg mb-0 text-white/90 max-w-2xl mx-auto">
              Được hình thành với mục tiêu mang đến trải nghiệm tìm kiếm và thuê phòng trọ tốt nhất.
              TL STAY đã tập hợp những chuyên gia hàng đầu trong lĩnh vực bất động sản và công nghệ.
            </p>
          </div>
        </div>
      </template>

      <!-- Slider Container -->
      <div v-else class="relative w-full h-full overflow-hidden">
        <div
          v-for="(slider, index) in sliders"
          :key="slider.id"
          class="absolute inset-0 w-full h-full slider-slide"
          :class="{
            'slider-slide-active': currentSlide === index,
            'slider-slide-inactive': currentSlide !== index
          }"
        >
          <!-- Background Image -->
          <img
            :src="slider.image"
            :alt="slider.title"
            class="absolute inset-0 w-full h-full object-cover"
          />
          <!-- Overlay để text dễ đọc hơn -->
          <div class="absolute inset-0 w-full h-full bg-black/40"></div>

          <!-- Content - Only show if show_text is enabled -->
          <Link
            v-if="slider.show_text && slider.link"
            :href="slider.link"
            class="block container mx-auto px-4 py-12 md:py-16 relative z-10 w-full h-full flex items-center"
          >
            <div
              class="max-w-6xl mx-auto w-full"
              :class="{
                'text-left': slider.text_position === 'left',
                'text-center': slider.text_position === 'center',
                'text-right': slider.text_position === 'right',
              }"
            >
              <!-- Dòng 1 (chữ nhỏ) -->
              <p v-if="slider.text_line1" class="text-sm md:text-base mb-2 md:mb-3 text-white/90">
                {{ slider.text_line1 }}
              </p>
              <!-- Dòng 2 (chữ to) -->
              <h1 v-if="slider.text_line2" class="text-4xl md:text-6xl font-bold mb-3 md:mb-4 leading-tight">
                {{ slider.text_line2 }}
              </h1>
              <!-- Dòng 3 (chữ nhỏ) -->
              <p v-if="slider.text_line3" class="text-lg mb-0 text-white/90" :class="slider.text_position === 'center' ? 'max-w-4xl mx-auto' : ''">
                {{ slider.text_line3 }}
              </p>
            </div>
          </Link>
          <div
            v-else-if="slider.show_text"
            class="container mx-auto px-4 py-12 md:py-16 relative z-10 w-full h-full flex items-center"
          >
            <div
              class="max-w-6xl mx-auto w-full"
              :class="{
                'text-left': slider.text_position === 'left',
                'text-center': slider.text_position === 'center',
                'text-right': slider.text_position === 'right',
              }"
            >
              <!-- Dòng 1 (chữ nhỏ) -->
              <p v-if="slider.text_line1" class="text-sm md:text-base mb-2 md:mb-3 text-white/90">
                {{ slider.text_line1 }}
              </p>
              <!-- Dòng 2 (chữ to) -->
              <h1 v-if="slider.text_line2" class="text-4xl md:text-6xl font-bold mb-3 md:mb-4 leading-tight">
                {{ slider.text_line2 }}
              </h1>
              <!-- Dòng 3 (chữ nhỏ) -->
              <p v-if="slider.text_line3" class="text-lg mb-0 text-white/90" :class="slider.text_position === 'center' ? 'max-w-4xl mx-auto' : ''">
                {{ slider.text_line3 }}
              </p>
            </div>
          </div>
          <!-- If slider has link but no text, make entire slide clickable -->
          <Link
            v-else-if="slider.link"
            :href="slider.link"
            class="absolute inset-0 w-full h-full z-10"
          />
        </div>

        <!-- Navigation Arrows -->
        <button
          v-if="sliders.length > 1"
          @click="previousSlide"
          class="absolute left-4 top-1/2 transform -translate-y-1/2 z-20 bg-white/20 hover:bg-white/30 rounded-full p-3 transition-all"
          aria-label="Previous slide"
        >
          <ChevronLeftIcon class="w-6 h-6 text-white" />
        </button>
        <button
          v-if="sliders.length > 1"
          @click="nextSlide"
          class="absolute right-4 top-1/2 transform -translate-y-1/2 z-20 bg-white/20 hover:bg-white/30 rounded-full p-3 transition-all"
          aria-label="Next slide"
        >
          <ChevronRightIcon class="w-6 h-6 text-white" />
        </button>

        <!-- Dots Indicator -->
        <div
          v-if="sliders.length > 1"
          class="absolute bottom-20 left-1/2 transform -translate-x-1/2 z-20 flex space-x-2"
        >
          <button
            v-for="(slider, index) in sliders"
            :key="slider.id"
            @click="goToSlide(index)"
            class="w-3 h-3 rounded-full transition-all"
            :class="currentSlide === index ? 'bg-white' : 'bg-white/50'"
            :aria-label="`Go to slide ${index + 1}`"
          />
        </div>
      </div>

      <!-- Search Box - Nằm ở cuối Slider, tràn ra ngoài -->
      <div class="absolute bottom-0 left-0 right-0 transform translate-y-1/2 z-30">
        <div class="container mx-auto px-4">
          <div class="max-w-5xl mx-auto">
            <div class="bg-white rounded-2xl p-6 md:p-8 shadow-2xl">
              <div class="grid grid-cols-1 md:grid-cols-4 gap-4 md:gap-6">
                <!-- Ward (Phường) -->
                <div class="relative">
                  <label class="block text-sm font-semibold text-gray-700 mb-2">Tên phường</label>
                  <div class="relative">
                    <MapPinIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 z-10" />
                    <select
                      v-model="searchForm.ward"
                      class="w-full pl-10 pr-8 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary transition-all text-gray-900 appearance-none bg-white cursor-pointer"
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

                <!-- Street (Đường) -->
                <div class="relative">
                  <label class="block text-sm font-semibold text-gray-700 mb-2">Tên đường</label>
                  <div class="relative">
                    <MapPinIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 z-10" />
                    <select
                      v-model="searchForm.street"
                      class="w-full pl-10 pr-8 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary transition-all text-gray-900 appearance-none bg-white cursor-pointer"
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
                  <label class="block text-sm font-semibold text-gray-700 mb-2">Khoảng giá</label>
                  <div class="relative">
                    <select
                      v-model="searchForm.priceRange"
                      class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary transition-all text-gray-900 appearance-none bg-white cursor-pointer"
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

                <!-- Search Button -->
                <div class="flex items-end">
                  <button
                    @click="handleSearch"
                    class="w-full bg-primary hover:bg-secondary text-white rounded-xl font-semibold py-3 px-6 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center space-x-2"
                  >
                    <MagnifyingGlassIcon class="w-5 h-5" />
                    <span>Tìm kiếm</span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- About Section -->
    <section class="py-20 bg-white pt-32 md:pt-40">
      <div class="container mx-auto px-4">
        <div class="grid md:grid-cols-2 gap-12 items-center">
          <div>
            <span class="text-primary font-semibold mb-2 block">Về chúng tôi</span>
            <h2 class="text-4xl font-bold mb-6 text-secondary">
              Hơn 1000 căn phòng trọ có sẵn để thuê tại các thành phố lớn trên toàn quốc
            </h2>
            <p class="text-gray-600 mb-6 leading-relaxed">
              TL STAY là nền tảng tìm kiếm phòng trọ lớn nhất tại Việt Nam và là điểm khởi đầu
              lý tưởng cho hành trình tìm kiếm phòng trọ của bạn. Bạn có thể dễ dàng tìm kiếm và
              quản lý những căn phòng yêu thích.
            </p>
            <button class="btn-secondary">Tìm hiểu thêm</button>
          </div>
          <div class="relative">
            <img
              src="https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=800"
              alt="About"
              class="rounded-2xl shadow-2xl"
            />
            <!-- Map Overlay -->
            <div class="absolute -bottom-6 -left-6 bg-white p-4 rounded-xl shadow-xl">
              <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                  <MapIcon class="w-6 h-6 text-primary" />
                </div>
                <div>
                  <p class="text-sm text-gray-600">Có mặt tại</p>
                  <p class="font-bold text-secondary">50+ Thành phố</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Featured Properties -->
    <section class="py-20 bg-light">
      <div class="container mx-auto px-4">
        <div class="text-center mb-12">
          <span class="text-primary font-semibold mb-2 block">Bất động sản</span>
          <h2 class="text-4xl font-bold text-secondary mb-4">Nhà trọ nổi bật</h2>
          <p class="text-gray-600 max-w-2xl mx-auto">
            Khám phá bộ sưu tập nhà trọ cao cấp được chúng tôi tuyển chọn kỹ lưỡng
          </p>
        </div>

        <div v-if="featuredHouses.length === 0" class="text-center py-12">
          <p class="text-gray-500 text-lg">Chưa có nhà trọ nổi bật</p>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <HouseCard
            v-for="house in featuredHouses"
            :key="house.id"
            :house="house"
          />
        </div>

        <div v-if="featuredHouses.length > 0" class="text-center mt-12">
          <Link href="/houses" class="btn-secondary"> Xem tất cả nhà trọ </Link>
        </div>
      </div>
    </section>

    <!-- Services Section -->
    <section class="py-20 bg-white">
      <div class="container mx-auto px-4">
        <div class="text-center mb-12">
          <span class="text-primary font-semibold mb-2 block">Dịch vụ</span>
          <h2 class="text-4xl font-bold text-secondary mb-4">
            Quảng bá nhà trọ của bạn để bán hoặc cho thuê với các dịch vụ của chúng tôi
          </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div class="text-center p-8 rounded-2xl hover:shadow-xl transition-all bg-light">
            <div
              class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4"
            >
              <svg
                class="w-8 h-8 text-primary"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                />
              </svg>
            </div>
            <h3 class="text-xl font-bold mb-3 text-secondary">Tìm kiếm dễ dàng</h3>
            <p class="text-gray-600">Tìm căn phòng trọ hoàn hảo với bộ lọc tìm kiếm tiên tiến của chúng tôi</p>
          </div>

          <div class="text-center p-8 rounded-2xl hover:shadow-xl transition-all bg-light">
            <div
              class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4"
            >
              <ShieldCheckIcon class="w-8 h-8 text-primary" />
            </div>
            <h3 class="text-xl font-bold mb-3 text-secondary">Thanh toán an toàn</h3>
            <p class="text-gray-600">Thanh toán 100% an toàn với nhiều phương thức thanh toán</p>
          </div>

          <div class="text-center p-8 rounded-2xl hover:shadow-xl transition-all bg-light">
            <div
              class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4"
            >
              <Cog6ToothIcon class="w-8 h-8 text-primary" />
            </div>
            <h3 class="text-xl font-bold mb-3 text-secondary">Hỗ trợ 24/7</h3>
            <p class="text-gray-600">Hỗ trợ khách hàng 24/7 để phục vụ bạn tốt nhất</p>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-gradient-to-r from-primary to-secondary text-white">
      <div class="container mx-auto px-4 text-center">
        <h2 class="text-4xl font-bold mb-6">Tham gia cùng chúng tôi để nhận cập nhật mới nhất</h2>
        <p class="text-xl mb-8 text-white/90">
          Đăng ký để nhận thông tin cập nhật mới nhất và các ưu đãi độc quyền
        </p>
        <button
          class="px-8 py-4 bg-white text-primary rounded-xl font-semibold hover:bg-gray-100 transition-all shadow-xl"
        >
          Tham gia cộng đồng
        </button>
      </div>
    </section>
  </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import HouseCard from '@/components/house/HouseCard.vue'
import {
  MapPinIcon,
  ChevronDownIcon,
  MagnifyingGlassIcon,
  MapIcon,
  ShieldCheckIcon,
  Cog6ToothIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  featuredHouses: {
    type: Array,
    default: () => [],
  },
  sliders: {
    type: Array,
    default: () => [],
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

const currentSlide = ref(0)
const slideInterval = ref(null)

const startAutoPlay = () => {
  // Clear existing interval if any
  if (slideInterval.value) {
    clearInterval(slideInterval.value)
  }
  
  // Start new interval
  if (props.sliders.length > 1) {
    slideInterval.value = setInterval(() => {
      currentSlide.value = (currentSlide.value + 1) % props.sliders.length
    }, 5000) // Change slide every 5 seconds
  }
}

const nextSlide = () => {
  if (props.sliders.length > 0) {
    currentSlide.value = (currentSlide.value + 1) % props.sliders.length
    startAutoPlay() // Reset timer
  }
}

const previousSlide = () => {
  if (props.sliders.length > 0) {
    currentSlide.value = (currentSlide.value - 1 + props.sliders.length) % props.sliders.length
    startAutoPlay() // Reset timer
  }
}

const goToSlide = (index) => {
  if (props.sliders.length > 0 && index >= 0 && index < props.sliders.length) {
    currentSlide.value = index
    startAutoPlay() // Reset timer
  }
}

// Auto-play slider
onMounted(() => {
  startAutoPlay()
})

onUnmounted(() => {
  if (slideInterval.value) {
    clearInterval(slideInterval.value)
  }
})

const searchForm = ref({
  ward: '',
  street: '',
  priceRange: '',
})

// Find selected ward ID
const selectedWardId = computed(() => {
  if (!searchForm.value.ward) {
    return null
  }
  const ward = props.wards.find(w => w.name === searchForm.value.ward)
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
watch(() => searchForm.value.ward, () => {
  searchForm.value.street = ''
})

const handleSearch = () => {
  const params = new URLSearchParams()
  
  if (searchForm.value.ward) {
    params.append('ward', searchForm.value.ward)
  }
  
  if (searchForm.value.street) {
    params.append('street', searchForm.value.street)
  }
  
  if (searchForm.value.priceRange) {
    params.append('priceRange', searchForm.value.priceRange)
  }
  
  const queryString = params.toString()
  router.visit(`/houses${queryString ? '?' + queryString : ''}`)
}

const formatPrice = (price) => {
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND',
  }).format(price)
}
</script>

<style scoped>
/* Smooth Slider Transition */
.slider-slide {
  opacity: 0;
  transform: scale(1.05);
  transition: opacity 1s cubic-bezier(0.4, 0, 0.2, 1), transform 1s cubic-bezier(0.4, 0, 0.2, 1);
  pointer-events: none;
}

.slider-slide-active {
  opacity: 1;
  transform: scale(1);
  pointer-events: auto;
  z-index: 1;
}

.slider-slide-inactive {
  opacity: 0;
  transform: scale(1.05);
  pointer-events: none;
  z-index: 0;
}
</style>
