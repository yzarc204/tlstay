<template>
    <Head title="Danh sách yêu thích" />
    <AppLayout>
        <div class="wishlist py-12 bg-light min-h-screen">
            <div class="container mx-auto px-4">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-4xl font-bold text-secondary mb-2">
                        Danh sách yêu thích
                    </h1>
                    <p class="text-gray-600">Các nhà trọ bạn đã lưu</p>
                </div>

                <!-- Empty State -->
                <div v-if="wishlists.length === 0" class="text-center py-20">
                    <HeartIcon class="w-24 h-24 text-gray-300 mx-auto mb-4" />
                    <h3 class="text-2xl font-semibold text-gray-700 mb-2">
                        Chưa có nhà trọ yêu thích
                    </h3>
                    <p class="text-gray-500 mb-6">
                        Hãy khám phá và thêm các nhà trọ bạn quan tâm vào danh
                        sách yêu thích
                    </p>
                    <Link
                        href="/houses"
                        class="inline-flex items-center px-6 py-3 bg-primary text-white rounded-lg hover:bg-secondary transition-colors"
                    >
                        <HomeIcon class="w-5 h-5 mr-2" />
                        Khám phá nhà trọ
                    </Link>
                </div>

                <!-- Wishlist Grid -->
                <div
                    v-else
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
                >
                    <div
                        v-for="wishlist in wishlists"
                        :key="wishlist.id"
                        class="card group relative"
                    >
                        <!-- Image -->
                        <div class="relative overflow-hidden h-56">
                            <img
                                :src="
                                    wishlist.house.image ||
                                    'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800'
                                "
                                :alt="wishlist.house.name"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500 cursor-pointer"
                                @click="goToDetail(wishlist.house.id)"
                            />
                            <div class="absolute top-4 left-4">
                                <span class="badge bg-primary text-white">
                                    {{ wishlist.house.availableRooms }} phòng
                                    trống
                                </span>
                            </div>
                            <div class="absolute top-4 right-4">
                                <button
                                    @click="removeFromWishlist(wishlist.id)"
                                    class="w-10 h-10 bg-white rounded-full flex items-center justify-center hover:bg-red-500 hover:text-white transition-all duration-300 shadow-md hover:shadow-lg"
                                    :disabled="isRemoving === wishlist.id"
                                >
                                    <HeartIcon
                                        v-if="isRemoving !== wishlist.id"
                                        class="w-5 h-5 fill-current text-red-500"
                                    />
                                    <svg
                                        v-else
                                        class="animate-spin h-5 w-5 text-red-500"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                    >
                                        <circle
                                            class="opacity-25"
                                            cx="12"
                                            cy="12"
                                            r="10"
                                            stroke="currentColor"
                                            stroke-width="4"
                                        ></circle>
                                        <path
                                            class="opacity-75"
                                            fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                        ></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-5">
                            <!-- Title -->
                            <h3
                                class="text-xl font-bold text-gray-900 mb-2 group-hover:text-primary transition-colors cursor-pointer"
                                @click="goToDetail(wishlist.house.id)"
                            >
                                {{ wishlist.house.name }}
                            </h3>

                            <!-- Location -->
                            <div
                                class="flex items-start space-x-2 text-gray-600 mb-4"
                            >
                                <MapPinIcon
                                    class="w-5 h-5 mt-0.5 flex-shrink-0"
                                />
                                <span class="text-sm">{{
                                    wishlist.house.address
                                }}</span>
                            </div>

                            <!-- Rating -->
                            <div
                                class="flex items-center space-x-4 text-sm text-gray-600 mb-4 pb-4 border-b"
                            >
                                <div class="flex items-center space-x-1">
                                    <StarIconSolid
                                        class="w-5 h-5 text-yellow-400"
                                    />
                                    <span>{{ wishlist.house.rating }}</span>
                                    <span class="text-gray-400"
                                        >({{ wishlist.house.reviews }})</span
                                    >
                                </div>
                                <div class="flex items-center space-x-1">
                                    <HomeIcon class="w-5 h-5" />
                                    <span
                                        >{{
                                            wishlist.house.totalRooms
                                        }}
                                        phòng</span
                                    >
                                </div>
                            </div>

                            <!-- Price & Action -->
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-2xl font-bold text-primary">
                                        {{
                                            formatPrice(
                                                calculateMonthlyPrice(
                                                    wishlist.house
                                                )
                                            )
                                        }}
                                        <span
                                            class="text-sm text-gray-500 font-normal"
                                            >/tháng</span
                                        >
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        {{
                                            formatPrice(
                                                wishlist.house.pricePerDay
                                            )
                                        }}/ngày
                                    </p>
                                </div>
                                <button
                                    @click="goToDetail(wishlist.house.id)"
                                    class="px-5 py-2.5 bg-primary text-white rounded-lg hover:bg-secondary transition-all duration-300 font-medium shadow-md hover:shadow-lg transform hover:-translate-y-0.5"
                                >
                                    Xem chi tiết
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import AppLayout from "@/layouts/AppLayout.vue";
import {
    HeartIcon,
    MapPinIcon,
    HomeIcon,
    StarIcon,
} from "@heroicons/vue/24/outline";
import { StarIcon as StarIconSolid } from "@heroicons/vue/24/solid";
import axios from "axios";

const page = usePage();

const props = defineProps({
    wishlists: {
        type: Array,
        default: () => [],
    },
});

const isRemoving = ref(null);

const formatPrice = (price) => {
    return new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
    }).format(price);
};

const calculateMonthlyPrice = (house) => {
    if (house.pricePerMonth) {
        return house.pricePerMonth;
    }
    if (!house.pricePerDay) return 0;
    return Math.round(house.pricePerDay * 30);
};

const goToDetail = (houseId) => {
    router.visit(`/houses/${houseId}`);
};

const removeFromWishlist = async (wishlistId) => {
    if (isRemoving.value) return;

    isRemoving.value = wishlistId;

    try {
        await axios.delete(`/wishlist/${wishlistId}`);

        // Reload page to update wishlist
        router.reload({
            only: ["wishlists"],
            preserveScroll: true,
        });
    } catch (error) {
        console.error("Error removing from wishlist:", error);
    } finally {
        isRemoving.value = null;
    }
};
</script>
