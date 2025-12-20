import { confirmStore } from '@/stores/confirm'

export function useConfirm() {
  return {
    show: (options) => {
      return confirmStore.show(options)
    },
    confirm: (message, options = {}) => {
      return confirmStore.show({
        message,
        ...options
      })
    },
  }
}
