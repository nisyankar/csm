<template>
  <AppLayout title="Denetim Detayı" :full-width="true">
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-purple-600 via-purple-700 to-indigo-800 border-b border-purple-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
              </div>
              <div>
                <h1 class="text-2xl lg:text-3xl font-bold text-white">Denetim Detayı</h1>
                <p class="text-purple-100 text-sm mt-1">{{ inspection.inspection_number }}</p>
              </div>
            </div>
            <div class="flex items-center space-x-3">
              <span
                class="inline-flex px-4 py-2 text-sm font-semibold rounded-lg"
                :class="{
                  'bg-blue-100 text-blue-800': inspection.status === 'scheduled',
                  'bg-green-100 text-green-800': inspection.status === 'completed',
                  'bg-yellow-100 text-yellow-800': inspection.status === 'pending_action',
                  'bg-gray-100 text-gray-800': inspection.status === 'closed'
                }"
              >
                {{ getStatusLabel(inspection.status) }}
              </span>
              <Link
                :href="route('inspections.edit', inspection.id)"
                class="inline-flex items-center px-4 py-2 bg-white text-purple-700 rounded-lg hover:bg-purple-50 font-medium transition-colors"
              >
                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                </svg>
                Düzenle
              </Link>
            </div>
          </div>
        </div>

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
                  <Link :href="route('inspections.index')" class="text-purple-100 hover:text-white text-sm">Denetimler</Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-purple-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
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

    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Denetim Bilgileri -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Denetim Bilgileri</h3>
            </div>
            <div class="p-6">
              <dl class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                  <dt class="text-sm font-medium text-gray-500">Denetim No</dt>
                  <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ inspection.inspection_number }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Denetim Tarihi</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ new Date(inspection.inspection_date).toLocaleDateString('tr-TR') }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Proje</dt>
                  <dd class="mt-1 text-sm text-gray-900">
                    {{ inspection.project?.name }}
                    <span class="text-gray-500 text-xs ml-1">({{ inspection.project?.project_code }})</span>
                  </dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Denetim Kuruluşu</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ inspection.inspection_company?.company_name }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Denetçi</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ inspection.inspector_name }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Denetim Türü</dt>
                  <dd class="mt-1">
                    <span
                      class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                      :class="{
                        'bg-blue-100 text-blue-800': inspection.inspection_type === 'periodic',
                        'bg-purple-100 text-purple-800': inspection.inspection_type === 'special',
                        'bg-green-100 text-green-800': inspection.inspection_type === 'final'
                      }"
                    >
                      {{ getInspectionTypeLabel(inspection.inspection_type) }}
                    </span>
                  </dd>
                </div>
                <div v-if="inspection.next_inspection_date">
                  <dt class="text-sm font-medium text-gray-500">Sonraki Denetim Tarihi</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ new Date(inspection.next_inspection_date).toLocaleDateString('tr-TR') }}</dd>
                </div>
              </dl>
            </div>
          </div>

          <!-- Bulgular -->
          <div v-if="inspection.findings" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Genel Bulgular</h3>
            </div>
            <div class="p-6">
              <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ inspection.findings }}</p>
            </div>
          </div>

          <!-- Uygunsuzluklar -->
          <div v-if="inspection.non_conformities?.length" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Uygunsuzluklar</h3>
            </div>
            <div class="p-6">
              <div class="space-y-4">
                <div
                  v-for="(nc, index) in inspection.non_conformities"
                  :key="index"
                  class="border border-gray-200 rounded-lg p-4"
                >
                  <div class="flex items-start justify-between mb-2">
                    <span
                      class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                      :class="{
                        'bg-yellow-100 text-yellow-800': nc.severity === 'minor',
                        'bg-orange-100 text-orange-800': nc.severity === 'major',
                        'bg-red-100 text-red-800': nc.severity === 'critical'
                      }"
                    >
                      {{ getSeverityLabel(nc.severity) }}
                    </span>
                    <span v-if="nc.deadline" class="text-xs text-gray-500">
                      Son Tarih: {{ new Date(nc.deadline).toLocaleDateString('tr-TR') }}
                    </span>
                  </div>
                  <p class="text-sm text-gray-900">{{ nc.description }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Düzeltici Faaliyetler -->
          <div v-if="inspection.corrective_actions?.length" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Düzeltici Faaliyetler</h3>
            </div>
            <div class="p-6">
              <div class="space-y-4">
                <div
                  v-for="(action, index) in inspection.corrective_actions"
                  :key="index"
                  class="border border-gray-200 rounded-lg p-4"
                >
                  <div class="flex items-start justify-between mb-2">
                    <span
                      class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                      :class="{
                        'bg-gray-100 text-gray-800': action.status === 'pending',
                        'bg-blue-100 text-blue-800': action.status === 'in_progress',
                        'bg-green-100 text-green-800': action.status === 'completed'
                      }"
                    >
                      {{ getActionStatusLabel(action.status) }}
                    </span>
                  </div>
                  <p class="text-sm text-gray-900 font-medium mb-2">{{ action.action }}</p>
                  <div class="flex items-center text-xs text-gray-500 space-x-4">
                    <span>Sorumlu: {{ action.responsible }}</span>
                    <span v-if="action.deadline">Son Tarih: {{ new Date(action.deadline).toLocaleDateString('tr-TR') }}</span>
                    <span v-if="action.completion_date">Tamamlanma: {{ new Date(action.completion_date).toLocaleDateString('tr-TR') }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Notlar -->
          <div v-if="inspection.notes" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Notlar</h3>
            </div>
            <div class="p-6">
              <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ inspection.notes }}</p>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Rapor -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Denetim Raporu</h3>
            </div>
            <div class="p-6">
              <div v-if="inspection.report_path" class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                <div class="flex items-center space-x-3">
                  <svg class="w-8 h-8 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                  </svg>
                  <div>
                    <p class="text-sm font-medium text-gray-900">Rapor Dosyası</p>
                    <p class="text-xs text-gray-500">PDF</p>
                  </div>
                </div>
                <a
                  :href="`/storage/${inspection.report_path}`"
                  target="_blank"
                  class="text-purple-600 hover:text-purple-800 p-2"
                  title="İndir"
                >
                  <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                  </svg>
                </a>
              </div>
              <div v-else class="text-center py-6 text-gray-500 text-sm">
                Rapor dosyası yüklenmemiş
              </div>
            </div>
          </div>

          <!-- Ekler -->
          <div v-if="inspection.attachments?.length" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">Ek Dosyalar</h3>
            </div>
            <div class="p-6">
              <div class="space-y-3">
                <div
                  v-for="(attachment, index) in inspection.attachments"
                  :key="index"
                  class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
                >
                  <div class="flex items-center space-x-3 flex-1 min-w-0">
                    <svg class="w-6 h-6 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13" />
                    </svg>
                    <div class="flex-1 min-w-0">
                      <p class="text-sm font-medium text-gray-900 truncate">{{ attachment.name }}</p>
                      <p class="text-xs text-gray-500">{{ formatFileSize(attachment.size) }}</p>
                    </div>
                  </div>
                  <a
                    :href="`/storage/${attachment.path}`"
                    target="_blank"
                    class="text-purple-600 hover:text-purple-800 p-2 flex-shrink-0"
                    title="İndir"
                  >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                  </a>
                </div>
              </div>
            </div>
          </div>

          <!-- İşlemler -->
          <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900">İşlemler</h3>
            </div>
            <div class="p-6 space-y-3">
              <Link
                :href="route('inspections.edit', inspection.id)"
                class="flex items-center justify-center w-full px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium transition-colors"
              >
                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                </svg>
                Düzenle
              </Link>
              <Link
                :href="route('inspections.index')"
                class="flex items-center justify-center w-full px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-gray-700 font-medium transition-colors"
              >
                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                Listeye Dön
              </Link>
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
  inspection: Object,
})

const getStatusLabel = (status) => {
  const labels = {
    scheduled: 'Planlandı',
    completed: 'Tamamlandı',
    pending_action: 'Eylem Bekliyor',
    closed: 'Kapatıldı',
  }
  return labels[status] || status
}

const getInspectionTypeLabel = (type) => {
  const labels = {
    periodic: 'Periyodik',
    special: 'Özel',
    final: 'Final',
  }
  return labels[type] || type
}

const getSeverityLabel = (severity) => {
  const labels = {
    minor: 'Minör',
    major: 'Majör',
    critical: 'Kritik',
  }
  return labels[severity] || severity
}

const getActionStatusLabel = (status) => {
  const labels = {
    pending: 'Bekliyor',
    in_progress: 'Devam Ediyor',
    completed: 'Tamamlandı',
  }
  return labels[status] || status
}

const formatFileSize = (bytes) => {
  if (!bytes) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
}
</script>
