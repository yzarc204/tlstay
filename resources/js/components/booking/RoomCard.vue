<template>
    <div
        class="relative p-2.5 rounded-lg border-2 transition-all cursor-pointer"
        :class="roomClasses"
        @click="handleClick"
    >
        <!-- Selected Indicator -->
        <div v-if="selected" class="absolute top-1 left-1 z-10">
            <div
                class="w-5 h-5 bg-green-500 rounded-full flex items-center justify-center"
            >
                <CheckIcon class="w-3 h-3 text-white" stroke-width="3" />
            </div>
        </div>

        <!-- Room Number -->
        <div class="text-center mb-1">
            <p class="text-base font-bold" :class="statusClasses.text">
                {{ room.roomNumber }}
            </p>
        </div>

        <!-- Room Icon -->
        <div class="flex justify-center mb-1">
            <HomeIcon class="w-8 h-8" :class="statusClasses.icon" />
        </div>

        <!-- Room Info -->
        <div class="text-center">
            <p class="text-xs text-gray-600 mb-0.5">{{ room.area }}m²</p>
            <p class="text-xs font-semibold" :class="statusClasses.text">
                {{ formatPrice(room.pricePerDay || room.price) }}/ngày
            </p>
        </div>

        <!-- Status Badge -->
        <div class="absolute top-1 right-1">
            <span
                class="badge text-[10px] px-1.5 py-0.5"
                :class="statusClasses.badge"
            >
                {{ statusText }}
            </span>
        </div>

        <!-- Tenant Info (if active) -->
        <div
            v-if="room.status === 'active'"
            class="mt-1 pt-1 border-t text-center"
        >
            <p class="text-[10px] text-gray-500">{{ room.tenant || "N/A" }}</p>
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";
import { CheckIcon, HomeIcon } from "@heroicons/vue/24/outline";

const props = defineProps({
    room: {
        type: Object,
        required: true,
    },
    selected: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["select"]);

const statusText = computed(() => {
    const statusMap = {
        available: "Trống",
        active: "Đang ở",
    };
    return statusMap[props.room.status] || "Trống";
});

const statusClasses = computed(() => {
    const status = props.room.status;
    const classes = {
        available: {
            text: "text-primary",
            icon: "text-primary",
            badge: "bg-primary-100 text-primary-700",
        },
        active: {
            text: "text-red-600",
            icon: "text-red-400",
            badge: "bg-red-100 text-red-700",
        },
    };
    return classes[status] || classes["available"];
});

const roomClasses = computed(() => {
    if (props.room.status === "available") {
        if (props.selected) {
            return "border-green-500 bg-green-50 shadow-lg ring-2 ring-green-200 ring-offset-2";
        }
        return "border-primary bg-primary-50 hover:bg-primary-100 hover:shadow-lg";
    } else if (props.room.status === "active") {
        return "border-red-300 bg-red-50 cursor-not-allowed opacity-80";
    } else {
        // Default to available style if status is unknown
        return "border-primary bg-primary-50 hover:bg-primary-100 hover:shadow-lg";
    }
});

const formatPrice = (price) => {
    return new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
    }).format(price);
};

const handleClick = () => {
    if (props.room.status === "available") {
        emit("select", props.room);
    }
};
</script>
