<template>
  <header
    :class="cn(
      'sticky top-0 z-30 flex h-16 items-center gap-4 border-b bg-white px-4 lg:px-6',
      attrs.class
    )"
  >
    <button
      @click="$emit('toggle-sidebar')"
      class="lg:hidden"
    >
      <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
      </svg>
    </button>
    
    <div class="flex-1">
      <slot name="title">
        <h1 class="text-lg font-semibold">{{ title }}</h1>
      </slot>
    </div>
    
    <div class="flex items-center gap-4">
      <slot name="actions" />
      
      <div class="flex items-center gap-3">
        <div class="text-right">
          <p class="text-sm font-medium">{{ user?.name || 'Admin' }}</p>
          <p class="text-xs text-gray-500">{{ user?.email }}</p>
        </div>
        <button
          @click="logout"
          class="rounded-lg p-2 hover:bg-gray-100"
          title="Đăng xuất"
        >
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
          </svg>
        </button>
      </div>
    </div>
  </header>
</template>

<script setup>
import { computed, useAttrs } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { cn } from '@/lib/utils'

const props = defineProps({
  title: {
    type: String,
    default: 'Dashboard'
  }
})

defineEmits(['toggle-sidebar'])

const attrs = useAttrs()
const page = usePage()
const user = computed(() => page.props.auth?.user)

const logout = () => {
  router.post('/logout')
}
</script>
