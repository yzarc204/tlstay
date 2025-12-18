<template>
  <div class="relative">
    <Listbox v-model="selectedBank" @update:modelValue="handleChange">
      <div class="relative">
        <ListboxButton
          :class="[
            'relative w-full cursor-default rounded-lg border bg-white py-2 pl-3 pr-10 text-left focus:outline-none focus-visible:border-primary focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 sm:text-sm',
            error
              ? 'border-red-500 focus-visible:ring-red-500'
              : 'border-gray-300',
            loading ? 'opacity-50 cursor-not-allowed' : ''
          ]"
          :disabled="loading"
        >
          <span class="flex items-center">
            <img
              v-if="selectedBank && !loading"
              :src="getBankLogo(selectedBank)"
              :alt="getBankName(selectedBank)"
              class="h-6 w-6 object-contain mr-3 flex-shrink-0"
              @error="handleImageError"
            />
            <span class="block truncate text-gray-900" :class="{ 'text-gray-500': !selectedBank }">
              <span v-if="loading">Đang tải...</span>
              <span v-else>{{ selectedBank ? getBankName(selectedBank) : placeholder }}</span>
            </span>
          </span>
          <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
            <ChevronUpDownIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
          </span>
        </ListboxButton>

        <Transition
          leave-active-class="transition duration-100 ease-in"
          leave-from-class="opacity-100"
          leave-to-class="opacity-0"
        >
          <ListboxOptions
            v-if="!loading"
            class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black/5 focus:outline-none sm:text-sm"
          >
            <ListboxOption
              v-slot="{ active, selected }"
              :value="null"
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
                  {{ placeholder }}
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
            </ListboxOption>
            <ListboxOption
              v-for="bank in banks"
              :key="bank.code"
              v-slot="{ active, selected }"
              :value="bank.code"
              as="template"
            >
              <li
                :class="[
                  'relative cursor-default select-none py-2 pl-10 pr-4',
                  active ? 'bg-primary text-white' : 'text-gray-900'
                ]"
              >
                <div class="flex items-center">
                  <img
                    :src="bank.logo || getBankLogo(bank.code)"
                    :alt="bank.name"
                    class="h-6 w-6 object-contain mr-3 flex-shrink-0"
                    @error="handleImageError"
                  />
                  <span
                    :class="[
                      'block truncate',
                      selected ? 'font-medium' : 'font-normal'
                    ]"
                  >
                    {{ bank.name }}
                  </span>
                </div>
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
            </ListboxOption>
          </ListboxOptions>
          <ListboxOptions
            v-else
            class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black/5 focus:outline-none sm:text-sm"
          >
            <li class="relative cursor-default select-none py-2 px-4 text-gray-500 text-center">
              Đang tải danh sách ngân hàng...
            </li>
          </ListboxOptions>
        </Transition>
      </div>
    </Listbox>
    <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Listbox, ListboxButton, ListboxOptions, ListboxOption } from '@headlessui/vue'
import { CheckIcon, ChevronUpDownIcon } from '@heroicons/vue/20/solid'
import axios from 'axios'

const props = defineProps({
  modelValue: {
    type: String,
    default: null,
  },
  placeholder: {
    type: String,
    default: 'Chọn ngân hàng',
  },
  error: {
    type: String,
    default: null,
  },
})

const emit = defineEmits(['update:modelValue'])

const banks = ref([])
const loading = ref(true)

// Fetch banks from API
onMounted(async () => {
  try {
    const response = await axios.get('/api/banks')
    if (response.data && response.data.banks) {
      // Filter only banks that support transfer
      banks.value = response.data.banks
        .filter(bank => bank.transferSupported === 1)
        .map(bank => ({
          code: bank.code,
          name: bank.name,
          shortName: bank.shortName,
          logo: bank.logo,
        }))
        .sort((a, b) => a.name.localeCompare(b.name, 'vi'))
    }
  } catch (error) {
    console.error('Failed to fetch banks:', error)
    // Fallback to empty array
    banks.value = []
  } finally {
    loading.value = false
  }
})

const selectedBank = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value),
})

const getBankLogo = (bankCode) => {
  if (!bankCode) return null
  const bank = banks.value.find(b => b.code === bankCode)
  if (bank && bank.logo) {
    return bank.logo
  }
  // Fallback to API logo URL format
  return `https://api.vietqr.io/img/${bankCode}.png`
}

const getBankName = (bankCode) => {
  if (!bankCode) return ''
  const bank = banks.value.find(b => b.code === bankCode)
  return bank ? bank.name : bankCode
}

const handleImageError = (event) => {
  // Hide broken image
  event.target.style.display = 'none'
}

const handleChange = (value) => {
  selectedBank.value = value
}
</script>
