<template>
  <AppLayout
    title="Metraj Aşımı Raporu - SPT İnşaat Puantaj Sistemi"
    :full-width="true"
  >
    <!-- Full-width header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-red-600 via-red-700 to-orange-800 border-b border-red-900/20 w-full">
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
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">Metraj Aşımı Raporu</h1>
                  <p class="text-red-100 text-sm mt-1">Planlanan metrajı aşan hakediş kayıtları</p>
                </div>
              </div>

              <!-- Stats Row -->
              <div class="flex flex-wrap items-center gap-4 lg:gap-6">
                <div class="bg-white/10 backdrop-blur-sm rounded-lg px-3 py-2">
                  <span class="text-red-100 text-sm">Toplam Aşım:</span>
                  <span class="font-semibold text-white ml-1">{{ summary.overrun_count }}</span>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-lg px-3 py-2">
                  <span class="text-red-100 text-sm">Aşım Miktarı:</span>
                  <span class="font-semibold text-white ml-1">{{ formatNumber(summary.total_overrun_amount) }}</span>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-lg px-3 py-2">
                  <span class="text-red-100 text-sm">Toplam Tutar:</span>
                  <span class="font-semibold text-white ml-1">{{ formatCurrency(summary.total_overrun_value) }}</span>
                </div>
              </div>
            </div>

            <!-- Action Button -->
            <div class="flex-shrink-0">
              <Link
                :href="route('progress-payments.index')"
                class="inline-flex items-center px-4 py-2 bg-white text-red-600 text-sm font-medium rounded-lg hover:bg-red-50 shadow-lg hover:shadow-xl transition-all duration-200"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Hakediş Listesi
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
                    class="text-red-100 hover:text-white transition-colors"
                  >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                    <span class="sr-only">Ana Sayfa</span>
                  </Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-red-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                  </svg>
                  <Link
                    :href="route('progress-payments.index')"
                    class="text-red-100 hover:text-white transition-colors text-xs font-medium"
                  >
                    Hakediş Takibi
                  </Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-red-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                  </svg>
                  <span class="text-xs font-medium text-white">Metraj Aşımı Raporu</span>
                </li>
              </ol>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Main Content -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
      <!-- Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Toplam Aşım Sayısı -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
          <div class="p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                  <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                  </svg>
                </div>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Toplam Aşım Kaydı</dt>
                  <dd class="flex items-baseline">
                    <div class="text-2xl font-semibold text-gray-900">{{ summary.overrun_count }}</div>
                    <div class="ml-2 flex items-baseline text-sm font-semibold text-red-600">
                      <svg class="w-3 h-3 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                      </svg>
                      Aşım Var
                    </div>
                  </dd>
                </dl>
              </div>
            </div>
          </div>
          <div class="bg-red-50 px-6 py-3">
            <div class="text-sm">
              <span class="font-medium text-red-700">Dikkat!</span>
              <span class="text-red-600"> Metraj sınırları aşılmış hakediş kayıtları</span>
            </div>
          </div>
        </div>

        <!-- Toplam Aşım Miktarı -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
          <div class="p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                  <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                  </svg>
                </div>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Toplam Aşım Miktarı</dt>
                  <dd class="flex items-baseline">
                    <div class="text-2xl font-semibold text-gray-900">{{ formatNumber(summary.total_overrun_amount) }}</div>
                    <div class="ml-2 flex items-baseline text-sm font-semibold text-orange-600">
                      birim
                    </div>
                  </dd>
                </dl>
              </div>
            </div>
          </div>
          <div class="bg-orange-50 px-6 py-3">
            <div class="text-sm">
              <span class="font-medium text-orange-700">Toplam:</span>
              <span class="text-orange-600"> Planlanan metrajı aşan toplam miktar</span>
            </div>
          </div>
        </div>

        <!-- Toplam Aşım Tutarı -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
          <div class="p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center">
                  <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Toplam Finansal Tutar</dt>
                  <dd class="flex items-baseline">
                    <div class="text-2xl font-semibold text-gray-900">{{ formatCurrency(summary.total_overrun_value) }}</div>
                  </dd>
                </dl>
              </div>
            </div>
          </div>
          <div class="bg-amber-50 px-6 py-3">
            <div class="text-sm">
              <span class="font-medium text-amber-700">Tutar:</span>
              <span class="text-amber-600"> Aşım yapılan kayıtların toplam tutarı</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters Panel -->
      <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
          <h3 class="text-lg font-medium text-gray-900">Arama ve Filtreler</h3>
        </div>

        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-4">
            <!-- Proje Filtresi -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Proje</label>
              <select
                v-model="filterForm.project_id"
                @change="applyFilters"
                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
              >
                <option value="">Tümü</option>
                <option v-for="project in projects" :key="project.id" :value="project.id">
                  {{ project.name }}
                </option>
              </select>
            </div>

            <!-- Taşeron Filtresi -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Taşeron</label>
              <select
                v-model="filterForm.subcontractor_id"
                @change="applyFilters"
                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
              >
                <option value="">Tümü</option>
                <option v-for="subcontractor in subcontractors" :key="subcontractor.id" :value="subcontractor.id">
                  {{ subcontractor.company_name }}
                </option>
              </select>
            </div>

            <!-- İş Kalemi Filtresi -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">İş Kalemi</label>
              <select
                v-model="filterForm.work_item_id"
                @change="applyFilters"
                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
              >
                <option value="">Tümü</option>
                <option v-for="workItem in workItems" :key="workItem.id" :value="workItem.id">
                  {{ workItem.name }}
                </option>
              </select>
            </div>

            <!-- Başlangıç Tarihi -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Başlangıç</label>
              <input
                type="date"
                v-model="filterForm.start_date"
                @change="applyFilters"
                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
              />
            </div>

            <!-- Bitiş Tarihi -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Bitiş</label>
              <input
                type="date"
                v-model="filterForm.end_date"
                @change="applyFilters"
                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
              />
            </div>
          </div>

          <div class="flex items-center justify-between">
            <button
              v-if="hasActiveFilters"
              @click="clearFilters"
              class="text-sm text-red-600 hover:text-red-900 font-medium"
            >
              Filtreleri Temizle
            </button>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proje / Lokasyon</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Taşeron</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İş Kalemi</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hakediş</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aşım</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tutar</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tarih</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="overruns.data.length === 0">
                <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                  <div class="flex flex-col items-center">
                    <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm font-medium">Metraj Aşımı Bulunmamaktadır</p>
                    <p class="text-xs text-gray-400 mt-1">Tüm hakediş kayıtları planlanan metraj limitleri içindedir</p>
                  </div>
                </td>
              </tr>
              <tr v-for="overrun in overruns.data" :key="overrun.id" class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4">
                  <div class="text-sm font-medium text-gray-900">{{ overrun.project_name }}</div>
                  <div class="text-xs text-gray-500">{{ overrun.location }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-900">{{ overrun.subcontractor_name }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-900">{{ overrun.work_item_name }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">
                    {{ formatNumber(overrun.completed_quantity) }} {{ overrun.unit }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    +{{ formatNumber(overrun.overrun_amount) }} {{ overrun.unit }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">
                    {{ formatCurrency(overrun.overrun_value) }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', getStatusColor(overrun.status)]"
                  >
                    {{ overrun.status_label }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ overrun.created_at }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <Link
                    :href="route('progress-payments.show', overrun.id)"
                    class="text-red-600 hover:text-red-900"
                  >
                    Detay
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="overruns.data.length > 0" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
          <div class="flex items-center justify-between">
            <div class="flex-1 flex justify-between sm:hidden">
              <Link
                v-if="overruns.prev_page_url"
                :href="overruns.prev_page_url"
                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
              >
                Önceki
              </Link>
              <Link
                v-if="overruns.next_page_url"
                :href="overruns.next_page_url"
                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
              >
                Sonraki
              </Link>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
              <div>
                <p class="text-sm text-gray-700">
                  <span class="font-medium">{{ overruns.from || 0 }}</span>
                  -
                  <span class="font-medium">{{ overruns.to || 0 }}</span>
                  arası gösteriliyor, toplam
                  <span class="font-medium">{{ overruns.total || 0 }}</span>
                  kayıt
                </p>
              </div>
              <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                  <Link
                    v-if="overruns.prev_page_url"
                    :href="overruns.prev_page_url"
                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                  >
                    <span class="sr-only">Önceki</span>
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                  </Link>
                  <Link
                    v-if="overruns.next_page_url"
                    :href="overruns.next_page_url"
                    class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                  >
                    <span class="sr-only">Sonraki</span>
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                  </Link>
                </nav>
              </div>
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
  overruns: {
    type: Object,
    required: true
  },
  summary: {
    type: Object,
    required: true
  },
  projects: {
    type: Array,
    required: true
  },
  subcontractors: {
    type: Array,
    required: true
  },
  workItems: {
    type: Array,
    required: true
  },
  filters: {
    type: Object,
    default: () => ({})
  }
})

const filterForm = ref({
  project_id: props.filters.project_id || '',
  subcontractor_id: props.filters.subcontractor_id || '',
  work_item_id: props.filters.work_item_id || '',
  start_date: props.filters.start_date || '',
  end_date: props.filters.end_date || ''
})

const hasActiveFilters = computed(() => {
  return Object.values(filterForm.value).some(value => value !== '')
})

const applyFilters = () => {
  router.get(route('progress-payments.quantity-overrun-report'), filterForm.value, {
    preserveState: true,
    preserveScroll: true
  })
}

const clearFilters = () => {
  filterForm.value = {
    project_id: '',
    subcontractor_id: '',
    work_item_id: '',
    start_date: '',
    end_date: ''
  }
  applyFilters()
}

const formatCurrency = (value) => {
  return new Intl.NumberFormat('tr-TR', {
    style: 'currency',
    currency: 'TRY',
    minimumFractionDigits: 2
  }).format(value)
}

const formatNumber = (value) => {
  return new Intl.NumberFormat('tr-TR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(value)
}

const getStatusColor = (status) => {
  const colors = {
    'planned': 'bg-gray-100 text-gray-800',
    'in_progress': 'bg-blue-100 text-blue-800',
    'completed': 'bg-green-100 text-green-800',
    'approved': 'bg-purple-100 text-purple-800',
    'paid': 'bg-teal-100 text-teal-800'
  }
  return colors[status] || 'bg-gray-100 text-gray-800'
}
</script>
