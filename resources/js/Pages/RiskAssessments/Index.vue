<template>
  <AppLayout title="Risk Değerlendirmeleri" :full-width="true">
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-yellow-600 via-amber-600 to-yellow-700 border-b w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex justify-between items-center">
            <div>
              <h1 class="text-2xl lg:text-3xl font-bold text-white">Risk Değerlendirmeleri</h1>
              <p class="text-yellow-100 text-sm mt-1">İş aktiviteleri için risk analizi (RAMS)</p>
            </div>
            <Link :href="route('risk-assessments.create')" class="px-4 py-2 bg-white text-yellow-700 rounded-lg">Yeni Değerlendirme</Link>
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
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aktivite</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Risk Seviyesi</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Durum</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">İşlemler</th>
            </tr>
          </thead>
          <tbody class="divide-y">
            <tr v-for="assessment in assessments.data" :key="assessment.id">
              <td class="px-6 py-4 text-sm">{{ formatDate(assessment.assessment_date) }}</td>
              <td class="px-6 py-4 text-sm">{{ assessment.assessment_title }}</td>
              <td class="px-6 py-4 text-sm">{{ assessment.work_activity }}</td>
              <td class="px-6 py-4 text-sm"><span :class="getRiskClass(assessment.overall_risk_level)" class="px-2 py-1 rounded text-xs">{{ assessment.overall_risk_level }}</span></td>
              <td class="px-6 py-4 text-sm"><span :class="getStatusClass(assessment.status)" class="px-2 py-1 rounded text-xs">{{ assessment.status }}</span></td>
              <td class="px-6 py-4 text-sm space-x-2">
                <Link :href="route('risk-assessments.show', assessment.id)" class="text-blue-600">Detay</Link>
                <Link :href="route('risk-assessments.edit', assessment.id)" class="text-indigo-600">Düzenle</Link>
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

defineProps({ assessments: Object })

const formatDate = (date) => new Date(date).toLocaleDateString('tr-TR')
const getRiskClass = (level) => ({
  'low': 'bg-green-100 text-green-800',
  'medium': 'bg-yellow-100 text-yellow-800',
  'high': 'bg-orange-100 text-orange-800',
  'critical': 'bg-red-100 text-red-800'
}[level])
const getStatusClass = (status) => ({
  'draft': 'bg-gray-100 text-gray-800',
  'submitted': 'bg-blue-100 text-blue-800',
  'approved': 'bg-green-100 text-green-800',
  'active': 'bg-green-100 text-green-800'
}[status])
</script>
