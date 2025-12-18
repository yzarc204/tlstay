<template>
  <div class="relative inline-block group">
    <slot />
    <div
      v-if="show"
      :class="[
        'absolute z-50 px-2 py-1 text-xs font-medium text-white bg-gray-900 rounded shadow-lg whitespace-nowrap',
        position === 'top' ? 'bottom-full left-1/2 -translate-x-1/2 mb-2' : '',
        position === 'bottom' ? 'top-full left-1/2 -translate-x-1/2 mt-2' : '',
        position === 'left' ? 'right-full top-1/2 -translate-y-1/2 mr-2' : '',
        position === 'right' ? 'left-full top-1/2 -translate-y-1/2 ml-2' : '',
      ]"
    >
      {{ text }}
      <div
        v-if="position === 'top'"
        class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-900"
      ></div>
      <div
        v-if="position === 'bottom'"
        class="absolute bottom-full left-1/2 -translate-x-1/2 border-4 border-transparent border-b-gray-900"
      ></div>
      <div
        v-if="position === 'left'"
        class="absolute left-full top-1/2 -translate-y-1/2 border-4 border-transparent border-l-gray-900"
      ></div>
      <div
        v-if="position === 'right'"
        class="absolute right-full top-1/2 -translate-y-1/2 border-4 border-transparent border-r-gray-900"
      ></div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

defineProps({
  text: {
    type: String,
    required: true,
  },
  position: {
    type: String,
    default: 'top',
    validator: (value) => ['top', 'bottom', 'left', 'right'].includes(value),
  },
  show: {
    type: Boolean,
    default: false,
  },
})
</script>

<style scoped>
.group:hover .group-hover\:block {
  display: block;
}
</style>
