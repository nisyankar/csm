<template>
  <AppLayout title="Denetim Kuruluşları" :full-width="true">
    <!-- Full-width header -->
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-purple-600 via-purple-700 to-indigo-800 border-b border-purple-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <!-- Header Info -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center space-x-4 mb-3">
                <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                  <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                  </svg>
                </div>
                <div>
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">Denetim Kuruluşları</h1>
                  <p class="text-purple-100 text-sm mt-1">Yapı denetim kuruluşlarını yönetin</p>
                </div>
              </div>

              <!-- Stats Row -->
              <div class="flex flex-wrap items-center gap-4 lg:gap-6">
                <div class="bg-white/10 backdrop-blur-sm rounded-lg px-3 py-2">
                  <span class="text-purple-100 text-sm">Toplam Kuruluş:</span>
                  <span class="font-semibold text-white ml-1">{{ companies?.total || 0 }}</span>
                </div>
                <div class="flex items-center space-x-4 text-sm">
                  <span class="flex items-center text-purple-100">
                    <div class="w-2 h-2 bg-green-400 rounded-full mr-2"></div>
                    Aktif: <span class="text-white font-medium ml-1">{{ countActive(true) }}</span>
                  </span>
                  <span class="flex items-center text-purple-100">
                    <div class="w-2 h-2 bg-gray-400 rounded-full mr-2"></div>
                    Pasif: <span class="text-white font-medium ml-1">{{ countActive(false) }}</span>
                  </span>
                </div>
              </div>
            </div>

            <!-- Action Button -->
            <div class="flex-shrink-0">
              <Link
                :href="route('inspection-companies.create')"
                class="inline-flex items-center px-4 py-2 bg-white text-purple-600 text-sm font-medium rounded-lg hover:bg-purple-50 shadow-lg hover:shadow-xl transition-all duration-200"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Yeni Kuruluş
              </Link>
            </div>
          </div>
        </div>

        <!-- Breadcrumb -->
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
                  <span class="text-white font-medium text-sm">Denetim Kuruluşları</span>
                </li>
              </ol>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Main Content -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
      <!-- Filters -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900">Filtreler</h3>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Arama</label>
              <input
                v-model="filters.search"
                @input="applyFilters"
                type="text"
                placeholder="Kuruluş adı, belge no..."
                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Durum</label>
              <select
                v-model="filters.is_active"
                @change="applyFilters"
                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
              >
                <option :value="null">Tümü</option>
                <option :value="1">Aktif</option>
                <option :value="0">Pasif</option>
              </select>
            </div>

            <div class="flex items-end">
              <button
                @click="resetFilters"
                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg hover:bg-gray-50 text-gray-700 text-sm font-medium transition-colors"
              >
                Filtreleri Temizle
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Companies Table -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                  Kuruluş Bilgileri
                </th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                  İletişim
                </th>
                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                  Denetim Sayısı
                </th>
                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                  Durum
                </th>
                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">
                  İşlemler
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="company in companies.data" :key="company.id" class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4">
                  <div>
                    <div class="text-sm font-medium text-gray-900">{{ company.company_name }}</div>
                    <div class="text-sm text-gray-500">Belge No: {{ company.license_number }}</div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div>
                    <div class="text-sm text-gray-900">{{ company.contact_person || '-' }}</div>
                    <div class="text-sm text-gray-500">{{ company.phone || '-' }}</div>
                    <div class="text-sm text-gray-500">{{ company.email || '-' }}</div>
                  </div>
                </td>
                <td class="px-6 py-4 text-center">
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                    {{ company.inspections_count }}
                  </span>
                </td>
                <td class="px-6 py-4 text-center">
                  <span
                    v-if="company.is_active"
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"
                  >
                    <svg class="w-2 h-2 mr-1" fill="currentColor" viewBox="0 0 8 8">
                      <circle cx="4" cy="4" r="3" />
                    </svg>
                    Aktif
                  </span>
                  <span
                    v-else
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800"
                  >
                    <svg class="w-2 h-2 mr-1" fill="currentColor" viewBox="0 0 8 8">
                      <circle cx="4" cy="4" r="3" />
                    </svg>
                    Pasif
                  </span>
                </td>
                <td class="px-6 py-4 text-right text-sm font-medium space-x-2">
                  <Link
                    :href="route('inspection-companies.edit', company.id)"
                    class="text-purple-600 hover:text-purple-900 transition-colors"
                  >
                    Düzenle
                  </Link>
                  <button
                    @click="deleteCompany(company.id)"
                    class="text-red-600 hover:text-red-900 transition-colors"
                  >
                    Sil
                  </button>
                </td>
              </tr>
              <tr v-if="!companies.data.length">
                <td colspan="5" class="px-6 py-12 text-center">
                  <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                  </svg>
                  <p class="mt-2 text-sm text-gray-500">Henüz denetim kuruluşu eklenmemiş.</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="companies.data.length" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
          <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-sm text-gray-700">
              <span class="font-medium">{{ companies.from }}</span>
              -
              <span class="font-medium">{{ companies.to }}</span>
              arası gösteriliyor
              (Toplam: <span class="font-medium">{{ companies.total }}</span>)
            </div>
            <div class="flex gap-2">
              <Link
                v-for="link in companies.links"
                :key="link.label"
                :href="link.url"
                :class="[
                  'px-3 py-2 text-sm rounded-lg border transition-all',
                  link.active
                    ? 'bg-purple-600 text-white border-purple-600 font-medium'
                    : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                ]"
                :disabled="!link.url"
                v-html="link.label"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  companies: Object,
  filters: Object,
})

const filters = ref({
  search: props.filters?.search || '',
  is_active: props.filters?.is_active !== undefined ? props.filters.is_active : null,
})

const countActive = (isActive) => {
  return props.companies.data.filter(c => c.is_active === isActive).length
}

function applyFilters() {
  router.get(route('inspection-companies.index'), filters.value, {
    preserveState: true,
    preserveScroll: true,
  })
}

function resetFilters() {
  filters.value = {
    search: '',
    is_active: null,
  }
  applyFilters()
}

function deleteCompany(id) {
  if (confirm('Bu denetim kuruluşunu silmek istediğinizden emin misiniz?')) {
    router.delete(route('inspection-companies.destroy', id))
  }
}
</script>
