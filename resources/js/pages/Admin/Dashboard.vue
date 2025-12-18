<template>
  <AdminLayout title="Dashboard">
    <div class="space-y-6">
      <!-- Welcome Section -->
      <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-2">
          Chào mừng đến với Admin Panel
        </h2>
        <p class="text-gray-600">
          Quản lý hệ thống nhà/phòng trọ Thăng Long Stay
        </p>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow-sm p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Tổng Nhà trọ</p>
              <p class="text-2xl font-bold text-gray-900 mt-1">0</p>
            </div>
            <div class="h-12 w-12 rounded-full bg-primary/10 flex items-center justify-center">
              <BuildingOffice2Icon class="h-6 w-6 text-primary" />
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Tổng Phòng</p>
              <p class="text-2xl font-bold text-gray-900 mt-1">0</p>
            </div>
            <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
              <Squares2X2Icon class="h-6 w-6 text-blue-600" />
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Đặt phòng</p>
              <p class="text-2xl font-bold text-gray-900 mt-1">0</p>
            </div>
            <div class="h-12 w-12 rounded-full bg-green-100 flex items-center justify-center">
              <CalendarIcon class="h-6 w-6 text-green-600" />
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Tài khoản</p>
              <p class="text-2xl font-bold text-gray-900 mt-1">0</p>
            </div>
            <div class="h-12 w-12 rounded-full bg-purple-100 flex items-center justify-center">
              <UsersIcon class="h-6 w-6 text-purple-600" />
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Thao tác nhanh</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <Link href="/admin/houses" class="inline-flex">
            <Button variant="outline" class="justify-start w-full">
              <BuildingOffice2Icon class="h-5 w-5 mr-2" />
              Thêm Nhà trọ mới
            </Button>
          </Link>
          <Link href="/admin/promotions" class="inline-flex">
            <Button variant="outline" class="justify-start w-full">
              <TicketIcon class="h-5 w-5 mr-2" />
              Tạo khuyến mãi
            </Button>
          </Link>
          <Link href="/admin/banners" class="inline-flex">
            <Button variant="outline" class="justify-start w-full">
              <PhotoIcon class="h-5 w-5 mr-2" />
              Quản lý Slider
            </Button>
          </Link>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import Button from '@/components/ui/Button.vue'
import {
  BuildingOffice2Icon,
  Squares2X2Icon,
  CalendarIcon,
  UsersIcon,
  TicketIcon,
  PhotoIcon
} from '@heroicons/vue/24/outline'
</script>
