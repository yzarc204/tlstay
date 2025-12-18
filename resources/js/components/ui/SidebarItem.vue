<template>
  <Link
    :href="href"
    :class="cn(
      'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
      isActive
        ? 'bg-primary text-white'
        : 'text-gray-700 hover:bg-gray-100 hover:text-primary',
      attrs.class
    )"
  >
    <component :is="icon" v-if="icon" class="h-5 w-5" />
    <span>{{ label }}</span>
  </Link>
</template>

<script setup>
import { computed, useAttrs } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { cn } from '@/lib/utils'

const props = defineProps({
  href: {
    type: String,
    required: true
  },
  label: {
    type: String,
    required: true
  },
  icon: {
    type: [Object, Function],
    default: null
  }
})

const attrs = useAttrs()
const page = usePage()
const isActive = computed(() => {
  const currentUrl = page.url.split('?')[0] // Remove query string
  const href = props.href.split('?')[0] // Remove query string
  
  // Special handling for home page - only active if exactly "/"
  if (href === '/') {
    return currentUrl === '/'
  }
  
  // Normalize URLs (remove trailing slashes for comparison)
  const normalizedHref = href.endsWith('/') && href !== '/' ? href.slice(0, -1) : href
  const normalizedCurrentUrl = currentUrl.endsWith('/') && currentUrl !== '/' ? currentUrl.slice(0, -1) : currentUrl
  
  // Exact match
  if (normalizedCurrentUrl === normalizedHref) {
    return true
  }
  
  // Split into path segments
  const hrefParts = normalizedHref.split('/').filter(p => p)
  const currentParts = normalizedCurrentUrl.split('/').filter(p => p)
  
  // Only match sub-routes if href has more than 1 segment (not a root-level route)
  // This prevents /admin from matching /admin/users
  // But allows /admin/users to match /admin/users/1
  if (hrefParts.length > 1 && normalizedCurrentUrl.startsWith(normalizedHref + '/')) {
    // Only match if currentUrl is a sub-route (has more segments)
    return currentParts.length > hrefParts.length
  }
  
  return false
})
</script>
