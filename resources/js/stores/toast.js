import { ref } from 'vue'

export const toastStore = {
  toasts: ref([]),
  
  add(toast) {
    const id = Date.now() + Math.random()
    const newToast = {
      id,
      type: toast.type || 'default',
      message: toast.message,
      duration: toast.duration || 3000,
      ...toast
    }
    
    this.toasts.value.push(newToast)
    
    // Auto remove after duration
    if (newToast.duration > 0) {
      setTimeout(() => {
        this.remove(id)
      }, newToast.duration)
    }
    
    return id
  },
  
  remove(id) {
    const index = this.toasts.value.findIndex(t => t.id === id)
    if (index > -1) {
      this.toasts.value.splice(index, 1)
    }
  },
  
  success(message, options = {}) {
    return this.add({ type: 'success', message, ...options })
  },
  
  error(message, options = {}) {
    return this.add({ type: 'error', message, ...options })
  },
  
  info(message, options = {}) {
    return this.add({ type: 'info', message, ...options })
  },
  
  warning(message, options = {}) {
    return this.add({ type: 'warning', message, ...options })
  },
  
  clear() {
    this.toasts.value = []
  }
}
