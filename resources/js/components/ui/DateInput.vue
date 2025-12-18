<template>
  <div>
    <input
      :id="inputId"
      ref="inputRef"
      v-model="displayValue"
      type="text"
      :placeholder="placeholder || 'dd/mm/yyyy'"
      :class="[
        'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:outline-none transition-colors',
        error
          ? 'border-red-500 focus:ring-red-500'
          : 'border-gray-300 focus:ring-primary'
      ]"
      :disabled="disabled"
      :readonly="readonly"
      @input="handleInput"
      @blur="handleBlur"
      @keydown="handleKeydown"
    />
    <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
    <p v-if="hint && !error" class="mt-1 text-xs text-gray-500">{{ hint }}</p>
  </div>
</template>

<script setup>
import { ref, watch, computed, onMounted } from 'vue'

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
  readonly: {
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
const inputId = computed(() => `date-input-${Math.random().toString(36).substr(2, 9)}`)
const displayValue = ref('')

// Convert yyyy-mm-dd to dd/mm/yyyy
const formatToDisplay = (value) => {
  if (!value) return ''
  // If already in dd/mm/yyyy format, return as is
  if (/^\d{2}\/\d{2}\/\d{4}$/.test(value)) {
    return value
  }
  // If in yyyy-mm-dd format, convert to dd/mm/yyyy
  if (/^\d{4}-\d{2}-\d{2}$/.test(value)) {
    const [year, month, day] = value.split('-')
    return `${day}/${month}/${year}`
  }
  return value
}

// Convert dd/mm/yyyy to yyyy-mm-dd
const formatToModel = (value) => {
  if (!value) return ''
  // If in dd/mm/yyyy format, convert to yyyy-mm-dd
  if (/^\d{2}\/\d{2}\/\d{4}$/.test(value)) {
    const [day, month, year] = value.split('/')
    return `${year}-${month}-${day}`
  }
  // If already in yyyy-mm-dd format, return as is
  if (/^\d{4}-\d{2}-\d{2}$/.test(value)) {
    return value
  }
  return ''
}

// Validation is now handled by parent component using VeeValidate

// Format input as user types (dd/mm/yyyy)
const handleInput = (event) => {
  let value = event.target.value.replace(/\D/g, '') // Remove non-digits
  
  // Limit to 8 digits (ddmmyyyy)
  if (value.length > 8) {
    value = value.slice(0, 8)
  }
  
  // Format with slashes
  let formatted = value
  if (value.length > 2) {
    formatted = value.slice(0, 2) + '/' + value.slice(2)
  }
  if (value.length > 4) {
    formatted = value.slice(0, 2) + '/' + value.slice(2, 4) + '/' + value.slice(4)
  }
  
  displayValue.value = formatted
  
  // Emit yyyy-mm-dd format (always emit to keep v-model in sync)
  if (formatted.length === 10) {
    emit('update:modelValue', formatToModel(formatted))
  } else if (formatted.length === 0) {
    emit('update:modelValue', '')
  }
}

// Validate on blur - emit event for parent to handle validation
const handleBlur = () => {
  // Parent component will handle validation via VeeValidate
  // We just ensure the value is emitted
  if (displayValue.value && displayValue.value.length === 10) {
    emit('update:modelValue', formatToModel(displayValue.value))
  }
}

// Handle keyboard navigation
const handleKeydown = (event) => {
  // Allow: backspace, delete, tab, escape, enter, and arrow keys
  if ([8, 9, 27, 13, 46, 37, 38, 39, 40].indexOf(event.keyCode) !== -1 ||
    // Allow: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
    (event.keyCode === 65 && event.ctrlKey === true) ||
    (event.keyCode === 67 && event.ctrlKey === true) ||
    (event.keyCode === 86 && event.ctrlKey === true) ||
    (event.keyCode === 88 && event.ctrlKey === true) ||
    // Allow: home, end, left, right
    (event.keyCode >= 35 && event.keyCode <= 39)) {
    return
  }
  // Ensure that it is a number and stop the keypress
  if ((event.shiftKey || (event.keyCode < 48 || event.keyCode > 57)) && (event.keyCode < 96 || event.keyCode > 105)) {
    event.preventDefault()
  }
}

// Watch for external changes to modelValue
watch(() => props.modelValue, (newValue) => {
  if (newValue) {
    const formatted = formatToDisplay(newValue)
    if (formatted !== displayValue.value) {
      displayValue.value = formatted
    }
  } else {
    displayValue.value = ''
  }
}, { immediate: true })

onMounted(() => {
  if (props.modelValue) {
    displayValue.value = formatToDisplay(props.modelValue)
  }
})
</script>
