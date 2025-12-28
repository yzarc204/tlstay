<template>
    <Head title="Đăng ký" />
    <div
        class="min-h-screen flex items-center justify-center bg-gradient-to-br from-secondary to-primary py-12 px-4 sm:px-6 lg:px-8"
    >
        <div class="max-w-md w-full">
            <!-- Logo -->
            <div class="text-center mb-8">
                <div
                    class="inline-flex items-center justify-center w-16 h-16 bg-white/20 rounded-2xl mb-4"
                >
                    <span class="text-white font-bold text-2xl">TL</span>
                </div>
                <h2 class="text-3xl font-bold text-white mb-2">
                    Tạo tài khoản
                </h2>
                <p class="text-white/80">Tham gia TL STAY ngay hôm nay</p>
            </div>

            <!-- Register Form -->
            <div class="bg-white rounded-2xl shadow-2xl p-8">
                <form @submit.prevent="handleRegister">
                    <!-- Full Name -->
                    <div class="mb-4">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Họ và tên</label
                        >
                        <div class="relative">
                            <div
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                            >
                                <UserIcon class="h-5 w-5 text-gray-400" />
                            </div>
                            <input
                                v-model="form.name"
                                type="text"
                                required
                                class="input-field pl-10"
                                placeholder="Nguyễn Văn A"
                            />
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="mb-4">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Địa chỉ email</label
                        >
                        <div class="relative">
                            <div
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                            >
                                <EnvelopeIcon class="h-5 w-5 text-gray-400" />
                            </div>
                            <input
                                v-model="form.email"
                                type="email"
                                required
                                class="input-field pl-10"
                                placeholder="user@example.com"
                            />
                        </div>
                    </div>

                    <!-- Phone -->
                    <div class="mb-4">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Số điện thoại</label
                        >
                        <div class="relative">
                            <div
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                            >
                                <PhoneIcon class="h-5 w-5 text-gray-400" />
                            </div>
                            <input
                                v-model="form.phone"
                                type="tel"
                                required
                                class="input-field pl-10"
                                placeholder="0123456789 hoặc +84123456789"
                            />
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            Ví dụ: 0123456789 hoặc +84123456789
                        </p>
                    </div>

                    <!-- Password -->
                    <div class="mb-4">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Mật khẩu</label
                        >
                        <div class="relative">
                            <div
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                            >
                                <LockClosedIcon class="h-5 w-5 text-gray-400" />
                            </div>
                            <input
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                class="input-field pl-10 pr-10"
                                placeholder="••••••••"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center"
                            >
                                <EyeIcon
                                    v-if="!showPassword"
                                    class="h-5 w-5 text-gray-400"
                                />
                                <EyeSlashIcon
                                    v-else
                                    class="h-5 w-5 text-gray-400"
                                />
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            Tối thiểu 6 ký tự
                        </p>
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-6">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Xác nhận mật khẩu</label
                        >
                        <div class="relative">
                            <div
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                            >
                                <LockClosedIcon class="h-5 w-5 text-gray-400" />
                            </div>
                            <input
                                v-model="form.confirmPassword"
                                :type="
                                    showConfirmPassword ? 'text' : 'password'
                                "
                                required
                                class="input-field pl-10 pr-10"
                                placeholder="••••••••"
                            />
                            <button
                                type="button"
                                @click="
                                    showConfirmPassword = !showConfirmPassword
                                "
                                class="absolute inset-y-0 right-0 pr-3 flex items-center"
                            >
                                <EyeIcon
                                    v-if="!showConfirmPassword"
                                    class="h-5 w-5 text-gray-400"
                                />
                                <EyeSlashIcon
                                    v-else
                                    class="h-5 w-5 text-gray-400"
                                />
                            </button>
                        </div>
                    </div>

                    <!-- Terms -->
                    <div class="mb-6">
                        <label class="flex items-start">
                            <input
                                type="checkbox"
                                required
                                class="mt-1 rounded border-gray-300 text-primary focus:ring-primary"
                            />
                            <span class="ml-2 text-sm text-gray-600">
                                Tôi đồng ý với
                                <Link
                                    href="/terms"
                                    target="_blank"
                                    class="text-primary hover:text-secondary underline font-medium"
                                    >Điều khoản dịch vụ</Link
                                >
                                và
                                <Link
                                    href="/privacy"
                                    target="_blank"
                                    class="text-primary hover:text-secondary underline font-medium"
                                    >Chính sách bảo mật</Link
                                >
                            </span>
                        </label>
                    </div>

                    <!-- Error Message -->
                    <div
                        v-if="errorMessage"
                        class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg"
                    >
                        <p class="text-sm text-red-600">{{ errorMessage }}</p>
                    </div>

                    <!-- Success Message -->
                    <div
                        v-if="successMessage"
                        class="mb-4 p-3 bg-primary-50 border border-primary-200 rounded-lg"
                    >
                        <p class="text-sm text-primary">{{ successMessage }}</p>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="loading"
                        class="w-full btn-primary disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="!loading">Tạo tài khoản</span>
                        <span v-else class="flex items-center justify-center">
                            <ArrowPathIcon
                                class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                            />
                            Đang tạo tài khoản...
                        </span>
                    </button>
                </form>

                <!-- Sign In Link -->
                <p class="mt-6 text-center text-sm text-gray-600">
                    Đã có tài khoản?
                    <Link
                        href="/login"
                        class="font-medium text-primary hover:text-secondary"
                    >
                        Đăng nhập
                    </Link>
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import { Head, router, Link, usePage } from "@inertiajs/vue3";
import { useAuthStore } from "@/stores/auth";
import {
    ArrowPathIcon,
    UserIcon,
    EnvelopeIcon,
    PhoneIcon,
    LockClosedIcon,
    EyeIcon,
    EyeSlashIcon,
} from "@heroicons/vue/24/outline";

const authStore = useAuthStore();
const page = usePage();

const form = ref({
    name: "",
    email: "",
    phone: "",
    password: "",
    confirmPassword: "",
});

const showPassword = ref(false);
const showConfirmPassword = ref(false);
const loading = ref(false);
const errorMessage = ref("");
const successMessage = ref("");

const handleRegister = async () => {
    errorMessage.value = "";
    successMessage.value = "";

    // Validate form fields
    if (!form.value.name?.trim()) {
        errorMessage.value = "Vui lòng nhập họ tên";
        return;
    }

    if (!form.value.email?.trim()) {
        errorMessage.value = "Vui lòng nhập email";
        return;
    }

    if (!form.value.phone?.trim()) {
        errorMessage.value = "Vui lòng nhập số điện thoại";
        return;
    }

    // Validate email format
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(form.value.email)) {
        errorMessage.value = "Email không hợp lệ";
        return;
    }

    // Validate phone format (Vietnamese phone numbers)
    const phoneRegex = /^(0|\+84)[1-9][0-9]{8,9}$/;
    const cleanPhone = form.value.phone.replace(/\s+/g, "");
    if (!phoneRegex.test(cleanPhone)) {
        errorMessage.value =
            "Số điện thoại không hợp lệ (ví dụ: 0123456789 hoặc +84123456789)";
        return;
    }

    // Validate passwords match
    if (form.value.password !== form.value.confirmPassword) {
        errorMessage.value = "Mật khẩu xác nhận không khớp";
        return;
    }

    // Validate password length
    if (form.value.password.length < 6) {
        errorMessage.value = "Mật khẩu phải có ít nhất 6 ký tự";
        return;
    }

    loading.value = true;
    errorMessage.value = "";
    successMessage.value = "";

    // Gọi register với callbacks
    authStore.register(
        {
            name: form.value.name,
            email: form.value.email,
            phone: form.value.phone,
            password: form.value.password,
            confirmPassword: form.value.confirmPassword,
        },
        // onSuccess
        (page) => {
            // Sync user vào store
            if (page.props?.auth?.user) {
                authStore.user = page.props.auth.user;
            }
            successMessage.value = "Đăng ký thành công! Đang chuyển hướng...";
            loading.value = false;
            // Redirect sẽ được xử lý bởi controller
        },
        // onError
        (message) => {
            errorMessage.value = message || "Đã có lỗi xảy ra khi đăng ký";
            loading.value = false;
        }
    );
};
</script>
