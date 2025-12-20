<template>
  <Head title="Không tìm thấy trang" />
  <AppLayout>
    <div class="min-h-[80vh] flex items-center justify-center py-12 px-4 bg-white">
      <div class="container mx-auto max-w-7xl">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
          <!-- Left Content -->
          <div class="text-left space-y-6">
            <!-- Main Title -->
            <h1 class="text-6xl md:text-7xl lg:text-8xl font-bold text-primary leading-tight">
              Ooops...
            </h1>
            
            <!-- Subtitle -->
            <p class="text-xl md:text-2xl text-gray-700 font-medium">
              Xin lỗi, chúng tôi không tìm thấy trang này
            </p>

            <!-- Back Button -->
            <div class="pt-4">
              <button
                @click="goBack"
                class="inline-flex items-center space-x-2 px-6 py-2.5 text-sm font-medium text-primary border-2 border-primary rounded-lg hover:bg-primary hover:text-white transition-all duration-300"
              >
                <ArrowLeftIcon class="w-4 h-4" />
                <span>Quay lại</span>
              </button>
            </div>
          </div>

          <!-- Right Illustration -->
          <div class="flex items-center justify-center lg:justify-end">
            <div class="relative w-full max-w-lg">
              <!-- 404 Illustration Image -->
              <img
                :src="image404"
                alt="Hình minh họa 404 - chiếc xe màu trắng xanh bên gốc cây"
                class="w-full h-full object-contain"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'
import image404 from '@images/404.webp'

const goBack = () => {
  if (window.history.length > 1) {
    window.history.back()
  } else {
    router.visit('/')
  }
}
</script>

<style scoped>
/* Ensure font family is applied */
h1, p, button {
  font-family: 'Thăng Long Sans', system-ui, -apple-system, sans-serif;
}
</style>
