<template>
  <AppLayout title="Denetim Dashboard" :full-width="true">
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-purple-600 via-purple-700 to-indigo-800 border-b border-purple-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                </svg>
              </div>
              <div>
                <h1 class="text-2xl lg:text-3xl font-bold text-white">Yapı Denetim Dashboard</h1>
                <p class="text-purple-100 text-sm mt-1">Denetim faaliyetlerinin özeti ve istatistikler</p>
              </div>
            </div>
            <div class="flex items-center space-x-3">
              <Link
                :href="route('inspections.create')"
                class="inline-flex items-center px-4 py-2 bg-white text-purple-700 rounded-lg hover:bg-purple-50 font-medium transition-colors"
              >
                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Yeni Denetim Ekle
              </Link>
              <Link
                :href="route('inspections.index')"
                class="inline-flex items-center px-4 py-2 bg-white/10 text-white rounded-lg hover:bg-white/20 font-medium transition-colors backdrop-blur-sm"
              >
                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                </svg>
                Tüm Denetimler
              </Link>
              <Link
                :href="route('inspection-companies.index')"
                class="inline-flex items-center px-4 py-2 bg-white/10 text-white rounded-lg hover:bg-white/20 font-medium transition-colors backdrop-blur-sm"
              >
                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                </svg>
                Denetim Kuruluşları
              </Link>
            </div>
          </div>
        </div>

        <div class="bg-black/10 border-t border-white/10">
          <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="flex items-center space-x-2 py-2">
              <ol class="flex items-center space-x-2">
                <li>
                  <Link :href="route('dashboard')" class="text-purple-100 hover:text-white transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                  </Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-purple-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <span class="text-white font-medium text-sm">Yapı Denetim Dashboard</span>
                </li>
              </ol>
            </div>
          </div>
        </div>
      </div>
    </template>

    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-6">
        <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
          <div class="p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Toplam Denetim</dt>
                  <dd class="text-2xl font-bold text-gray-900">{{ stats.total }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
          <div class="p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-8 w-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Planlandı</dt>
                  <dd class="text-2xl font-bold text-blue-600">{{ stats.scheduled }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
          <div class="p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-8 w-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Eylem Bekliyor</dt>
                  <dd class="text-2xl font-bold text-yellow-600">{{ stats.pending_actions }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
          <div class="p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-8 w-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Kritik Uygunsuzluk</dt>
                  <dd class="text-2xl font-bold text-red-600">{{ stats.critical_non_conformities }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
          <div class="p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-8 w-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Gecikmiş</dt>
                  <dd class="text-2xl font-bold text-orange-600">{{ stats.overdue }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Upcoming Inspections -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Yaklaşan Denetimler (7 Gün İçinde)</h3>
          </div>
          <div class="p-6">
            <div class="space-y-3">
              <div
                v-for="inspection in upcomingInspections"
                :key="inspection.id"
                class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
              >
                <div class="flex-1">
                  <div class="font-medium text-gray-900">{{ inspection.project?.name }}</div>
                  <div class="text-sm text-gray-500 mt-1">{{ inspection.inspection_company?.company_name }}</div>
                </div>
                <div class="text-right ml-4">
                  <div class="text-sm font-medium text-gray-900 mb-1">
                    {{ new Date(inspection.next_inspection_date).toLocaleDateString('tr-TR') }}
                  </div>
                  <Link
                    :href="route('inspections.show', inspection.id)"
                    class="inline-flex items-center text-xs text-purple-600 hover:text-purple-800 font-medium"
                  >
                    Detay →
                  </Link>
                </div>
              </div>
              <div v-if="!upcomingInspections.length" class="text-center py-8 text-gray-500 text-sm">
                Yaklaşan denetim bulunmuyor.
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Inspections -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Son Denetimler</h3>
          </div>
          <div class="p-6">
            <div class="space-y-3">
              <div
                v-for="inspection in recentInspections"
                :key="inspection.id"
                class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
              >
                <div class="flex-1">
                  <div class="font-medium text-gray-900">{{ inspection.project?.name }}</div>
                  <div class="text-sm text-gray-500 mt-1">{{ inspection.inspection_number }}</div>
                </div>
                <div class="text-right ml-4">
                  <div class="text-sm font-medium text-gray-900 mb-1">
                    {{ new Date(inspection.inspection_date).toLocaleDateString('tr-TR') }}
                  </div>
                  <Link
                    :href="route('inspections.show', inspection.id)"
                    class="inline-flex items-center text-xs text-purple-600 hover:text-purple-800 font-medium"
                  >
                    Detay →
                  </Link>
                </div>
              </div>
              <div v-if="!recentInspections.length" class="text-center py-8 text-gray-500 text-sm">
                Denetim kaydı bulunmuyor.
              </div>
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
  stats: Object,
  upcomingInspections: Array,
  recentInspections: Array,
  inspectionsByType: Array,
})
</script>
