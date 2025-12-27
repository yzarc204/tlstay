<template>
    <section class="py-20 bg-light">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <span class="text-primary font-semibold mb-2 block"
                    >Dịch vụ lưu trú</span
                >
                <h2 class="text-4xl font-bold text-secondary mb-4">
                    Nhà trọ nổi bật
                </h2>
                <p class="text-gray-600 max-w-2xl mx-auto">
                    Khám phá bộ sưu tập nhà trọ cao cấp được chúng tôi tuyển
                    chọn kỹ lưỡng
                </p>
            </div>

            <div v-if="featuredHouses.length === 0" class="text-center py-12">
                <p class="text-gray-500 text-lg">Chưa có nhà trọ nổi bật</p>
            </div>

            <!-- Carousel Container -->
            <div
                v-else
                class="relative"
            >
                <!-- Carousel Wrapper -->
                <div
                    class="overflow-hidden"
                    ref="carouselRef"
                    @mousedown="handleMouseDown"
                    @mousemove="handleMouseMove"
                    @mouseup="handleMouseUp"
                    @mouseleave="handleMouseUp"
                    @touchstart="handleTouchStart"
                    @touchmove="handleTouchMove"
                    @touchend="handleTouchEnd"
                >
                    <div
                        class="flex transition-transform duration-300 ease-out"
                        :style="{
                            transform: `translateX(${translateX}px)`,
                            transition: isDragging ? 'none' : 'transform 0.3s ease-out'
                        }"
                    >
                        <div
                            v-for="(chunk, chunkIndex) in houseChunks"
                            :key="chunkIndex"
                            class="flex-shrink-0 w-full"
                        >
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 px-2">
                                <HouseCard
                                    v-for="house in chunk"
                                    :key="house.id"
                                    :house="house"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Arrows -->
                <button
                    v-if="totalChunks > 1"
                    @click="previousSlide"
                    class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-4 md:-translate-x-8 z-10 bg-white rounded-full p-2 md:p-3 shadow-lg hover:shadow-xl transition-all hover:bg-primary hover:text-white group"
                    aria-label="Previous slide"
                >
                    <ChevronLeftIcon class="w-5 h-5 md:w-6 md:h-6 text-gray-700 group-hover:text-white transition-colors" />
                </button>
                <button
                    v-if="totalChunks > 1"
                    @click="nextSlide"
                    class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-4 md:translate-x-8 z-10 bg-white rounded-full p-2 md:p-3 shadow-lg hover:shadow-xl transition-all hover:bg-primary hover:text-white group"
                    aria-label="Next slide"
                >
                    <ChevronRightIcon class="w-5 h-5 md:w-6 md:h-6 text-gray-700 group-hover:text-white transition-colors" />
                </button>

                <!-- Dots Indicator -->
                <div
                    v-if="totalChunks > 1"
                    class="flex justify-center items-center space-x-2 mt-8"
                >
                    <button
                        v-for="(chunk, index) in houseChunks"
                        :key="index"
                        @click="goToSlide(index)"
                        class="w-2 h-2 md:w-3 md:h-3 rounded-full transition-all"
                        :class="
                            currentIndex === index
                                ? 'bg-primary w-6 md:w-8'
                                : 'bg-gray-300 hover:bg-gray-400'
                        "
                        :aria-label="`Go to slide ${index + 1}`"
                    />
                </div>
            </div>

            <div v-if="featuredHouses.length > 0" class="text-center mt-12">
                <Link href="/houses" class="btn-secondary">
                    Xem tất cả nhà trọ
                </Link>
            </div>
        </div>
    </section>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from "vue";
import { Link } from "@inertiajs/vue3";
import HouseCard from "@/components/house/HouseCard.vue";
import { ChevronLeftIcon, ChevronRightIcon } from "@heroicons/vue/24/outline";

const props = defineProps({
    featuredHouses: {
        type: Array,
        default: () => [],
    },
});

const carouselRef = ref(null);
const currentIndex = ref(0);
const translateX = ref(0);
const isDragging = ref(false);
const startX = ref(0);
const currentX = ref(0);
const autoPlayInterval = ref(null);
const itemsPerSlide = ref(3); // Default for desktop

// Calculate items per slide based on screen size
const updateItemsPerSlide = () => {
    if (typeof window === 'undefined') return;
    if (window.innerWidth >= 1024) {
        itemsPerSlide.value = 3; // lg: 3 items
    } else if (window.innerWidth >= 768) {
        itemsPerSlide.value = 2; // md: 2 items
    } else {
        itemsPerSlide.value = 1; // sm: 1 item
    }
};

// Chunk houses into groups based on items per slide
const houseChunks = computed(() => {
    const chunks = [];
    for (let i = 0; i < props.featuredHouses.length; i += itemsPerSlide.value) {
        chunks.push(props.featuredHouses.slice(i, i + itemsPerSlide.value));
    }
    return chunks;
});

const totalChunks = computed(() => houseChunks.value.length);

// Update translateX based on currentIndex
const updateTranslateX = () => {
    if (!carouselRef.value) return;
    const containerWidth = carouselRef.value.offsetWidth;
    translateX.value = -(currentIndex.value * containerWidth);
};

// Mouse drag handlers
const handleMouseDown = (e) => {
    isDragging.value = true;
    startX.value = e.clientX - translateX.value;
    currentX.value = e.clientX;
    if (autoPlayInterval.value) {
        clearInterval(autoPlayInterval.value);
    }
    e.preventDefault();
};

const handleMouseMove = (e) => {
    if (!isDragging.value) return;
    currentX.value = e.clientX;
    translateX.value = currentX.value - startX.value;
    
    // Constrain dragging
    if (!carouselRef.value) return;
    const containerWidth = carouselRef.value.offsetWidth;
    const minTranslate = -(totalChunks.value - 1) * containerWidth;
    const maxTranslate = 0;
    translateX.value = Math.max(minTranslate, Math.min(maxTranslate, translateX.value));
};

const handleMouseUp = () => {
    if (!isDragging.value) return;
    isDragging.value = false;
    
    // Snap to nearest chunk
    if (!carouselRef.value) return;
    const containerWidth = carouselRef.value.offsetWidth;
    
    // Calculate which chunk to snap to
    const draggedDistance = -translateX.value;
    const newIndex = Math.round(draggedDistance / containerWidth);
    const clampedIndex = Math.max(0, Math.min(newIndex, totalChunks.value - 1));
    
    currentIndex.value = clampedIndex;
    updateTranslateX();
    startAutoPlay();
};

// Touch handlers
const touchStartX = ref(0);
const touchStartY = ref(0);

const handleTouchStart = (e) => {
    if (autoPlayInterval.value) {
        clearInterval(autoPlayInterval.value);
    }
    const touch = e.touches[0];
    touchStartX.value = touch.clientX;
    touchStartY.value = touch.clientY;
    startX.value = touch.clientX - translateX.value;
    isDragging.value = true;
};

const handleTouchMove = (e) => {
    if (!isDragging.value) return;
    const touch = e.touches[0];
    const deltaX = touch.clientX - touchStartX.value;
    const deltaY = touch.clientY - touchStartY.value;
    
    // Only allow horizontal scrolling if horizontal movement is greater than vertical
    if (Math.abs(deltaX) > Math.abs(deltaY)) {
        e.preventDefault();
        currentX.value = touch.clientX;
        translateX.value = currentX.value - startX.value;
        
        // Constrain dragging
        if (!carouselRef.value) return;
        const containerWidth = carouselRef.value.offsetWidth;
        const minTranslate = -(totalChunks.value - 1) * containerWidth;
        const maxTranslate = 0;
        translateX.value = Math.max(minTranslate, Math.min(maxTranslate, translateX.value));
    }
};

const handleTouchEnd = () => {
    if (!isDragging.value) return;
    isDragging.value = false;
    
    // Snap to nearest chunk
    if (!carouselRef.value) return;
    const containerWidth = carouselRef.value.offsetWidth;
    
    // Calculate which chunk to snap to
    const draggedDistance = -translateX.value;
    const newIndex = Math.round(draggedDistance / containerWidth);
    const clampedIndex = Math.max(0, Math.min(newIndex, totalChunks.value - 1));
    
    currentIndex.value = clampedIndex;
    updateTranslateX();
    startAutoPlay();
};

// Navigation functions
const nextSlide = () => {
    if (currentIndex.value < totalChunks.value - 1) {
        currentIndex.value++;
        updateTranslateX();
        resetAutoPlay();
    }
};

const previousSlide = () => {
    if (currentIndex.value > 0) {
        currentIndex.value--;
        updateTranslateX();
        resetAutoPlay();
    }
};

const goToSlide = (index) => {
    if (index >= 0 && index < totalChunks.value) {
        currentIndex.value = index;
        updateTranslateX();
        resetAutoPlay();
    }
};

// Auto-play
const startAutoPlay = () => {
    if (autoPlayInterval.value) {
        clearInterval(autoPlayInterval.value);
    }
    
    if (totalChunks.value > 1) {
        autoPlayInterval.value = setInterval(() => {
            if (currentIndex.value < totalChunks.value - 1) {
                nextSlide();
            } else {
                // Reset to beginning
                currentIndex.value = 0;
                updateTranslateX();
            }
        }, 5000);
    }
};

const resetAutoPlay = () => {
    startAutoPlay();
};

// Handle resize
const handleResize = () => {
    updateItemsPerSlide();
    // Reset to first slide when resizing to avoid index out of bounds
    if (currentIndex.value >= totalChunks.value) {
        currentIndex.value = Math.max(0, totalChunks.value - 1);
    }
    updateTranslateX();
};

onMounted(() => {
    updateItemsPerSlide();
    updateTranslateX();
    startAutoPlay();
    window.addEventListener("resize", handleResize);
    
    // Update on next tick to ensure DOM is ready
    nextTick(() => {
        updateTranslateX();
    });
});

onUnmounted(() => {
    if (autoPlayInterval.value) {
        clearInterval(autoPlayInterval.value);
    }
    window.removeEventListener("resize", handleResize);
});
</script>
