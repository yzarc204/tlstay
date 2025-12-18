import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

export function useAuth() {
  const page = usePage()

  const user = computed(() => page.props.auth?.user || null)
  const isAuthenticated = computed(() => !!user.value)
  const isManager = computed(() => {
    const role = user.value?.role?.toString().trim().toLowerCase()
    return role === 'manager'
  })
  const isCustomer = computed(() => {
    const role = user.value?.role?.toString().trim().toLowerCase()
    return role === 'customer'
  })

  return {
    user,
    isAuthenticated,
    isManager,
    isCustomer,
  }
}
