<template>
  <AppLayout title="Ekipmanlar" :full-width="true">
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-yellow-600 via-amber-600 to-orange-600 border-b border-yellow-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="flex-1 min-w-0">
              <div class="flex items-center space-x-4 mb-3">
                <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                  <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                  </svg>
                </div>
                <div>
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">Ekipman Yönetimi</h1>
                  <p class="text-yellow-100 text-sm mt-1">Şantiye ekipmanları ve makineler</p>
                </div>
              </div>
              <div class="flex flex-wrap items-center gap-4">
                <div class="bg-white/10 backdrop-blur-sm rounded-lg px-3 py-2">
                  <span class="text-yellow-100 text-sm">Toplam:</span>
                  <span class="font-semibold text-white ml-1">{{ stats.total_equipments }}</span>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-lg px-3 py-2">
                  <span class="text-yellow-100 text-sm">Müsait:</span>
                  <span class="font-semibold text-white ml-1">{{ stats.available }}</span>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-lg px-3 py-2">
                  <span class="text-yellow-100 text-sm">Kullanımda:</span>
                  <span class="font-semibold text-white ml-1">{{ stats.in_use }}</span>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-lg px-3 py-2">
                  <span class="text-yellow-100 text-sm">Bakım Zamanı:</span>
                  <span class="font-semibold text-white ml-1">{{ stats.maintenance_due }}</span>
                </div>
              </div>
            </div>
            <div class="flex-shrink-0">
              <Link :href="route('equipments.create')" class="inline-flex items-center px-4 py-2 bg-white text-yellow-600 text-sm font-medium rounded-lg hover:bg-yellow-50 shadow-lg transition-all">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Yeni Ekipman
              </Link>
            </div>
          </div>
        </div>
      </div>
    </template>

    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
      <!-- Filters -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <input v-model="filterForm.search" type="text" placeholder="Ara (kod, isim, marka)..." class="rounded-lg border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500">
          <select v-model="filterForm.type" class="rounded-lg border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500">
            <option value="">Tüm Tipler</option>
            <option value="excavator">Ekskavatör</option>
            <option value="bulldozer">Dozer</option>
            <option value="crane">Vinç</option>
            <option value="loader">Yükleyici</option>
            <option value="other">Diğer</option>
          </select>
          <select v-model="filterForm.status" class="rounded-lg border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500">
            <option value="">Tüm Durumlar</option>
            <option value="available">Müsait</option>
            <option value="in_use">Kullanımda</option>
            <option value="maintenance">Bakımda</option>
            <option value="repair">Onarımda</option>
          </select>
          <select v-model="filterForm.ownership" class="rounded-lg border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500">
            <option value="">Tüm Sahiplikler</option>
            <option value="owned">Şirket Malı</option>
            <option value="rented">Kiralık</option>
            <option value="leased">Leasingli</option>
          </select>
        </div>
        <div class="mt-4 flex justify-end">
          <button @click="applyFilters" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors">
            Filtrele
          </button>
        </div>
      </div>

      <!-- Equipment Table -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kod</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">İsim</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tip</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Marka/Model</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sahiplik</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Durum</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Proje</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">İşlemler</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="equipment in equipments.data" :key="equipment.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ equipment.code }}</td>
                <td class="px-6 py-4 text-sm text-gray-900">{{ equipment.name }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ equipment.type_label }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ equipment.brand }} {{ equipment.model }}</td>
                <td class="px-6 py-4 text-sm">
                  <span :class="getOwnershipClass(equipment.ownership)" class="px-2 py-1 rounded-full text-xs">
                    {{ equipment.ownership_label }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm">
                  <span :class="getStatusClass(equipment.status_color)" class="px-2 py-1 rounded-full text-xs">
                    {{ equipment.status_label }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ equipment.current_project?.name || '-' }}</td>
                <td class="px-6 py-4 text-sm space-x-2">
                  <Link :href="route('equipments.show', equipment.id)" class="text-blue-600 hover:text-blue-800">Detay</Link>
                  <Link :href="route('equipments.edit', equipment.id)" class="text-indigo-600 hover:text-indigo-800">Düzenle</Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="equipments.links" class="bg-gray-50 px-4 py-3 border-t border-gray-200">
          <div class="flex justify-between items-center">
            <div class="text-sm text-gray-600">
              Toplam {{ equipments.total }} kayıt - Sayfa {{ equipments.current_page }} / {{ equipments.last_page }}
            </div>
            <div class="flex space-x-2">
              <template v-for="link in equipments.links" :key="link.label">
                <Link v-if="link.url" :href="link.url"
                      :class="['px-3 py-1 rounded', link.active ? 'bg-yellow-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100']"
                      v-html="link.label"
                />
                <span v-else
                      :class="['px-3 py-1 rounded', 'bg-gray-100 text-gray-400 cursor-not-allowed']"
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
import { ref, reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  equipments: Object,
  projects: Array,
  stats: Object,
  filters: Object
})

const filterForm = reactive({
  search: props.filters?.search || '',
  type: props.filters?.type || '',
  status: props.filters?.status || '',
  ownership: props.filters?.ownership || ''
})

const applyFilters = () => {
  router.get(route('equipments.index'), filterForm, { preserveState: true })
}

const getStatusClass = (color) => ({
  'green': 'bg-green-100 text-green-800',
  'blue': 'bg-blue-100 text-blue-800',
  'yellow': 'bg-yellow-100 text-yellow-800',
  'orange': 'bg-orange-100 text-orange-800',
  'red': 'bg-red-100 text-red-800',
  'gray': 'bg-gray-100 text-gray-800'
}[color] || 'bg-gray-100 text-gray-800')

const getOwnershipClass = (ownership) => ({
  'owned': 'bg-blue-100 text-blue-800',
  'rented': 'bg-purple-100 text-purple-800',
  'leased': 'bg-indigo-100 text-indigo-800'
}[ownership] || 'bg-gray-100 text-gray-800')
</script>
