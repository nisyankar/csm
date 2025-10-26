<template>
  <AppLayout :title="`İşlem #${transaction.id}`" :full-width="true">
    <!-- Full-width header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-emerald-600 via-green-700 to-teal-800 border-b border-emerald-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <!-- Header Info -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center space-x-4 mb-3">
                <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                  <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                  </svg>
                </div>
                <div>
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">
                    İşlem #{{ transaction.id }}
                  </h1>
                  <p class="text-emerald-100 text-sm mt-1">{{ transaction.project?.name || 'Proje bilgisi yok' }}</p>
                </div>
              </div>

              <!-- Status Badge -->
              <div class="flex items-center space-x-3">
                <span
                  :class="transaction.transaction_type === 'income' ? 'bg-green-600/20 text-white border-green-400' : 'bg-red-600/20 text-white border-red-400'"
                  class="px-4 py-1.5 text-sm font-semibold rounded-full border-2"
                >
                  {{ transaction.transaction_type === 'income' ? 'Gelir' : 'Gider' }}
                </span>
                <span :class="getPaymentStatusBadgeClass(transaction.payment_status)" class="px-4 py-1.5 text-sm font-semibold rounded-full border-2">
                  {{ getPaymentStatusLabel(transaction.payment_status) }}
                </span>
                <span v-if="transaction.is_approved" class="px-4 py-1.5 text-sm bg-blue-600/20 text-white rounded-full border-2 border-blue-400">
                  Onaylandı
                </span>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex-shrink-0 flex items-center space-x-3">
              <Link
                :href="route('financial.edit', transaction.id)"
                class="inline-flex items-center px-4 py-2 bg-white text-emerald-600 text-sm font-medium rounded-lg hover:bg-emerald-50 transition-all duration-200"
              >
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                </svg>
                Düzenle
              </Link>
              <Link
                :href="route('financial.index')"
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
                  <Link :href="route('financial.index')" class="text-emerald-100 hover:text-white text-sm">Finansal İşlemler</Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-emerald-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <span class="text-white font-medium text-sm">Detay</span>
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
          <!-- Tutar Bilgisi -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
              <h3 class="text-lg font-semibold text-gray-900">Tutar Bilgileri</h3>
            </div>
            <div class="p-6">
              <div :class="transaction.transaction_type === 'income' ? 'bg-gradient-to-br from-green-50 to-emerald-50 border-green-200' : 'bg-gradient-to-br from-red-50 to-pink-50 border-red-200'" class="p-6 rounded-lg border">
                <div class="flex justify-between items-start">
                  <div class="flex-1">
                    <div :class="transaction.transaction_type === 'income' ? 'text-green-600' : 'text-red-600'" class="text-sm mb-2">
                      {{ transaction.transaction_type === 'income' ? 'Gelir Tutarı' : 'Gider Tutarı' }}
                    </div>
                    <div :class="transaction.transaction_type === 'income' ? 'text-green-700' : 'text-red-700'" class="text-4xl font-bold mb-2">
                      {{ formatCurrency(transaction.amount) }}
                    </div>
                    <div :class="transaction.transaction_type === 'income' ? 'text-green-600' : 'text-red-600'" class="text-sm">
                      Tarih: {{ formatDate(transaction.transaction_date) }}
                    </div>
                  </div>
                </div>
              </div>

              <!-- Ödeme Durumu -->
              <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-gradient-to-br from-gray-50 to-gray-100 p-4 rounded-lg border border-gray-200">
                  <div class="text-sm text-gray-600 mb-1">Ödenen Tutar</div>
                  <div class="text-2xl font-bold text-gray-900">
                    {{ formatCurrency(transaction.paid_amount || 0) }}
                  </div>
                </div>
                <div class="bg-gradient-to-br from-yellow-50 to-orange-50 p-4 rounded-lg border border-yellow-200">
                  <div class="text-sm text-yellow-600 mb-1">Kalan Tutar</div>
                  <div class="text-2xl font-bold text-yellow-700">
                    {{ formatCurrency(transaction.remaining_amount || transaction.amount) }}
                  </div>
                </div>
              </div>

              <!-- Ödeme İlerlemesi -->
              <div v-if="transaction.payment_percentage" class="mt-4">
                <div class="flex justify-between items-center mb-2">
                  <span class="text-sm font-medium text-gray-700">Ödeme İlerlemesi</span>
                  <span class="text-sm font-bold text-gray-900">{{ transaction.payment_percentage }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                  <div
                    class="h-3 rounded-full transition-all"
                    :class="transaction.payment_percentage >= 100 ? 'bg-emerald-600' : 'bg-blue-600'"
                    :style="{ width: `${Math.min(transaction.payment_percentage, 100)}%` }"
                  ></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Detay Bilgileri -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
              <h3 class="text-lg font-semibold text-gray-900">Detay Bilgileri</h3>
            </div>
            <div class="p-6">
              <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <dt class="text-sm font-medium text-gray-500">Proje</dt>
                  <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ transaction.project?.name || '-' }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Kategori</dt>
                  <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ transaction.category_name || '-' }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">İşlem Tarihi</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDate(transaction.transaction_date) }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Ödeme Yöntemi</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ getPaymentMethodLabel(transaction.payment_method) }}</dd>
                </div>
                <div v-if="transaction.invoice_number" class="md:col-span-2">
                  <dt class="text-sm font-medium text-gray-500">Fatura Numarası</dt>
                  <dd class="mt-1 text-sm text-gray-900 font-mono">{{ transaction.invoice_number }}</dd>
                </div>
                <div v-if="transaction.invoice_date">
                  <dt class="text-sm font-medium text-gray-500">Fatura Tarihi</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDate(transaction.invoice_date) }}</dd>
                </div>
                <div v-if="transaction.accounting_code">
                  <dt class="text-sm font-medium text-gray-500">Muhasebe Kodu</dt>
                  <dd class="mt-1 text-sm text-gray-900 font-mono">{{ transaction.accounting_code }}</dd>
                </div>
                <div class="md:col-span-2">
                  <dt class="text-sm font-medium text-gray-500">Açıklama</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ transaction.description }}</dd>
                </div>
              </dl>
            </div>
          </div>

          <!-- Kaynak Bilgisi (Otomatik İşlemler) -->
          <div v-if="transaction.source_module" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50">
              <h3 class="text-lg font-semibold text-gray-900">Kaynak Bilgisi</h3>
              <p class="text-sm text-gray-600 mt-1">Bu işlem otomatik olarak oluşturulmuştur</p>
            </div>
            <div class="p-6">
              <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <dt class="text-sm font-medium text-gray-500">Kaynak Modül</dt>
                  <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ getSourceModuleLabel(transaction.source_module) }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Kaynak ID</dt>
                  <dd class="mt-1 text-sm text-gray-900 font-mono">#{{ transaction.source_id }}</dd>
                </div>
              </dl>
            </div>
          </div>
        </div>

        <!-- Right Column - Additional Info -->
        <div class="space-y-6">
          <!-- Onay Bilgileri -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
              <h3 class="text-lg font-semibold text-gray-900">Onay Bilgileri</h3>
            </div>
            <div class="p-6 space-y-4">
              <div v-if="transaction.is_approved">
                <div class="flex items-center space-x-2 text-green-600 mb-3">
                  <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <span class="font-semibold">Onaylandı</span>
                </div>
                <div class="text-sm text-gray-600">
                  <div><span class="font-medium">Onaylayan:</span> {{ transaction.approved_by?.name || '-' }}</div>
                  <div class="mt-1"><span class="font-medium">Onay Tarihi:</span> {{ formatDateTime(transaction.approved_at) }}</div>
                </div>
              </div>
              <div v-else>
                <div class="flex items-center space-x-2 text-yellow-600 mb-3">
                  <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <span class="font-semibold">Onay Bekliyor</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Sistem Bilgileri -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
              <h3 class="text-lg font-semibold text-gray-900">Sistem Bilgileri</h3>
            </div>
            <div class="p-6 space-y-3 text-sm">
              <div>
                <div class="text-gray-500">Oluşturan</div>
                <div class="font-medium text-gray-900">{{ transaction.created_by?.name || '-' }}</div>
              </div>
              <div>
                <div class="text-gray-500">Oluşturma Tarihi</div>
                <div class="font-medium text-gray-900">{{ formatDateTime(transaction.created_at) }}</div>
              </div>
              <div v-if="transaction.updated_at !== transaction.created_at">
                <div class="text-gray-500">Son Güncelleme</div>
                <div class="font-medium text-gray-900">{{ formatDateTime(transaction.updated_at) }}</div>
              </div>
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

const props = defineProps({
  transaction: {
    type: Object,
    required: true
  }
})

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('tr-TR', {
    style: 'currency',
    currency: 'TRY',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(amount || 0)
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('tr-TR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatDateTime = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleString('tr-TR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getPaymentStatusLabel = (status) => {
  const labels = {
    pending: 'Beklemede',
    partial: 'Kısmi Ödendi',
    paid: 'Ödendi'
  }
  return labels[status] || status
}

const getPaymentStatusBadgeClass = (status) => {
  const classes = {
    pending: 'bg-yellow-600/20 text-white border-yellow-400',
    partial: 'bg-blue-600/20 text-white border-blue-400',
    paid: 'bg-emerald-600/20 text-white border-emerald-400'
  }
  return classes[status] || 'bg-gray-600/20 text-white border-gray-400'
}

const getPaymentMethodLabel = (method) => {
  const labels = {
    cash: 'Nakit',
    bank_transfer: 'Banka Havalesi',
    credit_card: 'Kredi Kartı',
    check: 'Çek',
    promissory_note: 'Senet'
  }
  return labels[method] || method || '-'
}

const getSourceModuleLabel = (module) => {
  const labels = {
    timesheet: 'Puantaj',
    purchase_order: 'Satınalma',
    progress_payment: 'Hakediş'
  }
  return labels[module] || module
}
</script>