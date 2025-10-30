<template>
  <AppLayout title="İş Kazaları ve Olaylar" :full-width="true">
    <!-- Full-width header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-red-600 via-orange-600 to-red-700 border-b border-red-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <!-- Header Info -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center space-x-4 mb-3">
                <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                  <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                  </svg>
                </div>
                <div>
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">İş Kazaları ve Olaylar</h1>
                  <p class="text-red-100 text-sm mt-1">İş kazaları, ramak kala olaylar ve güvenlik raporlaması</p>
                </div>
              </div>

              <!-- Stats Row -->
              <div class="flex flex-wrap items-center gap-4 lg:gap-6">
                <div class="bg-white/10 backdrop-blur-sm rounded-lg px-3 py-2">
                  <span class="text-red-100 text-sm">Toplam Olay:</span>
                  <span class="font-semibold text-white ml-1">{{ incidents?.total || 0 }}</span>
                </div>
                <div class="flex items-center space-x-4 text-sm">
                  <span class="flex items-center text-red-100">
                    <div class="w-2 h-2 bg-blue-400 rounded-full mr-2"></div>
                    Raporlandı: <span class="text-white font-medium ml-1">{{ countByStatus('reported') }}</span>
                  </span>
                  <span class="flex items-center text-red-100">
                    <div class="w-2 h-2 bg-yellow-400 rounded-full mr-2"></div>
                    İnceleniyor: <span class="text-white font-medium ml-1">{{ countByStatus('investigating') }}</span>
                  </span>
                  <span class="flex items-center text-red-100">
                    <div class="w-2 h-2 bg-green-400 rounded-full mr-2"></div>
                    Çözüldü: <span class="text-white font-medium ml-1">{{ countByStatus('resolved') }}</span>
                  </span>
                </div>
              </div>
            </div>

            <!-- Action Button -->
            <div class="flex-shrink-0">
              <Link
                :href="route('safety-incidents.create')"
                class="inline-flex items-center px-4 py-2 bg-white text-red-600 text-sm font-medium rounded-lg hover:bg-red-50 shadow-lg hover:shadow-xl transition-all duration-200"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Yeni Olay Raporu
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
                  <span class="text-white font-medium text-sm">İş Kazaları</span>
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
          <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Proje</label>
              <select
                v-model="filters.project_id"
                @change="applyFilters"
                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all"
              >
                <option :value="null">Tüm Projeler</option>
                <option v-for="project in projects" :key="project.id" :value="project.id">
                  {{ project.name }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Olay Türü</label>
              <select
                v-model="filters.incident_type"
                @change="applyFilters"
                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all"
              >
                <option :value="null">Tüm Türler</option>
                <option value="minor_injury">Küçük Yaralanma</option>
                <option value="major_injury">Büyük Yaralanma</option>
                <option value="near_miss">Ramak Kala</option>
                <option value="property_damage">Mal Hasarı</option>
                <option value="environmental">Çevresel Olay</option>
                <option value="fatal">Ölümlü Kaza</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Önem Derecesi</label>
              <select
                v-model="filters.severity"
                @change="applyFilters"
                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all"
              >
                <option :value="null">Tüm Dereceler</option>
                <option value="low">Düşük</option>
                <option value="medium">Orta</option>
                <option value="high">Yüksek</option>
                <option value="critical">Kritik</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Durum</label>
              <select
                v-model="filters.status"
                @change="applyFilters"
                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all"
              >
                <option :value="null">Tüm Durumlar</option>
                <option value="reported">Raporlandı</option>
                <option value="investigating">İnceleniyor</option>
                <option value="resolved">Çözüldü</option>
                <option value="closed">Kapatıldı</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Arama</label>
              <input
                v-model="filters.search"
                @input="applyFilters"
                type="text"
                placeholder="Konum, açıklama ara..."
                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Incidents Table -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tarih</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proje</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Konum</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Olay Türü</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Önem</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="incident in incidents.data" :key="incident.id" class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatDate(incident.incident_date) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ incident.project?.name }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">
                  {{ incident.location }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <span :class="getIncidentTypeClass(incident.incident_type)" class="px-2 py-1 rounded-full text-xs font-medium">
                    {{ getIncidentTypeLabel(incident.incident_type) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <span :class="getSeverityClass(incident.severity)" class="px-2 py-1 rounded-full text-xs font-medium">
                    {{ getSeverityLabel(incident.severity) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <span :class="getStatusClass(incident.status)" class="px-2 py-1 rounded-full text-xs font-medium">
                    {{ getStatusLabel(incident.status) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                  <Link :href="route('safety-incidents.show', incident.id)" class="text-blue-600 hover:text-blue-900">Detay</Link>
                  <Link :href="route('safety-incidents.edit', incident.id)" class="text-indigo-600 hover:text-indigo-900">Düzenle</Link>
                </td>
              </tr>
              <tr v-if="incidents.data.length === 0">
                <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500">
                  Kayıt bulunamadı
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="incidents.data && incidents.data.length > 0" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
              Toplam <span class="font-medium">{{ incidents.total }}</span> kayıttan
              <span class="font-medium">{{ incidents.from }}</span> -
              <span class="font-medium">{{ incidents.to }}</span> arası gösteriliyor
            </div>
            <div class="flex space-x-2">
              <component
                v-for="link in incidents.links"
                :key="link.label"
                :is="link.url ? Link : 'span'"
                :href="link.url"
                :class="[
                  'px-3 py-2 text-sm rounded-lg transition-colors',
                  link.active
                    ? 'bg-red-600 text-white'
                    : link.url
                    ? 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50'
                    : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                ]"
                v-html="link.label"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  incidents: Object,
  projects: Array,
  filters: Object
})

const filters = ref({
  project_id: props.filters?.project_id || null,
  incident_type: props.filters?.incident_type || null,
  severity: props.filters?.severity || null,
  status: props.filters?.status || null,
  search: props.filters?.search || ''
})

const applyFilters = () => {
  router.get(route('safety-incidents.index'), filters.value, {
    preserveState: true,
    preserveScroll: true
  })
}

const countByStatus = (status) => {
  return props.incidents.data.filter(i => i.status === status).length
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('tr-TR')
}

const getIncidentTypeLabel = (type) => {
  const labels = {
    minor_injury: 'Küçük Yaralanma',
    major_injury: 'Büyük Yaralanma',
    near_miss: 'Ramak Kala',
    property_damage: 'Mal Hasarı',
    environmental: 'Çevresel',
    fatal: 'Ölümlü'
  }
  return labels[type] || type
}

const getIncidentTypeClass = (type) => {
  const classes = {
    minor_injury: 'bg-yellow-100 text-yellow-800',
    major_injury: 'bg-orange-100 text-orange-800',
    near_miss: 'bg-blue-100 text-blue-800',
    property_damage: 'bg-purple-100 text-purple-800',
    environmental: 'bg-green-100 text-green-800',
    fatal: 'bg-red-100 text-red-800'
  }
  return classes[type] || 'bg-gray-100 text-gray-800'
}

const getSeverityLabel = (severity) => {
  const labels = {
    low: 'Düşük',
    medium: 'Orta',
    high: 'Yüksek',
    critical: 'Kritik'
  }
  return labels[severity] || severity
}

const getSeverityClass = (severity) => {
  const classes = {
    low: 'bg-green-100 text-green-800',
    medium: 'bg-yellow-100 text-yellow-800',
    high: 'bg-orange-100 text-orange-800',
    critical: 'bg-red-100 text-red-800'
  }
  return classes[severity] || 'bg-gray-100 text-gray-800'
}

const getStatusLabel = (status) => {
  const labels = {
    reported: 'Raporlandı',
    investigating: 'İnceleniyor',
    resolved: 'Çözüldü',
    closed: 'Kapatıldı'
  }
  return labels[status] || status
}

const getStatusClass = (status) => {
  const classes = {
    reported: 'bg-blue-100 text-blue-800',
    investigating: 'bg-yellow-100 text-yellow-800',
    resolved: 'bg-green-100 text-green-800',
    closed: 'bg-gray-100 text-gray-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}
</script>
