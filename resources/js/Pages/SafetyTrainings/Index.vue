<template>
  <AppLayout title="İSG Eğitimleri" :full-width="true">
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-orange-600 via-amber-600 to-orange-700 border-b border-orange-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="flex-1 min-w-0">
              <div class="flex items-center space-x-4 mb-3">
                <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                  <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />
                  </svg>
                </div>
                <div>
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">İSG Eğitimleri</h1>
                  <p class="text-orange-100 text-sm mt-1">İş sağlığı ve güvenliği eğitim kayıtları</p>
                </div>
              </div>
              <div class="flex flex-wrap items-center gap-4">
                <div class="bg-white/10 backdrop-blur-sm rounded-lg px-3 py-2">
                  <span class="text-orange-100 text-sm">Toplam Eğitim:</span>
                  <span class="font-semibold text-white ml-1">{{ trainings?.total || 0 }}</span>
                </div>
              </div>
            </div>
            <div class="flex-shrink-0">
              <Link :href="route('safety-trainings.create')" class="inline-flex items-center px-4 py-2 bg-white text-orange-600 text-sm font-medium rounded-lg hover:bg-orange-50 shadow-lg transition-all">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Yeni Eğitim
              </Link>
            </div>
          </div>
        </div>
      </div>
    </template>
    
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
      <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tarih</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Başlık</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tür</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Katılımcı</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Durum</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">İşlemler</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="training in trainings.data" :key="training.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 text-sm">{{ formatDate(training.training_date) }}</td>
                <td class="px-6 py-4 text-sm">{{ training.training_title }}</td>
                <td class="px-6 py-4 text-sm">{{ training.training_type }}</td>
                <td class="px-6 py-4 text-sm">{{ training.attendee_count || 0 }}</td>
                <td class="px-6 py-4 text-sm"><span :class="getStatusClass(training.status)" class="px-2 py-1 rounded-full text-xs">{{ training.status }}</span></td>
                <td class="px-6 py-4 text-sm space-x-2">
                  <Link :href="route('safety-trainings.show', training.id)" class="text-blue-600">Detay</Link>
                  <Link :href="route('safety-trainings.edit', training.id)" class="text-indigo-600">Düzenle</Link>
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
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

defineProps({ trainings: Object, projects: Array, filters: Object })

const formatDate = (date) => new Date(date).toLocaleDateString('tr-TR')
const getStatusClass = (status) => ({
  'planned': 'bg-blue-100 text-blue-800',
  'completed': 'bg-green-100 text-green-800',
  'cancelled': 'bg-red-100 text-red-800'
}[status] || 'bg-gray-100 text-gray-800')
</script>
