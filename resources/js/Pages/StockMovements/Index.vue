<template>
  <AppLayout title="Stok Hareketleri" :full-width="true">
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-emerald-600 via-emerald-700 to-green-800 border-b border-emerald-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="flex-1 min-w-0">
              <div class="flex items-center space-x-4 mb-3">
                <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                  <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                  </svg>
                </div>
                <div>
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">Stok Hareketleri</h1>
                  <p class="text-emerald-100 text-sm mt-1">Stok giriş-çıkış hareketlerini görüntüleyin ve yönetin</p>
                </div>
              </div>

              <div class="flex flex-wrap items-center gap-4 lg:gap-6">
                <div class="bg-white/10 backdrop-blur-sm rounded-lg px-3 py-2">
                  <span class="text-emerald-100 text-sm">Toplam Hareket:</span>
                  <span class="font-semibold text-white ml-1">{{ movements?.total || 0 }}</span>
                </div>
                <div class="flex items-center space-x-4 text-sm">
                  <span class="flex items-center text-emerald-100">
                    <div class="w-2 h-2 bg-blue-400 rounded-full mr-2"></div>
                    Giriş: <span class="text-white font-medium ml-1">{{ countByType('in') }}</span>
                  </span>
                  <span class="flex items-center text-emerald-100">
                    <div class="w-2 h-2 bg-red-400 rounded-full mr-2"></div>
                    Çıkış: <span class="text-white font-medium ml-1">{{ countByType('out') }}</span>
                  </span>
                </div>
              </div>
            </div>

            <div class="flex-shrink-0">
              <Link
                :href="route('stock-movements.create')"
                class="inline-flex items-center px-4 py-2 bg-white text-emerald-600 text-sm font-medium rounded-lg hover:bg-emerald-50 shadow-lg hover:shadow-xl transition-all duration-200"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Yeni Hareket
              </Link>
            </div>
          </div>
        </div>

        <div class="bg-black/10 border-t border-white/10">
          <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="flex items-center space-x-2 py-2">
              <ol class="flex items-center space-x-2">
                <li>
                  <Link :href="route('dashboard')" class="text-emerald-100 hover:text-white transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                  </Link>
                </li>
                <li class="flex items-center">
                  <svg class="h-3 w-3 text-emerald-200 mx-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                  <span class="text-white font-medium text-sm">Stok Hareketleri</span>
                </li>
              </ol>
            </div>
          </div>
        </div>
      </div>
    </template>

    <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
      <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900">Filtreler</h3>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Arama</label>
              <input
                v-model="filters.search"
                @input="applyFilters"
                type="text"
                placeholder="Malzeme adı..."
                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Depo</label>
              <select
                v-model="filters.warehouse_id"
                @change="applyFilters"
                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
              >
                <option :value="null">Tüm Depolar</option>
                <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">
                  {{ warehouse.name }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Malzeme</label>
              <select
                v-model="filters.material_id"
                @change="applyFilters"
                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
              >
                <option :value="null">Tüm Malzemeler</option>
                <option v-for="material in materials" :key="material.id" :value="material.id">
                  {{ material.name }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Hareket Tipi</label>
              <select
                v-model="filters.movement_type"
                @change="applyFilters"
                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
              >
                <option :value="null">Tümü</option>
                <option value="in">Giriş</option>
                <option value="out">Çıkış</option>
                <option value="transfer">Transfer</option>
                <option value="adjustment">Düzeltme</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Başlangıç</label>
              <input
                v-model="filters.date_from"
                @input="applyFilters"
                type="date"
                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
              />
            </div>

            <div class="flex items-end">
              <button
                @click="resetFilters"
                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg hover:bg-gray-50 text-gray-700 text-sm font-medium transition-colors"
              >
                Temizle
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Tarih</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Malzeme</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Depo</th>
                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Tip</th>
                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Miktar</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">İşlemi Yapan</th>
                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">İşlemler</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="movement in movements.data" :key="movement.id" class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4 text-sm text-gray-900">
                  {{ formatDate(movement.movement_date) }}
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm font-medium text-gray-900">{{ movement.material?.name }}</div>
                  <div class="text-sm text-gray-500">{{ movement.material?.unit }}</div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">
                  {{ movement.warehouse?.name }}
                </td>
                <td class="px-6 py-4 text-center">
                  <span
                    :class="[
                      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                      movement.movement_type === 'in' ? 'bg-blue-100 text-blue-800' :
                      movement.movement_type === 'out' ? 'bg-red-100 text-red-800' :
                      movement.movement_type === 'transfer' ? 'bg-purple-100 text-purple-800' :
                      'bg-gray-100 text-gray-800'
                    ]"
                  >
                    {{ getMovementTypeLabel(movement.movement_type) }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-right font-medium text-gray-900">
                  {{ movement.quantity }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">
                  {{ movement.performed_by?.name }}
                </td>
                <td class="px-6 py-4 text-right text-sm font-medium space-x-2">
                  <Link
                    :href="route('stock-movements.edit', movement.id)"
                    class="text-emerald-600 hover:text-emerald-900 transition-colors"
                  >
                    Düzenle
                  </Link>
                  <button
                    @click="deleteMovement(movement.id)"
                    class="text-red-600 hover:text-red-900 transition-colors"
                  >
                    Sil
                  </button>
                </td>
              </tr>
              <tr v-if="!movements.data.length">
                <td colspan="7" class="px-6 py-12 text-center">
                  <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                  </svg>
                  <p class="mt-2 text-sm text-gray-500">Henüz stok hareketi kaydedilmemiş.</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="movements.data.length" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
          <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-sm text-gray-700">
              <span class="font-medium">{{ movements.from }}</span>
              -
              <span class="font-medium">{{ movements.to }}</span>
              arası gösteriliyor
              (Toplam: <span class="font-medium">{{ movements.total }}</span>)
            </div>
            <div class="flex gap-2">
              <template v-for="link in movements.links" :key="link.label">
                <Link
                  v-if="link.url"
                  :href="link.url"
                  :class="[
                    'px-3 py-2 text-sm rounded-lg border transition-all',
                    link.active
                      ? 'bg-emerald-600 text-white border-emerald-600 font-medium'
                      : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                  ]"
                  v-html="link.label"
                />
                <span
                  v-else
                  :class="[
                    'px-3 py-2 text-sm rounded-lg border transition-all',
                    'bg-gray-100 text-gray-400 border-gray-300 cursor-not-allowed'
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
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  movements: Object,
  warehouses: Array,
  materials: Array,
  filters: Object,
})

const filters = ref({
  search: props.filters?.search || '',
  warehouse_id: props.filters?.warehouse_id || null,
  material_id: props.filters?.material_id || null,
  movement_type: props.filters?.movement_type || null,
  date_from: props.filters?.date_from || '',
  date_to: props.filters?.date_to || '',
})

function countByType(type) {
  return props.movements.data.filter(m => m.movement_type === type).length
}

function getMovementTypeLabel(type) {
  const labels = {
    'in': 'Giriş',
    'out': 'Çıkış',
    'transfer': 'Transfer',
    'adjustment': 'Düzeltme'
  }
  return labels[type] || type
}

function formatDate(date) {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('tr-TR', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit'
  })
}

function applyFilters() {
  router.get(route('stock-movements.index'), filters.value, {
    preserveState: true,
    preserveScroll: true,
  })
}

function resetFilters() {
  filters.value = {
    search: '',
    warehouse_id: null,
    material_id: null,
    movement_type: null,
    date_from: '',
    date_to: '',
  }
  applyFilters()
}

function deleteMovement(id) {
  if (confirm('Bu stok hareketini silmek istediğinizden emin misiniz? Bu işlem stok miktarını etkileyecektir.')) {
    router.delete(route('stock-movements.destroy', id))
  }
}
</script>
