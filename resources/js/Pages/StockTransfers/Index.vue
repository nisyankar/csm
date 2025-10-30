<template>
  <AppLayout title="Stok Transferleri" :full-width="true">
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 border-b border-blue-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="flex-1 min-w-0">
              <div class="flex items-center space-x-4 mb-3">
                <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                  <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                  </svg>
                </div>
                <div>
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">Stok Transferleri</h1>
                  <p class="text-blue-100 text-sm mt-1">Depolar arası malzeme transferleri</p>
                </div>
              </div>
              <div class="flex flex-wrap items-center gap-4">
                <div class="bg-white/10 backdrop-blur-sm rounded-lg px-3 py-2">
                  <span class="text-blue-100 text-sm">Toplam:</span>
                  <span class="font-semibold text-white ml-1">{{ statistics.total_transfers }}</span>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-lg px-3 py-2">
                  <span class="text-blue-100 text-sm">Bugün:</span>
                  <span class="font-semibold text-white ml-1">{{ statistics.today_transfers }}</span>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-lg px-3 py-2">
                  <span class="text-blue-100 text-sm">Bu Ay:</span>
                  <span class="font-semibold text-white ml-1">{{ statistics.this_month_transfers }}</span>
                </div>
              </div>
            </div>
            <div class="flex-shrink-0">
              <Link :href="route('stock-transfers.create')" class="inline-flex items-center px-4 py-2 bg-white text-blue-600 text-sm font-medium rounded-lg hover:bg-blue-50 shadow-lg transition-all">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Yeni Transfer
              </Link>
            </div>
          </div>
        </div>
      </div>
    </template>

    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
      <!-- Filters -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
          <input v-model="filterForm.search" type="text" placeholder="Ara..." class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
          <select v-model="filterForm.from_warehouse" class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            <option value="">Kaynak Depo</option>
            <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">{{ warehouse.name }}</option>
          </select>
          <select v-model="filterForm.to_warehouse" class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            <option value="">Hedef Depo</option>
            <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">{{ warehouse.name }}</option>
          </select>
          <input v-model="filterForm.date_from" type="date" class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
          <button @click="applyFilters" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">Filtrele</button>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tarih</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Malzeme</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kaynak</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hedef</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Miktar</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Gerçekleştiren</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">İşlemler</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="transfer in transfers.data" :key="transfer.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatDate(transfer.movement_date) }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ transfer.material?.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ transfer.warehouse?.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ transfer.to_warehouse?.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ transfer.quantity }} {{ transfer.material?.unit }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ transfer.performed_by?.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <Link :href="route('stock-transfers.show', transfer.id)" class="text-blue-600 hover:text-blue-900">Detay</Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="transfers.links" class="bg-gray-50 px-4 py-3 border-t border-gray-200">
          <div class="flex justify-between items-center">
            <div class="text-sm text-gray-600">Toplam {{ transfers.total }} kayıt</div>
            <div class="flex space-x-2">
              <template v-for="link in transfers.links" :key="link.label">
                <Link v-if="link.url" :href="link.url" :class="['px-3 py-1 rounded', link.active ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100']" v-html="link.label" />
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
import { ref, reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  transfers: Object,
  statistics: Object,
  warehouses: Array,
  filters: Object
})

const filterForm = reactive({
  search: props.filters?.search || '',
  from_warehouse: props.filters?.from_warehouse || '',
  to_warehouse: props.filters?.to_warehouse || '',
  date_from: props.filters?.date_from || '',
  date_to: props.filters?.date_to || ''
})

function applyFilters() {
  router.get(route('stock-transfers.index'), filterForm, { preserveState: true })
}

function formatDate(date) {
  return date ? new Date(date).toLocaleDateString('tr-TR') : '-'
}
</script>
