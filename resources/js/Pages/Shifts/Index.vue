<template>
  <AppLayout title="Vardiya Yönetimi" :full-width="true">
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-indigo-600 via-indigo-700 to-indigo-800 border-b border-indigo-900/20 w-full">
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
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">Vardiya Yönetimi</h1>
                  <p class="text-indigo-100 text-sm mt-1">Vardiya tanımlarını yönetin</p>
                </div>
              </div>
              <div class="flex flex-wrap items-center gap-4 lg:gap-6">
                <div class="bg-white/10 backdrop-blur-sm rounded-lg px-3 py-2">
                  <span class="text-indigo-100 text-sm">Toplam Vardiya:</span>
                  <span class="font-semibold text-white ml-1">{{ shifts.total || 0 }}</span>
                </div>
              </div>
            </div>
            <div class="flex-shrink-0">
              <Link :href="route('shifts.create')" class="inline-flex items-center px-4 py-2 bg-white text-indigo-600 text-sm font-medium rounded-lg hover:bg-indigo-50 shadow-lg hover:shadow-xl transition-all duration-200">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Yeni Vardiya
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
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kod</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vardiya Adı</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tip</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Günlük Saat</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">FM Çarpanı</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="shift in shifts.data" :key="shift.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ shift.code }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ shift.name }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ shift.shift_type }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ shift.daily_hours }}h</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ shift.overtime_multiplier }}x</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="[shift.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800', 'px-2 py-1 text-xs font-medium rounded-full']">
                  {{ shift.is_active ? 'Aktif' : 'Pasif' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                <Link :href="route('shifts.edit', shift.id)" class="text-indigo-600 hover:text-indigo-900">Düzenle</Link>
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
  shifts: Object
})
</script>
