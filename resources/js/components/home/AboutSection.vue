<template>
    <section class="py-20 bg-white pt-32 md:pt-40">
        <div class="container mx-auto px-4">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div>
                    <span class="text-primary font-semibold mb-2 block"
                        >Về chúng tôi</span
                    >
                    <h2 class="text-4xl font-bold mb-6 text-secondary">
                        Hơn 500 căn phòng trọ có sẵn để thuê trong bán kính 5km
                        từ Trường Đại học Thăng Long
                    </h2>
                    <p class="text-gray-600 mb-6 leading-relaxed">
                        TL STAY là nền tảng cho thuê phòng trọ hàng đầu tại Hà
                        Nội, phục vụ nhu cầu lưu trú ngắn hạn và dài hạn trong
                        bán kính 5km từ Trường Đại học Thăng Long. Hãy bắt đầu
                        hành trình tìm kiếm chỗ ở lý tưởng của bạn, nơi bạn có
                        thể dễ dàng khám phá và quản lý những căn phòng yêu
                        thích.
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
                    <div
                        class="absolute -bottom-6 -left-6 bg-white p-4 rounded-xl shadow-xl"
                    >
                        <div class="flex items-center space-x-3">
                            <div
                                class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center"
                            >
                                <MapIcon class="w-6 h-6 text-primary" />
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Có mặt tại</p>
                                <p class="font-bold text-secondary">
                                    5+ phường
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { MapIcon } from "@heroicons/vue/24/outline";
</script>
