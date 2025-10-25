<template>
  <AppLayout title="Hakediş Dashboard - SPT İnşaat Takip Sistemi" :full-width="true">
    <!-- Full-width header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-800 border-b border-blue-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex items-center">
            <div class="flex-shrink-0 w-16 h-16 bg-white/10 rounded-full flex items-center justify-center backdrop-blur-sm">
              <DocumentTextIcon class="h-8 w-8 text-white" />
            </div>
            <div class="ml-6">
              <h1 class="text-2xl lg:text-3xl font-bold text-white">
                Hakediş Takibi Dashboard
              </h1>
              <p class="text-blue-100 text-sm mt-1">
                Genel hakediş ve ilerleme durumu özet bilgileri
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
                  <Link :href="route('dashboard')" class="text-blue-100 hover:text-white transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                    <span class="sr-only">Ana Sayfa</span>
                  </Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-blue-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                  </svg>
                  <span class="text-xs font-medium text-white">Hakediş Takibi</span>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-blue-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
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
      <!-- Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Progress Payments -->
        <StatCard
          title="Toplam Hakediş"
          :value="summary.total_payments"
          icon="document-text"
          icon-color="blue"
        />

        <!-- Completed Payments -->
        <StatCard
          title="Tamamlanan"
          :value="summary.completed_payments"
          icon="check-circle"
          icon-color="green"
          :subtitle="`%${completionRate} tamamlandı`"
        />

        <!-- Total Amount -->
        <StatCard
          title="Toplam Tutar"
          :value="formatCurrency(summary.total_amount)"
          icon="currency-dollar"
          icon-color="purple"
        />

        <!-- Paid Amount -->
        <StatCard
          title="Ödenen Tutar"
          :value="formatCurrency(summary.paid_amount)"
          icon="banknotes"
          icon-color="yellow"
          :subtitle="`${formatCurrency(summary.pending_amount)} bekliyor`"
        />
      </div>

      <!-- Quick Actions Widget -->
      <QuickActionWidget
        title="Hızlı İşlemler"
        :actions="quickActions"
      />

      <!-- Charts Row -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Status Distribution -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden hover:shadow-md transition-shadow duration-200">
          <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Durum Dağılımı</h3>
          </div>
          <div class="p-6 space-y-4">
            <div v-for="(count, status) in summary.by_status" :key="status">
              <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-medium text-gray-700">{{ getStatusLabel(status) }}</span>
                <span class="text-sm font-semibold text-gray-900">{{ count }}</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div
                  class="h-2.5 rounded-full transition-all duration-300"
                  :class="getStatusColor(status)"
                  :style="{ width: `${(count / summary.total_payments) * 100}%` }"
                ></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Project Progress -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden hover:shadow-md transition-shadow duration-200">
          <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Proje Bazlı İlerleme</h3>
          </div>
          <div class="p-6 space-y-4">
            <div v-for="project in summary.by_project" :key="project.id">
              <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-medium text-gray-700">{{ project.name }}</span>
                <span class="text-sm font-semibold text-gray-900">{{ project.progress }}%</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div
                  class="h-2.5 rounded-full transition-all duration-300"
                  :class="project.progress >= 100 ? 'bg-green-600' : 'bg-blue-600'"
                  :style="{ width: `${Math.min(project.progress, 100)}%` }"
                ></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Payments & Pending Approvals -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Payments -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden hover:shadow-md transition-shadow duration-200">
          <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">Son Hakediş Kayıtları</h3>
            <Link :href="route('progress-payments.index')" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
              Tümünü Gör →
            </Link>
          </div>
          <div class="p-6 space-y-3">
            <div
              v-for="payment in recentPayments"
              :key="payment.id"
              class="flex items-center justify-between p-4 bg-gradient-to-r from-gray-50 to-white rounded-lg hover:from-blue-50 hover:to-white transition-all duration-200 border border-gray-100"
            >
              <div class="flex-1">
                <Link :href="route('progress-payments.show', payment.id)" class="text-sm font-semibold text-gray-900 hover:text-blue-600 transition-colors">
                  Hakediş #{{ payment.id }}
                </Link>
                <p class="text-xs text-gray-600 mt-1">{{ payment.project.name }} - {{ payment.subcontractor.company_name }}</p>
              </div>
              <div class="text-right">
                <p class="text-sm font-bold text-gray-900">{{ formatCurrency(payment.total_amount) }}</p>
                <Badge :type="getStatusBadgeType(payment.status)" size="sm" class="mt-1">
                  {{ payment.status_label }}
                </Badge>
              </div>
            </div>
          </div>
        </div>

        <!-- Pending Approvals -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden hover:shadow-md transition-shadow duration-200">
          <div class="px-6 py-4 bg-gradient-to-r from-yellow-50 to-orange-50 border-b border-yellow-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">Onay Bekleyenler</h3>
            <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-bold rounded-full">
              {{ pendingApprovals.length }} kayıt
            </span>
          </div>
          <div class="p-6 space-y-3">
            <div
              v-for="payment in pendingApprovals"
              :key="payment.id"
              class="flex items-center justify-between p-4 bg-gradient-to-r from-yellow-50 to-white rounded-lg hover:from-yellow-100 hover:to-yellow-50 transition-all duration-200 border border-yellow-200"
            >
              <div class="flex-1">
                <Link :href="route('progress-payments.show', payment.id)" class="text-sm font-semibold text-gray-900 hover:text-blue-600 transition-colors">
                  Hakediş #{{ payment.id }}
                </Link>
                <p class="text-xs text-gray-600 mt-1">{{ payment.project.name }}</p>
                <p class="text-xs text-gray-500">{{ payment.completion_percentage }}% tamamlandı</p>
              </div>
              <div class="text-right">
                <p class="text-sm font-bold text-gray-900">{{ formatCurrency(payment.total_amount) }}</p>
                <Link
                  :href="route('progress-payments.show', payment.id)"
                  class="text-xs text-blue-600 hover:text-blue-800 font-medium mt-1 inline-block"
                >
                  İncele →
                </Link>
              </div>
            </div>
            <div v-if="pendingApprovals.length === 0" class="text-center py-8 text-gray-500">
              <CheckCircleIcon class="h-12 w-12 mx-auto text-gray-300 mb-2" />
              <p class="text-sm">Onay bekleyen hakediş yok</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Subcontractor Performance -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden hover:shadow-md transition-shadow duration-200">
        <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900">Taşeron Performansı</h3>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Taşeron</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Toplam Hakediş</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tutar</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ortalama İlerleme</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="subcontractor in summary.by_subcontractor" :key="subcontractor.id" class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-semibold text-gray-900">{{ subcontractor.company_name }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ subcontractor.payment_count }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                  {{ formatCurrency(subcontractor.total_amount) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="w-24 bg-gray-200 rounded-full h-2.5 mr-2">
                      <div
                        class="h-2.5 rounded-full transition-all duration-300"
                        :class="subcontractor.avg_progress >= 100 ? 'bg-green-600' : 'bg-blue-600'"
                        :style="{ width: `${Math.min(subcontractor.avg_progress, 100)}%` }"
                      ></div>
                    </div>
                    <span class="text-sm font-medium text-gray-600">{{ subcontractor.avg_progress }}%</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <Badge :type="subcontractor.avg_progress >= 100 ? 'success' : 'info'" size="sm">
                    {{ subcontractor.avg_progress >= 100 ? 'Tamamlandı' : 'Devam Ediyor' }}
                  </Badge>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Badge from '@/Components/UI/Badge.vue'
import StatCard from '@/Components/Widgets/StatCard.vue'
import QuickActionWidget from '@/Components/Widgets/QuickActionWidget.vue'
import {
  DocumentTextIcon,
  CheckCircleIcon,
  CurrencyDollarIcon,
  BanknotesIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  summary: {
    type: Object,
    required: true
  },
  recentPayments: {
    type: Array,
    required: true
  },
  pendingApprovals: {
    type: Array,
    required: true
  }
})

// Debug: Log summary data
console.log('Progress Payments Dashboard - Summary:', props.summary)
console.log('Total Amount:', props.summary.total_amount, 'Type:', typeof props.summary.total_amount)
console.log('Paid Amount:', props.summary.paid_amount, 'Type:', typeof props.summary.paid_amount)

const completionRate = computed(() => {
  if (props.summary.total_payments === 0) return 0
  return Math.round((props.summary.completed_payments / props.summary.total_payments) * 100)
})

// Quick Actions Configuration
const quickActions = computed(() => {
  return [
    {
      label: 'Yeni Hakediş',
      description: 'Yeni hakediş kaydı oluşturun',
      href: route('progress-payments.create'),
      icon: 'document-plus',
      color: 'bg-blue-50 text-blue-700'
    },
    {
      label: 'Hakediş Listesi',
      description: 'Tüm hakediş kayıtlarını görüntüleyin',
      href: route('progress-payments.index'),
      icon: 'document-text',
      color: 'bg-green-50 text-green-700'
    },
    {
      label: 'Onay Bekleyenler',
      description: 'Onay bekleyen hakediş kayıtları',
      href: route('progress-payments.index', { filter: 'completed' }),
      icon: 'clock',
      color: 'bg-yellow-50 text-yellow-700'
    },
    {
      label: 'Ödeme Bekleyenler',
      description: 'Ödeme bekleyen hakediş kayıtları',
      href: route('progress-payments.index', { filter: 'approved' }),
      icon: 'banknotes',
      color: 'bg-purple-50 text-purple-700'
    }
  ]
})

const formatCurrency = (amount) => {
  console.log('formatCurrency called with:', amount, 'Type:', typeof amount)

  // Handle null, undefined, NaN
  if (amount === null || amount === undefined || isNaN(amount)) {
    console.warn('Invalid amount for currency formatting:', amount)
    return '₺0'
  }

  const numAmount = Number(amount)
  if (isNaN(numAmount)) {
    console.warn('Could not convert to number:', amount)
    return '₺0'
  }

  return new Intl.NumberFormat('tr-TR', {
    style: 'currency',
    currency: 'TRY',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(numAmount)
}

const getStatusLabel = (status) => {
  const labels = {
    planned: 'Planlandı',
    in_progress: 'Devam Ediyor',
    completed: 'Tamamlandı',
    approved: 'Onaylandı',
    paid: 'Ödendi'
  }
  return labels[status] || status
}

const getStatusColor = (status) => {
  const colors = {
    planned: 'bg-gray-600',
    in_progress: 'bg-blue-600',
    completed: 'bg-purple-600',
    approved: 'bg-green-600',
    paid: 'bg-emerald-600'
  }
  return colors[status] || 'bg-gray-600'
}

const getStatusBadgeType = (status) => {
  const types = {
    planned: 'secondary',
    in_progress: 'info',
    completed: 'primary',
    approved: 'success',
    paid: 'success'
  }
  return types[status] || 'secondary'
}
</script>