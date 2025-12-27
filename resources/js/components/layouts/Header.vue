<template>
    <nav class="bg-primary text-white shadow-lg relative">
        <div
            class="container mx-auto px-4 py-3 flex justify-between items-center relative"
        >
            <Link
                href="/"
                class="flex items-center space-x-2 hover:opacity-80 transition cursor-pointer"
            >
                <img
                    v-if="siteLogo"
                    :src="siteLogo"
                    :alt="siteName"
                    class="h-10 w-auto object-contain"
                />
                <span v-if="siteLogo && logoShowText" class="text-xl font-bold">
                    {{ siteName }}
                </span>
                <span v-else-if="!siteLogo" class="text-xl font-bold">{{
                    siteName
                }}</span>
            </Link>
            <div class="hidden md:flex space-x-6 items-center">
                <Link
                    v-if="!authStore.isAuthenticated"
                    href="/login"
                    class="px-4 py-2 bg-white text-primary rounded-lg hover:bg-secondary hover:text-white transition-all font-medium"
                >
                    Đăng nhập
                </Link>

                <!-- User Dropdown -->
                <div
                    v-if="authStore.isAuthenticated"
                    class="relative user-dropdown"
                >
                    <button
                        @click="userDropdownOpen = !userDropdownOpen"
                        class="flex items-center space-x-2 px-3 py-2 rounded-lg hover:bg-white/10 transition-all"
                    >
                        <img
                            :src="getAvatarUrl(authStore.currentUser)"
                            :alt="authStore.currentUser?.name"
                            class="w-8 h-8 rounded-full border-2 border-white object-cover"
                            @error="handleAvatarError"
                        />
                        <span class="font-medium hidden lg:block">{{
                            authStore.currentUser?.name
                        }}</span>
                        <ChevronDownIcon
                            class="w-4 h-4 transition-transform"
                            :class="{ 'rotate-180': userDropdownOpen }"
                        />
                    </button>

                    <!-- Dropdown Menu -->
                    <div
                        v-if="userDropdownOpen"
                        class="absolute right-0 mt-2 w-auto min-w-48 max-w-xs bg-white rounded-lg shadow-lg py-2 z-50"
                    >
                        <div class="px-4 py-2 border-b border-gray-200">
                            <p class="font-semibold text-gray-900 break-words">
                                {{ authStore.currentUser?.name }}
                            </p>
                            <p class="text-sm text-gray-500 break-words">
                                {{ authStore.currentUser?.email }}
                            </p>
                        </div>
                        <Link
                            v-if="isManager"
                            href="/admin"
                            @click="userDropdownOpen = false"
                            class="flex items-center space-x-2 px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors"
                        >
                            <Cog6ToothIcon class="w-5 h-5 text-primary" />
                            <span>Quản lí</span>
                        </Link>
                        <Link
                            href="/my-rentals"
                            @click="userDropdownOpen = false"
                            class="flex items-center space-x-2 px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors"
                        >
                            <HomeIcon class="w-5 h-5" />
                            <span>Phòng của tôi</span>
                        </Link>
                        <Link
                            href="/wishlist"
                            @click="userDropdownOpen = false"
                            class="flex items-center space-x-2 px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors"
                        >
                            <HeartIcon class="w-5 h-5 text-red-500" />
                            <span>Yêu thích</span>
                        </Link>
                        <Link
                            href="/profile"
                            @click="userDropdownOpen = false"
                            class="flex items-center space-x-2 px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors"
                        >
                            <UserIcon class="w-5 h-5 text-primary" />
                            <span>Thông tin của tôi</span>
                        </Link>
                        <Link
                            href="/invoices"
                            @click="userDropdownOpen = false"
                            class="flex items-center space-x-2 px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors"
                        >
                            <DocumentTextIcon class="w-5 h-5" />
                            <span>Hóa đơn</span>
                            <span
                                v-if="pendingInvoicesCount > 0"
                                class="flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs font-bold text-white"
                            >
                                {{
                                    pendingInvoicesCount > 99
                                        ? "99+"
                                        : pendingInvoicesCount
                                }}
                            </span>
                        </Link>
                        <button
                            @click="handleLogout"
                            class="w-full text-left flex items-center space-x-2 px-4 py-2 text-red-600 hover:bg-red-50 transition-colors"
                        >
                            <ArrowRightOnRectangleIcon class="w-5 h-5" />
                            <span>Đăng xuất</span>
                        </button>
                    </div>
                </div>
            </div>
            <!-- Menu mobile -->
            <div class="md:hidden relative">
                <button
                    @click="menuOpen = !menuOpen"
                    class="focus:outline-none transition-transform active:scale-95"
                    data-mobile-menu-button
                    type="button"
                >
                    <Bars3Icon
                        class="w-6 h-6 transition-transform"
                        :class="menuOpen ? 'rotate-90' : 'rotate-0'"
                    />
                </button>

                <!-- Backdrop with Transition -->
                <Transition
                    enter-active-class="transition-opacity duration-300 ease-out"
                    enter-from-class="opacity-0"
                    enter-to-class="opacity-100"
                    leave-active-class="transition-opacity duration-300 ease-in"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div
                        v-if="menuOpen"
                        @click="menuOpen = false"
                        class="fixed inset-0 bg-black/50 z-40"
                    ></div>
                </Transition>

                <!-- Mobile Sidebar Menu with Transition -->
                <Transition
                    enter-active-class="transition-transform duration-300 ease-out"
                    enter-from-class="-translate-x-full"
                    enter-to-class="translate-x-0"
                    leave-active-class="transition-transform duration-300 ease-in"
                    leave-from-class="translate-x-0"
                    leave-to-class="-translate-x-full"
                >
                    <div
                        v-if="menuOpen"
                        data-mobile-menu
                        class="fixed top-0 left-0 h-full w-[85vw] max-w-sm bg-white shadow-xl z-50 overflow-y-auto"
                    >
                        <!-- Sidebar Header -->
                        <div
                            class="bg-primary text-white p-4 flex items-center justify-between"
                        >
                            <div class="flex items-center space-x-2">
                                <img
                                    v-if="siteLogo"
                                    :src="siteLogo"
                                    :alt="siteName"
                                    class="h-8 w-auto object-contain"
                                />
                                <h2
                                    v-if="siteLogo && logoShowText"
                                    class="text-lg font-bold"
                                >
                                    {{ siteName }}
                                </h2>
                                <h2
                                    v-else-if="!siteLogo"
                                    class="text-lg font-bold"
                                >
                                    {{ siteName }}
                                </h2>
                            </div>
                            <button
                                @click="menuOpen = false"
                                class="p-1 hover:bg-white/10 rounded transition-colors"
                            >
                                <svg
                                    class="w-6 h-6"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>

                        <!-- Sidebar Content -->
                        <div class="p-4">
                            <!-- Not Authenticated -->
                            <div
                                v-if="!authStore.isAuthenticated"
                                class="space-y-2"
                            >
                                <Link
                                    href="/login"
                                    @click="menuOpen = false"
                                    class="flex items-center justify-center space-x-2 w-full py-3 px-4 bg-primary text-white rounded-lg hover:bg-primary-700 transition-all font-medium"
                                >
                                    <span>Đăng nhập</span>
                                </Link>
                            </div>

                            <!-- Authenticated User Section -->
                            <div
                                v-if="authStore.isAuthenticated"
                                class="space-y-4"
                            >
                                <!-- User Info -->
                                <div
                                    class="flex items-center space-x-3 pb-4 border-b border-gray-200"
                                >
                                    <img
                                        :src="
                                            getAvatarUrl(authStore.currentUser)
                                        "
                                        :alt="authStore.currentUser?.name"
                                        class="w-12 h-12 rounded-full border-2 border-primary object-cover"
                                        @error="handleAvatarError"
                                    />
                                    <div class="flex-1 min-w-0">
                                        <p
                                            class="font-semibold text-gray-900 truncate"
                                        >
                                            {{ authStore.currentUser?.name }}
                                        </p>
                                        <p
                                            class="text-sm text-gray-500 truncate"
                                        >
                                            {{ authStore.currentUser?.email }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Menu Items -->
                                <div class="space-y-2">
                                    <Link
                                        v-if="isManager"
                                        href="/admin"
                                        @click="menuOpen = false"
                                        :class="[
                                            'menu-item flex items-center space-x-3 w-full py-3 px-4 text-gray-700 hover:bg-gray-100 rounded-lg transition-all duration-200',
                                            { 'menu-item-animated': menuOpen },
                                        ]"
                                        :style="{ '--delay': '0.05s' }"
                                    >
                                        <Cog6ToothIcon
                                            class="w-5 h-5 text-primary"
                                        />
                                        <span class="font-medium">Quản lí</span>
                                    </Link>
                                    <Link
                                        href="/profile"
                                        @click="menuOpen = false"
                                        :class="[
                                            'menu-item flex items-center space-x-3 w-full py-3 px-4 text-gray-700 hover:bg-gray-100 rounded-lg transition-all duration-200',
                                            { 'menu-item-animated': menuOpen },
                                        ]"
                                        :style="{ '--delay': '0.1s' }"
                                    >
                                        <UserIcon
                                            class="w-5 h-5 text-primary"
                                        />
                                        <span class="font-medium"
                                            >Thông tin của tôi</span
                                        >
                                    </Link>
                                    <Link
                                        href="/my-rentals"
                                        @click="menuOpen = false"
                                        :class="[
                                            'menu-item flex items-center space-x-3 w-full py-3 px-4 text-gray-700 hover:bg-gray-100 rounded-lg transition-all duration-200',
                                            { 'menu-item-animated': menuOpen },
                                        ]"
                                        :style="{ '--delay': '0.15s' }"
                                    >
                                        <HomeIcon
                                            class="w-5 h-5 text-primary"
                                        />
                                        <span class="font-medium"
                                            >Phòng của tôi</span
                                        >
                                    </Link>
                                    <Link
                                        href="/wishlist"
                                        @click="menuOpen = false"
                                        :class="[
                                            'menu-item flex items-center space-x-3 w-full py-3 px-4 text-gray-700 hover:bg-gray-100 rounded-lg transition-all duration-200',
                                            { 'menu-item-animated': menuOpen },
                                        ]"
                                        :style="{ '--delay': '0.2s' }"
                                    >
                                        <HeartIcon
                                            class="w-5 h-5 text-red-500"
                                        />
                                        <span class="font-medium"
                                            >Yêu thích</span
                                        >
                                    </Link>
                                    <Link
                                        href="/invoices"
                                        @click="menuOpen = false"
                                        :class="[
                                            'menu-item flex items-center space-x-3 w-full py-3 px-4 text-gray-700 hover:bg-gray-100 rounded-lg transition-all duration-200',
                                            { 'menu-item-animated': menuOpen },
                                        ]"
                                        :style="{ '--delay': '0.25s' }"
                                    >
                                        <DocumentTextIcon
                                            class="w-5 h-5 text-primary"
                                        />
                                        <span class="font-medium">Hóa đơn</span>
                                        <span
                                            v-if="pendingInvoicesCount > 0"
                                            class="flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs font-bold text-white"
                                        >
                                            {{
                                                pendingInvoicesCount > 99
                                                    ? "99+"
                                                    : pendingInvoicesCount
                                            }}
                                        </span>
                                    </Link>
                                </div>

                                <!-- Logout Button -->
                                <div class="pt-4 border-t border-gray-200">
                                    <button
                                        @click="handleLogout"
                                        :class="[
                                            'menu-item flex items-center space-x-3 w-full py-3 px-4 text-red-600 hover:bg-red-50 rounded-lg transition-all duration-200',
                                            { 'menu-item-animated': menuOpen },
                                        ]"
                                        :style="{ '--delay': '0.3s' }"
                                    >
                                        <ArrowRightOnRectangleIcon
                                            class="w-5 h-5"
                                        />
                                        <span class="font-medium"
                                            >Đăng xuất</span
                                        >
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>
        </div>
    </nav>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import { useAuthStore } from "@/stores/auth";
import { useAuth } from "@/composables/useAuth";
import {
    ChevronDownIcon,
    Cog6ToothIcon,
    Bars3Icon,
    HomeIcon,
    UserIcon,
    DocumentTextIcon,
    ArrowRightOnRectangleIcon,
    HeartIcon,
} from "@heroicons/vue/24/outline";

const authStore = useAuthStore();
const page = usePage();
const { isManager } = useAuth();
const menuOpen = ref(false);
const userDropdownOpen = ref(false);
const pendingInvoicesCount = computed(
    () => page.props.pendingInvoicesCount || 0
);

// Get site name from settings
const siteName = computed(() => {
    return page.props?.siteSettings?.site_name || "THANG LONG STAY";
});

// Get site logo from settings
const siteLogo = computed(() => {
    return page.props?.siteSettings?.site_logo || null;
});

// Get logo show text setting
const logoShowText = computed(() => {
    const value = page.props?.siteSettings?.site_logo_show_text;
    return value === "1" || value === "true" || value === true || value === 1;
});

// Format price helper
const formatPrice = (price) => {
    if (!price && price !== 0) return "0 ₫";
    return new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(price);
};

// Sync user từ page props khi component mount
onMounted(() => {
    if (page.props?.auth?.user) {
        authStore.user = page.props.auth.user;
    }

    // Đóng dropdown khi click bên ngoài
    document.addEventListener("click", handleClickOutside);
});

// Watch để sync user khi page props thay đổi
watch(
    () => page.props?.auth?.user,
    (newUser) => {
        if (newUser) {
            authStore.user = newUser;
        }
    },
    { immediate: true, deep: true }
);

// Đóng mobile menu khi route thay đổi
watch(
    () => page.url,
    () => {
        menuOpen.value = false;
    }
);

const handleLogout = () => {
    menuOpen.value = false;
    userDropdownOpen.value = false;
    authStore.logout(() => {
        router.visit("/login");
    });
};

// Đóng dropdown khi click bên ngoài
const handleClickOutside = (event) => {
    const dropdown = event.target.closest(".user-dropdown");
    if (!dropdown) {
        userDropdownOpen.value = false;
    }

    // Đóng mobile menu khi click bên ngoài
    const mobileMenuButton = event.target.closest("[data-mobile-menu-button]");
    const mobileMenu = event.target.closest("[data-mobile-menu]");
    if (!mobileMenuButton && !mobileMenu) {
        menuOpen.value = false;
    }
};

onUnmounted(() => {
    document.removeEventListener("click", handleClickOutside);
});

// Get avatar URL with fallback
const getAvatarUrl = (user) => {
    if (user?.avatar) {
        return user.avatar;
    }
    // Generate default avatar from name
    const name = user?.name || "User";
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(
        name
    )}&background=000066&color=fff&size=128`;
};

// Handle avatar image error
const handleAvatarError = (event) => {
    const user = authStore.currentUser;
    if (user?.name) {
        event.target.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(
            user.name
        )}&background=000066&color=fff&size=128`;
    } else {
        event.target.src =
            "https://ui-avatars.com/api/?name=User&background=000066&color=fff&size=128";
    }
};
</script>

<style scoped>
.menu-item {
    opacity: 0;
    transform: translateX(1rem);
}

.menu-item-animated {
    animation: slideIn 0.3s ease-out forwards;
    animation-delay: var(--delay, 0s);
}

@keyframes slideIn {
    to {
        opacity: 1;
        transform: translateX(0);
    }
}
</style>
