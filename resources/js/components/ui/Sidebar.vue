<template>
  <aside
    :class="cn(
      'fixed left-0 top-0 z-40 h-screen w-80 border-r bg-white transition-transform',
      isOpen ? 'translate-x-0' : '-translate-x-full',
      'lg:translate-x-0',
      attrs.class
    )"
  >
    <div class="flex h-full flex-col">
      <div class="flex h-16 items-center border-b px-6">
        <h2 class="text-xl font-bold text-primary">Admin Panel</h2>
        <button
          @click="$emit('close')"
          class="ml-auto lg:hidden"
        >
          <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      <nav class="flex-1 space-y-1 px-3 py-4 overflow-y-auto">
        <slot />
      </nav>
      
      <!-- Bottom section with home link -->
      <div class="border-t border-gray-200 px-3 py-2 mt-auto">
        <slot name="bottom" />
      </div>
    </div>
  </aside>
  <div
    v-if="isOpen"
    @click="$emit('close')"
    class="fixed inset-0 z-30 bg-black/50 lg:hidden"
  />
</template>

<script setup>
import { useAttrs } from 'vue'
import { cn } from '@/lib/utils'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  }
})

defineEmits(['close'])

const attrs = useAttrs()
</script>
