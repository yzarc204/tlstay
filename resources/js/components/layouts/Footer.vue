<template>
    <footer class="bg-primary-900 text-white">
        <div class="container mx-auto px-4 py-12">
            <div
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-8"
            >
                <!-- Company Info -->
                <div class="lg:col-span-1">
                    <div class="flex items-center space-x-2 mb-4">
                        <div
                            class="w-10 h-10 bg-primary rounded-lg flex items-center justify-center"
                        >
                            <span class="text-white font-bold text-xl">TL</span>
                        </div>
                        <span class="text-xl font-bold">{{ siteName }}</span>
                    </div>
                    <p
                        v-if="siteAddress"
                        class="text-primary-200 text-sm mb-4 leading-relaxed"
                    >
                        {{ siteAddress }}
                    </p>
                    <p
                        v-else
                        class="text-primary-200 text-sm mb-4 leading-relaxed"
                    >
                        Nền tảng cho thuê phòng trọ hàng đầu Việt Nam. Tìm kiếm
                        và đặt phòng trọ dễ dàng, nhanh chóng.
                    </p>

                    <!-- Contact Info -->
                    <div
                        v-if="contactPhone || contactEmail"
                        class="mb-4 space-y-2"
                    >
                        <p
                            v-if="contactPhone"
                            class="text-primary-200 text-sm flex items-center"
                        >
                            <PhoneIcon class="w-4 h-4 mr-2" />
                            <a
                                :href="`tel:${contactPhone}`"
                                class="hover:text-secondary transition-colors"
                            >
                                {{ contactPhone }}
                            </a>
                        </p>
                        <p
                            v-if="contactEmail"
                            class="text-primary-200 text-sm flex items-center"
                        >
                            <EnvelopeIcon class="w-4 h-4 mr-2" />
                            <a
                                :href="`mailto:${contactEmail}`"
                                class="hover:text-secondary transition-colors"
                            >
                                {{ contactEmail }}
                            </a>
                        </p>
                    </div>

                    <div v-if="socialLinks.length > 0" class="flex space-x-3">
                        <a
                            v-for="link in socialLinks"
                            :key="link.id"
                            :href="link.url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="w-9 h-9 bg-primary-800 rounded-full flex items-center justify-center hover:bg-primary hover:text-white transition-colors"
                            :aria-label="link.name"
                        >
                            <SocialIcon :icon="link.icon" />
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-base font-semibold mb-4 text-white">
                        Liên kết nhanh
                    </h3>
                    <ul class="space-y-3 text-primary-200">
                        <li>
                            <a
                                href="#"
                                class="hover:text-secondary transition-colors text-sm"
                                >Về chúng tôi</a
                            >
                        </li>
                        <li>
                            <Link
                                href="/privacy"
                                class="hover:text-secondary transition-colors text-sm"
                                >Chính sách bảo mật</Link
                            >
                        </li>
                        <li>
                            <Link
                                href="/terms"
                                class="hover:text-secondary transition-colors text-sm"
                                >Điều khoản & Điều kiện</Link
                            >
                        </li>
                        <li>
                            <a
                                href="#"
                                class="hover:text-secondary transition-colors text-sm"
                                >Liên hệ</a
                            >
                        </li>
                        <li>
                            <a
                                href="#"
                                class="hover:text-secondary transition-colors text-sm"
                                >Blog</a
                            >
                        </li>
                    </ul>
                </div>

                <!-- Services -->
                <div>
                    <h3 class="text-base font-semibold mb-4 text-white">
                        Dịch vụ
                    </h3>
                    <ul class="space-y-3 text-primary-200">
                        <li>
                            <a
                                href="#"
                                class="hover:text-secondary transition-colors text-sm"
                                >Cho thuê phòng</a
                            >
                        </li>
                        <li>
                            <a
                                href="#"
                                class="hover:text-secondary transition-colors text-sm"
                                >Hỗ trợ khách hàng</a
                            >
                        </li>
                        <li>
                            <a
                                href="#"
                                class="hover:text-secondary transition-colors text-sm"
                                >Phương thức thanh toán</a
                            >
                        </li>
                        <li>
                            <a
                                href="#"
                                class="hover:text-secondary transition-colors text-sm"
                                >Câu hỏi thường gặp</a
                            >
                        </li>
                        <li>
                            <a
                                href="#"
                                class="hover:text-secondary transition-colors text-sm"
                                >Trung tâm trợ giúp</a
                            >
                        </li>
                    </ul>
                </div>

                <!-- Newsletter -->
                <div>
                    <h3 class="text-base font-semibold mb-4 text-white">
                        Bản tin
                    </h3>
                    <p class="text-primary-200 text-sm mb-4 leading-relaxed">
                        Đăng ký để nhận cập nhật mới nhất và ưu đãi độc quyền
                    </p>
                    <div class="flex flex-col space-y-3">
                        <div class="flex">
                            <input
                                type="email"
                                placeholder="Địa chỉ email của bạn"
                                class="flex-1 px-4 py-2.5 rounded-l-lg bg-primary-800 text-white placeholder-primary-300 border-none focus:outline-none focus:ring-2 focus:ring-secondary text-sm"
                            />
                            <button
                                class="px-4 py-2.5 bg-secondary rounded-r-lg hover:bg-secondary-600 transition-colors text-white"
                                aria-label="Đăng ký"
                            >
                                <ArrowRightIcon class="w-5 h-5" />
                            </button>
                        </div>
                        <p class="text-xs text-primary-300">
                            Chúng tôi tôn trọng quyền riêng tư của bạn. Hủy đăng
                            ký bất cứ lúc nào.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Bottom Bar -->
            <div class="border-t border-primary-800 pt-8">
                <div
                    class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0"
                >
                    <p
                        class="text-primary-200 text-sm text-center md:text-left"
                    >
                        &copy; {{ new Date().getFullYear() }} {{ siteName }}.
                        All rights reserved.
                    </p>
                    <div
                        class="flex flex-wrap justify-center md:justify-end gap-6 text-sm"
                    >
                        <Link
                            href="/privacy"
                            class="text-primary-200 hover:text-secondary transition-colors"
                            >Bảo mật</Link
                        >
                        <Link
                            href="/terms"
                            class="text-primary-200 hover:text-secondary transition-colors"
                            >Điều khoản</Link
                        >
                        <a
                            href="#"
                            class="text-primary-200 hover:text-secondary transition-colors"
                            >Cookies</a
                        >
                        <a
                            href="#"
                            class="text-primary-200 hover:text-secondary transition-colors"
                            >Sơ đồ trang</a
                        >
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <!-- Crafted with love bar -->
    <div class="bg-mid-blue-600 border-t border-mid-blue-700 py-3">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-center">
                <p class="text-white text-xs flex items-center gap-1">
                    <span>Crafted with</span>
                    <HeartIcon class="w-3.5 h-3.5 text-red-500" />
                    <span>by</span>
                    <a
                        href="https://www.instagram.com/ngthuifff/"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="text-secondary hover:text-secondary-300 transition-colors font-medium"
                    >
                        @kadev
                    </a>
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import {
    ArrowRightIcon,
    PhoneIcon,
    EnvelopeIcon,
} from "@heroicons/vue/24/outline";
import { HeartIcon } from "@heroicons/vue/24/solid";
import SocialIcon from "@/components/SocialIcon.vue";

const page = usePage();

// Get site settings from page props
const siteName = computed(() => {
    return page.props?.siteSettings?.site_name || "THANG LONG STAY";
});

const siteAddress = computed(() => {
    return page.props?.siteSettings?.site_address || "";
});

const contactPhone = computed(() => {
    return page.props?.siteSettings?.contact_phone || "+84 123 456 789";
});

const contactEmail = computed(() => {
    return page.props?.siteSettings?.contact_email || "contact@tlstay.com";
});

const socialLinks = computed(() => {
    return page.props?.socialLinks || [];
});
</script>
