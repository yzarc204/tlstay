<template>
    <Head :title="`Chi tiết đặt phòng #${booking?.booking_code || ''}`" />
    <AppLayout>
        <div class="booking-detail bg-light min-h-screen py-12">
            <div class="container mx-auto px-4">
                <!-- Header -->
                <div class="mb-6">
                    <Link
                        href="/my-rentals"
                        class="inline-flex items-center text-primary hover:text-primary-dark mb-4"
                    >
                        <ArrowLeftIcon class="w-5 h-5 mr-2" />
                        Quay lại lịch sử thuê phòng
                    </Link>
                    <h1 class="text-3xl font-bold text-secondary">
                        Chi tiết đặt phòng
                    </h1>
                </div>

                <div v-if="!booking" class="text-center py-20">
                    <div
                        class="inline-block animate-spin rounded-full h-16 w-16 border-4 border-primary border-t-transparent"
                    ></div>
                    <p class="mt-4 text-gray-600">Đang tải thông tin...</p>
                </div>

                <div v-else class="grid lg:grid-cols-3 gap-8">
                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Booking Info Card -->
                        <div class="card p-6">
                            <div class="flex items-start justify-between mb-6">
                                <div>
                                    <h2
                                        class="text-2xl font-bold text-secondary mb-2"
                                    >
                                        Thông tin đặt phòng
                                    </h2>
                                    <p class="text-gray-600">
                                        Mã đặt phòng:
                                        <span
                                            class="font-mono font-semibold text-primary"
                                            >{{ booking.booking_code }}</span
                                        >
                                    </p>
                                </div>
                                <span
                                    class="badge text-sm"
                                    :class="
                                        getBookingStatusBadgeClass(
                                            booking.booking_status ||
                                                booking.status
                                        )
                                    "
                                >
                                    {{
                                        getBookingStatusText(
                                            booking.booking_status ||
                                                booking.status
                                        )
                                    }}
                                </span>
                            </div>

                            <div class="grid md:grid-cols-2 gap-6">
                                <!-- House Info -->
                                <div class="space-y-4">
                                    <div>
                                        <label
                                            class="text-sm font-medium text-gray-500 block mb-1"
                                            >Nhà trọ</label
                                        >
                                        <p
                                            class="text-lg font-semibold text-gray-900"
                                        >
                                            {{ booking.house.name }}
                                        </p>
                                        <p class="text-sm text-gray-600 mt-1">
                                            {{ booking.house.address }}
                                        </p>
                                        <Link
                                            v-if="booking.house.id"
                                            :href="`/houses/${booking.house.id}`"
                                            class="text-primary hover:text-primary-dark text-sm mt-2 inline-block"
                                        >
                                            Xem chi tiết nhà trọ →
                                        </Link>
                                    </div>

                                    <div>
                                        <label
                                            class="text-sm font-medium text-gray-500 block mb-1"
                                            >Phòng</label
                                        >
                                        <p
                                            class="text-lg font-semibold text-gray-900"
                                        >
                                            Phòng
                                            {{ booking.room.room_number }} -
                                            Tầng {{ booking.room.floor }}
                                        </p>
                                        <p
                                            v-if="booking.room.area"
                                            class="text-sm text-gray-600 mt-1"
                                        >
                                            Diện tích:
                                            {{ booking.room.area }} m²
                                        </p>
                                    </div>
                                </div>

                                <!-- Dates -->
                                <div class="space-y-4">
                                    <div>
                                        <label
                                            class="text-sm font-medium text-gray-500 block mb-1"
                                            >Ngày nhận phòng</label
                                        >
                                        <p
                                            class="text-lg font-semibold text-gray-900"
                                        >
                                            {{ formatDate(booking.start_date) }}
                                        </p>
                                    </div>

                                    <div>
                                        <label
                                            class="text-sm font-medium text-gray-500 block mb-1"
                                            >Ngày trả phòng</label
                                        >
                                        <p
                                            class="text-lg font-semibold text-gray-900"
                                        >
                                            {{ formatDate(booking.end_date) }}
                                        </p>
                                    </div>

                                    <div>
                                        <label
                                            class="text-sm font-medium text-gray-500 block mb-1"
                                            >Số ngày thuê</label
                                        >
                                        <p
                                            class="text-lg font-semibold text-primary"
                                        >
                                            {{
                                                calculateDays(
                                                    booking.start_date,
                                                    booking.end_date
                                                )
                                            }}
                                            ngày
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div
                                v-if="booking.notes"
                                class="mt-6 pt-6 border-t"
                            >
                                <label
                                    class="text-sm font-medium text-gray-500 block mb-2"
                                    >Ghi chú</label
                                >
                                <p
                                    class="text-gray-700 bg-gray-50 px-4 py-3 rounded-lg whitespace-pre-wrap"
                                >
                                    {{ booking.notes }}
                                </p>
                            </div>
                        </div>

                        <!-- Payment Info Card -->
                        <div class="card p-6">
                            <h2 class="text-2xl font-bold text-secondary mb-6">
                                Thông tin thanh toán
                            </h2>

                            <div class="space-y-4">
                                <!-- Price Breakdown Table -->
                                <div
                                    v-if="
                                        priceBreakdown.fullMonths > 0 ||
                                        priceBreakdown.fullWeeks > 0 ||
                                        priceBreakdown.remainingDays > 0
                                    "
                                    class="mb-4"
                                >
                                    <h3
                                        class="text-sm font-semibold text-gray-700 mb-3"
                                    >
                                        Chi tiết giá thuê
                                    </h3>
                                    <div
                                        class="border border-gray-200 rounded-lg overflow-hidden"
                                    >
                                        <table class="w-full text-sm">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th
                                                        class="px-4 py-2 text-left text-gray-700 font-medium"
                                                    >
                                                        Loại
                                                    </th>
                                                    <th
                                                        class="px-4 py-2 text-right text-gray-700 font-medium"
                                                    >
                                                        Số lượng
                                                    </th>
                                                    <th
                                                        class="px-4 py-2 text-right text-gray-700 font-medium"
                                                    >
                                                        Thành tiền
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody
                                                class="divide-y divide-gray-200"
                                            >
                                                <tr
                                                    v-if="
                                                        priceBreakdown.fullMonths >
                                                        0
                                                    "
                                                >
                                                    <td
                                                        class="px-4 py-2 text-gray-700"
                                                    >
                                                        Tháng
                                                    </td>
                                                    <td
                                                        class="px-4 py-2 text-right text-gray-600"
                                                    >
                                                        {{
                                                            priceBreakdown.fullMonths
                                                        }}
                                                        tháng ({{
                                                            priceBreakdown.fullMonths *
                                                            30
                                                        }}
                                                        ngày)
                                                    </td>
                                                    <td
                                                        class="px-4 py-2 text-right font-semibold text-gray-900"
                                                    >
                                                        {{
                                                            formatPrice(
                                                                priceBreakdown.monthsPrice
                                                            )
                                                        }}
                                                    </td>
                                                </tr>
                                                <tr
                                                    v-if="
                                                        priceBreakdown.fullWeeks >
                                                        0
                                                    "
                                                >
                                                    <td
                                                        class="px-4 py-2 text-gray-700"
                                                    >
                                                        Tuần
                                                    </td>
                                                    <td
                                                        class="px-4 py-2 text-right text-gray-600"
                                                    >
                                                        {{
                                                            priceBreakdown.fullWeeks
                                                        }}
                                                        tuần ({{
                                                            priceBreakdown.fullWeeks *
                                                            7
                                                        }}
                                                        ngày)
                                                    </td>
                                                    <td
                                                        class="px-4 py-2 text-right font-semibold text-gray-900"
                                                    >
                                                        {{
                                                            formatPrice(
                                                                priceBreakdown.weeksPrice
                                                            )
                                                        }}
                                                    </td>
                                                </tr>
                                                <tr
                                                    v-if="
                                                        priceBreakdown.remainingDays >
                                                        0
                                                    "
                                                >
                                                    <td
                                                        class="px-4 py-2 text-gray-700"
                                                    >
                                                        Ngày
                                                    </td>
                                                    <td
                                                        class="px-4 py-2 text-right text-gray-600"
                                                    >
                                                        {{
                                                            priceBreakdown.remainingDays
                                                        }}
                                                        ngày
                                                    </td>
                                                    <td
                                                        class="px-4 py-2 text-right font-semibold text-gray-900"
                                                    >
                                                        {{
                                                            formatPrice(
                                                                priceBreakdown.remainingPrice
                                                            )
                                                        }}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div
                                    class="flex justify-between items-center py-3 border-b"
                                >
                                    <span class="text-gray-700 font-medium"
                                        >Tổng tiền phòng</span
                                    >
                                    <span
                                        class="font-semibold text-gray-900 text-lg"
                                    >
                                        {{ formatPrice(booking.total_price) }}
                                    </span>
                                </div>
                                <div
                                    class="flex justify-between items-center pt-4 mt-2 border-t-2 border-gray-400"
                                >
                                    <span
                                        class="text-xl font-bold text-gray-900"
                                        >Tổng thanh toán</span
                                    >
                                    <span
                                        class="text-3xl font-bold text-primary"
                                    >
                                        {{ formatPrice(booking.total_price) }}
                                    </span>
                                </div>
                            </div>

                            <div
                                class="grid md:grid-cols-2 gap-6 mt-6 pt-6 border-t"
                            >
                                <div>
                                    <label
                                        class="text-sm font-medium text-gray-500 block mb-2"
                                        >Trạng thái thanh toán</label
                                    >
                                    <span
                                        class="inline-block px-3 py-1 text-sm rounded-full"
                                        :class="
                                            getPaymentStatusBadgeClass(
                                                booking.payment_status
                                            )
                                        "
                                    >
                                        {{
                                            getPaymentStatusText(
                                                booking.payment_status
                                            )
                                        }}
                                    </span>
                                </div>
                                <div v-if="booking.payment_method">
                                    <label
                                        class="text-sm font-medium text-gray-500 block mb-2"
                                        >Phương thức thanh toán</label
                                    >
                                    <p class="font-semibold text-gray-900">
                                        {{
                                            getPaymentMethodText(
                                                booking.payment_method
                                            )
                                        }}
                                    </p>
                                </div>
                                <div v-if="booking.paid_at">
                                    <label
                                        class="text-sm font-medium text-gray-500 block mb-2"
                                        >Thời gian thanh toán</label
                                    >
                                    <p class="font-semibold text-gray-900">
                                        {{ formatDateTime(booking.paid_at) }}
                                    </p>
                                </div>
                                <div v-if="booking.vnpay_transaction_id">
                                    <label
                                        class="text-sm font-medium text-gray-500 block mb-2"
                                        >Mã giao dịch</label
                                    >
                                    <p
                                        class="font-mono text-sm text-gray-900 break-all"
                                    >
                                        {{ booking.vnpay_transaction_id }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Contract Card -->
                        <div class="card p-6">
                            <h2 class="text-2xl font-bold text-secondary mb-6">
                                Hợp đồng
                            </h2>

                            <div class="space-y-4">
                                <div
                                    class="flex items-center justify-between p-4 bg-gray-50 rounded-lg"
                                >
                                    <div>
                                        <p class="font-semibold text-gray-900">
                                            Trạng thái hợp đồng
                                        </p>
                                        <p class="text-sm text-gray-600 mt-1">
                                            <span
                                                v-if="booking.contract_signed"
                                                class="text-green-600"
                                                >Đã ký hợp đồng</span
                                            >
                                            <span v-else class="text-yellow-600"
                                                >Chưa ký hợp đồng</span
                                            >
                                        </p>
                                        <p
                                            v-if="booking.signed_at"
                                            class="text-xs text-gray-500 mt-1"
                                        >
                                            Đã ký vào:
                                            {{
                                                formatDateTime(
                                                    booking.signed_at
                                                )
                                            }}
                                        </p>
                                    </div>
                                    <div class="flex space-x-2">
                                        <a
                                            :href="`/contract/${booking.id}/preview`"
                                            target="_blank"
                                            class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors"
                                        >
                                            Xem hợp đồng
                                        </a>
                                        <a
                                            :href="`/contract/${booking.id}`"
                                            target="_blank"
                                            class="px-4 py-2 border-2 border-primary text-primary rounded-lg hover:bg-primary hover:text-white transition-colors"
                                        >
                                            Tải PDF
                                        </a>
                                        <Link
                                            v-if="
                                                !booking.contract_signed &&
                                                booking.payment_status ===
                                                    'paid'
                                            "
                                            :href="`/contract/${booking.id}/sign`"
                                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
                                        >
                                            Ký hợp đồng
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Invoices Card -->
                        <div class="card p-6">
                            <h2 class="text-2xl font-bold text-secondary mb-6">
                                Hóa đơn dịch vụ
                            </h2>

                            <div
                                v-if="
                                    booking.invoices &&
                                    booking.invoices.length > 0
                                "
                                class="space-y-4"
                            >
                                <div
                                    v-for="invoice in booking.invoices"
                                    :key="invoice.id"
                                    class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
                                >
                                    <div
                                        class="flex items-start justify-between mb-3"
                                    >
                                        <div>
                                            <p
                                                class="font-semibold text-gray-900"
                                            >
                                                {{
                                                    invoice.month_year ||
                                                    `Hóa đơn ${invoice.month}/${invoice.year}`
                                                }}
                                            </p>
                                            <p
                                                v-if="invoice.invoice_code"
                                                class="text-sm text-gray-500 mt-1"
                                            >
                                                Mã: {{ invoice.invoice_code }}
                                            </p>
                                        </div>
                                        <span
                                            class="badge text-xs"
                                            :class="
                                                invoice.status === 'paid'
                                                    ? 'bg-green-100 text-green-700'
                                                    : 'bg-yellow-100 text-yellow-700'
                                            "
                                        >
                                            {{
                                                invoice.status === "paid"
                                                    ? "Đã thanh toán"
                                                    : "Chưa thanh toán"
                                            }}
                                        </span>
                                    </div>

                                    <div class="grid md:grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <p class="text-sm text-gray-600">
                                                Điện
                                            </p>
                                            <p
                                                class="font-semibold text-gray-900"
                                            >
                                                {{
                                                    formatPrice(
                                                        invoice.electricity_amount
                                                    )
                                                }}
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">
                                                Nước
                                            </p>
                                            <p
                                                class="font-semibold text-gray-900"
                                            >
                                                {{
                                                    formatPrice(
                                                        invoice.water_amount
                                                    )
                                                }}
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">
                                                Internet
                                            </p>
                                            <p
                                                class="font-semibold text-gray-900"
                                            >
                                                {{
                                                    formatPrice(
                                                        invoice.other_fees
                                                    )
                                                }}
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">
                                                Tổng cộng
                                            </p>
                                            <p
                                                class="font-bold text-primary text-lg"
                                            >
                                                {{ formatPrice(invoice.total) }}
                                            </p>
                                        </div>
                                    </div>

                                    <div
                                        class="flex items-center justify-between pt-3 border-t"
                                    >
                                        <div class="text-sm text-gray-600">
                                            <p v-if="invoice.due_date">
                                                Hạn thanh toán:
                                                {{
                                                    formatDate(invoice.due_date)
                                                }}
                                            </p>
                                            <p v-if="invoice.paid_at">
                                                Đã thanh toán:
                                                {{
                                                    formatDateTime(
                                                        invoice.paid_at
                                                    )
                                                }}
                                            </p>
                                        </div>
                                        <div class="flex space-x-2">
                                            <Link
                                                :href="`/invoices/${invoice.id}`"
                                                class="px-3 py-1 text-sm border border-gray-300 rounded-lg hover:bg-gray-50"
                                            >
                                                Chi tiết
                                            </Link>
                                            <Link
                                                v-if="invoice.status !== 'paid'"
                                                :href="`/payment/invoice/${invoice.id}`"
                                                class="px-3 py-1 text-sm bg-primary text-white rounded-lg hover:bg-primary-dark"
                                            >
                                                Thanh toán
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-8 text-gray-500">
                                <DocumentTextIcon
                                    class="w-16 h-16 mx-auto mb-2 text-gray-300"
                                />
                                <p>Chưa có hóa đơn dịch vụ</p>
                            </div>
                        </div>

                        <!-- Review Card -->
                        <div
                            v-if="booking.review || booking.can_review"
                            class="card p-6"
                        >
                            <h2 class="text-2xl font-bold text-secondary mb-6">
                                Đánh giá
                            </h2>

                            <!-- Review Form -->
                            <ReviewForm
                                v-if="
                                    booking.can_review &&
                                    booking.payment_status === 'paid'
                                "
                                :booking-id="booking.id"
                                :house-id="booking.house.id"
                                @review-created="handleReviewCreated"
                            />

                            <!-- Existing Review -->
                            <div v-if="booking.review" class="space-y-4">
                                <ReviewItem :review="booking.review" />
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="lg:col-span-1">
                        <div class="card p-6 sticky top-24 space-y-4">
                            <h3 class="text-lg font-bold text-secondary mb-4">
                                Thao tác
                            </h3>

                            <!-- Payment Button -->
                            <button
                                v-if="booking.payment_status === 'pending'"
                                @click="goToPayment"
                                class="w-full btn-primary"
                            >
                                <CreditCardIcon class="w-5 h-5 inline mr-2" />
                                Thanh toán ngay
                            </button>

                            <!-- Contract Actions -->
                            <div
                                v-if="booking.payment_status === 'paid'"
                                class="space-y-3"
                            >
                                <a
                                    :href="`/contract/${booking.id}/preview`"
                                    target="_blank"
                                    class="block w-full btn-outline text-center"
                                >
                                    <DocumentIcon class="w-5 h-5 inline mr-2" />
                                    Xem hợp đồng
                                </a>
                                <a
                                    :href="`/contract/${booking.id}`"
                                    target="_blank"
                                    class="block w-full btn-outline text-center"
                                >
                                    <ArrowDownTrayIcon
                                        class="w-5 h-5 inline mr-2"
                                    />
                                    Tải hợp đồng PDF
                                </a>
                                <Link
                                    v-if="!booking.contract_signed"
                                    :href="`/contract/${booking.id}/sign`"
                                    class="block w-full btn-primary text-center"
                                >
                                    <PencilIcon class="w-5 h-5 inline mr-2" />
                                    Ký hợp đồng
                                </Link>
                            </div>

                            <!-- Contact Info -->
                            <div class="pt-6 border-t">
                                <h4 class="font-semibold text-gray-800 mb-3">
                                    Liên hệ hỗ trợ
                                </h4>

                                <!-- Owner Contact -->
                                <div
                                    v-if="booking.owner"
                                    class="bg-primary-50 rounded-lg p-4"
                                >
                                    <p
                                        class="text-xs font-semibold text-primary-700 mb-2 uppercase"
                                    >
                                        Quản lý nhà trọ
                                    </p>
                                    <div class="space-y-2 text-sm">
                                        <p class="font-semibold text-gray-900">
                                            {{ booking.owner.name }}
                                        </p>
                                        <p
                                            v-if="booking.owner.phone"
                                            class="flex items-center text-gray-700"
                                        >
                                            <PhoneIcon
                                                class="w-4 h-4 mr-2 text-primary"
                                            />
                                            <a
                                                :href="`tel:${booking.owner.phone}`"
                                                class="hover:text-primary"
                                            >
                                                {{ booking.owner.phone }}
                                            </a>
                                        </p>
                                        <p
                                            v-if="booking.owner.email"
                                            class="flex items-center text-gray-700"
                                        >
                                            <EnvelopeIcon
                                                class="w-4 h-4 mr-2 text-primary"
                                            />
                                            <a
                                                :href="`mailto:${booking.owner.email}`"
                                                class="hover:text-primary break-all"
                                            >
                                                {{ booking.owner.email }}
                                            </a>
                                        </p>
                                    </div>
                                </div>
                                <div v-else class="text-sm text-gray-500">
                                    <p>Thông tin quản lý chưa có sẵn</p>
                                </div>
                            </div>

                            <!-- Map -->
                            <div
                                v-if="
                                    booking.house.latitude &&
                                    booking.house.longitude
                                "
                                class="pt-6 border-t"
                            >
                                <h4 class="font-semibold text-gray-800 mb-3">
                                    Vị trí
                                </h4>
                                <div
                                    class="rounded-lg overflow-hidden border border-gray-200"
                                >
                                    <iframe
                                        :src="mapEmbedUrl"
                                        width="100%"
                                        height="200"
                                        style="border: 0"
                                        allowfullscreen=""
                                        loading="lazy"
                                        referrerpolicy="no-referrer-when-downgrade"
                                    ></iframe>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AppLayout from "@/layouts/AppLayout.vue";
import ReviewForm from "@/components/reviews/ReviewForm.vue";
import ReviewItem from "@/components/reviews/ReviewItem.vue";
import {
    ArrowLeftIcon,
    CreditCardIcon,
    DocumentIcon,
    ArrowDownTrayIcon,
    PencilIcon,
    PhoneIcon,
    EnvelopeIcon,
    DocumentTextIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    booking: {
        type: Object,
        default: null,
    },
});

const formatPrice = (price) => {
    if (!price && price !== 0) return "0 ₫";
    return new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
    }).format(price);
};

const formatDate = (dateString) => {
    if (!dateString) return "";
    return new Date(dateString).toLocaleDateString("vi-VN", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
};

const formatDateTime = (dateString) => {
    if (!dateString) return "";
    return new Date(dateString).toLocaleString("vi-VN", {
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

const calculateDays = (startDate, endDate) => {
    if (!startDate || !endDate) return 0;
    const start = new Date(startDate);
    const end = new Date(endDate);
    const diffTime = Math.abs(end - start);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays + 1; // +1 để bao gồm cả ngày cuối
};

// Tính breakdown tự động: tháng -> tuần -> ngày
const calculateBreakdown = (days) => {
    let remaining = days;
    const fullMonths = Math.floor(remaining / 30);
    remaining = remaining % 30;
    const fullWeeks = Math.floor(remaining / 7);
    const remainingDays = remaining % 7;
    return { fullMonths, fullWeeks, remainingDays };
};

// Tính giá breakdown chi tiết - sử dụng dữ liệu đã lưu từ booking
const priceBreakdown = computed(() => {
    if (!props.booking) {
        return {
            fullMonths: 0,
            fullWeeks: 0,
            remainingDays: 0,
            monthsPrice: 0,
            weeksPrice: 0,
            remainingPrice: 0,
        };
    }

    // Ưu tiên sử dụng dữ liệu đã lưu từ booking
    if (
        props.booking.full_months !== undefined ||
        props.booking.full_weeks !== undefined ||
        props.booking.remaining_days !== undefined
    ) {
        return {
            fullMonths: props.booking.full_months || 0,
            fullWeeks: props.booking.full_weeks || 0,
            remainingDays: props.booking.remaining_days || 0,
            monthsPrice: props.booking.months_price || 0,
            weeksPrice: props.booking.weeks_price || 0,
            remainingPrice: props.booking.remaining_price || 0,
        };
    }

    // Fallback: tính toán lại nếu không có dữ liệu đã lưu (cho các booking cũ)
    if (
        !props.booking?.room ||
        !props.booking?.start_date ||
        !props.booking?.end_date
    ) {
        return {
            fullMonths: 0,
            fullWeeks: 0,
            remainingDays: 0,
            monthsPrice: 0,
            weeksPrice: 0,
            remainingPrice: 0,
        };
    }

    const days = calculateDays(
        props.booking.start_date,
        props.booking.end_date
    );
    if (days <= 0) {
        return {
            fullMonths: 0,
            fullWeeks: 0,
            remainingDays: 0,
            monthsPrice: 0,
            weeksPrice: 0,
            remainingPrice: 0,
        };
    }

    const room = props.booking.room;
    const pricePerDay = room.price_per_day || room.pricePerDay || 0;
    const pricePerWeek = room.price_per_week || room.pricePerWeek || null;
    const pricePerMonth = room.price_per_month || room.pricePerMonth || null;

    // Phân tích số ngày thành tháng/tuần/ngày
    const { fullMonths, fullWeeks, remainingDays } = calculateBreakdown(days);

    // Tính giá cho từng phần - ưu tiên giá ưu đãi
    let monthsPrice = 0;
    if (fullMonths > 0) {
        if (pricePerMonth !== null && pricePerMonth > 0) {
            monthsPrice = fullMonths * pricePerMonth;
        } else {
            monthsPrice = fullMonths * pricePerDay * 30;
        }
    }

    let weeksPrice = 0;
    if (fullWeeks > 0) {
        if (pricePerWeek !== null && pricePerWeek > 0) {
            weeksPrice = fullWeeks * pricePerWeek;
        } else {
            weeksPrice = fullWeeks * pricePerDay * 7;
        }
    }

    const remainingPrice = remainingDays * pricePerDay;

    return {
        fullMonths,
        fullWeeks,
        remainingDays,
        monthsPrice: Math.round(monthsPrice),
        weeksPrice: Math.round(weeksPrice),
        remainingPrice: Math.round(remainingPrice),
    };
});

const getBookingStatusText = (status) => {
    const statusMap = {
        upcoming: "Sắp tới",
        active: "Đang ở",
        past: "Đã ở",
        pending: "Chờ thanh toán",
        completed: "Đã kết thúc",
        cancelled: "Đã hủy",
    };
    return statusMap[status] || status;
};

const getBookingStatusBadgeClass = (status) => {
    const classMap = {
        upcoming: "bg-amber-100 text-amber-800 border border-amber-300",
        active: "bg-green-100 text-green-700",
        past: "bg-gray-100 text-gray-600",
        pending: "bg-yellow-100 text-yellow-700",
        completed: "bg-gray-100 text-gray-600",
        cancelled: "bg-red-100 text-red-700",
    };
    return classMap[status] || "bg-gray-100 text-gray-600";
};

const getPaymentStatusText = (status) => {
    const statusMap = {
        pending: "Chờ thanh toán",
        paid: "Đã thanh toán",
        failed: "Thanh toán thất bại",
        cancelled: "Đã hủy",
    };
    return statusMap[status] || status;
};

const getPaymentStatusBadgeClass = (status) => {
    const classMap = {
        pending: "bg-yellow-100 text-yellow-700",
        paid: "bg-green-100 text-green-700",
        failed: "bg-red-100 text-red-700",
        cancelled: "bg-gray-100 text-gray-600",
    };
    return classMap[status] || "bg-gray-100 text-gray-600";
};

const getPaymentMethodText = (method) => {
    const methodMap = {
        vnpay: "VNPay",
        bank_transfer: "Chuyển khoản ngân hàng",
        cash: "Tiền mặt",
    };
    return methodMap[method] || method;
};

const mapEmbedUrl = computed(() => {
    if (!props.booking?.house?.latitude || !props.booking?.house?.longitude) {
        return "";
    }
    const lat = props.booking.house.latitude;
    const lng = props.booking.house.longitude;
    return `https://www.google.com/maps?q=${lat},${lng}&hl=vi&z=15&output=embed`;
});

const goToPayment = () => {
    if (props.booking?.id) {
        router.visit(`/payment/${props.booking.id}`);
    }
};

const handleReviewCreated = () => {
    router.reload({ only: ["booking"], preserveScroll: true });
};
</script>
