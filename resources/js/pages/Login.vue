<template>
  <Head title="Đăng nhập" />
  <div
    class="min-h-screen flex items-center justify-center bg-gradient-to-br from-secondary to-primary py-12 px-4 sm:px-6 lg:px-8"
  >
    <div class="max-w-md w-full">
      <!-- Logo -->
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-white/20 rounded-2xl mb-4">
          <span class="text-white font-bold text-2xl">RT</span>
        </div>
        <h2 class="text-3xl font-bold text-white mb-2">Chào mừng trở lại</h2>
        <p class="text-white/80">Đăng nhập để tiếp tục với TL STAY</p>
      </div>

      <!-- Login Form -->
      <div class="bg-white rounded-2xl shadow-2xl p-8">
        <form @submit.prevent="handleLogin" method="POST" action="/login">
          <!-- Email -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Địa chỉ email</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <EnvelopeIcon class="h-5 w-5 text-gray-400" />
              </div>
              <input
                v-model="form.email"
                type="email"
                name="email"
                required
                class="input-field pl-10"
                placeholder="user@example.com"
                :class="{ 'border-red-500': errorMessage }"
              />
            </div>
          </div>

          <!-- Password -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Mật khẩu</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <LockClosedIcon class="h-5 w-5 text-gray-400" />
              </div>
              <input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                name="password"
                required
                class="input-field pl-10 pr-10"
                placeholder="••••••••"
                :class="{ 'border-red-500': errorMessage }"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute inset-y-0 right-0 pr-3 flex items-center"
              >
                <EyeIcon v-if="!showPassword" class="h-5 w-5 text-gray-400" />
                <EyeSlashIcon v-else class="h-5 w-5 text-gray-400" />
              </button>
            </div>
          </div>

          <!-- Remember & Forgot -->
          <div class="flex items-center justify-between mb-6">
            <label class="flex items-center">
              <input
                v-model="form.remember"
                type="checkbox"
                name="remember"
                class="rounded border-gray-300 text-primary focus:ring-primary"
              />
              <span class="ml-2 text-sm text-gray-600">Ghi nhớ đăng nhập</span>
            </label>
            <a href="#" class="text-sm text-primary hover:text-secondary font-medium"
              >Quên mật khẩu?</a
            >
          </div>

          <!-- Error Message -->
          <div v-if="errorMessage" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-sm text-red-600">{{ errorMessage }}</p>
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="loading"
            class="w-full btn-primary disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="!loading">Đăng nhập</span>
            <span v-else class="flex items-center justify-center">
              <ArrowPathIcon class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" />
              Đang đăng nhập...
            </span>
          </button>
        </form>

        <!-- Divider -->
        <div class="relative my-6">
          <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-300"></div>
          </div>
          <div class="relative flex justify-center text-sm">
            <span class="px-2 bg-white text-gray-500">Hoặc đăng nhập bằng</span>
          </div>
        </div>

        <!-- Social Login -->
        <div class="grid grid-cols-2 gap-3">
          <button
            class="flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
          >
            <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24">
              <path
                fill="#4285F4"
                d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
              />
              <path
                fill="#34A853"
                d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
              />
              <path
                fill="#FBBC05"
                d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"
              />
              <path
                fill="#EA4335"
                d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
              />
            </svg>
            <span class="text-sm font-medium text-gray-700">Google</span>
          </button>
          <button
            class="flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
          >
            <svg class="w-5 h-5 mr-2" fill="#1877F2" viewBox="0 0 24 24">
              <path
                d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"
              />
            </svg>
            <span class="text-sm font-medium text-gray-700">Facebook</span>
          </button>
        </div>

        <!-- Sign Up Link -->
        <p class="mt-6 text-center text-sm text-gray-600">
          Chưa có tài khoản?
          <Link href="/register" class="font-medium text-primary hover:text-secondary">
            Đăng ký ngay
          </Link>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, router, Link, usePage } from '@inertiajs/vue3'
import { useAuthStore } from '@/stores/auth'
import { EnvelopeIcon, LockClosedIcon, EyeIcon, EyeSlashIcon, ArrowPathIcon } from '@heroicons/vue/24/outline'

const authStore = useAuthStore()
const page = usePage()

const form = ref({
  email: '',
  password: '',
  remember: false,
})

const showPassword = ref(false)
const loading = ref(false)

// Lấy error từ Inertia page props
const errorMessage = computed(() => {
  const errors = page.props.errors
  if (errors?.email) {
    return Array.isArray(errors.email) ? errors.email[0] : errors.email
  }
  return ''
})

const handleLogin = () => {
  loading.value = true
  
  authStore.login(
    {
      email: form.value.email,
      password: form.value.password,
      remember: form.value.remember,
    },
    // onSuccess
    () => {
      loading.value = false
      // Redirect sẽ được xử lý bởi controller
    },
    // onError
    () => {
      loading.value = false
    }
  )
}
</script>
