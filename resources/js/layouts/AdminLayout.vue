<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Sidebar -->
    <Sidebar :is-open="sidebarOpen" @close="sidebarOpen = false">
      <SidebarItem
        href="/admin"
        label="Dashboard"
        :icon="HomeIcon"
      />
      <SidebarItem
        href="/admin/houses"
        label="Quản lý Nhà trọ"
        :icon="BuildingOffice2Icon"
      />
      <SidebarItem
        href="/admin/addresses"
        label="Quản lý Địa chỉ"
        :icon="MapPinIcon"
      />
      <SidebarItem
        href="/admin/bookings"
        label="Quản lý Đặt phòng"
        :icon="CalendarIcon"
      />
      <SidebarItem
        href="/admin/banners"
        label="Quản lý Slider"
        :icon="PhotoIcon"
      />
      <SidebarItem
        href="/admin/users"
        label="Quản lý Tài khoản"
        :icon="UsersIcon"
      />
      <SidebarItem
        href="/admin/company-information"
        label="Thông tin doanh nghiệp"
        :icon="BuildingOfficeIcon"
      />
      <SidebarItem
        href="/admin/settings"
        label="Cài đặt website"
        :icon="Cog6ToothIcon"
      />
      <SidebarItem
        href="/admin/god-system"
        label="God System"
        :icon="SparklesIcon"
      />
      
      <template #bottom>
        <SidebarItem
          href="/"
          label="Về trang chủ"
          :icon="HomeIcon"
        />
      </template>
    </Sidebar>

    <!-- Main Content -->
    <div class="lg:pl-80">
      <!-- Header -->
      <AdminHeader
        :title="title"
        @toggle-sidebar="sidebarOpen = !sidebarOpen"
      >
        <template #actions>
          <slot name="header-actions" />
        </template>
      </AdminHeader>

      <!-- Page Content -->
      <main class="p-4 lg:p-6">
        <slot />
      </main>
    </div>
    
    <!-- Toast Container -->
    <Toaster />
    
    <!-- Confirm Dialog -->
    <ConfirmDialog />
    
    <!-- Flash Messages Handler -->
    <FlashToaster />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import Sidebar from '@/components/ui/Sidebar.vue'
import SidebarItem from '@/components/ui/SidebarItem.vue'
import AdminHeader from '@/components/ui/Header.vue'
import Toaster from '@/components/ui/Toaster.vue'
import ConfirmDialog from '@/components/ui/ConfirmDialog.vue'
import FlashToaster from '@/components/FlashToaster.vue'
import {
  HomeIcon,
  BuildingOffice2Icon,
  BuildingOfficeIcon,
  PhotoIcon,
  UsersIcon,
  MapPinIcon,
  CalendarIcon,
  Cog6ToothIcon,
  SparklesIcon,
} from '@heroicons/vue/24/outline'

defineProps({
  title: {
    type: String,
    default: 'Dashboard'
  }
})

const sidebarOpen = ref(false)
</script>
