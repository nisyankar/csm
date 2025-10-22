<template>
  <AppLayout
    title="Günlük Raporlar - SPT İnşaat Puantaj Sistemi"
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
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">Günlük Raporlar</h1>
                  <p class="text-green-100 text-sm mt-1">Günlük şantiye raporlarını görüntüleyin ve yönetin</p>
                </div>
              </div>

              <!-- Stats Row -->
              <div class="flex flex-wrap items-center gap-4 lg:gap-6">
                <div class="bg-white/10 backdrop-blur-sm rounded-lg px-3 py-2">
                  <span class="text-green-100 text-sm">Toplam:</span>
                  <span class="font-semibold text-white ml-1">{{ reportsData.total || 0 }}</span>
                </div>
                <div class="flex items-center space-x-4 text-sm">
                  <span class="flex items-center text-green-100">
                    <div class="w-2 h-2 bg-yellow-400 rounded-full mr-2"></div>
                    Gönderildi: <span class="text-white font-medium ml-1">{{ getStatusCount('submitted') }}</span>
                  </span>
                  <span class="flex items-center text-green-100">
                    <div class="w-2 h-2 bg-green-400 rounded-full mr-2"></div>
                    Onaylı: <span class="text-white font-medium ml-1">{{ getStatusCount('approved') }}</span>
                  </span>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex-shrink-0">
              <Link
                :href="route('daily-reports.create')"
                class="inline-flex items-center px-4 py-2 bg-white text-green-600 text-sm font-medium rounded-lg hover:bg-green-50 shadow-lg hover:shadow-xl transition-all duration-200"
              >
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Yeni Rapor
              </Link>
            </div>
          </div>
        </div>

        <!-- Breadcrumb inside header -->
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
                  <span class="text-xs font-medium text-white">Günlük Raporlar</span>
                </li>
              </ol>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Main Content Container -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
      <!-- Filters Panel -->
      <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
          <h3 class="text-lg font-medium text-gray-900">Arama ve Filtreler</h3>
        </div>

        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Project Filter -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Proje</label>
              <select
                v-model="filters.project_id"
                @change="applyFilters"
                class="block w-full px-3 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
              >
                <option value="">Tümü</option>
                <option v-for="project in projects" :key="project.id" :value="project.id">
                  {{ project.name }}
                </option>
              </select>
            </div>

            <!-- Status Filter -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Durum</label>
              <select
                v-model="filters.status"
                @change="applyFilters"
                class="block w-full px-3 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
              >
                <option value="">Tümü</option>
                <option value="draft">Taslak</option>
                <option value="submitted">Gönderildi</option>
                <option value="approved">Onaylandı</option>
                <option value="rejected">Reddedildi</option>
              </select>
            </div>

            <!-- Start Date Filter -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Başlangıç Tarihi</label>
              <input
                v-model="filters.start_date"
                @change="applyFilters"
                type="date"
                class="block w-full px-3 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
              />
            </div>

            <!-- End Date Filter -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Bitiş Tarihi</label>
              <input
                v-model="filters.end_date"
                @change="applyFilters"
                type="date"
                class="block w-full px-3 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Data Table Panel -->
      <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
        <!-- Table Controls -->
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
              <span class="font-medium">{{ reportsData.from || 0 }}-{{ reportsData.to || 0 }}</span>
              / {{ reportsData.total || 0 }} sonuç
            </div>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="p-8">
          <div class="animate-pulse space-y-4">
            <div v-for="i in 5" :key="i" class="flex items-center space-x-4">
              <div class="flex-1 space-y-2">
                <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                <div class="h-3 bg-gray-200 rounded w-1/2"></div>
              </div>
              <div class="w-20 h-8 bg-gray-200 rounded"></div>
            </div>
          </div>
        </div>

        <!-- Table -->
        <div v-else class="w-full overflow-x-auto">
          <table class="w-full min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Tarih
                </th>
                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Proje
                </th>
                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Rapor Eden
                </th>
                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Hava Durumu
                </th>
                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  İşçi Sayısı
                </th>
                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Durum
                </th>
                <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  İşlemler
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="report in reportsList" :key="report.id" class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap">
                  <Link
                    :href="route('daily-reports.show', report.id)"
                    class="text-green-600 hover:text-green-900 font-medium"
                  >
                    {{ formatDate(report.report_date) }}
                  </Link>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ report.project?.name || '-' }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ report.reporter?.name || '-' }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <span class="text-sm text-gray-900">{{ getWeatherDisplay(report.weather_condition) }}</span>
                    <span v-if="report.temperature" class="ml-2 text-xs text-gray-500">
                      {{ report.temperature }}°C
                    </span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ report.total_workers || 0 }}</div>
                  <div class="text-xs text-gray-500">
                    İç: {{ report.internal_workers || 0 }} / Taşeron: {{ report.subcontractor_workers || 0 }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="getStatusClass(report.approval_status)"
                    class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                  >
                    {{ getStatusText(report.approval_status) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end space-x-2">
                    <Link
                      :href="route('daily-reports.show', report.id)"
                      class="text-green-600 hover:text-green-900"
                    >
                      Görüntüle
                    </Link>
                    <Link
                      v-if="report.approval_status === 'draft' || report.approval_status === 'rejected'"
                      :href="route('daily-reports.edit', report.id)"
                      class="text-indigo-600 hover:text-indigo-900"
                    >
                      Düzenle
                    </Link>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Empty State -->
          <div v-if="reportsList.length === 0" class="text-center py-16">
            <svg
              class="mx-auto h-16 w-16 text-gray-400 mb-4"
              fill="none"
              viewBox="0 0 24 24"
              stroke-width="1.5"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z"
              />
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Rapor bulunamadı</h3>
            <p class="text-gray-500 mb-6">Yeni bir günlük rapor oluşturarak başlayın.</p>
            <Link
              :href="route('daily-reports.create')"
              class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md"
            >
              <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
              </svg>
              İlk Raporu Oluştur
            </Link>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="reportsList.length > 0" class="bg-gray-50 px-6 py-4 border-t border-gray-200">
          <Pagination
            :pagination="reportsData"
            @page-changed="changePage"
          />
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import { format } from 'date-fns'
import { tr } from 'date-fns/locale'
import AppLayout from '@/Layouts/AppLayout.vue'
import Pagination from '@/Components/UI/Pagination.vue'

// Props
const props = defineProps({
  reports: {
    type: Object,
    default: () => ({})
  },
  projects: {
    type: Array,
    default: () => []
  }
})

// State
const loading = ref(false)

// Filter state
const filters = reactive({
  project_id: '',
  status: '',
  start_date: '',
  end_date: ''
})

// Computed
const reportsData = computed(() => {
  if (props.reports && typeof props.reports === 'object' && props.reports.data) {
    return props.reports
  }
  return {
    data: [],
    total: 0,
    per_page: 20,
    current_page: 1,
    last_page: 1,
    from: 0,
    to: 0
  }
})

const reportsList = computed(() => {
  return reportsData.value.data || []
})

// Methods
const applyFilters = () => {
  const params = { ...filters }

  // Remove empty values
  Object.keys(params).forEach(key => {
    if (params[key] === '' || params[key] === null) {
      delete params[key]
    }
  })

  loading.value = true
  router.get(route('daily-reports.index'), params, {
    preserveState: true,
    preserveScroll: true,
    onFinish: () => {
      loading.value = false
    }
  })
}

const changePage = (page) => {
  const params = {
    ...filters,
    page: page
  }

  router.get(route('daily-reports.index'), params, {
    preserveState: true,
    preserveScroll: true
  })
}

const getStatusCount = (status) => {
  if (!reportsList.value || !Array.isArray(reportsList.value)) {
    return 0
  }
  return reportsList.value.filter(r => r.approval_status === status).length
}

const getStatusClass = (status) => {
  const classes = {
    draft: 'bg-gray-100 text-gray-800 border border-gray-200',
    submitted: 'bg-yellow-100 text-yellow-800 border border-yellow-200',
    approved: 'bg-green-100 text-green-800 border border-green-200',
    rejected: 'bg-red-100 text-red-800 border border-red-200'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
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
    return format(new Date(date), 'dd MMM yyyy', { locale: tr })
  } catch (error) {
    return date
  }
}

// Lifecycle
onMounted(() => {
  console.log('DailyReports Index mounted')
  console.log('Reports data:', reportsData.value)
})
</script>

<style scoped>
.w-full {
  width: 100% !important;
}

.overflow-x-auto::-webkit-scrollbar {
  height: 6px;
}

.overflow-x-auto::-webkit-scrollbar-track {
  background: #f1f5f9;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>
