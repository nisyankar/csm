<template>
  <AppLayout title="İş Kazası Detayı" :full-width="true">
    <!-- Full-width header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-red-600 via-orange-600 to-red-700 border-b border-red-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
              </div>
              <div>
                <h1 class="text-2xl lg:text-3xl font-bold text-white">İş Kazası Detayı #{{ incident.id }}</h1>
                <p class="text-red-100 text-sm mt-1">{{ formatDate(incident.incident_date) }} - {{ incident.project?.name }}</p>
              </div>
            </div>
            <div class="flex space-x-2">
              <Link :href="route('safety-incidents.edit', incident.id)" class="px-4 py-2.5 bg-white/10 backdrop-blur-sm text-white border border-white/20 rounded-lg hover:bg-white/20 transition-all">
                Düzenle
              </Link>
              <Link :href="route('safety-incidents.index')" class="px-4 py-2.5 bg-white text-red-600 rounded-lg hover:bg-red-50 transition-all font-medium">
                Geri
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
                  <Link :href="route('dashboard')" class="text-red-100 hover:text-white transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                  </Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-red-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <Link :href="route('safety-incidents.index')" class="text-red-100 hover:text-white text-sm">İş Kazaları</Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-red-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
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
      <!-- Durum Kartları -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <p class="text-sm text-gray-600 mb-2">Durum</p>
          <span :class="getStatusClass(incident.status)" class="inline-block px-3 py-1.5 rounded-full text-sm font-medium">
            {{ getStatusLabel(incident.status) }}
          </span>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <p class="text-sm text-gray-600 mb-2">Önem Derecesi</p>
          <span :class="getSeverityClass(incident.severity)" class="inline-block px-3 py-1.5 rounded-full text-sm font-medium">
            {{ getSeverityLabel(incident.severity) }}
          </span>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <p class="text-sm text-gray-600 mb-2">Olay Türü</p>
          <span :class="getIncidentTypeClass(incident.incident_type)" class="inline-block px-3 py-1.5 rounded-full text-sm font-medium">
            {{ getIncidentTypeLabel(incident.incident_type) }}
          </span>
        </div>
      </div>

      <!-- Detaylar -->
      <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
          <h3 class="text-lg font-medium text-gray-900">Temel Bilgiler</h3>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <p class="text-sm font-medium text-gray-500 mb-1">Proje</p>
              <p class="text-base text-gray-900">{{ incident.project?.name }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-500 mb-1">Konum</p>
              <p class="text-base text-gray-900">{{ incident.location }}</p>
            </div>
            <div v-if="incident.employee">
              <p class="text-sm font-medium text-gray-500 mb-1">Çalışan</p>
              <p class="text-base text-gray-900">{{ incident.employee.first_name }} {{ incident.employee.last_name }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-500 mb-1">Raporlayan</p>
              <p class="text-base text-gray-900">{{ incident.reported_by?.name }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Açıklama -->
      <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
          <h3 class="text-lg font-medium text-gray-900">Olay Açıklaması</h3>
        </div>
        <div class="p-6">
          <p class="text-base text-gray-900 whitespace-pre-wrap leading-relaxed">{{ incident.description }}</p>
        </div>
      </div>

      <!-- Anında Aksiyonlar -->
      <div v-if="incident.immediate_actions" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
          <h3 class="text-lg font-medium text-gray-900">Anında Alınan Aksiyonlar</h3>
        </div>
        <div class="p-6">
          <p class="text-base text-gray-900 whitespace-pre-wrap leading-relaxed">{{ incident.immediate_actions }}</p>
        </div>
      </div>

      <!-- Kök Sebep ve Düzeltici Faaliyetler -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div v-if="incident.root_cause" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Kök Sebep Analizi</h3>
          </div>
          <div class="p-6">
            <p class="text-base text-gray-900 whitespace-pre-wrap leading-relaxed">{{ incident.root_cause }}</p>
          </div>
        </div>

        <div v-if="incident.corrective_actions" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Düzeltici Faaliyetler</h3>
          </div>
          <div class="p-6">
            <p class="text-base text-gray-900 whitespace-pre-wrap leading-relaxed">{{ incident.corrective_actions }}</p>
          </div>
        </div>
      </div>

      <!-- İş Kaybı ve Maliyet -->
      <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
          <h3 class="text-lg font-medium text-gray-900">Ek Bilgiler</h3>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-gray-50 rounded-lg p-4">
              <p class="text-sm font-medium text-gray-500 mb-2">İş Kaybı</p>
              <p class="text-2xl font-bold text-gray-900">{{ incident.days_lost }} <span class="text-sm font-normal text-gray-600">gün</span></p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
              <p class="text-sm font-medium text-gray-500 mb-2">Tıbbi Tedavi</p>
              <p class="text-base font-semibold" :class="incident.medical_treatment_required ? 'text-orange-600' : 'text-green-600'">
                {{ incident.medical_treatment_required ? 'Gerekti' : 'Gerekmedi' }}
              </p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
              <p class="text-sm font-medium text-gray-500 mb-2">İş Durdurma</p>
              <p class="text-base font-semibold" :class="incident.work_stopped ? 'text-red-600' : 'text-green-600'">
                {{ incident.work_stopped ? 'Evet' : 'Hayır' }}
              </p>
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
  incident: Object
})

const formatDate = (date) => new Date(date).toLocaleDateString('tr-TR')

const getStatusLabel = (status) => ({
  'reported': 'Raporlandı',
  'investigating': 'İnceleniyor',
  'resolved': 'Çözüldü',
  'closed': 'Kapatıldı'
}[status])

const getStatusClass = (status) => ({
  'reported': 'bg-blue-100 text-blue-800',
  'investigating': 'bg-yellow-100 text-yellow-800',
  'resolved': 'bg-green-100 text-green-800',
  'closed': 'bg-gray-100 text-gray-800'
}[status])

const getSeverityLabel = (severity) => ({
  'low': 'Düşük',
  'medium': 'Orta',
  'high': 'Yüksek',
  'critical': 'Kritik'
}[severity])

const getSeverityClass = (severity) => ({
  'low': 'bg-green-100 text-green-800',
  'medium': 'bg-yellow-100 text-yellow-800',
  'high': 'bg-orange-100 text-orange-800',
  'critical': 'bg-red-100 text-red-800'
}[severity])

const getIncidentTypeLabel = (type) => ({
  'minor_injury': 'Küçük Yaralanma',
  'major_injury': 'Büyük Yaralanma',
  'near_miss': 'Ramak Kala',
  'property_damage': 'Mal Hasarı',
  'environmental': 'Çevresel',
  'fatal': 'Ölümlü'
}[type])

const getIncidentTypeClass = (type) => ({
  'minor_injury': 'bg-yellow-100 text-yellow-800',
  'major_injury': 'bg-orange-100 text-orange-800',
  'near_miss': 'bg-blue-100 text-blue-800',
  'property_damage': 'bg-purple-100 text-purple-800',
  'environmental': 'bg-green-100 text-green-800',
  'fatal': 'bg-red-100 text-red-800'
}[type])
</script>
