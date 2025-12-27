<template>
    <Head :title="house?.name || 'Chi tiết nhà trọ'" />
    <AppLayout>
        <div class="house-detail bg-light min-h-screen">
            <!-- Loading State -->
            <div v-if="!house" class="container mx-auto px-4 py-20 text-center">
                <div
                    class="inline-block animate-spin rounded-full h-16 w-16 border-4 border-primary border-t-transparent"
                ></div>
                <p class="mt-4 text-gray-600">Đang tải thông tin nhà trọ...</p>
            </div>

            <!-- Content -->
            <div v-else class="pb-12">
                <!-- Image Gallery -->
                <div
                    class="relative h-96 md:h-[500px] bg-gray-900 overflow-hidden"
                >
                    <img
                        :src="currentImage || house.image"
                        :alt="house.name"
                        class="w-full h-full object-cover transition-opacity duration-300"
                    />

                    <!-- Previous Button -->
                    <button
                        v-if="displayImages.length > 1"
                        @click="previousImage"
                        class="absolute left-4 top-1/2 transform -translate-y-1/2 w-12 h-12 bg-white/90 rounded-full flex items-center justify-center hover:bg-white transition-all z-20 shadow-lg"
                    >
                        <ChevronLeftIcon class="w-6 h-6 text-gray-800" />
                    </button>

                    <!-- Next Button -->
                    <button
                        v-if="displayImages.length > 1"
                        @click="nextImage"
                        class="absolute right-4 top-1/2 transform -translate-y-1/2 w-12 h-12 bg-white/90 rounded-full flex items-center justify-center hover:bg-white transition-all z-20 shadow-lg"
                    >
                        <ChevronRightIcon class="w-6 h-6 text-gray-800" />
                    </button>
                </div>

                <div class="container mx-auto px-4 -mt-20 relative z-10">
                    <div class="grid lg:grid-cols-3 gap-8">
                        <!-- Main Content -->
                        <div class="lg:col-span-2">
                            <!-- Info Card -->
                            <div class="card p-8 mb-6">
                                <div
                                    class="flex items-start justify-between mb-6"
                                >
                                    <div>
                                        <h1
                                            class="text-3xl md:text-4xl font-bold text-secondary mb-3"
                                        >
                                            {{ house.name }}
                                        </h1>
                                        <div
                                            class="flex items-center text-gray-600 mb-2"
                                        >
                                            <MapPinIcon class="w-5 h-5 mr-2" />
                                            <span>{{ house.address }}</span>
                                        </div>
                                        <div
                                            class="flex items-center space-x-4"
                                        >
                                            <div class="flex items-center">
                                                <StarIconSolid
                                                    class="w-5 h-5 text-yellow-400 mr-1"
                                                />
                                                <span class="font-semibold">{{
                                                    house.rating
                                                }}</span>
                                                <span class="text-gray-500 ml-1"
                                                    >({{
                                                        house.reviews
                                                    }}
                                                    reviews)</span
                                                >
                                            </div>
                                            <span
                                                class="badge bg-primary-100 text-primary-700"
                                            >
                                                {{ house.availableRooms }} phòng
                                                trống
                                            </span>
                                        </div>
                                    </div>
                                    <button
                                        @click="toggleWishlist"
                                        :class="[
                                            'w-12 h-12 rounded-full flex items-center justify-center transition-all',
                                            isInWishlist
                                                ? 'bg-secondary text-white'
                                                : 'bg-secondary-50 hover:bg-secondary hover:text-white',
                                        ]"
                                    >
                                        <HeartIcon
                                            :class="[
                                                'w-6 h-6',
                                                isInWishlist
                                                    ? 'fill-current'
                                                    : '',
                                            ]"
                                        />
                                    </button>
                                </div>

                                <!-- Quick Stats -->
                                <div
                                    class="grid grid-cols-3 gap-4 py-6 border-y"
                                >
                                    <div class="text-center">
                                        <div
                                            class="w-12 h-12 bg-primary-50 rounded-lg flex items-center justify-center mx-auto mb-2"
                                        >
                                            <BuildingOfficeIcon
                                                class="w-6 h-6 text-primary"
                                            />
                                        </div>
                                        <p
                                            class="text-2xl font-bold text-primary"
                                        >
                                            {{ house.floors }}
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            Tầng
                                        </p>
                                    </div>
                                    <div class="text-center">
                                        <div
                                            class="w-12 h-12 bg-primary-50 rounded-lg flex items-center justify-center mx-auto mb-2"
                                        >
                                            <HomeIcon
                                                class="w-6 h-6 text-primary"
                                            />
                                        </div>
                                        <p
                                            class="text-2xl font-bold text-primary"
                                        >
                                            {{ house.totalRooms }}
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            Tổng phòng
                                        </p>
                                    </div>
                                    <div class="text-center">
                                        <div
                                            class="w-12 h-12 bg-primary-50 rounded-lg flex items-center justify-center mx-auto mb-2"
                                        >
                                            <CheckCircleIcon
                                                class="w-6 h-6 text-primary"
                                            />
                                        </div>
                                        <p
                                            class="text-2xl font-bold text-primary"
                                        >
                                            {{ house.availableRooms }}
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            Còn trống
                                        </p>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="mt-6">
                                    <h2
                                        class="text-2xl font-bold text-secondary mb-4"
                                    >
                                        Mô tả
                                    </h2>
                                    <p class="text-gray-600 leading-relaxed">
                                        {{ house.description }}
                                    </p>
                                </div>

                                <!-- Amenities -->
                                <div class="mt-8">
                                    <h2
                                        class="text-2xl font-bold text-secondary mb-4"
                                    >
                                        Tiện nghi
                                    </h2>
                                    <div
                                        v-if="amenitiesList.length > 0"
                                        class="grid grid-cols-2 md:grid-cols-3 gap-4"
                                    >
                                        <div
                                            v-for="amenity in amenitiesList"
                                            :key="amenity"
                                            class="flex items-center space-x-3 p-3 bg-light rounded-lg hover:bg-gray-50 transition-colors"
                                        >
                                            <component
                                                :is="getAmenityIcon(amenity)"
                                                class="w-5 h-5 text-primary flex-shrink-0"
                                            />
                                            <span class="text-gray-700">{{
                                                getAmenityName(amenity)
                                            }}</span>
                                        </div>
                                    </div>
                                    <div
                                        v-else
                                        class="text-center py-8 text-gray-500"
                                    >
                                        <p>Chưa có thông tin tiện nghi</p>
                                    </div>
                                </div>

                                <!-- Service Prices (for monthly package) -->
                                <div class="mt-8">
                                    <h2
                                        class="text-2xl font-bold text-secondary mb-4"
                                    >
                                        Giá dịch vụ
                                    </h2>
                                    <p class="text-sm text-gray-600 mb-4">
                                        Áp dụng cho gói thuê theo tháng
                                    </p>
                                    <div
                                        class="grid grid-cols-1 md:grid-cols-2 gap-4"
                                    >
                                        <div
                                            class="flex items-center justify-between p-4 bg-light rounded-lg border border-gray-200"
                                        >
                                            <div
                                                class="flex items-center space-x-3"
                                            >
                                                <BoltIcon
                                                    class="w-6 h-6 text-yellow-500"
                                                />
                                                <div>
                                                    <p
                                                        class="font-semibold text-gray-800"
                                                    >
                                                        Điện
                                                    </p>
                                                    <p
                                                        class="text-sm text-gray-500"
                                                    >
                                                        Tính theo số điện
                                                    </p>
                                                </div>
                                            </div>
                                            <span
                                                class="font-bold text-secondary text-lg"
                                                >4.000đ / số</span
                                            >
                                        </div>
                                        <div
                                            class="flex items-center justify-between p-4 bg-light rounded-lg border border-gray-200"
                                        >
                                            <div
                                                class="flex items-center space-x-3"
                                            >
                                                <BeakerIcon
                                                    class="w-6 h-6 text-blue-500"
                                                />
                                                <div>
                                                    <p
                                                        class="font-semibold text-gray-800"
                                                    >
                                                        Nước
                                                    </p>
                                                    <p
                                                        class="text-sm text-gray-500"
                                                    >
                                                        Tính theo khối
                                                    </p>
                                                </div>
                                            </div>
                                            <span
                                                class="font-bold text-secondary text-lg"
                                                >25.000đ / khối</span
                                            >
                                        </div>
                                    </div>
                                </div>

                                <!-- Location Map -->
                                <div class="mt-8">
                                    <h2
                                        class="text-2xl font-bold text-secondary mb-4"
                                    >
                                        Vị trí
                                    </h2>
                                    <div
                                        v-if="house.latitude && house.longitude"
                                        class="rounded-xl overflow-hidden shadow-lg"
                                    >
                                        <iframe
                                            :src="mapEmbedUrl"
                                            width="100%"
                                            height="450"
                                            style="border: 0"
                                            allowfullscreen=""
                                            loading="lazy"
                                            referrerpolicy="no-referrer-when-downgrade"
                                            class="w-full"
                                        ></iframe>
                                    </div>
                                    <div
                                        v-else
                                        class="bg-gray-200 rounded-xl h-80 flex items-center justify-center"
                                    >
                                        <div class="text-center">
                                            <MapIcon
                                                class="w-16 h-16 text-gray-400 mx-auto mb-2"
                                            />
                                            <p class="text-gray-500">
                                                Thông tin vị trí chưa có sẵn
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Reviews Section -->
                                <div class="mt-8">
                                    <h2
                                        class="text-2xl font-bold text-secondary mb-4"
                                    >
                                        Đánh giá ({{ reviews?.length || 0 }})
                                    </h2>

                                    <!-- Review Form (only show if user can review) -->
                                    <ReviewForm
                                        v-if="
                                            canReview &&
                                            isAuthenticated &&
                                            reviewableBookingId
                                        "
                                        :booking-id="reviewableBookingId"
                                        :house-id="house.id"
                                        @review-created="handleReviewCreated"
                                    />

                                    <!-- Reviews List -->
                                    <div
                                        v-if="reviews && reviews.length > 0"
                                        class="space-y-6 mt-6"
                                    >
                                        <ReviewItem
                                            v-for="review in reviews"
                                            :key="review.id"
                                            :review="review"
                                        />
                                    </div>
                                    <div
                                        v-else
                                        class="text-center py-8 text-gray-500"
                                    >
                                        <p>Chưa có đánh giá nào</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar -->
                        <div class="lg:col-span-1">
                            <!-- Pricing Card -->
                            <div class="card p-6 sticky top-24">
                                <div class="mb-6">
                                    <p class="text-sm text-gray-600 mb-2">
                                        Giá thuê
                                    </p>
                                    <div class="space-y-3">
                                        <div
                                            class="flex items-center justify-between p-3 bg-light rounded-lg"
                                        >
                                            <span class="text-gray-700"
                                                >Theo ngày</span
                                            >
                                            <span
                                                class="font-bold text-secondary"
                                                >{{
                                                    formatPrice(
                                                        house.pricePerDay
                                                    )
                                                }}</span
                                            >
                                        </div>
                                        <div
                                            class="flex items-center justify-between p-3 bg-light rounded-lg"
                                        >
                                            <span class="text-gray-700"
                                                >Theo tuần</span
                                            >
                                            <div
                                                class="text-right flex items-center gap-2"
                                            >
                                                <span
                                                    class="font-bold text-secondary"
                                                    >{{
                                                        formatPrice(
                                                            pricePerWeek
                                                        )
                                                    }}</span
                                                >
                                                <span
                                                    v-if="
                                                        hasCustomWeekPrice &&
                                                        weekSavingsPercent > 0
                                                    "
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-secondary-100 text-secondary-800"
                                                >
                                                    Tiết kiệm
                                                    {{ weekSavingsPercent }}%
                                                </span>
                                            </div>
                                        </div>
                                        <div
                                            class="flex items-center justify-between p-3 bg-primary/10 rounded-lg border-2 border-primary"
                                        >
                                            <span class="text-gray-700"
                                                >Theo tháng</span
                                            >
                                            <div
                                                class="text-right flex items-center gap-2"
                                            >
                                                <span
                                                    class="font-bold text-primary text-lg"
                                                    >{{
                                                        formatPrice(
                                                            pricePerMonth
                                                        )
                                                    }}</span
                                                >
                                                <span
                                                    v-if="
                                                        hasCustomMonthPrice &&
                                                        monthSavingsPercent > 0
                                                    "
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-secondary-100 text-secondary-800"
                                                >
                                                    Tiết kiệm
                                                    {{ monthSavingsPercent }}%
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <button
                                    @click="goToBooking"
                                    class="w-full btn-primary mb-3"
                                >
                                    <CalendarIcon class="w-5 h-5 inline mr-2" />
                                    Đặt phòng ngay
                                </button>

                                <button class="w-full btn-outline">
                                    <PhoneIcon class="w-5 h-5 inline mr-2" />
                                    Liên hệ
                                </button>

                                <p
                                    v-if="!isAuthenticated"
                                    class="text-sm text-center text-gray-500 mt-4"
                                >
                                    <Link
                                        href="/login"
                                        class="text-primary hover:text-secondary font-medium"
                                    >
                                        Đăng nhập
                                    </Link>
                                    để đặt phòng
                                </p>

                                <!-- Contact Info -->
                                <div
                                    v-if="house.owner"
                                    class="mt-6 pt-6 border-t"
                                >
                                    <h3
                                        class="font-semibold text-gray-800 mb-3"
                                    >
                                        Thông tin liên hệ
                                    </h3>
                                    <div
                                        class="space-y-2 text-sm text-gray-600"
                                    >
                                        <p
                                            class="font-semibold text-gray-900 mb-2"
                                        >
                                            {{ house.owner.name }}
                                        </p>
                                        <p
                                            v-if="house.owner.phone"
                                            class="flex items-center"
                                        >
                                            <PhoneIcon class="w-4 h-4 mr-2" />
                                            <a
                                                :href="`tel:${house.owner.phone}`"
                                                class="hover:text-primary"
                                            >
                                                {{ house.owner.phone }}
                                            </a>
                                        </p>
                                        <p
                                            v-if="house.owner.email"
                                            class="flex items-center"
                                        >
                                            <EnvelopeIcon
                                                class="w-4 h-4 mr-2"
                                            />
                                            <a
                                                :href="`mailto:${house.owner.email}`"
                                                class="hover:text-primary break-all"
                                            >
                                                {{ house.owner.email }}
                                            </a>
                                        </p>
                                    </div>
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
import { ref, computed, watch } from "vue";
import { Head, router, Link, usePage } from "@inertiajs/vue3";
import AppLayout from "@/layouts/AppLayout.vue";
import { useAuth } from "@/composables/useAuth";
import { getAmenityIcon, getAmenityName } from "@/utils/amenityIcons";
import ReviewForm from "@/components/reviews/ReviewForm.vue";
import ReviewItem from "@/components/reviews/ReviewItem.vue";
import axios from "axios";

const page = usePage();
import {
    ChevronLeftIcon,
    ChevronRightIcon,
    MapPinIcon,
    StarIcon as StarIconSolid,
    HeartIcon,
    BuildingOfficeIcon,
    HomeIcon,
    CheckCircleIcon,
    BoltIcon,
    ChatBubbleLeftRightIcon,
    CalendarIcon,
    PhoneIcon,
    EnvelopeIcon,
    MapIcon,
    BeakerIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    house: {
        type: Object,
        default: null,
    },
    rooms: {
        type: Array,
        default: () => [],
    },
    reviews: {
        type: Array,
        default: () => [],
    },
    canReview: {
        type: Boolean,
        default: false,
    },
    reviewableBookingId: {
        type: Number,
        default: null,
    },
});

const { isAuthenticated } = useAuth();
const currentImage = ref("");
const currentImageIndex = ref(0);
const isInWishlist = ref(props.house?.isInWishlist || false);
const isLoadingWishlist = ref(false);

// Convert amenities to array format (support both array and object)
const amenitiesList = computed(() => {
    if (!props.house?.amenities) return [];

    // If it's already an array, return it
    if (Array.isArray(props.house.amenities)) {
        return props.house.amenities.filter(Boolean);
    }

    // If it's an object, convert to array of keys where value is truthy
    if (typeof props.house.amenities === "object") {
        return Object.keys(props.house.amenities).filter(
            (key) => props.house.amenities[key]
        );
    }

    return [];
});

// Initialize current image when house data is available
watch(
    () => props.house,
    (newHouse) => {
        if (newHouse) {
            // Get display images: prioritize featured_images, then images
            const images =
                newHouse.featured_images &&
                Array.isArray(newHouse.featured_images) &&
                newHouse.featured_images.length > 0
                    ? newHouse.featured_images
                    : Array.isArray(newHouse.images) &&
                      newHouse.images.length > 0
                    ? newHouse.images
                    : [];

            // Set current image: use first from display images, or fallback to house.image
            if (images.length > 0) {
                currentImage.value = images[0];
                currentImageIndex.value = 0;
            } else {
                currentImage.value = newHouse.image || "";
                currentImageIndex.value = 0;
            }
        }
    },
    { immediate: true }
);

// Tính giá tuần: sử dụng giá tuần có sẵn hoặc tính từ giá ngày × 7
const pricePerWeek = computed(() => {
    if (!props.house) return 0;
    // Nếu có giá tuần riêng thì dùng
    if (props.house.pricePerWeek) {
        return props.house.pricePerWeek;
    }
    // Nếu không thì tính từ giá ngày × 7
    return Math.round((props.house.pricePerDay || 0) * 7);
});

// Tính giá tháng: sử dụng giá tháng có sẵn hoặc tính từ giá ngày × 30
const pricePerMonth = computed(() => {
    if (!props.house) return 0;
    // Nếu có giá tháng riêng thì dùng
    if (props.house.pricePerMonth) {
        return props.house.pricePerMonth;
    }
    // Nếu không thì tính từ giá ngày × 30
    return Math.round((props.house.pricePerDay || 0) * 30);
});

// Kiểm tra xem có giá ưu đãi riêng không
const hasCustomWeekPrice = computed(() => {
    if (!props.house) return false;
    return !!props.house.pricePerWeek;
});

const hasCustomMonthPrice = computed(() => {
    if (!props.house) return false;
    return !!props.house.pricePerMonth;
});

// Tính phần trăm tiết kiệm cho tuần
// Công thức: ((Giá gốc - Giá ưu đãi) / Giá gốc) × 100
// Giá gốc = giá ngày × 7
// Chỉ trả về số dương (nếu giá ưu đãi > giá gốc thì không có tiết kiệm)
const weekSavingsPercent = computed(() => {
    if (!props.house || !hasCustomWeekPrice.value || !props.house.pricePerDay)
        return 0;
    const fullWeekPrice = props.house.pricePerDay * 7;
    if (fullWeekPrice <= 0) return 0;
    // Nếu giá ưu đãi >= giá gốc thì không có tiết kiệm
    if (props.house.pricePerWeek >= fullWeekPrice) return 0;
    const savings =
        ((fullWeekPrice - props.house.pricePerWeek) / fullWeekPrice) * 100;
    return Math.max(0, Math.round(savings));
});

// Tính phần trăm tiết kiệm cho tháng
// Công thức: ((Giá gốc - Giá ưu đãi) / Giá gốc) × 100
// Giá gốc = giá ngày × 30
// Chỉ trả về số dương (nếu giá ưu đãi > giá gốc thì không có tiết kiệm)
const monthSavingsPercent = computed(() => {
    if (!props.house || !hasCustomMonthPrice.value || !props.house.pricePerDay)
        return 0;
    const fullMonthPrice = props.house.pricePerDay * 30;
    if (fullMonthPrice <= 0) return 0;
    // Nếu giá ưu đãi >= giá gốc thì không có tiết kiệm
    if (props.house.pricePerMonth >= fullMonthPrice) return 0;
    const savings =
        ((fullMonthPrice - props.house.pricePerMonth) / fullMonthPrice) * 100;
    return Math.max(0, Math.round(savings));
});

// Tạo URL cho Google Maps embed
const mapEmbedUrl = computed(() => {
    if (!props.house?.latitude || !props.house?.longitude) {
        return "";
    }
    const lat = props.house.latitude;
    const lng = props.house.longitude;
    // Sử dụng Google Maps embed với tọa độ
    return `https://www.google.com/maps?q=${lat},${lng}&hl=vi&z=15&output=embed`;
});

// Get display images (featured_images if available, otherwise all images)
// If no array images available, use house.image as fallback
const displayImages = computed(() => {
    if (!props.house) return [];

    // Prioritize featured_images
    if (
        props.house.featured_images &&
        Array.isArray(props.house.featured_images) &&
        props.house.featured_images.length > 0
    ) {
        return props.house.featured_images;
    }

    // Fallback to images array
    if (
        props.house.images &&
        Array.isArray(props.house.images) &&
        props.house.images.length > 0
    ) {
        return props.house.images;
    }

    // If no array images, use house.image as single-item array for consistency
    if (props.house.image) {
        return [props.house.image];
    }

    return [];
});

// Hàm chuyển đến ảnh tiếp theo
const nextImage = () => {
    const images = displayImages.value;
    if (!images || images.length === 0) return;
    currentImageIndex.value = (currentImageIndex.value + 1) % images.length;
    currentImage.value = images[currentImageIndex.value];
};

// Hàm chuyển đến ảnh trước
const previousImage = () => {
    const images = displayImages.value;
    if (!images || images.length === 0) return;
    currentImageIndex.value =
        currentImageIndex.value === 0
            ? images.length - 1
            : currentImageIndex.value - 1;
    currentImage.value = images[currentImageIndex.value];
};

// Hàm chuyển đến ảnh cụ thể
const goToImage = (index) => {
    const images = displayImages.value;
    if (!images || images.length === 0) return;
    if (index >= 0 && index < images.length) {
        currentImageIndex.value = index;
        currentImage.value = images[index];
    }
};

const formatPrice = (price) => {
    return new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
    }).format(price);
};

const goToBooking = () => {
    if (props.house?.id) {
        router.visit(`/booking/${props.house.id}`);
    }
};

const handleReviewCreated = () => {
    // Reload page to show new review and update canReview status
    // This will hide the form if user can no longer review
    router.reload({
        only: ["reviews", "canReview", "reviewableBookingId"],
        preserveScroll: true,
    });
};

const toggleWishlist = async () => {
    if (!isAuthenticated.value) {
        router.visit("/login");
        return;
    }

    if (isLoadingWishlist.value || !props.house?.id) return;

    isLoadingWishlist.value = true;

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
        isLoadingWishlist.value = false;
    }
};

// Watch house prop to update wishlist status
watch(
    () => props.house,
    (newHouse) => {
        if (newHouse) {
            isInWishlist.value = newHouse.isInWishlist || false;
        }
    },
    { immediate: true }
);
</script>
