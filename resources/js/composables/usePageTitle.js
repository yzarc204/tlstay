import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

/**
 * Composable để format page title với site name từ settings
 * @param {string} pageTitle - Tiêu đề của trang
 * @returns {computed} - Computed title đã format: "Tiêu đề trang - Tên website"
 */
export function usePageTitle(pageTitle) {
  const page = usePage()
  
  const siteName = computed(() => {
    return page.props?.siteSettings?.site_name || 'THANG LONG STAY'
  })
  
  const fullTitle = computed(() => {
    if (!pageTitle) {
      return siteName.value
    }
    return `${pageTitle} - ${siteName.value}`
  })
  
  return {
    siteName,
    fullTitle,
  }
}
