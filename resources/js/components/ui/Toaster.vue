<template>
  <Teleport to="body">
    <div class="toast-container">
      <TransitionGroup
        name="toast"
        tag="div"
        class="toast-list"
      >
        <div
          v-for="toast in toasts"
          :key="toast.id"
          :class="[
            'toast',
            `toast-${toast.type}`
          ]"
        >
          <div class="toast-content">
            <!-- Icon -->
            <div class="toast-icon">
              <CheckCircleIcon v-if="toast.type === 'success'" />
              <XCircleIcon v-else-if="toast.type === 'error'" />
              <InformationCircleIcon v-else-if="toast.type === 'info'" />
              <ExclamationTriangleIcon v-else-if="toast.type === 'warning'" />
            </div>
            
            <!-- Message -->
            <div class="toast-message">
              {{ toast.message }}
            </div>
            
            <!-- Close Button -->
            <button
              @click="removeToast(toast.id)"
              class="toast-close"
              aria-label="Đóng"
            >
              <XMarkIcon class="w-4 h-4" />
            </button>
          </div>
          
          <!-- Progress Bar -->
          <div
            v-if="toast.duration > 0"
            class="toast-progress"
            :style="{ animationDuration: `${toast.duration}ms` }"
          />
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script setup>
import { Teleport, TransitionGroup } from 'vue'
import { toastStore } from '@/stores/toast'
import {
  CheckCircleIcon,
  XCircleIcon,
  InformationCircleIcon,
  ExclamationTriangleIcon,
  XMarkIcon,
} from '@heroicons/vue/24/solid'

const toasts = toastStore.toasts

const removeToast = (id) => {
  toastStore.remove(id)
}
</script>

<style scoped>
.toast-container {
  position: fixed;
  top: 0;
  right: 0;
  z-index: 9999;
  padding: 1rem;
  pointer-events: none;
  max-width: 420px;
  width: 100%;
}

.toast-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.toast {
  position: relative;
  background: white;
  border-radius: 0.5rem;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  pointer-events: auto;
  overflow: hidden;
  min-width: 300px;
  max-width: 100%;
}

.toast-content {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 1rem;
}

.toast-icon {
  flex-shrink: 0;
  width: 1.25rem;
  height: 1.25rem;
  margin-top: 0.125rem;
}

.toast-success .toast-icon {
  color: #10b981;
}

.toast-error .toast-icon {
  color: #ef4444;
}

.toast-info .toast-icon {
  color: #3b82f6;
}

.toast-warning .toast-icon {
  color: #f59e0b;
}

.toast-message {
  flex: 1;
  font-size: 0.875rem;
  line-height: 1.5;
  color: #1f2937;
  word-wrap: break-word;
}

.toast-close {
  flex-shrink: 0;
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

.toast-close:hover {
  color: #374151;
  background: #f3f4f6;
}

.toast-progress {
  position: absolute;
  bottom: 0;
  left: 0;
  height: 3px;
  background: currentColor;
  animation: toast-progress linear forwards;
}

.toast-success .toast-progress {
  background: #10b981;
}

.toast-error .toast-progress {
  background: #ef4444;
}

.toast-info .toast-progress {
  background: #3b82f6;
}

.toast-warning .toast-progress {
  background: #f59e0b;
}

@keyframes toast-progress {
  from {
    width: 100%;
  }
  to {
    width: 0%;
  }
}

/* Toast Animations */
.toast-enter-active {
  transition: all 0.3s ease-out;
}

.toast-leave-active {
  transition: all 0.2s ease-in;
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(100%);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(100%);
}

.toast-move {
  transition: transform 0.3s ease;
}
</style>
