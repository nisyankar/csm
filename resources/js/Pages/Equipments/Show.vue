<template>
  <AppLayout title="Ekipman Detayı" :full-width="true">
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-yellow-600 via-amber-600 to-orange-600 border-b border-yellow-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="flex-1 min-w-0">
              <div class="flex items-center space-x-4 mb-3">
                <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                  <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                  </svg>
                </div>
                <div>
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">{{ equipment.code }}</h1>
                  <p class="text-yellow-100 text-sm mt-1">{{ equipment.name }}</p>
                </div>
              </div>
              <div class="flex flex-wrap items-center gap-4">
                <div class="bg-white/10 backdrop-blur-sm rounded-lg px-3 py-2">
                  <span class="text-yellow-100 text-sm">Tip:</span>
                  <span class="font-semibold text-white ml-1">{{ equipment.type_label }}</span>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-lg px-3 py-2">
                  <span :class="getStatusClass(equipment.status_color)" class="px-2 py-1 rounded-full text-xs font-semibold">
                    {{ equipment.status_label }}
                  </span>
                </div>
              </div>
            </div>
            <div class="flex-shrink-0">
              <Link :href="route('equipments.edit', equipment.id)" class="inline-flex items-center px-4 py-2 bg-white text-yellow-600 text-sm font-medium rounded-lg hover:bg-yellow-50 shadow-lg transition-all">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Düzenle
              </Link>
            </div>
          </div>
        </div>
      </div>
    </template>

    <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
      <!-- Temel Bilgiler -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Temel Bilgiler</h3>
          <dl class="space-y-3">
            <div>
              <dt class="text-sm text-gray-600">Marka/Model</dt>
              <dd class="text-base font-medium text-gray-900">{{ equipment.brand }} {{ equipment.model }}</dd>
            </div>
            <div>
              <dt class="text-sm text-gray-600">Seri No</dt>
              <dd class="text-base font-medium text-gray-900">{{ equipment.serial_number || '-' }}</dd>
            </div>
            <div>
              <dt class="text-sm text-gray-600">Üretim Yılı</dt>
              <dd class="text-base font-medium text-gray-900">{{ equipment.manufacture_year || '-' }}</dd>
            </div>
            <div>
              <dt class="text-sm text-gray-600">Sahiplik</dt>
              <dd class="text-base font-medium text-gray-900">{{ equipment.ownership_label }}</dd>
            </div>
          </dl>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Konum & Durum</h3>
          <dl class="space-y-3">
            <div>
              <dt class="text-sm text-gray-600">Güncel Proje</dt>
              <dd class="text-base font-medium text-gray-900">{{ equipment.current_project?.name || '-' }}</dd>
            </div>
            <div>
              <dt class="text-sm text-gray-600">Lokasyon</dt>
              <dd class="text-base font-medium text-gray-900">{{ equipment.current_location || '-' }}</dd>
            </div>
            <div>
              <dt class="text-sm text-gray-600">Son Bakım</dt>
              <dd class="text-base font-medium text-gray-900">{{ formatDate(equipment.last_maintenance_date) }}</dd>
            </div>
            <div>
              <dt class="text-sm text-gray-600">Sonraki Bakım</dt>
              <dd :class="['text-base font-medium', equipment.is_maintenance_due ? 'text-red-600' : 'text-gray-900']">
                {{ formatDate(equipment.next_maintenance_date) }}
                <span v-if="equipment.is_maintenance_due" class="ml-2 px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">Zamanı Geldi</span>
              </dd>
            </div>
          </dl>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Maliyet Özeti</h3>
          <dl class="space-y-3">
            <div>
              <dt class="text-sm text-gray-600">Toplam Bakım</dt>
              <dd class="text-base font-medium text-gray-900">{{ formatCurrency(costSummary.total_maintenance_cost) }}</dd>
            </div>
            <div>
              <dt class="text-sm text-gray-600">Toplam Kullanım</dt>
              <dd class="text-base font-medium text-gray-900">{{ formatCurrency(costSummary.total_usage_cost) }}</dd>
            </div>
            <div class="pt-3 border-t border-gray-200">
              <dt class="text-sm text-gray-600">Toplam İşletme</dt>
              <dd class="text-xl font-bold text-yellow-600">{{ formatCurrency(costSummary.total_operating_cost) }}</dd>
            </div>
          </dl>
        </div>
      </div>

      <!-- Son Kullanımlar -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Son Kullanımlar</h3>
            <Link :href="route('equipment-usages.create', { equipment_id: equipment.id })" class="text-sm text-yellow-600 hover:text-yellow-700">
              Yeni Kullanım →
            </Link>
          </div>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tarih</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Proje</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Operatör</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Süre</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Durum</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Maliyet</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="usage in recentUsages" :key="usage.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 text-sm">{{ formatDate(usage.start_date) }}</td>
                <td class="px-6 py-4 text-sm">{{ usage.project?.name }}</td>
                <td class="px-6 py-4 text-sm">{{ usage.operator_name || usage.operator?.first_name }}</td>
                <td class="px-6 py-4 text-sm">{{ usage.duration_days }} gün</td>
                <td class="px-6 py-4 text-sm">
                  <span :class="getStatusClass(usage.status_color)" class="px-2 py-1 rounded-full text-xs">
                    {{ usage.status_label }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm font-medium">{{ formatCurrency(usage.total_cost) }}</td>
              </tr>
              <tr v-if="!recentUsages || recentUsages.length === 0">
                <td colspan="6" class="px-6 py-4 text-sm text-gray-500 text-center">Kullanım kaydı bulunamadı</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Son Bakımlar -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Bakım Geçmişi</h3>
            <Link :href="route('equipment-maintenance.create', { equipment_id: equipment.id })" class="text-sm text-yellow-600 hover:text-yellow-700">
              Yeni Bakım →
            </Link>
          </div>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tarih</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kod</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tip</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Servis</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Durum</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Maliyet</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="maintenance in recentMaintenance" :key="maintenance.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 text-sm">{{ formatDate(maintenance.maintenance_date) }}</td>
                <td class="px-6 py-4 text-sm font-medium">{{ maintenance.maintenance_code }}</td>
                <td class="px-6 py-4 text-sm">{{ maintenance.type_label }}</td>
                <td class="px-6 py-4 text-sm">{{ maintenance.service_provider_label }}</td>
                <td class="px-6 py-4 text-sm">
                  <span :class="getStatusClass(maintenance.status_color)" class="px-2 py-1 rounded-full text-xs">
                    {{ maintenance.status_label }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm font-medium">{{ formatCurrency(maintenance.total_cost) }}</td>
              </tr>
              <tr v-if="!recentMaintenance || recentMaintenance.length === 0">
                <td colspan="6" class="px-6 py-4 text-sm text-gray-500 text-center">Bakım kaydı bulunamadı</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

defineProps({
  equipment: Object,
  recentUsages: Array,
  recentMaintenance: Array,
  costSummary: Object
})

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('tr-TR')
}

const formatCurrency = (amount) => {
  if (!amount) return '₺0,00'
  return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(amount)
}

const getStatusClass = (color) => ({
  'green': 'bg-green-100 text-green-800',
  'blue': 'bg-blue-100 text-blue-800',
  'yellow': 'bg-yellow-100 text-yellow-800',
  'orange': 'bg-orange-100 text-orange-800',
  'red': 'bg-red-100 text-red-800',
  'gray': 'bg-gray-100 text-gray-800'
}[color] || 'bg-gray-100 text-gray-800')
</script>
