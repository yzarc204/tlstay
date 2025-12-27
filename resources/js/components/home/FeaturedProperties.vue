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

            <div
                v-else
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8"
            >
                <HouseCard
                    v-for="house in featuredHouses"
                    :key="house.id"
                    :house="house"
                />
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
import { Link } from "@inertiajs/vue3";
import HouseCard from "@/components/house/HouseCard.vue";

defineProps({
    featuredHouses: {
        type: Array,
        default: () => [],
    },
});
</script>
