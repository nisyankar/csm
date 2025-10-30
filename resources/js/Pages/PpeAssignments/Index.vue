<template>
  <AppLayout title="KKD Atamaları" :full-width="true">
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-cyan-600 via-teal-600 to-cyan-700 border-b w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex justify-between items-center">
            <div>
              <h1 class="text-2xl lg:text-3xl font-bold text-white">KKD Atamaları</h1>
              <p class="text-cyan-100 text-sm mt-1">Kişisel koruyucu donanım takibi</p>
            </div>
            <Link :href="route('ppe-assignments.create')" class="px-4 py-2 bg-white text-cyan-600 rounded-lg">Yeni Atama</Link>
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
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Çalışan</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">KKD Türü</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Marka</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Miktar</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Durum</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">İşlemler</th>
            </tr>
          </thead>
          <tbody class="divide-y">
            <tr v-for="assignment in assignments.data" :key="assignment.id">
              <td class="px-6 py-4 text-sm">{{ formatDate(assignment.assigned_date) }}</td>
              <td class="px-6 py-4 text-sm">{{ assignment.employee?.first_name }} {{ assignment.employee?.last_name }}</td>
              <td class="px-6 py-4 text-sm">{{ assignment.ppe_type }}</td>
              <td class="px-6 py-4 text-sm">{{ assignment.brand || '-' }}</td>
              <td class="px-6 py-4 text-sm">{{ assignment.quantity }}</td>
              <td class="px-6 py-4 text-sm"><span :class="getStatusClass(assignment.status)" class="px-2 py-1 rounded text-xs">{{ assignment.status }}</span></td>
              <td class="px-6 py-4 text-sm space-x-2">
                <Link :href="route('ppe-assignments.show', assignment.id)" class="text-blue-600">Detay</Link>
                <Link :href="route('ppe-assignments.edit', assignment.id)" class="text-indigo-600">Düzenle</Link>
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

defineProps({ assignments: Object })

const formatDate = (date) => new Date(date).toLocaleDateString('tr-TR')
const getStatusClass = (status) => ({
  'assigned': 'bg-green-100 text-green-800',
  'returned': 'bg-blue-100 text-blue-800',
  'lost': 'bg-red-100 text-red-800',
  'damaged': 'bg-orange-100 text-orange-800',
  'expired': 'bg-red-100 text-red-800'
}[status])
</script>
