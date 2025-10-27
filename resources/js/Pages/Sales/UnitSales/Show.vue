<template>
  <AppLayout :title="`Satış #${unitSale.sale_number}`" :full-width="true">
    <!-- Header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-emerald-600 via-emerald-700 to-green-800 border-b border-emerald-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <!-- Header Info -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center space-x-4 mb-3">
                <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                  <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                  </svg>
                </div>
                <div>
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">
                    Satış #{{ unitSale.sale_number }}
                  </h1>
                  <p class="text-emerald-100 text-sm mt-1">{{ unitSale.customer?.full_name }}</p>
                </div>
              </div>

              <!-- Status Badges -->
              <div class="flex items-center space-x-3 flex-wrap gap-2">
                <span :class="unitSale.status_badge?.class" class="px-4 py-1.5 text-sm font-semibold rounded-full">
                  {{ unitSale.status_badge?.text }}
                </span>
                <span :class="unitSale.deed_status_badge?.class" class="px-4 py-1.5 text-sm font-semibold rounded-full">
                  {{ unitSale.deed_status_badge?.text }}
                </span>
                <span class="px-4 py-1.5 text-sm bg-white/10 text-white rounded-full border-2 border-white/30">
                  {{ unitSale.payment_completion_percentage }}% ödendi
                </span>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex-shrink-0 flex items-center space-x-3">
              <Link
                :href="route('sales.unit-sales.edit', unitSale.id)"
                class="inline-flex items-center px-4 py-2 bg-white text-emerald-600 text-sm font-medium rounded-lg hover:bg-emerald-50 transition-all duration-200"
              >
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                </svg>
                Düzenle
              </Link>
              <Link
                :href="route('sales.unit-sales.index')"
                class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm border border-white/30 text-white text-sm font-medium rounded-lg hover:bg-white/20 transition-all duration-200"
              >
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                Geri Dön
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
                  <Link :href="route('dashboard')" class="text-emerald-100 hover:text-white transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                  </Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-emerald-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <Link :href="route('sales.unit-sales.index')" class="text-emerald-100 hover:text-white text-sm">Satışlar</Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-emerald-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <span class="text-white font-medium text-sm">{{ unitSale.sale_number }}</span>
                </li>
              </ol>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Main Content -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Main Details -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Ödeme Durumu -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Ödeme Durumu</h3>
            </div>
            <div class="p-6">
              <!-- Progress Bar -->
              <div class="mb-6">
                <div class="flex justify-between items-center mb-2">
                  <span class="text-sm font-medium text-gray-700">Ödeme Tamamlanma Oranı</span>
                  <span class="text-3xl font-bold text-emerald-600">{{ unitSale.payment_completion_percentage }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-4">
                  <div
                    class="h-4 rounded-full transition-all"
                    :class="unitSale.payment_completion_percentage >= 100 ? 'bg-green-600' : 'bg-emerald-600'"
                    :style="{ width: `${Math.min(unitSale.payment_completion_percentage, 100)}%` }"
                  ></div>
                </div>
              </div>

              <!-- Fiyat Bilgisi -->
              <div class="bg-gradient-to-br from-emerald-50 to-green-50 p-6 rounded-lg border border-emerald-200">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                  <div>
                    <div class="text-sm text-emerald-600 mb-1">Liste Fiyatı</div>
                    <div class="text-2xl font-bold text-gray-900">
                      {{ formatCurrency(unitSale.list_price) }}
                    </div>
                  </div>
                  <div>
                    <div class="text-sm text-red-600 mb-1">İndirim</div>
                    <div class="text-2xl font-bold text-red-600">
                      -{{ formatCurrency(unitSale.discount_amount) }}
                      <span v-if="unitSale.discount_percentage" class="text-sm">(%{{ unitSale.discount_percentage }})</span>
                    </div>
                  </div>
                </div>
                <div class="border-t border-emerald-300 pt-4">
                  <div class="text-sm text-emerald-600 mb-2">Net Satış Fiyatı</div>
                  <div class="text-4xl font-bold text-emerald-700">
                    {{ formatCurrency(unitSale.final_price) }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Satış Detayları -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Satış Detayları</h3>
            </div>
            <div class="p-6">
              <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <dt class="text-sm font-medium text-gray-500">Müşteri</dt>
                  <dd class="mt-1">
                    <Link :href="route('sales.customers.show', unitSale.customer_id)" class="text-sm text-emerald-600 hover:text-emerald-800 font-semibold">
                      {{ unitSale.customer?.full_name }}
                    </Link>
                  </dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Proje</dt>
                  <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ unitSale.project?.name }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Birim/Daire</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ unitSale.project_unit?.name || '-' }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Satış Tipi</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ unitSale.sale_type_label }}</dd>
                </div>
                <div v-if="unitSale.reservation_date">
                  <dt class="text-sm font-medium text-gray-500">Rezervasyon Tarihi</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDate(unitSale.reservation_date) }}</dd>
                </div>
                <div v-if="unitSale.sale_date">
                  <dt class="text-sm font-medium text-gray-500">Satış Tarihi</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDate(unitSale.sale_date) }}</dd>
                </div>
                <div v-if="unitSale.contract_date">
                  <dt class="text-sm font-medium text-gray-500">Sözleşme Tarihi</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDate(unitSale.contract_date) }}</dd>
                </div>
                <div v-if="unitSale.delivery_date">
                  <dt class="text-sm font-medium text-gray-500">Planlanan Teslim</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDate(unitSale.delivery_date) }}</dd>
                </div>
              </dl>
            </div>
          </div>

          <!-- Ödeme Planı -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Ödeme Planı</h3>
            </div>
            <div class="p-6">
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                  <dt class="text-xs font-medium text-blue-600 mb-1">Peşinat</dt>
                  <dd class="text-lg font-bold text-blue-700">
                    {{ formatCurrency(unitSale.down_payment) }}
                  </dd>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                  <dt class="text-xs font-medium text-purple-600 mb-1">Taksit Sayısı</dt>
                  <dd class="text-lg font-bold text-purple-700">
                    {{ unitSale.installment_count }} ay
                  </dd>
                </div>
                <div class="bg-indigo-50 p-4 rounded-lg border border-indigo-200">
                  <dt class="text-xs font-medium text-indigo-600 mb-1">Aylık Taksit</dt>
                  <dd class="text-lg font-bold text-indigo-700">
                    {{ formatCurrency(unitSale.monthly_installment) }}
                  </dd>
                </div>
              </div>
            </div>
          </div>

          <!-- Ödemeler -->
          <div v-if="unitSale.sale_payments && unitSale.sale_payments.length > 0" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Ödemeler</h3>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Taksit</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vade</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tutar</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ödenen</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Durum</th>
                    <th class="px-6 py-3"></th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="payment in unitSale.sale_payments" :key="payment.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                      {{ payment.installment_number === 0 ? 'Peşinat' : `Taksit ${payment.installment_number}` }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ formatDate(payment.due_date) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ formatCurrency(payment.amount) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ formatCurrency(payment.paid_amount) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span :class="payment.status_badge?.class" class="px-3 py-1 text-xs font-semibold rounded-full">
                        {{ payment.status_badge?.text }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                      <Link :href="route('sales.payments.show', payment.id)" class="text-emerald-600 hover:text-emerald-900">
                        Detay
                      </Link>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Tapu Bilgileri -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Tapu Bilgileri</h3>
            </div>
            <div class="p-6">
              <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <dt class="text-sm font-medium text-gray-500">Tapu Durumu</dt>
                  <dd class="mt-1">
                    <span :class="unitSale.deed_status_badge?.class" class="px-3 py-1 text-xs font-semibold rounded-full">
                      {{ unitSale.deed_status_badge?.text }}
                    </span>
                  </dd>
                </div>
                <div v-if="unitSale.deed_type">
                  <dt class="text-sm font-medium text-gray-500">Tapu Tipi</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ unitSale.deed_type }}</dd>
                </div>
                <div v-if="unitSale.deed_transfer_date">
                  <dt class="text-sm font-medium text-gray-500">Devir Tarihi</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDate(unitSale.deed_transfer_date) }}</dd>
                </div>
              </dl>
            </div>
          </div>

          <!-- Notlar -->
          <div v-if="unitSale.notes" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Notlar</h3>
            </div>
            <div class="p-6">
              <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ unitSale.notes }}</p>
            </div>
          </div>
        </div>

        <!-- Right Column - Stats & Info -->
        <div class="space-y-6">
          <!-- İstatistikler -->
          <div class="bg-gradient-to-br from-emerald-50 to-green-50 rounded-xl border border-emerald-200 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Özet Bilgiler</h3>
            <div class="space-y-4">
              <div class="bg-white rounded-lg p-4 border border-emerald-100">
                <div class="text-xs text-gray-600 mb-1">Net Satış Fiyatı</div>
                <div class="text-2xl font-bold text-emerald-600">
                  {{ formatCurrency(unitSale.final_price) }}
                </div>
              </div>
              <div class="bg-white rounded-lg p-4 border border-emerald-100">
                <div class="text-xs text-gray-600 mb-1">Ödeme Tamamlanma</div>
                <div class="text-2xl font-bold text-blue-600">
                  {{ unitSale.payment_completion_percentage }}%
                </div>
              </div>
              <div class="bg-white rounded-lg p-4 border border-emerald-100">
                <div class="text-xs text-gray-600 mb-1">Satış Durumu</div>
                <div class="text-sm font-bold text-gray-900">
                  {{ unitSale.status_badge?.text }}
                </div>
              </div>
            </div>
          </div>

          <!-- Zaman Çizelgesi -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Zaman Çizelgesi</h3>
            </div>
            <div class="p-6">
              <dl class="space-y-4">
                <div>
                  <dt class="text-xs font-medium text-gray-500 uppercase">Oluşturulma</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDate(unitSale.created_at) }}</dd>
                </div>
                <div>
                  <dt class="text-xs font-medium text-gray-500 uppercase">Son Güncelleme</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDate(unitSale.updated_at) }}</dd>
                </div>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

defineProps({
  unitSale: {
    type: Object,
    required: true
  }
})

const formatCurrency = (amount) => {
  if (!amount) return '₺0'
  return new Intl.NumberFormat('tr-TR', {
    style: 'currency',
    currency: 'TRY',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount)
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('tr-TR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}
</script>
