<template>
  <AppLayout
    title="Günlük Rapor Detayı - SPT İnşaat Puantaj Sistemi"
    :full-width="true"
  >
    <!-- Full-width header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-green-600 via-green-700 to-green-800 border-b border-green-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <!-- Header Info -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center space-x-4 mb-3">
                <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                  <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z" />
                  </svg>
                </div>
                <div>
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">{{ formatDate(dailyReport.report_date) }}</h1>
                  <p class="text-green-100 text-sm mt-1">{{ dailyReport.project?.name || '-' }}</p>
                </div>
              </div>

              <!-- Status Badge -->
              <div class="flex items-center space-x-3">
                <span
                  :class="getStatusClass(dailyReport.approval_status)"
                  class="px-4 py-1.5 text-sm font-semibold rounded-full border-2"
                >
                  {{ getStatusText(dailyReport.approval_status) }}
                </span>
                <span class="px-4 py-1.5 text-sm bg-white/10 text-white rounded-full border-2 border-white/30">
                  {{ getWeatherDisplay(dailyReport.weather_condition) }}
                  <span v-if="dailyReport.temperature"> - {{ dailyReport.temperature }}°C</span>
                </span>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex-shrink-0 flex items-center space-x-3">
              <Link
                v-if="canEdit"
                :href="route('daily-reports.edit', dailyReport.id)"
                class="inline-flex items-center px-4 py-2 bg-white text-green-600 text-sm font-medium rounded-lg hover:bg-green-50 transition-all duration-200"
              >
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                </svg>
                Düzenle
              </Link>
              <Link
                :href="route('daily-reports.index')"
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
                  <Link
                    :href="route('dashboard')"
                    class="text-green-100 hover:text-white transition-colors"
                  >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                    <span class="sr-only">Ana Sayfa</span>
                  </Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-green-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                  </svg>
                  <Link :href="route('daily-reports.index')" class="text-xs font-medium text-green-100 hover:text-white">
                    Günlük Raporlar
                  </Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-green-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                  </svg>
                  <span class="text-xs font-medium text-white">{{ formatDate(dailyReport.report_date) }}</span>
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
        <!-- Ana İçerik (Sol - 2/3) -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Hava Durumu ve İşçi Bilgileri -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Genel Bilgiler</h3>
            </div>

            <div class="p-6">
              <div class="grid grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-500 mb-1">Hava Durumu</label>
                  <p class="text-base font-medium text-gray-900">{{ getWeatherDisplay(dailyReport.weather_condition) }}</p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-500 mb-1">Sıcaklık</label>
                  <p class="text-base font-medium text-gray-900">{{ dailyReport.temperature ? dailyReport.temperature + '°C' : '-' }}</p>
                </div>

                <div v-if="dailyReport.weather_notes" class="col-span-2">
                  <label class="block text-sm font-medium text-gray-500 mb-1">Hava Notları</label>
                  <p class="text-base text-gray-700 bg-gray-50 p-4 rounded-lg">{{ dailyReport.weather_notes }}</p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-500 mb-1">Toplam İşçi</label>
                  <p class="text-2xl font-bold text-green-600">{{ dailyReport.total_workers || 0 }}</p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-500 mb-1">İç / Taşeron</label>
                  <p class="text-base font-medium text-gray-900">
                    {{ dailyReport.internal_workers || 0 }} / {{ dailyReport.subcontractor_workers || 0 }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- İş Özeti -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">İş Özeti</h3>
            </div>

            <div class="p-6">
              <div class="prose max-w-none">
                <p class="text-gray-700">{{ dailyReport.work_summary || '-' }}</p>
              </div>
            </div>
          </div>

          <!-- Tamamlanan İşler -->
          <div v-if="dailyReport.completed_works && dailyReport.completed_works.length > 0" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Tamamlanan İşler</h3>
            </div>

            <div class="p-6">
              <ul class="space-y-2">
                <li v-for="(work, index) in dailyReport.completed_works" :key="index" class="flex items-start">
                  <svg class="h-5 w-5 text-green-500 mr-2 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                  </svg>
                  <span class="text-gray-700">{{ work }}</span>
                </li>
              </ul>
            </div>
          </div>

          <!-- Devam Eden İşler -->
          <div v-if="dailyReport.ongoing_works && dailyReport.ongoing_works.length > 0" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Devam Eden İşler</h3>
            </div>

            <div class="p-6">
              <ul class="space-y-2">
                <li v-for="(work, index) in dailyReport.ongoing_works" :key="index" class="flex items-start">
                  <svg class="h-5 w-5 text-blue-500 mr-2 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                  </svg>
                  <span class="text-gray-700">{{ work }}</span>
                </li>
              </ul>
            </div>
          </div>

          <!-- Planlanan İşler -->
          <div v-if="dailyReport.planned_works && dailyReport.planned_works.length > 0" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Planlanan İşler</h3>
            </div>

            <div class="p-6">
              <ul class="space-y-2">
                <li v-for="(work, index) in dailyReport.planned_works" :key="index" class="flex items-start">
                  <svg class="h-5 w-5 text-gray-400 mr-2 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                  </svg>
                  <span class="text-gray-700">{{ work }}</span>
                </li>
              </ul>
            </div>
          </div>

          <!-- Sorunlar -->
          <div v-if="dailyReport.has_delays || dailyReport.has_accidents || dailyReport.has_material_shortage" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Sorunlar ve Gecikmeler</h3>
            </div>

            <div class="p-6 space-y-6">
              <!-- Gecikmeler -->
              <div v-if="dailyReport.has_delays" class="border-l-4 border-yellow-500 pl-4">
                <h4 class="text-sm font-medium text-gray-900 mb-2">Gecikme Nedenleri</h4>
                <ul v-if="dailyReport.delay_reasons && dailyReport.delay_reasons.length > 0" class="space-y-1">
                  <li v-for="(reason, index) in dailyReport.delay_reasons" :key="index" class="text-sm text-gray-700">
                    • {{ reason }}
                  </li>
                </ul>
              </div>

              <!-- Kazalar -->
              <div v-if="dailyReport.has_accidents" class="border-l-4 border-red-500 pl-4">
                <h4 class="text-sm font-medium text-gray-900 mb-2">Kaza Detayları</h4>
                <ul v-if="dailyReport.accident_details && dailyReport.accident_details.length > 0" class="space-y-1">
                  <li v-for="(detail, index) in dailyReport.accident_details" :key="index" class="text-sm text-gray-700">
                    • {{ detail }}
                  </li>
                </ul>
              </div>

              <!-- Malzeme Eksiklikleri -->
              <div v-if="dailyReport.has_material_shortage" class="border-l-4 border-orange-500 pl-4">
                <h4 class="text-sm font-medium text-gray-900 mb-2">Eksik Malzemeler</h4>
                <ul v-if="dailyReport.material_shortage_details && dailyReport.material_shortage_details.length > 0" class="space-y-1">
                  <li v-for="(material, index) in dailyReport.material_shortage_details" :key="index" class="text-sm text-gray-700">
                    • {{ material }}
                  </li>
                </ul>
              </div>
            </div>
          </div>

          <!-- Fotoğraflar -->
          <div v-if="dailyReport.photos && dailyReport.photos.length > 0" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Fotoğraflar</h3>
            </div>

            <div class="p-6">
              <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div v-for="(photo, index) in dailyReport.photos" :key="index" class="relative group">
                  <img
                    :src="`/storage/${photo}`"
                    :alt="`Fotoğraf ${index + 1}`"
                    class="w-full h-48 object-cover rounded-lg cursor-pointer hover:opacity-90 transition-opacity"
                    @click="openPhotoModal(photo)"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Notlar -->
          <div v-if="dailyReport.notes" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Ek Notlar</h3>
            </div>

            <div class="p-6">
              <p class="text-gray-700 whitespace-pre-wrap">{{ dailyReport.notes }}</p>
            </div>
          </div>
        </div>

        <!-- Yan Panel (Sağ - 1/3) -->
        <div class="lg:col-span-1 space-y-6">
          <!-- Rapor Bilgileri -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Rapor Bilgileri</h3>
            </div>

            <div class="p-6 space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Rapor Eden</label>
                <div class="flex items-center space-x-2">
                  <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                    <span class="text-green-600 font-medium text-xs">
                      {{ dailyReport.reporter?.name?.charAt(0) || '?' }}
                    </span>
                  </div>
                  <p class="text-base font-medium text-gray-900">{{ dailyReport.reporter?.name || '-' }}</p>
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Durum</label>
                <span
                  :class="getStatusClass(dailyReport.approval_status)"
                  class="inline-flex px-3 py-1 text-sm font-semibold rounded-full"
                >
                  {{ getStatusText(dailyReport.approval_status) }}
                </span>
              </div>

              <div v-if="dailyReport.approved_by">
                <label class="block text-sm font-medium text-gray-500 mb-1">
                  {{ dailyReport.approval_status === 'approved' ? 'Onaylayan' : 'Reddeden' }}
                </label>
                <div class="flex items-center space-x-2">
                  <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                    <span class="text-blue-600 font-medium text-xs">
                      {{ dailyReport.approver?.name?.charAt(0) || '?' }}
                    </span>
                  </div>
                  <p class="text-base font-medium text-gray-900">{{ dailyReport.approver?.name || '-' }}</p>
                </div>
                <p class="text-sm text-gray-500 mt-1">{{ formatDateTime(dailyReport.approved_at) }}</p>
              </div>

              <div v-if="dailyReport.rejection_reason">
                <label class="block text-sm font-medium text-gray-500 mb-1">Ret Nedeni</label>
                <p class="text-sm text-gray-700 bg-red-50 p-3 rounded-lg border border-red-200">{{ dailyReport.rejection_reason }}</p>
              </div>
            </div>
          </div>

          <!-- Ziyaretçiler -->
          <div v-if="dailyReport.visitors && dailyReport.visitors.length > 0" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Ziyaretçiler</h3>
            </div>

            <div class="p-6">
              <ul class="space-y-2">
                <li v-for="(visitor, index) in dailyReport.visitors" :key="index" class="flex items-center">
                  <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                  </svg>
                  <span class="text-sm text-gray-700">{{ visitor }}</span>
                </li>
              </ul>
            </div>
          </div>

          <!-- Aksiyonlar -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">İşlemler</h3>
            </div>

            <div class="p-6 space-y-3">
              <button
                v-if="canSubmit"
                @click="submitReport"
                class="w-full px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-all duration-200"
              >
                Raporu Gönder
              </button>

              <button
                v-if="canApprove"
                @click="approveReport"
                class="w-full px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-all duration-200"
              >
                Raporu Onayla
              </button>

              <button
                v-if="canApprove"
                @click="showRejectModal = true"
                class="w-full px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-all duration-200"
              >
                Raporu Reddet
              </button>

              <button
                v-if="canDelete"
                @click="deleteReport"
                class="w-full px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-300 transition-all duration-200"
              >
                Raporu Sil
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Reject Modal -->
    <div v-if="showRejectModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="showRejectModal = false">
      <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Raporu Reddet</h3>
        <textarea
          v-model="rejectionReason"
          rows="4"
          class="block w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors resize-none"
          placeholder="Ret nedenini yazın..."
        ></textarea>
        <div class="mt-4 flex items-center justify-end space-x-3">
          <button
            @click="showRejectModal = false"
            class="px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-300"
          >
            İptal
          </button>
          <button
            @click="rejectReport"
            :disabled="!rejectionReason"
            class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 disabled:opacity-50"
          >
            Reddet
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import { format } from 'date-fns'
import { tr } from 'date-fns/locale'
import AppLayout from '@/Layouts/AppLayout.vue'

// Props
const props = defineProps({
  dailyReport: {
    type: Object,
    required: true
  }
})

// State
const showRejectModal = ref(false)
const rejectionReason = ref('')

// Computed
const canEdit = computed(() => {
  return props.dailyReport.approval_status === 'draft' || props.dailyReport.approval_status === 'rejected'
})

const canSubmit = computed(() => {
  return props.dailyReport.approval_status === 'draft'
})

const canApprove = computed(() => {
  return props.dailyReport.approval_status === 'submitted'
})

const canDelete = computed(() => {
  return props.dailyReport.approval_status === 'draft'
})

// Methods
const getStatusClass = (status) => {
  const classes = {
    draft: 'bg-gray-100 text-gray-800 border-gray-200',
    submitted: 'bg-yellow-100 text-yellow-800 border-yellow-200',
    approved: 'bg-green-100 text-green-800 border-green-200',
    rejected: 'bg-red-100 text-red-800 border-red-200'
  }
  return classes[status] || 'bg-gray-100 text-gray-800 border-gray-200'
}

const getStatusText = (status) => {
  const texts = {
    draft: 'Taslak',
    submitted: 'Gönderildi',
    approved: 'Onaylandı',
    rejected: 'Reddedildi'
  }
  return texts[status] || status
}

const getWeatherDisplay = (condition) => {
  const weather = {
    sunny: 'Güneşli',
    cloudy: 'Bulutlu',
    rainy: 'Yağmurlu',
    snowy: 'Karlı',
    windy: 'Rüzgarlı',
    stormy: 'Fırtınalı'
  }
  return weather[condition] || '-'
}

const formatDate = (date) => {
  if (!date) return '-'
  try {
    return format(new Date(date), 'dd MMMM yyyy', { locale: tr })
  } catch (error) {
    return date
  }
}

const formatDateTime = (date) => {
  if (!date) return '-'
  try {
    return format(new Date(date), 'dd MMM yyyy HH:mm', { locale: tr })
  } catch (error) {
    return date
  }
}

const openPhotoModal = (photo) => {
  window.open(`/storage/${photo}`, '_blank')
}

const submitReport = () => {
  if (confirm('Raporu göndermek istediğinizden emin misiniz?')) {
    router.post(route('daily-reports.submit', props.dailyReport.id))
  }
}

const approveReport = () => {
  if (confirm('Raporu onaylamak istediğinizden emin misiniz?')) {
    router.post(route('daily-reports.approve', props.dailyReport.id))
  }
}

const rejectReport = () => {
  if (!rejectionReason.value) return

  router.post(route('daily-reports.reject', props.dailyReport.id), {
    rejection_reason: rejectionReason.value
  }, {
    onSuccess: () => {
      showRejectModal.value = false
      rejectionReason.value = ''
    }
  })
}

const deleteReport = () => {
  if (confirm('Bu raporu silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.')) {
    router.delete(route('daily-reports.destroy', props.dailyReport.id))
  }
}
</script>

<style scoped>
.w-full {
  width: 100% !important;
}

.prose {
  max-width: none;
}
</style>
