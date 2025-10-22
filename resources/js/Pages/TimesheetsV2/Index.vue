<template>
  <AppLayout title="Yeni Puantaj Sistemi (v2)" :full-width="true">
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-blue-600 via-blue-700 to-blue-800 border-b border-blue-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="flex-1 min-w-0">
              <div class="flex items-center space-x-4 mb-3">
                <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                  <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div>
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">Yeni Puantaj Sistemi</h1>
                  <p class="text-blue-100 text-sm mt-1">Haftalık hesaplama ve devir sistemi</p>
                </div>
              </div>
            </div>
            <div class="flex-shrink-0">
              <Link :href="route('timesheets-v2.create')" class="inline-flex items-center px-4 py-2 bg-white text-blue-600 text-sm font-medium rounded-lg hover:bg-blue-50 shadow-lg">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Yeni Kayıt
              </Link>
            </div>
          </div>
        </div>
      </div>
    </template>

    <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
      <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tarih</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Çalışan</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Proje</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vardiya</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Saat</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">İşlemler</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="timesheet in timesheets.data" :key="timesheet.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm">{{ formatDate(timesheet.work_date) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">{{ timesheet.employee?.name }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">{{ timesheet.project?.name }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">{{ timesheet.shift?.name }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ timesheet.hours_worked }}h</td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-2">
                <Link :href="route('timesheets-v2.edit', timesheet.id)" class="text-blue-600 hover:text-blue-900">Düzenle</Link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link } from '@inertiajs/vue3'

defineProps({
  timesheets: Object
})

const formatDate = (date) => new Date(date).toLocaleDateString('tr-TR')
</script>