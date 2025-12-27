<template>
    <Head :title="house?.name ? `Đặt phòng - ${house.name}` : 'Đặt phòng'" />
    <AppLayout>
        <div class="booking-room bg-light min-h-screen py-12">
            <div class="container mx-auto px-4">
                <!-- Header -->
                <div class="mb-8">
                    <button
                        @click="goBack"
                        class="flex items-center text-gray-600 hover:text-primary mb-4"
                    >
                        <ChevronLeftIcon class="w-5 h-5 mr-2" />
                        Quay lại
                    </button>
                    <h1 class="text-4xl font-bold text-secondary mb-2">
                        Chọn phòng
                    </h1>
                    <p class="text-gray-600">{{ house?.name }}</p>
                </div>

                <!-- Error Message -->
                <div v-if="errorMessage" class="text-center py-20">
                    <div
                        class="bg-red-50 border border-red-200 rounded-lg p-6 max-w-md mx-auto"
                    >
                        <ExclamationCircleIcon
                            class="w-12 h-12 text-red-500 mx-auto mb-4"
                        />
                        <h3 class="text-lg font-semibold text-red-800 mb-2">
                            Lỗi
                        </h3>
                        <p class="text-red-600 mb-4">{{ errorMessage }}</p>
                        <button
                            @click="goBack"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                        >
                            Quay lại
                        </button>
                    </div>
                </div>

                <!-- Content -->
                <div
                    v-if="!errorMessage && house"
                    class="grid lg:grid-cols-3 gap-8"
                >
                    <!-- Floor Plan -->
                    <div class="lg:col-span-2">
                        <div
                            class="card p-6 max-h-[calc(100vh-8rem)] flex flex-col"
                        >
                            <div
                                class="flex items-center justify-between mb-6 flex-shrink-0"
                            >
                                <h2 class="text-2xl font-bold text-secondary">
                                    Sơ đồ phòng
                                </h2>
                                <div class="flex items-center space-x-4">
                                    <div
                                        v-if="isCheckingRoomStatus"
                                        class="flex items-center space-x-2 text-primary"
                                    >
                                        <svg
                                            class="animate-spin h-4 w-4"
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                        >
                                            <circle
                                                class="opacity-25"
                                                cx="12"
                                                cy="12"
                                                r="10"
                                                stroke="currentColor"
                                                stroke-width="4"
                                            ></circle>
                                            <path
                                                class="opacity-75"
                                                fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                            ></path>
                                        </svg>
                                        <span class="text-sm text-gray-600"
                                            >Đang kiểm tra...</span
                                        >
                                    </div>
                                    <div
                                        v-else
                                        class="flex items-center space-x-4"
                                    >
                                        <div
                                            class="flex items-center space-x-2"
                                        >
                                            <div
                                                class="w-4 h-4 bg-primary rounded"
                                            ></div>
                                            <span class="text-sm text-gray-600"
                                                >Phòng trống</span
                                            >
                                        </div>
                                        <div
                                            class="flex items-center space-x-2"
                                        >
                                            <div
                                                class="w-4 h-4 bg-gray-400 rounded"
                                            ></div>
                                            <span class="text-sm text-gray-600"
                                                >Đã thuê</span
                                            >
                                        </div>
                                        <div
                                            class="flex items-center space-x-2"
                                        >
                                            <div
                                                class="w-4 h-4 bg-secondary-50 border-2 border-secondary rounded relative"
                                            >
                                                <CheckIcon
                                                    class="w-2.5 h-2.5 text-secondary absolute top-0.5 left-0.5"
                                                    stroke-width="3"
                                                />
                                            </div>
                                            <span class="text-sm text-gray-600"
                                                >Đã chọn</span
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Floors -->
                            <div
                                class="space-y-6 overflow-y-auto flex-1 pr-2 relative"
                            >
                                <!-- Loading overlay - less intrusive -->
                                <div
                                    v-if="isCheckingRoomStatus"
                                    class="absolute inset-0 bg-white/60 backdrop-blur-sm z-10 flex items-center justify-center rounded-lg pointer-events-none"
                                >
                                    <div
                                        class="text-center bg-white/90 rounded-lg px-4 py-3 shadow-lg"
                                    >
                                        <svg
                                            class="animate-spin h-6 w-6 text-primary mx-auto mb-2"
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                        >
                                            <circle
                                                class="opacity-25"
                                                cx="12"
                                                cy="12"
                                                r="10"
                                                stroke="currentColor"
                                                stroke-width="4"
                                            ></circle>
                                            <path
                                                class="opacity-75"
                                                fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                            ></path>
                                        </svg>
                                        <p
                                            class="text-sm text-gray-700 font-medium"
                                        >
                                            Đang cập nhật trạng thái phòng...
                                        </p>
                                    </div>
                                </div>

                                <div
                                    v-for="floor in sortedFloors"
                                    :key="floor"
                                    class="border-2 border-gray-200 rounded-xl p-6 bg-white transition-opacity duration-200"
                                    :class="{
                                        'opacity-60': isCheckingRoomStatus,
                                    }"
                                >
                                    <h3
                                        class="text-xl font-bold text-secondary mb-4 flex items-center"
                                    >
                                        <BuildingOfficeIcon
                                            class="w-6 h-6 mr-2"
                                        />
                                        Tầng {{ floor }}
                                    </h3>

                                    <div
                                        class="grid grid-cols-2 md:grid-cols-4 gap-4"
                                    >
                                        <RoomCard
                                            v-for="room in roomsByFloor[floor]"
                                            :key="room.id"
                                            :room="room"
                                            :selected="
                                                selectedRoom?.id === room.id
                                            "
                                            @select="selectRoom"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Booking Summary -->
                    <div class="lg:col-span-1">
                        <div
                            class="card p-6 sticky top-24 max-h-[calc(100vh-8rem)] overflow-y-auto"
                        >
                            <h2 class="text-xl font-bold text-secondary mb-6">
                                Thông tin đặt phòng
                            </h2>

                            <!-- Selected Room -->
                            <div
                                v-if="selectedRoom"
                                class="mb-6 bg-primary-50 rounded-lg border-2 border-primary overflow-hidden"
                            >
                                <!-- Room Images - Grid Thumbnails -->
                                <div v-if="roomImages.length > 0" class="p-4">
                                    <h3
                                        class="text-sm font-semibold text-gray-700 mb-3"
                                    >
                                        Ảnh phòng
                                    </h3>
                                    <div class="grid grid-cols-4 gap-1.5">
                                        <button
                                            v-for="(img, index) in roomImages"
                                            :key="index"
                                            @click="openImageSlider(index)"
                                            class="relative aspect-square rounded-lg overflow-hidden border-2 border-transparent hover:border-primary transition-all group"
                                        >
                                            <img
                                                :src="img"
                                                :alt="`Ảnh phòng ${index + 1}`"
                                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                                            />
                                            <div
                                                class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors"
                                            ></div>
                                        </button>
                                    </div>
                                </div>
                                <!-- Default image if no room images -->
                                <div v-else class="p-4">
                                    <h3
                                        class="text-sm font-semibold text-gray-700 mb-3"
                                    >
                                        Ảnh phòng
                                    </h3>
                                    <div
                                        class="relative aspect-square rounded-lg bg-gray-200 overflow-hidden"
                                    >
                                        <img
                                            :src="
                                                house?.image ||
                                                'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800'
                                            "
                                            :alt="`Phòng ${selectedRoom.roomNumber}`"
                                            class="w-full h-full object-cover opacity-50"
                                        />
                                        <div
                                            class="absolute inset-0 flex items-center justify-center"
                                        >
                                            <HomeIcon
                                                class="w-16 h-16 text-gray-400"
                                            />
                                        </div>
                                    </div>
                                </div>

                                <!-- Room Info -->
                                <div class="p-4 border-t border-primary/20">
                                    <div
                                        class="flex items-center justify-between mb-2"
                                    >
                                        <span class="font-semibold text-primary"
                                            >Phòng
                                            {{ selectedRoom.roomNumber }} - Tầng
                                            {{ selectedRoom.floor }}</span
                                        >
                                        <button
                                            @click="
                                                () => {
                                                    selectedRoom = null;
                                                    bookingForm.room_id = null;
                                                }
                                            "
                                            class="text-gray-500 hover:text-secondary"
                                        >
                                            <XMarkIcon class="w-5 h-5" />
                                        </button>
                                    </div>
                                    <p class="text-sm text-gray-600">
                                        Diện tích: {{ selectedRoom.area }}m²
                                    </p>
                                    <div class="mt-3 space-y-2">
                                        <p
                                            class="text-sm font-semibold text-gray-700 mb-2"
                                        >
                                            Giá thuê:
                                        </p>
                                        <div
                                            class="flex items-center justify-between text-sm"
                                        >
                                            <span class="text-gray-600"
                                                >Theo ngày:</span
                                            >
                                            <span
                                                class="font-medium text-gray-900"
                                            >
                                                {{
                                                    formatPrice(
                                                        selectedRoom.pricePerDay
                                                    )
                                                }}
                                            </span>
                                        </div>
                                        <div
                                            class="flex items-center justify-between text-sm"
                                        >
                                            <span class="text-gray-600"
                                                >Theo tuần:</span
                                            >
                                            <span
                                                class="font-medium text-gray-900"
                                            >
                                                {{
                                                    formatPrice(
                                                        selectedRoom.pricePerWeek ||
                                                            selectedRoom.pricePerDay *
                                                                7
                                                    )
                                                }}
                                            </span>
                                        </div>
                                        <div
                                            class="flex items-center justify-between text-sm"
                                        >
                                            <span class="text-gray-600"
                                                >Theo tháng:</span
                                            >
                                            <span
                                                class="font-medium text-gray-900"
                                            >
                                                {{
                                                    formatPrice(
                                                        selectedRoom.pricePerMonth ||
                                                            selectedRoom.pricePerDay *
                                                                30
                                                    )
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div
                                v-else
                                class="mb-6 p-4 bg-gray-50 rounded-lg text-center text-gray-500"
                            >
                                <svg
                                    class="w-12 h-12 mx-auto mb-2 text-gray-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                                    />
                                </svg>
                                <p class="text-sm">Vui lòng chọn phòng</p>
                            </div>

                            <!-- Booking Form -->
                            <form
                                @submit.prevent="handleBookingSubmit"
                                class="space-y-4"
                            >
                                <!-- Start Date -->
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                        >Ngày bắt đầu</label
                                    >
                                    <DatePicker
                                        v-model="startDate"
                                        :min="today"
                                        format="dd/mm/yyyy"
                                        placeholder="dd/mm/yyyy"
                                        :error="
                                            startDateError ||
                                            bookingForm.errors.start_date
                                        "
                                    />
                                </div>

                                <!-- End Date -->
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                        >Ngày kết thúc</label
                                    >
                                    <DatePicker
                                        v-model="endDate"
                                        :min="startDate || today"
                                        format="dd/mm/yyyy"
                                        placeholder="dd/mm/yyyy"
                                        :error="
                                            endDateError ||
                                            bookingForm.errors.end_date
                                        "
                                    />
                                </div>

                                <!-- Notes -->
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                        >Ghi chú</label
                                    >
                                    <textarea
                                        v-model="values.notes"
                                        @input="
                                            setFieldValue(
                                                'notes',
                                                $event.target.value
                                            )
                                        "
                                        class="input-field"
                                        rows="3"
                                        placeholder="Yêu cầu đặc biệt..."
                                    ></textarea>
                                    <p
                                        v-if="bookingForm.errors.notes"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ bookingForm.errors.notes }}
                                    </p>
                                </div>

                                <!-- Calculation Summary - Trước nút submit -->
                                <div
                                    v-if="selectedRoom && startDate && endDate"
                                    class="p-4 bg-light rounded-lg border-2 border-primary/20 transition-opacity duration-200"
                                    :class="{
                                        'opacity-60': isCheckingRoomStatus,
                                    }"
                                >
                                    <div
                                        class="flex items-center justify-between mb-3"
                                    >
                                        <h3
                                            class="text-sm font-semibold text-gray-700"
                                        >
                                            Tóm tắt đặt phòng
                                        </h3>
                                        <div
                                            v-if="isCheckingRoomStatus"
                                            class="flex items-center space-x-2 text-xs text-gray-500"
                                        >
                                            <svg
                                                class="animate-spin h-3 w-3"
                                                xmlns="http://www.w3.org/2000/svg"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                            >
                                                <circle
                                                    class="opacity-25"
                                                    cx="12"
                                                    cy="12"
                                                    r="10"
                                                    stroke="currentColor"
                                                    stroke-width="4"
                                                ></circle>
                                                <path
                                                    class="opacity-75"
                                                    fill="currentColor"
                                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                                ></path>
                                            </svg>
                                            <span>Đang cập nhật...</span>
                                        </div>
                                    </div>

                                    <div class="space-y-2 mb-4">
                                        <div
                                            class="flex justify-between text-sm"
                                        >
                                            <span class="text-gray-600"
                                                >Tổng số ngày thuê:</span
                                            >
                                            <span
                                                class="font-medium text-gray-900"
                                                >{{ rentalDays }} ngày</span
                                            >
                                        </div>

                                        <!-- Breakdown tự động: tháng -> tuần -> ngày -->
                                        <template v-if="priceBreakdown">
                                            <!-- Tháng -->
                                            <div
                                                v-if="
                                                    priceBreakdown.fullMonths >
                                                    0
                                                "
                                                class="flex justify-between text-sm"
                                            >
                                                <span class="text-gray-600">
                                                    {{
                                                        priceBreakdown.fullMonths
                                                    }}
                                                    tháng ({{
                                                        priceBreakdown.fullMonths *
                                                        30
                                                    }}
                                                    ngày):
                                                </span>
                                                <span
                                                    class="font-medium text-gray-900"
                                                    >{{
                                                        formatPrice(
                                                            priceBreakdown.monthsPrice
                                                        )
                                                    }}</span
                                                >
                                            </div>
                                            <!-- Tuần -->
                                            <div
                                                v-if="
                                                    priceBreakdown.fullWeeks > 0
                                                "
                                                class="flex justify-between text-sm"
                                            >
                                                <span class="text-gray-600">
                                                    {{
                                                        priceBreakdown.fullWeeks
                                                    }}
                                                    tuần ({{
                                                        priceBreakdown.fullWeeks *
                                                        7
                                                    }}
                                                    ngày):
                                                </span>
                                                <span
                                                    class="font-medium text-gray-900"
                                                    >{{
                                                        formatPrice(
                                                            priceBreakdown.weeksPrice
                                                        )
                                                    }}</span
                                                >
                                            </div>
                                            <!-- Ngày lẻ -->
                                            <div
                                                v-if="
                                                    priceBreakdown.remainingDays >
                                                    0
                                                "
                                                class="flex justify-between text-sm"
                                            >
                                                <span class="text-gray-600">
                                                    {{
                                                        priceBreakdown.remainingDays
                                                    }}
                                                    ngày:
                                                </span>
                                                <span
                                                    class="font-medium text-gray-900"
                                                    >{{
                                                        formatPrice(
                                                            priceBreakdown.remainingPrice
                                                        )
                                                    }}</span
                                                >
                                            </div>
                                            <!-- Giảm giá -->
                                            <div
                                                v-if="
                                                    priceBreakdown.discount > 0
                                                "
                                                class="flex justify-between text-sm text-primary mt-2 pt-2 border-t border-gray-200"
                                            >
                                                <span>Giảm giá:</span>
                                                <span class="font-medium"
                                                    >-{{
                                                        formatPrice(
                                                            priceBreakdown.discount
                                                        )
                                                    }}</span
                                                >
                                            </div>
                                        </template>

                                        <!-- Fallback nếu chưa có breakdown -->
                                        <template v-else>
                                            <div
                                                class="flex justify-between text-sm"
                                            >
                                                <span class="text-gray-600"
                                                    >Giá gốc:</span
                                                >
                                                <span
                                                    class="font-medium text-gray-900"
                                                    >{{
                                                        formatPrice(
                                                            (selectedRoom?.pricePerDay ||
                                                                0) * rentalDays
                                                        )
                                                    }}</span
                                                >
                                            </div>
                                        </template>
                                    </div>

                                    <div class="pt-3 border-t border-gray-300">
                                        <div
                                            class="flex items-center justify-between mb-2"
                                        >
                                            <span
                                                class="text-gray-700 font-semibold"
                                                >Tổng tiền:</span
                                            >
                                            <span
                                                class="text-2xl font-bold text-primary"
                                                >{{
                                                    formatPrice(totalAmount)
                                                }}</span
                                            >
                                        </div>
                                        <p
                                            v-if="
                                                priceBreakdown &&
                                                priceBreakdown.discount > 0
                                            "
                                            class="text-xs text-primary"
                                        >
                                            * Tiết kiệm
                                            {{
                                                formatPrice(
                                                    priceBreakdown.discount
                                                )
                                            }}
                                            so với thuê theo ngày
                                            <span
                                                v-if="
                                                    priceBreakdown.hasCustomMonthPrice &&
                                                    priceBreakdown.fullMonths >
                                                        0
                                                "
                                            >
                                                ({{ priceBreakdown.fullMonths }}
                                                tháng với giá ưu đãi)
                                            </span>
                                            <span
                                                v-if="
                                                    priceBreakdown.hasCustomWeekPrice &&
                                                    priceBreakdown.fullWeeks > 0
                                                "
                                            >
                                                <span
                                                    v-if="
                                                        priceBreakdown.hasCustomMonthPrice &&
                                                        priceBreakdown.fullMonths >
                                                            0
                                                    "
                                                    >,
                                                </span>
                                                ({{ priceBreakdown.fullWeeks }}
                                                tuần với giá ưu đãi)
                                            </span>
                                        </p>
                                        <p
                                            v-else-if="
                                                priceBreakdown &&
                                                priceBreakdown.fullMonths ===
                                                    0 &&
                                                priceBreakdown.fullWeeks === 0
                                            "
                                            class="text-xs text-gray-500"
                                        >
                                            * Thuê từ 7 ngày trở lên để được áp
                                            dụng giá tuần ưu đãi
                                        </p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            * Đã bao gồm thuế VAT
                                        </p>
                                    </div>
                                </div>

                                <!-- Total (fallback khi chưa có đủ thông tin) -->
                                <div
                                    v-else-if="selectedRoom"
                                    class="p-4 bg-light rounded-lg"
                                >
                                    <div
                                        class="flex items-center justify-between mb-2"
                                    >
                                        <span class="text-gray-600"
                                            >Tổng tiền</span
                                        >
                                        <span
                                            class="text-2xl font-bold text-primary"
                                            >{{
                                                formatPrice(totalAmount)
                                            }}</span
                                        >
                                    </div>
                                    <p class="text-xs text-gray-500">
                                        * Vui lòng điền đầy đủ thông tin để xem
                                        chi tiết
                                    </p>
                                </div>

                                <!-- Error Message -->
                                <div
                                    v-if="
                                        errorMessage ||
                                        bookingForm.errors.room_id ||
                                        bookingForm.errors.personal_info
                                    "
                                    class="p-3 bg-red-50 border border-red-200 rounded-lg"
                                >
                                    <p class="text-sm text-red-600">
                                        {{
                                            errorMessage ||
                                            bookingForm.errors.room_id ||
                                            bookingForm.errors.personal_info
                                        }}
                                    </p>
                                    <button
                                        type="button"
                                        v-if="
                                            bookingForm.errors.personal_info ||
                                            (!hasCompletePersonalInfo &&
                                                isAuthenticated)
                                        "
                                        @click="showPersonalInfoModal = true"
                                        class="mt-2 text-sm text-primary hover:text-secondary font-medium underline"
                                    >
                                        Cập nhật thông tin ngay
                                    </button>
                                </div>

                                <!-- Warning nếu thiếu thông tin cá nhân -->
                                <div
                                    v-if="
                                        isAuthenticated &&
                                        !hasCompletePersonalInfo &&
                                        !errorMessage
                                    "
                                    class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg"
                                >
                                    <div class="flex items-start">
                                        <ExclamationCircleIcon
                                            class="w-5 h-5 text-yellow-600 mr-2 mt-0.5 flex-shrink-0"
                                        />
                                        <div class="flex-1">
                                            <p
                                                class="text-sm font-medium text-yellow-800 mb-1"
                                            >
                                                Thông tin cá nhân chưa đầy đủ
                                            </p>
                                            <p
                                                class="text-sm text-yellow-700 mb-2"
                                            >
                                                Vui lòng cập nhật thông tin căn
                                                cước công dân, ngày cấp CCCD,
                                                nơi cấp CCCD, địa chỉ thường
                                                trú, ngày sinh và giới tính để
                                                đặt phòng.
                                            </p>
                                            <button
                                                type="button"
                                                @click="
                                                    showPersonalInfoModal = true
                                                "
                                                class="text-sm text-yellow-800 hover:text-yellow-900 font-medium underline"
                                            >
                                                Cập nhật thông tin ngay
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <button
                                    type="submit"
                                    :disabled="
                                        !selectedRoom ||
                                        bookingForm.processing ||
                                        !isAuthenticated ||
                                        !hasCompletePersonalInfo
                                    "
                                    class="w-full btn-primary disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <span v-if="!bookingForm.processing"
                                        >Xác nhận đặt phòng</span
                                    >
                                    <span v-else>Đang xử lý...</span>
                                </button>

                                <p
                                    v-if="!isAuthenticated"
                                    class="text-sm text-center text-gray-500 mt-4"
                                >
                                    <Link
                                        href="/login"
                                        class="text-primary hover:text-secondary font-medium"
                                    >
                                        Đăng nhập
                                    </Link>
                                    để đặt phòng
                                </p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Success Modal -->
            <div
                v-if="showSuccessModal"
                class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
                @click="showSuccessModal = false"
            >
                <div
                    class="bg-white rounded-2xl p-8 max-w-md w-full"
                    @click.stop
                >
                    <div class="text-center">
                        <div
                            class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4"
                        >
                            <CheckIcon class="w-8 h-8 text-primary" />
                        </div>
                        <h3 class="text-2xl font-bold text-secondary mb-2">
                            Đặt phòng thành công!
                        </h3>
                        <p class="text-gray-600 mb-6">
                            Chúng tôi sẽ liên hệ với bạn sớm nhất
                        </p>
                        <div class="space-y-3">
                            <button
                                @click="goToHistory"
                                class="w-full btn-primary"
                            >
                                Xem lịch sử thuê
                            </button>
                            <button
                                @click="goToHome"
                                class="w-full btn-outline"
                            >
                                Về trang chủ
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Image Slider Modal -->
            <div
                v-if="showImageSlider && roomImages.length > 0"
                class="fixed inset-0 bg-black/90 flex items-center justify-center z-50"
                @click.self="closeImageSlider"
            >
                <div
                    class="relative w-full h-full flex items-center justify-center p-4"
                >
                    <!-- Close Button -->
                    <button
                        @click="closeImageSlider"
                        class="absolute top-4 right-4 z-20 w-10 h-10 bg-white/10 hover:bg-white/20 rounded-full flex items-center justify-center text-white transition-all"
                    >
                        <XMarkIcon class="w-6 h-6" />
                    </button>

                    <!-- Previous Button -->
                    <button
                        v-if="roomImages.length > 1"
                        @click.stop="previousSliderImage"
                        class="absolute left-4 z-20 w-12 h-12 bg-white/10 hover:bg-white/20 rounded-full flex items-center justify-center text-white transition-all"
                    >
                        <ChevronLeftIcon class="w-6 h-6" />
                    </button>

                    <!-- Next Button -->
                    <button
                        v-if="roomImages.length > 1"
                        @click.stop="nextSliderImage"
                        class="absolute right-4 z-20 w-12 h-12 bg-white/10 hover:bg-white/20 rounded-full flex items-center justify-center text-white transition-all"
                    >
                        <ChevronRightIcon class="w-6 h-6" />
                    </button>

                    <!-- Main Image -->
                    <div
                        class="relative max-w-7xl max-h-full w-full h-full flex items-center justify-center"
                    >
                        <img
                            :src="roomImages[sliderImageIndex]"
                            :alt="`Ảnh phòng ${sliderImageIndex + 1}`"
                            class="max-w-full max-h-full object-contain"
                        />
                    </div>

                    <!-- Image Counter -->
                    <div
                        v-if="roomImages.length > 1"
                        class="absolute bottom-4 left-1/2 transform -translate-x-1/2 bg-black/50 text-white px-4 py-2 rounded-full text-sm z-20"
                    >
                        {{ sliderImageIndex + 1 }} / {{ roomImages.length }}
                    </div>

                    <!-- Thumbnail Navigation -->
                    <div
                        v-if="roomImages.length > 1"
                        class="absolute bottom-20 left-1/2 transform -translate-x-1/2 flex space-x-2 max-w-4xl overflow-x-auto px-4 z-20"
                    >
                        <button
                            v-for="(img, index) in roomImages"
                            :key="index"
                            @click.stop="goToSliderImage(index)"
                            class="flex-shrink-0 w-16 h-16 rounded overflow-hidden border-2 transition-all"
                            :class="
                                sliderImageIndex === index
                                    ? 'border-white'
                                    : 'border-white/30 hover:border-white/60'
                            "
                        >
                            <img
                                :src="img"
                                :alt="`Ảnh ${index + 1}`"
                                class="w-full h-full object-cover"
                            />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Personal Info Modal -->
            <div
                v-if="showPersonalInfoModal"
                class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
                @click.self="showPersonalInfoModal = false"
            >
                <div
                    class="bg-white rounded-2xl p-6 max-w-xl w-full max-h-[90vh] overflow-y-auto"
                    @click.stop
                >
                    <div class="mb-6">
                        <h3 class="text-2xl font-bold text-secondary mb-2">
                            Cập nhật thông tin cá nhân
                        </h3>
                        <p class="text-gray-600 text-sm">
                            Vui lòng điền đầy đủ thông tin để tiếp tục đặt phòng
                        </p>
                    </div>

                    <form
                        @submit.prevent="handleUpdatePersonalInfo($event)"
                        class="space-y-4"
                    >
                        <!-- Name -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Họ và tên <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="personalInfoForm.name"
                                type="text"
                                class="input-field"
                                placeholder="Nhập họ và tên"
                                required
                            />
                            <p
                                v-if="personalInfoForm.errors.name"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ personalInfoForm.errors.name }}
                            </p>
                        </div>

                        <!-- Date of Birth -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Ngày sinh <span class="text-red-500">*</span>
                            </label>
                            <DatePicker
                                v-model="personalInfoForm.date_of_birth"
                                :max="today"
                                format="dd/mm/yyyy"
                                placeholder="dd/mm/yyyy"
                            />
                            <p
                                v-if="personalInfoForm.errors.date_of_birth"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ personalInfoForm.errors.date_of_birth }}
                            </p>
                        </div>

                        <!-- ID Card Image Upload -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Ảnh căn cước công dân
                                <span class="text-red-500">*</span>
                            </label>
                            <div class="space-y-3">
                                <div v-if="idCardImagePreview" class="relative">
                                    <img
                                        :src="idCardImagePreview"
                                        alt="Ảnh CCCD"
                                        class="w-full max-w-md h-auto rounded-lg border-2 border-gray-300"
                                    />
                                    <button
                                        type="button"
                                        @click="clearIdCardImage"
                                        class="absolute top-2 right-2 p-1 bg-red-500 text-white rounded-full hover:bg-red-600"
                                    >
                                        <XMarkIcon class="w-5 h-5" />
                                    </button>
                                </div>
                                <div
                                    v-else-if="user?.id_card_image"
                                    class="relative"
                                >
                                    <img
                                        :src="user.id_card_image"
                                        alt="Ảnh CCCD hiện tại"
                                        class="w-full max-w-md h-auto rounded-lg border-2 border-gray-300"
                                    />
                                    <p class="mt-2 text-xs text-gray-500">
                                        Ảnh CCCD hiện tại
                                    </p>
                                </div>
                                <input
                                    ref="idCardImageInput"
                                    type="file"
                                    accept="image/*"
                                    @change="handleIdCardImageUpload"
                                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:bg-secondary"
                                />
                                <p
                                    v-if="personalInfoForm.errors.id_card_image"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ personalInfoForm.errors.id_card_image }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    Tải lên ảnh căn cước công dân.
                                </p>
                            </div>
                        </div>

                        <!-- ID Card Number -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Số căn cước công dân
                                <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="personalInfoForm.id_card_number"
                                type="text"
                                class="input-field"
                                placeholder="Nhập số căn cước công dân"
                                required
                            />
                            <p
                                v-if="personalInfoForm.errors.id_card_number"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ personalInfoForm.errors.id_card_number }}
                            </p>
                        </div>

                        <!-- ID Card Issue Date -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Ngày cấp CCCD
                                <span class="text-red-500">*</span>
                            </label>
                            <DatePicker
                                v-model="personalInfoForm.id_card_issue_date"
                                :max="today"
                                format="dd/mm/yyyy"
                                placeholder="dd/mm/yyyy"
                            />
                            <p
                                v-if="
                                    personalInfoForm.errors.id_card_issue_date
                                "
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ personalInfoForm.errors.id_card_issue_date }}
                            </p>
                        </div>

                        <!-- ID Card Issue Place -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Nơi cấp CCCD <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="personalInfoForm.id_card_issue_place"
                                type="text"
                                list="id-card-issue-places"
                                class="input-field"
                                placeholder="Chọn hoặc nhập nơi cấp CCCD"
                                required
                            />
                            <datalist id="id-card-issue-places">
                                <option
                                    value="Cục Cảnh sát quản lý hành chính về trật tự xã hội"
                                >
                                    Cục Cảnh sát quản lý hành chính về trật tự
                                    xã hội
                                </option>
                                <option
                                    value="Cục Cảnh sát đăng ký quản lý cư trú và dữ liệu Quốc gia về dân cư"
                                >
                                    Cục Cảnh sát đăng ký quản lý cư trú và dữ
                                    liệu Quốc gia về dân cư
                                </option>
                            </datalist>
                            <p
                                v-if="
                                    personalInfoForm.errors.id_card_issue_place
                                "
                                class="mt-1 text-sm text-red-600"
                            >
                                {{
                                    personalInfoForm.errors.id_card_issue_place
                                }}
                            </p>
                            <p class="mt-1 text-xs text-gray-500">
                                Bạn có thể chọn từ danh sách hoặc nhập tùy chỉnh
                            </p>
                        </div>

                        <!-- Permanent Address -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Địa chỉ thường trú
                                <span class="text-red-500">*</span>
                            </label>
                            <textarea
                                v-model="personalInfoForm.permanent_address"
                                class="input-field"
                                rows="3"
                                placeholder="Nhập địa chỉ thường trú"
                                required
                            ></textarea>
                            <p
                                v-if="personalInfoForm.errors.permanent_address"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ personalInfoForm.errors.permanent_address }}
                            </p>
                        </div>

                        <!-- Gender -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Giới tính <span class="text-red-500">*</span>
                            </label>
                            <div class="flex space-x-6">
                                <label class="flex items-center cursor-pointer">
                                    <input
                                        type="radio"
                                        v-model="personalInfoForm.gender"
                                        value="male"
                                        class="w-4 h-4 text-primary border-gray-300 focus:ring-primary"
                                        required
                                    />
                                    <span class="ml-2 text-gray-700">Nam</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input
                                        type="radio"
                                        v-model="personalInfoForm.gender"
                                        value="female"
                                        class="w-4 h-4 text-primary border-gray-300 focus:ring-primary"
                                    />
                                    <span class="ml-2 text-gray-700">Nữ</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input
                                        type="radio"
                                        v-model="personalInfoForm.gender"
                                        value="other"
                                        class="w-4 h-4 text-primary border-gray-300 focus:ring-primary"
                                    />
                                    <span class="ml-2 text-gray-700">Khác</span>
                                </label>
                            </div>
                            <p
                                v-if="personalInfoForm.errors.gender"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ personalInfoForm.errors.gender }}
                            </p>
                        </div>

                        <!-- Error Message -->
                        <div
                            v-if="personalInfoForm.errors.message"
                            class="p-3 bg-red-50 border border-red-200 rounded-lg"
                        >
                            <p class="text-sm text-red-600">
                                {{ personalInfoForm.errors.message }}
                            </p>
                        </div>

                        <!-- Buttons -->
                        <div class="flex space-x-3 pt-4">
                            <button
                                type="button"
                                @click="showPersonalInfoModal = false"
                                class="flex-1 btn-outline"
                            >
                                Hủy
                            </button>
                            <button
                                type="button"
                                @click="handleUpdatePersonalInfo"
                                :disabled="personalInfoForm.processing"
                                class="flex-1 btn-primary disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span v-if="!personalInfoForm.processing"
                                    >Cập nhật</span
                                >
                                <span v-else>Đang xử lý...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from "vue";
import { Head, router, useForm as useInertiaForm, Link } from "@inertiajs/vue3";
import { useForm, useField } from "vee-validate";
import * as yup from "yup";
import AppLayout from "@/layouts/AppLayout.vue";
import { useAuth } from "@/composables/useAuth";
import RoomCard from "@/components/booking/RoomCard.vue";
import DatePicker from "@/components/ui/DatePicker.vue";
import {
    ChevronLeftIcon,
    ChevronRightIcon,
    ExclamationCircleIcon,
    BuildingOfficeIcon,
    XMarkIcon,
    CheckIcon,
    HomeIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    house: {
        type: Object,
        required: true,
    },
    rooms: {
        type: Array,
        default: () => [],
    },
});

const { user, isAuthenticated } = useAuth();

const selectedRoom = ref(null);
const errorMessage = ref("");
const showSuccessModal = ref(false);
const showPersonalInfoModal = ref(false);
const showImageSlider = ref(false);
const sliderImageIndex = ref(0);
const idCardImageInput = ref(null);
const idCardImagePreview = ref(null);

// Kiểm tra xem user có đầy đủ thông tin cá nhân không
const hasCompletePersonalInfo = computed(() => {
    if (!user.value) return false;
    return !!(
        user.value.id_card_number &&
        user.value.id_card_issue_date &&
        user.value.id_card_issue_place &&
        user.value.permanent_address &&
        user.value.date_of_birth &&
        user.value.gender
    );
});

// Validation schema with Yup
const bookingSchema = yup.object({
    start_date: yup
        .string()
        .required("Vui lòng chọn ngày bắt đầu")
        .test("valid-date", "Ngày không hợp lệ", function (value) {
            if (!value) return true;
            const date = new Date(value + "T00:00:00");
            return !isNaN(date.getTime());
        })
        .test(
            "min-date",
            "Ngày bắt đầu phải từ hôm nay trở đi",
            function (value) {
                if (!value) return true;
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                const selectedDate = new Date(value + "T00:00:00");
                return selectedDate >= today;
            }
        ),
    end_date: yup
        .string()
        .required("Vui lòng chọn ngày kết thúc")
        .test("valid-date", "Ngày không hợp lệ", function (value) {
            if (!value) return true;
            const date = new Date(value + "T00:00:00");
            return !isNaN(date.getTime());
        })
        .test("min-date", function (value) {
            if (!value) return true;
            const startDate = this.parent.start_date;
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const selectedDate = new Date(value + "T00:00:00");

            // Nếu đã chọn ngày bắt đầu, ngày kết thúc phải sau ngày bắt đầu
            if (startDate) {
                const start = new Date(startDate + "T00:00:00");
                if (selectedDate <= start) {
                    return this.createError({
                        message: "Ngày kết thúc phải sau ngày bắt đầu",
                    });
                }
            } else {
                // Nếu chưa chọn ngày bắt đầu, ngày kết thúc phải từ hôm nay trở đi
                if (selectedDate < today) {
                    return this.createError({
                        message: "Ngày kết thúc phải từ hôm nay trở đi",
                    });
                }
            }
            return true;
        }),
    room_id: yup
        .mixed()
        .required("Vui lòng chọn phòng")
        .test("required", "Vui lòng chọn phòng", function (value) {
            return value !== null && value !== undefined && value !== "";
        }),
    notes: yup.string().nullable(),
});

// VeeValidate form
const { handleSubmit, values, setFieldValue, errors, validate } = useForm({
    validationSchema: bookingSchema,
    initialValues: {
        house_id: props.house.id,
        room_id: null,
        start_date: "",
        end_date: "",
        total_price: 0,
        discount_amount: 0,
        notes: "",
    },
    validateOnMount: false,
    validateOnBlur: true,
    validateOnChange: true,
});

// Set house_id initially
setFieldValue("house_id", props.house.id);

// Inertia form for submission
const bookingForm = useInertiaForm({
    house_id: props.house.id,
    room_id: null,
    start_date: "",
    end_date: "",
    total_price: 0,
    discount_amount: 0,
    notes: "",
});

// Sync VeeValidate values with Inertia form (only for submission)
// We don't need to watch all values, just update when submitting

const personalInfoForm = useInertiaForm(
    {
        name: "",
        id_card_number: "",
        id_card_issue_date: "",
        id_card_issue_place: "",
        permanent_address: "",
        date_of_birth: "",
        gender: "",
        id_card_image: null,
    },
    {
        forceFormData: true,
    }
);

// Cập nhật form khi user thay đổi
watch(
    () => user.value,
    (newUser) => {
        if (newUser) {
            personalInfoForm.name = newUser.name || "";
            personalInfoForm.id_card_number = newUser.id_card_number || "";
            personalInfoForm.id_card_issue_date =
                newUser.id_card_issue_date || "";
            personalInfoForm.id_card_issue_place =
                newUser.id_card_issue_place || "";
            personalInfoForm.permanent_address =
                newUser.permanent_address || "";
            personalInfoForm.date_of_birth = newUser.date_of_birth || "";
            personalInfoForm.gender = newUser.gender || "";
            // Không set id_card_image từ user vì nó là file, chỉ hiển thị preview nếu có
            if (newUser.id_card_image && !idCardImagePreview.value) {
                // Không cần set preview từ URL vì đã có trong template
            }
        }
    },
    { immediate: true }
);

const today = computed(() => {
    return new Date().toISOString().split("T")[0];
});

// Use VeeValidate fields for date inputs
const { value: startDate, errorMessage: startDateError } =
    useField("start_date");
const { value: endDate, errorMessage: endDateError } = useField("end_date");
const { value: roomId } = useField("room_id");

const roomsByFloor = computed(() => {
    const floors = {};
    props.rooms.forEach((room) => {
        if (!floors[room.floor]) {
            floors[room.floor] = [];
        }
        floors[room.floor].push(room);
    });
    return floors;
});

const sortedFloors = computed(() => {
    return Object.keys(roomsByFloor.value).sort((a, b) => b - a);
});

// Hàm này không còn cần thiết vì người dùng tự nhập endDate

// Tính số ngày từ startDate đến endDate
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

// Tính giá dựa trên giá có sẵn (ngày, tuần, tháng), tự động phân tích thành tháng/tuần/ngày
// Ưu tiên sử dụng giá ưu đãi (tháng/tuần) để khách hàng được tiết kiệm
const calculatePrice = (room, days) => {
    if (!room || !days || days <= 0)
        return { total: 0, discount: 0, breakdown: null };

    const pricePerDay = room.pricePerDay || 0;
    const pricePerWeek = room.pricePerWeek || null;
    const pricePerMonth = room.pricePerMonth || null;

    // Phân tích số ngày thành tháng/tuần/ngày
    const { fullMonths, fullWeeks, remainingDays } = calculateBreakdown(days);

    // Tính giá cho từng phần - ưu tiên giá ưu đãi
    // 1. Tháng: Nếu có giá tháng riêng (ưu đãi) thì dùng, nếu không thì tính giá ngày × 30
    let monthsPrice = 0;
    if (fullMonths > 0) {
        if (pricePerMonth !== null && pricePerMonth > 0) {
            // Sử dụng giá tháng ưu đãi
            monthsPrice = fullMonths * pricePerMonth;
        } else {
            // Fallback: tính từ giá ngày × 30
            monthsPrice = fullMonths * pricePerDay * 30;
        }
    }

    // 2. Tuần: Nếu có giá tuần riêng (ưu đãi) thì dùng, nếu không thì tính giá ngày × 7
    let weeksPrice = 0;
    if (fullWeeks > 0) {
        if (pricePerWeek !== null && pricePerWeek > 0) {
            // Sử dụng giá tuần ưu đãi
            weeksPrice = fullWeeks * pricePerWeek;
        } else {
            // Fallback: tính từ giá ngày × 7
            weeksPrice = fullWeeks * pricePerDay * 7;
        }
    }

    // 3. Ngày lẻ: Luôn tính theo giá ngày
    const remainingPrice = remainingDays * pricePerDay;

    // Tổng giá sau khi áp dụng giá ưu đãi
    const total = monthsPrice + weeksPrice + remainingPrice;

    // Tính giảm giá so với việc thuê toàn bộ theo ngày (để hiển thị tiết kiệm)
    const fullDayPrice = days * pricePerDay;
    const discount = Math.max(0, fullDayPrice - total);

    const breakdown = {
        fullMonths,
        fullWeeks,
        remainingDays,
        monthsPrice: Math.round(monthsPrice),
        weeksPrice: Math.round(weeksPrice),
        remainingPrice: Math.round(remainingPrice),
        discount: Math.round(discount),
        hasCustomWeekPrice: pricePerWeek !== null && pricePerWeek > 0,
        hasCustomMonthPrice: pricePerMonth !== null && pricePerMonth > 0,
    };

    return {
        total: Math.round(total),
        discount: Math.round(discount),
        breakdown,
    };
};

const totalAmount = computed(() => {
    if (!selectedRoom.value) return 0;

    const days = calculateDays(startDate.value, endDate.value);

    if (days === 0) return 0;

    const result = calculatePrice(selectedRoom.value, days);
    return result.total;
});

// Computed để lấy breakdown chi tiết
const priceBreakdown = computed(() => {
    if (!selectedRoom.value) return null;

    const days = calculateDays(startDate.value, endDate.value);

    if (days === 0) return null;

    const result = calculatePrice(selectedRoom.value, days);
    return result.breakdown;
});

// Computed để tính tổng giảm giá
const totalDiscount = computed(() => {
    if (!selectedRoom.value) return 0;

    const days = calculateDays(startDate.value, endDate.value);

    if (days === 0) return 0;

    const result = calculatePrice(selectedRoom.value, days);
    return result.discount;
});

// Tính số ngày thuê để hiển thị
const rentalDays = computed(() => {
    return calculateDays(startDate.value, endDate.value);
});

const formatPrice = (price) => {
    return new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
    }).format(price);
};

// Format ngày để hiển thị dễ đọc
const formatDate = (dateString) => {
    if (!dateString) return "";
    const date = new Date(dateString);
    return date.toLocaleDateString("vi-VN", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
    });
};

const selectRoom = (room) => {
    // Only allow selecting available rooms
    if (room.status === "available") {
        selectedRoom.value = room;
        setFieldValue("room_id", room.id);
        bookingForm.room_id = room.id;
        errorMessage.value = "";
    }
    // Nếu phòng không available, không làm gì cả (component RoomCard đã disable click)
};

// Watch để cập nhật total_price và discount_amount khi thay đổi
const updateTotalPrice = () => {
    bookingForm.total_price = totalAmount.value;
    bookingForm.discount_amount = totalDiscount.value;
};

// Watch selectedRoom, start_date, end_date để cập nhật total_price và sync với form
watch(
    [() => selectedRoom.value, () => startDate.value, () => endDate.value],
    () => {
        updateTotalPrice();
        // Sync room_id with VeeValidate form
        if (selectedRoom.value) {
            setFieldValue("room_id", selectedRoom.value.id);
        }
    },
    { deep: true }
);

// Watch rooms để tự động bỏ chọn phòng nếu nó không còn available
watch(
    () => props.rooms,
    (newRooms) => {
        if (selectedRoom.value) {
            const room = newRooms.find((r) => r.id === selectedRoom.value.id);
            if (!room || room.status !== "available") {
                // Phòng đã chọn không còn available, tự động bỏ chọn (không hiển thị thông báo)
                selectedRoom.value = null;
                setFieldValue("room_id", null);
                bookingForm.room_id = null;
            } else {
                // Cập nhật thông tin phòng đã chọn với dữ liệu mới
                selectedRoom.value = room;
            }
        }
    },
    { deep: true }
);

// Loading state for room status check
const isCheckingRoomStatus = ref(false);
let reloadTimeout = null;
let isReloading = false;
let currentRequestId = null;
let lastReloadedDates = { start: null, end: null };

// Debounce function với cancel capability
const debounce = (func, wait) => {
    let timeout;
    const debounced = function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
    debounced.cancel = () => {
        clearTimeout(timeout);
    };
    return debounced;
};

// Validate dates before reloading
const validateDates = (startDate, endDate) => {
    if (!startDate || !endDate) {
        return false;
    }

    const start = new Date(startDate + "T00:00:00");
    const end = new Date(endDate + "T00:00:00");

    if (isNaN(start.getTime()) || isNaN(end.getTime())) {
        return false;
    }

    if (end <= start) {
        return false;
    }

    // Check if dates are the same as last reloaded (avoid unnecessary reloads)
    if (
        lastReloadedDates.start === startDate &&
        lastReloadedDates.end === endDate
    ) {
        return false;
    }

    return true;
};

// Function to reload rooms with new dates
const reloadRoomsWithDates = (newStartDate, newEndDate) => {
    // Validate dates before proceeding
    if (!validateDates(newStartDate, newEndDate)) {
        return;
    }

    // Generate unique request ID
    const requestId = Date.now() + Math.random();
    currentRequestId = requestId;

    // Preserve selected room ID
    const selectedRoomId = selectedRoom.value?.id;

    isCheckingRoomStatus.value = true;
    isReloading = true;

    // Update last reloaded dates
    lastReloadedDates = { start: newStartDate, end: newEndDate };

    router.reload({
        only: ["rooms"],
        data: {
            start_date: newStartDate,
            end_date: newEndDate,
        },
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            // Check if this request is still current (not superseded by a newer one)
            if (currentRequestId !== requestId) {
                return;
            }

            isCheckingRoomStatus.value = false;
            isReloading = false;

            // Restore selected room after reload, but only if it's still available
            if (selectedRoomId) {
                const room = props.rooms.find((r) => r.id === selectedRoomId);
                if (room) {
                    // Check if room is still available for the selected dates
                    if (room.status === "available") {
                        selectedRoom.value = room;
                        setFieldValue("room_id", room.id);
                        bookingForm.room_id = room.id;
                    } else {
                        // Room is no longer available, deselect it silently
                        selectedRoom.value = null;
                        setFieldValue("room_id", null);
                        bookingForm.room_id = null;
                    }
                } else {
                    // Room not found, deselect it
                    selectedRoom.value = null;
                    setFieldValue("room_id", null);
                    bookingForm.room_id = null;
                }
            }
        },
        onError: () => {
            // Check if this request is still current
            if (currentRequestId !== requestId) {
                return;
            }
            isCheckingRoomStatus.value = false;
            isReloading = false;
        },
        onFinish: () => {
            // Check if this request is still current
            if (currentRequestId !== requestId) {
                return;
            }
            isCheckingRoomStatus.value = false;
            isReloading = false;
        },
    });
};

// Debounced version of reloadRoomsWithDates (reduced to 300ms for better responsiveness)
const debouncedReloadRooms = debounce(reloadRoomsWithDates, 300);

// Watch start_date và end_date để reload rooms với status mới
watch(
    [() => startDate.value, () => endDate.value],
    ([newStartDate, newEndDate], [oldStartDate, oldEndDate]) => {
        // Cancel any pending debounced calls if dates are being cleared
        if (!newStartDate || !newEndDate) {
            debouncedReloadRooms.cancel();
            isCheckingRoomStatus.value = false;

            // If dates are cleared, also clear selection if room becomes unavailable
            if (selectedRoom.value) {
                const room = props.rooms.find(
                    (r) => r.id === selectedRoom.value.id
                );
                if (room && room.status !== "available") {
                    selectedRoom.value = null;
                    setFieldValue("room_id", null);
                    bookingForm.room_id = null;
                }
            }

            // Reset last reloaded dates
            lastReloadedDates = { start: null, end: null };
            return;
        }

        // Only reload if both dates are selected and valid
        // Skip if dates haven't actually changed
        if (newStartDate === oldStartDate && newEndDate === oldEndDate) {
            return;
        }

        // Quick validation before debouncing
        const start = new Date(newStartDate + "T00:00:00");
        const end = new Date(newEndDate + "T00:00:00");

        if (isNaN(start.getTime()) || isNaN(end.getTime()) || end <= start) {
            // Invalid dates, cancel any pending reloads
            debouncedReloadRooms.cancel();
            isCheckingRoomStatus.value = false;
            return;
        }

        // Debounce the reload
        debouncedReloadRooms(newStartDate, newEndDate);
    },
    { immediate: false }
);

const handleBooking = handleSubmit(
    (formValues) => {
        if (!isAuthenticated.value) {
            errorMessage.value = "Vui lòng đăng nhập để đặt phòng";
            return;
        }

        // Kiểm tra thông tin cá nhân đầy đủ
        if (!hasCompletePersonalInfo.value) {
            showPersonalInfoModal.value = true;
            errorMessage.value =
                "Vui lòng cập nhật đầy đủ thông tin cá nhân trước khi đặt phòng";
            return;
        }

        // Update Inertia form with validated values
        bookingForm.house_id = formValues.house_id;
        bookingForm.room_id = formValues.room_id;
        bookingForm.start_date = formValues.start_date;
        bookingForm.end_date = formValues.end_date;
        bookingForm.total_price = totalAmount.value;
        bookingForm.discount_amount = totalDiscount.value;
        bookingForm.notes = formValues.notes || "";

        errorMessage.value = "";

        bookingForm.post("/booking", {
            preserveScroll: false,
            preserveState: false,
            onSuccess: () => {
                // Inertia::location() should trigger a full page redirect
                // If we reach here, the redirect should have already happened
                // Just wait a moment to see if redirect occurs
            },
            onError: (errors) => {
                console.error("Booking errors:", errors);

                // Handle different error types
                if (errors.personal_info) {
                    showPersonalInfoModal.value = true;
                    errorMessage.value = errors.personal_info;
                } else if (errors.room_id) {
                    errorMessage.value = errors.room_id;
                } else if (errors.message) {
                    errorMessage.value = errors.message;
                } else {
                    // Generic error
                    const errorMessages = Object.values(errors).flat();
                    if (errorMessages.length > 0) {
                        errorMessage.value = errorMessages[0];
                    } else {
                        errorMessage.value =
                            "Có lỗi xảy ra khi đặt phòng. Vui lòng thử lại.";
                    }
                }
            },
        });
    },
    (validationErrors) => {
        // Callback khi validation fail - hiển thị errors và scroll to first error
        console.log("Validation errors:", validationErrors);
        console.log("Form values:", values);

        // Hiển thị error message tổng quát nếu có
        const errorMessages = Object.values(validationErrors).filter(Boolean);
        if (errorMessages.length > 0) {
            errorMessage.value =
                errorMessages[0] || "Vui lòng kiểm tra lại thông tin đã nhập";
        }

        // Scroll to first error field
        const firstErrorField = Object.keys(validationErrors)[0];
        if (firstErrorField) {
            // Try to find the input field
            const errorElement =
                document.querySelector(`[name="${firstErrorField}"]`) ||
                document.querySelector(`[data-field="${firstErrorField}"]`) ||
                document.querySelector(
                    `input[aria-label*="${firstErrorField}"]`
                );
            if (errorElement) {
                setTimeout(() => {
                    errorElement.scrollIntoView({
                        behavior: "smooth",
                        block: "center",
                    });
                    errorElement.focus();
                }, 100);
            }
        }
    }
);

// Wrapper function để đảm bảo form submit luôn được xử lý
const handleBookingSubmit = (event) => {
    event.preventDefault();
    console.log("Form submitted, validating...");
    console.log("Current values:", values);
    console.log("Selected room:", selectedRoom.value);
    console.log("Current errors:", errors);

    // Gọi handleBooking (handleSubmit sẽ tự động validate và gọi callback nếu pass)
    // handleSubmit trả về một function, gọi nó với event
    const submitHandler = handleBooking;
    submitHandler(event);
};

// Xử lý upload ảnh CCCD
const handleIdCardImageUpload = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    // Kiểm tra loại file
    if (!file.type.startsWith("image/")) {
        return;
    }

    // Hiển thị preview
    const reader = new FileReader();
    reader.onload = (e) => {
        idCardImagePreview.value = e.target.result;
    };
    reader.readAsDataURL(file);

    // Thêm file vào form
    personalInfoForm.id_card_image = file;
};

// Xóa ảnh preview
const clearIdCardImage = () => {
    idCardImagePreview.value = null;
    personalInfoForm.id_card_image = null;
    if (idCardImageInput.value) {
        idCardImageInput.value.value = "";
    }
};

const handleUpdatePersonalInfo = (event) => {
    // Ngăn chặn event bubbling để tránh submit form đặt phòng
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    personalInfoForm.post("/profile/personal-info", {
        preserveScroll: true,
        onSuccess: () => {
            alert("Cập nhật thông tin cá nhân thành công!");
            errorMessage.value = "";
            showPersonalInfoModal.value = false;
            // Reload page để cập nhật user info - reload toàn bộ để đảm bảo dữ liệu được cập nhật
            router.reload({ only: ["auth", "house", "rooms"] });
        },
        onError: (errors) => {
            console.error("Personal info errors:", errors);
        },
    });
};

const goBack = () => {
    window.history.length > 1 ? window.history.back() : router.visit("/");
};

const goToHistory = () => {
    router.visit("/history");
};

const goToHome = () => {
    router.visit("/");
};

// Room images management
const roomImages = computed(() => {
    if (!selectedRoom.value?.images) return [];
    if (Array.isArray(selectedRoom.value.images)) {
        return selectedRoom.value.images.filter(
            (img) => img && img.trim() !== ""
        );
    }
    return [];
});

// Image slider functions
const openImageSlider = (index = 0) => {
    if (roomImages.value.length === 0) return;
    sliderImageIndex.value =
        index >= 0 && index < roomImages.value.length ? index : 0;
    showImageSlider.value = true;
    // Prevent body scroll when modal is open
    document.body.style.overflow = "hidden";
};

const closeImageSlider = () => {
    showImageSlider.value = false;
    // Restore body scroll
    document.body.style.overflow = "";
};

const nextSliderImage = () => {
    if (roomImages.value.length === 0) return;
    sliderImageIndex.value =
        (sliderImageIndex.value + 1) % roomImages.value.length;
};

const previousSliderImage = () => {
    if (roomImages.value.length === 0) return;
    sliderImageIndex.value =
        sliderImageIndex.value === 0
            ? roomImages.value.length - 1
            : sliderImageIndex.value - 1;
};

const goToSliderImage = (index) => {
    if (index >= 0 && index < roomImages.value.length) {
        sliderImageIndex.value = index;
    }
};

// Keyboard navigation for image slider
const handleKeydown = (event) => {
    if (!showImageSlider.value) return;

    if (event.key === "Escape") {
        closeImageSlider();
    } else if (event.key === "ArrowLeft") {
        previousSliderImage();
    } else if (event.key === "ArrowRight") {
        nextSliderImage();
    }
};

// Add keyboard event listener
onMounted(() => {
    window.addEventListener("keydown", handleKeydown);
});

// Cleanup on unmount
onUnmounted(() => {
    window.removeEventListener("keydown", handleKeydown);
    // Restore body scroll in case component unmounts with modal open
    document.body.style.overflow = "";

    // Cancel any pending debounced calls
    if (debouncedReloadRooms) {
        debouncedReloadRooms.cancel();
    }

    // Invalidate current request ID to prevent callbacks from executing
    currentRequestId = null;

    // Clear any pending timeouts
    if (reloadTimeout) {
        clearTimeout(reloadTimeout);
    }
});
</script>
