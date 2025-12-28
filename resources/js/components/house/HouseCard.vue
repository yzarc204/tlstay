<template>
  <div class="card group cursor-pointer" @click="goToDetail">
    <!-- Image -->
    <div class="relative overflow-hidden h-56">
      <img
        :src="house.image"
        :alt="house.name"
        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
      />
      <div class="absolute top-4 left-4">
                <span class="badge bg-primary text-white">
                    {{ house.availableRooms }} phòng trống
                </span>
      </div>
            <div class="absolute top-4 right-4" @click.stop>
        <button
                    @click="toggleWishlist"
                    :class="[
                        'w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300 shadow-md hover:shadow-lg',
                        isInWishlist
                            ? 'bg-secondary text-white'
                            : 'bg-white hover:bg-primary hover:text-white',
                    ]"
        >
                    <HeartIcon
                        :class="['w-5 h-5', isInWishlist ? 'fill-current' : '']"
                    />
        </button>
      </div>
    </div>

    <!-- Content -->
    <div class="p-5">
      <!-- Title -->
            <h3
                class="text-xl font-bold text-gray-900 mb-2 group-hover:text-primary transition-colors"
            >
        {{ house.name }}
      </h3>

      <!-- Location -->
      <div class="flex items-start space-x-2 text-gray-600 mb-4">
        <MapPinIcon class="w-5 h-5 mt-0.5 flex-shrink-0" />
        <span class="text-sm">{{ house.address }}</span>
      </div>

      <!-- Features -->
            <div
                class="flex items-center space-x-4 text-sm text-gray-600 mb-4 pb-4 border-b"
            >
        <div class="flex items-center space-x-1">
          <BuildingOfficeIcon class="w-5 h-5" />
          <span>{{ house.floors }} tầng</span>
        </div>
        <div class="flex items-center space-x-1">
          <HomeIcon class="w-5 h-5" />
          <span>{{ house.totalRooms }} phòng</span>
        </div>
        <div class="flex items-center space-x-1">
          <StarIconSolid class="w-5 h-5 text-yellow-400" />
          <span>{{ house.rating }}</span>
        </div>
      </div>

      <!-- Price & Action -->
      <div class="flex items-center justify-between">
        <div>
          <p class="text-2xl font-bold text-primary">
            {{ formatPrice(calculateMonthlyPrice()) }}
                        <span class="text-sm text-gray-500 font-normal"
                            >/tháng</span
                        >
          </p>
                    <p class="text-sm text-gray-500">
                        {{ formatPrice(house.pricePerDay) }}/ngày
                    </p>
        </div>
        <button
          class="px-5 py-2.5 bg-primary text-white rounded-lg hover:bg-secondary transition-all duration-300 font-medium shadow-md hover:shadow-lg transform hover:-translate-y-0.5"
        >
          Xem chi tiết
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import {
    HeartIcon,
    MapPinIcon,
    BuildingOfficeIcon,
    HomeIcon,
    StarIcon,
} from "@heroicons/vue/24/outline";
import { StarIcon as StarIconSolid } from "@heroicons/vue/24/solid";
import axios from "axios";

const page = usePage();
const props = defineProps({
  house: {
    type: Object,
    required: true,
  },
});

const isInWishlist = ref(props.house.isInWishlist || false);
const isLoading = ref(false);

// Kiểm tra wishlist status khi component mount
onMounted(async () => {
    if (page.props.auth?.user) {
        try {
            const response = await axios.get("/wishlist/check", {
                params: { house_id: props.house.id },
            });
            isInWishlist.value = response.data.isInWishlist;
        } catch (error) {
            // Ignore error, user might not be logged in
        }
    }
});

const toggleWishlist = async (e) => {
    e.stopPropagation();

    if (!page.props.auth?.user) {
        router.visit("/login");
        return;
    }

    if (isLoading.value) return;

    isLoading.value = true;

    try {
        const response = await axios.post("/wishlist/toggle", {
            house_id: props.house.id,
        });

        isInWishlist.value = response.data.isInWishlist;

        // Show toast notification (if available)
        if (window.flash) {
            window.flash(
                response.data.message,
                response.data.isInWishlist ? "success" : "info"
            );
        }
    } catch (error) {
        console.error("Error toggling wishlist:", error);
        if (error.response?.status === 401) {
            router.visit("/login");
        }
    } finally {
        isLoading.value = false;
    }
};

const formatPrice = (price) => {
    return new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
    }).format(price);
};

// Tính giá theo tháng: sử dụng giá tháng có sẵn hoặc tính từ giá ngày
const calculateMonthlyPrice = () => {
  // Nếu có giá tháng riêng thì dùng
  if (props.house.pricePerMonth) {
        return props.house.pricePerMonth;
  }
  // Nếu không thì tính từ giá ngày × 30
    if (!props.house.pricePerDay) return 0;
    return Math.round(props.house.pricePerDay * 30);
};

const goToDetail = () => {
    router.visit(`/houses/${props.house.id}`);
};
</script>
