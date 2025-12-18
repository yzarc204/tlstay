<template>
  <div class="relative">
    <div class="relative">
      <input
        ref="inputRef"
        :value="displayValue"
        type="text"
        :placeholder="placeholder || 'dd/mm/yyyy'"
        :class="[
          'w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:outline-none transition-colors',
          error
            ? 'border-red-500 focus:ring-red-500'
            : 'border-gray-300 focus:ring-primary'
        ]"
        :disabled="disabled"
        readonly
        @click="toggleCalendar"
        @focus="toggleCalendar"
      />
      <CalendarIcon
        class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400 pointer-events-none"
      />
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
              <option v-for="(month, index) in months" :key="index" :value="index">
                {{ month }}
              </option>
            </select>
            <select
              v-model.number="currentYear"
              @change="updateCalendar"
              class="text-sm font-semibold text-gray-900 border-none focus:ring-0 cursor-pointer"
            >
              <option v-for="year in years" :key="year" :value="year">
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
    <p v-if="hint && !error" class="mt-1 text-xs text-gray-500">{{ hint }}</p>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue'
import { CalendarIcon, ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: 'dd/mm/yyyy'
  },
  error: {
    type: String,
    default: ''
  },
  hint: {
    type: String,
    default: ''
  },
  disabled: {
    type: Boolean,
    default: false
  },
  min: {
    type: String,
    default: ''
  },
  max: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['update:modelValue'])

const inputRef = ref(null)
const calendarRef = ref(null)
const isOpen = ref(false)
const currentMonth = ref(new Date().getMonth())
const currentYear = ref(new Date().getFullYear())

const weekDays = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7']
const months = [
  'Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6',
  'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'
]

// Generate years (current year ± 10)
const years = computed(() => {
  const current = new Date().getFullYear()
  const yearList = []
  for (let i = current - 10; i <= current + 10; i++) {
    yearList.push(i)
  }
  return yearList
})

// Convert yyyy-mm-dd to dd/mm/yyyy
const formatToDisplay = (value) => {
  if (!value) return ''
  if (/^\d{4}-\d{2}-\d{2}$/.test(value)) {
    const [year, month, day] = value.split('-')
    return `${day}/${month}/${year}`
  }
  return value
}

// Convert dd/mm/yyyy to yyyy-mm-dd
const formatToModel = (day, month, year) => {
  const d = String(day).padStart(2, '0')
  const m = String(month + 1).padStart(2, '0')
  return `${year}-${m}-${d}`
}

// Get display value
const displayValue = computed(() => {
  return formatToDisplay(props.modelValue)
})

// Calendar days
const calendarDays = computed(() => {
  const days = []
  const firstDay = new Date(currentYear.value, currentMonth.value, 1)
  const lastDay = new Date(currentYear.value, currentMonth.value + 1, 0)
  const startDate = new Date(firstDay)
  startDate.setDate(startDate.getDate() - firstDay.getDay())

  const selectedDate = props.modelValue ? new Date(props.modelValue + 'T00:00:00') : null
  const today = new Date()
  today.setHours(0, 0, 0, 0)

  const minDate = props.min ? new Date(props.min + 'T00:00:00') : null
  const maxDate = props.max ? new Date(props.max + 'T00:00:00') : null

  for (let i = 0; i < 42; i++) {
    const date = new Date(startDate)
    date.setDate(startDate.getDate() + i)

    const isCurrentMonth = date.getMonth() === currentMonth.value
    const isToday = date.getTime() === today.getTime()
    const isSelected = selectedDate && date.getTime() === selectedDate.getTime()
    
    let isDisabled = false
    if (minDate && date < minDate) isDisabled = true
    if (maxDate && date > maxDate) isDisabled = true

    days.push({
      key: i,
      date: date.getDate(),
      fullDate: date,
      isCurrentMonth,
      isToday,
      isSelected,
      isDisabled
    })
  }

  return days
})

const toggleCalendar = async () => {
  if (!props.disabled) {
    isOpen.value = !isOpen.value
    if (isOpen.value) {
      await nextTick()
      // Update calendar view to selected date or today
      if (props.modelValue) {
        const date = new Date(props.modelValue + 'T00:00:00')
        currentMonth.value = date.getMonth()
        currentYear.value = date.getFullYear()
      }
    }
  }
}

const closeCalendar = () => {
  isOpen.value = false
}

const selectDate = (day) => {
  if (day.isDisabled || !day.isCurrentMonth) {
    return
  }

  const dateStr = formatToModel(day.date, day.fullDate.getMonth(), day.fullDate.getFullYear())
  emit('update:modelValue', dateStr)
  closeCalendar()
}

const selectToday = () => {
  const today = new Date()
  const dateStr = formatToModel(today.getDate(), today.getMonth(), today.getFullYear())
  emit('update:modelValue', dateStr)
  closeCalendar()
}

const previousMonth = () => {
  if (currentMonth.value === 0) {
    currentMonth.value = 11
    currentYear.value--
  } else {
    currentMonth.value--
  }
}

const nextMonth = () => {
  if (currentMonth.value === 11) {
    currentMonth.value = 0
    currentYear.value++
  } else {
    currentMonth.value++
  }
}

const updateCalendar = () => {
  // Calendar will update automatically via computed
}

// Watch modelValue to update calendar view
watch(() => props.modelValue, (newValue) => {
  if (newValue) {
    const date = new Date(newValue + 'T00:00:00')
    currentMonth.value = date.getMonth()
    currentYear.value = date.getFullYear()
  }
}, { immediate: true })

// Handle click outside
const handleClickOutside = (event) => {
  if (
    isOpen.value &&
    calendarRef.value &&
    !calendarRef.value.contains(event.target) &&
    !inputRef.value?.contains(event.target)
  ) {
    closeCalendar()
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>
