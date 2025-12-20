<template>
  <!-- Backdrop - Teleported to body to ensure full coverage -->
  <Teleport to="body">
    <Transition
      enter-active-class="transition-opacity duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-300"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="isOpen"
        @click="$emit('close')"
        class="fixed inset-0 z-40 bg-black/50"
        style="top: 0; left: 0; right: 0; bottom: 0; width: 100%; height: 100%; min-height: 100vh;"
      />
    </Transition>
  </Teleport>

  <!-- Side Panel -->
  <Teleport to="body">
    <Transition
      enter-active-class="transition-transform duration-300 ease-out"
      enter-from-class="translate-x-full"
      enter-to-class="translate-x-0"
      leave-active-class="transition-transform duration-300 ease-in"
      leave-from-class="translate-x-0"
      leave-to-class="translate-x-full"
    >
      <aside
        v-if="isOpen"
        :class="cn(
          'fixed right-0 top-0 z-50 h-screen w-full md:w-1/2 bg-white shadow-xl overflow-y-auto',
          attrs.class
        )"
      >
        <slot />
      </aside>
    </Transition>
  </Teleport>
</template>

<script setup>
import { useAttrs } from 'vue'
import { cn } from '@/lib/utils'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false,
  },
})

defineEmits(['close'])

const attrs = useAttrs()
</script>
