<template>
  <AppLayout title="Finansal Dashboard" :full-width="true">
    <!-- Full-width header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-emerald-600 via-green-700 to-teal-800 border-b border-emerald-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex items-center">
            <div class="flex-shrink-0 w-16 h-16 bg-white/10 rounded-full flex items-center justify-center backdrop-blur-sm">
              <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
              </svg>
            </div>
            <div class="ml-6">
              <h1 class="text-2xl lg:text-3xl font-bold text-white">
                Finansal Dashboard
              </h1>
              <p class="text-emerald-100 text-sm mt-1">
                Gelir, gider ve kar/zarar özet bilgileri
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
                    <span class="sr-only">Ana Sayfa</span>
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
                  <span class="text-xs font-medium text-white">Dashboard</span>
                </li>
              </ol>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Main Content -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
      <!-- Project Filter -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <div class="p-4">
          <div class="flex items-center gap-4">
            <label class="text-sm font-medium text-gray-700">Proje:</label>
            <select
              v-model="selectedProjectId"
              @change="filterByProject"
              class="px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
            >
              <option :value="null">Tüm Projeler</option>
              <option v-for="project in projects" :key="project.id" :value="project.id">
                {{ project.name }}
              </option>
            </select>
          </div>
        </div>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Toplam Gelir -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
          <div class="p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Toplam Gelir</p>
                <p class="text-2xl font-bold text-green-600 mt-2">{{ formatCurrency(summary.income) }}</p>
              </div>
              <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" />
                </svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Toplam Gider -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
          <div class="p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Toplam Gider</p>
                <p class="text-2xl font-bold text-red-600 mt-2">{{ formatCurrency(summary.expense) }}</p>
              </div>
              <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6" />
                </svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Net Kar/Zarar -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
          <div class="p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Net Kar/Zarar</p>
                <p class="text-2xl font-bold mt-2" :class="summary.profit >= 0 ? 'text-emerald-600' : 'text-red-600'">
                  {{ formatCurrency(summary.profit) }}
                </p>
              </div>
              <div class="w-12 h-12 rounded-full flex items-center justify-center" :class="summary.profit >= 0 ? 'bg-emerald-100' : 'bg-red-100'">
                <svg class="w-6 h-6" :class="summary.profit >= 0 ? 'text-emerald-600' : 'text-red-600'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Kar Marjı -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
          <div class="p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Kar Marjı</p>
                <p class="text-2xl font-bold text-blue-600 mt-2">%{{ summary.profit_margin }}</p>
              </div>
              <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts Row -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Aylık Gelir/Gider -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200/80 overflow-hidden hover:shadow-md transition-shadow">
          <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Aylık Gelir/Gider Grafiği</h3>
          </div>
          <div class="p-6">
            <div class="space-y-4">
              <div v-for="(data, index) in monthlyData" :key="index">
                <div class="flex justify-between items-center mb-2">
                  <span class="text-sm font-medium text-gray-700">{{ getMonthName(index + 1) }}</span>
                  <div class="text-sm text-gray-600">
                    <span class="text-green-600 font-semibold">{{ formatCurrency(data.income) }}</span>
                    /
                    <span class="text-red-600 font-semibold">{{ formatCurrency(data.expense) }}</span>
                  </div>
                </div>
                <div class="flex gap-1 h-8">
                  <div
                    class="bg-green-500 rounded transition-all"
                    :style="{ width: `${getBarWidth(data.income, data.income + data.expense)}%` }"
                  ></div>
                  <div
                    class="bg-red-500 rounded transition-all"
                    :style="{ width: `${getBarWidth(data.expense, data.income + data.expense)}%` }"
                  ></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Son İşlemler -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200/80 overflow-hidden hover:shadow-md transition-shadow">
          <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">Son İşlemler</h3>
            <Link :href="route('financial.index')" class="text-sm text-emerald-600 hover:text-emerald-800 font-medium">
              Tümünü Gör →
            </Link>
          </div>
          <div class="p-6 space-y-3">
            <div
              v-for="transaction in recentTransactions"
              :key="transaction.id"
              class="flex items-center justify-between p-4 bg-gradient-to-r from-gray-50 to-white rounded-lg hover:from-emerald-50 hover:to-white transition-all border border-gray-100"
            >
              <div class="flex-1">
                <Link :href="route('financial.show', transaction.id)" class="text-sm font-semibold text-gray-900 hover:text-emerald-600 transition-colors">
                  {{ transaction.category_name }}
                </Link>
                <p class="text-xs text-gray-600 mt-1">{{ transaction.project?.name || '-' }}</p>
              </div>
              <div class="text-right">
                <p class="text-sm font-bold" :class="transaction.transaction_type === 'income' ? 'text-green-600' : 'text-red-600'">
                  {{ formatCurrency(transaction.amount) }}
                </p>
                <p class="text-xs text-gray-500 mt-1">{{ formatDate(transaction.transaction_date) }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Pending Payments -->
      <div class="bg-white shadow-sm rounded-xl border border-gray-200/80 overflow-hidden hover:shadow-md transition-shadow">
        <div class="px-6 py-4 bg-gradient-to-r from-yellow-50 to-orange-50 border-b border-yellow-200 flex justify-between items-center">
          <h3 class="text-lg font-semibold text-gray-900">Bekleyen Ödemeler</h3>
          <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-bold rounded-full">
            {{ pendingPayments.length }} kayıt
          </span>
        </div>
        <div class="p-6 space-y-3">
          <div
            v-for="payment in pendingPayments"
            :key="payment.id"
            class="flex items-center justify-between p-4 bg-gradient-to-r from-yellow-50 to-white rounded-lg hover:from-yellow-100 hover:to-yellow-50 transition-all border border-yellow-200"
          >
            <div class="flex-1">
              <Link :href="route('financial.show', payment.id)" class="text-sm font-semibold text-gray-900 hover:text-emerald-600 transition-colors">
                {{ payment.category_name }}
              </Link>
              <p class="text-xs text-gray-600 mt-1">{{ payment.project?.name || '-' }}</p>
              <p class="text-xs text-gray-500">Kalan: {{ formatCurrency(payment.remaining_amount) }}</p>
            </div>
            <div class="text-right">
              <p class="text-sm font-bold text-gray-900">{{ formatCurrency(payment.amount) }}</p>
              <Link
                :href="route('financial.show', payment.id)"
                class="text-xs text-emerald-600 hover:text-emerald-800 font-medium mt-1 inline-block"
              >
                İncele →
              </Link>
            </div>
          </div>
          <div v-if="pendingPayments.length === 0" class="text-center py-8 text-gray-500">
            <svg class="h-12 w-12 mx-auto text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-sm">Bekleyen ödeme yok</p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  summary: {
    type: Object,
    required: true
  },
  monthlyData: {
    type: Array,
    required: true
  },
  recentTransactions: {
    type: Array,
    required: true
  },
  pendingPayments: {
    type: Array,
    required: true
  },
  projects: {
    type: Array,
    required: true
  },
  selectedProjectId: {
    type: [Number, String],
    default: null
  }
})

const selectedProjectId = ref(props.selectedProjectId)

const filterByProject = () => {
  router.get(route('financial.dashboard'), {
    project_id: selectedProjectId.value
  }, {
    preserveState: true,
    preserveScroll: true
  })
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('tr-TR', {
    style: 'currency',
    currency: 'TRY',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount || 0)
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('tr-TR', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const getMonthName = (month) => {
  const months = ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık']
  return months[month - 1]
}

const getBarWidth = (value, total) => {
  if (total === 0) return 0
  return (value / total) * 100
}
</script>
