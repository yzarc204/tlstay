<template>
  <AppLayout>
    <div class="wallet bg-light min-h-screen py-12">
      <div class="container mx-auto px-4">
        <!-- Flash Messages -->
        <div v-if="$page.props.flash?.success" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
          <p class="text-green-800">{{ $page.props.flash.success }}</p>
        </div>
        <div v-if="$page.props.flash?.error" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
          <p class="text-red-800">{{ $page.props.flash.error }}</p>
        </div>

        <!-- Header -->
        <div class="mb-8">
          <h1 class="text-4xl font-bold text-secondary mb-2">Ví của tôi</h1>
          <p class="text-gray-600">Quản lý số dư và lịch sử giao dịch</p>
        </div>

        <div class="grid lg:grid-cols-3 gap-8">
          <!-- Wallet Balance Card -->
          <div class="lg:col-span-1">
            <div class="card p-6 bg-gradient-to-br from-primary to-secondary text-white">
              <h2 class="text-lg font-semibold mb-4 opacity-90">Số dư ví</h2>
              <p class="text-4xl font-bold mb-2">{{ formatWallet(wallet.balance) }}</p>
              <p class="text-sm opacity-75">Tiền trong ví có thể dùng để thanh toán hóa đơn điện nước</p>
            </div>
          </div>

          <!-- Transactions List -->
          <div class="lg:col-span-2">
            <div class="card p-6">
              <h2 class="text-xl font-bold text-secondary mb-6">Lịch sử giao dịch</h2>

              <div v-if="transactions.length === 0" class="text-center py-12">
                <DocumentTextIcon class="w-16 h-16 mx-auto text-gray-300 mb-4" />
                <p class="text-gray-500">Chưa có giao dịch nào</p>
              </div>

              <div v-else class="space-y-4">
                <div
                  v-for="transaction in transactions"
                  :key="transaction.id"
                  class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
                >
                  <div class="flex items-center justify-between">
                    <div class="flex-1">
                      <div class="flex items-center space-x-3 mb-2">
                        <span
                          class="px-3 py-1 rounded-full text-xs font-semibold"
                          :class="
                            transaction.type === 'credit'
                              ? 'bg-green-100 text-green-700'
                              : 'bg-red-100 text-red-700'
                          "
                        >
                          {{ transaction.type === 'credit' ? 'Nạp tiền' : 'Rút tiền' }}
                        </span>
                        <span class="text-sm text-gray-500">
                          {{ formatDateTime(transaction.created_at) }}
                        </span>
                      </div>
                      <p class="text-gray-800 font-medium mb-1">
                        {{ transaction.description || 'Giao dịch ví' }}
                      </p>
                      <p class="text-xs text-gray-500">
                        Số dư sau: {{ formatWallet(transaction.balance_after) }}
                      </p>
                    </div>
                    <div class="text-right">
                      <p
                        class="text-lg font-bold"
                        :class="
                          transaction.type === 'credit' ? 'text-green-600' : 'text-red-600'
                        "
                      >
                        {{ transaction.type === 'credit' ? '+' : '-' }}{{ formatWallet(transaction.amount) }}
                      </p>
                    </div>
                  </div>
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
import AppLayout from '@/layouts/AppLayout.vue'
import { DocumentTextIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  wallet: {
    type: Object,
    required: true,
  },
  transactions: {
    type: Array,
    default: () => [],
  },
})

// Format wallet balance in xu (1 xu = 1 đồng)
const formatWallet = (amount) => {
  if (!amount && amount !== 0) return '0 xu'
  // 1 xu = 1 đồng, so no conversion needed
  const xu = Math.round(amount)
  return new Intl.NumberFormat('vi-VN').format(xu) + ' xu'
}

const formatPrice = (price) => {
  if (!price && price !== 0) return '0 ₫'
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND',
  }).format(price)
}

const formatDateTime = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleString('vi-VN', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  })
}
</script>
