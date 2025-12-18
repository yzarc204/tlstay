import { defineStore } from 'pinia'
import { router } from '@inertiajs/vue3'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
  }),

  getters: {
    isAuthenticated(state) {
      return !!state.user
    },
    currentUser(state) {
      return state.user
    },
  },

  actions: {
    /**
     * Sync user từ Inertia page props
     */
    syncUser() {
      // Lấy user từ window (Inertia sẽ inject vào)
      if (typeof window !== 'undefined' && window.Inertia) {
        const page = window.Inertia.page
        if (page?.props?.auth?.user) {
          this.user = page.props.auth.user
        }
      }
    },

    login(formData, onSuccess, onError) {
      // Sử dụng Inertia router để submit form
      router.post('/login', formData, {
        preserveScroll: true,
        onSuccess: (page) => {
          // Lưu user vào state từ page props
          if (page.props?.auth?.user) {
            this.user = page.props.auth.user
          }
          if (onSuccess) {
            onSuccess(page)
          }
        },
        onError: (errors) => {
          // Xử lý lỗi validation
          const firstError = Object.values(errors)[0]
          const errorMessage = Array.isArray(firstError) ? firstError[0] : firstError
          if (onError) {
            onError(errorMessage || 'Email hoặc mật khẩu không đúng')
          }
        },
      })
    },
    
    /**
     * Sync user từ Inertia page props (gọi sau khi page load)
     */
    syncUserFromPage() {
      if (typeof window !== 'undefined' && window.Inertia) {
        try {
          const page = window.Inertia.page
          if (page?.props?.auth?.user) {
            this.user = page.props.auth.user
          } else {
            this.user = null
          }
        } catch (e) {
          // Ignore
        }
      }
    },

    register(userData, onSuccess, onError) {
      // Prepare user data for Laravel
      const formData = {
        name: userData.name.trim(),
        email: userData.email.toLowerCase().trim(),
        phone: userData.phone?.replace(/\s+/g, '') || '',
        password: userData.password,
        password_confirmation: userData.confirmPassword || userData.password,
      }

      // Sử dụng Inertia router để submit form
      router.post('/register', formData, {
        preserveScroll: true,
        onSuccess: (page) => {
          // Lưu user vào state từ page props
          if (page.props?.auth?.user) {
            this.user = page.props.auth.user
          }
          if (onSuccess) {
            onSuccess(page)
          }
        },
        onError: (errors) => {
          // Xử lý lỗi validation
          const firstError = Object.values(errors)[0]
          const errorMessage = Array.isArray(firstError) ? firstError[0] : firstError
          if (onError) {
            onError(errorMessage || 'Đã có lỗi xảy ra khi đăng ký')
          }
        },
      })
    },

    logout(onSuccess) {
      // Sử dụng Inertia router để logout
      router.post('/logout', {}, {
        preserveScroll: false,
        onSuccess: () => {
          this.user = null
          if (onSuccess) {
            onSuccess()
          }
        },
      })
    },
  },
})
