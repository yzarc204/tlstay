<template>
  <!-- This component handles flash messages automatically -->
</template>

<script setup>
import { watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useToast } from '@/composables/useToast'

const page = usePage()
const toast = useToast()

// Watch for flash messages and show toast
watch(
  () => page.props.flash,
  (flash) => {
    if (!flash) return

    if (flash.success) {
      toast.success(flash.success)
    }
    if (flash.error) {
      toast.error(flash.error)
    }
    if (flash.info) {
      toast.info(flash.info)
    }
    if (flash.warning) {
      toast.warning(flash.warning)
    }
  },
  { deep: true, immediate: true }
)
</script>
