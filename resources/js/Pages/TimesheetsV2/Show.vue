<template>
  <AppLayout title="Puantaj Detayı">
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
      <div class="max-w-4xl mx-auto space-y-6">
        <!-- Header -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
          <div class="flex justify-between items-start">
            <div>
              <h2 class="text-2xl font-bold text-gray-900">Puantaj Detayı</h2>
              <p class="text-gray-500 mt-1">{{ formatDate(timesheet.work_date) }}</p>
            </div>
            <div class="flex space-x-2">
              <Link v-if="!timesheet.is_locked && !timesheet.is_approved" :href="route('timesheets-v2.edit', timesheet.id)" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
                Düzenle
              </Link>
              <Link :href="route('timesheets-v2.index')" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                Geri
              </Link>
            </div>
          </div>
        </div>

        <!-- Details -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Temel Bilgiler</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-500">Çalışan</label>
              <p class="mt-1 text-sm text-gray-900">{{ timesheet.employee?.name }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-500">Proje</label>
              <p class="mt-1 text-sm text-gray-900">{{ timesheet.project?.name }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-500">Vardiya</label>
              <p class="mt-1 text-sm text-gray-900">{{ timesheet.shift?.name }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-500">Çalışma Saati</label>
              <p class="mt-1 text-sm text-gray-900 font-semibold">{{ timesheet.hours_worked }} saat</p>
            </div>
            <div v-if="timesheet.break_duration">
              <label class="block text-sm font-medium text-gray-500">Mola Süresi</label>
              <p class="mt-1 text-sm text-gray-900">{{ timesheet.break_duration }} saat</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-500">Durum</label>
              <span v-if="timesheet.is_approved" class="mt-1 inline-block px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                Onaylı
              </span>
              <span v-else-if="timesheet.is_locked" class="mt-1 inline-block px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">
                Kilitli
              </span>
              <span v-else class="mt-1 inline-block px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">
                Beklemede
              </span>
            </div>
          </div>

          <div v-if="timesheet.notes" class="mt-6">
            <label class="block text-sm font-medium text-gray-500">Notlar</label>
            <p class="mt-1 text-sm text-gray-900">{{ timesheet.notes }}</p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link } from '@inertiajs/vue3'

defineProps({
  timesheet: Object
})

const formatDate = (date) => new Date(date).toLocaleDateString('tr-TR', { 
  weekday: 'long', 
  year: 'numeric', 
  month: 'long', 
  day: 'numeric' 
})
</script>