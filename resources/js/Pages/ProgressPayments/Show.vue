<template>
  <AppLayout :title="`Hakediş #${progressPayment.id}`" :full-width="true">
    <!-- Full-width header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-800 border-b border-blue-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <!-- Header Info -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center space-x-4 mb-3">
                <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                  <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                  </svg>
                </div>
                <div>
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">
                    Hakediş #{{ progressPayment.id }}
                  </h1>
                  <p class="text-blue-100 text-sm mt-1">{{ progressPayment.project.name }}</p>
                </div>
              </div>

              <!-- Status Badge -->
              <div class="flex items-center space-x-3">
                <span :class="getStatusClass(progressPayment.status)" class="px-4 py-1.5 text-sm font-semibold rounded-full border-2">
                  {{ progressPayment.status_label }}
                </span>
                <span class="px-4 py-1.5 text-sm bg-white/10 text-white rounded-full border-2 border-white/30">
                  {{ progressPayment.completion_percentage }}% tamamlandı
                </span>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex-shrink-0 flex items-center space-x-3">
              <Link
                v-if="canEdit"
                :href="route('progress-payments.edit', progressPayment.id)"
                class="inline-flex items-center px-4 py-2 bg-white text-blue-600 text-sm font-medium rounded-lg hover:bg-blue-50 transition-all duration-200"
              >
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                </svg>
                Düzenle
              </Link>
              <button
                v-if="canApprove"
                @click="approve"
                class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-all duration-200"
              >
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                Onayla
              </button>
              <button
                v-if="canMarkAsPaid"
                @click="showPaymentModal = true"
                class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-all duration-200"
              >
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                </svg>
                Ödendi İşaretle
              </button>
              <Link
                :href="route('progress-payments.index')"
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
                  <Link :href="route('dashboard')" class="text-blue-100 hover:text-white transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                  </Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-blue-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <Link :href="route('progress-payments.index')" class="text-blue-100 hover:text-white text-sm">Hakediş Listesi</Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-blue-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
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
          <!-- İlerleme Durumu -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">İlerleme Durumu</h3>
            </div>
            <div class="p-6">
              <!-- Progress Bar -->
              <div class="mb-6">
                <div class="flex justify-between items-center mb-2">
                  <span class="text-sm font-medium text-gray-700">Tamamlanma Oranı</span>
                  <span class="text-3xl font-bold text-blue-600">{{ progressPayment.completion_percentage }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-4">
                  <div
                    class="h-4 rounded-full transition-all"
                    :class="progressPayment.completion_percentage >= 100 ? 'bg-green-600' : 'bg-blue-600'"
                    :style="{ width: `${Math.min(progressPayment.completion_percentage, 100)}%` }"
                  ></div>
                </div>
              </div>

              <!-- Metraj Grid -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="bg-gradient-to-br from-gray-50 to-gray-100 p-4 rounded-lg border border-gray-200">
                  <div class="text-sm text-gray-600 mb-1">Planlanan Metraj</div>
                  <div class="text-2xl font-bold text-gray-900">
                    {{ progressPayment.planned_quantity }} {{ progressPayment.unit }}
                  </div>
                </div>
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 p-4 rounded-lg border border-blue-200">
                  <div class="text-sm text-blue-600 mb-1">Tamamlanan Metraj</div>
                  <div class="text-2xl font-bold text-blue-700">
                    {{ progressPayment.completed_quantity }} {{ progressPayment.unit }}
                  </div>
                </div>
              </div>

              <!-- Tutar Bilgisi -->
              <div class="bg-gradient-to-br from-green-50 to-emerald-50 p-6 rounded-lg border border-green-200">
                <div class="flex justify-between items-start">
                  <div class="flex-1">
                    <div class="text-sm text-green-600 mb-2">Hakediş Tutarı</div>
                    <div class="text-4xl font-bold text-green-700 mb-2">
                      {{ formatCurrency(progressPayment.total_amount) }}
                    </div>
                    <div class="text-sm text-green-600">
                      {{ progressPayment.completed_quantity }} {{ progressPayment.unit }} × {{ formatCurrency(progressPayment.unit_price) }} birim fiyat
                    </div>
                  </div>
                  <div :class="getStatusBadgeClass(progressPayment.status)" class="px-4 py-2 rounded-lg font-semibold text-sm">
                    {{ progressPayment.status_label }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Detay Bilgileri -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Detay Bilgileri</h3>
            </div>
            <div class="p-6">
              <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <dt class="text-sm font-medium text-gray-500">Proje</dt>
                  <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ progressPayment.project.name }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Taşeron</dt>
                  <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ progressPayment.subcontractor.company_name }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">İş Kalemi</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ progressPayment.work_item.name }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Birim Fiyat</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatCurrency(progressPayment.unit_price) }} / {{ progressPayment.unit }}</dd>
                </div>
                <div v-if="progressPayment.structure">
                  <dt class="text-sm font-medium text-gray-500">Blok/Yapı</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ progressPayment.structure.name }}</dd>
                </div>
                <div v-if="progressPayment.floor">
                  <dt class="text-sm font-medium text-gray-500">Kat</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ progressPayment.floor.floor_display }}</dd>
                </div>
                <div v-if="progressPayment.project_unit">
                  <dt class="text-sm font-medium text-gray-500">Birim/Daire</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ progressPayment.project_unit.name }}</dd>
                </div>
                <div v-if="progressPayment.period_year && progressPayment.period_month">
                  <dt class="text-sm font-medium text-gray-500">Dönem</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ getMonthName(progressPayment.period_month) }} {{ progressPayment.period_year }}</dd>
                </div>
              </dl>
            </div>
          </div>

          <!-- İlişkili Metraj Kaydı -->
          <div v-if="progressPayment.quantity" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-emerald-50 to-green-50 flex items-center justify-between">
              <h3 class="text-lg font-medium text-gray-900">İlişkili Metraj Kaydı</h3>
              <Link
                :href="route('quantities.show', progressPayment.quantity.id)"
                class="inline-flex items-center text-sm text-emerald-600 hover:text-emerald-800 font-medium"
              >
                <span>Metraj Detayı</span>
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </Link>
            </div>
            <div class="p-6 space-y-4">
              <div class="grid grid-cols-2 gap-4">
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                  <dt class="text-xs font-medium text-gray-500 mb-1">Planlanan Toplam</dt>
                  <dd class="text-lg font-bold text-gray-900">
                    {{ progressPayment.quantity.planned_quantity }} {{ progressPayment.unit }}
                  </dd>
                </div>
                <div class="bg-emerald-50 p-4 rounded-lg border border-emerald-200">
                  <dt class="text-xs font-medium text-emerald-600 mb-1">Tamamlanan Toplam</dt>
                  <dd class="text-lg font-bold text-emerald-700">
                    {{ progressPayment.quantity.completed_quantity }} {{ progressPayment.unit }}
                  </dd>
                </div>
                <div class="bg-orange-50 p-4 rounded-lg border border-orange-200">
                  <dt class="text-xs font-medium text-orange-600 mb-1">Kalan</dt>
                  <dd class="text-lg font-bold text-orange-700">
                    {{ progressPayment.quantity.remaining_quantity }} {{ progressPayment.unit }}
                  </dd>
                </div>
                <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                  <dt class="text-xs font-medium text-blue-600 mb-1">Metraj İlerlemesi</dt>
                  <dd class="text-lg font-bold text-blue-700">
                    {{ progressPayment.quantity.completion_percentage }}%
                  </dd>
                </div>
              </div>
              <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mt-4">
                <div class="flex">
                  <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                    </svg>
                  </div>
                  <div class="ml-3">
                    <p class="text-sm text-amber-800">
                      Bu hakediş <strong>Metraj #{{ progressPayment.quantity.id }}</strong> kaydına bağlıdır. Tüm hakediş kayıtlarını görmek için metraj detay sayfasını ziyaret edebilirsiniz.
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Notlar -->
          <div v-if="progressPayment.notes" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Notlar</h3>
            </div>
            <div class="p-6">
              <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ progressPayment.notes }}</p>
            </div>
          </div>

          <!-- Onay Bilgileri -->
          <div v-if="progressPayment.approved_by" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Onay Bilgileri</h3>
            </div>
            <div class="p-6">
              <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <dt class="text-sm font-medium text-gray-500">Onaylayan</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ progressPayment.approved_by.name }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Onay Tarihi</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ progressPayment.approved_at }}</dd>
                </div>
                <div v-if="progressPayment.payment_date">
                  <dt class="text-sm font-medium text-gray-500">Ödeme Tarihi</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ progressPayment.payment_date }}</dd>
                </div>
              </dl>
            </div>
          </div>
        </div>

        <!-- Right Column - Timeline & Stats -->
        <div class="space-y-6">
          <!-- Zaman Çizelgesi -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Zaman Çizelgesi</h3>
            </div>
            <div class="p-6">
              <dl class="space-y-4">
                <div>
                  <dt class="text-xs font-medium text-gray-500 uppercase">Oluşturulma</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ progressPayment.created_at }}</dd>
                </div>
                <div>
                  <dt class="text-xs font-medium text-gray-500 uppercase">Son Güncelleme</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ progressPayment.updated_at }}</dd>
                </div>
              </dl>
            </div>
          </div>

          <!-- Hızlı İstatistikler -->
          <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl border border-blue-200 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Hızlı Bilgiler</h3>
            <div class="space-y-3">
              <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Tamamlanma</span>
                <span class="text-sm font-bold text-blue-600">{{ progressPayment.completion_percentage }}%</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Durum</span>
                <span class="text-sm font-bold text-gray-900">{{ progressPayment.status_label }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Toplam Tutar</span>
                <span class="text-sm font-bold text-green-600">{{ formatCurrency(progressPayment.total_amount) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Payment Modal -->
    <Modal :show="showPaymentModal" @close="showPaymentModal = false">
      <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Ödeme Tarihi Belirle</h3>
        <form @submit.prevent="markAsPaid">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Ödeme Tarihi</label>
            <input
              v-model="paymentForm.payment_date"
              type="date"
              class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              required
            />
          </div>
          <div class="flex justify-end space-x-3">
            <button
              type="button"
              @click="showPaymentModal = false"
              class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300"
            >
              İptal
            </button>
            <button
              type="submit"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
            >
              Kaydet
            </button>
          </div>
        </form>
      </div>
    </Modal>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Modal from '@/Components/UI/Modal.vue'

const props = defineProps({
  progressPayment: {
    type: Object,
    required: true
  }
})

const showPaymentModal = ref(false)
const paymentForm = ref({
  payment_date: new Date().toISOString().split('T')[0]
})

const canEdit = computed(() => {
  return ['planned', 'in_progress'].includes(props.progressPayment.status)
})

const canApprove = computed(() => {
  return props.progressPayment.status === 'completed'
})

const canMarkAsPaid = computed(() => {
  return props.progressPayment.status === 'approved'
})

const getStatusClass = (status) => {
  const classes = {
    planned: 'bg-gray-100 text-gray-700 border-gray-300',
    in_progress: 'bg-blue-100 text-blue-700 border-blue-300',
    completed: 'bg-purple-100 text-purple-700 border-purple-300',
    approved: 'bg-green-100 text-green-700 border-green-300',
    paid: 'bg-emerald-100 text-emerald-700 border-emerald-300'
  }
  return classes[status] || classes.planned
}

const getStatusBadgeClass = (status) => {
  const classes = {
    planned: 'bg-gray-100 text-gray-700',
    in_progress: 'bg-blue-100 text-blue-700',
    completed: 'bg-purple-100 text-purple-700',
    approved: 'bg-green-100 text-green-700',
    paid: 'bg-emerald-100 text-emerald-700'
  }
  return classes[status] || classes.planned
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('tr-TR', {
    style: 'currency',
    currency: 'TRY',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount)
}

const getMonthName = (month) => {
  const months = ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık']
  return months[month - 1] || month
}

const approve = () => {
  if (confirm('Bu hakediş kaydını onaylamak istediğinizden emin misiniz?')) {
    router.post(route('progress-payments.approve', props.progressPayment.id), {}, {
      preserveScroll: true,
      onSuccess: () => {
        // Success handled by controller
      }
    })
  }
}

const markAsPaid = () => {
  router.post(route('progress-payments.mark-as-paid', props.progressPayment.id), paymentForm.value, {
    preserveScroll: true,
    onSuccess: () => {
      showPaymentModal.value = false
    }
  })
}
</script>