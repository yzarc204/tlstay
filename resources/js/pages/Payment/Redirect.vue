<template>
  <Head title="Đang chuyển hướng thanh toán" />
  <div class="min-h-screen bg-gray-50 flex items-center justify-center">
    <div class="text-center">
      <div class="inline-block animate-spin rounded-full h-16 w-16 border-4 border-primary border-t-transparent mb-4"></div>
      <p class="text-gray-600 mb-2">Đang chuyển hướng đến cổng thanh toán VNPay...</p>
      <p class="text-sm text-gray-500">Vui lòng đợi trong giây lát</p>
    </div>
  </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import { onMounted } from 'vue'

const props = defineProps({
  paymentUrl: {
    type: String,
    required: true,
  },
})

onMounted(() => {
  // Redirect to VNPay immediately
  if (props.paymentUrl) {
    window.location.href = props.paymentUrl
  }
})
</script>
