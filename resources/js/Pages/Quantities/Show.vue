<template>
  <AppLayout title="Metraj Detayı" :full-width="true">
    <!-- Full-width header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-emerald-600 via-teal-700 to-cyan-800 border-b border-emerald-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex items-center space-x-4">
              <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                </svg>
              </div>
              <div>
                <h1 class="text-2xl lg:text-3xl font-bold text-white">Metraj Detayı #{{ quantity.id }}</h1>
                <p class="text-emerald-100 text-sm mt-1">{{ quantity.project.name }}</p>
              </div>
            </div>

            <div class="flex items-center space-x-3">
              <div class="bg-white/10 backdrop-blur-sm rounded-lg px-4 py-2">
                <span class="text-emerald-100 text-sm">İlerleme:</span>
                <span class="font-bold text-white ml-2">{{ quantity.completion_percentage }}%</span>
              </div>
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
                  <Link :href="route('quantities.index')" class="text-emerald-100 hover:text-white text-sm">Metraj Listesi</Link>
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
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
      <!-- Actions -->
      <div class="flex flex-wrap items-center gap-3">
        <Link
          :href="route('quantities.edit', quantity.id)"
          class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition-all duration-200 shadow-sm hover:shadow"
        >
          <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
          </svg>
          Düzenle
        </Link>

        <button
          v-if="!quantity.is_verified && canVerify"
          @click="verifyQuantity"
          class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition-all duration-200 shadow-sm hover:shadow"
        >
          <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          Doğrula
        </button>

        <button
          v-if="quantity.is_verified && !quantity.is_approved && canApprove"
          @click="approveQuantity"
          class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium transition-all duration-200 shadow-sm hover:shadow"
        >
          <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          Onayla
        </button>

        <Link
          :href="route('quantities.index')"
          class="inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-gray-700 rounded-lg hover:bg-gray-50 font-medium transition-all duration-200"
        >
          <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Geri Dön
        </Link>
      </div>

      <!-- Main Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column (2/3) -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Proje Bilgileri -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Proje Bilgileri</h3>
            </div>
            <div class="p-6">
              <div class="grid grid-cols-2 gap-6">
                <div>
                  <dt class="text-sm font-medium text-gray-500">Proje</dt>
                  <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ quantity.project.name }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">İş Kalemi</dt>
                  <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ quantity.work_item.name }}</dd>
                  <dd class="text-xs text-gray-500">{{ quantity.work_item.code }}</dd>
                </div>
              </div>
            </div>
          </div>

          <!-- İş Kalemi ve Metraj -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Metraj Bilgileri</h3>
            </div>
            <div class="p-6">
              <div class="grid grid-cols-2 gap-6">
                <div>
                  <dt class="text-sm font-medium text-gray-500">Planlanan Metraj</dt>
                  <dd class="mt-1 text-2xl font-bold text-gray-900">
                    {{ formatQuantity(quantity.planned_quantity) }} <span class="text-base text-gray-500">{{ quantity.unit }}</span>
                  </dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Gerçekleşen Metraj</dt>
                  <dd class="mt-1 text-2xl font-bold text-emerald-600">
                    {{ formatQuantity(quantity.completed_quantity) }} <span class="text-base text-gray-500">{{ quantity.unit }}</span>
                  </dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Kalan Metraj</dt>
                  <dd class="mt-1 text-xl font-bold" :class="quantity.remaining_quantity > 0 ? 'text-orange-600' : 'text-green-600'">
                    {{ formatQuantity(quantity.remaining_quantity) }} <span class="text-base text-gray-500">{{ quantity.unit }}</span>
                  </dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Tamamlanma Oranı</dt>
                  <dd class="mt-1">
                    <div class="flex items-center">
                      <div class="flex-1 mr-3">
                        <div class="w-full bg-gray-200 rounded-full h-3">
                          <div
                            class="h-3 rounded-full transition-all duration-300"
                            :class="quantity.completion_percentage >= 100 ? 'bg-green-600' : 'bg-emerald-600'"
                            :style="{ width: `${Math.min(quantity.completion_percentage, 100)}%` }"
                          ></div>
                        </div>
                      </div>
                      <span class="text-lg font-bold text-gray-900">{{ quantity.completion_percentage }}%</span>
                    </div>
                  </dd>
                </div>
              </div>
            </div>
          </div>

          <!-- Lokasyon Bilgileri -->
          <div v-if="quantity.structure || quantity.floor || quantity.unit_info" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Lokasyon Bilgileri</h3>
            </div>
            <div class="p-6">
              <div class="grid grid-cols-3 gap-6">
                <div v-if="quantity.structure">
                  <dt class="text-sm font-medium text-gray-500">Blok/Yapı</dt>
                  <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ quantity.structure.name }}</dd>
                </div>
                <div v-if="quantity.floor">
                  <dt class="text-sm font-medium text-gray-500">Kat</dt>
                  <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ quantity.floor.name }}</dd>
                </div>
                <div v-if="quantity.unit_info">
                  <dt class="text-sm font-medium text-gray-500">Birim</dt>
                  <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ quantity.unit_info.name }}</dd>
                </div>
              </div>
            </div>
          </div>

          <!-- Ölçüm Detayları -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Ölçüm Detayları</h3>
            </div>
            <div class="p-6">
              <div class="grid grid-cols-2 gap-6">
                <div>
                  <dt class="text-sm font-medium text-gray-500">Ölçüm Tarihi</dt>
                  <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ quantity.measurement_date || '-' }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Ölçüm Yöntemi</dt>
                  <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ quantity.measurement_method || '-' }}</dd>
                </div>
              </div>
              <div v-if="quantity.notes" class="mt-6">
                <dt class="text-sm font-medium text-gray-500 mb-2">Notlar</dt>
                <dd class="text-sm text-gray-700 bg-gray-50 rounded-lg p-4">{{ quantity.notes }}</dd>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Column (1/3) -->
        <div class="space-y-6">
          <!-- Durum Bilgileri -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Durum Bilgileri</h3>
            </div>
            <div class="p-6 space-y-4">
              <div>
                <dt class="text-sm font-medium text-gray-500 mb-2">Durum</dt>
                <dd>
                  <span
                    class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-semibold"
                    :class="getStatusClass()"
                  >
                    <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                      <circle cx="10" cy="10" r="4"/>
                    </svg>
                    {{ getStatusLabel() }}
                  </span>
                </dd>
              </div>

              <div v-if="quantity.is_verified">
                <dt class="text-sm font-medium text-gray-500">Doğrulayan</dt>
                <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ quantity.verified_by }}</dd>
                <dd class="text-xs text-gray-500">{{ quantity.verified_at }}</dd>
              </div>

              <div v-if="quantity.is_approved">
                <dt class="text-sm font-medium text-gray-500">Onaylayan</dt>
                <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ quantity.approved_by }}</dd>
                <dd class="text-xs text-gray-500">{{ quantity.approved_at }}</dd>
              </div>
            </div>
          </div>

          <!-- Metraj Özeti -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Metraj Özeti</h3>
            </div>
            <div class="p-6 space-y-4">
              <div class="flex justify-between items-center">
                <dt class="text-sm font-medium text-gray-500">Planlanan</dt>
                <dd class="text-sm font-bold text-gray-900">
                  {{ formatQuantity(quantity.planned_quantity) }} {{ quantity.unit }}
                </dd>
              </div>
              <div class="flex justify-between items-center">
                <dt class="text-sm font-medium text-gray-500">Tamamlanan</dt>
                <dd class="text-sm font-bold text-emerald-600">
                  {{ formatQuantity(quantity.completed_quantity) }} {{ quantity.unit }}
                </dd>
              </div>
              <div class="flex justify-between items-center pt-3 border-t border-gray-200">
                <dt class="text-sm font-medium text-gray-500">Kalan</dt>
                <dd class="text-sm font-bold" :class="quantity.remaining_quantity > 0 ? 'text-orange-600' : 'text-green-600'">
                  {{ formatQuantity(quantity.remaining_quantity) }} {{ quantity.unit }}
                </dd>
              </div>
            </div>
          </div>

          <!-- İlişkili Hakediş Kayıtları -->
          <div v-if="relatedPayments && relatedPayments.length > 0" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-cyan-50 to-teal-50 flex items-center justify-between">
              <h3 class="text-lg font-medium text-gray-900">İlişkili Hakediş Kayıtları</h3>
              <span class="px-3 py-1 bg-cyan-600 text-white text-xs font-semibold rounded-full">
                {{ relatedPayments.length }} Kayıt
              </span>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hakediş #</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Taşeron</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dönem</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Miktar</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Birim Fiyat</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Tutar</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Durum</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">İşlem</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="payment in relatedPayments" :key="payment.id" class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">#{{ payment.id }}</td>
                    <td class="px-4 py-3 text-sm text-gray-900">{{ payment.subcontractor }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ payment.period }}</td>
                    <td class="px-4 py-3 text-sm text-right font-medium text-gray-900">
                      {{ formatQuantity(payment.completed_quantity) }} {{ quantity.unit }}
                    </td>
                    <td class="px-4 py-3 text-sm text-right text-gray-900">
                      {{ formatCurrency(payment.unit_price) }}
                    </td>
                    <td class="px-4 py-3 text-sm text-right font-bold text-cyan-600">
                      {{ formatCurrency(payment.total_amount) }}
                    </td>
                    <td class="px-4 py-3 text-center">
                      <span
                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                        :class="{
                          'bg-yellow-100 text-yellow-800': payment.status === 'planned',
                          'bg-blue-100 text-blue-800': payment.status === 'in_progress',
                          'bg-purple-100 text-purple-800': payment.status === 'completed',
                          'bg-green-100 text-green-800': payment.status === 'approved' || payment.status === 'paid'
                        }"
                      >
                        {{ getPaymentStatusLabel(payment.status) }}
                      </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                      <Link
                        :href="`/progress-payments/${payment.id}`"
                        class="text-cyan-600 hover:text-cyan-900 text-sm font-medium"
                      >
                        Görüntüle
                      </Link>
                    </td>
                  </tr>
                </tbody>
                <tfoot class="bg-gray-50">
                  <tr>
                    <td colspan="5" class="px-4 py-3 text-right text-sm font-bold text-gray-900">Toplam:</td>
                    <td class="px-4 py-3 text-right text-sm font-bold text-cyan-700">
                      {{ formatCurrency(totalPaymentAmount) }}
                    </td>
                    <td colspan="2"></td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>

          <!-- Kayıt Bilgileri -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Kayıt Bilgileri</h3>
            </div>
            <div class="p-6 space-y-3">
              <div>
                <dt class="text-sm font-medium text-gray-500">Oluşturulma</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ quantity.created_at }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Son Güncelleme</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ quantity.updated_at }}</dd>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { router, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  quantity: {
    type: Object,
    required: true
  },
  canVerify: {
    type: Boolean,
    default: true
  },
  canApprove: {
    type: Boolean,
    default: true
  },
  relatedPayments: {
    type: Array,
    default: () => []
  }
})

const formatQuantity = (quantity) => {
  if (quantity === null || quantity === undefined || isNaN(quantity)) {
    return '0'
  }

  const numQuantity = Number(quantity)
  if (isNaN(numQuantity)) {
    return '0'
  }

  return new Intl.NumberFormat('tr-TR', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 2
  }).format(numQuantity)
}

const getStatusLabel = () => {
  if (props.quantity.is_approved) {
    return 'Onaylanmış'
  } else if (props.quantity.is_verified) {
    return 'Doğrulanmış'
  } else {
    return 'Doğrulama Bekliyor'
  }
}

const getStatusClass = () => {
  if (props.quantity.is_approved) {
    return 'bg-green-100 text-green-700'
  } else if (props.quantity.is_verified) {
    return 'bg-blue-100 text-blue-700'
  } else {
    return 'bg-yellow-100 text-yellow-700'
  }
}

const verifyQuantity = () => {
  if (confirm('Bu metrajı doğrulamak istediğinizden emin misiniz?')) {
    router.post(route('quantities.verify', props.quantity.id), {}, {
      preserveScroll: true
    })
  }
}

const approveQuantity = () => {
  if (confirm('Bu metrajı onaylamak istediğinizden emin misiniz?')) {
    router.post(route('quantities.approve', props.quantity.id), {}, {
      preserveScroll: true
    })
  }
}

// Format currency
const formatCurrency = (amount) => {
  if (amount === null || amount === undefined || isNaN(amount)) {
    return '0,00 ₺'
  }

  const numAmount = Number(amount)
  if (isNaN(numAmount)) {
    return '0,00 ₺'
  }

  return new Intl.NumberFormat('tr-TR', {
    style: 'currency',
    currency: 'TRY',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(numAmount)
}

// Payment status label
const getPaymentStatusLabel = (status) => {
  const labels = {
    planned: 'Planlandı',
    in_progress: 'Devam Ediyor',
    completed: 'Tamamlandı',
    approved: 'Onaylandı',
    paid: 'Ödendi'
  }
  return labels[status] || status
}

// Computed: Total payment amount
const totalPaymentAmount = computed(() => {
  if (!props.relatedPayments || props.relatedPayments.length === 0) {
    return 0
  }

  return props.relatedPayments.reduce((sum, payment) => {
    const amount = parseFloat(payment.total_amount) || 0
    return sum + amount
  }, 0)
})
</script>
