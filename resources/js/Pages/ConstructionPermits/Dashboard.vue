<template>
  <AppLayout title="Ruhsat Yönetimi Dashboard" :full-width="true">
    <!-- Header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-amber-600 via-amber-700 to-orange-800 border-b border-amber-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <!-- Header Info -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center space-x-4 mb-3">
                <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                  <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                  </svg>
                </div>
                <div>
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">Ruhsat Yönetimi</h1>
                  <p class="text-amber-100 text-sm mt-1">Yapı Ruhsatı, İskan İzni ve Diğer İzinler</p>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex-shrink-0 flex items-center space-x-3">
              <Link
                :href="route('construction-permits.create')"
                class="inline-flex items-center px-4 py-2 bg-white text-amber-600 text-sm font-medium rounded-lg hover:bg-amber-50 transition-all duration-200"
              >
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Yeni Ruhsat
              </Link>
              <Link
                :href="route('construction-permits.index')"
                class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm border border-white/30 text-white text-sm font-medium rounded-lg hover:bg-white/20 transition-all duration-200"
              >
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                </svg>
                Tüm Ruhsatlar
              </Link>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Main Content -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-5 mb-6">
        <!-- Total Permits -->
        <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center">
                  <svg class="w-6 h-6 text-slate-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                  </svg>
                </div>
              </div>
              <div class="ml-4 flex-1">
                <p class="text-sm font-medium text-gray-500">Toplam Ruhsat</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats.total }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Pending -->
        <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
                  <svg class="w-6 h-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                  </svg>
                </div>
              </div>
              <div class="ml-4 flex-1">
                <p class="text-sm font-medium text-gray-500">Beklemede</p>
                <p class="text-2xl font-bold text-yellow-600">{{ stats.pending }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Approved -->
        <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                  <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                  </svg>
                </div>
              </div>
              <div class="ml-4 flex-1">
                <p class="text-sm font-medium text-gray-500">Onaylandı</p>
                <p class="text-2xl font-bold text-green-600">{{ stats.approved }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Expiring Soon -->
        <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                  <svg class="w-6 h-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                  </svg>
                </div>
              </div>
              <div class="ml-4 flex-1">
                <p class="text-sm font-medium text-gray-500">Süresi Dolacak</p>
                <p class="text-2xl font-bold text-orange-600">{{ stats.expiring_soon }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Expired -->
        <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                  <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                  </svg>
                </div>
              </div>
              <div class="ml-4 flex-1">
                <p class="text-sm font-medium text-gray-500">Süresi Doldu</p>
                <p class="text-2xl font-bold text-red-600">{{ stats.expired }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Expiring Soon Permits -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Süresi Dolacak Ruhsatlar (30 Gün)</h3>
          </div>
          <div class="divide-y divide-gray-200">
            <div v-if="expiringSoon.length === 0" class="px-6 py-8 text-center text-gray-500">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
              </svg>
              <p class="mt-2">Süresi dolacak ruhsat bulunmuyor</p>
            </div>
            <div v-for="permit in expiringSoon" :key="permit.id" class="px-6 py-4 hover:bg-gray-50 transition-colors">
              <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                  <Link :href="route('construction-permits.show', permit.id)" class="block hover:text-amber-600">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ permit.permit_number }}</p>
                    <p class="text-sm text-gray-500 truncate">{{ permit.project?.name }}</p>
                  </Link>
                  <div class="mt-1 flex items-center space-x-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800">
                      {{ permit.permit_type_label }}
                    </span>
                    <span :class="permit.status_badge.class" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium border">
                      {{ permit.status_badge.text }}
                    </span>
                  </div>
                </div>
                <div class="ml-4 flex-shrink-0 text-right">
                  <p class="text-sm font-medium text-orange-600">{{ formatDate(permit.expiry_date) }}</p>
                  <p class="text-xs text-gray-500">{{ permit.days_until_expiry }} gün kaldı</p>
                </div>
              </div>
            </div>
          </div>
          <div v-if="expiringSoon.length > 0" class="px-6 py-3 bg-gray-50 border-t border-gray-200 text-center">
            <Link :href="route('construction-permits.index', { filter: 'expiring_soon' })" class="text-sm font-medium text-amber-600 hover:text-amber-700">
              Tümünü Görüntüle →
            </Link>
          </div>
        </div>

        <!-- Recent Permits -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Son Ruhsatlar</h3>
          </div>
          <div class="divide-y divide-gray-200">
            <div v-if="recentPermits.length === 0" class="px-6 py-8 text-center text-gray-500">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
              </svg>
              <p class="mt-2">Henüz ruhsat eklenmemiş</p>
            </div>
            <div v-for="permit in recentPermits" :key="permit.id" class="px-6 py-4 hover:bg-gray-50 transition-colors">
              <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                  <Link :href="route('construction-permits.show', permit.id)" class="block hover:text-amber-600">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ permit.permit_number }}</p>
                    <p class="text-sm text-gray-500 truncate">{{ permit.project?.name }}</p>
                  </Link>
                  <div class="mt-1 flex items-center space-x-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800">
                      {{ permit.permit_type_label }}
                    </span>
                    <span :class="permit.status_badge.class" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium border">
                      {{ permit.status_badge.text }}
                    </span>
                  </div>
                  <p v-if="permit.issuing_authority" class="mt-1 text-xs text-gray-500">{{ permit.issuing_authority }}</p>
                </div>
                <div class="ml-4 flex-shrink-0 text-right">
                  <p v-if="permit.approval_date" class="text-sm text-gray-600">{{ formatDate(permit.approval_date) }}</p>
                  <p class="text-xs text-gray-400">{{ formatRelativeTime(permit.created_at) }}</p>
                </div>
              </div>
            </div>
          </div>
          <div v-if="recentPermits.length > 0" class="px-6 py-3 bg-gray-50 border-t border-gray-200 text-center">
            <Link :href="route('construction-permits.index')" class="text-sm font-medium text-amber-600 hover:text-amber-700">
              Tümünü Görüntüle →
            </Link>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  stats: Object,
  permitTypes: Object,
  expiringSoon: Array,
  recentPermits: Array,
})

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('tr-TR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatRelativeTime = (date) => {
  if (!date) return '-'
  const now = new Date()
  const past = new Date(date)
  const diffInSeconds = Math.floor((now - past) / 1000)

  if (diffInSeconds < 60) return 'Az önce'
  if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} dakika önce`
  if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} saat önce`
  if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)} gün önce`

  return formatDate(date)
}
</script>
