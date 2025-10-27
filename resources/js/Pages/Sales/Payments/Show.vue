<template>
  <AppLayout :title="`Ödeme #${payment.payment_number}`" :full-width="true">
    <!-- Header -->
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
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">
                    Ödeme #{{ payment.payment_number }}
                  </h1>
                  <p class="text-amber-100 text-sm mt-1">{{ payment.customer?.full_name }}</p>
                </div>
              </div>

              <!-- Status Badge -->
              <div class="flex items-center space-x-3">
                <span :class="payment.status_badge?.class" class="px-4 py-1.5 text-sm font-semibold rounded-full">
                  {{ payment.status_badge?.text }}
                </span>
                <span v-if="payment.is_overdue" class="px-4 py-1.5 text-sm bg-red-500/20 text-red-100 rounded-full border border-red-400/30">
                  {{ payment.days_until_due }} gün gecikmiş
                </span>
                <span v-else-if="payment.days_until_due !== null && payment.days_until_due >= 0" class="px-4 py-1.5 text-sm bg-blue-500/20 text-blue-100 rounded-full border border-blue-400/30">
                  {{ payment.days_until_due }} gün kaldı
                </span>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex-shrink-0 flex items-center space-x-3">
              <Link
                v-if="payment.status !== 'paid'"
                :href="route('sales.payments.edit', payment.id)"
                class="inline-flex items-center px-4 py-2 bg-white text-amber-600 text-sm font-medium rounded-lg hover:bg-amber-50 transition-all duration-200"
              >
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                </svg>
                Düzenle
              </Link>
              <Link
                :href="route('sales.payments.index')"
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
                  <Link :href="route('sales.payments.index')" class="text-amber-100 hover:text-white text-sm">Ödeme Takibi</Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-amber-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <span class="text-white font-medium text-sm">{{ payment.payment_number }}</span>
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
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 p-4 rounded-lg border border-blue-200">
                  <dt class="text-xs font-medium text-blue-600 mb-1">Toplam Tutar</dt>
                  <dd class="text-2xl font-bold text-blue-700">
                    {{ formatCurrency(payment.amount) }}
                  </dd>
                </div>
                <div class="bg-gradient-to-br from-emerald-50 to-green-50 p-4 rounded-lg border border-emerald-200">
                  <dt class="text-xs font-medium text-emerald-600 mb-1">Ödenen</dt>
                  <dd class="text-2xl font-bold text-emerald-700">
                    {{ formatCurrency(payment.paid_amount) }}
                  </dd>
                </div>
                <div class="bg-gradient-to-br from-orange-50 to-amber-50 p-4 rounded-lg border border-orange-200">
                  <dt class="text-xs font-medium text-orange-600 mb-1">Kalan</dt>
                  <dd class="text-2xl font-bold text-orange-700">
                    {{ formatCurrency(payment.remaining_amount) }}
                  </dd>
                </div>
              </div>

              <div v-if="payment.late_fee > 0" class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-center">
                  <svg class="h-5 w-5 text-red-600 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                  </svg>
                  <div>
                    <div class="text-sm font-medium text-red-800">Gecikme Cezası</div>
                    <div class="text-lg font-bold text-red-900">{{ formatCurrency(payment.late_fee) }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Ödeme Detayları -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Ödeme Detayları</h3>
            </div>
            <div class="p-6">
              <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <dt class="text-sm font-medium text-gray-500">Müşteri</dt>
                  <dd class="mt-1">
                    <Link :href="route('sales.customers.show', payment.customer_id)" class="text-sm text-amber-600 hover:text-amber-800 font-semibold">
                      {{ payment.customer?.full_name }}
                    </Link>
                  </dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Satış Kaydı</dt>
                  <dd class="mt-1">
                    <Link :href="route('sales.unit-sales.show', payment.unit_sale_id)" class="text-sm text-amber-600 hover:text-amber-800 font-semibold">
                      {{ payment.unit_sale?.sale_number }}
                    </Link>
                  </dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Ödeme Tipi</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ payment.payment_type_label }}</dd>
                </div>
                <div v-if="payment.installment_number !== null">
                  <dt class="text-sm font-medium text-gray-500">Taksit Numarası</dt>
                  <dd class="mt-1 text-sm text-gray-900">
                    {{ payment.installment_number === 0 ? 'Peşinat' : `Taksit ${payment.installment_number}` }}
                  </dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Vade Tarihi</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDate(payment.due_date) }}</dd>
                </div>
                <div v-if="payment.payment_date">
                  <dt class="text-sm font-medium text-gray-500">Ödeme Tarihi</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDate(payment.payment_date) }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Ödeme Yöntemi</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ payment.payment_method_label }}</dd>
                </div>
              </dl>
            </div>
          </div>

          <!-- Banka/Çek Bilgileri -->
          <div v-if="payment.bank_name || payment.check_number || payment.transaction_reference" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Banka ve Çek Bilgileri</h3>
            </div>
            <div class="p-6">
              <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div v-if="payment.bank_name">
                  <dt class="text-sm font-medium text-gray-500">Banka</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ payment.bank_name }}</dd>
                </div>
                <div v-if="payment.check_number">
                  <dt class="text-sm font-medium text-gray-500">Çek Numarası</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ payment.check_number }}</dd>
                </div>
                <div v-if="payment.check_date">
                  <dt class="text-sm font-medium text-gray-500">Çek Tarihi</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDate(payment.check_date) }}</dd>
                </div>
                <div v-if="payment.transaction_reference">
                  <dt class="text-sm font-medium text-gray-500">İşlem Referansı</dt>
                  <dd class="mt-1 text-sm text-gray-900 font-mono">{{ payment.transaction_reference }}</dd>
                </div>
              </dl>
            </div>
          </div>

          <!-- Notlar -->
          <div v-if="payment.notes" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Notlar</h3>
            </div>
            <div class="p-6">
              <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ payment.notes }}</p>
            </div>
          </div>

          <!-- Onay Bilgileri -->
          <div v-if="payment.approved_by" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Onay Bilgileri</h3>
            </div>
            <div class="p-6">
              <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <dt class="text-sm font-medium text-gray-500">Onaylayan</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ payment.approver?.name }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Onay Tarihi</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDate(payment.approved_at) }}</dd>
                </div>
              </dl>
            </div>
          </div>
        </div>

        <!-- Right Column - Stats & Info -->
        <div class="space-y-6">
          <!-- İstatistikler -->
          <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-xl border border-amber-200 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Özet Bilgiler</h3>
            <div class="space-y-4">
              <div class="bg-white rounded-lg p-4 border border-amber-100">
                <div class="text-xs text-gray-600 mb-1">Ödeme Tutarı</div>
                <div class="text-2xl font-bold text-amber-600">
                  {{ formatCurrency(payment.amount) }}
                </div>
              </div>
              <div class="bg-white rounded-lg p-4 border border-amber-100">
                <div class="text-xs text-gray-600 mb-1">Ödenen</div>
                <div class="text-2xl font-bold text-emerald-600">
                  {{ formatCurrency(payment.paid_amount) }}
                </div>
              </div>
              <div class="bg-white rounded-lg p-4 border border-amber-100">
                <div class="text-xs text-gray-600 mb-1">Durum</div>
                <div class="text-sm font-bold text-gray-900">
                  {{ payment.status_badge?.text }}
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
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDate(payment.created_at) }}</dd>
                </div>
                <div>
                  <dt class="text-xs font-medium text-gray-500 uppercase">Son Güncelleme</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDate(payment.updated_at) }}</dd>
                </div>
                <div v-if="payment.reminder_sent_at">
                  <dt class="text-xs font-medium text-gray-500 uppercase">Son Hatırlatma</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDate(payment.reminder_sent_at) }}</dd>
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
  payment: {
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
