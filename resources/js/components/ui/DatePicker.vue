<template>
    <div class="relative">
        <div class="relative">
            <input
                ref="inputRef"
                v-model="inputValue"
                type="text"
                :placeholder="placeholder || computedPlaceholder"
                :class="[
                    'w-full px-4 py-2 pr-10 border rounded-lg focus:ring-2 focus:outline-none transition-colors',
                    error
                        ? 'border-red-500 focus:ring-red-500'
                        : 'border-gray-300 focus:ring-primary',
                ]"
                :disabled="disabled"
                @input="handleInput"
                @blur="handleBlur"
                @keydown="handleKeydown"
            />
            <button
                type="button"
                @click.stop="toggleCalendar"
                :disabled="disabled"
                class="absolute right-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400 hover:text-gray-600 focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <CalendarIcon class="h-5 w-5" />
            </button>
        </div>

        <!-- Calendar Popup -->
        <Transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <div
                v-if="isOpen"
                ref="calendarRef"
                class="absolute z-50 mt-2 bg-white rounded-lg shadow-lg border border-gray-200 p-4 w-80"
            >
                <!-- Calendar Header -->
                <div class="flex items-center justify-between mb-4">
                    <button
                        type="button"
                        @click="previousMonth"
                        class="p-1 hover:bg-gray-100 rounded"
                    >
                        <ChevronLeftIcon class="h-5 w-5 text-gray-600" />
                    </button>
                    <div class="flex items-center space-x-2">
                        <select
                            v-model.number="currentMonth"
                            @change="updateCalendar"
                            class="text-sm font-semibold text-gray-900 border-none focus:ring-0 cursor-pointer"
                        >
                            <option
                                v-for="(month, index) in months"
                                :key="index"
                                :value="index"
                            >
                                {{ month }}
                            </option>
                        </select>
                        <select
                            v-model.number="currentYear"
                            @change="updateCalendar"
                            class="text-sm font-semibold text-gray-900 border-none focus:ring-0 cursor-pointer"
                        >
                            <option
                                v-for="year in years"
                                :key="year"
                                :value="year"
                            >
                                {{ year }}
                            </option>
                        </select>
                    </div>
                    <button
                        type="button"
                        @click="nextMonth"
                        class="p-1 hover:bg-gray-100 rounded"
                    >
                        <ChevronRightIcon class="h-5 w-5 text-gray-600" />
                    </button>
                </div>

                <!-- Calendar Days -->
                <div class="grid grid-cols-7 gap-1 mb-2">
                    <div
                        v-for="day in weekDays"
                        :key="day"
                        class="text-center text-xs font-medium text-gray-500 py-1"
                    >
                        {{ day }}
                    </div>
                </div>

                <div class="grid grid-cols-7 gap-1">
                    <div
                        v-for="day in calendarDays"
                        :key="day.key"
                        :class="[
                            'text-center py-2 rounded cursor-pointer text-sm transition-colors',
                            day.isCurrentMonth
                                ? day.isSelected
                                    ? 'bg-primary text-white font-semibold'
                                    : day.isToday
                                    ? 'bg-primary/10 text-primary font-semibold'
                                    : day.isDisabled
                                    ? 'text-gray-300 cursor-not-allowed'
                                    : 'text-gray-700 hover:bg-gray-100'
                                : 'text-gray-300',
                        ]"
                        @click="selectDate(day)"
                    >
                        {{ day.date }}
                    </div>
                </div>

                <!-- Today Button -->
                <div class="mt-4 pt-4 border-t">
                    <button
                        type="button"
                        @click="selectToday"
                        class="w-full text-sm text-primary hover:text-primary-600 font-medium"
                    >
                        Hôm nay
                    </button>
                </div>
            </div>
        </Transition>

        <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
        <p v-if="hint && !error" class="mt-1 text-xs text-gray-500">
            {{ hint }}
        </p>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from "vue";
import {
    CalendarIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    modelValue: {
        type: String,
        default: "",
    },
    placeholder: {
        type: String,
        default: "",
    },
    format: {
        type: String,
        default: "dd/mm/yyyy",
        validator: (value) => {
            // Supported formats: dd/mm/yyyy, mm/dd/yyyy, yyyy-mm-dd, dd-mm-yyyy, mm-dd-yyyy
            return [
                "dd/mm/yyyy",
                "mm/dd/yyyy",
                "yyyy-mm-dd",
                "dd-mm-yyyy",
                "mm-dd-yyyy",
            ].includes(value);
        },
    },
    error: {
        type: String,
        default: "",
    },
    hint: {
        type: String,
        default: "",
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    min: {
        type: String,
        default: "",
    },
    max: {
        type: String,
        default: "",
    },
});

const emit = defineEmits(["update:modelValue"]);

const inputRef = ref(null);
const calendarRef = ref(null);
const isOpen = ref(false);
const currentMonth = ref(new Date().getMonth());
const currentYear = ref(new Date().getFullYear());
const inputValue = ref("");

const weekDays = ["CN", "T2", "T3", "T4", "T5", "T6", "T7"];
const months = [
    "Tháng 1",
    "Tháng 2",
    "Tháng 3",
    "Tháng 4",
    "Tháng 5",
    "Tháng 6",
    "Tháng 7",
    "Tháng 8",
    "Tháng 9",
    "Tháng 10",
    "Tháng 11",
    "Tháng 12",
];

// Generate years (current year ± 10)
const years = computed(() => {
    const current = new Date().getFullYear();
    const yearList = [];
    for (let i = current - 10; i <= current + 10; i++) {
        yearList.push(i);
    }
    return yearList;
});

// Format placeholder based on format prop
const computedPlaceholder = computed(() => {
    if (props.placeholder) return props.placeholder;
    return props.format;
});

// Get separator based on format
const separator = computed(() => {
    return props.format.includes("/") ? "/" : "-";
});

// Format input as user types (similar to DateInput)
const formatInputValue = (value) => {
    if (!value) return "";

    // Remove all non-digit characters
    const digits = value.replace(/\D/g, "");
    if (digits.length === 0) return "";

    const sep = separator.value;
    const maxDigits = props.format === "yyyy-mm-dd" ? 8 : 8; // ddmmyyyy or yyyymmdd

    // Limit to max digits
    const limitedDigits = digits.slice(0, maxDigits);

    // Format based on format type
    switch (props.format) {
        case "dd/mm/yyyy":
        case "dd-mm-yyyy":
            if (limitedDigits.length <= 2) {
                return limitedDigits;
            } else if (limitedDigits.length <= 4) {
                return `${limitedDigits.slice(0, 2)}${sep}${limitedDigits.slice(
                    2
                )}`;
            } else {
                return `${limitedDigits.slice(0, 2)}${sep}${limitedDigits.slice(
                    2,
                    4
                )}${sep}${limitedDigits.slice(4, 8)}`;
            }
        case "mm/dd/yyyy":
        case "mm-dd-yyyy":
            if (limitedDigits.length <= 2) {
                return limitedDigits;
            } else if (limitedDigits.length <= 4) {
                return `${limitedDigits.slice(0, 2)}${sep}${limitedDigits.slice(
                    2
                )}`;
            } else {
                return `${limitedDigits.slice(0, 2)}${sep}${limitedDigits.slice(
                    2,
                    4
                )}${sep}${limitedDigits.slice(4, 8)}`;
            }
        case "yyyy-mm-dd":
            if (limitedDigits.length <= 4) {
                return limitedDigits;
            } else if (limitedDigits.length <= 6) {
                return `${limitedDigits.slice(0, 4)}${sep}${limitedDigits.slice(
                    4
                )}`;
            } else {
                return `${limitedDigits.slice(0, 4)}${sep}${limitedDigits.slice(
                    4,
                    6
                )}${sep}${limitedDigits.slice(6, 8)}`;
            }
        default:
            if (limitedDigits.length <= 2) {
                return limitedDigits;
            } else if (limitedDigits.length <= 4) {
                return `${limitedDigits.slice(0, 2)}${sep}${limitedDigits.slice(
                    2
                )}`;
            } else {
                return `${limitedDigits.slice(0, 2)}${sep}${limitedDigits.slice(
                    2,
                    4
                )}${sep}${limitedDigits.slice(4, 8)}`;
            }
    }
};

// Convert custom format to yyyy-mm-dd (model format)
const formatToModel = (day, month, year) => {
    const d = String(day).padStart(2, "0");
    const m = String(month + 1).padStart(2, "0");
    return `${year}-${m}-${d}`;
};

// Convert yyyy-mm-dd to display format
const formatToDisplay = (value) => {
    if (!value || !/^\d{4}-\d{2}-\d{2}$/.test(value)) return "";
    const [year, month, day] = value.split("-");
    const sep = separator.value;

    switch (props.format) {
        case "dd/mm/yyyy":
        case "dd-mm-yyyy":
            return `${day}${sep}${month}${sep}${year}`;
        case "mm/dd/yyyy":
        case "mm-dd-yyyy":
            return `${month}${sep}${day}${sep}${year}`;
        case "yyyy-mm-dd":
            return `${year}${sep}${month}${sep}${day}`;
        default:
            return `${day}${sep}${month}${sep}${year}`;
    }
};

// Validate and emit date
const validateAndEmit = (day, month, year) => {
    // If all fields are empty, emit empty
    if (!day && !month && !year) {
        emit("update:modelValue", "");
        return false;
    }

    // If not all fields are filled, don't emit yet
    if (!day || !month || !year) {
        return false;
    }

    // Validate ranges
    if (
        day < 1 ||
        day > 31 ||
        month < 1 ||
        month > 12 ||
        year < 1900 ||
        year > 2100
    ) {
        return false;
    }

    // Validate actual date
    const date = new Date(year, month - 1, day);
    if (
        date.getDate() !== day ||
        date.getMonth() !== month - 1 ||
        date.getFullYear() !== year
    ) {
        return false;
    }

    // Check min/max constraints
    const dateStr = formatToModel(day, month - 1, year);
    const dateObj = new Date(dateStr + "T00:00:00");

    if (props.min) {
        const minDate = new Date(props.min + "T00:00:00");
        if (dateObj < minDate) return false;
    }

    if (props.max) {
        const maxDate = new Date(props.max + "T00:00:00");
        if (dateObj > maxDate) return false;
    }

    emit("update:modelValue", dateStr);
    return true;
};

// Handle input - simple like DateInput
const handleInput = (event) => {
    const cursorPos = event.target.selectionStart;
    const value = event.target.value;
    const formatted = formatInputValue(value);
    inputValue.value = formatted;

    // Restore cursor position after formatting
    nextTick(() => {
        if (inputRef.value) {
            // Calculate new cursor position
            // Count digits before cursor in original value
            const digitsBeforeCursor = value
                .slice(0, cursorPos)
                .replace(/\D/g, "").length;

            // Find position in formatted string that corresponds to same number of digits
            let newCursorPos = 0;
            let digitCount = 0;

            for (
                let i = 0;
                i < formatted.length && digitCount < digitsBeforeCursor;
                i++
            ) {
                if (/\d/.test(formatted[i])) {
                    digitCount++;
                }
                newCursorPos = i + 1;
            }

            // If we're at the end of digits, position after last digit
            if (digitCount < digitsBeforeCursor) {
                newCursorPos = formatted.length;
            }

            inputRef.value.setSelectionRange(newCursorPos, newCursorPos);
        }
    });

    // Try to parse and emit if complete
    const digits = formatted.replace(/\D/g, "");
    if (digits.length === 8) {
        // Complete date entered
        let day, month, year;
        switch (props.format) {
            case "dd/mm/yyyy":
            case "dd-mm-yyyy":
                day = parseInt(digits.slice(0, 2), 10);
                month = parseInt(digits.slice(2, 4), 10);
                year = parseInt(digits.slice(4, 8), 10);
                break;
            case "mm/dd/yyyy":
            case "mm-dd-yyyy":
                month = parseInt(digits.slice(0, 2), 10);
                day = parseInt(digits.slice(2, 4), 10);
                year = parseInt(digits.slice(4, 8), 10);
                break;
            case "yyyy-mm-dd":
                year = parseInt(digits.slice(0, 4), 10);
                month = parseInt(digits.slice(4, 6), 10);
                day = parseInt(digits.slice(6, 8), 10);
                break;
        }

        if (day && month && year) {
            validateAndEmit(day, month, year);
        }
    } else if (formatted.length === 0) {
        emit("update:modelValue", "");
    }
};

// Handle blur - validate and format
const handleBlur = () => {
    if (!inputValue.value) {
        emit("update:modelValue", "");
        return;
    }

    const digits = inputValue.value.replace(/\D/g, "");
    if (digits.length === 8) {
        // Complete date - validate
        let day, month, year;
        switch (props.format) {
            case "dd/mm/yyyy":
            case "dd-mm-yyyy":
                day = parseInt(digits.slice(0, 2), 10);
                month = parseInt(digits.slice(2, 4), 10);
                year = parseInt(digits.slice(4, 8), 10);
                break;
            case "mm/dd/yyyy":
            case "mm-dd-yyyy":
                month = parseInt(digits.slice(0, 2), 10);
                day = parseInt(digits.slice(2, 4), 10);
                year = parseInt(digits.slice(4, 8), 10);
                break;
            case "yyyy-mm-dd":
                year = parseInt(digits.slice(0, 4), 10);
                month = parseInt(digits.slice(4, 6), 10);
                day = parseInt(digits.slice(6, 8), 10);
                break;
        }

        if (day && month && year) {
            if (validateAndEmit(day, month, year)) {
                // Update display with properly formatted value
                inputValue.value = formatToDisplay(props.modelValue);
            } else {
                // Invalid date, revert to model value
                inputValue.value = formatToDisplay(props.modelValue);
            }
        } else {
            // Invalid, revert
            inputValue.value = formatToDisplay(props.modelValue);
        }
    } else {
        // Incomplete date, revert to model value
        inputValue.value = formatToDisplay(props.modelValue);
    }
};

// Handle keydown - simple like DateInput
const handleKeydown = (event) => {
    // Allow: backspace, delete, tab, escape, enter, and arrow keys
    if (
        [8, 9, 27, 13, 46, 37, 38, 39, 40].includes(event.keyCode) ||
        (event.keyCode === 65 && event.ctrlKey === true) ||
        (event.keyCode === 67 && event.ctrlKey === true) ||
        (event.keyCode === 86 && event.ctrlKey === true) ||
        (event.keyCode === 88 && event.ctrlKey === true) ||
        // Allow: home, end, left, right
        (event.keyCode >= 35 && event.keyCode <= 39)
    ) {
        return;
    }

    // Ensure that it is a number and stop the keypress
    if (
        (event.shiftKey || event.keyCode < 48 || event.keyCode > 57) &&
        (event.keyCode < 96 || event.keyCode > 105)
    ) {
        event.preventDefault();
    }
};

// Calendar days
const calendarDays = computed(() => {
    const days = [];
    const firstDay = new Date(currentYear.value, currentMonth.value, 1);
    const lastDay = new Date(currentYear.value, currentMonth.value + 1, 0);
    const startDate = new Date(firstDay);
    startDate.setDate(startDate.getDate() - firstDay.getDay());

    const selectedDate = props.modelValue
        ? new Date(props.modelValue + "T00:00:00")
        : null;
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    const minDate = props.min ? new Date(props.min + "T00:00:00") : null;
    const maxDate = props.max ? new Date(props.max + "T00:00:00") : null;

    for (let i = 0; i < 42; i++) {
        const date = new Date(startDate);
        date.setDate(startDate.getDate() + i);

        const isCurrentMonth = date.getMonth() === currentMonth.value;
        const isToday = date.getTime() === today.getTime();
        const isSelected =
            selectedDate && date.getTime() === selectedDate.getTime();

        let isDisabled = false;
        if (minDate && date < minDate) isDisabled = true;
        if (maxDate && date > maxDate) isDisabled = true;

        days.push({
            key: i,
            date: date.getDate(),
            fullDate: date,
            isCurrentMonth,
            isToday,
            isSelected,
            isDisabled,
        });
    }

    return days;
});

const toggleCalendar = async () => {
    if (!props.disabled) {
        isOpen.value = !isOpen.value;
        if (isOpen.value) {
            await nextTick();
            // Update calendar view to selected date or today
            if (props.modelValue) {
                const date = new Date(props.modelValue + "T00:00:00");
                currentMonth.value = date.getMonth();
                currentYear.value = date.getFullYear();
            }
        }
    }
};

const closeCalendar = () => {
    isOpen.value = false;
};

const selectDate = (day) => {
    if (day.isDisabled || !day.isCurrentMonth) {
        return;
    }

    const dateStr = formatToModel(
        day.date,
        day.fullDate.getMonth(),
        day.fullDate.getFullYear()
    );
    emit("update:modelValue", dateStr);
    closeCalendar();
};

const selectToday = () => {
    const today = new Date();
    const dateStr = formatToModel(
        today.getDate(),
        today.getMonth(),
        today.getFullYear()
    );
    emit("update:modelValue", dateStr);
    closeCalendar();
};

const previousMonth = () => {
    if (currentMonth.value === 0) {
        currentMonth.value = 11;
        currentYear.value--;
    } else {
        currentMonth.value--;
    }
};

const nextMonth = () => {
    if (currentMonth.value === 11) {
        currentMonth.value = 0;
        currentYear.value++;
    } else {
        currentMonth.value++;
    }
};

const updateCalendar = () => {
    // Calendar will update automatically via computed
};

// Watch modelValue to update input and calendar view
watch(
    () => props.modelValue,
    (newValue) => {
        if (newValue) {
            const date = new Date(newValue + "T00:00:00");
            currentMonth.value = date.getMonth();
            currentYear.value = date.getFullYear();
            // Update input value if it's different (to avoid conflicts with user typing)
            const formatted = formatToDisplay(newValue);
            if (inputValue.value !== formatted) {
                inputValue.value = formatted;
            }
        } else {
            inputValue.value = "";
        }
    },
    { immediate: true }
);

// Handle click outside
const handleClickOutside = (event) => {
    if (
        isOpen.value &&
        calendarRef.value &&
        !calendarRef.value.contains(event.target) &&
        !inputRef.value?.contains(event.target) &&
        !event.target.closest("button")
    ) {
        closeCalendar();
    }
};

onMounted(() => {
    document.addEventListener("click", handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener("click", handleClickOutside);
});
</script>
