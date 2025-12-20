<template>
  <AdminLayout title="Thông tin doanh nghiệp">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Thông tin doanh nghiệp</h1>
          <p class="text-gray-600 mt-1">Quản lý thông tin doanh nghiệp hiển thị trên hợp đồng thuê trọ</p>
        </div>
      </div>

      <!-- Flash Messages -->
      <div v-if="$page.props.flash?.success" class="p-4 bg-green-50 border border-green-200 rounded-lg">
        <p class="text-green-800">{{ $page.props.flash.success }}</p>
      </div>
      <div v-if="$page.props.flash?.error" class="p-4 bg-red-50 border border-red-200 rounded-lg">
        <p class="text-red-800">{{ $page.props.flash.error }}</p>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleSubmit" class="bg-white rounded-lg shadow-sm p-6 space-y-8">
        <!-- Company Basic Information -->
        <div class="space-y-6">
          <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
              <BuildingOfficeIcon class="w-5 h-5 mr-2 text-primary" />
              Thông tin cơ bản
            </h2>
            <div class="grid md:grid-cols-2 gap-6">
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Tên doanh nghiệp <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.company_name"
                  type="text"
                  :class="[
                    'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:outline-none transition-colors',
                    form.errors.company_name
                      ? 'border-red-500 focus:ring-red-500 focus:border-red-500'
                      : 'border-gray-300 focus:ring-primary focus:border-transparent'
                  ]"
                  placeholder="Nhập tên doanh nghiệp"
                  required
                />
                <p v-if="form.errors.company_name" class="mt-1 text-sm text-red-600">
                  {{ form.errors.company_name }}
                </p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Mã số thuế
                </label>
                <input
                  v-model="form.company_tax_code"
                  type="text"
                  :class="[
                    'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:outline-none transition-colors',
                    form.errors.company_tax_code
                      ? 'border-red-500 focus:ring-red-500 focus:border-red-500'
                      : 'border-gray-300 focus:ring-primary focus:border-transparent'
                  ]"
                  placeholder="Nhập mã số thuế"
                />
                <p v-if="form.errors.company_tax_code" class="mt-1 text-sm text-red-600">
                  {{ form.errors.company_tax_code }}
                </p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Ngày cấp mã số thuế
                </label>
                <input
                  v-model="form.company_tax_code_issue_date"
                  type="date"
                  :class="[
                    'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:outline-none transition-colors',
                    form.errors.company_tax_code_issue_date
                      ? 'border-red-500 focus:ring-red-500 focus:border-red-500'
                      : 'border-gray-300 focus:ring-primary focus:border-transparent'
                  ]"
                />
                <p v-if="form.errors.company_tax_code_issue_date" class="mt-1 text-sm text-red-600">
                  {{ form.errors.company_tax_code_issue_date }}
                </p>
              </div>

              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Nơi cấp mã số thuế
                </label>
                <input
                  v-model="form.company_tax_code_issue_place"
                  type="text"
                  :class="[
                    'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:outline-none transition-colors',
                    form.errors.company_tax_code_issue_place
                      ? 'border-red-500 focus:ring-red-500 focus:border-red-500'
                      : 'border-gray-300 focus:ring-primary focus:border-transparent'
                  ]"
                  placeholder="Nhập nơi cấp mã số thuế"
                />
                <p v-if="form.errors.company_tax_code_issue_place" class="mt-1 text-sm text-red-600">
                  {{ form.errors.company_tax_code_issue_place }}
                </p>
              </div>

              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Địa chỉ doanh nghiệp
                </label>
                <textarea
                  v-model="form.company_address"
                  rows="3"
                  :class="[
                    'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:outline-none transition-colors',
                    form.errors.company_address
                      ? 'border-red-500 focus:ring-red-500 focus:border-red-500'
                      : 'border-gray-300 focus:ring-primary focus:border-transparent'
                  ]"
                  placeholder="Nhập địa chỉ trụ sở chính của doanh nghiệp"
                ></textarea>
                <p v-if="form.errors.company_address" class="mt-1 text-sm text-red-600">
                  {{ form.errors.company_address }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Contact Information -->
        <div class="space-y-6 border-t pt-6">
          <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
              <PhoneIcon class="w-5 h-5 mr-2 text-primary" />
              Thông tin liên hệ
            </h2>
            <div class="grid md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Số điện thoại
                </label>
                <input
                  v-model="form.company_phone"
                  type="text"
                  :class="[
                    'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:outline-none transition-colors',
                    form.errors.company_phone
                      ? 'border-red-500 focus:ring-red-500 focus:border-red-500'
                      : 'border-gray-300 focus:ring-primary focus:border-transparent'
                  ]"
                  placeholder="Nhập số điện thoại"
                />
                <p v-if="form.errors.company_phone" class="mt-1 text-sm text-red-600">
                  {{ form.errors.company_phone }}
                </p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Email
                </label>
                <input
                  v-model="form.company_email"
                  type="email"
                  :class="[
                    'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:outline-none transition-colors',
                    form.errors.company_email
                      ? 'border-red-500 focus:ring-red-500 focus:border-red-500'
                      : 'border-gray-300 focus:ring-primary focus:border-transparent'
                  ]"
                  placeholder="Nhập email"
                />
                <p v-if="form.errors.company_email" class="mt-1 text-sm text-red-600">
                  {{ form.errors.company_email }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Payment Information -->
        <div class="space-y-6 border-t pt-6">
          <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
              <CreditCardIcon class="w-5 h-5 mr-2 text-primary" />
              Thông tin thanh toán
            </h2>
            <div class="grid md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Chọn ngân hàng
                </label>
                <BankSelect
                  v-model="form.bank_code"
                  placeholder="Chọn ngân hàng"
                  :error="form.errors.bank_code"
                  @update:modelValue="handleBankCodeChange"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Số tài khoản
                </label>
                <input
                  v-model="form.bank_account_number"
                  type="text"
                  :class="[
                    'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:outline-none transition-colors',
                    form.errors.bank_account_number
                      ? 'border-red-500 focus:ring-red-500 focus:border-red-500'
                      : 'border-gray-300 focus:ring-primary focus:border-transparent'
                  ]"
                  placeholder="Nhập số tài khoản"
                />
                <p v-if="form.errors.bank_account_number" class="mt-1 text-sm text-red-600">
                  {{ form.errors.bank_account_number }}
                </p>
              </div>

              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Tên chủ tài khoản
                </label>
                <input
                  v-model="form.bank_account_holder"
                  type="text"
                  :class="[
                    'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:outline-none transition-colors',
                    form.errors.bank_account_holder
                      ? 'border-red-500 focus:ring-red-500 focus:border-red-500'
                      : 'border-gray-300 focus:ring-primary focus:border-transparent'
                  ]"
                  placeholder="Nhập tên chủ tài khoản"
                />
                <p v-if="form.errors.bank_account_holder" class="mt-1 text-sm text-red-600">
                  {{ form.errors.bank_account_holder }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end space-x-4 pt-6 border-t">
          <Link
            href="/admin"
            class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
          >
            Hủy
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="!form.processing">Lưu thông tin</span>
            <span v-else>Đang lưu...</span>
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'
import axios from 'axios'
import AdminLayout from '@/layouts/AdminLayout.vue'
import BankSelect from '@/components/ui/BankSelect.vue'
import {
  BuildingOfficeIcon,
  PhoneIcon,
  CreditCardIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  company: {
    type: Object,
    required: true,
  },
})

const form = useForm({
  company_name: props.company.company_name || '',
  company_tax_code: props.company.company_tax_code || '',
  company_tax_code_issue_date: props.company.company_tax_code_issue_date || '',
  company_tax_code_issue_place: props.company.company_tax_code_issue_place || '',
  company_address: props.company.company_address || '',
  company_phone: props.company.company_phone || '',
  company_email: props.company.company_email || '',
  bank_name: props.company.bank_name || '',
  bank_code: props.company.bank_code || '',
  bank_account_number: props.company.bank_account_number || '',
  bank_account_holder: props.company.bank_account_holder || '',
})

const banks = ref([])

// Fetch banks list
onMounted(async () => {
  try {
    const response = await axios.get('/api/banks')
    if (response.data && response.data.banks) {
      banks.value = response.data.banks
        .filter(bank => bank.transferSupported === 1)
        .map(bank => ({
          code: bank.code,
          name: bank.name,
        }))
    }
  } catch (error) {
    console.error('Failed to fetch banks:', error)
  }
})

const handleBankCodeChange = (bankCode) => {
  if (bankCode && banks.value.length > 0) {
    const bank = banks.value.find(b => b.code === bankCode)
    if (bank) {
      form.bank_name = bank.name
    }
  }
}

const handleSubmit = () => {
  form.put('/admin/company-information', {
    preserveScroll: true,
  })
}
</script>
