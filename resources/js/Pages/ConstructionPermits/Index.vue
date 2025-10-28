<template>
  <AppLayout title="Ruhsat Listesi" :full-width="true">
    <!-- Header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-amber-600 via-amber-700 to-orange-800 border-b border-amber-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="flex-1 min-w-0">
              <div class="flex items-center space-x-4 mb-3">
                <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                  <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                  </svg>
                </div>
                <div>
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">Ruhsat Listesi</h1>
                  <p class="text-amber-100 text-sm mt-1">{{ permits.total }} ruhsat kaydı</p>
                </div>
              </div>
            </div>

            <div class="flex-shrink-0 flex items-center space-x-3">
              <Link
                :href="route('construction-permits.dashboard')"
                class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm border border-white/30 text-white text-sm font-medium rounded-lg hover:bg-white/20 transition-all duration-200"
              >
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                </svg>
                Dashboard
              </Link>
              <Link
                :href="route('construction-permits.create')"
                class="inline-flex items-center px-4 py-2 bg-white text-amber-600 text-sm font-medium rounded-lg hover:bg-amber-50 transition-all duration-200"
              >
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Yeni Ruhsat
              </Link>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Main Content -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
      <!-- Filters -->
      <div class="bg-white shadow-sm rounded-xl border border-gray-200 mb-6">
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Search -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Arama</label>
              <input
                v-model="filterForm.search"
                type="text"
                placeholder="Ruhsat no, kurum..."
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500"
              />
            </div>

            <!-- Project -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Proje</label>
              <select v-model="filterForm.project_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                <option :value="null">Tüm Projeler</option>
                <option v-for="project in projects" :key="project.id" :value="project.id">{{ project.name }}</option>
              </select>
            </div>

            <!-- Permit Type -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Ruhsat Türü</label>
              <select v-model="filterForm.permit_type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                <option :value="null">Tüm Türler</option>
                <option value="building">Yapı Ruhsatı</option>
                <option value="demolition">Yıkım Ruhsatı</option>
                <option value="occupancy">İskan İzni</option>
                <option value="usage">Yapı Kullanma İzni</option>
              </select>
            </div>

            <!-- Status -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Durum</label>
              <select v-model="filterForm.status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                <option :value="null">Tüm Durumlar</option>
                <option value="pending">Beklemede</option>
                <option value="approved">Onaylandı</option>
                <option value="rejected">Reddedildi</option>
                <option value="expired">Süresi Doldu</option>
                <option value="renewed">Yenilendi</option>
              </select>
            </div>

            <!-- Special Filter -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Özel Filtre</label>
              <select v-model="filterForm.filter" class="w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                <option :value="null">Yok</option>
                <option value="expiring_soon">Süresi Dolacaklar</option>
                <option value="expired">Süresi Dolanlar</option>
                <option value="active">Aktif Ruhsatlar</option>
              </select>
            </div>
          </div>

          <div class="mt-4 flex items-center justify-between">
            <button
              @click="applyFilters"
              class="inline-flex items-center px-4 py-2 bg-amber-600 text-white text-sm font-medium rounded-lg hover:bg-amber-700"
            >
              <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
              </svg>
              Filtrele
            </button>
            <button
              @click="clearFilters"
              class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200"
            >
              Temizle
            </button>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ruhsat Bilgileri</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proje</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kurum</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tarihler</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="permits.data.length === 0">
                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                  <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                  </svg>
                  <p>Ruhsat kaydı bulunamadı</p>
                </td>
              </tr>
              <tr v-for="permit in permits.data" :key="permit.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div>
                    <Link :href="route('construction-permits.show', permit.id)" class="text-sm font-medium text-gray-900 hover:text-amber-600">
                      {{ permit.permit_number }}
                    </Link>
                    <div class="mt-1">
                      <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800">
                        {{ permit.permit_type_label }}
                      </span>
                    </div>
                    <div v-if="permit.documents_count > 0" class="mt-1 flex items-center text-xs text-gray-500">
                      <svg class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                      </svg>
                      {{ permit.documents_count }} belge
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-900">{{ permit.project?.name }}</div>
                  <div class="text-xs text-gray-500">{{ permit.project?.project_code }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-900">{{ permit.issuing_authority || '-' }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div v-if="permit.approval_date" class="text-sm text-gray-900">
                    Onay: {{ formatDate(permit.approval_date) }}
                  </div>
                  <div v-if="permit.expiry_date" class="text-sm text-gray-600 mt-1">
                    Son: {{ formatDate(permit.expiry_date) }}
                  </div>
                  <div v-if="permit.is_expiring_soon && !permit.is_expired" class="mt-1 text-xs text-orange-600">
                    {{ permit.days_until_expiry }} gün kaldı
                  </div>
                  <div v-if="permit.is_expired" class="mt-1 text-xs text-red-600">
                    Süresi doldu
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="permit.status_badge.class" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border">
                    {{ permit.status_badge.text }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <Link
                    :href="route('construction-permits.show', permit.id)"
                    class="text-amber-600 hover:text-amber-900 mr-3"
                  >
                    Görüntüle
                  </Link>
                  <Link
                    :href="route('construction-permits.edit', permit.id)"
                    class="text-blue-600 hover:text-blue-900"
                  >
                    Düzenle
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="permits.data.length > 0" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
              <span class="font-medium">{{ permits.from }}</span> - <span class="font-medium">{{ permits.to }}</span> arası,
              toplam <span class="font-medium">{{ permits.total }}</span> kayıt
            </div>
            <div class="flex space-x-2">
              <template v-for="link in permits.links" :key="link.label">
                <Link
                  v-if="link.url"
                  :href="link.url"
                  :class="[
                    'px-3 py-1 text-sm rounded border',
                    link.active ? 'bg-amber-600 text-white border-amber-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                  ]"
                  v-html="link.label"
                />
                <span
                  v-else
                  :class="[
                    'px-3 py-1 text-sm rounded border',
                    'bg-white text-gray-400 border-gray-300 opacity-50 cursor-not-allowed'
                  ]"
                  v-html="link.label"
                />
              </template>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  permits: Object,
  projects: Array,
  filters: Object,
})

const filterForm = reactive({
  search: props.filters.search || null,
  project_id: props.filters.project_id || null,
  permit_type: props.filters.permit_type || null,
  status: props.filters.status || null,
  filter: props.filters.filter || null,
})

const applyFilters = () => {
  router.get(route('construction-permits.index'), filterForm, {
    preserveState: true,
    preserveScroll: true,
  })
}

const clearFilters = () => {
  filterForm.search = null
  filterForm.project_id = null
  filterForm.permit_type = null
  filterForm.status = null
  filterForm.filter = null
  applyFilters()
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('tr-TR', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}
</script>
