import { toastStore } from '@/stores/toast'

export function useToast() {
  return {
    success: (message, options) => {
      return toastStore.success(message, options)
    },
    error: (message, options) => {
      return toastStore.error(message, options)
    },
    info: (message, options) => {
      return toastStore.info(message, options)
    },
    warning: (message, options) => {
      return toastStore.warning(message, options)
    },
    default: (message, options) => {
      return toastStore.add({ message, ...options })
    },
  }
}
