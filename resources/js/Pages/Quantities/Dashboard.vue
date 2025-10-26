<template>
  <AppLayout title="Keşif & Metraj Dashboard - SPT İnşaat Takip Sistemi" :full-width="true">
    <!-- Full-width header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-emerald-600 via-teal-700 to-cyan-800 border-b border-emerald-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex items-center">
            <div class="flex-shrink-0 w-16 h-16 bg-white/10 rounded-full flex items-center justify-center backdrop-blur-sm">
              <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
              </svg>
            </div>
            <div class="ml-6">
              <h1 class="text-2xl lg:text-3xl font-bold text-white">
                Keşif & Metraj Yönetimi
              </h1>
              <p class="text-emerald-100 text-sm mt-1">
                Metraj ölçümleri ve keşif karşılaştırma özet bilgileri
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
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                  </svg>
                  <span class="text-xs font-medium text-white">Keşif & Metraj</span>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-emerald-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
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
        <!-- Total Measurements -->
        <StatCard
          title="Toplam Ölçüm"
          :value="summary.total_measurements"
          icon="chart-bar"
          icon-color="teal"
        />

        <!-- Verified Count -->
        <StatCard
          title="Doğrulanmış"
          :value="summary.verified_count"
          icon="check-badge"
          icon-color="green"
          :subtitle="`${summary.pending_verification} doğrulama bekliyor`"
        />

        <!-- Planned Quantity -->
        <StatCard
          title="Toplam Planlanan"
          :value="formatQuantity(summary.total_planned_quantity)"
          icon="clipboard-document-list"
          icon-color="blue"
        />

        <!-- Completed Quantity -->
        <StatCard
          title="Tamamlanan Metraj"
          :value="formatQuantity(summary.total_completed_quantity)"
          icon="chart-pie"
          icon-color="purple"
          :subtitle="`%${summary.completion_percentage} tamamlandı`"
        />
      </div>

      <!-- Quick Actions Widget -->
      <QuickActionWidget
        title="Hızlı İşlemler"
        :actions="quickActions"
      />

      <!-- Charts Row -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Project Progress -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden hover:shadow-md transition-shadow duration-200">
          <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Proje Bazlı İlerleme</h3>
          </div>
          <div class="p-6 space-y-4">
            <div v-for="project in byProject" :key="project.id">
              <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-medium text-gray-700">{{ project.name }}</span>
                <div class="text-right">
                  <span class="text-sm font-semibold text-gray-900">{{ project.completion_percentage }}%</span>
                  <p class="text-xs text-gray-500">{{ project.measurement_count }} ölçüm</p>
                </div>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div
                  class="h-2.5 rounded-full transition-all duration-300"
                  :class="project.completion_percentage >= 100 ? 'bg-green-600' : 'bg-teal-600'"
                  :style="{ width: `${Math.min(project.completion_percentage, 100)}%` }"
                ></div>
              </div>
            </div>
            <div v-if="byProject.length === 0" class="text-center py-8 text-gray-500">
              <p class="text-sm">Henüz metraj kaydı yok</p>
            </div>
          </div>
        </div>

        <!-- Verification Status -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden hover:shadow-md transition-shadow duration-200">
          <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Doğrulama Durumu</h3>
          </div>
          <div class="p-6">
            <div class="space-y-4">
              <!-- Approved -->
              <div>
                <div class="flex justify-between items-center mb-2">
                  <span class="text-sm font-medium text-gray-700">Onaylanmış</span>
                  <span class="text-sm font-semibold text-gray-900">{{ summary.approved_count }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                  <div
                    class="h-2.5 rounded-full bg-green-600 transition-all duration-300"
                    :style="{ width: `${(summary.approved_count / summary.total_measurements) * 100}%` }"
                  ></div>
                </div>
              </div>

              <!-- Verified -->
              <div>
                <div class="flex justify-between items-center mb-2">
                  <span class="text-sm font-medium text-gray-700">Doğrulanmış</span>
                  <span class="text-sm font-semibold text-gray-900">{{ summary.verified_count }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                  <div
                    class="h-2.5 rounded-full bg-blue-600 transition-all duration-300"
                    :style="{ width: `${(summary.verified_count / summary.total_measurements) * 100}%` }"
                  ></div>
                </div>
              </div>

              <!-- Pending -->
              <div>
                <div class="flex justify-between items-center mb-2">
                  <span class="text-sm font-medium text-gray-700">Doğrulama Bekliyor</span>
                  <span class="text-sm font-semibold text-gray-900">{{ summary.pending_verification }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                  <div
                    class="h-2.5 rounded-full bg-yellow-600 transition-all duration-300"
                    :style="{ width: `${(summary.pending_verification / summary.total_measurements) * 100}%` }"
                  ></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Measurements & Pending Verification -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Measurements -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden hover:shadow-md transition-shadow duration-200">
          <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">Son Ölçümler</h3>
            <Link :href="route('quantities.index')" class="text-sm text-teal-600 hover:text-teal-800 font-medium">
              Tümünü Gör →
            </Link>
          </div>
          <div class="p-6 space-y-3">
            <div
              v-for="measurement in recentMeasurements"
              :key="measurement.id"
              class="flex items-center justify-between p-4 bg-gradient-to-r from-gray-50 to-white rounded-lg hover:from-teal-50 hover:to-white transition-all duration-200 border border-gray-100"
            >
              <div class="flex-1">
                <Link :href="route('quantities.show', measurement.id)" class="text-sm font-semibold text-gray-900 hover:text-teal-600 transition-colors">
                  {{ measurement.work_item.name }}
                </Link>
                <p class="text-xs text-gray-600 mt-1">
                  {{ measurement.project.name }}
                  <span v-if="measurement.location !== '-'" class="text-gray-400"> • {{ measurement.location }}</span>
                </p>
                <p class="text-xs text-gray-500">{{ measurement.measurement_date }}</p>
              </div>
              <div class="text-right">
                <p class="text-sm font-bold text-gray-900">{{ formatQuantity(measurement.completed_quantity) }} {{ measurement.unit }}</p>
                <div class="mt-1 flex items-center justify-end space-x-1">
                  <Badge v-if="measurement.is_approved" type="success" size="sm">Onaylı</Badge>
                  <Badge v-else-if="measurement.is_verified" type="info" size="sm">Doğrulandı</Badge>
                  <Badge v-else type="warning" size="sm">Bekliyor</Badge>
                </div>
              </div>
            </div>
            <div v-if="recentMeasurements.length === 0" class="text-center py-8 text-gray-500">
              <p class="text-sm">Henüz metraj kaydı yok</p>
            </div>
          </div>
        </div>

        <!-- Pending Verification -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden hover:shadow-md transition-shadow duration-200">
          <div class="px-6 py-4 bg-gradient-to-r from-yellow-50 to-orange-50 border-b border-yellow-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">Doğrulama Bekleyenler</h3>
            <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-bold rounded-full">
              {{ pendingVerification.length }} kayıt
            </span>
          </div>
          <div class="p-6 space-y-3">
            <div
              v-for="measurement in pendingVerification"
              :key="measurement.id"
              class="flex items-center justify-between p-4 bg-gradient-to-r from-yellow-50 to-white rounded-lg hover:from-yellow-100 hover:to-yellow-50 transition-all duration-200 border border-yellow-200"
            >
              <div class="flex-1">
                <Link :href="route('quantities.show', measurement.id)" class="text-sm font-semibold text-gray-900 hover:text-teal-600 transition-colors">
                  {{ measurement.work_item.name }}
                </Link>
                <p class="text-xs text-gray-600 mt-1">{{ measurement.project.name }}</p>
                <p class="text-xs text-gray-500">
                  <span v-if="measurement.location !== '-'">{{ measurement.location }} • </span>
                  {{ measurement.measurement_date }}
                </p>
              </div>
              <div class="text-right">
                <p class="text-sm font-bold text-gray-900">{{ formatQuantity(measurement.completed_quantity) }} {{ measurement.unit }}</p>
                <Link
                  :href="route('quantities.show', measurement.id)"
                  class="text-xs text-teal-600 hover:text-teal-800 font-medium mt-1 inline-block"
                >
                  İncele →
                </Link>
              </div>
            </div>
            <div v-if="pendingVerification.length === 0" class="text-center py-8 text-gray-500">
              <CheckCircleIcon class="h-12 w-12 mx-auto text-gray-300 mb-2" />
              <p class="text-sm">Doğrulama bekleyen metraj yok</p>
            </div>
          </div>
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
import { CheckCircleIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  summary: {
    type: Object,
    required: true
  },
  byProject: {
    type: Array,
    required: true
  },
  recentMeasurements: {
    type: Array,
    required: true
  },
  pendingVerification: {
    type: Array,
    required: true
  }
})

// Quick Actions Configuration
const quickActions = computed(() => {
  return [
    {
      label: 'Yeni Metraj',
      description: 'Yeni metraj kaydı oluşturun',
      href: route('quantities.create'),
      icon: 'document-plus',
      color: 'bg-teal-50 text-teal-700'
    },
    {
      label: 'Metraj Listesi',
      description: 'Tüm metraj kayıtlarını görüntüleyin',
      href: route('quantities.index'),
      icon: 'chart-bar',
      color: 'bg-blue-50 text-blue-700'
    },
    {
      label: 'Doğrulama Bekleyenler',
      description: 'Doğrulama bekleyen metraj kayıtları',
      href: route('quantities.index', { status: 'pending' }),
      icon: 'clock',
      color: 'bg-yellow-50 text-yellow-700'
    },
    {
      label: 'Onaylanmış Metrajlar',
      description: 'Onaylanmış metraj kayıtları',
      href: route('quantities.index', { status: 'approved' }),
      icon: 'check-badge',
      color: 'bg-green-50 text-green-700'
    }
  ]
})

const formatQuantity = (quantity) => {
  if (quantity === null || quantity === undefined || isNaN(quantity)) {
    return '0'
  }

  const numQuantity = Number(quantity)
  if (isNaN(numQuantity)) {
    return '0'
  }

  return new Intl.NumberFormat('tr-TR', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 2
  }).format(numQuantity)
}
</script>
