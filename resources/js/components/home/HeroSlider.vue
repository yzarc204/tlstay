<template>
  <section
    class="relative text-white overflow-visible w-full"
    :class="sliders.length > 0 ? 'h-[400px] md:h-[700px]' : 'min-h-[400px] md:min-h-[500px]'"
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
      <div
        class="w-full px-2 md:px-3 py-2 md:py-12 relative z-10 min-h-[400px] md:min-h-[500px] flex items-center"
      >
        <div class="w-[90%] md:w-[50%] mx-auto text-center">
          <p
            class="text-sm md:text-base mb-4 md:mb-3 text-white/90"
          >
            Hiện có mặt trên toàn thế giới, và là cộng đồng
            bất động sản lớn nhất tại Việt Nam năm 2024
          </p>
          <h1
            class="text-2xl md:text-3xl lg:text-4xl xl:text-5xl font-bold mb-5 md:mb-4 leading-tight"
          >
            Khám phá những căn phòng trọ tốt nhất tại Việt
            Nam
          </h1>
          <p class="text-base md:text-lg mb-0 text-white/90">
            Được hình thành với mục tiêu mang đến trải
            nghiệm tìm kiếm và thuê phòng trọ tốt nhất. TL
            STAY đã tập hợp những chuyên gia hàng đầu trong
            lĩnh vực bất động sản và công nghệ.
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
          'slider-slide-inactive': currentSlide !== index,
        }"
      >
        <!-- Background Image -->
        <img
          :src="slider.image"
          :alt="slider.title"
          class="absolute inset-0 w-full h-full object-cover"
        />
        <!-- Overlay để text dễ đọc hơn -->
        <div
          class="absolute inset-0 w-full h-full bg-black/40"
        ></div>

        <!-- Content - Only show if show_text is enabled -->
        <Link
          v-if="slider.show_text && slider.link"
          :href="slider.link"
          class="block w-full px-2 md:px-3 py-2 md:py-12 relative z-10 h-full flex items-center"
        >
          <div
            class="w-[90%] md:w-[50%] mx-auto"
            :class="{
              'text-left':
                slider.text_position === 'left',
              'text-center':
                slider.text_position === 'center',
              'text-right':
                slider.text_position === 'right',
            }"
          >
            <!-- Dòng 1 (chữ nhỏ) -->
            <p
              v-if="slider.text_line1"
              class="text-sm md:text-base mb-4 md:mb-3 text-white/90"
            >
              {{ slider.text_line1 }}
            </p>
            <!-- Dòng 2 (chữ to) -->
            <h1
              v-if="slider.text_line2"
              class="text-2xl md:text-3xl lg:text-4xl xl:text-5xl font-bold mb-5 md:mb-4 leading-tight"
            >
              {{ slider.text_line2 }}
            </h1>
            <!-- Dòng 3 (chữ nhỏ) -->
            <p
              v-if="slider.text_line3"
              class="text-base md:text-lg mb-0 text-white/90"
            >
              {{ slider.text_line3 }}
            </p>
          </div>
        </Link>
        <div
          v-else-if="slider.show_text"
          class="w-full px-2 md:px-3 py-2 md:py-12 relative z-10 h-full flex items-center"
        >
          <div
            class="w-[90%] md:w-[50%] mx-auto"
            :class="{
              'text-left':
                slider.text_position === 'left',
              'text-center':
                slider.text_position === 'center',
              'text-right':
                slider.text_position === 'right',
            }"
          >
            <!-- Dòng 1 (chữ nhỏ) -->
            <p
              v-if="slider.text_line1"
              class="text-sm md:text-base mb-4 md:mb-3 text-white/90"
            >
              {{ slider.text_line1 }}
            </p>
            <!-- Dòng 2 (chữ to) -->
            <h1
              v-if="slider.text_line2"
              class="text-2xl md:text-3xl lg:text-4xl xl:text-5xl font-bold mb-5 md:mb-4 leading-tight"
            >
              {{ slider.text_line2 }}
            </h1>
            <!-- Dòng 3 (chữ nhỏ) -->
            <p
              v-if="slider.text_line3"
              class="text-base md:text-lg mb-0 text-white/90"
            >
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
          :class="
            currentSlide === index
              ? 'bg-white'
              : 'bg-white/50'
          "
          :aria-label="`Go to slide ${index + 1}`"
        />
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  sliders: {
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
      currentSlide.value =
        (currentSlide.value + 1) % props.sliders.length
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
    currentSlide.value =
      (currentSlide.value - 1 + props.sliders.length) %
      props.sliders.length
    startAutoPlay() // Reset timer
  }
}

const goToSlide = (index) => {
  if (
    props.sliders.length > 0 &&
    index >= 0 &&
    index < props.sliders.length
  ) {
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
</script>

<style scoped>
/* Smooth Slider Transition */
.slider-slide {
  opacity: 0;
  transform: scale(1.05);
  transition: opacity 1s cubic-bezier(0.4, 0, 0.2, 1),
    transform 1s cubic-bezier(0.4, 0, 0.2, 1);
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
