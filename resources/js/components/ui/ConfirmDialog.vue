<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition-opacity duration-150"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-150"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="currentDialog"
        class="confirm-backdrop"
        @click.self="handleBackdropClick"
      >
        <Transition
          enter-active-class="transition-all duration-150 ease-out"
          enter-from-class="opacity-0 scale-95 translate-y-[-10px]"
          enter-to-class="opacity-100 scale-100 translate-y-0"
          leave-active-class="transition-all duration-100 ease-in"
          leave-from-class="opacity-100 scale-100 translate-y-0"
          leave-to-class="opacity-0 scale-95 translate-y-[-10px]"
        >
          <div
            v-if="currentDialog"
            class="confirm-dialog"
            @click.stop
          >
            <!-- Header -->
            <div class="confirm-header">
              <h3 class="confirm-title">{{ currentDialog.title }}</h3>
              <button
                @click="handleCancel"
                class="confirm-close"
                aria-label="Đóng"
                type="button"
              >
                <XMarkIcon class="w-5 h-5" />
              </button>
            </div>
            
            <!-- Body -->
            <div class="confirm-body">
              <p class="confirm-message">{{ currentDialog.message }}</p>
            </div>
            
            <!-- Footer -->
            <div class="confirm-footer">
              <button
                @click="handleCancel"
                :class="[
                  'confirm-button',
                  `confirm-button-${currentDialog.cancelVariant}`
                ]"
                type="button"
              >
                {{ currentDialog.cancelText }}
              </button>
              <button
                @click="handleConfirm"
                :class="[
                  'confirm-button',
                  `confirm-button-${currentDialog.confirmVariant}`
                ]"
                type="button"
              >
                {{ currentDialog.confirmText }}
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { Teleport, Transition, computed } from 'vue'
import { confirmStore } from '@/stores/confirm'
import { XMarkIcon } from '@heroicons/vue/24/outline'

// Use computed to get current dialog - watch dialogs array for reactivity
const currentDialog = computed(() => {
  // Access dialogs.value to ensure reactivity tracking
  const dialogs = confirmStore.dialogs.value
  return dialogs.length > 0 ? dialogs[dialogs.length - 1] : null
})

// Memoize handlers to avoid recreating on each render
const handleConfirm = () => {
  const dialog = currentDialog.value
  if (dialog?.onConfirm) {
    dialog.onConfirm()
  }
}

const handleCancel = () => {
  const dialog = currentDialog.value
  if (dialog?.onCancel) {
    dialog.onCancel()
  }
}

const handleBackdropClick = () => {
  handleCancel()
}
</script>

<style scoped>
.confirm-backdrop {
  position: fixed;
  inset: 0;
  z-index: 9998;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  /* Performance optimization */
  will-change: opacity;
  transform: translateZ(0);
}

.confirm-dialog {
  background: white;
  border-radius: 0.75rem;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  width: 100%;
  max-width: 500px;
  overflow: hidden;
  /* Performance optimization */
  will-change: transform, opacity;
  transform: translateZ(0);
}

.confirm-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #e5e7eb;
}

.confirm-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: #111827;
  margin: 0;
}

.confirm-close {
  padding: 0.25rem;
  color: #6b7280;
  background: transparent;
  border: none;
  cursor: pointer;
  border-radius: 0.25rem;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.confirm-close:hover {
  color: #374151;
  background: #f3f4f6;
}

.confirm-body {
  padding: 1.5rem;
}

.confirm-message {
  font-size: 0.875rem;
  line-height: 1.5;
  color: #4b5563;
  margin: 0;
}

.confirm-footer {
  display: flex;
  gap: 0.75rem;
  padding: 1rem 1.5rem;
  border-top: 1px solid #e5e7eb;
  justify-content: flex-end;
}

.confirm-button {
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
  font-weight: 500;
  border-radius: 0.5rem;
  border: none;
  cursor: pointer;
  /* Optimize transition - only transition necessary properties */
  transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease;
  min-width: 80px;
  /* Performance optimization */
  will-change: background-color, border-color, color;
}

.confirm-button-outline {
  background: white;
  color: #374151;
  border: 1px solid #d1d5db;
}

.confirm-button-outline:hover {
  background: #f9fafb;
  border-color: #9ca3af;
}

.confirm-button-primary {
  background: #000066;
  color: white;
}

.confirm-button-primary:hover {
  background: #000055;
}

.confirm-button-danger {
  background: #ef4444;
  color: white;
}

.confirm-button-danger:hover {
  background: #dc2626;
}

.confirm-button-success {
  background: #10b981;
  color: white;
}

.confirm-button-success:hover {
  background: #059669;
}

/* Animations are handled by Vue Transition component */
</style>
