<template>
  <div class="relative">
    <Combobox v-model="selectedValue" @update:modelValue="handleChange">
      <div class="relative">
        <div
          class="relative w-full cursor-default overflow-hidden rounded-lg border bg-white text-left focus:outline-none focus-visible:ring-2 focus-visible:ring-white/75 focus-visible:ring-offset-2 focus-visible:ring-offset-primary sm:text-sm"
          :class="[
            error
              ? 'border-red-500 focus-visible:ring-red-500'
              : 'border-gray-300 focus-visible:ring-primary'
          ]"
        >
          <ComboboxInput
            class="w-full border-none py-2 pl-3 pr-10 text-sm leading-5 text-gray-900 focus:ring-0"
            :display-value="() => {
              if (query) return query
              return selectedValue ? getDisplayValue(selectedValue) : ''
            }"
            @change="query = $event.target.value"
            :placeholder="placeholder"
          />
          <ComboboxButton class="absolute inset-y-0 right-0 flex items-center pr-2">
            <ChevronUpDownIcon
              class="h-5 w-5 text-gray-400"
              aria-hidden="true"
            />
          </ComboboxButton>
        </div>
        <Transition
          leave-active-class="transition duration-100 ease-in"
          leave-from-class="opacity-100"
          leave-to-class="opacity-0"
          @after-leave="query = ''"
        >
          <ComboboxOptions
            class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black/5 focus:outline-none sm:text-sm"
          >
            <div
              v-if="filteredOptions.length === 0 && query !== ''"
              class="relative cursor-default select-none px-4 py-2 text-gray-700"
            >
              Không tìm thấy kết quả.
            </div>

            <ComboboxOption
              v-for="option in filteredOptions"
              :key="getOptionValue(option)"
              v-slot="{ selected, active }"
              :value="option"
              as="template"
            >
              <li
                :class="[
                  'relative cursor-default select-none py-2 pl-10 pr-4',
                  active ? 'bg-primary text-white' : 'text-gray-900'
                ]"
              >
                <span
                  :class="[
                    'block truncate',
                    selected ? 'font-medium' : 'font-normal'
                  ]"
                >
                  {{ getDisplayValue(option) }}
                </span>
                <span
                  v-if="selected"
                  :class="[
                    'absolute inset-y-0 left-0 flex items-center pl-3',
                    active ? 'text-white' : 'text-primary'
                  ]"
                >
                  <CheckIcon class="h-5 w-5" aria-hidden="true" />
                </span>
              </li>
            </ComboboxOption>
          </ComboboxOptions>
        </Transition>
      </div>
    </Combobox>
    <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
    <p v-if="hint" class="mt-1 text-xs text-gray-500">{{ hint }}</p>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import {
  Combobox,
  ComboboxInput,
  ComboboxButton,
  ComboboxOptions,
  ComboboxOption,
} from '@headlessui/vue'
import { CheckIcon, ChevronUpDownIcon } from '@heroicons/vue/20/solid'

const props = defineProps({
  modelValue: {
    type: [String, Number, Object, null],
    default: null,
  },
  options: {
    type: Array,
    required: true,
  },
  optionValue: {
    type: [String, Function],
    default: 'id',
  },
  optionLabel: {
    type: [String, Function],
    default: 'name',
  },
  placeholder: {
    type: String,
    default: 'Chọn...',
  },
  error: {
    type: String,
    default: null,
  },
  hint: {
    type: String,
    default: null,
  },
  filterBy: {
    type: [String, Function],
    default: null, // If null, will use optionLabel
  },
})

const emit = defineEmits(['update:modelValue'])

const query = ref('')

const selectedValue = computed({
  get: () => {
    if (!props.modelValue) return null
    return props.options.find(
      (opt) => getOptionValue(opt) === props.modelValue
    ) || null
  },
  set: (value) => {
    emit('update:modelValue', value ? getOptionValue(value) : null)
  },
})

const getOptionValue = (option) => {
  if (typeof props.optionValue === 'function') {
    return props.optionValue(option)
  }
  return option[props.optionValue]
}

const getDisplayValue = (option) => {
  if (!option) return ''
  if (typeof props.optionLabel === 'function') {
    return props.optionLabel(option)
  }
  return option[props.optionLabel] || ''
}

const getFilterValue = (option) => {
  if (props.filterBy) {
    if (typeof props.filterBy === 'function') {
      return props.filterBy(option)
    }
    return option[props.filterBy] || ''
  }
  return getDisplayValue(option)
}

const filteredOptions = computed(() => {
  if (query.value === '') {
    return props.options
  }

  const searchTerm = query.value.toLowerCase()
  return props.options.filter((option) => {
    const filterValue = getFilterValue(option)
    return String(filterValue).toLowerCase().includes(searchTerm)
  })
})

const handleChange = (value) => {
  selectedValue.value = value
}
</script>
