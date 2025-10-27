<template>
  <AppLayout :title="customer.full_name" :full-width="true">
    <!-- Header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-purple-600 via-purple-700 to-indigo-800 border-b border-purple-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <!-- Header Info -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center space-x-4 mb-3">
                <div class="flex-shrink-0 w-16 h-16 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                  <div class="text-2xl font-bold text-white">
                    {{ getInitials(customer.full_name) }}
                  </div>
                </div>
                <div>
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">
                    {{ customer.full_name }}
                  </h1>
                  <p class="text-purple-100 text-sm mt-1">
                    {{ customer.customer_type === 'individual' ? 'Bireysel Müşteri' : 'Kurumsal Müşteri' }}
                  </p>
                </div>
              </div>

              <!-- Status Badge -->
              <div class="flex items-center space-x-3">
                <span :class="customer.status_badge?.class" class="px-4 py-1.5 text-sm font-semibold rounded-full">
                  {{ customer.status_badge?.text || customer.customer_status }}
                </span>
                <span v-if="customer.customer_type === 'corporate'" class="px-4 py-1.5 text-sm bg-blue-500/20 text-blue-100 rounded-full border border-blue-400/30">
                  Kurumsal
                </span>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex-shrink-0 flex items-center space-x-3">
              <Link
                :href="route('sales.customers.edit', customer.id)"
                class="inline-flex items-center px-4 py-2 bg-white text-purple-600 text-sm font-medium rounded-lg hover:bg-purple-50 transition-all duration-200"
              >
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                </svg>
                Düzenle
              </Link>
              <Link
                :href="route('sales.customers.index')"
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
                  <Link :href="route('dashboard')" class="text-purple-100 hover:text-white transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                  </Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-purple-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <Link :href="route('sales.customers.index')" class="text-purple-100 hover:text-white text-sm">Müşteriler</Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-purple-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <span class="text-white font-medium text-sm">{{ customer.full_name }}</span>
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
          <!-- Temel Bilgiler -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Temel Bilgiler</h3>
            </div>
            <div class="p-6">
              <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div v-if="customer.customer_type === 'corporate' && customer.company_name">
                  <dt class="text-sm font-medium text-gray-500">Şirket Adı</dt>
                  <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ customer.company_name }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Ad Soyad</dt>
                  <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ customer.full_name }}</dd>
                </div>
                <div v-if="customer.customer_type === 'individual' && customer.tc_number">
                  <dt class="text-sm font-medium text-gray-500">TC Kimlik No</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ customer.tc_number }}</dd>
                </div>
                <div v-if="customer.customer_type === 'corporate' && customer.tax_office">
                  <dt class="text-sm font-medium text-gray-500">Vergi Dairesi</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ customer.tax_office }}</dd>
                </div>
                <div v-if="customer.customer_type === 'corporate' && customer.tax_number">
                  <dt class="text-sm font-medium text-gray-500">Vergi Numarası</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ customer.tax_number }}</dd>
                </div>
              </dl>
            </div>
          </div>

          <!-- İletişim Bilgileri -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">İletişim Bilgileri</h3>
            </div>
            <div class="p-6">
              <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <dt class="text-sm font-medium text-gray-500">E-posta</dt>
                  <dd class="mt-1">
                    <a :href="`mailto:${customer.email}`" class="text-sm text-blue-600 hover:text-blue-800">
                      {{ customer.email }}
                    </a>
                  </dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Telefon</dt>
                  <dd class="mt-1">
                    <a :href="`tel:${customer.phone}`" class="text-sm text-blue-600 hover:text-blue-800">
                      {{ customer.phone }}
                    </a>
                  </dd>
                </div>
                <div v-if="customer.city">
                  <dt class="text-sm font-medium text-gray-500">Şehir</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ customer.city }}</dd>
                </div>
                <div v-if="customer.district">
                  <dt class="text-sm font-medium text-gray-500">İlçe</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ customer.district }}</dd>
                </div>
                <div v-if="customer.address" class="md:col-span-2">
                  <dt class="text-sm font-medium text-gray-500">Adres</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ customer.address }}</dd>
                </div>
              </dl>
            </div>
          </div>

          <!-- Satış Geçmişi -->
          <div v-if="customer.unit_sales && customer.unit_sales.length > 0" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Satış Geçmişi</h3>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Satış No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Proje</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Birim</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tutar</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Durum</th>
                    <th class="px-6 py-3"></th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="sale in customer.unit_sales" :key="sale.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                      {{ sale.sale_number }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ sale.project?.name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ sale.project_unit?.name || '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ formatCurrency(sale.final_price) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span :class="sale.status_badge?.class" class="px-3 py-1 text-xs font-semibold rounded-full">
                        {{ sale.status_badge?.text }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                      <Link :href="route('sales.unit-sales.show', sale.id)" class="text-purple-600 hover:text-purple-900">
                        Detay
                      </Link>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Ödeme Geçmişi -->
          <div v-if="customer.sale_payments && customer.sale_payments.length > 0" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Son Ödemeler</h3>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ödeme No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vade Tarihi</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tutar</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Durum</th>
                    <th class="px-6 py-3"></th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="payment in customer.sale_payments.slice(0, 5)" :key="payment.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                      {{ payment.payment_number }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ formatDate(payment.due_date) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ formatCurrency(payment.amount) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span :class="payment.status_badge?.class" class="px-3 py-1 text-xs font-semibold rounded-full">
                        {{ payment.status_badge?.text }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                      <Link :href="route('sales.payments.show', payment.id)" class="text-purple-600 hover:text-purple-900">
                        Detay
                      </Link>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div v-if="customer.sale_payments.length > 5" class="px-6 py-3 bg-gray-50 border-t border-gray-200 text-center">
              <Link :href="route('sales.payments.index', { customer_id: customer.id })" class="text-sm text-purple-600 hover:text-purple-900 font-medium">
                Tüm Ödemeleri Görüntüle ({{ customer.sale_payments.length }} ödeme)
              </Link>
            </div>
          </div>

          <!-- Notlar -->
          <div v-if="customer.notes" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Notlar</h3>
            </div>
            <div class="p-6">
              <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ customer.notes }}</p>
            </div>
          </div>
        </div>

        <!-- Right Column - Stats & Info -->
        <div class="space-y-6">
          <!-- İstatistikler -->
          <div class="bg-gradient-to-br from-purple-50 to-indigo-50 rounded-xl border border-purple-200 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Müşteri İstatistikleri</h3>
            <div class="space-y-4">
              <div class="bg-white rounded-lg p-4 border border-purple-100">
                <div class="text-xs text-gray-600 mb-1">Toplam Satış</div>
                <div class="text-2xl font-bold text-purple-600">
                  {{ customer.unit_sales?.length || 0 }}
                </div>
              </div>
              <div class="bg-white rounded-lg p-4 border border-purple-100">
                <div class="text-xs text-gray-600 mb-1">Bekleyen Ödeme</div>
                <div class="text-2xl font-bold text-amber-600">
                  {{ pendingPaymentsCount }}
                </div>
              </div>
              <div class="bg-white rounded-lg p-4 border border-purple-100">
                <div class="text-xs text-gray-600 mb-1">Müşteri Durumu</div>
                <div class="text-sm font-bold text-gray-900">
                  {{ customer.status_badge?.text || customer.customer_status }}
                </div>
              </div>
            </div>
          </div>

          <!-- Ek Bilgiler -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Ek Bilgiler</h3>
            </div>
            <div class="p-6">
              <dl class="space-y-4">
                <div v-if="customer.reference_source">
                  <dt class="text-xs font-medium text-gray-500 uppercase">Referans Kaynağı</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ customer.reference_source }}</dd>
                </div>
                <div>
                  <dt class="text-xs font-medium text-gray-500 uppercase">Kayıt Tarihi</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDate(customer.created_at) }}</dd>
                </div>
                <div>
                  <dt class="text-xs font-medium text-gray-500 uppercase">Son Güncelleme</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDate(customer.updated_at) }}</dd>
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
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  customer: {
    type: Object,
    required: true
  }
})

const pendingPaymentsCount = computed(() => {
  if (!props.customer.sale_payments) return 0
  return props.customer.sale_payments.filter(p => p.status === 'pending' || p.status === 'overdue').length
})

const getInitials = (name) => {
  if (!name) return '?'
  const parts = name.trim().split(' ')
  if (parts.length >= 2) {
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
  }
  return name.substring(0, 2).toUpperCase()
}

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
