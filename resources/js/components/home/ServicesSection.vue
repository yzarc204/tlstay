<template>
  <section class="py-20 bg-white">
    <div class="container mx-auto px-4">
      <div class="text-center mb-12">
        <span class="text-primary font-semibold mb-2 block"
          >Dịch vụ</span
        >
        <h2 class="text-4xl font-bold text-secondary mb-4">
          Quảng bá nhà trọ của bạn để bán hoặc cho thuê với
          các dịch vụ của chúng tôi
        </h2>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div
          class="text-center p-8 rounded-2xl hover:shadow-xl transition-all bg-light"
        >
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
          <h3 class="text-xl font-bold mb-3 text-secondary">
            Tìm kiếm dễ dàng
          </h3>
          <p class="text-gray-600">
            Tìm căn phòng trọ hoàn hảo với bộ lọc tìm kiếm
            tiên tiến của chúng tôi
          </p>
        </div>

        <div
          class="text-center p-8 rounded-2xl hover:shadow-xl transition-all bg-light"
        >
          <div
            class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4"
          >
            <ShieldCheckIcon class="w-8 h-8 text-primary" />
          </div>
          <h3 class="text-xl font-bold mb-3 text-secondary">
            Thanh toán an toàn
          </h3>
          <p class="text-gray-600">
            Thanh toán 100% an toàn với nhiều phương thức
            thanh toán
          </p>
        </div>

        <div
          class="text-center p-8 rounded-2xl hover:shadow-xl transition-all bg-light"
        >
          <div
            class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4"
          >
            <Cog6ToothIcon class="w-8 h-8 text-primary" />
          </div>
          <h3 class="text-xl font-bold mb-3 text-secondary">
            Hỗ trợ 24/7
          </h3>
          <p class="text-gray-600">
            Hỗ trợ khách hàng 24/7 để phục vụ bạn tốt nhất
          </p>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ShieldCheckIcon, Cog6ToothIcon } from '@heroicons/vue/24/outline'
</script>
