<template>
  <AppLayout title="Ödeme Takibi" :full-width="true">
    <!-- Full-width header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-amber-600 via-amber-700 to-orange-800 border-b border-amber-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <!-- Header Info -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center space-x-4 mb-3">
                <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                  <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                  </svg>
                </div>
                <div>
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">Ödeme Takibi</h1>
                  <p class="text-amber-100 text-sm mt-1">Satış taksitlerini ve ödemeleri takip edin</p>
                </div>
              </div>

              <!-- Stats Row -->
              <div class="flex flex-wrap items-center gap-4 lg:gap-6">
                <div class="bg-white/10 backdrop-blur-sm rounded-lg px-3 py-2">
                  <span class="text-amber-100 text-sm">Toplam Ödeme:</span>
                  <span class="font-semibold text-white ml-1">{{ salePayments?.total || 0 }}</span>
                </div>
                <div class="flex items-center space-x-4 text-sm">
                  <span class="flex items-center text-amber-100">
                    <div class="w-2 h-2 bg-yellow-400 rounded-full mr-2"></div>
                    Bekleyen: <span class="text-white font-medium ml-1">{{ countByStatus('pending') }}</span>
                  </span>
                  <span class="flex items-center text-amber-100">
                    <div class="w-2 h-2 bg-red-400 rounded-full mr-2"></div>
                    Vadesi Geçmiş: <span class="text-white font-medium ml-1">{{ countByStatus('overdue') }}</span>
                  </span>
                  <span class="flex items-center text-amber-100">
                    <div class="w-2 h-2 bg-green-400 rounded-full mr-2"></div>
                    Ödendi: <span class="text-white font-medium ml-1">{{ countByStatus('paid') }}</span>
                  </span>
                </div>
              </div>
            </div>

            <!-- Action Button -->
            <div class="flex-shrink-0">
              <Link
                :href="route('sales.payments.create')"
                class="inline-flex items-center px-4 py-2 bg-white text-amber-600 text-sm font-medium rounded-lg hover:bg-amber-50 shadow-lg hover:shadow-xl transition-all duration-200"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Yeni Ödeme
              </Link>
            </div>
          </div>
        </div>

        <!-- Breadcrumb -->
        <div class="bg-black/10 border-t border-white/10">
          <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="flex items-center space-x-2 py-2">
              <ol class="flex items-center space-x-2">
                <li>
                  <Link :href="route('dashboard')" class="text-amber-100 hover:text-white transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                  </Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-amber-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <span class="text-white font-medium text-sm">Ödeme Takibi</span>
                </li>
              </ol>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Main Content -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
      <!-- Filters -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900">Filtreler</h3>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Durum</label>
              <select
                v-model="filters.status"
                @change="applyFilters"
                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
              >
                <option :value="null">Tüm Durumlar</option>
                <option value="pending">Bekleyen</option>
                <option value="paid">Ödendi</option>
                <option value="partial">Kısmi Ödeme</option>
                <option value="overdue">Vadesi Geçmiş</option>
                <option value="cancelled">İptal</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Ödeme Tipi</label>
              <select
                v-model="filters.payment_type"
                @change="applyFilters"
                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
              >
                <option :value="null">Tümü</option>
                <option value="down_payment">Peşinat</option>
                <option value="installment">Taksit</option>
                <option value="additional">Ek Ödeme</option>
                <option value="penalty">Gecikme Faizi</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Vade Ayı</label>
              <input
                v-model="filters.due_month"
                @change="applyFilters"
                type="month"
                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
              />
            </div>

            <div class="flex items-end">
              <label class="flex items-center space-x-2 cursor-pointer">
                <input
                  v-model="filters.overdue_only"
                  @change="applyFilters"
                  type="checkbox"
                  class="w-4 h-4 text-amber-600 border-gray-300 rounded focus:ring-amber-500"
                />
                <span class="text-sm font-medium text-gray-700">Sadece Vadesi Geçmiş</span>
              </label>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Arama</label>
            <input
              v-model="filters.search"
              @input="debounceSearch"
              type="text"
              placeholder="Ödeme no, müşteri veya satış ara..."
              class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
            />
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Ödeme No</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Müşteri</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Satış No</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Taksit</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Tutar</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Vade</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Durum</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">İşlemler</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="salePayments.data.length === 0">
                <td colspan="8" class="px-6 py-12 text-center">
                  <div class="flex flex-col items-center justify-center">
                    <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                    </svg>
                    <p class="text-gray-500 text-sm">Ödeme kaydı bulunamadı.</p>
                  </div>
                </td>
              </tr>
              <tr v-for="payment in salePayments.data" :key="payment.id"
                  class="hover:bg-amber-50/50 transition-colors duration-150"
                  :class="{ 'bg-red-50/30': payment.is_overdue }">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-semibold text-gray-900">{{ payment.payment_number }}</div>
                  <div class="text-xs text-gray-500">{{ payment.payment_type_label }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ payment.customer?.full_name }}</div>
                  <div class="text-xs text-gray-500">{{ payment.customer?.phone }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ payment.unit_sale?.sale_number }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span v-if="payment.installment_number === 0" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-700">
                    Peşinat
                  </span>
                  <span v-else class="text-sm text-gray-900">
                    {{ payment.installment_number }}. Taksit
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-bold text-gray-900">{{ formatCurrency(payment.amount) }}</div>
                  <div v-if="payment.paid_amount > 0" class="text-xs text-green-600">
                    {{ formatCurrency(payment.paid_amount) }} ödendi
                  </div>
                  <div v-if="payment.late_fee > 0" class="text-xs text-red-600">
                    +{{ formatCurrency(payment.late_fee) }} gecikme
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ formatDate(payment.due_date) }}</div>
                  <div v-if="payment.payment_date" class="text-xs text-green-600">
                    Ödendi: {{ formatDate(payment.payment_date) }}
                  </div>
                  <div v-else-if="payment.is_overdue" class="text-xs text-red-600">
                    {{ getDaysOverdue(payment.due_date) }} gün gecikmiş
                  </div>
                  <div v-else class="text-xs text-gray-500">
                    {{ getDaysUntilDue(payment.due_date) }} gün kaldı
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold"
                    :class="getStatusClass(payment.status)"
                  >
                    {{ payment.status_badge.text }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex items-center space-x-3">
                    <Link
                      :href="route('sales.payments.show', payment.id)"
                      class="inline-flex items-center text-amber-600 hover:text-amber-800 font-medium transition-colors"
                    >
                      <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                      Detay
                    </Link>
                    <button
                      v-if="payment.status === 'pending' || payment.status === 'overdue'"
                      @click="markAsPaid(payment.id)"
                      class="inline-flex items-center text-green-600 hover:text-green-800 font-medium transition-colors"
                    >
                      <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                      </svg>
                      Ödendi
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="salePayments.meta && salePayments.meta.last_page > 1" class="px-6 py-4 bg-gray-50 border-t border-gray-200">
          <Pagination
            :meta="salePayments.meta"
            :links="salePayments.links"
          />
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Pagination from '@/Components/UI/Pagination.vue'

const props = defineProps({
  salePayments: {
    type: Object,
    required: true
  },
  filters: {
    type: Object,
    default: () => ({})
  }
})

const filters = ref({
  status: props.filters.status || null,
  payment_type: props.filters.payment_type || null,
  due_month: props.filters.due_month || null,
  overdue_only: props.filters.overdue_only || false,
  search: props.filters.search || ''
})

const countByStatus = (status) => {
  return props.salePayments.data.filter(p => p.status === status).length
}

let searchTimeout = null
const debounceSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    applyFilters()
  }, 500)
}

const applyFilters = () => {
  router.get(route('sales.payments.index'), filters.value, {
    preserveState: true,
    preserveScroll: true
  })
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('tr-TR')
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('tr-TR', {
    style: 'currency',
    currency: 'TRY',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount)
}

const getDaysUntilDue = (dueDate) => {
  const today = new Date()
  const due = new Date(dueDate)
  const diffTime = due - today
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  return diffDays
}

const getDaysOverdue = (dueDate) => {
  return Math.abs(getDaysUntilDue(dueDate))
}

const getStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-700',
    paid: 'bg-green-100 text-green-700',
    partial: 'bg-blue-100 text-blue-700',
    overdue: 'bg-red-100 text-red-700',
    cancelled: 'bg-gray-100 text-gray-700'
  }
  return classes[status] || 'bg-gray-100 text-gray-700'
}

const markAsPaid = (paymentId) => {
  if (confirm('Bu ödemeyi ödendi olarak işaretlemek istediğinize emin misiniz?')) {
    router.post(route('sales.payments.mark-as-paid', paymentId), {
      // Bu veriler modal'dan alınacak, şimdilik basit
      paid_amount: 0,
      payment_method: 'bank_transfer'
    }, {
      preserveScroll: true
    })
  }
}
</script>
