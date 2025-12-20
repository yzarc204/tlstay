import { shallowRef } from 'vue'

export const confirmStore = {
  // Use shallowRef for better performance - we don't need deep reactivity
  dialogs: shallowRef([]),
  
  show(options) {
    return new Promise((resolve, reject) => {
      const id = Date.now() + Math.random()
      
      // Pre-compute default values to avoid repeated checks
      const dialog = {
        id,
        title: options.title ?? 'Xác nhận',
        message: options.message ?? 'Bạn có chắc chắn muốn thực hiện hành động này?',
        confirmText: options.confirmText ?? 'Xác nhận',
        cancelText: options.cancelText ?? 'Hủy',
        confirmVariant: options.confirmVariant ?? 'danger',
        cancelVariant: options.cancelVariant ?? 'outline',
        onConfirm: () => {
          this.remove(id)
          resolve(true)
        },
        onCancel: () => {
          this.remove(id)
          reject(new Error('USER_CANCELLED'))
        },
      }
      
      // Create new array reference to trigger reactivity with shallowRef
      this.dialogs.value = [...this.dialogs.value, dialog]
    })
  },
  
  remove(id) {
    const dialogs = this.dialogs.value
    const index = dialogs.findIndex(d => d.id === id)
    if (index > -1) {
      // Create new array for reactivity (shallowRef still needs new reference)
      this.dialogs.value = dialogs.filter(d => d.id !== id)
    }
  },
  
  clear() {
    this.dialogs.value = []
  },
  
}
