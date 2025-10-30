<template>
  <AppLayout title="Ekipman Bakım" :full-width="true">
    <template #fullWidthHeader>
      <div class="bg-gradient-to-r from-orange-500 via-amber-500 to-yellow-500 border-b border-orange-900/20 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="flex-1 min-w-0">
              <div class="flex items-center space-x-4 mb-3">
                <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                  <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z" />
                  </svg>
                </div>
                <div>
                  <h1 class="text-2xl lg:text-3xl font-bold text-white">Bakım ve Onarım</h1>
                  <p class="text-orange-100 text-sm mt-1">Ekipman bakım kayıtları</p>
                </div>
              </div>
              <div class="flex flex-wrap items-center gap-4">
                <div class="bg-white/10 backdrop-blur-sm rounded-lg px-3 py-2">
                  <span class="text-orange-100 text-sm">Toplam:</span>
                  <span class="font-semibold text-white ml-1">{{ stats.total_maintenances }}</span>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-lg px-3 py-2">
                  <span class="text-orange-100 text-sm">Planlanan:</span>
                  <span class="font-semibold text-white ml-1">{{ stats.scheduled }}</span>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-lg px-3 py-2">
                  <span class="text-orange-100 text-sm">Toplam Maliyet:</span>
                  <span class="font-semibold text-white ml-1">{{ formatCurrency(stats.total_cost) }}</span>
                </div>
              </div>
            </div>
            <div class="flex-shrink-0">
              <Link :href="route('equipment-maintenance.create')" class="inline-flex items-center px-4 py-2 bg-white text-orange-600 text-sm font-medium rounded-lg hover:bg-orange-50 shadow-lg transition-all">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Yeni Bakım
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
          <input v-model="filterForm.search" type="text" placeholder="Ara..." class="rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
          <select v-model="filterForm.equipment_id" class="rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
            <option value="">Tüm Ekipmanlar</option>
            <option v-for="eq in equipments" :key="eq.id" :value="eq.id">{{ eq.code }} - {{ eq.name }}</option>
          </select>
          <select v-model="filterForm.type" class="rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
            <option value="">Tüm Tipler</option>
            <option value="routine">Rutin Bakım</option>
            <option value="preventive">Önleyici Bakım</option>
            <option value="corrective">Onarım</option>
            <option value="breakdown">Arıza</option>
          </select>
          <select v-model="filterForm.status" class="rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
            <option value="">Tüm Durumlar</option>
            <option value="scheduled">Planlandı</option>
            <option value="in_progress">Devam Ediyor</option>
            <option value="completed">Tamamlandı</option>
          </select>
        </div>
        <div class="mt-4 flex justify-end">
          <button @click="applyFilters" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">Filtrele</button>
        </div>
      </div>

      <!-- Maintenance Table -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kod</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ekipman</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tarih</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tip</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Servis</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Durum</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Maliyet</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">İşlemler</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="maint in maintenances.data" :key="maint.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ maint.maintenance_code }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ maint.equipment?.code }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ formatDate(maint.maintenance_date) }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ maint.type_label }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ maint.service_provider_label }}</td>
                <td class="px-6 py-4 text-sm">
                  <span :class="getStatusClass(maint.status_color)" class="px-2 py-1 rounded-full text-xs">{{ maint.status_label }}</span>
                </td>
                <td class="px-6 py-4 text-sm font-medium">{{ formatCurrency(maint.total_cost) }}</td>
                <td class="px-6 py-4 text-sm space-x-2">
                  <Link :href="route('equipment-maintenance.edit', maint.id)" class="text-indigo-600 hover:text-indigo-800">Düzenle</Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="maintenances.links" class="bg-gray-50 px-4 py-3 border-t border-gray-200">
          <div class="flex justify-between items-center">
            <div class="text-sm text-gray-600">Toplam {{ maintenances.total }} kayıt</div>
            <div class="flex space-x-2">
              <template v-for="link in maintenances.links" :key="link.label">
                <Link v-if="link.url" :href="link.url"
                      :class="['px-3 py-1 rounded', link.active ? 'bg-orange-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100']"
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
import { reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ maintenances: Object, equipments: Array, stats: Object, filters: Object })

const filterForm = reactive({
  search: props.filters?.search || '', equipment_id: props.filters?.equipment_id || '',
  type: props.filters?.type || '', status: props.filters?.status || ''
})

const applyFilters = () => router.get(route('equipment-maintenance.index'), filterForm, { preserveState: true })
const formatDate = (date) => date ? new Date(date).toLocaleDateString('tr-TR') : '-'
const formatCurrency = (amount) => amount ? new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(amount) : '₺0,00'
const getStatusClass = (color) => ({'green': 'bg-green-100 text-green-800', 'blue': 'bg-blue-100 text-blue-800', 'yellow': 'bg-yellow-100 text-yellow-800', 'red': 'bg-red-100 text-red-800'}[color] || 'bg-gray-100 text-gray-800')
</script>
