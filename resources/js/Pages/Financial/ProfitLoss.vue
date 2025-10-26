<template>
  <AppLayout title="Kar/Zarar Raporu" :full-width="true">
    <!-- Full-width header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-emerald-600 via-green-700 to-teal-800 border-b border-emerald-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex items-center">
            <div class="flex-shrink-0 w-16 h-16 bg-white/10 rounded-full flex items-center justify-center backdrop-blur-sm">
              <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0013.5 3v7.5z" />
              </svg>
            </div>
            <div class="ml-6">
              <h1 class="text-2xl lg:text-3xl font-bold text-white">
                Kar/Zarar Raporu
              </h1>
              <p class="text-emerald-100 text-sm mt-1">
                Detaylı gelir, gider ve kar analizi
              </p>
            </div>
          </div>
        </div>

        <!-- Breadcrumb inside header -->
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
                  <span class="text-xs font-medium text-white">Finansal Yönetim</span>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-emerald-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <span class="text-xs font-medium text-white">Kar/Zarar Raporu</span>
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
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Proje</label>
              <select
                v-model="filters.project_id"
                @change="applyFilters"
                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
              >
                <option :value="null">Tüm Projeler</option>
                <option v-for="project in projects" :key="project.id" :value="project.id">
                  {{ project.name }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Yıl</label>
              <select
                v-model="filters.year"
                @change="applyFilters"
                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
              >
                <option v-for="year in yearOptions" :key="year" :value="year">
                  {{ year }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Ay</label>
              <select
                v-model="filters.month"
                @change="applyFilters"
                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
              >
                <option :value="null">Tüm Yıl</option>
                <option :value="1">Ocak</option>
                <option :value="2">Şubat</option>
                <option :value="3">Mart</option>
                <option :value="4">Nisan</option>
                <option :value="5">Mayıs</option>
                <option :value="6">Haziran</option>
                <option :value="7">Temmuz</option>
                <option :value="8">Ağustos</option>
                <option :value="9">Eylül</option>
                <option :value="10">Ekim</option>
                <option :value="11">Kasım</option>
                <option :value="12">Aralık</option>
              </select>
            </div>

            <div class="flex items-end">
              <button
                @click="exportReport"
                class="w-full px-4 py-2.5 bg-emerald-600 text-white rounded-lg font-medium hover:bg-emerald-700 transition-colors"
              >
                <svg class="w-4 h-4 inline-block mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Rapor İndir
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Gelir -->
        <div class="bg-gradient-to-br from-green-50 to-emerald-50 shadow-sm rounded-xl border-2 border-green-200 overflow-hidden">
          <div class="p-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-semibold text-green-700">Toplam Gelir</h3>
              <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" />
                </svg>
              </div>
            </div>
            <p class="text-3xl font-bold text-green-700">{{ formatCurrency(summary.income) }}</p>
          </div>
        </div>

        <!-- Gider -->
        <div class="bg-gradient-to-br from-red-50 to-pink-50 shadow-sm rounded-xl border-2 border-red-200 overflow-hidden">
          <div class="p-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-semibold text-red-700">Toplam Gider</h3>
              <div class="w-10 h-10 bg-red-600 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6" />
                </svg>
              </div>
            </div>
            <p class="text-3xl font-bold text-red-700">{{ formatCurrency(summary.expense) }}</p>
          </div>
        </div>

        <!-- Kar/Zarar -->
        <div class="shadow-sm rounded-xl border-2 overflow-hidden" :class="summary.profit >= 0 ? 'bg-gradient-to-br from-emerald-50 to-teal-50 border-emerald-200' : 'bg-gradient-to-br from-red-50 to-pink-50 border-red-200'">
          <div class="p-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-semibold" :class="summary.profit >= 0 ? 'text-emerald-700' : 'text-red-700'">
                Net {{ summary.profit >= 0 ? 'Kar' : 'Zarar' }}
              </h3>
              <div class="w-10 h-10 rounded-full flex items-center justify-center" :class="summary.profit >= 0 ? 'bg-emerald-600' : 'bg-red-600'">
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
            <p class="text-3xl font-bold" :class="summary.profit >= 0 ? 'text-emerald-700' : 'text-red-700'">
              {{ formatCurrency(summary.profit) }}
            </p>
            <p class="text-sm mt-2" :class="summary.profit >= 0 ? 'text-emerald-600' : 'text-red-600'">
              Kar Marjı: %{{ summary.profit_margin }}
            </p>
          </div>
        </div>
      </div>

      <!-- Category Breakdown -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Gelir Kategorileri -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200/80 overflow-hidden">
          <div class="px-6 py-4 bg-gradient-to-r from-green-50 to-emerald-50 border-b border-green-200">
            <h3 class="text-lg font-semibold text-gray-900">Gelir Kategorileri</h3>
          </div>
          <div class="p-6">
            <div class="space-y-4">
              <div v-for="(category, index) in categoryBreakdown.income_categories" :key="index">
                <div class="flex justify-between items-center mb-2">
                  <span class="text-sm font-medium text-gray-700">{{ category.category_name }}</span>
                  <span class="text-sm font-bold text-green-600">{{ formatCurrency(category.total) }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                  <div
                    class="bg-green-500 h-3 rounded-full transition-all"
                    :style="{ width: `${getPercentage(category.total, summary.income)}%` }"
                  ></div>
                </div>
                <p class="text-xs text-gray-500 mt-1">%{{ getPercentage(category.total, summary.income).toFixed(1) }}</p>
              </div>
              <div v-if="!categoryBreakdown.income_categories || categoryBreakdown.income_categories.length === 0" class="text-center py-8 text-gray-500">
                <p class="text-sm">Gelir kaydı bulunamadı</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Gider Kategorileri -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200/80 overflow-hidden">
          <div class="px-6 py-4 bg-gradient-to-r from-red-50 to-pink-50 border-b border-red-200">
            <h3 class="text-lg font-semibold text-gray-900">Gider Kategorileri</h3>
          </div>
          <div class="p-6">
            <div class="space-y-4">
              <div v-for="(category, index) in categoryBreakdown.expense_categories" :key="index">
                <div class="flex justify-between items-center mb-2">
                  <span class="text-sm font-medium text-gray-700">{{ category.category_name }}</span>
                  <span class="text-sm font-bold text-red-600">{{ formatCurrency(category.total) }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                  <div
                    class="bg-red-500 h-3 rounded-full transition-all"
                    :style="{ width: `${getPercentage(category.total, summary.expense)}%` }"
                  ></div>
                </div>
                <p class="text-xs text-gray-500 mt-1">%{{ getPercentage(category.total, summary.expense).toFixed(1) }}</p>
              </div>
              <div v-if="!categoryBreakdown.expense_categories || categoryBreakdown.expense_categories.length === 0" class="text-center py-8 text-gray-500">
                <p class="text-sm">Gider kaydı bulunamadı</p>
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
import { router, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  summary: {
    type: Object,
    required: true
  },
  categoryBreakdown: {
    type: Object,
    required: true
  },
  projects: {
    type: Array,
    required: true
  },
  filters: {
    type: Object,
    default: () => ({})
  }
})

const filters = ref({
  project_id: props.filters.projectId || null,
  year: props.filters.year || new Date().getFullYear(),
  month: props.filters.month || null
})

const yearOptions = computed(() => {
  const currentYear = new Date().getFullYear()
  const options = []
  for (let i = currentYear; i >= currentYear - 5; i--) {
    options.push(i)
  }
  return options
})

const applyFilters = () => {
  router.get(route('financial.reports.profit-loss'), filters.value, {
    preserveState: true,
    preserveScroll: true
  })
}

const exportReport = () => {
  window.location.href = route('api.v1.pm.financial.transactions.profit-loss', {
    ...filters.value,
    export: 'pdf'
  })
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('tr-TR', {
    style: 'currency',
    currency: 'TRY',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(amount || 0)
}

const getPercentage = (value, total) => {
  if (total === 0) return 0
  return (value / total) * 100
}
</script>