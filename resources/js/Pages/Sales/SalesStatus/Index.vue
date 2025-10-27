<template>
  <AppLayout title="Satış Durumu - Proje Listesi" :full-width="true">
    <!-- Header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-slate-800 via-slate-700 to-gray-800 border-b border-slate-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <!-- Header Info -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center space-x-4">
                <div class="flex-shrink-0 w-12 h-12 bg-emerald-600 rounded-xl flex items-center justify-center shadow-lg">
                  <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                  </svg>
                </div>
                <div>
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">Proje Satış Durumları</h1>
                  <p class="text-slate-300 text-sm mt-1">Tüm projelerin satış istatistiklerini görüntüleyin</p>
                </div>
              </div>
            </div>

            <!-- Stats Summary -->
            <div class="flex-shrink-0">
              <div class="bg-white/10 backdrop-blur-sm rounded-xl border border-white/20 px-6 py-4">
                <div class="text-xs text-slate-200 mb-1">Toplam Proje</div>
                <div class="text-3xl font-bold text-white">{{ projects.length }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Main Content -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
      <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <Link
            v-for="project in projects"
            :key="project.id"
            :href="route('sales.sales-status.show', project.id)"
            class="group bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-xl hover:border-emerald-300 transition-all duration-300 transform hover:-translate-y-1"
          >
            <!-- Project Header -->
            <div class="flex items-start justify-between mb-4">
              <div class="flex items-center space-x-3">
                <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-lg flex items-center justify-center shadow-md">
                  <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                  </svg>
                </div>
                <div>
                  <h3 class="text-lg font-bold text-gray-900 group-hover:text-emerald-600 transition-colors">{{ project.name }}</h3>
                  <p class="text-xs text-gray-500 mt-0.5">{{ project.code }}</p>
                </div>
              </div>
              <span
                :class="[
                  'px-2.5 py-1 text-xs font-semibold rounded-full',
                  project.status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700'
                ]"
              >
                {{ project.status === 'active' ? 'Aktif' : 'Planlama' }}
              </span>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 gap-3 mb-4">
              <div class="bg-gradient-to-br from-slate-50 to-slate-100 rounded-lg p-3 border border-slate-200">
                <div class="text-xs text-slate-600 font-medium">Toplam Birim</div>
                <div class="text-2xl font-bold text-slate-900 mt-1">{{ project.stats.total_units }}</div>
              </div>
              <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-lg p-3 border border-emerald-200">
                <div class="text-xs text-emerald-700 font-medium">Satılan</div>
                <div class="text-2xl font-bold text-emerald-700 mt-1">{{ project.stats.sold_units }}</div>
              </div>
              <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-lg p-3 border border-amber-200">
                <div class="text-xs text-amber-700 font-medium">Rezerve</div>
                <div class="text-2xl font-bold text-amber-700 mt-1">{{ project.stats.reserved_units }}</div>
              </div>
              <div class="bg-gradient-to-br from-sky-50 to-sky-100 rounded-lg p-3 border border-sky-200">
                <div class="text-xs text-sky-700 font-medium">Müsait</div>
                <div class="text-2xl font-bold text-sky-700 mt-1">{{ project.stats.available_units }}</div>
              </div>
            </div>

            <!-- Progress Bar -->
            <div class="mb-4">
              <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-600">Satış Oranı</span>
                <span class="text-xl font-bold text-emerald-600">{{ project.stats.sales_percentage }}%</span>
              </div>
              <div class="w-full bg-slate-200 rounded-full h-2.5 overflow-hidden">
                <div
                  class="h-2.5 rounded-full transition-all duration-500 bg-gradient-to-r from-emerald-500 to-teal-500"
                  :style="{ width: `${Math.min(project.stats.sales_percentage, 100)}%` }"
                ></div>
              </div>
            </div>

            <!-- Sales Amount -->
            <div class="pt-4 border-t border-gray-200 space-y-2">
              <div class="flex justify-between items-center">
                <span class="text-xs text-slate-600 font-medium">Toplam Satış</span>
                <span class="text-sm font-bold text-slate-900">{{ formatCurrency(project.stats.total_sales_amount) }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-xs text-slate-600 font-medium">Tahsil Edilen</span>
                <span class="text-sm font-bold text-emerald-600">{{ formatCurrency(project.stats.total_paid_amount) }}</span>
              </div>
            </div>

            <!-- View Details Button -->
            <div class="mt-4 pt-4 border-t border-gray-100">
              <div class="inline-flex items-center text-sm font-semibold text-emerald-600 group-hover:text-emerald-700">
                Detayları Görüntüle
                <svg class="ml-2 h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                </svg>
              </div>
            </div>
          </Link>

          <!-- Empty State -->
          <div v-if="projects.length === 0" class="col-span-full">
            <div class="bg-gradient-to-br from-slate-50 to-slate-100 rounded-xl shadow-sm border-2 border-dashed border-slate-300 p-16 text-center">
              <div class="inline-flex items-center justify-center w-16 h-16 bg-slate-200 rounded-full mb-4">
                <svg class="h-10 w-10 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                </svg>
              </div>
              <h3 class="text-lg font-semibold text-slate-900">Proje Bulunamadı</h3>
              <p class="mt-2 text-sm text-slate-600 max-w-sm mx-auto">Henüz aktif veya planlama aşamasında proje bulunmamaktadır.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

defineProps({
  projects: {
    type: Array,
    required: true
  }
})

const formatCurrency = (amount) => {
  if (!amount) return '₺0'
  return new Intl.NumberFormat('tr-TR', {
    style: 'currency',
    currency: 'TRY',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount)
}
</script>
