<template>
  <AppLayout title="Stok Sayımları" :full-width="true">
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-purple-600 via-pink-600 to-rose-600 border-b border-purple-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="flex-1 min-w-0">
              <div class="flex items-center space-x-4 mb-3">
                <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                  <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                  </svg>
                </div>
                <div>
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">Stok Sayımları</h1>
                  <p class="text-purple-100 text-sm mt-1">Depo stok sayım kayıtları</p>
                </div>
              </div>
              <div class="flex flex-wrap items-center gap-4">
                <div class="bg-white/10 backdrop-blur-sm rounded-lg px-3 py-2">
                  <span class="text-purple-100 text-sm">Toplam:</span>
                  <span class="font-semibold text-white ml-1">{{ statistics.total_counts }}</span>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-lg px-3 py-2">
                  <span class="text-purple-100 text-sm">Beklemede:</span>
                  <span class="font-semibold text-white ml-1">{{ statistics.pending_counts }}</span>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-lg px-3 py-2">
                  <span class="text-purple-100 text-sm">Onaylı:</span>
                  <span class="font-semibold text-white ml-1">{{ statistics.approved_counts }}</span>
                </div>
              </div>
            </div>
            <div class="flex-shrink-0">
              <Link :href="route('stock-counts.create')" class="inline-flex items-center px-4 py-2 bg-white text-purple-600 text-sm font-medium rounded-lg hover:bg-purple-50 shadow-lg transition-all">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Yeni Sayım
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
          <input v-model="filterForm.search" type="text" placeholder="Ara..." class="rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
          <select v-model="filterForm.warehouse" class="rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
            <option value="">Tüm Depolar</option>
            <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">{{ warehouse.name }}</option>
          </select>
          <select v-model="filterForm.status" class="rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
            <option value="">Tüm Durumlar</option>
            <option value="pending">Beklemede</option>
            <option value="approved">Onaylandı</option>
            <option value="rejected">Reddedildi</option>
          </select>
          <button @click="applyFilters" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">Filtrele</button>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Referans</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tarih</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Depo</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Malzeme</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sistem</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sayım</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fark</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Durum</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">İşlemler</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="count in counts.data" :key="count.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ count.reference_number }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatDate(count.count_date) }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ count.warehouse?.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ count.material?.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ count.system_quantity }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ count.counted_quantity }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <span :class="getDifferenceClass(count.difference)">{{ count.difference }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getStatusClass(count.status)" class="px-2 py-1 text-xs font-medium rounded-full">{{ count.status_label }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 space-x-2">
                  <Link :href="route('stock-counts.show', count.id)" class="text-purple-600 hover:text-purple-900">Detay</Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="counts.links" class="bg-gray-50 px-4 py-3 border-t border-gray-200">
          <div class="flex justify-between items-center">
            <div class="text-sm text-gray-600">Toplam {{ counts.total }} kayıt</div>
            <div class="flex space-x-2">
              <template v-for="link in counts.links" :key="link.label">
                <Link v-if="link.url" :href="link.url" :class="['px-3 py-1 rounded', link.active ? 'bg-purple-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100']" v-html="link.label" />
                <span v-else :class="['px-3 py-1 rounded', 'bg-gray-100 text-gray-400 cursor-not-allowed']" v-html="link.label" />
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
  counts: Object,
  statistics: Object,
  warehouses: Array,
  filters: Object
})

const filterForm = reactive({
  search: props.filters?.search || '',
  warehouse: props.filters?.warehouse || '',
  status: props.filters?.status || ''
})

function applyFilters() {
  router.get(route('stock-counts.index'), filterForm, { preserveState: true })
}

function formatDate(date) {
  return date ? new Date(date).toLocaleDateString('tr-TR') : '-'
}

function getDifferenceClass(diff) {
  if (diff > 0) return 'text-blue-600 font-medium'
  if (diff < 0) return 'text-red-600 font-medium'
  return 'text-green-600 font-medium'
}

function getStatusClass(status) {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}
</script>
