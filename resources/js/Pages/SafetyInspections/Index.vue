<template>
  <AppLayout title="Güvenlik Denetimleri" :full-width="true">
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-purple-600 via-indigo-600 to-purple-700 border-b w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex justify-between items-center">
            <div>
              <h1 class="text-2xl lg:text-3xl font-bold text-white">Güvenlik Denetimleri</h1>
              <p class="text-purple-100 text-sm mt-1">Periyodik güvenlik kontrol ve denetim kayıtları</p>
            </div>
            <Link :href="route('safety-inspections.create')" class="px-4 py-2 bg-white text-purple-600 rounded-lg hover:bg-purple-50">Yeni Denetim</Link>
          </div>
        </div>
      </div>
    </template>
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
      <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="min-w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tarih</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Başlık</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tür</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Skor</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sonuç</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">İşlemler</th>
            </tr>
          </thead>
          <tbody class="divide-y">
            <tr v-for="inspection in inspections.data" :key="inspection.id">
              <td class="px-6 py-4 text-sm">{{ formatDate(inspection.inspection_date) }}</td>
              <td class="px-6 py-4 text-sm">{{ inspection.inspection_title }}</td>
              <td class="px-6 py-4 text-sm">{{ inspection.inspection_type }}</td>
              <td class="px-6 py-4 text-sm">{{ inspection.score || '-' }}</td>
              <td class="px-6 py-4 text-sm"><span :class="getResultClass(inspection.overall_status)" class="px-2 py-1 rounded text-xs">{{ inspection.overall_status }}</span></td>
              <td class="px-6 py-4 text-sm space-x-2">
                <Link :href="route('safety-inspections.show', inspection.id)" class="text-blue-600">Detay</Link>
                <Link :href="route('safety-inspections.edit', inspection.id)" class="text-indigo-600">Düzenle</Link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

defineProps({ inspections: Object })

const formatDate = (date) => new Date(date).toLocaleDateString('tr-TR')
const getResultClass = (status) => ({
  'passed': 'bg-green-100 text-green-800',
  'passed_with_notes': 'bg-blue-100 text-blue-800',
  'requires_action': 'bg-yellow-100 text-yellow-800',
  'failed': 'bg-red-100 text-red-800'
}[status])
</script>
